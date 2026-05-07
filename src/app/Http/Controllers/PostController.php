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
        $validated = $request->validate([
            'content' => 'required|string|max:500',
        ]);

        Post::create([
            'user_id' => Auth::id(),
            'content' => $validated['content'],
        ]);

        return back()->with('success', 'Post created!');
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
}
