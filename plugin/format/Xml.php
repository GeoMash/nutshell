<?php
namespace nutshell\plugin\format
{
	/**
	 * This class has been ported from Leadgen v1. Its previous name is Application_Model_Format_XML.
	 */
	class Xml extends FormatBase {
	    
		const ROOT_ELEMENT = 'xml';
		
		const LEAD_ELEMENT = 'record';
		
	    public function __construct($values = array()) {
	        parent::__construct($values);
	        
	        $this->format_name = 'XML';
	        $this->file_extension = 'xml';
	    }
	    
	    public function encode($record, $includeHeader = false)
	    {
	    	$s = <<<EOL
<%1\$s>
%2\$s</%1\$s>

EOL;
	    	return
				($includeHeader ? $this->getHeader() : '').
	    		sprintf($s, self::LEAD_ELEMENT, $this->buildRecordString($record) ).
	    		($includeHeader ? $this->getFooter() : '')
	    	;
	    }
	    
	    protected function getHeader()
	    {
	    	$s = 
<<<EOL
<?xml version="1.0" encoding="UTF-8"?>
<%s>

EOL;
	    	return sprintf( $s, self::ROOT_ELEMENT );
	    }
	    
	    protected function getFooter()
	    {
			$s = 
<<<EOL
</%s>
EOL;
	    	return sprintf( $s, self::ROOT_ELEMENT );
	    }
		
	    protected function writeRecord($batch) 
	    {
	        $this->writef( $this->encode($batch,false) );
	    }
	    
	    protected function initHandler() {
	    	parent::initHandler();
	        $this->writef( $this->getHeader() );
	    }
	    
	    protected function closeHandler() {
	        $this->writef( $this->getFooter() );
	        parent::closeHandler();
	    }
	    
	    protected function buildRecordString($batch) {
	    	$text = '';
	    	
	    	foreach($batch as $key => $value) {
	    		$s = 
<<<EOL
	<%1\$s><![CDATA[%2\$s]]></%1\$s>

EOL;
	    		$text .= sprintf($s, $key, $value);
	    	}
	    	
	    	return $text;
	    }
	}
}