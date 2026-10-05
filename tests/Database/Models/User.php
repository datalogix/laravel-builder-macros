<?php

namespace Datalogix\BuilderMacros\Tests\Database\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $guarded = [];

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function avatar()
    {
        return $this->morphOne(Image::class, 'imageable');
    }

    public function latestComment()
    {
        return $this->hasOneThrough(Comment::class, Post::class);
    }
}
