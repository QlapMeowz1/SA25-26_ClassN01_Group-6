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
        $this->middleware('auth');
    }

    public function index()
    {
        $posts = Post::with('user', 'likes', 'comments')
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
        $comments = $post->comments()->with('user')->latest()->paginate(20);

        return view('posts.show', compact('post', 'comments'));
    }

    public function delete(Post $post)
    {
        if (Auth::id() !== $post->user_id) {
            return back()->with('error', 'Unauthorized');
        }

        $post->delete();

        return back()->with('success', 'Post deleted!');
    }

    public function like(Post $post)
    {
        $user = Auth::user();

        if ($post->isLikedBy($user->id)) {
            PostLike::where('post_id', $post->id)
                     ->where('user_id', $user->id)
                     ->delete();
            $post->likes_count -= 1;
        } else {
            PostLike::create([
                'post_id' => $post->id,
                'user_id' => $user->id,
            ]);
            $post->likes_count += 1;

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

        $post->save();

        return back();
    }

    public function comment(Post $post, Request $request)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:300',
        ]);

        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => Auth::id(),
            'content' => $validated['content'],
        ]);

        if ($post->user_id !== Auth::id()) {
            Notification::create([
                'user_id' => $post->user_id,
                'title' => 'New Comment',
                'message' => Auth::user()->name . ' commented on your post!',
                'type' => 'comment',
                'related_user_id' => Auth::id(),
            ]);
        }

        return back()->with('success', 'Comment added!');
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
        $posts = Post::with(['user', 'comments'])
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
}
