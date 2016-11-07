<?php

use Spatie\Once\Backtrace;

function once($callback)
{
    $trace = debug_backtrace(
        DEBUG_BACKTRACE_PROVIDE_OBJECT, 2
    )[1];

    $backtrace = new Backtrace($trace);

    if (! $object = $backtrace->getObject()) {
        throw new Exception('Cannot use `once` outside a class');
    }

    $hash = $backtrace->getHash();

    if (! isset($object->__memoized[$hash])) {
        $result = call_user_func($callback, $backtrace->getArguments());

        $object->__memoized[$hash] = $result;
    }

    return $object->__memoized[$hash];
}
