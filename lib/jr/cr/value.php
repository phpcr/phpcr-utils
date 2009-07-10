<?php
class jr_cr_value implements PHPCR_ValueInterface {
    protected $JRvalue;
    protected $isStream = null;
    
    public function __construct($JRvalue) {
        $this->JRvalue = $JRvalue;
    }
    
    /**
     * Returns a string representation of this value.
     *
     * @return string
     *
     * @throws {@link ValueFormatException}
     *    If conversion to a string is not possible.
     * @throws {@link RepositoryException}
     *    If another error occurs.
     */
    public function getString() {
        try {
            return $this->JRvalue->getString();
        } catch(JavaException $e) {
            $this->throwExceptionFromJava($e);
        }
    }
    
    /**
     * Returns a number of the value. Which format can be given as param.
     */
    public function getNumber($float = false) {
        try {
            if (true === $float) {
                $num = $this->JRvalue->getDouble();
            } else {
                $num = $this->JRvalue->getLong();
            }
        } catch (JavaException $e) {
            $this->throwExceptionFromJava($e);
        }
        
        if (true === $float) {
            return (float) $num;
        } else {
            return (int)  $num;
        }
    }
    
    /**
     * Returns the int representation of this value.
     *
     * This method should always return exactly what {@link getInt()} does.
     * It has been left as a requirement to satisfy JCR compliance.
     *
     * @return int
     * @see getInt()
     */
    public function getLong() {
        return $this->getInt();
    }
    
    /**
     * Returns the int representation of this value.
     *
     * @return int
     *
     * @throws {@link ValueFormatException}
     *    If conversion to a int is not possible.
     * @throws {@link RepositoryException}
     *    If another error occurs.
     */
    public function getInt() {
        return $this->getNumber();
    }
    
    /**
     * Returns the float/double representation of this value.
     *
     * This method should always return exactly what {@link getFloat()} does.
     * It has been left as a requirement to satisfy JCR compliance.
     *
     * @see getFloat()
     * @return float
     */
    public function getDouble() {
        return $this->getFloat();
    }
    
    /**
     * Returns the float/double representation of this value.
     *
     * This method should always be an alias of {@link getFloat()}.
     *
     * @see getFloat()
     * @return float
     *
     * @throws {@link ValueFormatException}
     *    If conversion to a float is not possible.
     * @throws {@link RepositoryException}
     *    If another error occurs.
     */
    public function getFloat() {
        return $this->getNumber(true);
    }
    
    /**
     * Returns the timestamp string of this value.
     *
     * <b>PHP Note</b>: PHP does not have a default Calendar object.  This
     * method has been adjusted to return a string representing a valid
     * timestamp.
     *
     * Future version of PHPCR may implement a simple date/time object to
     * handle returning a mock of Java's Calendar object.
     *
     * Given the fluid nature of this method, it is advisable to throw a
     * {@link ValidFormatException} on all {@link Value}s except those which
     * must be returned as dates until a definitive return value is determined.
     *
     * @return string
     *
     * @throws {@link ValueFormatException}
     *    If conversion to a timestamp/date is not possible.
     * @throws {@link RepositoryException}
     *    If another error occurs.
     */
    public function getDate() {
        try {
            $date = $this->JRvalue->getDate();
            $date = date_create($date->getTime()->toString());
        } catch (JavaException $e) {
            $this->throwExceptionFromJava($e);
        }
        
        if (! $date instanceOf DateTime) {
            throw new PHPCR_ValueFormatException('Could not get Date');
        }
        
        return $date;
    }
    
    /**
     * Returns the boolean representation of this value.
     *
     * @return bool
     *
     * @throws {@link ValueFormatException}
     *    If conversion to a boolean is not possible.
     * @throws {@link RepositoryException}
     *    If another error occurs.
     */
    public function getBoolean() {
        try {
            $bool = $this->JRvalue->getBoolean();
        } catch (JavaException $e) {
            $this->throwExceptionFromJava($e);
        }
        return $bool;
    }
    
    /**
     * Returns the type of this Value.
     * One of:
     * <ul>
     *    <li>{@link PropertyType::STRING}</li>
     *    <li>{@link PropertyType::DATE}</li>
     *    <li>{@link PropertyType::BINARY}</li>
     *    <li>{@link PropertyType::DOUBLE}</li>
     *    <li>{@link PropertyType::LONG}</li>
     *    <li>{@link PropertyType::BOOLEAN}</li>
     *    <li>{@link PropertyType::NAME}</li>
     *    <li>{@link PropertyType::PATH}</li>
     *    <li>{@link PropertyType::REFERENCE}</li>
     * </ul>
     *
     * The type returned is that which was set at property creation.
     *
     * @see PropertyType
     * @return int
     */
    public function getType() {
        //TODO: Insert code
    }
    
    protected function throwExceptionFromJava($e) {
        $str = split("\n", $e->getMessage(), 2);
        if (false !== strpos($str[0], 'ValueFormatException')) {
            throw new PHPCR_ValueFormatException($e->getMessage());
        } else {
            throw new PHPCR_RepositoryException($e->getMessage());
        }
    }
    
    /**
     * Returns a Binary representation of this value. The Binary object in turn provides
     * methods to access the binary data itself. Uses the standard conversion to binary
     * (see JCR specification).
     *
     * @return PHPCR_BinaryInterface A Binary representation of this value.
     * @throws PHPCR_RepositoryException if another error occurs.
     */
    public function getBinary() {
        //TODO: Insert Code
    }
    
    /**
     * Returns a BigDecimal representation of this value.
     *
     * @return string A double representation of the value of this property.
     * @throws PHPCR_ValueFormatException if conversion is not possible.
     * @throws PHPCR_RepositoryException if another error occurs.
     */
    public function getDecimal() {
        //TODO: Insert Code
    }
}
