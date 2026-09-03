<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Author;
use App\Models\Room;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ideas', function (Blueprint $table) {
            $table->id();
            $table->foreignUuidFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Author::class)->constrained()->cascadeOnDelete();            
            $table->foreignIdFor(Room::class)->constrained()->cascadeOnDelete();
            $table->text('content');
            $table->integer('total_score')->default(0)->after('content');
            $table->unsignedInteger('ratings_count')->default(0)->after('total_score');
            $table->timestamps();
            $table->index(['room_id', 'total_score']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ideas');
    }
};
