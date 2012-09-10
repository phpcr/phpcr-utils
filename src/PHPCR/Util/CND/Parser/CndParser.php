<?php

namespace PHPCR\Util\CND\Parser;

use PHPCR\Util\CND\Scanner\GenericToken as Token,
    PHPCR\Util\CND\Scanner\TokenQueue,
    PHPCR\Util\CND\Exception\ParserException;

/**
 * Parser for JCR-2.0 CND files.
 *
 * @see http://www.day.com/specs/jcr/2.0/25_Appendix.html#25.2.3 CND Grammar
 * @see http://jackrabbit.apache.org/node-type-notation.html
 */
class CndParser extends AbstractParser
{
    /**
     * Parse a TokenQueue containing CND statements. This parser does not expect any whitespaces, new lines
     * or comments in the queue. Please use the CndScanner to be sure to generate a valid TokenQueue.
     *
     * This function returns an array of node type definition in array representation.
     * It does not actually return and array of PHPCR\NodeType !
     *
     * @return SyntaxTreeNode
     */
    public function parse()
    {
        $root = new SyntaxTreeNode('root');
        $nsMapping = new SyntaxTreeNode('nsMappings');
        $nodeTypes = new SyntaxTreeNode('nodeTypes');
        $root->addChild($nsMapping);
        $root->addChild($nodeTypes);

        while (!$this->tokenQueue->isEof()) {

            $this->debugSection('PARSER CYCLE');

            while ($this->checkToken(Token::TK_SYMBOL, '<')) {
                $nsMapping->addChild($this->parseNamespaceMapping());
            }

            if (!$this->tokenQueue->isEof()) {
                $nodeTypes->addChild($this->parseNodeTypeDef());
            }

        }

        return $root;
    }

    /**
     * A namespace declaration consists of prefix/URI pair. The prefix must be
     * a valid JCR namespace prefix, which is the same as a valid XML namespace
     * prefix. The URI can in fact be any string. Just as in XML, it need not
     * actually be a URI, though adhering to that convention is recommended.
     *
     * NamespaceMapping ::= '<' Prefix '=' Uri '>'
     * Prefix ::= String
     * Uri ::= String
     *
     * @return SyntaxTreeNode
     */
    protected function parseNamespaceMapping()
    {
        $this->debug('parseNamespaceMapping');

        $this->expectToken(Token::TK_SYMBOL, '<');
        $prefix = $this->parseCndString();
        $this->expectToken(Token::TK_SYMBOL, '=');
        $uri = substr($this->expectToken(Token::TK_STRING)->getData(), 1, -1);
        $this->expectToken(Token::TK_SYMBOL, '>');

        $this->debugRes("nsmapping: $prefix => $uri");

        return new SyntaxTreeNode('nsMapping', array('prefix' => $prefix, 'uri' => $uri));
    }

    /**
     * A node type definition consists of a node type name followed by an optional
     * supertypes block, an optional node type attributes block and zero or more
     * blocks, each of which is either a property or child node definition.
     * 
     *      NodeTypeDef ::= NodeTypeName [Supertypes]
     *          [NodeTypeAttribute {NodeTypeAttribute}]
     *          {PropertyDef | ChildNodeDef}
     *
     * @return SyntaxTreeNode
     */
    protected function parseNodeTypeDef()
    {
        $this->debug('parseNodeTypeDef');

        $node = new SyntaxTreeNode('nodeTypeDef');
        $node->addChild($this->parseNodeTypeName());

        if ($this->checkToken(Token::TK_SYMBOL, '>')) {
            $node->addChild($this->parseSupertypes());
        }

        if ($attrNode = $this->parseNodeTypeAttribues()){
            $node->addChild($attrNode);
        }

        if ($children = $this->parseChildDefs()) {
            foreach($children as $child) {
                $node->addChild($child);
            }
        }

        return $node;
    }

    /**
     * The node type name is delimited by square brackets and must be a valid JCR name.
     * 
     *      NodeTypeName ::= '[' String ']'
     * 
     * @return SyntaxTreeNode
     */
    protected function parseNodeTypeName()
    {
        $this->debug('parseNodeTypeName');

        $this->expectToken(Token::TK_SYMBOL, '[');
        $name = $this->parseCndString();
        $this->expectToken(Token::TK_SYMBOL, ']');

        $this->debugRes("nodeTypeName: $name");

        return new SyntaxTreeNode('nodeTypeName', array('value' => $name));
    }

    /**
     * The list of supertypes is prefixed by a '>'. If the node type is not a
     * mixin then it implicitly has nt:base as a supertype even if neither
     * nt:base nor a subtype of nt:base appears in the list or if this element
     * is absent. A question mark indicates that the supertypes list is a variant.
     *
     *      Supertypes ::= '>' (StringList | '?')
     * 
     * @return SyntaxTreeNode
     */
    protected function parseSupertypes()
    {
        $this->debug('parseSupertypes');

        $this->expectToken(Token::TK_SYMBOL, '>');

        $supertypes = new SyntaxTreeNode('supertypes');

        if ($this->checkAndExpectToken(Token::TK_SYMBOL, '?')) {
            $supertypes->setProperty('value', '?');
            $display = '?';
        } else {
            $list = $this->parseCndStringList();
            $supertypes->setProperty('value', $list);
            $display = join(', ', $list);
        }

        $this->debugRes(sprintf('supertypes: (%s)', $display));

        return $supertypes;
    }

    /**
     * The node type attributes are indicated by the presence or absence of keywords.
     *
     * If 'orderable' is present without a '?' then orderable child nodes is supported.
     * If 'orderable' is present with a '?' then orderable child nodes is a variant.
     * If 'orderable' is absent then orderable child nodes * is not supported.
     *
     * If 'mixin' is present without a '?' then the node type is a mixin.
     * If 'mixin' is present with a '?' then the mixin status is a variant.
     * If 'mixin' is absent then the node type is primary.
     *
     * If 'abstract' is present without a '?' then the node type is abstract.
     * If 'abstract' is present with a '?' then the abstract status is a variant.
     * If 'abstract' is absent then the node type is concrete.
     *
     * If 'query' is present then the node type is queryable.
     * If 'noquery' is present then the node type is not queryable.
     * If neither query nor noquery are present then the queryable setting of the
     * node type is a variant.
     *
     * If 'primaryitem' is present without a '?' then the string following it is
     * the name of the primary item of the node type.
     * If 'primaryitem' is present with a '?' then the primary item is a variant.
     * If 'primaryitem' is absent then the node type has no primary item.
     *
     *      NodeTypeAttribute ::= Orderable | Mixin | Abstract | Query | PrimaryItem
     *      Orderable ::= ('orderable' | 'ord' | 'o') ['?']
     *      Mixin ::= ('mixin' | 'mix' | 'm') ['?']
     *      Abstract ::= ('abstract' | 'abs' | 'a') ['?']
     *      Query ::= ('noquery' | 'nq') | ('query' | 'q' )
     *      PrimaryItem ::= ('primaryitem'| '!')(String | '?')
     *
     * @return SyntaxTreeNode
     */
    protected function parseNodeTypeAttribues()
    {
        $this->debug('parseNodeTypeAttributes');
        return $this->parseAttributes('nodeTypeAttributes', $this->getNodeTypeAttributes());
    }

    /**
     * Parse both the children propery and nodes definitions
     *
     *      {PropertyDef | ChildNodeDef}
     *
     * @return SyntaxTreeNode
     */
    protected function parseChildDefs()
    {
        $this->debug('parseChildDefs');

        $propDefs = new SyntaxTreeNode('propertyDefs');
        $childNodeDef = new SyntaxTreeNode('childNodeDefs');

        while (true) {

            if ($this->checkToken(Token::TK_SYMBOL, '-')) {
                $propDefs->addChild($this->parsePropDef());
            } elseif ($this->checkToken(Token::TK_SYMBOL, '+')) {
                $childNodeDef->addChild($this->parseChildNodeDef());
            } else {
                break;
            }
        }

        // Only return the nodes that actually have children
        $children = array();

        if ($propDefs->hasChildren()) {
            $children[] = $propDefs;
        }
        if ($childNodeDef->hasChildren()) {
            $children[] = $childNodeDef;
        }

        return $children;
    }

    /**
     * A property definition consists of a property name element followed by
     * optional property type, default values, property attributes and value
     * constraints elements.
     *
     * The property name, or '*' to indicate a residual property definition,
     * is prefixed with a '-'.
     *
     *      PropertyDef ::= PropertyName [PropertyType] [DefaultValues]
     *          [PropertyAttribute {PropertyAttribute}]
     *          [ValueConstraints]
     *      PropertyName ::= '-' String
     * 
     * @return SyntaxTreeNode
     */
    protected function parsePropDef()
    {
        $this->debug('parsePropDef');

        $node = new SyntaxTreeNode('propertyDef');

        // Parse the property name
        $this->expectToken(Token::TK_SYMBOL, '-');
        if ($this->checkAndExpectToken(Token::TK_SYMBOL, '*')) {
            $name = '*';
        } else {
            $name = $this->parseCndString();
        }
        $node->addChild(new SyntaxTreeNode('propertyName', array('value' => $name)));

        // Parse the property type
        if ($this->checkToken(Token::TK_SYMBOL, '(')) {
            $node->addChild($this->parsePropertyType());
        }

        // Parse default value
        if ($this->checkToken(Token::TK_SYMBOL, '=')) {
            $node->addChild($this->parseDefaultValue());
        }

        if ($attrNode = $this->parsePropertyAttributes()) {
            $node->addChild($attrNode);
        }
        // Check if there is a constraint (and not another namespace def)
        // Next token is '<' and two token later it's not '=', i.e. not '<ident='
        $next1 = $this->tokenQueue->peek();
        $next2 = $this->tokenQueue->peek(2);
        if ($next1 && $next1->getData() === '<' && (!$next2 || $next2->getData() !== '=')) {
            $node->addChild($this->parseValueConstraints());
        }

        $this->debugRes('propertyName: ' . $name);
        
        return $node;
    }

    /**
     * The property type is delimited by parentheses ('*' is a synonym for UNDEFINED).
     * If this element is absent, STRING is assumed. A '?' indicates that this
     * attribute is a variant.
     *
     *      PropertyType ::= '(' ('STRING' | 'BINARY' | 'LONG' | 'DOUBLE' |
     *          'BOOLEAN' | 'DATE' | 'NAME' | 'PATH' |
     *          'REFERENCE' | 'WEAKREFERENCE' |
     *          'DECIMAL' | 'URI' | 'UNDEFINED' | '*' |
     *          '?') ')'
     *
     * @return SyntaxTreeNode
     */
    protected function parsePropertyType()
    {
        $this->debug('parsePropertyType');

        // TODO: can the property be lowercase or camelcase as in old spec?
        $types = array("STRING", "BINARY", "LONG", "DOUBLE", "BOOLEAN",  "DATE", "NAME", "PATH",
                       "REFERENCE", "WEAKREFERENCE", "DECIMAL", "URI", "UNDEFINED", "*", "?");

        $this->expectToken(Token::TK_SYMBOL, '(');

        $data = $this->tokenQueue->get()->getData();
        if (!in_array($data, $types)) {
            throw new ParserException($this->tokenQueue, sprintf("Invalid property type: %s", $data));
        }

        $this->expectToken(Token::TK_SYMBOL, ')');

        $this->debugRes('propertyType: ' . $data);

        return new SyntaxTreeNode('propertyType', array('value' => $data));
    }

    /**
     * The default values, if any, are listed after a '='. The attribute is a
     * list in order to accommodate multi-value properties. The absence of this
     * element indicates that there is no static default value reportable. A '?'
     * indicates that this attribute is a variant
     *
     *      DefaultValues ::= '=' (StringList | '?')
     *
     * @return SyntaxTreeNode
     */
    protected function parseDefaultValue()
    {
        $this->debug('parseDefaultValues');

        // TODO: parse ?
        $this->expectToken(Token::TK_SYMBOL, '=');

        if ($this->checkAndExpectToken(Token::TK_SYMBOL, '?')) {
            $list = array('?');
        } else {
            $list = $this->parseCndStringList();
        }

        $this->debugRes(sprintf('defaultValues: (%s)', join(', ', $list)));

        return new SyntaxTreeNode('defaultValues', array('value' => $list));
    }

    /**
     * The value constraints, if any, are listed after a '<'. The absence of
     * this element indicates that no value constraints reportable within the
     * value constraint syntax. A '?' indicates that this attribute is a variant
     *
     *      ValueConstraints ::= '<' (StringList | '?')
     *
     * @return SyntaxTreeNode
     */
    protected function parseValueConstraints()
    {
        $this->debug('parseValueConstraints');

        $this->expectToken(Token::TK_SYMBOL, '<');

        if ($this->checkAndExpectToken(Token::TK_SYMBOL, '?')) {
            $list = array('?');
        } else {
            $list = $this->parseCndStringList();
        }

        $this->debugRes(sprintf('valueConstraints: (%s)', join(', ', $list)));

        return new SyntaxTreeNode('valueConstraints', array('value' => $list));
    }

    /**
     * The property attributes are indicated by the presence or absence of keywords.
     *
     * If 'autocreated' is present without a '?' then the item is autocreated.
     * If 'autocreated' is present with a '?' then the autocreated status is a variant.
     * If 'autocreated' is absent then the item is not autocreated.
     *
     * If 'mandatory' is present without a '?' then the item is mandatory.
     * If 'mandatory' is present with a '?' then the mandatory status is a variant.
     * If 'mandatory' is absent then the item is not mandatory.
     *
     * If 'protected' is present without a '?' then the item is protected.
     * If 'protected' is present with a '?' then the protected status is a variant.
     * If 'protected' is absent then the item is not protected.
     *
     * The OPV status of an item is indicated by the presence of that corresponding
     * keyword.
     * If no OPV keyword is present then an OPV status of COPY is assumed.
     * If the keyword 'OPV' followed by a '?' is present then the OPV status of the
     * item is a variant.
     *
     * If 'multiple' is present without a '?' then the property is multi-valued.
     * If 'multiple' is present with a '?' then the multi-value status is a variant.
     * If 'multiple' is absent then the property is single-valued.
     *
     * The available query comparison operators are listed after the keyword 'queryops'.
     * If 'queryops' is followed by a '?' then this attribute is a variant.
     * If this element is absent then the full set of operators is available.
     *
     * If 'nofulltext' is present without a '?' then the property does not support full
     * text search.
     * If 'nofulltext' is present with a '?' then this attribute is a variant.
     * If 'nofulltext' is absent then the property does support full text search.
     *
     * If 'noqueryorder' is present without a '?' then query results cannot be ordered
     * by this property.
     * If 'noqueryorder' is present with a '?' then this attribute is a variant.
     * If 'noqueryorder' is absent then query results can be ordered by this property.
     * 
     *      PropertyAttribute ::= Autocreated | Mandatory | Protected |
     *          Opv | Multiple | QueryOps | NoFullText |
     *          NoQueryOrder
     *      Autocreated ::= ('autocreated' | 'aut' | 'a' )['?']
     *      Mandatory ::= ('mandatory' | 'man' | 'm') ['?']
     *      Protected ::= ('protected' | 'pro' | 'p') ['?']
     *      Opv ::= 'COPY' | 'VERSION' | 'INITIALIZE' | 'COMPUTE' |
     *          'IGNORE' | 'ABORT' | ('OPV' '?')
     *      Multiple ::= ('multiple' | 'mul' | '*') ['?']
     *      QueryOps ::= ('queryops' | 'qop')
     *          (('''Operator {','Operator}''') | '?')
     *      Operator ::= '=' | '<>' | '<' | '<=' | '>' | '>=' | 'LIKE'
     *      NoFullText ::= ('nofulltext' | 'nof') ['?']
     *      NoQueryOrder ::= ('noqueryorder' | 'nqord') ['?']
     *
     * @return SyntaxTreeNode
     */
    protected function parsePropertyAttributes()
    {
        $this->debug('parsePropertyAttributes');
        return $this->parseAttributes('propertyTypeAttributes', $this->getPropertyTypeAttributes());
    }

    /**
     * The node attributes are indicated by the presence or absence of keywords.
     * 
     * If 'autocreated' is present without a '?' then the item is autocreated.
     * If 'autocreated' is present with a '?' then the autocreated status is a variant.
     * If 'autocreated' is absent then the item is not autocreated.
     *
     * If 'mandatory' is present without a '?' then the item is mandatory.
     * If 'mandatory' is present with a '?' then the mandatory status is a variant.
     * If 'mandatory' is absent then the item is not mandatory.
     *
     * If 'protected' is present without a '?' then the item is protected.
     * If 'protected' is present with a '?' then the protected status is a variant.
     * If 'protected' is absent then the item is not protected.
     *
     * The OPV status of an item is indicated by the presence of that corresponding
     * keyword.
     * If no OPV keyword is present then an OPV status of COPY is assumed.
     * If the keyword 'OPV' followed by a '?' is present then the OPV status of the
     * item is a variant.
     *
     * If 'sns' is present without a '?' then the child node supports same-name siblings.
     * If 'sns' is present with a '?' then this attribute is a variant.
     * If 'sns' is absent then the child node does support same-name siblings.
     *
     *      NodeAttribute ::= Autocreated | Mandatory | Protected |
     *          Opv | Sns
     *      Autocreated ::= ('autocreated' | 'aut' | 'a' )['?']
     *      Mandatory ::= ('mandatory' | 'man' | 'm') ['?']
     *      Protected ::= ('protected' | 'pro' | 'p') ['?']
     *      Opv ::= 'COPY' | 'VERSION' | 'INITIALIZE' | 'COMPUTE' |
     *          'IGNORE' | 'ABORT' | ('OPV' '?')
     *      Sns ::= ('sns' | '*') ['?']
     *
     * @return SyntaxTreeNode
     */
    protected function parseNodeAttributes()
    {
        /**
         * TODO: Clarify this problem
         *
         * Either there is a bug in the Jackrabbit builtin nodetypes CND file here:
         *
         * [rep:Group] > rep:Authorizable
         *   + rep:members (rep:Members) = rep:Members multiple protected VERSION
         *   - rep:members (WEAKREFERENCE) protected multiple < 'rep:Authorizable'
         *
         * or there is an error in the spec that says that a node attribute cannot be
         * "multiple".
         */
        $this->debug('parseNodeAttributes');
        return $this->parseAttributes('nodeAttributes', $this->getNodeAttributes());
    }

    /**
     * A child node definition consists of a node name element followed by optional
     * required node types, default node types  and node attributes elements.
     *
     * The node name, or '*' to indicate a residual property definition, is prefixed
     * with a '+'.
     *
     * The required primary node type list is delimited by parentheses. If this
     * element is missing then a required  primary node type of nt:base is assumed.
     * A '?' indicates that the this attribute is a variant.
     * 
     *      ChildNodeDef ::= NodeName [RequiredTypes] [DefaultType]
     *          [NodeAttribute {NodeAttribute}]
     *      NodeName ::= '+' String
     *      RequiredTypes ::= '(' (StringList | '?') ')'
     *      DefaultType ::= '=' (String | '?')
     *
     * @return SyntaxTreeNode
     */
    protected function parseChildNodeDef()
    {
        $this->debug('parseChildNodeDef');

        $node = new SyntaxTreeNode('childNodeDef');

        $this->expectToken(Token::TK_SYMBOL, '+');

        // Parse the property name
        if ($this->checkAndExpectToken(Token::TK_SYMBOL, '*')) {
            $name = '*';
        } else {
            $name = $this->parseCndString();
        }
        $node->addChild(new SyntaxTreeNode('nodeName', array('value' => $name)));

        // Parse the required types
        if ($this->checkAndExpectToken(Token::TK_SYMBOL, '(')) {
            if ($this->checkAndExpectToken(Token::TK_SYMBOL, '?')) {
                $list = '?';
            } else {
                $list = $this->parseCndStringList();
            }
            $this->expectToken(Token::TK_SYMBOL, ')');

            $node->addChild(new SyntaxTreeNode('requiredTypes', array('value' => $list)));
        }

        // Parse the default type
        if ($this->checkAndExpectToken(Token::TK_SYMBOL, '=')) {
            $node->addChild(new SyntaxTreeNode('defaultType', array('value' => $this->parseCndString())));
        }

        if ($attrNode = $this->parseNodeAttributes()) {
            $node->addChild($attrNode);
        }

        $this->debugRes('childNodeName: ' . $name);

        return $node;
    }

    /**
     * Parse a string list
     *
     *      StringList ::= String {',' String}
     *
     * @return array
     */
    protected function parseCndStringList()
    {
        $this->debug('parseCndStringList');

        $strings = array();

        $strings[] = $this->parseCndString();
        while ($this->checkAndExpectToken(Token::TK_SYMBOL, ',')) {
            $strings[] = $this->parseCndString();
        }

        $this->debugRes(sprintf('string-list: (%s)', join(', ', $strings)));

        return $strings;
    }

    /**
     * Parse a string
     *
     *      String ::= QuotedString | UnquotedString
     *      QuotedString ::= SingleQuotedString | DoubleQuotedString
     *      SingleQuotedString ::= ''' UnquotedString '''
     *      DoubleQuotedString ::= '"' UnquotedString '"'
     *      UnquotedString ::= LocalName
     *      LocalName ::= ValidString – SelfOrParent
     *      SelfOrParent ::= '.' | '..'
     *      ValidString ::= ValidChar {ValidChar}
     *      ValidChar ::= XmlChar – InvalidChar
     *      InvalidChar ::= '/' | ':' | '[' | ']' | '|' | '*'
     *      XmlChar ::= Any character that matches the Char production
     *                  at http://www.w3.org/TR/xml/#NT-Char
     *      Char ::= "\t" | "\r" | "\n" | [#x20-#xD7FF] | [#xE000-#xFFFD] | [#x10000-#x10FFFF]
     *
     * TODO: check \n, \r, \t are valid in CND strings!
     *
     * @return string
     */
    protected function parseCndString()
    {
        $this->debug('parseCndString');

        // TODO: adapt
        
        $string = '';
        $lastType = null;

        while (true) {

            $token = $this->tokenQueue->peek();
            $type = $token->getType();
            $data = $token->getData();

            IF ($type === Token::TK_STRING) {
                $string = substr($data, 1, -1);
                $this->tokenQueue->next();
                return $string;
            }

            // If it's not an identifier or a symbol allowed in a string, break
            if ($type !== Token::TK_IDENTIFIER && $type !== Token::TK_SYMBOL
            || ($type === Token::TK_SYMBOL && $data !== '_' && $data !== ':')) {
                break;
            }

            // Detect spaces (an identifier cannot be followed by an identifier as it would have been read as a single token)
            if ($type === Token::TK_IDENTIFIER && $lastType === Token::TK_IDENTIFIER) {
                break;
            }

            $string .= $token->getData();

            $this->tokenQueue->next();
            $lastType = $type;
        }

        if ($string === '') {
            throw new ParserException($this->tokenQueue, sprintf("Expected CND string, found '%s': ", $this->tokenQueue->peek()->getData()));
        }

        $this->debugRes(sprintf('string: %s', $string));

        return $string;
    }

    /**
     * Return an array representing the allowed node type attributes.
     * The values are the aliases for the attribute.
     * Variant indicates if the attribute can be variant (followed by a ?)
     * Some attributes will need special handling in the parsing function.
     *
     * @return array
     */
    protected function getNodeTypeAttributes()
    {
        return array(
            'orderable' => array('values' => array('o', 'ord', 'orderable'), 'variant' => true),
            'mixin' => array('values' => array('m', 'mix', 'mixin'), 'variant' => true),
            'abstract' => array('values' => array('a', 'abs', 'abstract'), 'variant' => true),
            'noquery' => array('values' => array('noquery', 'nq'), 'variant' => false),
            'query' => array('values' => array('query', 'q'), 'variant' => false),
            'primaryitem' => array('values' => array('primaryitem', '!'), 'variant' => false), // Needs special handling !
        );
    }

    /**
     * Return an array representing the commonly allowed attributes for nodes and property types.
     * The values are the aliases for the attribute.
     * Variant indicates if the attribute can be variant (followed by a ?)
     * Some attributes will need special handling in the parsing function.
     *
     * @return array
     */
    protected function getCommonAttributes()
    {
        return array(
            'autocreated' => array('values' => array('a', 'aut', 'autocreated'), 'variant' => true),
            'mandatory' => array('values' => array('m', 'man', 'mandatory'), 'variant' => true),
            'protected' => array('values' => array('p', 'pro', 'protected'), 'variant' => true),
            'COPY' => array('values' => array('COPY'), 'variant' => false),
            'VERSION' => array('values' => array('VERSION'), 'variant' => false),
            'INITIALIZE' => array('values' => array('INITIALIZE'), 'variant' => false),
            'COMPUTE' => array('values' => array('COMPUTE'), 'variant' => false),
            'IGNORE' => array('values' => array('IGNORE'), 'variant' => false),
            'ABORT' => array('values' => array('ABORT'), 'variant' => false),
            'OPV' => array('values' => array('OPV'), 'variant' => true),
        );
    }

    /**
     * Return an array representing the allowed property type attributes.
     * The values are the aliases for the attribute.
     * Variant indicates if the attribute can be variant (followed by a ?)
     * Some attributes will need special handling in the parsing function.
     *
     * @return array
     */
    protected function getPropertyTypeAttributes()
    {
        return array_merge(
            $this->getCommonAttributes(),
            array(
                'multiple' => array('values' => array('*', 'mul', 'multiple'), 'variant' => true),
                'queryops' => array('values' => array('qop', 'queryops'), 'variant' => true), // Needs special handling !
                'nofulltext' => array('values' => array('nof', 'nofulltext'), 'variant' => true),
                'noqueryorder' => array('values' => array('nqord', 'noqueryorder'), 'variant' => true),
            )
        );
    }

    /**
     * Return an array representing the allowed node attributes.
     * The values are the aliases for the attribute.
     * Variant indicates if the attribute can be variant (followed by a ?)
     * Some attributes will need special handling in the parsing function.
     *
     * @return array
     */
    protected function getNodeAttributes()
    {
        return array_merge(
            $this->getCommonAttributes(),
            array(
                'sns' => array('values' => array('*', 'sns'), 'variant' => true),
            )
        );
    }

    /**
     * Parse a list of attributes.
     * The allowed attributes must be specified in $attributes.
     * The type of the list must be given (node type attributes, property type attributes or node attributes).
     *
     * @param string $type
     * @param array $attributes
     * @return SyntaxTreeNode
     */
    protected function parseAttributes($type, $attributes)
    {
        $this->debug('parseAttributes');

        $node = new SyntaxTreeNode($type);

        $options = array();

        while ($attrNode = $this->parseAttribute($attributes)) {
            $node->addChild($attrNode);
            $options[] = $attrNode->getType();
        }

        $this->debugRes(sprintf('%s: (%s)', $type, join(', ', $options)));

        if (empty($options)) {
            return false;
        }

        return $node;
    }

    /**
     * Parse a single attribute.
     * The allowed attributes must be given in $attributes.
     * Return the attribute if any was found or false.
     *
     * @param array $attributes
     * @return bool|SyntaxTreeNode
     */
    protected function parseAttribute($attributes)
    {
        $this->debug('parseAttribute');

        $token = $this->tokenQueue->peek();
        if (!$token) {
            return false;
        }
        $data = $token->getData();
        
        foreach ($attributes as $name => $def) {

            if (in_array($data, $def['values'])) {

                // Node type attribute found
                $this->tokenQueue->next();

                // Handle special cases
                if ($attribute = $this->parseSpecialCaseAttribute($name)) {
                    return $attribute;
                }

                // If this attribute can ba variant
                if ($def['variant']) {
                    if ($this->checkAndExpectToken(Token::TK_SYMBOL, '?')) {
                        $variant = true;
                    }
                }

                $node = new SyntaxTreeNode($name);
                if (isset($variant)) {
                    $node->setProperty('variant', true);
                }
                return $node;
            }

        }

        return false;
    }

    /**
     * Some attributes need special handling to be parsed, they are managed in
     * this function. Return the attribute if any was found or false.
     *
     * At this point the attribute token has already been removed from the queue.
     *
     * @param string $attributeName
     * @return bool|SyntaxTreeNode
     */
    protected function parseSpecialCaseAttribute($attributeName)
    {
        $this->debug('parseSpecialCaseAttribute');

        if ($attributeName === 'primaryitem') {

            /**
             * If 'primaryitem' is present without a '?' then the string following it is
             * the name of the primary item of the node type.
             * If 'primaryitem' is present with a '?' then the primary item is a variant.
             * If 'primaryitem' is absent then the node type has no primary item.
             *
             *      PrimaryItem ::= ('primaryitem'| '!')(String | '?')
             */

            if ($this->checkAndExpectToken(Token::TK_SYMBOL, '?')) {
                return new SyntaxTreeNode('primaryitem', array('value' => '?'));
            }
            return new SyntaxTreeNode('primaryitem', array('value' => $this->parseCndString()));
        }

        if ($attributeName === 'queryops') {

            return $this->parseQueryOpsAttribute();
        }

        return false;
    }

    /**
     * The available query comparison operators are listed after the keyword 'queryops'.
     * If 'queryops' is followed by a '?' then this attribute is a variant.
     * If this element is absent then the full set of operators is available.
     *
     *      QueryOps ::= ('queryops' | 'qop')
     *          (('''Operator {','Operator}''') | '?')
     *      Operator ::= '=' | '<>' | '<' | '<=' | '>' | '>=' | 'LIKE'
     *
     * @return SyntaxTreeNode
     */
    protected function parseQueryOpsAttribute()
    {
        if ($this->checkAndExpectToken(Token::TK_SYMBOL, '?')) {
            return new SyntaxTreeNode('queryops', array('variant' => true));
        }

        $ops = array();
        do {

            $op = $this->parseQueryOperator();
            $ops[] = $op;

        } while ($op && $this->checkAndExpectToken(Token::TK_SYMBOL, ','));

        if (empty($ops)) {
            // There must be at least an operator if this attribute is not variant
            throw new ParserException($this->tokenQueue, 'Operator expected');
        }

        return new SyntaxTreeNode('queryops', array('value' => $ops));
    }

    /**
     * Parse a query operator.
     *
     * This is quite complicated for not so much... Idealy this should be implemented
     * in a specialized Scanner.
     *
     * @return bool|string
     */
    protected function parseQueryOperator()
    {
        $token = $this->tokenQueue->peek();
        $data = $token->getData();

        $nextToken = $this->tokenQueue->peek(1);
        $nextData = $nextToken->getData();
        $op = false;

        switch ($data) {
            case '<':
                $op = ($nextData === '>' ? '>=' : ($nextData === '=' ? '<=' : '<'));
                break;
            case '>':
                $op = ($nextData === '=' ? '>=' : '>');
                break;
            case '=':
                $op = '=';
                break;
            case 'LIKE':
                $op = 'LIKE';
                break;
        }

        // Consume the correct number of tokens
        if ($op === 'LIKE' || strlen($op) === 1) {
            $this->tokenQueue->next();
        } elseif (strlen($op) === 2) {
            $this->tokenQueue->next();
            $this->tokenQueue->next();
        }

        return $op;
    }
}
