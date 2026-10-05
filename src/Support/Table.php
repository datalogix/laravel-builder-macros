<?php

namespace Datalogix\BuilderMacros\Support;

class Table
{
    /**
     * Get the name used to reference the table, which is the alias when the table is aliased.
     *
     * Returns null when the table is an expression, like a raw or sub query, as its name can't be resolved.
     *
     * @param  mixed  $from
     * @return string|null
     */
    public static function reference($from)
    {
        if (! is_string($from)) {
            return null;
        }

        // "users as u" is referenced as "u"
        $parts = preg_split('/\s+as\s+/i', trim($from));

        return end($parts);
    }
}
