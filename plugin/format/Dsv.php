<?php
namespace nutshell\plugin\format
{
	/**
	 * This class has been ported from Leadgen v1. It's previous name is Application_Model_Format_DSV.
	 */
	class Dsv extends FormatBase {
		
		/**
		 * Encodes a record into a string.
		 * @param array $record
		 */
		public function encode($record)
		{
			if($this->_delimiter_escape) {
	    		$this->escapeDelimiter($record);
	    	}
	    	
			return sprintf
			(
<<<EOL
%s

EOL
			,implode($this->_delimiter, $record)
			);
		}
		
	    protected function writeRecord($batch) {
	        $this->writef( $this->encode($batch) );
	    }
	
	    protected function initHandler() {
	    	parent::initHandler();
	    	if (!is_null($this->getHeaders())) {
	    		$this->writeRecord($this->getHeaders());
	    	}
	    }
	
		protected function needsEscape($value) {
			return strpos($value, $this->_delimiter) !== false || strpos($value, "\n") !== false;
		}
	
		protected function escapeDelimiter(&$batch) {
	        foreach($batch as $i => $value) {
	        	if ($this->needsEscape($value)) {
	                $batch[$i] = $this->_text_qualifier . str_replace($this->_text_qualifier, $this->_escape_with . $this->_text_qualifier, $value) . $this->_text_qualifier;
	        	}
	        }
	    }
	}
}