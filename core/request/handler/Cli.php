<?php
namespace nutshell\core\request\handler
{
	use nutshell\core\request\Handler;
	
	class Cli extends Handler
	{
		public function __construct() {
			$this->parse($_SERVER['argv']);
			parent::__construct();
		}
		
		protected function setupNodes()
		{
			//handled by the parse method
		}
		
		protected function setupData()
		{
			//handled by the parse method
		}
		
		private function parse($arguments) 
		{
			$this->data = array();
			$this->nodes  = array();
			
			array_shift($arguments);
			
			for($i = 0; $i < count($arguments); $i++)
			{
				$str = $arguments[$i];
				
				// --foo
				if(strlen($str) > 2 && substr($str, 0, 2) == '--')
				{
					$str = substr($str, 2);
					$parts = explode('=', $str, 2);
					$this->data[$parts[0]] = true;
					
					if(count($parts) == 2) // Has a =, so pick the second piece
					{
						$this->data[$parts[0]] = $parts[1];
					}
				}
				elseif(preg_match('/^-([a-zA-Z]*)([a-zA-Z])(=?)/', $str, $matches)) // -a
				{
					//trim the hyphen
					$str = substr($str, 1);
					$parts = explode('=', $str, 2);
					
					if($flags = $matches[1]) {
						for($j = 0; $j < count($flags); $j++) {
							$this->data[$flags[$j]] = true;
						}
					}
					
					$arg = $matches[2];
					
					$this->data[$arg] = true;
						
					if(count($parts) == 2) // Has a =, so pick the second piece
					{
						$this->data[$arg] = $parts[1];
					}
				}
			}
			
			for($j = 0; $j < count($arguments); $j++)
			{
				if(strpos($arguments[$j], '-') !== 0) {
					$this->nodes[] = $arguments[$j];
				}
			}
		}
	}
}
?>