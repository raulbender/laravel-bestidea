<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Idea extends Model {

    use HasFactory;

    protected $guarded = [];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function author(): BelongsTo {
        return $this->belongsTo(Author::class);
    }

    public function room(): BelongsTo {
        return $this->belongsTo(Room::class);
    }

    public function ratings(): HasMany {
        return $this->hasMany(Rating::class);
    }

    public function comments(): HasMany {
        return $this->hasMany(Comment::class);
    }
}
