<?php

namespace TechiesAfrica\Nomad\Tests\Stubs\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';
    protected $guarded = [];

    protected $casts = [
        'published_at' => 'datetime',
    ];
}
