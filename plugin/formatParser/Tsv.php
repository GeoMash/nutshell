<?php
namespace nutshell\plugin\formatParser
{
	class Tsv extends Dsv {
	
	    protected $fieldDelimiter = "\t";

		protected $stringDelimiter = '"';

		protected $escapeMode = self::ESCAPE_MODE_CHARACTER;

		public function __construct($values = array()) {
			parent::__construct($values);
		}
	}
}