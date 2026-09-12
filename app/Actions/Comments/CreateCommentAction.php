<?php

namespace App\Actions\Comments;

use App\Actions\Rooms\AssignAuthorToRoomAction;
use App\Models\Comment;
use App\Models\Idea;
use App\Models\User;
use App\Models\Rating;

class CreateCommentAction
{
    public function __construct(
        private AssignAuthorToRoomAction $assignAuthorAction
    ) {}

    public function execute(Idea $idea, User $user, string $content, bool $attachRating = false): Comment
    {
        // Reutiliza a Action de vínculo com a sala
        $roomUser = $this->assignAuthorAction->execute($idea->room, $user);

        // Busca o rating prévio do usuário nesta ideia se a flag for verdadeira
        $ratingId = null;
        if ($attachRating) {
            $ratingId = Rating::where('idea_id', $idea->id)
                ->where('user_id', $user->id)
                ->value('id');
        }

        // Se estiver vinculado a um rating existente, verifica se o comentário daquele rating já existe
        if ($ratingId) {
            $existingComment = Comment::where('rating_id', $ratingId)
                ->where('user_id', $user->id)
                ->first();

            if ($existingComment) {
                $existingComment->update([
                    'content' => $content,
                ]);

                return $existingComment;
            }
        }

        // Cria o comentário
        $comment = Comment::create([
            'idea_id'   => $idea->id,
            'user_id'   => $user->id,
            'author_id' => $roomUser->author_id,
            'rating_id' => $ratingId,            
            'content'   => $content,
        ]);

        // Incrementa o contador na ideia
        $idea->increment('comments_count');

        return $comment;
    }
}