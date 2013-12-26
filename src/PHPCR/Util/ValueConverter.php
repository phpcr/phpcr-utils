<?php

namespace PHPCR\Util;

use PHPCR\NodeInterface;
use PHPCR\PropertyInterface;
use PHPCR\PropertyType;
use PHPCR\RepositoryException;
use PHPCR\ValueFormatException;

/**
 * PHPCR compliant conversion between values.
 *
 * This class also proxies the determineType method to allow extending classes
 * to handle custom types.
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 *
 * @author David Buchmann <mail@davidbu.ch>
 *
 * @api
 */
class ValueConverter
{
    /**
     * Determine PropertyType from on variable type.
     *
     * This is most of the remainder of ValueFactory that is still needed.
     *
     * - if the given $value is a Node object, type will be REFERENCE, unless
     *    $weak is set to true which results in WEAKREFERENCE
     * - if the given $value is a DateTime object, the type will be DATE.
     *
     * Note that string is converted to date exactly if it matches the jcr
     * formatting spec for dates (sYYYY-MM-DDThh:mm:ss.sssTZD) according to
     * http://www.day.com/specs/jcr/2.0/3_Repository_Model.html#3.6.4.3%20From%20DATE%20To
     *
     * @param mixed   $value The variable we need to know the type of
     * @param boolean $weak  When a Node is given as $value this can be given
     *                       as true to create a WEAKREFERENCE.
     *
     * @return int One of the type constants
     *
     * @throws ValueFormatException if the type can not be determined
     */
    public function determineType($value, $weak = false)
    {
        return PropertyType::determineType($value, $weak);
    }

    /**
     * Attempt to convert $values into the proper format for $type.
     *
     * This is the other remaining part of ValueFactory functionality that is
     * still needed.
     *
     * If a $srctype is specified, the conversion also checks whether the
     * conversion is allowed according to the property type conversion of the
     * jcr specification (link below). This might be needed because NAME and
     * other properties have quite restricted conversion matrix but in php will
     * be modelled as string.
     *
     * Note that for converting to boolean, we follow the PHP convention of
     * treating any non-empty string as true, not just the word "true".
     *
     * Note for implementors: You should handle the special case of $value
     * being a PropertyInterface with a binary value. If you go through this
     * method, the stream will have to be loaded and rewritten, instead of
     * being directly copied.
     *
     * Table based on <a href="http://www.day.com/specs/jcr/2.0/3_Repository_Model.html#3.6.4%20Property%20Type%20Conversion">JCR spec</a>
     *
        <TABLE>
        <TR><TD><BR></TD><TD>STRING (1)</TD><TD>BINARY (2)</TD><TD>LONG (3)</TD><TD>DOUBLE (4)</TD><TD>DATE (5)</TD><TD>BOOLEAN (6)</TD><TD>NAME(7)</TD><TD>PATH (8)</TD><TD>REFERENCE (9/10)</TD><TD>URI (11)</TD><TD>DECIMAL (12)</TD></TR>
        <TR><TD>STRING</TD><TD>x</TD><TD>Utf-8 encoded</TD><TD>cast to int</TD><TD>cast to float</TD><TD>SYYYY-MM-DDThh:Mm:ss.sssTZD</TD><TD><I>'' is false, else true</I></TD><TD>if valid name, name</TD><TD>if valid path, as name</TD><TD>check valid uuid</TD><TD>RFC 3986</TD><TD>string</TD></TR>
        <TR><TD>BINARY</TD><TD>Utf-8</TD><TD>x</TD><TD COLSPAN="9" BGCOLOR="#E6E6E6">Converted to string and then interpreted as above</TD></TR>
        <TR><TD>LONG</TD><TD>cast to string</TD><TD>String, then Utf-8</TD><TD>x</TD><TD>cast to float</TD><TD>Unix Time</TD><TD><I>0 false else true</I></TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>cast to string</TD></TR>
        <TR><TD>DOUBLE</TD><TD>cast to string</TD><TD>String, then Utf-8</TD><TD>cast to int</TD><TD>x</TD><TD>Unix Time</TD><TD><I>0.0 is false, else true</I></TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>cast to string</TD></TR>
        <TR><TD>DATE</TD><TD>SYYYY-MM-DDThh:<BR>Mm:ss.sssTZD</TD><TD>String, then Utf-8</TD><TD>Unix timestamp</TD><TD>Unix timestamp</TD><TD>x</TD>
        <TD><I>true</I></TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>Unix timestamp</TD></TR>
        <TR><TD>BOOLEAN</TD><TD>cast to string</TD><TD>String, then Utf-8</TD><TD>0/1</TD><TD>0.0/1.0</TD><TD>ValueFormatException</TD><TD>x</TD><TD>'0'/'1'</TD>
        <TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD></TR>
        <TR><TD>NAME</TD><TD>Qualified form</TD><TD>String, then Utf-8</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>x</TD><TD>noop (relative path)</TD><TD>ValueFormatException</TD><TD>„./“ and qualified name. % encode illegal characters</TD><TD>ValueFormatException</TD></TR>
        <TR><TD>PATH</TD><TD>Standard form</TD><TD>String, then Utf-8</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>if relative path lenght 1 noop / otherwise ValueFormatException</TD><TD>x</TD><TD>ValueFormatException</TD><TD>„./“ if not starting with /. % encode illegal characters</TD><TD>ValueFormatException</TD></TR>
        <TR><TD>REFERENCE</TD><TD>noop</TD><TD>String, then Utf-8</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>x</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD></TR>
        <TR><TD>URI</TD><TD>noop</TD><TD>String, then Utf-8</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD>
        <TD>ValueFormatException</TD><TD>single name: decode %, remove ./  else ValueFormatException</TD><TD>Decode %, remove leading ./ . if not star w. name, / or ./ then ValueFormatException</TD><TD>ValueFormatException</TD><TD>x</TD><TD>ValueFormatException</TD></TR>
        <TR><TD>DECIMAL</TD><TD>noop</TD><TD>Utf-8 encoded</TD><TD>cast to int</TD><TD>cast to float</TD><TD>Unix Time</TD><TD><I>0 false else true</I></TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>x</TD></TR>
        </TABLE>
     *
     * @param mixed $value   The value or value array to check and convert
     * @param int   $type    Target type to convert into. One of the type constants in PropertyType
     * @param int   $srctype Source type to convert from, if not specified this is automatically determined, which will miss the string based types that are not strings (DECIMAL, NAME, PATH, URI)
     *
     * @return mixed the value casted into the proper format (throws an exception if conversion is not possible)
     *
     * @throws ValueFormatException      is thrown if the specified value cannot be converted to the specified type
     * @throws RepositoryException       if the specified Node is not referenceable, the current Session is no longer active, or another error occurs.
     * @throws \InvalidArgumentException if the specified DateTime value cannot be expressed in the ISO 8601-based format defined in the JCR 2.0 specification and the implementation does not support dates incompatible with that format.
     *
     * @see http://www.day.com/specs/jcr/2.0/3_Repository_Model.html#3.6.4%20Property%20Type%20Conversion
     */
    public function convertType($value, $type, $srctype = PropertyType::UNDEFINED)
    {
        if (is_array($value)) {
            $ret = array();
            foreach ($value as $v) {
                $ret[] = self::convertType($v, $type, $srctype);
            }

            return $ret;
        }

        if (PropertyType::UNDEFINED == $srctype) {
            $srctype = $this->determineType($value);
        }

        if ($value instanceof PropertyInterface) {
            $value = $value->getValue();
        }

        // except on noop, stream needs to be read into string first
        if (PropertyType::BINARY == $srctype && PropertyType::BINARY != $type && is_resource($value)) {
            $t = stream_get_contents($value);
            rewind($value);
            $value = $t;
            $srctype = PropertyType::STRING;
        } elseif ((PropertyType::REFERENCE == $srctype || PropertyType::WEAKREFERENCE == $srctype )
            && $value instanceof NodeInterface
        ) {
            /** @var $value NodeInterface */
            // In Jackrabbit a new node cannot be referenced until it has been persisted
            // See: https://issues.apache.org/jira/browse/JCR-1614
            if ($value->isNew()) {
                throw new ValueFormatException('Node ' . $value->getPath() . ' must be persisted before being referenceable');
            }
            if (! $value->isNodeType('mix:referenceable')) {
                throw new ValueFormatException('Node ' . $value->getPath() . ' is not referenceable');
            }
            $value = $value->getIdentifier();
        }

        switch ($type) {
            case PropertyType::STRING:
                switch ($srctype) {
                    case PropertyType::DATE:
                        if (! $value instanceof \DateTime) {
                            throw new RepositoryException('Can not convert a date that is not a \DateTime instance to string');
                        }
                        /** @var $value \DateTime */
                        // Milliseconds formatting is not possible in PHP so we
                        // construct it by cutting microseconds to 3 positions.
                        // This might not be as accurate as "real" rounded milliseconds.
                        return $value->format('Y-m-d\TH:i:s.') .
                            substr($value->format('u'), 0, 3) .
                            $value->format('P');
                    case PropertyType::NAME:
                    case PropertyType::PATH:
                        // TODO: The name/path is converted to qualified form according to the current local namespace mapping (see §3.2.5.2 Qualified Form).
                         return $value;
                    default:
                        if (is_object($value)) {
                            throw new ValueFormatException('Can not convert object of class '.get_class($value).' to STRING');
                        }
                        if (is_resource($value)) {
                            throw new ValueFormatException('Inconsistency: Non-binary property should not have resource stream value');
                        }
                        // TODO: how can we provide ValueFormatException on failure? invalid casting leads to 'catchable fatal error' instead of exception
                        return (string) $value;
                }

            case PropertyType::BINARY:
                if (is_resource($value)) {
                    return $value;
                }
                if (! is_string($value)) {
                    $value = $this->convertType($value, PropertyType::STRING, $srctype);
                }
                $f = fopen('php://memory', 'rwb+');
                fwrite($f, $value);
                rewind($f);

                return $f;

            case PropertyType::LONG:
                switch ($srctype) {
                    case PropertyType::STRING:
                    case PropertyType::LONG:
                    case PropertyType::DOUBLE:
                    case PropertyType::BOOLEAN:
                    case PropertyType::DECIMAL:
                        return (integer) $value;
                    case PropertyType::DATE:
                        if (! $value instanceof \DateTime) {
                            throw new RepositoryException('something weird');
                        }
                        /** @var $value \DateTime */

                        return $value->getTimestamp();
                }
                if (is_object($value)) {
                    throw new ValueFormatException('Can not convert object of class '.get_class($value).' to a LONG');
                }
                throw new ValueFormatException('Can not convert '.var_export($value, true).' to a LONG');

            case PropertyType::DOUBLE:
                switch ($srctype) {
                    case PropertyType::STRING:
                    case PropertyType::LONG:
                    case PropertyType::DOUBLE:
                    case PropertyType::BOOLEAN:
                    case PropertyType::DECIMAL:
                        return (double) $value;
                    case PropertyType::DATE:
                        if (! $value instanceof \DateTime) {
                            throw new RepositoryException('something weird');
                        }

                        /** @var $value \DateTime */

                        return (double) $value->getTimestamp();
                }
                if (is_object($value)) {
                    throw new ValueFormatException('Can not convert object of class '.get_class($value).' to a DOUBLE');
                }
                throw new ValueFormatException('Can not convert '.var_export($value, true).' to a DOUBLE');

            case PropertyType::DATE:
                switch ($srctype) {
                    case PropertyType::STRING:
                    case PropertyType::DATE:
                        if ($value instanceof \DateTime) {
                            return $value;
                        }
                        try {
                            return new \DateTime($value);
                        } catch (\Exception $e) {
                            throw new ValueFormatException("String '$value' is not a valid date", null, $e);
                        }
                    case PropertyType::LONG:
                    case PropertyType::DOUBLE:
                    case PropertyType::DECIMAL:
                        $datetime = new \DateTime();
                        $datetime = $datetime->setTimestamp($value);

                        return $datetime;
                }
                if (is_object($value)) {
                    throw new ValueFormatException('Can not convert object of class '.get_class($value).' to a DATE');
                }
                throw new ValueFormatException('Can not convert '.var_export($value, true).' to DATE');

            case PropertyType::BOOLEAN:
                switch ($srctype) {
                    case PropertyType::STRING:
                    case PropertyType::LONG:
                    case PropertyType::DOUBLE:
                    case PropertyType::BOOLEAN:
                        return (boolean) $value;
                    case PropertyType::DATE:
                        /** @var $value \DateTime */

                        return (boolean) $value->getTimestamp();
                    case PropertyType::DECIMAL:
                        return (boolean) ((double) $value); // '0' is false too
                }
                if (is_object($value)) {
                    throw new ValueFormatException('Can not convert object of class '.get_class($value).' to a BOOLEAN');
                }
                throw new ValueFormatException('Can not convert '.var_export($value, true).' to a BOOLEAN');

            case PropertyType::NAME:
                switch ($srctype) {
                    case PropertyType::STRING:
                    case PropertyType::PATH:
                    case PropertyType::NAME:
                        // TODO: check if valid
                        return $value;
                    case PropertyType::URI:
                        // TODO: check if valid, remove leading ./, decode
                        return $value;
                }
                if (is_object($value)) {
                    throw new ValueFormatException('Can not convert object of class '.get_class($value).' to a NAME');
                }
                throw new ValueFormatException('Can not convert '.var_export($value, true).' to NAME');

            case PropertyType::PATH:
                switch ($srctype) {
                    case PropertyType::STRING:
                        // TODO: check if valid
                        return $value;
                    case PropertyType::NAME:
                    case PropertyType::PATH:
                        return $value;
                    case PropertyType::URI:
                        // TODO: check if valid, remove leading ./, decode
                        return $value;
                }
                if (is_object($value)) {
                    throw new ValueFormatException('Can not convert object of class '.get_class($value).' to a PATH');
                }
                throw new ValueFormatException('Can not convert '.var_export($value, true).' to PATH');

            case PropertyType::REFERENCE:
            case PropertyType::WEAKREFERENCE:
                switch ($srctype) {
                    case PropertyType::STRING:
                    case PropertyType::REFERENCE:
                    case PropertyType::WEAKREFERENCE:
                        if (empty($value)) {
                            //TODO check if string is valid uuid
                            throw new ValueFormatException('Value '.var_export($value, true).' is not a valid unique id');
                        }

                        return $value;
                }
                if (is_object($value)) {
                    throw new ValueFormatException('Can not convert object of class '.get_class($value).' to a unique id');
                }
                throw new ValueFormatException('Can not convert '.var_export($value, true).' to unique id');

            case PropertyType::URI:
                switch ($srctype) {
                    case PropertyType::STRING:
                        // TODO: check if valid
                        return $value;
                    case PropertyType::NAME:
                        return '../'.rawurlencode($value);
                    case PropertyType::PATH:
                        if (strlen($value) > 0
                            && '/' != $value[0]
                            && '.' != $value[0]
                        ) {
                            $value = './'.$value;
                        }

                        return str_replace('%2F', '/', rawurlencode($value));
                    case PropertyType::URI:
                        return $value;
                }
                if (is_object($value)) {
                    throw new ValueFormatException('Can not convert object of class '.get_class($value).' to a URI');
                }
                throw new ValueFormatException('Can not convert '.var_export($value, true).' to URI');

            case PropertyType::DECIMAL:
                switch ($srctype) {
                    case PropertyType::STRING:
                        // TODO: validate
                        return $value;
                    case PropertyType::LONG:
                    case PropertyType::DOUBLE:
                    case PropertyType::BOOLEAN:
                    case PropertyType::DECIMAL:
                        return (string) $value;
                    case PropertyType::DATE:
                        /** @var $value \DateTime */

                        return (string) $value->getTimestamp();
                }
                if (is_object($value)) {
                    throw new ValueFormatException('Can not convert object of class '.get_class($value).' to a DECIMAL');
                }
                throw new ValueFormatException('Can not convert '.var_export($value, true).' to a DECIMAL');

            default:
                throw new ValueFormatException("Unexpected target type $type in conversion");
        }
    }
}
