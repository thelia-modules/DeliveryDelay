<?php

namespace DeliveryDelay\Model\Base;

use \Exception;
use \PDO;
use DeliveryDelay\Model\ProductDelay as ChildProductDelay;
use DeliveryDelay\Model\ProductDelayQuery as ChildProductDelayQuery;
use DeliveryDelay\Model\Map\ProductDelayTableMap;
use DeliveryDelay\Model\Thelia\Model\Product;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\ActiveQuery\ModelJoin;
use Propel\Runtime\Collection\Collection;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\Exception\PropelException;

/**
 * Base class that represents a query for the 'product_delay' table.
 *
 *
 *
 * @method     ChildProductDelayQuery orderById($order = Criteria::ASC) Order by the id column
 * @method     ChildProductDelayQuery orderByProductId($order = Criteria::ASC) Order by the product_id column
 * @method     ChildProductDelayQuery orderByDeliveryDelayMin($order = Criteria::ASC) Order by the delivery_delay_min column
 * @method     ChildProductDelayQuery orderByDeliveryDelayMax($order = Criteria::ASC) Order by the delivery_delay_max column
 * @method     ChildProductDelayQuery orderByRestockDelayMin($order = Criteria::ASC) Order by the restock_delay_min column
 * @method     ChildProductDelayQuery orderByRestockDelayMax($order = Criteria::ASC) Order by the restock_delay_max column
 * @method     ChildProductDelayQuery orderByDeliveryDateStart($order = Criteria::ASC) Order by the delivery_date_start column
 * @method     ChildProductDelayQuery orderByDeliveryType($order = Criteria::ASC) Order by the delivery_type column
 *
 * @method     ChildProductDelayQuery groupById() Group by the id column
 * @method     ChildProductDelayQuery groupByProductId() Group by the product_id column
 * @method     ChildProductDelayQuery groupByDeliveryDelayMin() Group by the delivery_delay_min column
 * @method     ChildProductDelayQuery groupByDeliveryDelayMax() Group by the delivery_delay_max column
 * @method     ChildProductDelayQuery groupByRestockDelayMin() Group by the restock_delay_min column
 * @method     ChildProductDelayQuery groupByRestockDelayMax() Group by the restock_delay_max column
 * @method     ChildProductDelayQuery groupByDeliveryDateStart() Group by the delivery_date_start column
 * @method     ChildProductDelayQuery groupByDeliveryType() Group by the delivery_type column
 *
 * @method     ChildProductDelayQuery leftJoin($relation) Adds a LEFT JOIN clause to the query
 * @method     ChildProductDelayQuery rightJoin($relation) Adds a RIGHT JOIN clause to the query
 * @method     ChildProductDelayQuery innerJoin($relation) Adds a INNER JOIN clause to the query
 *
 * @method     ChildProductDelayQuery leftJoinProduct($relationAlias = null) Adds a LEFT JOIN clause to the query using the Product relation
 * @method     ChildProductDelayQuery rightJoinProduct($relationAlias = null) Adds a RIGHT JOIN clause to the query using the Product relation
 * @method     ChildProductDelayQuery innerJoinProduct($relationAlias = null) Adds a INNER JOIN clause to the query using the Product relation
 *
 * @method     ChildProductDelay findOne(ConnectionInterface $con = null) Return the first ChildProductDelay matching the query
 * @method     ChildProductDelay findOneOrCreate(ConnectionInterface $con = null) Return the first ChildProductDelay matching the query, or a new ChildProductDelay object populated from the query conditions when no match is found
 *
 * @method     ChildProductDelay findOneById(int $id) Return the first ChildProductDelay filtered by the id column
 * @method     ChildProductDelay findOneByProductId(int $product_id) Return the first ChildProductDelay filtered by the product_id column
 * @method     ChildProductDelay findOneByDeliveryDelayMin(int $delivery_delay_min) Return the first ChildProductDelay filtered by the delivery_delay_min column
 * @method     ChildProductDelay findOneByDeliveryDelayMax(int $delivery_delay_max) Return the first ChildProductDelay filtered by the delivery_delay_max column
 * @method     ChildProductDelay findOneByRestockDelayMin(int $restock_delay_min) Return the first ChildProductDelay filtered by the restock_delay_min column
 * @method     ChildProductDelay findOneByRestockDelayMax(int $restock_delay_max) Return the first ChildProductDelay filtered by the restock_delay_max column
 * @method     ChildProductDelay findOneByDeliveryDateStart(string $delivery_date_start) Return the first ChildProductDelay filtered by the delivery_date_start column
 * @method     ChildProductDelay findOneByDeliveryType(string $delivery_type) Return the first ChildProductDelay filtered by the delivery_type column
 *
 * @method     array findById(int $id) Return ChildProductDelay objects filtered by the id column
 * @method     array findByProductId(int $product_id) Return ChildProductDelay objects filtered by the product_id column
 * @method     array findByDeliveryDelayMin(int $delivery_delay_min) Return ChildProductDelay objects filtered by the delivery_delay_min column
 * @method     array findByDeliveryDelayMax(int $delivery_delay_max) Return ChildProductDelay objects filtered by the delivery_delay_max column
 * @method     array findByRestockDelayMin(int $restock_delay_min) Return ChildProductDelay objects filtered by the restock_delay_min column
 * @method     array findByRestockDelayMax(int $restock_delay_max) Return ChildProductDelay objects filtered by the restock_delay_max column
 * @method     array findByDeliveryDateStart(string $delivery_date_start) Return ChildProductDelay objects filtered by the delivery_date_start column
 * @method     array findByDeliveryType(string $delivery_type) Return ChildProductDelay objects filtered by the delivery_type column
 *
 */
abstract class ProductDelayQuery extends ModelCriteria
{

    /**
     * Initializes internal state of \DeliveryDelay\Model\Base\ProductDelayQuery object.
     *
     * @param     string $dbName The database name
     * @param     string $modelName The phpName of a model, e.g. 'Book'
     * @param     string $modelAlias The alias for the model in this query, e.g. 'b'
     */
    public function __construct($dbName = 'thelia', $modelName = '\\DeliveryDelay\\Model\\ProductDelay', $modelAlias = null)
    {
        parent::__construct($dbName, $modelName, $modelAlias);
    }

    /**
     * Returns a new ChildProductDelayQuery object.
     *
     * @param     string $modelAlias The alias of a model in the query
     * @param     Criteria $criteria Optional Criteria to build the query from
     *
     * @return ChildProductDelayQuery
     */
    public static function create($modelAlias = null, $criteria = null)
    {
        if ($criteria instanceof \DeliveryDelay\Model\ProductDelayQuery) {
            return $criteria;
        }
        $query = new \DeliveryDelay\Model\ProductDelayQuery();
        if (null !== $modelAlias) {
            $query->setModelAlias($modelAlias);
        }
        if ($criteria instanceof Criteria) {
            $query->mergeWith($criteria);
        }

        return $query;
    }

    /**
     * Find object by primary key.
     * Propel uses the instance pool to skip the database if the object exists.
     * Go fast if the query is untouched.
     *
     * <code>
     * $obj  = $c->findPk(12, $con);
     * </code>
     *
     * @param mixed $key Primary key to use for the query
     * @param ConnectionInterface $con an optional connection object
     *
     * @return ChildProductDelay|array|mixed the result, formatted by the current formatter
     */
    public function findPk($key, $con = null)
    {
        if ($key === null) {
            return null;
        }
        if ((null !== ($obj = ProductDelayTableMap::getInstanceFromPool((string) $key))) && !$this->formatter) {
            // the object is already in the instance pool
            return $obj;
        }
        if ($con === null) {
            $con = Propel::getServiceContainer()->getReadConnection(ProductDelayTableMap::DATABASE_NAME);
        }
        $this->basePreSelect($con);
        if ($this->formatter || $this->modelAlias || $this->with || $this->select
         || $this->selectColumns || $this->asColumns || $this->selectModifiers
         || $this->map || $this->having || $this->joins) {
            return $this->findPkComplex($key, $con);
        } else {
            return $this->findPkSimple($key, $con);
        }
    }

    /**
     * Find object by primary key using raw SQL to go fast.
     * Bypass doSelect() and the object formatter by using generated code.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @return   ChildProductDelay A model object, or null if the key is not found
     */
    protected function findPkSimple($key, $con)
    {
        $sql = 'SELECT ID, PRODUCT_ID, DELIVERY_DELAY_MIN, DELIVERY_DELAY_MAX, RESTOCK_DELAY_MIN, RESTOCK_DELAY_MAX, DELIVERY_DATE_START, DELIVERY_TYPE FROM product_delay WHERE ID = :p0';
        try {
            $stmt = $con->prepare($sql);
            $stmt->bindValue(':p0', $key, PDO::PARAM_INT);
            $stmt->execute();
        } catch (Exception $e) {
            Propel::log($e->getMessage(), Propel::LOG_ERR);
            throw new PropelException(sprintf('Unable to execute SELECT statement [%s]', $sql), 0, $e);
        }
        $obj = null;
        if ($row = $stmt->fetch(\PDO::FETCH_NUM)) {
            $obj = new ChildProductDelay();
            $obj->hydrate($row);
            ProductDelayTableMap::addInstanceToPool($obj, (string) $key);
        }
        $stmt->closeCursor();

        return $obj;
    }

    /**
     * Find object by primary key.
     *
     * @param     mixed $key Primary key to use for the query
     * @param     ConnectionInterface $con A connection object
     *
     * @return ChildProductDelay|array|mixed the result, formatted by the current formatter
     */
    protected function findPkComplex($key, $con)
    {
        // As the query uses a PK condition, no limit(1) is necessary.
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKey($key)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->formatOne($dataFetcher);
    }

    /**
     * Find objects by primary key
     * <code>
     * $objs = $c->findPks(array(12, 56, 832), $con);
     * </code>
     * @param     array $keys Primary keys to use for the query
     * @param     ConnectionInterface $con an optional connection object
     *
     * @return ObjectCollection|array|mixed the list of results, formatted by the current formatter
     */
    public function findPks($keys, $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getReadConnection($this->getDbName());
        }
        $this->basePreSelect($con);
        $criteria = $this->isKeepQuery() ? clone $this : $this;
        $dataFetcher = $criteria
            ->filterByPrimaryKeys($keys)
            ->doSelect($con);

        return $criteria->getFormatter()->init($criteria)->format($dataFetcher);
    }

    /**
     * Filter the query by primary key
     *
     * @param     mixed $key Primary key to use for the query
     *
     * @return ChildProductDelayQuery The current query, for fluid interface
     */
    public function filterByPrimaryKey($key)
    {

        return $this->addUsingAlias(ProductDelayTableMap::ID, $key, Criteria::EQUAL);
    }

    /**
     * Filter the query by a list of primary keys
     *
     * @param     array $keys The list of primary key to use for the query
     *
     * @return ChildProductDelayQuery The current query, for fluid interface
     */
    public function filterByPrimaryKeys($keys)
    {

        return $this->addUsingAlias(ProductDelayTableMap::ID, $keys, Criteria::IN);
    }

    /**
     * Filter the query on the id column
     *
     * Example usage:
     * <code>
     * $query->filterById(1234); // WHERE id = 1234
     * $query->filterById(array(12, 34)); // WHERE id IN (12, 34)
     * $query->filterById(array('min' => 12)); // WHERE id > 12
     * </code>
     *
     * @param     mixed $id The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildProductDelayQuery The current query, for fluid interface
     */
    public function filterById($id = null, $comparison = null)
    {
        if (is_array($id)) {
            $useMinMax = false;
            if (isset($id['min'])) {
                $this->addUsingAlias(ProductDelayTableMap::ID, $id['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($id['max'])) {
                $this->addUsingAlias(ProductDelayTableMap::ID, $id['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ProductDelayTableMap::ID, $id, $comparison);
    }

    /**
     * Filter the query on the product_id column
     *
     * Example usage:
     * <code>
     * $query->filterByProductId(1234); // WHERE product_id = 1234
     * $query->filterByProductId(array(12, 34)); // WHERE product_id IN (12, 34)
     * $query->filterByProductId(array('min' => 12)); // WHERE product_id > 12
     * </code>
     *
     * @see       filterByProduct()
     *
     * @param     mixed $productId The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildProductDelayQuery The current query, for fluid interface
     */
    public function filterByProductId($productId = null, $comparison = null)
    {
        if (is_array($productId)) {
            $useMinMax = false;
            if (isset($productId['min'])) {
                $this->addUsingAlias(ProductDelayTableMap::PRODUCT_ID, $productId['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($productId['max'])) {
                $this->addUsingAlias(ProductDelayTableMap::PRODUCT_ID, $productId['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ProductDelayTableMap::PRODUCT_ID, $productId, $comparison);
    }

    /**
     * Filter the query on the delivery_delay_min column
     *
     * Example usage:
     * <code>
     * $query->filterByDeliveryDelayMin(1234); // WHERE delivery_delay_min = 1234
     * $query->filterByDeliveryDelayMin(array(12, 34)); // WHERE delivery_delay_min IN (12, 34)
     * $query->filterByDeliveryDelayMin(array('min' => 12)); // WHERE delivery_delay_min > 12
     * </code>
     *
     * @param     mixed $deliveryDelayMin The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildProductDelayQuery The current query, for fluid interface
     */
    public function filterByDeliveryDelayMin($deliveryDelayMin = null, $comparison = null)
    {
        if (is_array($deliveryDelayMin)) {
            $useMinMax = false;
            if (isset($deliveryDelayMin['min'])) {
                $this->addUsingAlias(ProductDelayTableMap::DELIVERY_DELAY_MIN, $deliveryDelayMin['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($deliveryDelayMin['max'])) {
                $this->addUsingAlias(ProductDelayTableMap::DELIVERY_DELAY_MIN, $deliveryDelayMin['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ProductDelayTableMap::DELIVERY_DELAY_MIN, $deliveryDelayMin, $comparison);
    }

    /**
     * Filter the query on the delivery_delay_max column
     *
     * Example usage:
     * <code>
     * $query->filterByDeliveryDelayMax(1234); // WHERE delivery_delay_max = 1234
     * $query->filterByDeliveryDelayMax(array(12, 34)); // WHERE delivery_delay_max IN (12, 34)
     * $query->filterByDeliveryDelayMax(array('min' => 12)); // WHERE delivery_delay_max > 12
     * </code>
     *
     * @param     mixed $deliveryDelayMax The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildProductDelayQuery The current query, for fluid interface
     */
    public function filterByDeliveryDelayMax($deliveryDelayMax = null, $comparison = null)
    {
        if (is_array($deliveryDelayMax)) {
            $useMinMax = false;
            if (isset($deliveryDelayMax['min'])) {
                $this->addUsingAlias(ProductDelayTableMap::DELIVERY_DELAY_MAX, $deliveryDelayMax['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($deliveryDelayMax['max'])) {
                $this->addUsingAlias(ProductDelayTableMap::DELIVERY_DELAY_MAX, $deliveryDelayMax['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ProductDelayTableMap::DELIVERY_DELAY_MAX, $deliveryDelayMax, $comparison);
    }

    /**
     * Filter the query on the restock_delay_min column
     *
     * Example usage:
     * <code>
     * $query->filterByRestockDelayMin(1234); // WHERE restock_delay_min = 1234
     * $query->filterByRestockDelayMin(array(12, 34)); // WHERE restock_delay_min IN (12, 34)
     * $query->filterByRestockDelayMin(array('min' => 12)); // WHERE restock_delay_min > 12
     * </code>
     *
     * @param     mixed $restockDelayMin The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildProductDelayQuery The current query, for fluid interface
     */
    public function filterByRestockDelayMin($restockDelayMin = null, $comparison = null)
    {
        if (is_array($restockDelayMin)) {
            $useMinMax = false;
            if (isset($restockDelayMin['min'])) {
                $this->addUsingAlias(ProductDelayTableMap::RESTOCK_DELAY_MIN, $restockDelayMin['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($restockDelayMin['max'])) {
                $this->addUsingAlias(ProductDelayTableMap::RESTOCK_DELAY_MIN, $restockDelayMin['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ProductDelayTableMap::RESTOCK_DELAY_MIN, $restockDelayMin, $comparison);
    }

    /**
     * Filter the query on the restock_delay_max column
     *
     * Example usage:
     * <code>
     * $query->filterByRestockDelayMax(1234); // WHERE restock_delay_max = 1234
     * $query->filterByRestockDelayMax(array(12, 34)); // WHERE restock_delay_max IN (12, 34)
     * $query->filterByRestockDelayMax(array('min' => 12)); // WHERE restock_delay_max > 12
     * </code>
     *
     * @param     mixed $restockDelayMax The value to use as filter.
     *              Use scalar values for equality.
     *              Use array values for in_array() equivalent.
     *              Use associative array('min' => $minValue, 'max' => $maxValue) for intervals.
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildProductDelayQuery The current query, for fluid interface
     */
    public function filterByRestockDelayMax($restockDelayMax = null, $comparison = null)
    {
        if (is_array($restockDelayMax)) {
            $useMinMax = false;
            if (isset($restockDelayMax['min'])) {
                $this->addUsingAlias(ProductDelayTableMap::RESTOCK_DELAY_MAX, $restockDelayMax['min'], Criteria::GREATER_EQUAL);
                $useMinMax = true;
            }
            if (isset($restockDelayMax['max'])) {
                $this->addUsingAlias(ProductDelayTableMap::RESTOCK_DELAY_MAX, $restockDelayMax['max'], Criteria::LESS_EQUAL);
                $useMinMax = true;
            }
            if ($useMinMax) {
                return $this;
            }
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }
        }

        return $this->addUsingAlias(ProductDelayTableMap::RESTOCK_DELAY_MAX, $restockDelayMax, $comparison);
    }

    /**
     * Filter the query on the delivery_date_start column
     *
     * Example usage:
     * <code>
     * $query->filterByDeliveryDateStart('fooValue');   // WHERE delivery_date_start = 'fooValue'
     * $query->filterByDeliveryDateStart('%fooValue%'); // WHERE delivery_date_start LIKE '%fooValue%'
     * </code>
     *
     * @param     string $deliveryDateStart The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildProductDelayQuery The current query, for fluid interface
     */
    public function filterByDeliveryDateStart($deliveryDateStart = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($deliveryDateStart)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $deliveryDateStart)) {
                $deliveryDateStart = str_replace('*', '%', $deliveryDateStart);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(ProductDelayTableMap::DELIVERY_DATE_START, $deliveryDateStart, $comparison);
    }

    /**
     * Filter the query on the delivery_type column
     *
     * Example usage:
     * <code>
     * $query->filterByDeliveryType('fooValue');   // WHERE delivery_type = 'fooValue'
     * $query->filterByDeliveryType('%fooValue%'); // WHERE delivery_type LIKE '%fooValue%'
     * </code>
     *
     * @param     string $deliveryType The value to use as filter.
     *              Accepts wildcards (* and % trigger a LIKE)
     * @param     string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildProductDelayQuery The current query, for fluid interface
     */
    public function filterByDeliveryType($deliveryType = null, $comparison = null)
    {
        if (null === $comparison) {
            if (is_array($deliveryType)) {
                $comparison = Criteria::IN;
            } elseif (preg_match('/[\%\*]/', $deliveryType)) {
                $deliveryType = str_replace('*', '%', $deliveryType);
                $comparison = Criteria::LIKE;
            }
        }

        return $this->addUsingAlias(ProductDelayTableMap::DELIVERY_TYPE, $deliveryType, $comparison);
    }

    /**
     * Filter the query by a related \DeliveryDelay\Model\Thelia\Model\Product object
     *
     * @param \DeliveryDelay\Model\Thelia\Model\Product|ObjectCollection $product The related object(s) to use as filter
     * @param string $comparison Operator to use for the column comparison, defaults to Criteria::EQUAL
     *
     * @return ChildProductDelayQuery The current query, for fluid interface
     */
    public function filterByProduct($product, $comparison = null)
    {
        if ($product instanceof \DeliveryDelay\Model\Thelia\Model\Product) {
            return $this
                ->addUsingAlias(ProductDelayTableMap::PRODUCT_ID, $product->getId(), $comparison);
        } elseif ($product instanceof ObjectCollection) {
            if (null === $comparison) {
                $comparison = Criteria::IN;
            }

            return $this
                ->addUsingAlias(ProductDelayTableMap::PRODUCT_ID, $product->toKeyValue('PrimaryKey', 'Id'), $comparison);
        } else {
            throw new PropelException('filterByProduct() only accepts arguments of type \DeliveryDelay\Model\Thelia\Model\Product or Collection');
        }
    }

    /**
     * Adds a JOIN clause to the query using the Product relation
     *
     * @param     string $relationAlias optional alias for the relation
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return ChildProductDelayQuery The current query, for fluid interface
     */
    public function joinProduct($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        $tableMap = $this->getTableMap();
        $relationMap = $tableMap->getRelation('Product');

        // create a ModelJoin object for this join
        $join = new ModelJoin();
        $join->setJoinType($joinType);
        $join->setRelationMap($relationMap, $this->useAliasInSQL ? $this->getModelAlias() : null, $relationAlias);
        if ($previousJoin = $this->getPreviousJoin()) {
            $join->setPreviousJoin($previousJoin);
        }

        // add the ModelJoin to the current object
        if ($relationAlias) {
            $this->addAlias($relationAlias, $relationMap->getRightTable()->getName());
            $this->addJoinObject($join, $relationAlias);
        } else {
            $this->addJoinObject($join, 'Product');
        }

        return $this;
    }

    /**
     * Use the Product relation Product object
     *
     * @see useQuery()
     *
     * @param     string $relationAlias optional alias for the relation,
     *                                   to be used as main alias in the secondary query
     * @param     string $joinType Accepted values are null, 'left join', 'right join', 'inner join'
     *
     * @return   \DeliveryDelay\Model\Thelia\Model\ProductQuery A secondary query class using the current class as primary query
     */
    public function useProductQuery($relationAlias = null, $joinType = Criteria::INNER_JOIN)
    {
        return $this
            ->joinProduct($relationAlias, $joinType)
            ->useQuery($relationAlias ? $relationAlias : 'Product', '\DeliveryDelay\Model\Thelia\Model\ProductQuery');
    }

    /**
     * Exclude object from result
     *
     * @param   ChildProductDelay $productDelay Object to remove from the list of results
     *
     * @return ChildProductDelayQuery The current query, for fluid interface
     */
    public function prune($productDelay = null)
    {
        if ($productDelay) {
            $this->addUsingAlias(ProductDelayTableMap::ID, $productDelay->getId(), Criteria::NOT_EQUAL);
        }

        return $this;
    }

    /**
     * Deletes all rows from the product_delay table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public function doDeleteAll(ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ProductDelayTableMap::DATABASE_NAME);
        }
        $affectedRows = 0; // initialize var to track total num of affected rows
        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();
            $affectedRows += parent::doDeleteAll($con);
            // Because this db requires some delete cascade/set null emulation, we have to
            // clear the cached instance *after* the emulation has happened (since
            // instances get re-added by the select statement contained therein).
            ProductDelayTableMap::clearInstancePool();
            ProductDelayTableMap::clearRelatedInstancePool();

            $con->commit();
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }

        return $affectedRows;
    }

    /**
     * Performs a DELETE on the database, given a ChildProductDelay or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or ChildProductDelay object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
     public function delete(ConnectionInterface $con = null)
     {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ProductDelayTableMap::DATABASE_NAME);
        }

        $criteria = $this;

        // Set the correct dbName
        $criteria->setDbName(ProductDelayTableMap::DATABASE_NAME);

        $affectedRows = 0; // initialize var to track total num of affected rows

        try {
            // use transaction because $criteria could contain info
            // for more than one table or we could emulating ON DELETE CASCADE, etc.
            $con->beginTransaction();


        ProductDelayTableMap::removeInstanceFromPool($criteria);

            $affectedRows += ModelCriteria::delete($con);
            ProductDelayTableMap::clearRelatedInstancePool();
            $con->commit();

            return $affectedRows;
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }
    }

} // ProductDelayQuery
