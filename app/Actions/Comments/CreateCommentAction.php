<?php

namespace App\Actions\Comments;

use App\Actions\Rooms\AssignAuthorToRoomAction;
use App\Models\Comment;
use App\Models\Idea;
use App\Models\User;

class CreateCommentAction
{
    public function __construct(
        private AssignAuthorToRoomAction $assignAuthorAction
    ) {}

    public function execute(Idea $idea, User $user, string $content): Comment
    {
        // Reutiliza a Action de vínculo com a sala
        $roomUser = $this->assignAuthorAction->execute($idea->room, $user);

        // Cria o comentário
        $comment = Comment::create([
            'idea_id'   => $idea->id,
            'user_id'   => $user->id,
            'author_id' => $roomUser->author_id,
            'content'   => $content,
        ]);

        // Incrementa o contador na ideia
        $idea->increment('comments_count');

        return $comment;
    }
}