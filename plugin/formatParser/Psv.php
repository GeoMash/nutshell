<?php
namespace nutshell\plugin\formatParser
{
	class Psv extends Dsv {
	
		protected $fieldDelimiter = '|';

		protected $stringDelimiter = '"';

		protected $escapeMode = self::ESCAPE_MODE_CHARACTER;

		public function __construct($values = array()) {
			parent::__construct($values);
		}
	}
}