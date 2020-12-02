<?php

namespace Datalogix\BuilderMacros\Tests\Database\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $guarded = false;

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
