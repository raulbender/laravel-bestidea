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
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Idea::class)->constrained()->cascadeOnDelete();
            $table->foreignUuidFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Author::class)->constrained()->cascadeOnDelete();
            
            $table->text('content');
            $table->foreignId('parent_id')->nullable()->constrained('comments')->nullOnDelete(); // Suporte futuro a threads
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};