<?php

declare(strict_types=1);

namespace PHPCR\Util;

use DateTime;
use Exception;
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
     * - if the $value is an empty array, the type is arbitrarily set to STRING
     * - if the $value is a non-empty array, the type of its first element is
     *   chosen.
     *
     * Note that string is converted to date exactly if it matches the jcr
     * formatting spec for dates (sYYYY-MM-DDThh:mm:ss.sssTZD) according to
     * http://www.day.com/specs/jcr/2.0/3_Repository_Model.html#3.6.4.3%20From%20DATE%20To
     *
     * @param mixed $value The variable we need to know the type of
     * @param bool  $weak  when a Node is given as $value this can be given
     *                     as true to create a WEAKREFERENCE
     *
     * @return int One of the type constants
     *
     * @throws ValueFormatException if the type can not be determined
     */
    public function determineType(mixed $value, bool $weak = false): int
    {
        if (is_array($value)) {
            if (0 === count($value)) {
                // there is no value to determine the type on. we arbitrarily
                // chose string, which is what jackrabbit does as well.
                return PropertyType::STRING;
            }
            $value = reset($value);
        }

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
     * <TABLE>
     * <TR><TD><BR></TD><TD>STRING (1)</TD><TD>BINARY (2)</TD><TD>LONG (3)</TD><TD>DOUBLE (4)</TD><TD>DATE (5)</TD><TD>BOOLEAN (6)</TD><TD>NAME(7)</TD><TD>PATH (8)</TD><TD>REFERENCE (9/10)</TD><TD>URI (11)</TD><TD>DECIMAL (12)</TD></TR>
     * <TR><TD>STRING</TD><TD>x</TD><TD>Utf-8 encoded</TD><TD>cast to int</TD><TD>cast to float</TD><TD>SYYYY-MM-DDThh:Mm:ss.sssTZD</TD><TD><I>'' is false, else true</I></TD><TD>if valid name, name</TD><TD>if valid path, as name</TD><TD>check valid uuid</TD><TD>RFC 3986</TD><TD>string</TD></TR>
     * <TR><TD>BINARY</TD><TD>Utf-8</TD><TD>x</TD><TD COLSPAN="9" BGCOLOR="#E6E6E6">Converted to string and then interpreted as above</TD></TR>
     * <TR><TD>LONG</TD><TD>cast to string</TD><TD>String, then Utf-8</TD><TD>x</TD><TD>cast to float</TD><TD>Unix Time</TD><TD><I>0 false else true</I></TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>cast to string</TD></TR>
     * <TR><TD>DOUBLE</TD><TD>cast to string</TD><TD>String, then Utf-8</TD><TD>cast to int</TD><TD>x</TD><TD>Unix Time</TD><TD><I>0.0 is false, else true</I></TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>cast to string</TD></TR>
     * <TR><TD>DATE</TD><TD>SYYYY-MM-DDThh:<BR>Mm:ss.sssTZD</TD><TD>String, then Utf-8</TD><TD>Unix timestamp</TD><TD>Unix timestamp</TD><TD>x</TD>
     * <TD><I>true</I></TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>Unix timestamp</TD></TR>
     * <TR><TD>BOOLEAN</TD><TD>cast to string</TD><TD>String, then Utf-8</TD><TD>0/1</TD><TD>0.0/1.0</TD><TD>ValueFormatException</TD><TD>x</TD><TD>'0'/'1'</TD>
     * <TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD></TR>
     * <TR><TD>NAME</TD><TD>Qualified form</TD><TD>String, then Utf-8</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>x</TD><TD>noop (relative path)</TD><TD>ValueFormatException</TD><TD>„./“ and qualified name. % encode illegal characters</TD><TD>ValueFormatException</TD></TR>
     * <TR><TD>PATH</TD><TD>Standard form</TD><TD>String, then Utf-8</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>if relative path lenght 1 noop / otherwise ValueFormatException</TD><TD>x</TD><TD>ValueFormatException</TD><TD>„./“ if not starting with /. % encode illegal characters</TD><TD>ValueFormatException</TD></TR>
     * <TR><TD>REFERENCE</TD><TD>noop</TD><TD>String, then Utf-8</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>x</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD></TR>
     * <TR><TD>URI</TD><TD>noop</TD><TD>String, then Utf-8</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD>
     * <TD>ValueFormatException</TD><TD>single name: decode %, remove ./  else ValueFormatException</TD><TD>Decode %, remove leading ./ . if not star w. name, / or ./ then ValueFormatException</TD><TD>ValueFormatException</TD><TD>x</TD><TD>ValueFormatException</TD></TR>
     * <TR><TD>DECIMAL</TD><TD>noop</TD><TD>Utf-8 encoded</TD><TD>cast to int</TD><TD>cast to float</TD><TD>Unix Time</TD><TD><I>0 false else true</I></TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>ValueFormatException</TD><TD>x</TD></TR>
     * </TABLE>
     *
     * @param mixed $value   The value or value array to check and convert
     * @param int   $type    Target type to convert into. One of the type constants in PropertyType
     * @param int   $srcType Source type to convert from, if not specified this is automatically determined, which will miss the string based types that are not strings (DECIMAL, NAME, PATH, URI)
     *
     * @return mixed the value casted into the proper format (throws an exception if conversion is not possible)
     *
     * @throws RepositoryException       if the specified Node is not referenceable, the current Session is no longer active, or another error occurs
     * @throws \InvalidArgumentException if the specified DateTime value cannot be expressed in the ISO 8601-based format defined in the JCR 2.0 specification and the implementation does not support dates incompatible with that format.
     * @throws ValueFormatException      is thrown if the specified value cannot be converted to the specified type
     *
     * @see http://www.day.com/specs/jcr/2.0/3_Repository_Model.html#3.6.4%20Property%20Type%20Conversion
     */
    public function convertType(mixed $value, int $type, int $srcType = PropertyType::UNDEFINED): mixed
    {
        if (is_array($value)) {
            $ret = [];
            foreach ($value as $v) {
                $ret[] = self::convertType($v, $type, $srcType);
            }

            return $ret;
        }

        if (PropertyType::UNDEFINED === $srcType) {
            $srcType = $this->determineType($value);
        }

        if ($value instanceof PropertyInterface) {
            $value = $value->getValue();
        }

        // except on noop, stream needs to be read into string first
        if (PropertyType::BINARY === $srcType && PropertyType::BINARY !== $type && is_resource($value)) {
            $t = stream_get_contents($value);
            rewind($value);
            $value = $t;
            $srcType = PropertyType::STRING;
        } elseif ((PropertyType::REFERENCE === $srcType || PropertyType::WEAKREFERENCE === $srcType)
            && $value instanceof NodeInterface
        ) {
            if (!$value->isNodeType('mix:referenceable')) {
                throw new ValueFormatException('Node '.$value->getPath().' is not referenceable');
            }
            $value = $value->getIdentifier();
        }

        switch ($type) {
            case PropertyType::STRING:
                switch ($srcType) {
                    case PropertyType::DATE:
                        if (!$value instanceof \DateTime) {
                            throw new RepositoryException('Cannot convert a date that is not a \DateTime instance to string');
                        }

                        /* @var $value DateTime */
                        // Milliseconds formatting is not possible in PHP so we
                        // construct it by cutting microseconds to 3 positions.
                        // This might not be as accurate as "real" rounded milliseconds.
                        return $value->format('Y-m-d\TH:i:s.').
                            substr($value->format('u'), 0, 3).
                            $value->format('P');
                    case PropertyType::NAME:
                    case PropertyType::PATH:
                        // TODO: The name/path is converted to qualified form according to the current local namespace mapping (see §3.2.5.2 Qualified Form).
                        return $value;
                    default:
                        if (is_object($value)) {
                            throw new ValueFormatException('Cannot convert object of class "'.$value::class.'" to STRING');
                        }
                        if (is_resource($value)) {
                            throw new ValueFormatException('Inconsistency: Non-binary property should not have resource stream value');
                        }

                        // TODO: how can we provide ValueFormatException on failure? invalid casting leads to 'catchable fatal error' instead of exception
                        return (string) $value;
                }

                // no break
            case PropertyType::BINARY:
                if (is_resource($value)) {
                    return $value;
                }
                if (!is_string($value)) {
                    $value = $this->convertType($value, PropertyType::STRING, $srcType);
                }
                $f = fopen('php://memory', 'rwb+');
                fwrite($f, $value);
                rewind($f);

                return $f;

            case PropertyType::LONG:
                switch ($srcType) {
                    case PropertyType::STRING:
                    case PropertyType::LONG:
                    case PropertyType::DOUBLE:
                    case PropertyType::BOOLEAN:
                    case PropertyType::DECIMAL:
                        return (int) $value;
                    case PropertyType::DATE:
                        if (!$value instanceof \DateTime) {
                            throw new RepositoryException('something weird');
                        }
                        /* @var $value DateTime */

                        return $value->getTimestamp();
                }
                if (is_object($value)) {
                    throw new ValueFormatException('Cannot convert object of class "'.$value::class.'" to a LONG');
                }

                throw new ValueFormatException('Cannot convert "'.var_export($value, true).'" to a LONG');
            case PropertyType::DOUBLE:
                switch ($srcType) {
                    case PropertyType::STRING:
                    case PropertyType::LONG:
                    case PropertyType::DOUBLE:
                    case PropertyType::BOOLEAN:
                    case PropertyType::DECIMAL:
                        return (float) $value;
                    case PropertyType::DATE:
                        if (!$value instanceof \DateTime) {
                            throw new RepositoryException('something weird');
                        }

                        /* @var $value DateTime */

                        return (float) $value->getTimestamp();
                }
                if (is_object($value)) {
                    throw new ValueFormatException('Cannot convert object of class "'.$value::class.'" to a DOUBLE');
                }

                throw new ValueFormatException('Cannot convert "'.var_export($value, true).'" to a DOUBLE');
            case PropertyType::DATE:
                switch ($srcType) {
                    case PropertyType::STRING:
                    case PropertyType::DATE:
                        if ($value instanceof \DateTime) {
                            return $value;
                        }

                        try {
                            return new \DateTime($value);
                        } catch (\Exception $e) {
                            throw new ValueFormatException("String '$value' is not a valid date", 0, $e);
                        }
                    case PropertyType::LONG:
                        return (new \DateTime())
                            ->setTimestamp($value)
                        ;
                    case PropertyType::DOUBLE:
                        return (new \DateTime())
                            ->setTimestamp((int) round($value))
                        ;
                    case PropertyType::DECIMAL:
                        if (function_exists('bccomp')
                            && 1 === \bccomp($value, (string) PHP_INT_MAX)
                        ) {
                            throw new ValueFormatException('Decimal number is too large for integer');
                        }

                        return (new \DateTime())
                            ->setTimestamp((int) round((float) $value))
                        ;
                }
                if (is_object($value)) {
                    throw new ValueFormatException('Cannot convert object of class "'.$value::class.'" to a DATE');
                }

                throw new ValueFormatException('Cannot convert "'.var_export($value, true).'" to DATE');
            case PropertyType::BOOLEAN:
                switch ($srcType) {
                    case PropertyType::STRING:
                    case PropertyType::LONG:
                    case PropertyType::DOUBLE:
                    case PropertyType::BOOLEAN:
                        return (bool) $value;
                    case PropertyType::DATE:
                        /* @var $value DateTime */

                        return (bool) $value->getTimestamp();
                    case PropertyType::DECIMAL:
                        return (bool) ((float) $value); // '0' is false too
                }
                if (is_object($value)) {
                    throw new ValueFormatException('Cannot convert object of class "'.$value::class.'" to a BOOLEAN');
                }

                throw new ValueFormatException('Cannot convert "'.var_export($value, true).'" to a BOOLEAN');
            case PropertyType::NAME:
                switch ($srcType) {
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
                    throw new ValueFormatException('Cannot convert object of class "'.$value::class.'" to a NAME');
                }

                throw new ValueFormatException('Cannot convert "'.var_export($value, true).'" to NAME');
            case PropertyType::PATH:
                switch ($srcType) {
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
                    throw new ValueFormatException('Cannot convert object of class "'.$value::class.'" to a PATH');
                }

                throw new ValueFormatException('Cannot convert "'.var_export($value, true).'" to PATH');
            case PropertyType::REFERENCE:
            case PropertyType::WEAKREFERENCE:
                switch ($srcType) {
                    case PropertyType::STRING:
                    case PropertyType::REFERENCE:
                    case PropertyType::WEAKREFERENCE:
                        if (empty($value)) {
                            // TODO check if string is valid uuid
                            throw new ValueFormatException('Value "'.var_export($value, true).'" is not a valid unique id');
                        }

                        return $value;
                }
                if (is_object($value)) {
                    throw new ValueFormatException('Cannot convert object of class "'.$value::class.'" to a unique id');
                }

                throw new ValueFormatException('Cannot convert "'.var_export($value, true).'" to unique id');
            case PropertyType::URI:
                switch ($srcType) {
                    case PropertyType::STRING:
                        // TODO: check if valid
                        return $value;
                    case PropertyType::NAME:
                        return '../'.rawurlencode($value);
                    case PropertyType::PATH:
                        if ('' !== $value
                            && '/' !== $value[0]
                            && '.' !== $value[0]
                        ) {
                            $value = './'.$value;
                        }

                        return str_replace('%2F', '/', rawurlencode($value));
                    case PropertyType::URI:
                        return $value;
                }
                if (is_object($value)) {
                    throw new ValueFormatException('Cannot convert object of class "'.$value::class.'" to a URI');
                }

                throw new ValueFormatException('Cannot convert "'.var_export($value, true).'" to URI');
            case PropertyType::DECIMAL:
                switch ($srcType) {
                    case PropertyType::STRING:
                        // TODO: validate
                        return $value;
                    case PropertyType::LONG:
                    case PropertyType::DOUBLE:
                    case PropertyType::BOOLEAN:
                    case PropertyType::DECIMAL:
                        return (string) $value;
                    case PropertyType::DATE:
                        /* @var $value DateTime */

                        return (string) $value->getTimestamp();
                }
                if (is_object($value)) {
                    throw new ValueFormatException('Cannot convert object of class "'.$value::class.'" to a DECIMAL');
                }

                throw new ValueFormatException('Cannot convert "'.var_export($value, true).'" to a DECIMAL');
            default:
                throw new ValueFormatException("Unexpected target type '$type' in conversion");
        }
    }
}
