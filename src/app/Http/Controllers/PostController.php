<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use App\Models\PostLike;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function __construct()
    {
        // Allow public access to index and show so guests can view the feed and post details
        $this->middleware('auth')->except(['index', 'show']);
    }

    public function index()
    {
        $posts = Post::with(['user', 'comments.user'])
                     ->latest()
                     ->paginate(15);

        return view('posts.index', compact('posts'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'images'  => 'nullable|array',
            'images.*'=> 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'videos'  => 'nullable|array',
            'videos.*'=> 'file|mimetypes:video/mp4,video/quicktime,video/x-msvideo,video/x-matroska,video/webm|max:30720',
        ]);

        $imageUrls = [];
        $videoUrls = [];

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $imageFile) {
                $uploadedUrl = uploadToSupabase($imageFile, 'posts');

                if (! $uploadedUrl) {
                    return back()->with('error', 'Upload ảnh thất bại!');
                }

                $imageUrls[] = $uploadedUrl;
            }
        }

        if ($request->hasFile('videos')) {
            foreach ($request->file('videos') as $videoFile) {
                $uploadedUrl = uploadToSupabase($videoFile, 'videos');

                if (! $uploadedUrl) {
                    return back()->with('error', 'Upload video thất bại!');
                }

                $videoUrls[] = $uploadedUrl;
            }
        }

        $imageUrl = $imageUrls[0] ?? null;
        $videoUrl = $videoUrls[0] ?? null;

        Post::create([
            'user_id' => Auth::id(),
            'content' => $request->content,
            'image'   => $imageUrl,
            'video'   => $videoUrl,
            'images'  => $imageUrls ?: null,
            'videos'  => $videoUrls ?: null,
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Đăng bài thành công!');
    }

    public function show(Post $post)
    {
        $comments = $post->comments()
            ->whereNull('parent_id')
            ->with(['user', 'replies.user', 'replies.likes'])
            ->withCount('likes')
            ->latest()
            ->paginate(20);

        $mentionUsers = collect([$post->user])
            ->merge($comments->getCollection()->flatMap(function ($comment) {
                return collect([$comment->user])->merge($comment->replies->pluck('user'));
            }))
            ->filter()
            ->unique('id')
            ->values()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                ];
            });

        return view('posts.show', compact('post', 'comments', 'mentionUsers'));
    }

    public function delete(Post $post)
    {
        if (Auth::id() !== $post->user_id) {
            return back()->with('error', 'Unauthorized');
        }

        $post->delete();

        return back()->with('success', 'Post deleted!');
    }

    public function like(Request $request, Post $post)
    {
        $user = Auth::user();
        $liked = false;

        if ($post->isLikedBy($user->id)) {
            PostLike::where('post_id', $post->id)
                     ->where('user_id', $user->id)
                     ->delete();
        } else {
            PostLike::create([
                'post_id' => $post->id,
                'user_id' => $user->id,
            ]);
            $liked = true;

            if ($post->user_id !== $user->id) {
                Notification::create([
                    'user_id' => $post->user_id,
                    'title' => 'New Like',
                    'message' => $user->name . ' liked your post!',
                    'type' => 'like',
                    'related_user_id' => $user->id,
                ]);
            }
        }

        $post->likes_count = $post->likes()->count();
        $post->save();

        if ($request->wantsJson() || $request->ajax() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'liked' => $liked,
                'likes_count' => $post->likes_count,
            ]);
        }

        return back();
    }

    public function comment(Post $post, Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:300',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $commentImageUrl = null;

        if ($request->hasFile('image')) {
            $commentImageUrl = uploadToSupabase($request->file('image'), 'comment_posts');

            if (! $commentImageUrl) {
                return back()->with('error', 'Upload comment image failed!');
            }
        }

        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => Auth::id(),
            'content' => $validated['content'],
            'image' => $commentImageUrl,
        ]);

        $comment->load(['user', 'replies.user', 'replies.likes']);

        if ($post->user_id !== Auth::id()) {
            Notification::create([
                'user_id' => $post->user_id,
                'title' => 'New Comment',
                'message' => Auth::user()->name . ' commented on your post!',
                'type' => 'comment',
                'related_user_id' => Auth::id(),
            ]);
        }

        if ($request->wantsJson() || $request->ajax() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'type' => 'comment',
                'html' => view('posts.partials.comment_item', [
                    'comment' => $comment,
                    'isReply' => false,
                    'showReplyForm' => true,
                ])->render(),
                'comment_id' => $comment->id,
                'post_id' => $post->id,
                'top_level_comments_count' => $post->comments()->whereNull('parent_id')->count(),
            ]);
        }

        return redirect()->route('posts.show', $post->id)->withFragment('comments-section')->with('success', 'Comment added!');
    }

    public function replyComment(Comment $comment, Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:300',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $replyImageUrl = null;

        if ($request->hasFile('image')) {
            $replyImageUrl = uploadToSupabase($request->file('image'), 'comment_posts');

            if (! $replyImageUrl) {
                return back()->with('error', 'Upload reply image failed!');
            }
        }

        $reply = Comment::create([
            'post_id' => $comment->post_id,
            'user_id' => Auth::id(),
            'parent_id' => $comment->id,
            'content' => $validated['content'],
            'image' => $replyImageUrl,
        ]);

        $reply->load(['user', 'replies.user', 'replies.likes']);

        if ($comment->user_id !== Auth::id()) {
            Notification::create([
                'user_id' => $comment->user_id,
                'title' => 'New Reply',
                'message' => Auth::user()->name . ' replied to your comment!',
                'type' => 'comment',
                'related_user_id' => Auth::id(),
            ]);
        }

        if ($request->wantsJson() || $request->ajax() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'type' => 'reply',
                'html' => view('posts.partials.comment_item', [
                    'comment' => $reply,
                    'isReply' => true,
                    'showReplyForm' => false,
                ])->render(),
                'comment_id' => $reply->id,
                'parent_comment_id' => $comment->id,
                'post_id' => $comment->post_id,
                'top_level_comments_count' => Comment::where('post_id', $comment->post_id)->whereNull('parent_id')->count(),
            ]);
        }

        return redirect()->route('posts.show', $comment->post_id)->withFragment('comments-section')->with('success', 'Reply added!');
    }

    public function toggleCommentLike(Request $request, Comment $comment)
    {
        $existingLike = $comment->likes()->where('user_id', Auth::id())->first();

        if ($existingLike) {
            $comment->likes()->where('user_id', Auth::id())->delete();

            if ($request->wantsJson() || $request->ajax() || $request->header('Accept') === 'application/json') {
                return response()->json([
                    'liked' => false,
                    'likes_count' => $comment->likes()->count(),
                ]);
            }

            return back()->with('success', 'Comment unliked!');
        }

        $comment->likes()->create([
            'user_id' => Auth::id(),
        ]);

        if ($comment->user_id !== Auth::id()) {
            Notification::create([
                'user_id' => $comment->user_id,
                'title' => 'New Comment Like',
                'message' => Auth::user()->name . ' liked your comment!',
                'type' => 'like',
                'related_user_id' => Auth::id(),
            ]);
        }

        if ($request->wantsJson() || $request->ajax() || $request->header('Accept') === 'application/json') {
            return response()->json([
                'liked' => true,
                'likes_count' => $comment->likes()->count(),
            ]);
        }

        return back()->with('success', 'Comment liked!');
    }

    public function deleteComment(Comment $comment)
    {
        if (Auth::id() !== $comment->user_id) {
            return back()->with('error', 'Unauthorized');
        }

        $postId = $comment->post_id;
        $comment->delete();

        return back()->with('success', 'Comment deleted!');
    }

    /**
     * AJAX: load more posts for infinite scroll
     */
    public function loadMore(Request $request)
    {
        $page = (int) $request->query('page', 1);
        $posts = Post::with(['user', 'comments.user'])
            ->withCount('likes')
            ->latest()
            ->paginate(6, ['*'], 'page', $page);

        $html = '';
        foreach ($posts as $post) {
            $html .= view('partials.post_card', ['post' => $post])->render();
        }

        return response()->json([
            'html' => $html,
            'hasMore' => $posts->hasMorePages(),
            'nextPage' => $posts->currentPage() + 1,
        ]);
    }

    public function getLikesCount(Request $request, Post $post)
    {
        $user = Auth::user();
        return response()->json([
            'likes_count' => $post->likes_count ?? $post->likes()->count(),
            'liked' => $user ? $post->isLikedBy($user->id) : false,
        ]);
    }
}
