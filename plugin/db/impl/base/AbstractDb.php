<?php
namespace nutshell\plugin\db\impl\base
{
	use nutshell\Nutshell;
	use \PDO;
	use \PDOException;
	use \PDOStatement;
	use nutshell\plugin\db\exception\DbException;
	
	/**
	 * NOTE: This class REQUIRES PDO AND a PDO Driver per database type.
	 * 
	 * @author Timothy Chandler <tim.chandler@spinifexgroup.com>
	 */
	class AbstractDb
	{

		const OPT_TYPE = 'type';
		
		const OPT_HOST = 'host';
		
		const OPT_PORT = 'port';
		
		const OPT_DB = 'database';
		
		const OPT_USERNAME = 'username';
		
		const OPT_PASSWORD = 'password';
		
		/*
		 ****************************************************
		 */
		
		const DEFAULT_HOST = 'localhost';
		
		const LOOPBACK_IP = '127.0.0.1';
		
		const DEFAULT_PASSWORD = '';
		
		/*
		 ****************************************************
		 */
		
		const HANDLER_MYSQL = 'mysql';
		
		const HANDLER_MSSQL = 'mssql';
		
		const HANDLER_SQLITE = 'sqlite';
		
		const HANDLER_ORACLE = 'oracle';
		
		const HANDLER_SYBASE = 'sybase';
		
		const HANDLER_DBLIB = 'dblib';
		
		const HANDLER_ODBC = 'odbc';
		
		const HANDLER_OCI = 'oci';
		
		/*
		 ****************************************************
		 */
		
		/**
		 * @var PDO
		 * @access private
		 */
		protected $connection	=null;
		
		/**
		 * When true, throws an exception when a problem is found running a SQL.
		 * @var boolean
		 */
		protected $throwExceptionOnError =true;
		
		
		/**
		 * MySQL schema (database)
		 * @var string
		 */
		protected $dbSchema = '';
		
		/**
		 * Currently active character set
		 * @var string
		 */
		protected $charset = null;
		
		/**
		 * @var array
		 * @access private
		 */
		protected $lastQuery=array
		(
			'statement'		=>false,
			'params'		=>false,
			'sql'			=>false,
			'resultSet'		=>false,
			'numResults'	=>0,
			'affectedRows'	=>0,
			'lastError'		=>false
		);
		
		protected $host 	=null;
		
		/**
		 * Class constructor.
		 * 
		 * NOTE: Not all database types will require all the parameters to connect. For example,
		 * the sqlite type only requires the 'host', and this is usually represented as a filename.
		 * 
		 * @param $options array
		 */
		public function __construct($options)
		{
			$this->prepareConstructorOptions($options);
			
			try
			{
				if(isset($options[self::OPT_DB])) 
				{
					$this->dbSchema = $options[self::OPT_DB];
				}
				
				if(isset($options[self::OPT_USERNAME]))
				{
					$this->connection = new PDO($this->getPDOConnectionString($options), $options[self::OPT_USERNAME], $options[self::OPT_PASSWORD]);
				} 
				else 
				{
					$this->connection = new PDO($this->getPDOConnectionString($options));
				}
			}
			catch(PDOException $exception)
			{
				throw new DbException(DbException::PDO_EXCEPTION, $exception->getMessage(), $exception->getCode(), $options);
			}
			
			return true;
		}
		
		/**
		 * Sets the connection's charset if supported
		 * @param string $charset
		 */
		public function setCharset($charset) {
			$this->charset = $charset;
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
		
		/**
		 * Generates a the PDO connection string.
		 * @param array $options
		 * @return string
		 */
		protected function getPDOConnectionString($options) 
		{
			return $options[self::OPT_TYPE] . ':host=' . $options[self::OPT_HOST] . ';port=' . $options[self::OPT_PORT] . ';dbname=' . $options[self::OPT_DB];
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
		 * When $pthrowExceptionOnError is true, provokes an exception when there is an error running a SQL.
		 * @param boolean $pthrowExceptionOnError
		 */
		public function setThrowExceptionOnError($pthrowExceptionOnError)
		{
			$this->throwExceptionOnError = $pthrowExceptionOnError;
		}
		
		/**
		 * Disconnects the database connection.
		 * 
		 * @access public
		 * @return $this
		 */
		public function disconnect()
		{
			unset($this->connection);
			return $this;
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
		 * Resets the last query array.
		 * 
		 * @access private
		 * @return $this
		 */
		private function resetLastQueryParams()
		{
			if ($this->lastQuery['statement'] instanceof PDOStatement)
			{
				$this->lastQuery['statement']->closeCursor();
			}
			unset($this->lastQuery);
			$this->lastQuery=array
			(
				'statement'		=>false,
				'params'		=>false,
				'sql'			=>false,
				'resultSet'		=>false,
				'numResults'	=>0,
				'affectedRows'	=>0
			);
			return $this;
		}
		
		/**
		 * The main internal method for executing the different types of statements.
		 * 
		 * @access private
		 * @param $type - The type of statement to execute.
		 * @param $args - Any arguments to pass to the statement (these are used as bound parameters). 
		 * @return PDOStatement
		 */
		public function executeStatement($type='query',$args=array(),$usePrepared=true)
		{
			$this->resetLastQueryParams();
			$return=false;
			
			if (!empty($args[0]))
			{
				if ($args[0]!=$this->lastQuery['sql'] || !$usePrepared)
				{
					$this->lastQuery['sql']=$args[0];
					$config = array();
					if(extension_loaded('pdo_mysql')) $config['PDO::MYSQL_ATTR_USE_BUFFERED_QUERY'] = true; 
					$this->lastQuery['statement']=$this->connection->prepare($args[0],$config);
				}
			}
			else if (empty($args[0]) && empty($this->lastQuery['sql']))
			{
				throw new DbException(DbException::INVALID_STATEMENT, 'Unable to execute statement. Query was empty!');
			}
			if (isset($args[1]))
			{
				if (!is_array($args[1]))
				{
					$this->lastQuery['params']=&$args;
					for ($i=1,$j=count($args); $i<$j; $i++)
					{
						$this->lastQuery['statement']->bindParam($i,$args[$i]);
					}
				}
				else
				{
					$i=1;
					$this->lastQuery['params']=&$args[1];
					foreach ($args[1] as &$val)
					{
						if(!is_object($this->lastQuery['statement'])) throw new DbException(DbException::INVALID_STATEMENT, $this->lastQuery);
						if(is_object($val)) throw new DbException(DbException::INVALID_STATEMENT, "Cannot bind-param with an object", $val);
						$this->lastQuery['statement']->bindParam($i,$val);
						$i++;
					}
				}
			}
			
			// Execute the query
			if ($this->lastQuery['statement'] && $this->lastQuery['statement']->execute())
			{
				$this->lastQuery['resultSet']		=$this->lastQuery['statement']->fetchAll();
				$this->lastQuery['numResults']		=count($this->lastQuery['resultSet']);
				$this->lastQuery['affectedRows']	=0;
				switch ($type)
				{
					case 'query':
					{
						$this->lastQuery['affectedRows']=$this->lastQuery['statement']->rowCount();
						if (stristr($this->lastQuery['sql'],'SELECT'))
						{
							$return=$this->lastQuery['numResults'];
						}
						else
						{
							$return=$this->lastQuery['affectedRows'];
						}
						break;
					}
					case 'select':
					{
						$return=$this->lastQuery['numResults'];
						break;
					}
					case 'insert':
					{
						$this->lastQuery['affectedRows']=$this->lastQuery['statement']->rowCount();
						$this->lastQuery['insertId']	=$this->connection->lastInsertId();
						if ($this->lastQuery['insertId']!=='0')
						{
							$return=$this->lastQuery['insertId'];
						}
						else
						{
							$return=true;
						}
						break;
					}
					case 'update':
					{
						$this->lastQuery['affectedRows']=$this->lastQuery['statement']->rowCount();
						$return							=$this->lastQuery['affectedRows'];
						break;
					}
					case 'delete':
					{
						$this->lastQuery['affectedRows']=$this->lastQuery['statement']->rowCount();
						$return							=$this->lastQuery['affectedRows'];
						break;
					}
				}
			}
			@list(,,$this->lastQuery['lastError'])		=$this->lastQuery['statement'] ? $this->lastQuery['statement']->errorInfo() : $this->connection->errorInfo();
			
			// throws an exception if there is a problem running the query and throwExceptionOnError
			if (($this->throwExceptionOnError) && ($this->lastQuery['lastError']))
			{
				$error_message = $this->lastQuery['lastError'];
				try 
				{
					$faulty_sql = $this->lastQuery['statement'] ? $this->lastQuery['statement']->queryString : $this->lastQuery['sql'];
					if (strlen($faulty_sql)>0)
					{
						Nutshell::getInstance()->plugin->Logger('nutshell.plugin.db')->error($error_message.":".$faulty_sql);
					}
				} catch (\Exception $e) {
					// nothing can be done if the error treatment fails.
				}
				throw new DbException($error_message,$this->lastQuery);			
			}
			
			return $return;
		}
		
		/**
		 * Executes any given query.
		 * 
		 * @access public
		 * @param ... Parameters to bind.
		 * @return PDOStatement
		 */
		public function query()
		{
			$args=func_get_args();
			$return = $this->executeStatement('query',$args);
			return $return;
		}
		
		/**
		 * This function executes a query and returns its result.
		 * 
		 * @access public
		 * @param  Query plus Parameters to bind.
		 * @return array
		 */
		public function getResultFromQuery()
		{
			$this->executeStatement('query',func_get_args());
			return $this->result('assoc');
		}
	
		/**
		 * This function executes a query and returns the first field value of the first row.
		 * In the case that no data is returned from the query, this function returns NULL.
		 * 
		 * @access public
		 * @param  Query plus Parameters to bind.
		 * @return array
		 */
		public function getFirstFieldFromQuery()
		{
			$result = null;
			$this->executeStatement('query',func_get_args());
			$resultset = $this->result('assoc');
			// do we have a resultset?
			if (count($resultset)>0)
			{
				$firstRow = $resultset[0];
				// do we have a first row?
				if (count($firstRow)>0)
				{
					reset($firstRow);
					$result = current($firstRow);
				}
			}
			
			return $result;
		}
		
		/**
		 * Executes an insert query.
		 * 
		 * @access public
		 * @param ... Parameters to bind.
		 * @return mixed - Either a PDOStatement or the number of affected rows.
		 */
		public function insert()
		{
			$args=func_get_args();
			return $this->executeStatement('insert',$args);
		}
		
		/**
		 * Executes a select query.
		 * 
		 * @access public
		 * @return mixed - Either a PDOStatement or the number of results in the selection.
		 */
		public function select()
		{
			$args=func_get_args();
			return $this->executeStatement('select',$args);
		}
		
		/**
		 * Executes an update query.
		 * 
		 * @access public
		 * @return Mixed - Either a PDOStatement or the number of affected rows.
		 */
		public function update()
		{
			$args=func_get_args();
			return $this->executeStatement('update',$args);
		}
		
		/**
		 * Executes a delete query.
		 * 
		 * @access public
		 * @return mixed - Either a PDOStatement or the number of affected rows.
		 */
		public function delete()
		{
			$args=func_get_args();
			return $this->executeStatement('delete',$args);
		}
		
		/**
		 * Returns the last SQL Error generated from whatever query caused it.
		 * 
		 * @access public
		 * @return mixed
		 */
		public function getLastError()
		{
			return $this->lastQuery['lastError'];
		}
		
		/**
		 * Returns the affected rows count of the lasts executed query.
		 * 
		 * @access public
		 * @return mixed - NULL if there has not been a query executed. A number otherwise.
		 */
		public function getAffectedRows()
		{
			return $this->lastQuery['affectedRows'];
		}
		
		/**
		 * Returns the number of results count of the lasts executed query.
		 * 
		 * @access public
		 * @return mixed - NULL if there has not been a query executed. A number otherwise.
		 */
		public function getNumResults()
		{
			return $this->lastQuery['numResults'];
		}
		
		/**
		 * Returns the PDOStatement from the last executed query.
		 * 
		 * @access public
		 * @return mixed - NULL if there has not been a query executed. A PDOSTatement object otherwise.
		 */
		public function getLastStatement()
		{
			return $this->lastQuery['statement'];
		}
		
		/**
		 * Returns the last query string from the last executed query.
		 * 
		 * @access public
		 * @return Mixed - NULL if there has not been a query executed. A String otherwise.
		 */
		public function getLastQuery()
		{
			return $this->lastQuery['sql'];
		}
		
		/**
		 * Returns an array containing information about the last executed query.
		 * 
		 * @access public
		 * @return Array
		 */
		public function getLastQueryObject()
		{
			return $this->lastQuery;
		}
		
		/**
		 * Returns the result of the last executed query.
		 * 
		 * @access public
		 * @param $returnFormat - The format to return the results. Valid values are: 'mixed','indexed','assoc'
		 * @return Mixed - False for no result, an array of results otherwise.
		 */
		public function result($returnFormat='mixed')
		{
			$return=false;
			if ($this->lastQuery['resultSet']===false)
			{
				if ($this->lastQuery['statement'] instanceof PDOStatement)
				{
					$this->lastQuery['resultSet']=$this->lastQuery['statement']->fetchAll();
				}
			}
			if ($this->lastQuery['resultSet']!==false)
			{
				switch ($returnFormat)
				{
					case 'indexed':
					{
						$return=array();
						for ($i=0; $i<$this->lastQuery['numResults']; $i++)
						{
							$return[$i]=array();
							foreach ($this->lastQuery['resultSet'][$i] as $key=>$resultItem)
							{
								if (is_int($key))
								{
									$return[$i][]=$resultItem;
								}
							}
						}
						break;
					}
					case 'assoc':
					{
						$return=array();
						for ($i=0; $i<$this->lastQuery['numResults']; $i++)
						{
							$return[$i]=array();
							foreach ($this->lastQuery['resultSet'][$i] as $key=>$resultItem)
							{
								if (is_string($key))
								{
									$return[$i][$key]=$resultItem;
								}
							}
						}
						break;
					}
					case 'mixed':
					default:	$return=$this->lastQuery['resultSet'];
				}
			}
			return $return;
		}
	
		public function truncate($table)
		{
			return $this->query('TRUNCATE TABLE '.$table);
		}
		
		/**
		 * Starts a new transaction.
		 */
		public function beginTransaction()
		{
			$this->connection->beginTransaction();
		}
		
		/**
	     * Commits a transaction.
		 */
		public function commit()
		{
			$this->connection->commit();
		}
		
		/**
	     * Rolls back a transaction.
		 */
		public function rollBack()
		{
			$this->connection->rollBack();
		}
		
		/**
		 * Quotes and escapes a string for DB usage.
		 * @param string $str
		 */
		public function quote($str)
		{
			return $this->connection->quote($str);
		}
	}
}