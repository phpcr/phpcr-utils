<?php

namespace PHPCR\Util\CND\Scanner;

use PHPCR\Util\CND\Reader\ReaderInterface;

class PhpScanner extends AbstractScanner
{
    /**
     * @param \PHPCR\Util\CND\Reader\ReaderInterface $reader
     * @return \PHPCR\Util\CND\Scanner\TokenQueue
     */
    public function scan(ReaderInterface $reader)
    {
        $tokens = array();
        $curLine = 0;

        foreach (token_get_all(substr($reader->getBuffer(), 0, -1)) as $phpToken) {
            
            if (is_string($phpToken)) {

                $token = new Token(0, trim($phpToken), $curLine);

            } elseif ($phpToken[0] !== T_WHITESPACE) {

                $line = isset($phpToken[2]) ? $phpToken[2] : 0;
                $token = new Token($phpToken[0], trim($phpToken[1]), $line);
                $curLine = $line;

            } else {
                // Strip whitespaces
                continue;
            }

            $token = $this->applyFilters($token);

            if ($token) {
                $tokens[] = $token;
            }
        }

        return new TokenQueue($tokens);
    }

}
