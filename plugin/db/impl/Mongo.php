<?php
namespace nutshell\plugin\db\impl
{
	use \MongoClient;
	use \MongoDB;
	use \MongoCollection;
	use \MongoCursor;
	use \MongoException;

	use nutshell\plugin\db\exception\DbException;

	/**
	 * NOTE: This class REQUIRES MongoDb PECL extension
	 *
	 * On Mongo query objects:
	 *
	 * - All query filters are associative arrays. The most basic form is an exact match:
	 * array('foo' => 'bar')
	 * will match all documents having a "foo" key and a value of "bar" for this key
	 *
	 * - A query can work on several fields at once
	 * array('foo' => 'bar', 'baz' => 'boz')
	 *
	 * - Mongo uses strict type validation
	 * array('foo' => 1)
	 * will not match array('foo' => '1') since 1 (integer) is not the same as '1' (string)
	 *
	 * - Mongo uses $ prefixed operators for comparison or other functionality:
	 * + $gt: greater than (scalar)
	 * + $gte: greater than or equal (scalar)
	 * + $lt: lower than (scalar)
	 * + $lte: lower than or equal (scalar)
	 * + $in: in a list (array)
	 * + $nin: not in a list (array)
	 * + $exists: has the specified key (boolean, existence or non existence check)
	 *
	 * - Mongo does not support LIKE statement, but uses regular expressions
	 * A regular expression must be passed as a MongoRegex object
	 * array('foo' => new MongoRegex('/^bar/i'))
	 * matches all foo keys starting by bar, case insensitive
	 *
	 * - It is possible to combine multiple conditions on the same key by wrapping
	 * in an array
	 * array('foo' => array('$gt' => 1, '$lt' => 10))
	 * translates into ($foo > 1 && $foo < 10)
	 *
	 * - querying on embedded objects can be done via dot notation
	 * array('address.state' => 'NSW')
	 *
	 * - querying on a property that is multivalued will check that the searched 
	 * value is part of the array
	 * array('colours' => 'blue')
	 * will match the document 
	 * array('colours' => ['blue', 'yellow', 'red'])
	 * and
	 * array('colours' => 'blue')
	 * as well
	 * 
	 * 
	 * @author Guillaume Bodi <bodi.giyomu@gmail.com>
	 */
	class Mongo 
	{
		const ASCENDING = MongoCollection::ASCENDING;

		const DESCENDING = MongoCollection::DESCENDING;

		const HANDLER_MONGO = 'mongo';

		const OPT_TYPE = 'type';
		
		const OPT_HOST = 'host';
		
		const OPT_PORT = 'port';
		
		const OPT_DB = 'database';
		
		const OPT_USERNAME = 'username';
		
		const OPT_PASSWORD = 'password';
		
		/*
		 ****************************************************
		 */
		
		const MONGO_CONNECTION_DB = 'db';

		const MONGO_CONNECTION_USERNAME = 'username';

		const MONGO_CONNECTION_PASSWORD = 'password';
		
		const DEFAULT_HOST = 'localhost';

		const DEFAULT_PORT = 27017;
		
		const LOOPBACK_IP = '127.0.0.1';
		
		const DEFAULT_PASSWORD = '';

		protected $dbSchema = '';

		protected $connection = null;

		protected $databaseHandle = null;

		public function __construct($options)
		{
			$this->prepareConstructorOptions($options);

			try
			{
				$connectionOptions = array();

				if(isset($options[self::OPT_DB])) 
				{
					$this->dbSchema = $options[self::OPT_DB];
					$connectionOptions[self::MONGO_CONNECTION_DB] = $this->dbSchema;
				}
				
				if(isset($options[self::OPT_USERNAME]))
				{
					$connectionOptions[self::MONGO_CONNECTION_USERNAME] = $options[self::OPT_USERNAME];
					$connectionOptions[self::MONGO_CONNECTION_PASSWORD] = $options[self::OPT_PASSWORD];
				} 
				$this->connection = new MongoClient(
					$this->getMongoConnectionString($options),
					$connectionOptions
				);

				$this->databaseHandle = $this->connection->selectDB($this->dbSchema);
			}
			catch(MongoException $exception)
			{
				throw new DbException(DbException::MONGO_EXCEPTION, $exception->getMessage(), $exception->getCode(), $options);
			}
		}

		protected function getMongoConnectionString($options)
		{
			return 
				'mongodb://' . $options[self::OPT_HOST] . 
				(
					isset($options[self::OPT_PORT]) && is_numeric($options[self::OPT_PORT]) ?
					':' . $options[self::OPT_PORT] :
					''
				);
		}

		/**
		 * Changes the active database
		 * @param  string $database the name of the DB to load
		 * @return Mongo           the current object
		 */
		public function selectDb($database)
		{
			$this->databaseHandle =	$this->connection->selectDB($database);
			return $this;
		}

		/**
		 * Ignored
		 */
		public function setCharset($charset) {
			// mongo uses UTF-8
		}

		/**
		 * Checks whether the DB manager has secured a valid DB connection or not.
		 * 
		 * @return boolean true if a connection to the server could be established, false otherwise
		 */
		public function isConnected() {
			return !is_null($this->connection);
		}

		/**
		 * Overloader method for dealing with a case where the PDO object is lost on a variable.
		 * 
		 * @access public
		 * @return void
		 */
		public function __destruct()
		{
			$this->disconnect();
		}

		/**
		 * Disconnects the database connection.
		 * 
		 * @access public
		 * @return $this
		 */
		public function disconnect()
		{
			if($this->connection)
			{
				$this->connection->close();
				unset($this->connection);
			}
			return $this;
		}

		/**
		 * Parses the constructor options and initialise values where missing
		 * @param array $options
		 */
		protected function prepareConstructorOptions(&$options) 
		{
			if(!isset($options[self::OPT_HOST])) 
			{
				$options[self::OPT_HOST] = self::LOOPBACK_IP;
			} 
			else if($options[self::OPT_HOST] == self::DEFAULT_HOST) 
			{
				$options[self::OPT_HOST] = self::LOOPBACK_IP;
			}
			
			if(isset($options[self::OPT_USERNAME]) && !isset($options[self::OPT_PASSWORD])) 
			{
				$options[self::OPT_PASSWORD] = self::DEFAULT_PASSWORD;
			}
		}

		public function runCommand($command, $options = array())
		{
			return $this->databaseHandle->command($command, $options);
		}


		/**
		 * Fetches the collection object matching the passed name.
		 * The method will always return a valid collection object, even if the
		 * collection does not exist yet (implicit creation).
		 * @param  string $collectionName	the name of the collection to use
		 * @return MongoCollection			the collection object
		 */
		public function getCollection($collectionName)
		{
			return $this->databaseHandle->selectCollection($collectionName);
		}

		/**
		 * Performs a find query (select)
		 * @param  string $collection the collection to run the find query onto
		 * @param  array  $query      a mongo compatible query (an associative array)
		 * @param  array  $fields     a mongo fieldset (associative array of fields to return/inhibit)
		 * @param  array  $sortFields associative array of fields to sort by
		 * @param  integer $limit     maximum number of documents to return
		 * @param  integer $offset     number of documents to skip before collecting results
		 * @return array             the result set of the query
		 */
		public function find($collection, $query = array(), $fields = array(), $sortFields = null, $limit = null, $offset = null)
		{
			$resultSet = array();

			try {
				$cursor = $this->getCollection($collection)->find($query, $fields);
				if($sortFields)
				{
					$cursor->sort($sortFields);
				}

				if(is_numeric($limit))
				{
					$cursor->limit($limit);
				}
				if(is_numeric($offset))
				{
					$cursor->skip($offset);
				}

				
				while($cursor->hasNext())
				{
					$resultSet[] = $cursor->getNext();
				}

				return $resultSet;
			}
			catch(MongoException $me)
			{
				throw new DbException(DbException::MONGO_EXCEPTION, $me->getMessage(), $me->getCode(), $me);
			}
		}

		/**
		 * Insert a new object in the specified collection.
		 * The object is modified and gains an _id key (if it wasn't already defined)
		 * after a successful insert.
		 * 
		 * @param  string $collection collection name to run the query onto
		 * @param  array $object     the new object to insert
		 */
		public function insert($collection, $object)
		{
			try {
				$this->getCollection($collection)->insert($object);
			}
			catch(MongoException $me)
			{
				throw new DbException(DbException::MONGO_EXCEPTION, $me->getMessage(), $me);
			}
		}

		/**
		 * This method inserts or updates completely an object.
		 * The db document will be updated if it has an _id key and
		 * a matching _id key is found in the collection
		 * @param  string $collection collection name to run the query onto
		 * @param  array $object     the new object to insert or update
		 */
		public function save($collection, $object)
		{
			try {
				$this->getCollection($collection)->save($object);
			}
			catch(MongoException $me)
			{
				throw new DbException(DbException::MONGO_EXCEPTION, $me->getMessage(), $me);
			}
		}

		/**
		 * Updates partially one or many objects
		 * 
		 * @param  string $collection collection name to run the query onto
		 * @param  array   $query       the filter to use for updating
		 * @param  array   $update      associative array of the values to update
		 * @param  boolean $multiUpdate whether to run the update on all elements
		 *                              of the matching set or just the first (defaults to true)
		 */
		public function update($collection, $query = array(), $update = array(), $multiUpdate = true)
		{
			try {
				$this->getCollection($collection)->update($query, array('$set' => $update), array(
					'multiple' => $multiUpdate == true
				));
			}
			catch(MongoException $me)
			{
				throw new DbException(DbException::MONGO_EXCEPTION, $me->getMessage(), $me);
			}
		}

		/**
		 * Remove documents matching the query filter
		 * @param  string $collection collection name to run the query onto
		 * @param  array   $query       the filter to use for deleting
		 * @param  boolean $justOne    whether to remove only the first matching document
		 *                             defaults to false
		 */
		public function delete($collection, $query, $justOne = false)
		{
			try {
				$this->getCollection($collection)->remove($query, array(
					'justOne' => $justOne == true
				));
			}
			catch(MongoException $me)
			{
				throw new DbException(DbException::MONGO_EXCEPTION, $me->getMessage(), $me);
			}
		}

		/**
		 * @alias nutshell\plugin\db\impl\Mongo::delete
		 */
		public function remove($collection, $query, $justOne = false)
		{
			return $this->delete($collection, $query, $justOne);
		}
		
		public function count($collection,$query=null)
		{
			try
			{
				return $this->getCollection($collection)->count($query);
			}
			catch(MongoException $me)
			{
				throw new DbException(DbException::MONGO_EXCEPTION, $me->getMessage(), $me);
			}
		}
	}
}