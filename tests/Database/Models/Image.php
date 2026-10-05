<?php

namespace Datalogix\BuilderMacros\Tests\Database\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Image extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    public function imageable()
    {
        return $this->morphTo();
    }
}
