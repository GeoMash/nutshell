<?php
/**
 * @package nutshell
 * @author guillaume
 */
namespace nutshell\helper
{
	/**
	 * The String helper class.
	 * 
	 * This class has many specialized methods for dealing 
	 * with strings.
	 *  
	 * @author guillaume
	 * @package nutshell
	 * @static
	 */
	class StringHelper
	{
		/**
		 * Gernates a random string with or without numeric characters.
		 * 
		 * Note that the first character is never numeric.
		 * 
		 * @access public
		 * @param Int $length - The length of the randomly generated string to generate.
		 * @param Boolean $numbers - Weather or not to include numbers.
		 * @return String - Returns a randomly generated string.
		 */
		public static function getRandomString($length=10,$numbers=true)
		{
			//Force length to be an integer.
			$length=(int)$length;
			//Generate characters in an array A-Z.
			$chars=range('a','z');
			//Include numbers?
			if ($numbers)
			{
				//Merge numbers into the character array.
				$chars=array_merge($chars,range(0,9));
			}
			//Cache the length.
			$charLength=count($chars)-1;
			//Create an empty string to return.
			$string='';
			//Set the first character flag to true.
			$first=true;
			//Loop until length is 0.
			while ($length)
			{
				//Make sure the first character is never a number.
				if ($first)
				{
					$char=0;
					while (is_numeric($char))
					{
						$char=$chars[mt_rand(0,$charLength)];
					}
					$first=false;
				}
				else
				{
					$char=$chars[mt_rand(0,$charLength)];
				}
				//Add $char to the string.
				$string.=$char;
				//Remove 1 from $length - This will cause the loop to stop if length reaches 0.
				$length--;
			}
			//Return the randomly generated string.
			return $string;
		}
		
		/**
		 * This function returns an array with strings tokenized by a separator.
		 * @param string $str
		 * @param string $separator
		 */
		public static function strTokenizer($str, $separator = ",")
		{
			$result = explode($separator, $str);
			
			// removes all empty words
			foreach($result as $key => $word)
			{
				if (strlen($word)==0)
				{
					unset($result[$key]);
				}
			}
			return $result;
		}
		
		/**
		 * This function removes CR and LF from a string. 
		 * @param string $str
		 */
		public static function removeCrLf($str)
		{
			return str_replace(array("\r","\n"), '', $str);;
		}
		
		/**
		 * This function is used to format class names. It is used by the model generator.
		 * @param string $classname
		 */
		public static function formatClassName($classname)
		{
			return ucfirst(strtolower($classname));
		}
		
		/**
		 * Takes a table name like "my_table" and reutrns a model name like "MyTable"
		 */
		public static function formatModelName($tableName)
		{
			$tableName = explode('_', $tableName);
			$modelName = '';
			foreach($tableName as $part)
			{
				$modelName .= ucfirst($part);
			}
			return $modelName;
		}
	}
}
?>