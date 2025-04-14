<?php

namespace Datalogix\BuilderMacros\Tests\Macros;

use Datalogix\BuilderMacros\Tests\Database\Models\Post;
use Datalogix\BuilderMacros\Tests\TestCase;

class JoinRelationTest extends TestCase
{
    public function test_query()
    {
        $expected = 'select * from "posts" inner join "users" on "posts"."user_id" = "users"."id"';
        $actual = Post::joinRelation('user')->toSql();

        $this->assertEquals($expected, $actual);
    }
}
