<?php

namespace PHPCR\Util\CndParser;

class CndParser
{
    protected $lineNo = 0;
    protected $lines = array();
    protected $scnr;

    protected $validPropTypes = array(
        'string',
        'binary',
        'long',
        'double',
        'boolean',
        'date',
        'name',
        'path',
        'reference',
        'undefined',
        '*'
    );

    public function parse($lines)
    {
        $this->lines = $lines;
        $this->scnr = new Scanner($lines);
        $this->check('Cnd');
    }

    public function getScanner()
    {
        return $this->scnr;
    }


    protected function check($symbol, $optional = false)
    {
        $method = 'do'.$symbol;
        try {
            $this->$method();
            return true;
        } catch (ParseError $e) {
            if (false === $optional) {
                throw $e;
            }

            $this->scnr->reweindToLastCommit();
            return false;
        }
    }

    protected function checkMany($symbols)
    {
        foreach ($symbols as $symbol) {
            if (false === $this->check($symbol, true)) {
                break;
            }
        }
    }

    protected function doCnd()
    {
        $this->check('NsMapping', false);
        $this->check('NodeTypeDef', false);
    }

    protected function doNsMapping()
    {
        $this->scnr->expect('symbol', '<');
        $this->scnr->expect('string', 'ns');
        $this->scnr->expect('symbol', '=');
        $ns = $this->scnr->getValue('string');
        $this->scnr->expect('symbol', '>');
        $this->scnr->commit();
    }

    protected function doNodeTypeDef()
    {
        $this->check('NodeTypeName');
        $this->check('Supertypes', true);
        $this->checkUnordered(array(
            'Orderable',
            'Mixin'
        ));
        $this->checkMany('PropertyDef');
    }

    protected function doNodeTypeName()
    {
        $this->scnr->expect('symbol', '[');
        $fullName = $this->scnr->getValue('string');
        $this->scnr->expect('symbol', ']');
        $this->scnr->commit();
    }

    protected function doSupertypes()
    {
        $supertypes = array();
        $this->scnr->expect('symbol', '>');
        while ($string = $this->scnr->getValue('string')) {
            $supertypes[] = $string;
            if (null === $this->scnr->expect('symbol', ',', true)) {
                break;
            }
        }
        $this->scnr->commit();
    }

    protected function doOrderable()
    {
        $this->scnr->expect('string', array('orderable', 'ord', 'o'));
        $this->commit();
        $orderable = true;
    }

    protected function doMixin()
    {
        $this->scnr->expect('string', array('mixin', 'mix', 'm'));
        $this->commit();
        $mixin = true;
    }

    protected function doPropertyDef()
    {
        $this->scnr->expect('symbol', '-');
        $propName = $this->scnr->getValue('string');
        $this->scnr->expect('symbol', '(');
        $propType = strtolower($this->scnr->getValue('string')); // just make them all lowercase

        // validate property type
        if (!in_array($propType, $this->validPropTypes)) {
            $t = $this->scnr->getCurrentToken();
            throw new ParseError(sprintf('Property "%s" has invalid type "%s", valid types are: "%s"',
                $propName, $propType, implode(',', $this->validPropTypes)), $t['lineNo']
            );
        }

        $this->scnr->expect('symbol', ')');
        $this->scnr->commit();
    }

    public function __toString()
    {
        return "Dump:\n".implode("\n", $this->lines);
    }
}
