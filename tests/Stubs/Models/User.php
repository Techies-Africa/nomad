<?php

namespace TechiesAfrica\Nomad\Tests\Stubs\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $table = 'users';
    protected $guarded = [];
}
