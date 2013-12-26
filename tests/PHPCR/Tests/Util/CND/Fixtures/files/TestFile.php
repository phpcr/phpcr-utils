<?php

namespace Test\Foobar;

class TestClass
{
    /**
     * Block comment
     */
    public function testMethod($testParam)
    {
        // Line comment
        $string = 'This is a "Test // string"';

        return "Test string";
    }

    // String in "comment"
}
