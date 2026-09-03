<?php

use App\Models\Author;
use App\Models\Idea;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Idea::class)->constrained()->cascadeOnDelete();
            $table->foreignUuidFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Author::class)->constrained()->cascadeOnDelete();
            
            $table->tinyInteger('score'); // Ex: -1 e +1 (upvote/downvote) ou 1 a 5
            $table->string('feedback', 255)->nullable(); // Comentário opcional atrelado à nota
            $table->timestamps();

            // Regra de Integridade: Cada usuário só avalia uma ideia 1 vez por sala
            $table->unique(['idea_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ratings');
    }
};