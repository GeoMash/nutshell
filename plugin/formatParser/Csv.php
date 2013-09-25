<?php
namespace nutshell\plugin\formatParser
{
	class Csv extends Dsv {
	
		protected $fieldDelimiter = ',';

		protected $stringDelimiter = '"';

		protected $escapeMode = self::ESCAPE_MODE_DOUBLING;

		public function __construct($values = array()) {
			parent::__construct($values);
		}
	}
}