<?php
/**
 * This namespace has been created to isolate php eval function execution in a restricted scope.
 * This class intentionally DOES NOT MAKE ANY REFERENCE OR USE NUTSHELL to keep the eval safe.
 * Imagine if somehow an evaluated function accesses nutshell and then config and then database config and then ...
 * As the name suggests, SCOPE here has to be very STRICT.
 */
namespace nutshell\plugin\format\strictTemplate
{
	
	/**
	 * This class CAN NOT reuse nutshell code to keep the eval safe.
	 */
	class StrictTemplate
	{
		private $keyVals	     = array();
		
		public $caseInsensitive  = true;
		
		public function setKeyVal($key,$val)
		{
			if ($this->caseInsensitive)
			{
				$key = strtolower($key);
			}
			$this->keyVals[$key]=$val;
		}
		
		public function get($key)
		{
			if ($this->caseInsensitive)
			{
				$key = strtolower($key);
			}

			if (isset($this->keyVals[$key]))
			{
				return $this->keyVals[$key];
			}
			else
			{
				return null;
			}
		}
		
		public function __get($key)
		{
			if ($this->caseInsensitive)
			{
				$key = strtolower($key);
			}

			if (isset($this->keyVals[$key]))
			{
				print $this->keyVals[$key];
			}
			else
			{
				print '';
			}
		}
	}
	
	/**
	 * Replaces {varName} by value AND executes an "eval($code)" call.
	 * @param string $code
	 */
	function evalInStrictNameSpace($code, array $args = null, $caseInsensitive = true)
	{
		// creates a tpl object that can be seen by the eval execution.
		$tpl = new StrictTemplate();
		
		$tpl->caseInsensitive = $caseInsensitive;
		
		if (isset($args))
		{
			$mayHaveSimpleVars = strpos(' '.$code, '{');
			
			foreach($args as $varName=>$value)
			{
				$tpl->setKeyVal($varName, $value);
			}
			
			if ($mayHaveSimpleVars)
			{
				foreach($args as $varName=>$value)
				{
					if ($caseInsensitive)
					{
						$code = str_ireplace('{'.$varName.'}', $value, $code);
					}
					else
					{
						$code = str_replace('{'.$varName.'}', $value, $code);
					}
				}
			}
		}
		
		eval($code);
	}
}