<?php

namespace Datalogix\BuilderMacros\Tests\Macros;

use Datalogix\BuilderMacros\Tests\Database\Models\User;
use Datalogix\BuilderMacros\Tests\TestCase;

class SearchTest extends TestCase
{
    public function test_query_is_the_same_as_where_like()
    {
        $expected = User::whereLike(['name', 'posts.title'], 'foo', false, true, true);
        $actual = User::search(['name', 'posts.title'], 'foo', false, true, true);

        $this->assertEquals($expected->toSql(), $actual->toSql());
        $this->assertEquals($expected->getBindings(), $actual->getBindings());
    }
}
