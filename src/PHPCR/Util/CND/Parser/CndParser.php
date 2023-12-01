<?php

declare(strict_types=1);

namespace PHPCR\Util\CND\Parser;

use PHPCR\NodeType\NodeDefinitionTemplateInterface;
use PHPCR\NodeType\NodeTypeDefinitionInterface;
use PHPCR\NodeType\NodeTypeManagerInterface;
use PHPCR\NodeType\NodeTypeTemplateInterface;
use PHPCR\NodeType\PropertyDefinitionTemplateInterface;
use PHPCR\PropertyType;
use PHPCR\Util\CND\Exception\ParserException;
use PHPCR\Util\CND\Reader\BufferReader;
use PHPCR\Util\CND\Reader\FileReader;
use PHPCR\Util\CND\Reader\ReaderInterface;
use PHPCR\Util\CND\Scanner\Context\DefaultScannerContextWithoutSpacesAndComments;
use PHPCR\Util\CND\Scanner\GenericScanner;
use PHPCR\Util\CND\Scanner\GenericToken as Token;
use PHPCR\Version\OnParentVersionAction;

/**
 * Parser for JCR-2.0 CND files.
 *
 * Implementation:
 * Builds a TokenQueue containing CND statements. The parser does not expect
 * any whitespaces, new lines or comments in the queue. It uses the CndScanner
 * to be sure to generate a valid TokenQueue.
 *
 * @see http://www.day.com/specs/jcr/2.0/25_Appendix.html#25.2.3 CND Grammar
 * @see http://jackrabbit.apache.org/node-type-notation.html
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 * @author Daniel Barsotti <daniel.barsotti@liip.ch>
 * @author David Buchmann <mail@davidbu.ch>
 */
final class CndParser extends AbstractParser
{
    // node type attributes
    private array $ORDERABLE = ['o', 'ord', 'orderable']; // , 'variant' => true);
    private array $MIXIN = ['m', 'mix', 'mixin']; // , 'variant' => true);
    private array $ABSTRACT = ['a', 'abs', 'abstract']; // , 'variant' => true);
    private array $NOQUERY = ['noquery', 'nq']; // , 'variant' => false);
    private array $QUERY = ['query', 'q']; // , 'variant' => false);
    private array $PRIMARYITEM = ['primaryitem', '!']; // , 'variant' => false);

    // common for properties and child definitions
    private array $PRIMARY = ['!', 'pri', 'primary']; // , 'variant' => true),
    private array $AUTOCREATED = ['a', 'aut', 'autocreated']; // , 'variant' => true),
    private array $MANDATORY = ['m', 'man', 'mandatory']; // , 'variant' => true),
    private array $PROTECTED = ['p', 'pro', 'protected']; // , 'variant' => true),
    private array $OPV = ['COPY', 'VERSION', 'INITIALIZE', 'COMPUTE', 'IGNORE', 'ABORT'];

    // property type attributes
    private array $MULTIPLE = ['*', 'mul', 'multiple']; // , 'variant' => true),
    private array $QUERYOPS = ['qop', 'queryops']; // , 'variant' => true), // Needs special handling !
    private array $NOFULLTEXT = ['nof', 'nofulltext']; // , 'variant' => true),
    private array $NOQUERYORDER = ['nqord', 'noqueryorder']; // , 'variant' => true),

    // child node attributes
    // multiple is actually a jackrabbit specific synonym for sns
    // http://www.mail-archive.com/users@jackrabbit.apache.org/msg19268.html
    private array $SNS = ['*', 'sns', 'multiple']; // , 'variant' => true),

    private NodeTypeManagerInterface $ntm;

    /**
     * @var string[]
     */
    private array $namespaces = [];

    /**
     * @var NodeTypeDefinitionInterface[]
     */
    private array $nodeTypes = [];

    public function __construct(NodeTypeManagerInterface $ntm)
    {
        $this->ntm = $ntm;
    }

    /**
     * Parse a file with CND statements.
     *
     * @param string $filename absolute path to the CND file to read
     *
     * @return array{namespaces: string[], nodeTypes: array<string, NodeTypeDefinitionInterface>}
     */
    public function parseFile(string $filename): array
    {
        $reader = new FileReader($filename);

        return $this->parse($reader);
    }

    /**
     * Parse a string of CND statements.
     *
     * @param string $cnd string with CND content
     *
     * @return array{namespaces: string[], nodeTypes: array<string, NodeTypeDefinitionInterface>}
     */
    public function parseString(string $cnd): array
    {
        $reader = new BufferReader($cnd);

        return $this->parse($reader);
    }

    /**
     * @return array{namespaces: string[], nodeTypes: array<string, NodeTypeDefinitionInterface>}
     */
    private function parse(ReaderInterface $reader): array
    {
        $scanner = new GenericScanner(new DefaultScannerContextWithoutSpacesAndComments());
        $this->tokenQueue = $scanner->scan($reader);

        while (!$this->tokenQueue->isEof()) {
            while ($this->checkToken(Token::TK_SYMBOL, '<')) {
                $this->parseNamespaceMapping();
            }

            if (!$this->tokenQueue->isEof()) {
                $this->parseNodeType();
            }
        }

        return [
            'namespaces' => $this->namespaces,
            'nodeTypes' => $this->nodeTypes,
        ];
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
     */
    private function parseNamespaceMapping(): void
    {
        $this->expectToken(Token::TK_SYMBOL, '<');
        $prefix = $this->parseCndString();
        $this->expectToken(Token::TK_SYMBOL, '=');
        $uri = substr($this->expectToken(Token::TK_STRING)->getData(), 1, -1);
        $this->expectToken(Token::TK_SYMBOL, '>');

        $this->namespaces[$prefix] = $uri;
    }

    /**
     * A node type definition consists of a node type name followed by an optional
     * supertypes block, an optional node type attributes block and zero or more
     * blocks, each of which is either a property or child node definition.
     *
     *      NodeTypeDef ::= NodeTypeName [Supertypes]
     *          [NodeTypeAttribute {NodeTypeAttribute}]
     *          {PropertyDef | ChildNodeDef}
     */
    private function parseNodeType(): void
    {
        $nodeType = $this->ntm->createNodeTypeTemplate();
        $this->parseNodeTypeName($nodeType);

        if ($this->checkToken(Token::TK_SYMBOL, '>')) {
            $this->parseSupertypes($nodeType);
        }

        $this->parseNodeTypeAttributes($nodeType);

        $this->parseChildrenAndAttributes($nodeType);

        $this->nodeTypes[$nodeType->getName()] = $nodeType;
    }

    /**
     * The node type name is delimited by square brackets and must be a valid JCR name.
     *
     *      NodeTypeName ::= '[' String ']'
     */
    private function parseNodeTypeName(NodeTypeTemplateInterface $nodeType): void
    {
        $this->expectToken(Token::TK_SYMBOL, '[');
        $name = $this->parseCndString();
        $this->expectToken(Token::TK_SYMBOL, ']');

        $nodeType->setName($name);
    }

    /**
     * The list of supertypes is prefixed by a '>'. If the node type is not a
     * mixin then it implicitly has nt:base as a supertype even if neither
     * nt:base nor a subtype of nt:base appears in the list or if this element
     * is absent. A question mark indicates that the supertypes list is a variant.
     *
     *      Supertypes ::= '>' (StringList | '?')
     */
    private function parseSupertypes(NodeTypeTemplateInterface $nodeType): void
    {
        $this->expectToken(Token::TK_SYMBOL, '>');

        if ($this->checkAndExpectToken(Token::TK_SYMBOL, '?')) {
            $nodeType->setDeclaredSuperTypeNames(['?']);
        } else {
            $nodeType->setDeclaredSuperTypeNames($this->parseCndStringList());
        }
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
     */
    private function parseNodeTypeAttributes(NodeTypeTemplateInterface $nodeType): void
    {
        while (true) {
            if ($this->checkTokenIn(Token::TK_IDENTIFIER, $this->ORDERABLE)) {
                $nodeType->setOrderableChildNodes(true);
            } elseif ($this->checkTokenIn(Token::TK_IDENTIFIER, $this->MIXIN)) {
                $nodeType->setMixin(true);
            } elseif ($this->checkTokenIn(Token::TK_IDENTIFIER, $this->ABSTRACT)) {
                $nodeType->setAbstract(true);
            } elseif ($this->checkTokenIn(Token::TK_IDENTIFIER, $this->NOQUERY)) {
                $nodeType->setQueryable(false);
            } elseif ($this->checkTokenIn(Token::TK_IDENTIFIER, $this->QUERY)) {
                $nodeType->setQueryable(true);
            } elseif ($this->checkTokenIn(Token::TK_IDENTIFIER, $this->PRIMARYITEM)) {
                /*
                 * If 'primaryitem' is present without a '?' then the string following it is
                 * the name of the primary item of the node type.
                 * If 'primaryitem' is present with a '?' then the primary item is a variant.
                 * If 'primaryitem' is absent then the node type has no primary item.
                 *
                 *      PrimaryItem ::= ('primaryitem'| '!')(String | '?')
                 */
                if ($this->checkAndExpectToken(Token::TK_SYMBOL, '?')) {
                    $nodeType->setPrimaryItemName('?');
                } else {
                    $this->tokenQueue->next();
                    $nodeType->setPrimaryItemName($this->parseCndString());
                    continue;
                }
            } else {
                return;
            }
            $this->tokenQueue->next();
        }
    }

    /**
     * Parse both the children propery and nodes definitions.
     *
     *      {PropertyDef | ChildNodeDef}
     */
    private function parseChildrenAndAttributes(NodeTypeTemplateInterface $nodeType): void
    {
        while (true) {
            if ($this->checkToken(Token::TK_SYMBOL, '-')) {
                $this->parsePropDef($nodeType);
            } elseif ($this->checkToken(Token::TK_SYMBOL, '+')) {
                $this->parseChildNodeDef($nodeType);
            } else {
                return;
            }
        }
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
     */
    private function parsePropDef(NodeTypeTemplateInterface $nodeType): void
    {
        $this->expectToken(Token::TK_SYMBOL, '-');

        $property = $this->ntm->createPropertyDefinitionTemplate();
        $property->setAutoCreated(false);
        $property->setMandatory(false);
        $property->setMultiple(false);
        $property->setOnParentVersion(OnParentVersionAction::COPY);
        $property->setProtected(false);
        $property->setRequiredType(PropertyType::STRING);
        $property->setFullTextSearchable(true);
        $property->setQueryOrderable(true);
        $nodeType->getPropertyDefinitionTemplates()->append($property);

        // Parse the property name
        if ($this->checkAndExpectToken(Token::TK_SYMBOL, '*')) {
            $property->setName('*');
        } else {
            $property->setName($this->parseCndString());
        }

        // Parse the property type
        if ($this->checkAndExpectToken(Token::TK_SYMBOL, '(')) {
            $this->parsePropertyType($property);
        }

        // Parse default value
        if ($this->checkAndExpectToken(Token::TK_SYMBOL, '=')) {
            $this->parseDefaultValue($property);
        }

        $this->parsePropertyAttributes($nodeType, $property);

        // Check if there is a constraint (and not another namespace def)
        // Next token is '<' and two token later it's not '=', i.e. not '<ident='
        $next1 = $this->tokenQueue->peek();
        $next2 = $this->tokenQueue->peek(2);
        if ($next1 && '<' === $next1->getData() && (!$next2 || '=' !== $next2->getData())) {
            $this->parseValueConstraints($property);
        }
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
     */
    private function parsePropertyType(PropertyDefinitionTemplateInterface $property): void
    {
        $types = ['STRING', 'BINARY', 'LONG', 'DOUBLE', 'BOOLEAN',  'DATE', 'NAME', 'PATH',
            'REFERENCE', 'WEAKREFERENCE', 'DECIMAL', 'URI', 'UNDEFINED', '*', '?', ];

        if (!$this->checkTokenIn(Token::TK_IDENTIFIER, $types, true)) {
            throw new ParserException($this->tokenQueue, sprintf('Invalid property type: %s', $this->tokenQueue->get()->getData()));
        }

        $data = $this->tokenQueue->get()->getData();

        $this->expectToken(Token::TK_SYMBOL, ')');

        $property->setRequiredType(PropertyType::valueFromName($data));
    }

    /**
     * The default values, if any, are listed after a '='. The attribute is a
     * list in order to accommodate multi-value properties. The absence of this
     * element indicates that there is no static default value reportable. A '?'
     * indicates that this attribute is a variant.
     *
     *      DefaultValues ::= '=' (StringList | '?')
     */
    private function parseDefaultValue(PropertyDefinitionTemplateInterface $property): void
    {
        if ($this->checkAndExpectToken(Token::TK_SYMBOL, '?')) {
            $list = ['?'];
        } else {
            $list = $this->parseCndStringList();
        }

        $property->setDefaultValues($list);
    }

    /**
     * The value constraints, if any, are listed after a '<'. The absence of
     * this element indicates that no value constraints reportable within the
     * value constraint syntax. A '?' indicates that this attribute is a variant.
     *
     *      ValueConstraints ::= '<' (StringList | '?')
     */
    private function parseValueConstraints(PropertyDefinitionTemplateInterface $property): void
    {
        $this->expectToken(Token::TK_SYMBOL, '<');

        if ($this->checkAndExpectToken(Token::TK_SYMBOL, '?')) {
            $list = ['?'];
        } else {
            $list = $this->parseCndStringList();
        }

        $property->setValueConstraints($list);
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
     */
    private function parsePropertyAttributes(NodeTypeTemplateInterface $parentType, PropertyDefinitionTemplateInterface $property): void
    {
        $opvSeen = false;
        while (true) {
            if ($this->checkTokenIn(Token::TK_IDENTIFIER, $this->PRIMARY)) {
                $parentType->setPrimaryItemName($property->getName());
            } elseif ($this->checkTokenIn(Token::TK_IDENTIFIER, $this->AUTOCREATED)) {
                $property->setAutoCreated(true);
            } elseif ($this->checkTokenIn(Token::TK_IDENTIFIER, $this->MANDATORY)) {
                $property->setMandatory(true);
            } elseif ($this->checkTokenIn(Token::TK_IDENTIFIER, $this->PROTECTED)) {
                $property->setProtected(true);
            } elseif ($this->checkTokenIn(Token::TK_IDENTIFIER, $this->MULTIPLE)) {
                $property->setMultiple(true);
            } elseif ($this->checkTokenIn(Token::TK_SYMBOL, $this->MULTIPLE)) {
                $property->setMultiple(true);
            } elseif ($this->checkTokenIn(Token::TK_IDENTIFIER, $this->QUERYOPS)) {
                $property->setAvailableQueryOperators($this->parseQueryOpsAttribute());
            } elseif ($this->checkTokenIn(Token::TK_IDENTIFIER, $this->NOFULLTEXT)) {
                $property->setFullTextSearchable(false);
            } elseif ($this->checkTokenIn(Token::TK_IDENTIFIER, $this->NOQUERYORDER)) {
                $property->setQueryOrderable(false);
            } elseif ($this->checkTokenIn(Token::TK_IDENTIFIER, $this->OPV)) {
                if ($opvSeen) {
                    throw new ParserException($this->tokenQueue, 'More than one on parent version action specified on property '.$property->getName());
                }
                $token = $this->tokenQueue->get();
                $property->setOnParentVersion(OnParentVersionAction::valueFromName($token->getData()));
                $opvSeen = true;
                continue;
            } else {
                return;
            }
            $this->tokenQueue->next();
        }
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
     */
    private function parseChildNodeDef(NodeTypeTemplateInterface $nodeType): void
    {
        $this->expectToken(Token::TK_SYMBOL, '+');
        $childType = $this->ntm->createNodeDefinitionTemplate();
        $nodeType->getNodeDefinitionTemplates()->append($childType);

        // Parse the child name
        if ($this->checkAndExpectToken(Token::TK_SYMBOL, '*')) {
            $childType->setName('*');
        } else {
            $childType->setName($this->parseCndString());
        }

        // Parse the required primary types
        if ($this->checkAndExpectToken(Token::TK_SYMBOL, '(')) {
            if ($this->checkAndExpectToken(Token::TK_SYMBOL, '?')) {
                $list = '?';
            } else {
                $list = $this->parseCndStringList();
            }
            $this->expectToken(Token::TK_SYMBOL, ')');
            $childType->setRequiredPrimaryTypeNames($list);
        }

        // Parse the default primary type
        if ($this->checkAndExpectToken(Token::TK_SYMBOL, '=')) {
            $childType->setDefaultPrimaryTypeName($this->parseCndString());
        }

        $this->parseChildNodeAttributes($nodeType, $childType);
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
     */
    private function parseChildNodeAttributes(
        NodeTypeTemplateInterface $parentType,
        NodeDefinitionTemplateInterface $childType
    ): void {
        while (true) {
            if ($this->checkTokenIn(Token::TK_IDENTIFIER, $this->PRIMARY)) {
                $parentType->setPrimaryItemName($childType->getName());
            } elseif ($this->checkTokenIn(Token::TK_IDENTIFIER, $this->AUTOCREATED)) {
                $childType->setAutoCreated(true);
            } elseif ($this->checkTokenIn(Token::TK_IDENTIFIER, $this->MANDATORY)) {
                $childType->setMandatory(true);
            } elseif ($this->checkTokenIn(Token::TK_IDENTIFIER, $this->PROTECTED)) {
                $childType->setProtected(true);
            } elseif ($this->checkTokenIn(Token::TK_IDENTIFIER, $this->SNS)) {
                $childType->setSameNameSiblings(true);
            } elseif ($this->checkTokenIn(Token::TK_IDENTIFIER, $this->OPV)) {
                $token = $this->tokenQueue->get();
                $childType->setOnParentVersion(OnParentVersionAction::valueFromName($token->getData()));
                continue;
            } else {
                return;
            }

            $this->tokenQueue->next();
        }
    }

    /**
     * Parse a string list.
     *
     *      StringList ::= String {',' String}
     *
     * @return string[]
     */
    private function parseCndStringList(): array
    {
        $strings = [];

        $strings[] = $this->parseCndString();
        while ($this->checkAndExpectToken(Token::TK_SYMBOL, ',')) {
            $strings[] = $this->parseCndString();
        }

        return $strings;
    }

    /**
     * Parse a string.
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
     */
    private function parseCndString(): string
    {
        $string = '';
        $lastType = null;

        while (true) {
            $token = $this->tokenQueue->peek();

            // If there are no more tokens, break
            if (!$token) {
                break;
            }

            $type = $token->getType();
            $data = $token->getData();

            if (Token::TK_STRING === $type) {
                $string = substr($data, 1, -1);
                $this->tokenQueue->next();

                return $string;
            }

            // If it's not an identifier or a symbol allowed in a string, break
            if (Token::TK_IDENTIFIER !== $type && Token::TK_SYMBOL !== $type
            || (Token::TK_SYMBOL === $type && '_' !== $data && ':' !== $data)) {
                break;
            }

            // Detect spaces (an identifier cannot be followed by an identifier as it would have been read as a single token)
            if (Token::TK_IDENTIFIER === $type && Token::TK_IDENTIFIER === $lastType) {
                break;
            }

            $string .= $token->getData();

            $this->tokenQueue->next();
            $lastType = $type;
        }

        if ('' === $string) {
            throw new ParserException($this->tokenQueue, sprintf("Expected CND string, found '%s': ", $this->tokenQueue->peek()->getData()));
        }

        return $string;
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
     * @return array<bool|string>
     */
    private function parseQueryOpsAttribute(): array
    {
        if ($this->checkAndExpectToken(Token::TK_SYMBOL, '?')) {
            // this denotes a variant, whatever that is
            throw new ParserException($this->tokenQueue, 'TODO: understand what "variant" means');
        }

        $ops = [];
        do {
            $op = $this->parseQueryOperator();
            $ops[] = $op;
        } while ($op && $this->checkAndExpectToken(Token::TK_SYMBOL, ','));

        return $ops;
    }

    private function parseQueryOperator(): bool|string
    {
        $token = $this->tokenQueue->peek();
        $data = $token->getData();

        $nextToken = $this->tokenQueue->peek(1);
        $nextData = $nextToken->getData();
        $op = false;

        switch ($data) {
            case '<':
                $op = ('>' === $nextData ? '>=' : ('=' === $nextData ? '<=' : '<'));
                break;
            case '>':
                $op = ('=' === $nextData ? '>=' : '>');
                break;
            case '=':
                $op = '=';
                break;
            case 'LIKE':
                $op = 'LIKE';
                break;
        }

        // Consume the correct number of tokens
        if ('LIKE' === $op || 1 === strlen($op)) {
            $this->tokenQueue->next();
        } elseif (2 === strlen($op)) {
            $this->tokenQueue->next();
            $this->tokenQueue->next();
        }

        return $op;
    }
}
