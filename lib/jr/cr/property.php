<?php
class jr_cr_property implements PHPCR_PropertyInterface {
    /**
     * Enter description here...
     *
     * @var jr_cr_session
     */
    protected $session = null;
    
    /**
     * Enter description here...
     *
     * @var jr_cr_node
     */
    protected $parentNode = null;
    protected $new = false;
    protected $modified = false;
    protected $path = null;
    protected $type = null;
    protected $JRprop = null;
    protected $name = null;
    protected $value = null;
    protected $values = null;
    
    /**
     * Enter description here...
     *
     * @param string $name
     * @param string $value
     * @param jr_cr_node $parentNode
     * @param int $type
     */
    public function __construct($parentNode, $jrprop) {
        $this->JRprop = $jrprop;
        $this->parentNode = $parentNode;
        $this->session = $parentNode->getSession();
    }
    
    /**
     *
     * @see Value::getBoolean()
     * @return bool
     * A boolean representation of the value of this {@link Property}.
     * @see PHPCR_Property::getBoolean()
     */
    public function getBoolean() {
        return $this->getValue()->getBoolean();
    }
    
    public function getNode() {
        return $this->parentNode;
    }
    
    /**
     *
     * @see Value, Value::getDate()
     * @return object
     * A date representation of the value of this {@link Property}.
     * @see PHPCR_Property::getDate()
     */
    public function getDate() {
        return $this->getValue()->getDate();
    }
    
    /**
     *
     * @see NodeType::getPropertyDefinitions()
     * @return object
     * A {@link PropertyDef} object
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Property::getDefinition()
     */
    public function getDefinition() {
        return    $this->JRprop->getDefinition();
        //TODO - Insert your code here
    }
    
    /**
     *
     * @see getFloat(), Value::getDouble()
     * @return float
     * @see PHPCR_Property::getDouble()
     */
    public function getDouble() {
        return $this->getValue()->getDouble();
    }
    
    /**
     *
     * @see Value, Value::getFloat(), getDouble()
     * @return float
     * A float representation of the value of this {@link Property}.
     * @see PHPCR_Property::getFloat()
     */
    public function getFloat() {
        return $this->getValue()->getFloat();
    }
    
    /**
     *
     * @see Value::getLong()
     * @return int
     * An integer representation of the value of this {@link Property}.
     * @see PHPCR_Property::getInt()
     */
    public function getInt() {
        return $this->getValue()->getInt();
    }
    
    /**
     *
     * @return int
     * @see PHPCR_Property::getLength()
     */
    public function getLength() {
        try {
            $length = $this->JRprop->getLength();
        } catch (JavaException $e) {
            $str = split("\n", $e->getMessage(), 2);
            if (false !== strpos($str[0], 'ValueFormatException')) {
                throw new PHPCR_ValueFormatException($e->getMessage());
            } else {
                throw new PHPCR_RepositoryException($e->getMessage());
            }
        }
        return $length;
    }
    
    /**
     *
     * @return array
     * @see PHPCR_Property::getLengths()
     */
    public function getLengths() {
        try {
            $lengths = $this->JRprop->getLengths();
        } catch (JavaException $e) {
            $str = split("\n", $e->getMessage(), 2);
            if (false !== strpos($str[0], 'ValueFormatException')) {
                throw new PHPCR_ValueFormatException($e->getMessage());
            } else {
                throw new PHPCR_RepositoryException($e->getMessage());
            }
        }
        return $lengths;
    }
    
    /**
     *
     * @see Value::getLong()
     * @return int
     * An integer representation of the value of this {@link Property}.
     * @see PHPCR_Property::getLong()
     */
    public function getLong() {
        return $this->getValue()->getLong();
    }
    
    /**
     *
     * @see Value
     * @return string
     * A string representation of the value of this {@link Property}.
     * @see PHPCR_Property::getString()
     */
    public function getString() {
        //TODO: this should actually return something like $jrproperty->getValue->getString();
        $cacheKey = md5("prop::getString::".$this->getPath());
        if (!($this->session->cache && $result = $this->session->cache->load($cacheKey))) {
            if ($this->getType() == PHPCR_PropertyType::BINARY) {
                
                /**
                 * the copyToFile() method is a patch for
                 *   jackrabbit-jcr-rmi/src/main/java/org/apache/jackrabbit/rmi/client/ClientProperty.java
                 *  which allows to put the content of a Binary Value to a file, which then can
                 *  be read by PHP. See also ClientProperty.java.patch
                 *
                 * If anyone has a fast and better way, without having to patch ClientProperty.java, tell me :)
                 *
                 * If copyToFile doesn't exist, use a muchmuch slower method
                 */
                try {
                    $filename = tempnam(sys_get_temp_dir(), "jrcr2");
                    $this->JRprop->copyToFile($filename);
                    $data = file_get_contents($filename);
                } catch (Exception $e) {
                    $in = $this->JRprop->getStream();
                    $data = "";
                    while (($len = $in->read()) != - 1) {
                        //$out->write($len);
                        if ($len < 0) {
                            $data .= chr($len + 256);
                        } else {
                            $data .= chr($len);
                        }
                    }
                    if ($this->session->cache) {
                        $this->session->cache->save($data,$cacheKey,array(md5($this->getParent()->getPath())));
                    }
                    return $data;
                    /* another way, to be benchmarked...

                        $out = new Java("java.io.FileOutputStream", $filename);
                    while (($len = $in->read())  != -1) {
                        $out->write($len);
                    }
                    $out->close();
                    */
                }
                if ($filename) {
                    unlink($filename);
                }
                $this->session->cache->save($data,$cacheKey,array(md5($this->getPath())));
                return $data;
            }
            $data = (string) $this->JRprop->getString();
            // $this->session->cache->save($data,$cacheKey,array(md5($this->getPath())));
            return $data;
        }
        return $result;
    }
    
    /**
     *
     * @return int
     * @throws {@link RepositoryException}
     * If an error occurs
     * @see PHPCR_Property::getType()
     */
    public function getType() {
        if (!$this->type) {
            $this->type =  $this->JRprop->getType();
        }
        return $this->type;
        //TODO - Insert your code here
    }
    
    /**
     *
     * @return object
     * @throws {@link ValueFormatException}
     * If the property is multi-valued.
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Property::getValue()
     */
    public function getValue() {
        if (null === $this->value)  {
            try {
                $value = $this->JRprop->getValue();
            } catch (JavaException $e) {
                $str = split("\n", $e->getMessage(), 2);
                if (false !== strpos($str[0], 'ValueFormatException')) {
                    throw new PHPCR_ValueFormatException($e->getMessage());
                } else {
                    throw new PHPCR_RepositoryException($e->getMessage());
                }
            }
            $this->value = new jr_cr_value($value);
        }
        return $this->value;
    }
    
    /**
     *
     * @return array
     * An array of {@link Value}s.
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Property::getValues()
     */
    public function getValues() {
        if (null === $this->values) {
            try {
                $values = $this->JRprop->getValues();
            } catch (JavaException $e) {
                $str = split("\n", $e->getMessage(), 2);
                if (false !== strpos($str[0], 'ValueFormatException')) {
                    throw new PHPCR_ValueFormatException($e->getMessage());
                } else {
                    throw new PHPCR_RepositoryException($e->getMessage());
                }
            }
            
            $this->values = array();
            foreach ($values as $value) {
                array_push($this->values, new jr_cr_value($value));
            }
        }
        return $this->values;
    }
    
    /**
     *
     * @param mixed
     *   The new value to set the {@link Property} to.
     * @throws {@link ValueFormatException}
     * If the type or format of the specified value is incompatible with the
     * type of this {@link Property}.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Property::setValue()
     */
    public function setValue($value) {
        $this->JRprop->setValue($value);
    }
    
    /**
     *
     * @param object
     * A {@link ItemVisitor} object
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Item::accept()
     */
    public function accept(PHPCR_ItemVisitorInterface $visitor) {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @param int
     * An integer, 0 &lt;= $degree &lt;= n where
     * n is the depth of $this {@link Item} along the
     * path returned by {@link getPath()}.
     * @return object
     * The ancestor of the specified absolute degree of $this
     * {@link Item} along the path returned by{@link getPath()}.
     * @throws {@link ItemNotFoundException}
     * If $degree &lt; 0 or $degree &gt; n
     * where n is the is the depth of $this {@link Item}
     * along the path returned by {@link getPath()}.
     * @throws {@link AccessDeniedException}
     * If the current {@link Ticket} does not have sufficient access rights to
     * complete the operation.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Item::getAncestor()
     */
    public function getAncestor($degree) {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @return int
     * The depth of this {@link Item} in the repository hierarchy.
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Item::getDepth()
     */
    public function getDepth() {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @return string
     * The (or a) name of this {@link Item} or an empty string if this
     * {@link Item} is the root {@link Node}.
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Item::getName()
     */
    public function getName() {
        if (null === $this->name) {
            $this->name = $this->JRprop->getName();
        }
        
        return $this->name;
    }
    
    /**
     *
     * @return object
     * The parent of this {@link Item} along the path returned by
     * {@link getPath()}.
     * @throws {@link ItemNotFoundException}
     * If there is no parent.  This only happens if $this
     * {@link Item} is the root node.
     * @throws {@link AccessDeniedException}
     * If the current {@link Ticket} does not have sufficient access rights to
     * complete the operation.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Item::getParent()
     */
    public function getParent() {
        return $this->parentNode;
    }
    
    /**
     *
     * @return string
     * The path (or one of the paths) of this {@link Item}.
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Item::getPath()
     */
    public function getPath() {
        if (!$this->path) {
            $this->path = $this->JRprop->getPath();
        }
        return $this->path;
    }
    
    /**
     *
     * @return object
     * A {@link Session} object
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Item::getSession()
     */
    public function getSession() {
        return $this->session;
    }
    
    /**
     *
     * @return boolean
     * @see PHPCR_Item::isModified()
     */
    public function isModified() {
        return $this->modified;
    }
    
    public function setModified($mod) {
        $this->modified = $mod;
    }
    
    /**
     *
     * @return boolean
     * @see PHPCR_Item::isNew()
     */
    public function isNew() {
        return $this->new;
    }
    
    public function setNew($new) {
        $this->new = $new;
    }
    
    /**
     *
     * @return bool
     * Returns TRUE if this {@link Item} is a {@link Node};
     * Returns FALSE if this {@link Item} is a {@link Property}.
     * @see PHPCR_Item::isNode()
     */
    public function isNode() {
        return false;
    }
    
    /**
     *
     * @param object
     * A {@link Item} object
     * @return boolean
     * @throws {@link RepositoryException}
     * If an error occurs.
     * @see PHPCR_Item::isSame()
     */
    public function isSame(PHPCR_ItemInterface $otherItem) {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @param boolean
     * @throws {@link InvalidItemStateException}
     * If this {@link Item} object represents a workspace item that has been
     * removed (either by this session or another).
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Item::refresh()
     */
    public function refresh($keepChanges) {
        //TODO - Insert your code here
    }
    
    /**
     *
     * @throws {@link VersionException}
     * If the parent node of this item is versionable and checked-in or is
     * non-versionable but its nearest versionable ancestor is checked-in
     * and this implementation performs this validation immediately instead
     * of waiting until {@link save()}.
     * @throws {@link LockException}
     * If a lock prevents the removal of this item and this implementation
     * performs this validation immediately instead of waiting until
     * {@link save()}.
     * @throws {@link ConstraintViolationException}
     * If removing the specified item would violate a node type or
     * implementation-specific constraint and this implementation performs
     * this validation immediately instead of waiting until {@link save()}.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Item::remove()
     */
    public function remove() {
        $this->JRprop->remove();
    }
    
    /**
     *
     * @throws {@link AccessDeniedException}
     * If any of the changes to be persisted would violate the access
     * privileges of the this {@link Session}. Also thrown if any of the
     * changes to be persisted would cause the removal of a node that is
     * currently referenced by a <i>REFERENCE</i> property that this
     * Session <i>does not</i> have read access to.
     * @throws {@link ItemExistsException}
     * If any of the changes to be persisted would be prevented by the
     * presence of an already existing     item in the workspace.
     * @throws {@link ConstraintViolationException}
     * If any of the changes to be persisted would violate a node type or
     * restriction. Additionally, a repository may use this exception to
     * enforce implementation- or configuration-dependent restrictions.
     * @throws {@link InvalidItemStateException}
     * If any of the changes to be persisted conflicts with a change already
     * persisted through another session and the implementation is such that
     * this conflict can only be detected at save-time and therefore was not
     * detected earlier, at change-time.
     * @throws {@link ReferentialIntegrityException}
     * If any of the changes to be persisted would cause the removal of a
     * node that is currently referenced by a <i>REFERENCE</i> property
     * that this {@link Session} has read access to.
     * @throws {@link VersionException}
     * If the {@link save()} would make a result in a change to persistent
     * storage that would violate the read-only status of a checked-in node.
     * @throws {@link LockException}
     * If the {@link save()} would result in a change to persistent storage
     * that would violate a lock.
     * @throws {@link NoSuchNodeTypeException}
     * If the {@link save()} would result in the addition of a node with an
     * unrecognized node type.
     * @throws {@link RepositoryException}
     * If another error occurs.
     * @see PHPCR_Item::save()
     */
    public function save() {
        $this->JRprop->save();
        $this->setModified(false);
        $this->setNew(false);
    }
    
    /**
     * Returns a Binary representation of the value of this property. A
     * shortcut for Property.getValue().getBinary(). See Value.
     *
     * @return PHPCR_BinaryInterface A Binary representation of the value of this property.
     * @throws PHPCR_ValueFormatException if the property is multi-valued.
     * @throws PHPCR_RepositoryException if another error occurs
     */
    public function getBinary() {
        //TODO: Insert Code
    }
    
    /**
     * Returns a BigDecimal representation of the value of this property. A
     * shortcut for Property.getValue().getDecimal(). See Value.
     *
     * @return float A float representation of the value of this property.
     * @throws PHPCR_ValueFormatException if conversion to a BigDecimal is not possible or if the property is multi-valued.
     * @throws PHPCR_RepositoryException if another error occurs
     */
    public function getDecimal() {
        //TODO: Insert Code
    }
    
    /**
     * If this property is of type PATH (or convertible to this type) this
     * method returns the Property to which this property refers.
     * If this property contains a relative path, it is interpreted relative
     * to the parent node of this property. Therefore, when resolving such a
     * relative path, the segment "." refers to the parent node itself, ".." to
     * the parent of the parent node and "foo" to a sibling property of this
     * property or this property itself.
     *
     * For example, if this property is located at /a/b/c and it has a value of
     * "../d" then this method will return the property at /a/d if such exists.
     *
     * @return PHPCR_PropertyInterface the referenced property
     * @throws PHPCR_ValueFormatException if this property cannot be converted to a PATH, if the property is multi-valued or if this property is a referring type but is currently part of the frozen state of a version in version storage.
     * @throws PHPCR_ItemNotFoundException If no property accessible by the current Session exists in this workspace at the specified path. Note that this applies even if a node exists at the specified location. To dereference to a target node, the method Property.getNode is used.
     * @throws PHPCR_RepositoryException if another error occurs
     */
    public function getProperty() {
        //TODO: Insert Code
    }
    
    /**
     * Returns TRUE if this property is multi-valued and FALSE if this property
     * is single-valued.
     *
     * @return boolean TRUE if this property is multi-valued; FALSE otherwise.
     * @throws PHPCR_RepositoryException if an error occurs.
     */
    public function isMultiple() {
        //TODO: Insert Code
    }
}
