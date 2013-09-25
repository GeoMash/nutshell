<?php
namespace nutshell\plugin\format
{
	use nutshell\plugin\format\exception\FormatException;
	
	/** 
	 * This class is a base for (is extended by) all formating classes.
	 */
	class FormatBase
	{
		/**
		 * Prefix used when creating new files.
		 * @var string
		 */
		protected $work_file_prefix = 'nuttmp_';
		
		/**
		 * Folder used to save files when calling new_file() method.
		 */
		protected $work_base_dir = '';
	
		protected $format_name;
	
		protected $_work_file_name;
	
		protected $_file_out;
	
		protected $_file_handle = null;
	
		protected $file_extension;
	
		protected $_headers;
		
		/**
		 * Used by DSV and CSV.
		 * @var string
		 */
		protected $_delimiter = null;
		
		/**
		 * Used by DSV and CSV.
		 * @var string
		 */
		protected $_delimiter_escape = false;
		
		/**
		 * To be used by DSV, CSV, ...
		 * @var string
		 */
		protected $_text_qualifier = '"';
		
		/**
		 * To be used in DSV, CSV, ...
		 * @var unknown_type
		 */
		protected $_escape_with = '"';
		
		const LINE_BREAK_WIN = 'CRLF';
	
		const LINE_BREAK_UNIX = 'LF';
		
		const LINE_BREAK_WIN_ID = 0;
		
		const LINE_BREAK_UNIX_ID = 1;
		
		protected $supportedEncodings = array 
		(
			'UCS-4',
			'UCS-4BE',
			'UCS-4LE',
			'UCS-2',
			'UCS-2BE',
			'UCS-2LE',
			'UTF-32',
			'UTF-32BE',
			'UTF-32LE',
			'UTF-16',
			'UTF-16BE',
			'UTF-16LE',
			'UTF-7',
			'UTF7-IMAP',
			'UTF-8',
			'ASCII',
			'EUC-JP',
			'SJIS',
			'eucJP-win',
			'SJIS-win',
			'ISO-2022-JP',
			'ISO-2022-JP-MS',
			'CP932',
			'CP51932',
			'JIS',
			'JIS-ms',
			'CP50220',
			'CP50220raw',
			'CP50221',
			'CP50222',
			'ISO-8859-1',
			'ISO-8859-2',
			'ISO-8859-3',
			'ISO-8859-4',
			'ISO-8859-5',
			'ISO-8859-6',
			'ISO-8859-7',
			'ISO-8859-8',
			'ISO-8859-9',
			'ISO-8859-10',
			'ISO-8859-13',
			'ISO-8859-14',
			'ISO-8859-15',
			'byte2be',
			'byte2le',
			'byte4be',
			'byte4le',
			'BASE64',
			'HTML-ENTITIES',
			'7bit',
			'8bit',
			'EUC-CN',
			'CP936',
			'HZ',
			'EUC-TW',
			'CP950',
			'BIG-5',
			'EUC-KR',
			'UHC',
			'ISO-2022-KR',
			'Windows-1251',
			'Windows-1252',
			'CP866',
			'KOI8-R'
		);
		
		protected $encoding = 'UTF-8';
		
		/**
		 * Array that maps ID to line break value
		 * @var array
		 */
		protected $aLineBreaks = array
		(
			self::LINE_BREAK_WIN_ID 	=> self::LINE_BREAK_WIN,
			self::LINE_BREAK_UNIX_ID 	=> self::LINE_BREAK_UNIX
		);
	
		protected $_format_config_line_break = self::LINE_BREAK_UNIX;
	
		protected $_format_config_file_name_override;
	
		protected static $_line_breaks = null;
	
		protected static $_formats = null;
		
		public function __construct($values = array()) {
			$this->work_base_dir = APP_HOME . 'cache' . _DS_ ;
		}
	
		public static function getAvailableLineBreaks() {
			if (is_null(self::$_line_breaks)) {
				self::$_line_breaks = array();
				self::$_line_breaks[self::LINE_BREAK_UNIX] = 'Unix (LF)';
				self::$_line_breaks[self::LINE_BREAK_WIN] = 'Windows (CRLF)';
			}
			 
			return array_merge(array(), self::$_line_breaks);
		}
	
		public function getHeaders()
		{
			return $this->_headers;
		}
		 
		public function setHeaders($value)
		{
			$this->_headers = $value;
			return $this;
		}
		 
		public function getFormat_class()
		{
			return get_class($this);
		}
		 
		protected function setFormat_class($value)
		{
			//ignore
			return $this;
		}
		 
		public function getFormat_name()
		{
			return $this->format_name;
		}
		 
		public function setFormat_name($value)
		{
			$this->format_name = $value;
			return $this;
		}
		
		/**
		 * This function sets the character encoding when saving.
		 * @param string $str
		 * @throws FormatException
		 */
		public function setEncoding($str)
		{
			if (in_array($str, $this->supportedEncodings))
			{
				$this->encoding = $str;
			}
			else
			{
				throw new FormatException(FormatException::INVALID_ENCODING, "$str isn't a valid character encoding.");
			}
		}

		/**
		 * Sets the delimiter. (Useful for CSV and DSV).
		 * @param string $delimiter
		 */
		public function setDelimiter($delimiter)
		{
			$this->_delimiter = $delimiter;
			$this->_delimiter_escape = true;
		}
		
		/**
		 * This function sets the line break from its ID.
		 * @param integer $id
		 */
		public function setLineBreakFromID($id)
		{
			$this->setFormat_config_line_break($this->aLineBreaks[$id]);
		}
		
		/**
		 * This function sets the text qualifier (identifies that what is inside is a string).
		 * @param string $str
		 */
		public function setTextQualifier($str)
		{
			$this->_text_qualifier = '"';
		}
		
		/**
		 * Escapes the text qualifier. In CSV it's ".
		 * @param string $str
		 */
		public function setEscapeWith($str)
		{
			$this->_escape_with = '"';
		}
		
		public function setFormat_config_line_break($value) {
			if (is_null($value) || empty($value)) {
				$value = self::LINE_BREAK_UNIX;
			}
			if (array_key_exists($value,self::getAvailableLineBreaks())) {
				$this->_format_config_line_break = $value;
			} else {
				throw new FormatException(FormatException::INVALID_PARAMETER, 'Invalid parameter');
			}
			return $this;
		}
		 
		public function getFormat_config_line_break() {
			return $this->_format_config_line_break;
		}
		 
		public function getFormat_config_file_name_override()
		{
			return $this->_format_config_file_name_override;
		}
		 
		public function setFormat_config_file_name_override($value)
		{
			$this->_format_config_file_name_override = $value;
			return $this;
		}
		 
		public function getWork_file_name()
		{
			return $this->_work_file_name;
		}
		 
		protected function setWork_file_name($value)
		{
			$this->_work_file_name = $value;
			return $this;
		}
		 
		public function getFile_out()
		{
			return $this->_file_out;
		}
		
		public function getSupportedEncodings()
		{
			return $this->supportedEncodings;
		}
		 
		public function setFile_out($value)
		{
			if (preg_match('/\.[a-zA-Z0-9]{3,4}$/', $value)) {
				//the user defined its own file extension
				$this->_file_out = $value;
			} else {
				//use the default file extension
				$this->_file_out = sprintf('%s.%s', $value, $this->file_extension);
			}
			return $this;
		}
		 
		protected function getFile_handle()
		{
			return $this->_file_handle;
		}
		 
		protected function setFile_handle($value)
		{
			$this->_file_handle = $value;
			return $this;
		}
		 
		public function getFile_extension()
		{
			return $this->file_extension;
		}
		 
		protected function setFile_extension($value)
		{
			$this->file_extension = $value;
			return $this;
		}
		 
		/*
		 * General methods for file output
		*/
		 
		protected final function open_file() {
			// in the case that there is an open filed, closes it.
			$this->close_file();
			
			$this->file_handle = fopen($this->_work_file_name, 'w');
			if (!$this->file_handle) {
				throw new FormatException(FormatException::CANNOT_OPEN_FILE, "Can't open the file {$this->_work_file_name} for edition.");
			}
			$this->initHandler();
		}
		
		/**
		 * This function sets the base (work) directory (folder).
		 * @param unknown_type $pDir
		 */
		public final function set_base_dir($pDir)
		{
			$this->work_base_dir = $pDir;
		}
		 
		public final function new_file($filename='') 
		{
			if (strlen($filename)>0)
			{
				$fileNameWithPreSpace = " ".$filename;
				if ( (strpos($fileNameWithPreSpace,"/")>0) || (strpos($fileNameWithPreSpace,"\\")>0) || (strpos($fileNameWithPreSpace,_DS_)>0) )
				{
					$this->_work_file_name = $filename;
				}
				else
				{
					$this->_work_file_name = $this->work_base_dir.$filename;
				}				
			}
			else
			{
				$this->_work_file_name = $this->work_base_dir . $this->work_file_prefix . mt_rand() . '.' . $this->file_extension ;
			}
			
			$this->open_file();
		}
		 
		public final function process(array $records) {
			foreach($records as $record) {
				$this->processRecord($record);
			}
		}

		/**
		 * This method processes only one record.
		 * @param array $record
		 */
		public final function processRecord(array $record) {
			$this->writeRecord($record);
		}
		 
		public final function close_file() {
			if (isset($this->file_handle))
			{
				if ($this->file_handle) 
				{
					$this->closeHandler();
					fclose($this->file_handle);
					unset($this->file_handle);
				}
			}
		}
		
		/**
		 * This funcion deletes the file from disk.
		 * If the file is open, it closes first.
		 */
		public final function delete()
		{
			$this->close_file();
			unlink($this->_work_file_name);
		}
		 
		public final function read()
		{
			$this->close_file();
			return file_get_contents($this->getWork_file_name());
		}
		
		/*
		 * Methods to override in implementations
		 */
		
		/**
		 * Utility method to write a formatted string to the currently opened file
		 */
		protected function writef() {
			$args = func_get_args();
			if (count($args)>1)
			{
				$content = call_user_func_array('sprintf', $args);
			}
			else
			{
				$content=$args[0];
			}
			$content = $this->updateLineBreaks($this->getFormat_config_line_break(), $content);
			
			if ($this->encoding != 'UTF-8')
			{
				$content = mb_convert_encoding ( $content ,  $this->encoding, 'UTF-8' );
			}
			fwrite($this->file_handle, $content);
		}
		
		/**
		 * Used to encode a record into a string (without writting to a file).
		 * @param array $record
		 * @return string
		 */
		public function encode($record)
		{
			throw new FormatException(FormatException::ENCODE_NOT_IMPLEMENTED, "Format->encode has not been implemented in this format");
		}
		 
		protected static function updateLineBreaks($toStyle, $txt) {
			switch($toStyle) {
				case self::LINE_BREAK_WIN:
					return preg_replace('/\r\n|\r|\n/', "\r\n", $txt);
				case self::LINE_BREAK_UNIX:
				default:
					return str_replace(array("\r\n", "\r"), array("\n", "\n"), $txt);
			}
		}
		 
		protected function writeRecord($batch) {
			$this->writef(serialize($batch));
		}
		 
		protected function initHandler() {
			//default do nothing
		}
		 
		protected function closeHandler() {
			//default do nothing
		}
		 
		public function __toString() {
			return $this->getFormat_name();
		}
		 
		public function getFormat_config() {
			$array = array();
			$this->push_config($array);
			return $array;
		}
		 
		public function setFormat_config($values) {
			if(is_string($values)) {
				$values = json_decode($values, true);
			}
			$this->setAll($values);
			return $this;
		}
		 
		public function toArray() {
			$array = array();
			$array['format_class'] = $this->format_class;
			$array['format_name'] = $this->format_name;
				
			$this->push_config($array);
			return $array;
		}
		 
		protected function push_config(array &$data) {
			$data['format_config_line_break'] = $this->format_config_line_break;
			$data['format_config_file_name_override'] = $this->format_config_file_name_override;
		}
	}
}