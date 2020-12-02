<?php

namespace Datalogix\BuilderMacros\Tests\Macros;

use Datalogix\BuilderMacros\Tests\Database\Models\Post;
use Datalogix\BuilderMacros\Tests\TestCase;

class LeftJoinRelationTest extends TestCase
{
    public function testQuery()
    {
        $expected = 'select * from "posts" left join "users" on "posts"."user_id" = "users"."id"';
        $actual = Post::query()->leftJoinRelation('user')->toSql();

        $this->assertEquals($expected, $actual);
    }
}
