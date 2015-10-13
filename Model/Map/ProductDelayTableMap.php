<?php

namespace DeliveryDelay\Model\Map;

use DeliveryDelay\Model\ProductDelay;
use DeliveryDelay\Model\ProductDelayQuery;
use Propel\Runtime\Propel;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\InstancePoolTrait;
use Propel\Runtime\Connection\ConnectionInterface;
use Propel\Runtime\DataFetcher\DataFetcherInterface;
use Propel\Runtime\Exception\PropelException;
use Propel\Runtime\Map\RelationMap;
use Propel\Runtime\Map\TableMap;
use Propel\Runtime\Map\TableMapTrait;


/**
 * This class defines the structure of the 'product_delay' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 */
class ProductDelayTableMap extends TableMap
{
    use InstancePoolTrait;
    use TableMapTrait;
    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = 'DeliveryDelay.Model.Map.ProductDelayTableMap';

    /**
     * The default database name for this class
     */
    const DATABASE_NAME = 'thelia';

    /**
     * The table name for this class
     */
    const TABLE_NAME = 'product_delay';

    /**
     * The related Propel class for this table
     */
    const OM_CLASS = '\\DeliveryDelay\\Model\\ProductDelay';

    /**
     * A class that can be returned by this tableMap
     */
    const CLASS_DEFAULT = 'DeliveryDelay.Model.ProductDelay';

    /**
     * The total number of columns
     */
    const NUM_COLUMNS = 8;

    /**
     * The number of lazy-loaded columns
     */
    const NUM_LAZY_LOAD_COLUMNS = 0;

    /**
     * The number of columns to hydrate (NUM_COLUMNS - NUM_LAZY_LOAD_COLUMNS)
     */
    const NUM_HYDRATE_COLUMNS = 8;

    /**
     * the column name for the ID field
     */
    const ID = 'product_delay.ID';

    /**
     * the column name for the PRODUCT_ID field
     */
    const PRODUCT_ID = 'product_delay.PRODUCT_ID';

    /**
     * the column name for the DELIVERY_DELAY_MIN field
     */
    const DELIVERY_DELAY_MIN = 'product_delay.DELIVERY_DELAY_MIN';

    /**
     * the column name for the DELIVERY_DELAY_MAX field
     */
    const DELIVERY_DELAY_MAX = 'product_delay.DELIVERY_DELAY_MAX';

    /**
     * the column name for the RESTOCK_DELAY_MIN field
     */
    const RESTOCK_DELAY_MIN = 'product_delay.RESTOCK_DELAY_MIN';

    /**
     * the column name for the RESTOCK_DELAY_MAX field
     */
    const RESTOCK_DELAY_MAX = 'product_delay.RESTOCK_DELAY_MAX';

    /**
     * the column name for the DELIVERY_DATE_START field
     */
    const DELIVERY_DATE_START = 'product_delay.DELIVERY_DATE_START';

    /**
     * the column name for the DELIVERY_TYPE field
     */
    const DELIVERY_TYPE = 'product_delay.DELIVERY_TYPE';

    /**
     * The default string format for model objects of the related table
     */
    const DEFAULT_STRING_FORMAT = 'YAML';

    /**
     * holds an array of fieldnames
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldNames[self::TYPE_PHPNAME][0] = 'Id'
     */
    protected static $fieldNames = array (
        self::TYPE_PHPNAME       => array('Id', 'ProductId', 'DeliveryDelayMin', 'DeliveryDelayMax', 'RestockDelayMin', 'RestockDelayMax', 'DeliveryDateStart', 'DeliveryType', ),
        self::TYPE_STUDLYPHPNAME => array('id', 'productId', 'deliveryDelayMin', 'deliveryDelayMax', 'restockDelayMin', 'restockDelayMax', 'deliveryDateStart', 'deliveryType', ),
        self::TYPE_COLNAME       => array(ProductDelayTableMap::ID, ProductDelayTableMap::PRODUCT_ID, ProductDelayTableMap::DELIVERY_DELAY_MIN, ProductDelayTableMap::DELIVERY_DELAY_MAX, ProductDelayTableMap::RESTOCK_DELAY_MIN, ProductDelayTableMap::RESTOCK_DELAY_MAX, ProductDelayTableMap::DELIVERY_DATE_START, ProductDelayTableMap::DELIVERY_TYPE, ),
        self::TYPE_RAW_COLNAME   => array('ID', 'PRODUCT_ID', 'DELIVERY_DELAY_MIN', 'DELIVERY_DELAY_MAX', 'RESTOCK_DELAY_MIN', 'RESTOCK_DELAY_MAX', 'DELIVERY_DATE_START', 'DELIVERY_TYPE', ),
        self::TYPE_FIELDNAME     => array('id', 'product_id', 'delivery_delay_min', 'delivery_delay_max', 'restock_delay_min', 'restock_delay_max', 'delivery_date_start', 'delivery_type', ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, 7, )
    );

    /**
     * holds an array of keys for quick access to the fieldnames array
     *
     * first dimension keys are the type constants
     * e.g. self::$fieldKeys[self::TYPE_PHPNAME]['Id'] = 0
     */
    protected static $fieldKeys = array (
        self::TYPE_PHPNAME       => array('Id' => 0, 'ProductId' => 1, 'DeliveryDelayMin' => 2, 'DeliveryDelayMax' => 3, 'RestockDelayMin' => 4, 'RestockDelayMax' => 5, 'DeliveryDateStart' => 6, 'DeliveryType' => 7, ),
        self::TYPE_STUDLYPHPNAME => array('id' => 0, 'productId' => 1, 'deliveryDelayMin' => 2, 'deliveryDelayMax' => 3, 'restockDelayMin' => 4, 'restockDelayMax' => 5, 'deliveryDateStart' => 6, 'deliveryType' => 7, ),
        self::TYPE_COLNAME       => array(ProductDelayTableMap::ID => 0, ProductDelayTableMap::PRODUCT_ID => 1, ProductDelayTableMap::DELIVERY_DELAY_MIN => 2, ProductDelayTableMap::DELIVERY_DELAY_MAX => 3, ProductDelayTableMap::RESTOCK_DELAY_MIN => 4, ProductDelayTableMap::RESTOCK_DELAY_MAX => 5, ProductDelayTableMap::DELIVERY_DATE_START => 6, ProductDelayTableMap::DELIVERY_TYPE => 7, ),
        self::TYPE_RAW_COLNAME   => array('ID' => 0, 'PRODUCT_ID' => 1, 'DELIVERY_DELAY_MIN' => 2, 'DELIVERY_DELAY_MAX' => 3, 'RESTOCK_DELAY_MIN' => 4, 'RESTOCK_DELAY_MAX' => 5, 'DELIVERY_DATE_START' => 6, 'DELIVERY_TYPE' => 7, ),
        self::TYPE_FIELDNAME     => array('id' => 0, 'product_id' => 1, 'delivery_delay_min' => 2, 'delivery_delay_max' => 3, 'restock_delay_min' => 4, 'restock_delay_max' => 5, 'delivery_date_start' => 6, 'delivery_type' => 7, ),
        self::TYPE_NUM           => array(0, 1, 2, 3, 4, 5, 6, 7, )
    );

    /**
     * Initialize the table attributes and columns
     * Relations are not initialized by this method since they are lazy loaded
     *
     * @return void
     * @throws PropelException
     */
    public function initialize()
    {
        // attributes
        $this->setName('product_delay');
        $this->setPhpName('ProductDelay');
        $this->setClassName('\\DeliveryDelay\\Model\\ProductDelay');
        $this->setPackage('DeliveryDelay.Model');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('ID', 'Id', 'INTEGER', true, null, null);
        $this->addForeignKey('PRODUCT_ID', 'ProductId', 'INTEGER', 'product', 'ID', true, null, null);
        $this->addColumn('DELIVERY_DELAY_MIN', 'DeliveryDelayMin', 'INTEGER', false, null, null);
        $this->addColumn('DELIVERY_DELAY_MAX', 'DeliveryDelayMax', 'INTEGER', false, null, null);
        $this->addColumn('RESTOCK_DELAY_MIN', 'RestockDelayMin', 'INTEGER', false, null, null);
        $this->addColumn('RESTOCK_DELAY_MAX', 'RestockDelayMax', 'INTEGER', false, null, null);
        $this->addColumn('DELIVERY_DATE_START', 'DeliveryDateStart', 'VARCHAR', false, 255, null);
        $this->addColumn('DELIVERY_TYPE', 'DeliveryType', 'VARCHAR', false, 255, null);
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('Product', '\\DeliveryDelay\\Model\\Thelia\\Model\\Product', RelationMap::MANY_TO_ONE, array('product_id' => 'id', ), 'CASCADE', 'CASCADE');
    } // buildRelations()

    /**
     * Retrieves a string version of the primary key from the DB resultset row that can be used to uniquely identify a row in this table.
     *
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, a serialize()d version of the primary key will be returned.
     *
     * @param array  $row       resultset row.
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     */
    public static function getPrimaryKeyHashFromRow($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        // If the PK cannot be derived from the row, return NULL.
        if ($row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)] === null) {
            return null;
        }

        return (string) $row[TableMap::TYPE_NUM == $indexType ? 0 + $offset : static::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)];
    }

    /**
     * Retrieves the primary key from the DB resultset row
     * For tables with a single-column primary key, that simple pkey value will be returned.  For tables with
     * a multi-column primary key, an array of the primary key columns will be returned.
     *
     * @param array  $row       resultset row.
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM
     *
     * @return mixed The primary key of the row
     */
    public static function getPrimaryKeyFromRow($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {

            return (int) $row[
                            $indexType == TableMap::TYPE_NUM
                            ? 0 + $offset
                            : self::translateFieldName('Id', TableMap::TYPE_PHPNAME, $indexType)
                        ];
    }

    /**
     * The class that the tableMap will make instances of.
     *
     * If $withPrefix is true, the returned path
     * uses a dot-path notation which is translated into a path
     * relative to a location on the PHP include_path.
     * (e.g. path.to.MyClass -> 'path/to/MyClass.php')
     *
     * @param boolean $withPrefix Whether or not to return the path with the class name
     * @return string path.to.ClassName
     */
    public static function getOMClass($withPrefix = true)
    {
        return $withPrefix ? ProductDelayTableMap::CLASS_DEFAULT : ProductDelayTableMap::OM_CLASS;
    }

    /**
     * Populates an object of the default type or an object that inherit from the default.
     *
     * @param array  $row       row returned by DataFetcher->fetch().
     * @param int    $offset    The 0-based offset for reading from the resultset row.
     * @param string $indexType The index type of $row. Mostly DataFetcher->getIndexType().
                                 One of the class type constants TableMap::TYPE_PHPNAME, TableMap::TYPE_STUDLYPHPNAME
     *                           TableMap::TYPE_COLNAME, TableMap::TYPE_FIELDNAME, TableMap::TYPE_NUM.
     *
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     * @return array (ProductDelay object, last column rank)
     */
    public static function populateObject($row, $offset = 0, $indexType = TableMap::TYPE_NUM)
    {
        $key = ProductDelayTableMap::getPrimaryKeyHashFromRow($row, $offset, $indexType);
        if (null !== ($obj = ProductDelayTableMap::getInstanceFromPool($key))) {
            // We no longer rehydrate the object, since this can cause data loss.
            // See http://www.propelorm.org/ticket/509
            // $obj->hydrate($row, $offset, true); // rehydrate
            $col = $offset + ProductDelayTableMap::NUM_HYDRATE_COLUMNS;
        } else {
            $cls = ProductDelayTableMap::OM_CLASS;
            $obj = new $cls();
            $col = $obj->hydrate($row, $offset, false, $indexType);
            ProductDelayTableMap::addInstanceToPool($obj, $key);
        }

        return array($obj, $col);
    }

    /**
     * The returned array will contain objects of the default type or
     * objects that inherit from the default.
     *
     * @param DataFetcherInterface $dataFetcher
     * @return array
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
    public static function populateObjects(DataFetcherInterface $dataFetcher)
    {
        $results = array();

        // set the class once to avoid overhead in the loop
        $cls = static::getOMClass(false);
        // populate the object(s)
        while ($row = $dataFetcher->fetch()) {
            $key = ProductDelayTableMap::getPrimaryKeyHashFromRow($row, 0, $dataFetcher->getIndexType());
            if (null !== ($obj = ProductDelayTableMap::getInstanceFromPool($key))) {
                // We no longer rehydrate the object, since this can cause data loss.
                // See http://www.propelorm.org/ticket/509
                // $obj->hydrate($row, 0, true); // rehydrate
                $results[] = $obj;
            } else {
                $obj = new $cls();
                $obj->hydrate($row);
                $results[] = $obj;
                ProductDelayTableMap::addInstanceToPool($obj, $key);
            } // if key exists
        }

        return $results;
    }
    /**
     * Add all the columns needed to create a new object.
     *
     * Note: any columns that were marked with lazyLoad="true" in the
     * XML schema will not be added to the select list and only loaded
     * on demand.
     *
     * @param Criteria $criteria object containing the columns to add.
     * @param string   $alias    optional table alias
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
    public static function addSelectColumns(Criteria $criteria, $alias = null)
    {
        if (null === $alias) {
            $criteria->addSelectColumn(ProductDelayTableMap::ID);
            $criteria->addSelectColumn(ProductDelayTableMap::PRODUCT_ID);
            $criteria->addSelectColumn(ProductDelayTableMap::DELIVERY_DELAY_MIN);
            $criteria->addSelectColumn(ProductDelayTableMap::DELIVERY_DELAY_MAX);
            $criteria->addSelectColumn(ProductDelayTableMap::RESTOCK_DELAY_MIN);
            $criteria->addSelectColumn(ProductDelayTableMap::RESTOCK_DELAY_MAX);
            $criteria->addSelectColumn(ProductDelayTableMap::DELIVERY_DATE_START);
            $criteria->addSelectColumn(ProductDelayTableMap::DELIVERY_TYPE);
        } else {
            $criteria->addSelectColumn($alias . '.ID');
            $criteria->addSelectColumn($alias . '.PRODUCT_ID');
            $criteria->addSelectColumn($alias . '.DELIVERY_DELAY_MIN');
            $criteria->addSelectColumn($alias . '.DELIVERY_DELAY_MAX');
            $criteria->addSelectColumn($alias . '.RESTOCK_DELAY_MIN');
            $criteria->addSelectColumn($alias . '.RESTOCK_DELAY_MAX');
            $criteria->addSelectColumn($alias . '.DELIVERY_DATE_START');
            $criteria->addSelectColumn($alias . '.DELIVERY_TYPE');
        }
    }

    /**
     * Returns the TableMap related to this object.
     * This method is not needed for general use but a specific application could have a need.
     * @return TableMap
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
    public static function getTableMap()
    {
        return Propel::getServiceContainer()->getDatabaseMap(ProductDelayTableMap::DATABASE_NAME)->getTable(ProductDelayTableMap::TABLE_NAME);
    }

    /**
     * Add a TableMap instance to the database for this tableMap class.
     */
    public static function buildTableMap()
    {
      $dbMap = Propel::getServiceContainer()->getDatabaseMap(ProductDelayTableMap::DATABASE_NAME);
      if (!$dbMap->hasTable(ProductDelayTableMap::TABLE_NAME)) {
        $dbMap->addTableObject(new ProductDelayTableMap());
      }
    }

    /**
     * Performs a DELETE on the database, given a ProductDelay or Criteria object OR a primary key value.
     *
     * @param mixed               $values Criteria or ProductDelay object or primary key or array of primary keys
     *              which is used to create the DELETE statement
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).  This includes CASCADE-related rows
     *                if supported by native driver or if emulated using Propel.
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
     public static function doDelete($values, ConnectionInterface $con = null)
     {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ProductDelayTableMap::DATABASE_NAME);
        }

        if ($values instanceof Criteria) {
            // rename for clarity
            $criteria = $values;
        } elseif ($values instanceof \DeliveryDelay\Model\ProductDelay) { // it's a model object
            // create criteria based on pk values
            $criteria = $values->buildPkeyCriteria();
        } else { // it's a primary key, or an array of pks
            $criteria = new Criteria(ProductDelayTableMap::DATABASE_NAME);
            $criteria->add(ProductDelayTableMap::ID, (array) $values, Criteria::IN);
        }

        $query = ProductDelayQuery::create()->mergeWith($criteria);

        if ($values instanceof Criteria) { ProductDelayTableMap::clearInstancePool();
        } elseif (!is_object($values)) { // it's a primary key, or an array of pks
            foreach ((array) $values as $singleval) { ProductDelayTableMap::removeInstanceFromPool($singleval);
            }
        }

        return $query->delete($con);
    }

    /**
     * Deletes all rows from the product_delay table.
     *
     * @param ConnectionInterface $con the connection to use
     * @return int The number of affected rows (if supported by underlying database driver).
     */
    public static function doDeleteAll(ConnectionInterface $con = null)
    {
        return ProductDelayQuery::create()->doDeleteAll($con);
    }

    /**
     * Performs an INSERT on the database, given a ProductDelay or Criteria object.
     *
     * @param mixed               $criteria Criteria or ProductDelay object containing data that is used to create the INSERT statement.
     * @param ConnectionInterface $con the ConnectionInterface connection to use
     * @return mixed           The new primary key.
     * @throws PropelException Any exceptions caught during processing will be
     *         rethrown wrapped into a PropelException.
     */
    public static function doInsert($criteria, ConnectionInterface $con = null)
    {
        if (null === $con) {
            $con = Propel::getServiceContainer()->getWriteConnection(ProductDelayTableMap::DATABASE_NAME);
        }

        if ($criteria instanceof Criteria) {
            $criteria = clone $criteria; // rename for clarity
        } else {
            $criteria = $criteria->buildCriteria(); // build Criteria from ProductDelay object
        }

        if ($criteria->containsKey(ProductDelayTableMap::ID) && $criteria->keyContainsValue(ProductDelayTableMap::ID) ) {
            throw new PropelException('Cannot insert a value for auto-increment primary key ('.ProductDelayTableMap::ID.')');
        }


        // Set the correct dbName
        $query = ProductDelayQuery::create()->mergeWith($criteria);

        try {
            // use transaction because $criteria could contain info
            // for more than one table (I guess, conceivably)
            $con->beginTransaction();
            $pk = $query->doInsert($con);
            $con->commit();
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }

        return $pk;
    }

} // ProductDelayTableMap
// This is the static code needed to register the TableMap for this table with the main Propel class.
//
ProductDelayTableMap::buildTableMap();
