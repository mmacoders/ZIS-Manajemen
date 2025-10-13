<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function index(Request $request)
    {
        $query = Comment::with(['user', 'replies.user']);
        
        if ($request->has('commentable_type') && $request->has('commentable_id')) {
            $query->where('commentable_type', $request->commentable_type)
                  ->where('commentable_id', $request->commentable_id);
        }
        
        if ($request->has('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        } else {
            $query->whereNull('parent_id');
        }
        
        $comments = $query->orderBy('created_at', 'asc')->get();
        
        return response()->json($comments);
    }

    public function store(Request $request)
    {
        $request->validate([
            'commentable_type' => 'required|string',
            'commentable_id' => 'required|integer',
            'content' => 'required|string',
            'parent_id' => 'nullable|exists:comments,id',
        ]);

        $comment = new Comment();
        $comment->user_id = Auth::id();
        $comment->commentable_type = $request->commentable_type;
        $comment->commentable_id = $request->commentable_id;
        $comment->content = $request->content;
        $comment->parent_id = $request->parent_id;
        $comment->save();

        // Load relationships for response
        $comment->load(['user', 'replies.user']);
        
        return response()->json($comment, 201);
    }

    public function update(Request $request, Comment $comment)
    {
        // Check if user is authorized to update this comment
        if ($comment->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'content' => 'required|string',
        ]);

        $comment->content = $request->content;
        $comment->save();

        return response()->json($comment);
    }

    public function destroy(Comment $comment)
    {
        // Check if user is authorized to delete this comment
        if ($comment->user_id !== Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $comment->delete();
        
        return response()->json(['message' => 'Comment deleted successfully']);
    }
}