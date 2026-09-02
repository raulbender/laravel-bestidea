<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Idea extends Model {
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
}
