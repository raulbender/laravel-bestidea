<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Author extends Model {
    protected $guarded = [];
    //


    public function ideas(): HasMany {
        return $this->hasMany(Ideas::class);
    }
}
