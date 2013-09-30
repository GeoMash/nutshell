<?php
namespace nutshell\plugin\format
{
	/**
	 * This class has been ported from Leadgen v1. Its previous name is Application_Model_Format_ADF.
	 */
	class Adf extends FormatBase {
		
	    public function __construct($values = array()) {
	        parent::__construct($values);
	        
	        $this->format_name = 'ADF';
	        $this->file_extension = 'adf';
	    
	    }
	    
	    protected function writeRecord($batch) {
	        $this->writef(<<<EOL
%s

EOL
	        , implode('', $batch)
	        );
	    }
	    
	    protected function initHandler() {
	    	parent::initHandler();
	        $this->writef(<<<EOL
<?xml version="1.0" encoding="utf-8"?>
<adf>

EOL
    	    );
	    }
	    
	    protected function closeHandler() {
	        $this->writef(<<<EOL
</adf>

EOL
        	);
        	parent::closeHandler();
    	}
	}
}