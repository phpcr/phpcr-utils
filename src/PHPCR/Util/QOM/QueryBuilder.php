<?php

namespace PHPCR\Util\QOM;

use PHPCR\Query\QOM\ColumnInterface;
use PHPCR\Query\QOM\ConstraintInterface;
use PHPCR\Query\QOM\DynamicOperandInterface;
use PHPCR\Query\QOM\JoinConditionInterface;
use PHPCR\Query\QOM\OrderingInterface;
use PHPCR\Query\QOM\QueryObjectModelConstantsInterface;
use PHPCR\Query\QOM\QueryObjectModelFactoryInterface;
use PHPCR\Query\QOM\QueryObjectModelInterface;
use PHPCR\Query\QOM\SourceInterface;
use PHPCR\Query\QueryInterface;
use PHPCR\Query\QueryResultInterface;

/**
 * QueryBuilder class is responsible for dynamically create QOM queries.
 *
 * @license http://www.apache.org/licenses Apache License Version 2.0, January 2004
 * @license http://opensource.org/licenses/MIT MIT License
 * @author      Nacho Martín <nitram.ohcan@gmail.com>
 * @author      Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author      Benjamin Eberlei <kontakt@beberlei.de>
 */
class QueryBuilder
{
    /** The builder states. */
    private const STATE_DIRTY = 0;
    private const STATE_CLEAN = 1;

    /**
     * @var int The state of the query object. Can be dirty or clean.
     */
    private int $state = self::STATE_CLEAN;

    /**
     * @var int the offset to retrieve only a slice of results
     */
    private int $firstResult = 0;

    /**
     * @var int The maximum number of results to retrieve. 0 sets no maximum.
     */
    private int $maxResults = 0;

    /**
     * @var OrderingInterface[] with the orderings that determine the order of the result
     */
    private array $orderings = [];

    private ?ConstraintInterface $constraint = null;

    /**
     * @var ColumnInterface[] the columns to be selected
     */
    private array $columns = [];

    private ?SourceInterface $source = null;

    private ?QueryObjectModelInterface $query = null;

    /**
     * @var array<string, mixed> the query parameters
     */
    private array $params = [];

    /**
     * Initializes a new QueryBuilder.
     */
    public function __construct(
        private QueryObjectModelFactoryInterface $qomFactory
    ) {
    }

    /**
     * Get a query builder instance from an existing query.
     *
     * @param string|QueryObjectModelInterface $statement the statement in the specified language
     * @param string                           $language  the query language
     *
     * @throws \InvalidArgumentException
     */
    public function setFromQuery(string|QueryObjectModelInterface $statement, string $language): static
    {
        if (QueryInterface::JCR_SQL2 === $language) {
            $converter = new Sql2ToQomQueryConverter($this->qomFactory);
            $statement = $converter->parse($statement);
        }

        if (!$statement instanceof QueryObjectModelInterface) {
            throw new \InvalidArgumentException("Language '$language' not supported");
        }

        $this->state = self::STATE_DIRTY;
        $this->source = $statement->getSource();
        $this->constraint = $statement->getConstraint();
        $this->orderings = $statement->getOrderings();
        $this->columns = $statement->getColumns();

        return $this;
    }

    /**
     * Get the associated QOMFactory for this query builder.
     */
    public function getQOMFactory(): QueryObjectModelFactoryInterface
    {
        return $this->qomFactory;
    }

    /**
     * Shortcut for getQOMFactory().
     */
    public function qomf(): QueryObjectModelFactoryInterface
    {
        return $this->getQOMFactory();
    }

    /**
     * sets the position of the first result to retrieve (the "offset").
     *
     * @param int $firstResult the First result to return
     */
    public function setFirstResult(int $firstResult): static
    {
        $this->firstResult = $firstResult;

        return $this;
    }

    /**
     * Gets the position of the first result the query object was set to retrieve (the "offset").
     * Returns NULL if {@link setFirstResult} was not applied to this QueryBuilder.
     *
     * @return int the position of the first result
     */
    public function getFirstResult(): int
    {
        return $this->firstResult;
    }

    /**
     * Sets the maximum number of results to retrieve (the "limit").
     *
     * @param int $maxResults the maximum number of results to retrieve
     */
    public function setMaxResults(int $maxResults): static
    {
        $this->maxResults = $maxResults;

        return $this;
    }

    /**
     * Gets the maximum number of results the query object was set to retrieve (the "limit").
     * Returns NULL if {@link setMaxResults} was not applied to this query builder.
     */
    public function getMaxResults(): int
    {
        return $this->maxResults;
    }

    /**
     * Gets the array of orderings.
     *
     * @return OrderingInterface[] orderings to apply
     */
    public function getOrderings(): array
    {
        return $this->orderings;
    }

    /**
     * Adds an ordering to the query results.
     *
     * @param DynamicOperandInterface $sort  the ordering expression
     * @param string                  $order the ordering direction
     *
     * @throws \InvalidArgumentException
     */
    public function addOrderBy(DynamicOperandInterface $sort, string $order = 'ASC'): static
    {
        $order = strtoupper($order);

        if (!in_array($order, ['ASC', 'DESC'])) {
            throw new \InvalidArgumentException('Order must be one of "ASC" or "DESC"');
        }

        $this->state = self::STATE_DIRTY;
        if ('DESC' === $order) {
            $ordering = $this->qomFactory->descending($sort);
        } else {
            $ordering = $this->qomFactory->ascending($sort);
        }
        $this->orderings[] = $ordering;

        return $this;
    }

    /**
     * Specifies an ordering for the query results.
     * Replaces any previously specified orderings, if any.
     *
     * @param DynamicOperandInterface $sort  the ordering expression
     * @param string                  $order the ordering direction
     */
    public function orderBy(DynamicOperandInterface $sort, string $order = 'ASC'): static
    {
        $this->orderings = [];
        $this->addOrderBy($sort, $order);

        return $this;
    }

    /**
     * Specifies one restriction (may be simple or composed).
     * Replaces any previously specified restrictions, if any.
     */
    public function where(ConstraintInterface $constraint): static
    {
        $this->state = self::STATE_DIRTY;
        $this->constraint = $constraint;

        return $this;
    }

    /**
     * Returns the constraint to apply.
     */
    public function getConstraint(): ?ConstraintInterface
    {
        return $this->constraint;
    }

    /**
     * Creates a new constraint formed by applying a logical AND to the
     * existing constraint and the new one.
     *
     * Order of ands is important:
     *
     * Given $this->constraint = $constraint1
     * running andWhere($constraint2)
     * resulting constraint will be $constraint1 AND $constraint2
     *
     * If there is no previous constraint then it will simply store the
     * provided one
     */
    public function andWhere(ConstraintInterface $constraint): static
    {
        $this->state = self::STATE_DIRTY;

        if ($this->constraint) {
            $this->constraint = $this->qomFactory->andConstraint($this->constraint, $constraint);
        } else {
            $this->constraint = $constraint;
        }

        return $this;
    }

    /**
     * Creates a new constraint formed by applying a logical OR to the
     * existing constraint and the new one.
     *
     * Order of ands is important:
     *
     * Given $this->constraint = $constraint1
     * running orWhere($constraint2)
     * resulting constraint will be $constraint1 OR $constraint2
     *
     * If there is no previous constraint then it will simply store the
     * provided one
     */
    public function orWhere(ConstraintInterface $constraint): static
    {
        $this->state = self::STATE_DIRTY;

        if ($this->constraint) {
            $this->constraint = $this->qomFactory->orConstraint($this->constraint, $constraint);
        } else {
            $this->constraint = $constraint;
        }

        return $this;
    }

    /**
     * Returns the columns to be selected.
     *
     * @return ColumnInterface[] The columns to be selected
     */
    public function getColumns(): array
    {
        return $this->columns;
    }

    /**
     * Sets the columns to be selected.
     *
     * @param ColumnInterface[] $columns The columns to be selected
     */
    public function setColumns(array $columns): static
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Identifies a property in the specified or default selector to include in the tabular view of query results.
     * Replaces any previously specified columns to be selected if any.
     */
    public function select(string $selectorName, string $propertyName, string $columnName = null): static
    {
        $this->state = self::STATE_DIRTY;
        $this->columns = [$this->qomFactory->column($selectorName, $propertyName, $columnName)];

        return $this;
    }

    /**
     * Adds a property in the specified or default selector to include in the tabular view of query results.
     */
    public function addSelect(string $selectorName, string $propertyName, string $columnName = null): static
    {
        $this->state = self::STATE_DIRTY;

        $this->columns[] = $this->qomFactory->column($selectorName, $propertyName, $columnName);

        return $this;
    }

    /**
     * Sets the default Selector or the node-tuple Source. Can be a selector
     * or a join.
     */
    public function from(SourceInterface $source): static
    {
        $this->state = self::STATE_DIRTY;
        $this->source = $source;

        return $this;
    }

    /**
     * Gets the default Selector.
     *
     * @return SourceInterface the default selector
     */
    public function getSource(): ?SourceInterface
    {
        return $this->source;
    }

    /**
     * Performs an inner join between the stored source and the supplied source.
     *
     * @throws \RuntimeException if there is not an existing source
     */
    public function join(SourceInterface $rightSource, JoinConditionInterface $joinCondition): static
    {
        return $this->innerJoin($rightSource, $joinCondition);
    }

    /**
     * Performs an inner join between the stored source and the supplied source.
     *
     * @throws \RuntimeException if there is not an existing source
     */
    public function innerJoin(SourceInterface $rightSource, JoinConditionInterface $joinCondition): static
    {
        return $this->joinWithType($rightSource, QueryObjectModelConstantsInterface::JCR_JOIN_TYPE_INNER, $joinCondition);
    }

    /**
     * Performs an left outer join between the stored source and the supplied source.
     *
     * @throws \RuntimeException if there is not an existing source
     */
    public function leftJoin(SourceInterface $rightSource, JoinConditionInterface $joinCondition): static
    {
        return $this->joinWithType($rightSource, QueryObjectModelConstantsInterface::JCR_JOIN_TYPE_LEFT_OUTER, $joinCondition);
    }

    /**
     * Performs a right outer join between the stored source and the supplied source.
     *
     * @throws \RuntimeException if there is not an existing source
     */
    public function rightJoin(SourceInterface $rightSource, JoinConditionInterface $joinCondition): static
    {
        return $this->joinWithType($rightSource, QueryObjectModelConstantsInterface::JCR_JOIN_TYPE_RIGHT_OUTER, $joinCondition);
    }

    /**
     * Performs an join between the stored source and the supplied source.
     *
     * @param string $joinType as specified in PHPCR\Query\QOM\QueryObjectModelConstantsInterface
     *
     * @throws \RuntimeException if there is not an existing source
     */
    public function joinWithType(SourceInterface $rightSource, string $joinType, JoinConditionInterface $joinCondition): static
    {
        if (!$this->source) {
            throw new \RuntimeException('Cannot perform a join without a previous call to from');
        }

        $this->state = self::STATE_DIRTY;
        $this->source = $this->qomFactory->join($this->source, $rightSource, $joinType, $joinCondition);

        return $this;
    }

    /**
     * Gets the query built.
     */
    public function getQuery(): ?QueryObjectModelInterface
    {
        if (null !== $this->query && self::STATE_CLEAN === $this->state) {
            return $this->query;
        }

        $this->state = self::STATE_CLEAN;
        $this->query = $this->qomFactory->createQuery($this->source, $this->constraint, $this->orderings, $this->columns);

        if ($this->firstResult) {
            $this->query->setOffset($this->firstResult);
        }

        if ($this->maxResults) {
            $this->query->setLimit($this->maxResults);
        }

        return $this->query;
    }

    /**
     * Executes the query setting firstResult and maxResults.
     */
    public function execute(): QueryResultInterface
    {
        if (null === $this->query || self::STATE_DIRTY === $this->state) {
            $this->query = $this->getQuery();
        }

        foreach ($this->params as $key => $value) {
            $this->query->bindValue($key, $value);
        }

        return $this->query->execute();
    }

    /**
     * Sets a query parameter for the query being constructed.
     *
     * @param string $key   the parameter name
     * @param mixed  $value the parameter value
     */
    public function setParameter(string $key, mixed $value): static
    {
        $this->params[$key] = $value;

        return $this;
    }

    /**
     * Gets a (previously set) query parameter of the query being constructed.
     */
    public function getParameter(string $key): mixed
    {
        return isset($this->params[$key]) ? $this->params[$key] : null;
    }

    /**
     * Sets a collection of query parameters for the query being constructed.
     *
     * @param array<string, mixed> $params the query parameters to set
     */
    public function setParameters(array $params): static
    {
        $this->params = $params;

        return $this;
    }

    /**
     * Gets all defined query parameters for the query being constructed.
     *
     * @return array<string, mixed>
     */
    public function getParameters(): array
    {
        return $this->params;
    }
}
