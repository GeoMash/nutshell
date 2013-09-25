<?php
namespace nutshell\plugin\format
{
	/**
	 * This class has been ported from Leadgen v1. Its previous name is Application_Model_Format_JSON.
	 */
	class Json extends FormatBase {
	    
		const RECORD_SEPARATOR = ',';
		
		protected $_count = 0;
		
	    public function __construct($values = array()) 
	    {
	        parent::__construct($values);
	        
	        $this->format_name = 'JSON';
	        $this->file_extension = 'json';
	    }
	    
   	    public function encode($record, $includeHeader = false)
	    {
	    	$s =
<<<EOL
%s%s
EOL;
	    	return
				($includeHeader ? $this->getHeader() : '').
	    		sprintf($s, $this->_count > 0 ? self::RECORD_SEPARATOR . "\n" : '', json_encode($record) ).
	    		($includeHeader ? $this->getFooter() : '')
	    	;
	    }

		
	    protected function writeRecord($batch) 
	    {
	        $this->writef( $this->encode($batch, false) );
	        $this->_count++;
	    }
	    
	    protected function getHeader()
	    {
	    	$this->_count = 0;
	    	return
<<<EOL
[

EOL;
	    }
	     
	    protected function getFooter()
	    {
	    	return
<<<EOL

]
EOL;
	    }
	    
	    protected function initHandler() 
	    {
	    	parent::initHandler();
	        $this->writef( $this->getHeader() );
    	}
	    
	    protected function closeHandler() 
	    {
	        $this->writef( $this->getFooter() );
	        parent::closeHandler();
	    }
	    
	    protected function buildRecordString() 
	    {
	    	
	    }
	}
}