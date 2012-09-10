<?php

namespace PHPCR\Util\CND\Helper;

/**
 * This abstract class provides few debugging function that will only
 * have an effect if the named constant DEBUG exists and is set to true.
 *
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 */
abstract class AbstractDebuggable
{
    /**
     * Display the message on the console.
     * @param string $msg The message to display
     * @param int $indent The indentation to use (size = 2 char)
     * @return void
     */
    protected function debug($msg, $indent = 0)
    {
        //@codeCoverageIgnoreStart
        if (defined('DEBUG') && DEBUG) {
            echo sprintf("%s%s\n", str_repeat('  ', $indent), $msg);
        }
        //@codeCoverageIgnoreEnd
    }

    /**
     * Display the message as a result, indented by 1 and prefixed with '=>'
     * @param string $msg The message to display
     * @return void
     */
    protected function debugRes($msg)
    {
        $this->debug("=> " . $msg, 1);
    }

    /**
     * Display the message as a section header
     * @param string $msg The message to display
     * @return void
     */
    protected function debugSection($msg)
    {
        $this->debug(sprintf("\n\n----- %s %s\n", $msg, str_repeat('-', 80 - strlen($msg))));
    }

}
