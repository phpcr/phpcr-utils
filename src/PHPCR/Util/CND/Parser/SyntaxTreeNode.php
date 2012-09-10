<?php

namespace PHPCR\Util\CND\Parser;

class SyntaxTreeNode
{
    /**
     * The type of the node
     * @var string
     */
    protected $type;

    /**
     * The properties of the node
     * @var array
     */
    protected $properties;

    /**
     * The child nodes
     * @var array
     */
    protected $children;

    /**
     * @param string $type
     */
    public function __construct($type, $properties = array())
    {
        $this->type = $type;
        $this->properties = $properties;
        $this->children = array();
    }

    /**
     * @param string $name
     * @param string $value
     * @return void
     */
    public function setProperty($name, $value)
    {
        $this->properties[$name] = $value;
    }

    /**
     * @param SyntaxTreeNode $child
     * @return void
     */
    public function addChild(SyntaxTreeNode $child)
    {
        $this->children[] = $child;
    }

    /**
     * @param SyntaxTreeVisitorInterface $visitor
     * @return void
     */
    public function accept(SyntaxTreeVisitorInterface $visitor)
    {
        $visitor->visit($this);
        foreach($this->children as $child) {
            $child->accept($visitor);
        }
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasProperty($name)
    {
        return array_key_exists($name, $this->properties);
    }

    /**
     * @throws \InvalidArgumentException
     * @param string $name
     * @return mixed
     */
    public function getProperty($name)
    {
        if (!array_key_exists($name, $this->properties)) {
            throw new \InvalidArgumentException("No property '$name' found.");
        }

        return $this->properties[$name];
    }

    /**
     * Get all the children
     * @return array
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * Return all the children of a given type
     * @param $type
     * @return array
     */
    public function getChildrenByType($type)
    {
        $children = array();
        foreach ($this->children as $child) {
            if ($child->getType() === $type) {
                $children[] = $child;
            }
        }
        return $children;
    }

    /**
     * Return the first child with the given type or false if none
     * @param $type
     * @return array
     */
    public function getFirstChildByType($type)
    {
        foreach ($this->children as $child) {
            if ($child->getType() === $type) {
                return $child;
            }
        }
        return false;
    }

    /**
     * Return true if the node has child nodes and false otherwise
     * @return bool
     */
    public function hasChildren()
    {
        return !empty($this->children);
    }

    /**
     * Return true if the node has at least one child of the given type
     * @param string $type
     * @return bool
     */
    public function hasChild($type)
    {
        foreach ($this->children as $child) {
            if ($child->getType() === $type) {
                return true;
            }
        }
        return false;
    }

    /**
     * Return a dumped version of the tree
     *
     * @param bool $compact If true, less spacing is added to the dump
     * @param int $level Internal parameter used to indicate the current indentation level
     * @return string
     */
    public function dump($compact = false, $level = 0)
    {
        $dump = '';
        $indent = str_repeat('  ', $level);
        $dump .= sprintf("%sNODE[%s]%s\n", $indent, $this->type, !empty($this->properties) || !empty($this->children) ? ':' : '');

        foreach ($this->properties as $key => $value) {
            if (is_array($value)) {
                $value = sprintf('(%s)', join(', ', $value));
            }
            $dump .= sprintf("%s  - %s = %s\n", $indent, $key, $value);
        }

        if (!$compact && $this->hasChildren()) {
            $dump .= "\n";
        }

        foreach ($this->children as $child) {
            $dump .= $child->dump($compact, $level + 1);
        }

        if (!$compact && !$this->hasChildren()) {
            $dump .= "\n";
        }

        return $dump;
    }
}
