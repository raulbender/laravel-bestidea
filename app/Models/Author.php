<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Author extends Model {
   // use hasfactory;

    protected $guarded = [];
    
    public function idea(): HasMany {
        return $this->hasMany(Idea::class);
    }
}
