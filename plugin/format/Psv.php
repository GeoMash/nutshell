<?php
namespace nutshell\plugin\format
{
	/**
	 * This class has been ported from Leadgen v1. Its previous name is Application_Model_Format_DSV_PSV.
	 */
	class Psv extends Dsv {
	
		protected $_delimiter = '|';
	
		public function __construct($values = array()) {
			parent::__construct($values);
			$this->_delimiter_escape = true;
	
			$this->format_name = 'PSV';
			$this->file_extension = 'psv';
		}
	}
}