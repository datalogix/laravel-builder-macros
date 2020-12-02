<?php

namespace Datalogix\BuilderMacros\Tests\Database\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $guarded = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
