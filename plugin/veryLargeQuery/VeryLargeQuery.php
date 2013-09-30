<?php
namespace nutshell\plugin\verylargequery
{
	use nutshell\Nutshell;
	use nutshell\core\plugin\Plugin;
	use nutshell\behaviour\Factory;
	use nutshell\helper\ArrayHelper;
	use nutshell\plugin\veryLargeQuery\exception\VeryLargeQueryException;

	/**
	 * This class is designed for running queries that return very large amounts of rows and memory. This class should support result sets as large as 500GB or 1TB.
	 * This class IS NOT INTENDED TO KEEP RESULT SETs in RAM. Pleas DO NOT TRY LOADING result sets INTO RAM using this class. You can use DB instead.
	 * It probably won't maky any sense using this class if you think your result sets will never be bigger than 100,000 rows (use DB nutshell plugin instead). This class is intended to be used with very large result sets.
	 * A common usage is loading data for exporting.
	   <pre>
	   	Usage Example:
	 	
	 		$lq = $this->plugin->VeryLargeQuery();
			$lq->connect('localhost', '3306', 'leadgen2', 'root', 'mypass');
			$sql = "select * from tbl_accounts";
			$resultingFiles = $lq->saveResultIntoFile($sql);
		
		Another example is sending the query result thru FTP as follows:

			$lq = $this->plugin->VeryLargeQuery();
			$lq->connect('localhost', '3306', 'leadgen2', 'root', 'rafael');
			
			$sql = "select * from tbl_accounts";
			
			$baseFilename = 'mytable'; 
			$remoteFolder = '';
			
			$user = 'sroot';
			$host = 'ftp.remoteserver.com';
			$password = 'mypassword';
			$port = 21;
			
			$format = 'csv';
			$protocol = 'ftp';
			
			$lq->transmitResult($sql, $baseFilename, $remoteFolder, $format, $protocol, $user, $host, $password, $port);
 	
		</pre>
	 */
	class VeryLargeQuery extends Plugin implements Factory
	{
		/**
		 * @var PDO
		 */
		protected $connection = null;

		/**
		 * Last executed statement (or under execution)
		 */
		protected $statement = null;
		
		/**
		 * This array is used to rename columns.
		 * Example: array('col1' => 'name', 'col2' => 'phone')
		 * @var array
		 */
		protected $aRenameColumns = array();
		
		/**
		 * This is the maximum number of rows per file. When the maximum is exceeded, a new file is created.
		 * @var int
		 */
		protected $maxNumberOfRowsPerFile = 100000;
		
		/**
		 * Indicates if field names have to be included.
		 * @var bool
		 */
		protected $includeFieldNames = false;
		
		/**
		 * The type of line break used when saving into a file.
		 * @var int
		 */
		protected $lineBreakID = 0; // WINDOWS/DEFALULT
		
		/**
		 * Character encoding used when saving files.
		 * @var string
		 */
		protected $charEncoding = '';
		
		public function init()
		{
				
		}
		
		/**
		 * Connects to a database.
		 *
		 * @param $host - Hostname or IP address to connect to.
		 * @param $port - The port number to connect with.
		 * @param $database - The name of the database which all transactions will run through.
		 * @param $username - The username to connect with.
		 * @param $password - The password to connect with.
		 * @return void
		 */
		public function connect($host='localhost',$port=null,$database=null,$username=null,$password='')
		{
			if (!empty($host))
			{
				if ($host=='localhost')$host='127.0.0.1';
				try
				{
					$this->disconnect();
					$this->dbSchema=$database;
					$this->connection=new \PDO('mysql:host='.$host.';port='.$port.';dbname='.$database,$username,$password);
					$this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
				}
				catch(PDOException $exception)
				{
					throw new VeryLargeQueryException(VeryLargeQueryException::PDO_EXCEPTION, $exception->getMessage());
				}
			}
			else
			{
				$args=func_get_args();
				throw new VeryLargeQueryException(VeryLargeQueryException::CANNOT_CONNECT_TO_DB, 'Unable to establish connection to the database.');
			}
			return true;
		}
		
		public function __destruct()
		{
			$this->disconnect();
		}
		
		/**
		 * Disconnects the database connection.
		 */
		public function disconnect()
		{
			unset($this->connection);
			unset($this->statement);
		}
		
		/**
		 * Executes a query and creates a statement so we can later fetch.
		 * @param string $sql
		 */
		public function query($sql)
		{
			unset($this->statement);
			$this->statement = $this->connection->prepare($sql,array(\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true));
			$this->statement->execute();
		}

		/**
		 * Returns only 1 row. 
		 */
		public function fetchRow()
		{
			if (isset($this->statement))
			{
				return $this->statement->fetch(\PDO::FETCH_ASSOC);
			}
			else
			{
				return null;
			}
		}
		
		/**
		 * This function sets an array containing renaming used in all rows.
		 * @param array $pRenameColumns Example: array('col1' => 'name', 'col2' => 'phone')
		 */
		public function setRenameColumns(array $pRenameColumns)
		{
			$this->aRenameColumns = $pRenameColumns;
		}
		
		/**
		 * When the parameter is true, indicates that field names are included into the output.
		 * @param bool $includeFieldNames
		 */
		public function setIncludeFieldNames($pIncludeFieldNames)
		{
			$this->includeFieldNames = $pIncludeFieldNames;
		}
		
		/**
		 * Sets the character encoding when saving a file.
		 * @param unknown_type $str
		 */
		public function setcharEncoding($str)
		{
			$this->charEncoding = $str;
		}
		
		/**
		 * Sets the type of line break
		 * @param int $lineBreakID
		 */
		public function setLineBreak($lineBreakID)
		{
			$this->lineBreakID = $lineBreakID;
		}

		/**
		 * This function does:
		 	1) runs a query.
		 	2) export the result in the given format to disk.
		 	3) Transmit (transfer) all created files to a remote server.
		 	4) Clears (deletes) all temp files.
		 * @param string $sql
		 * @param string $baseFilename
		 * @param string $remoteFolder
		 * @param string $format Such as CSV, XML, JSon
		 * @param string $protocol Such as ftp, scp, ...
		 * @param string $user
		 * @param string $host
		 * @param string $password
		 * @param int $port
		 */
		public function transmitResult($sql, $baseFilename, $remoteFolder, $format, $protocol, $user, $host = '127.0.0.1', $password = '', $port = 0)
		{
			$aFiles = $this->saveResultIntoFile($sql, $baseFilename, $format);
			
			$oTransfer = $this->plugin->Transfer()->$protocol();
			
			// connection settings
			$aSettings = array('host'=>$host);
			
			$port = (int) $port;
			
			if ($port>0) $aSettings['port'] = $port;
			if (strlen($user)>0) $aSettings['user'] = $user;
			if (strlen($password)>0) $aSettings['password'] = $password;
			
			// apply settings
			$oTransfer->setOptions($aSettings);
			
			try 
			{
				foreach($aFiles as $localfile)
				{
					if (strlen($remoteFolder>0))
					{
						$remoteFile = "$remoteFolder/".basename($localfile);
					}
					else
					{
						$remoteFile = basename($localfile);
					}
					
					$this->plugin->Logger()->info("Sending $localfile ($remoteFile) as $format thru $protocol to $host.");
					$oTransfer->putRetrying($localfile, $remoteFile);
					$this->plugin->Logger()->info("Sent $localfile ($remoteFile) as $format thru $protocol to $host.");
					
					// delete the file after uploading
					unlink($localfile);
				}
				$oTransfer->close();				
			} catch (NutshellException $e) {
				foreach($aFiles as $localfile)
				{
					if (file_exists ( $localfile ) )
					{
						unlink($localfile);
					}
				}
				$oTransfer->close();
				throw $e;
			}	
		}
		
		/**
		 * This function runs a query and stores the result in a file.
		 * It returns an array with all created file names.
		 * If a base file name isn't given, files will be placed in the default folder.
		 * @param string $sql
		 * @param string $filename
		 * @return array.
		 */
		public function saveResultIntoFile($sql, $basefilename = '', $format = 'csv')
		{
			$result = array();
			
			unset($this->statement);
			
			$openFile = false;
			
			$format = strtolower($format);
			
			$oFormat = $this->plugin->Format($format);
			
			$oFormat->setLineBreakFromID($this->lineBreakID);
			
			if (strlen($this->charEncoding)>0)
			{
				$oFormat->setEncoding($this->charEncoding);
			}

			// executes the query
			$this->query($sql);
			
			$rowCnt = 0;
			
			$fileCnt = 0;
			
			while ($row = $this->fetchRow())
			{
				ArrayHelper::renameFields($row, $this->aRenameColumns);
				
				// don't we have an open out put file? (if not, we have to open it)
				if (!$openFile)
				{
					if (strlen($basefilename)>0)
					{
						$file_name = $basefilename . '-' . $fileCnt . '.' . $format;
						$oFormat->new_file($file_name);
					}
					else
					{
						$oFormat->new_file();
					}
					
					if ($this->includeFieldNames)
					{
						$oFormat->processRecord(array_keys($row));
					}
					
					$openFile = true;
				}
				
				$rowCnt++;
				$oFormat->processRecord($row);
				
				// is the file too big?
				if ($rowCnt>$this->maxNumberOfRowsPerFile)
				{
					$fileCnt++;
					$oFormat->close_file();
					$result[] = $oFormat->getWork_file_name();
					$openFile = false;
				}
			}
			
			if ($openFile)
			{
				$oFormat->close_file();
				$result[] = $oFormat->getWork_file_name();
			}
			unset($this->statement);
			
			return $result;
		}
	}
}