<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Idea;
use App\Models\RoomUser;
use App\Http\Resources\Api\CommentResource;
use App\Actions\Comments\CreateCommentAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CommentController extends Controller {

    public function store(Request $request, int $id, CreateCommentAction $createCommentAction): JsonResponse 
    {
        $validated = $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $idea = Idea::findOrFail($id);

        $comment = $createCommentAction->execute($idea, Auth::user(), $validated['content']);

        $comment->load('author');
        
        return (new CommentResource($comment))
            ->response()
            ->setStatusCode(201);
    }


    public function index(int $id): AnonymousResourceCollection {
        $idea = Idea::findOrFail($id);

        $comments = $idea->comments()
            ->with('author')
            ->orderBy('id', 'asc') // Leitura em ordem cronológica
            ->paginate(10);
        
        return CommentResource::collection($comments);
    }
}
