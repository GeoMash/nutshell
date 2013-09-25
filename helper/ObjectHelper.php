<?php
/**
 * @package nutshell
 * @author guillaume
 */
namespace nutshell\helper
{
	use nutshell\core\exception\HelperException;

	use \ArrayObject;
	
	/**
	 * The Object helper class.
	 * 
	 * This class has many specialized methods for dealing 
	 * with classes and objects.
	 *  
	 * @author guillaume
	 * @package nutshell
	 * @static
	 */
	class ObjectHelper
	{
		/**
		 * @var Integer
		 */
		const CLASSINFO_NAMESPACE = 1;
		/**
		 * @var Integer
		 */
		const CLASSINFO_CLASSNAME = 2;
		/**
		 * @var String
		 */
		const CLASSINFO_VAL_NAMESPACE = 'namespace';
		/**
		 * @var String
		 */
		const CLASSINFO_VAL_CLASSNAME = 'classname';
		
		
		/**
		 * Gets the base class name for a given class.
		 * 
		 * @access public
		 * @static
		 * @param Mixed $className - Either the class name or an instance of the class.
		 * @return String - The base class name
		 */
		public static function getBaseClassName($className)
		{
			if (is_object($className))
			{
				$className=get_class($className);
			}
			return self::getClassInfo($className, self::CLASSINFO_CLASSNAME);
		}
		
		/**
		 * Gets the namespace for a given class.
		 * 
		 * @access public
		 * @static
		 * @param Mixed $className - Either the class name or an instance of the class.
		 * @return String
		 */
		public static function getNamespace($className)
		{
			if (is_object($className))
			{
				$className=get_class($className);
			}
			return self::getClassInfo($className, self::CLASSINFO_NAMESPACE);
		}
		
		/**
		 * Locates the source directory for specified class.
		 * Note: it does only work with native plugins.
		 * 
		 * @access public
		 * @static
		 * @param Mixed $className - Either the class name or an instance of the class.
		 * @return String the physical path to the directory containing the class. 
		 */
		public static function getClassPath($className)
		{
			if (is_object($className))
			{
				$className=get_class($className);
			}
			$root=(strstr($className,'nutshell'))?NS_HOME:APP_HOME;
			return $root . str_replace(
				array(
					'application\\',
					'nutshell\\', 
					'\\'
				), 
				array(
					'',
					'', 
					_DS_
				),  
				self::getNamespace($className)
			) . _DS_;
		}
		
		/**
		 * getClassInfo returns an associative array containing information about the $classname
		 * 
		 * @access public
		 * @static
		 * @param String $className the class being investigated
		 * @param int $options You can specify which elements are returned with optional parameter options. It composes from ObjectHelper::CLASSINFO_NAMESPACE, ObjectHelper::CLASSINFO_CLASSNAME. It defaults to return all elements.
		 * @return array an associative array of information corresponding to the following keys: 
		 * ObjectHelper::CLASSINFO_VAL_NAMESPACE, ObjectHelper::CLASSINFO_VAL_CLASSNAME. 
		 * In case the string cannot be parsed, null is returned.
		 * 		
		 */
		public static function getClassInfo($className, $options = null)
		{
			if($options === null) {
				$options = self::CLASSINFO_NAMESPACE | self::CLASSINFO_CLASSNAME;
			}
			//init
			$buff = array(
				self::CLASSINFO_VAL_NAMESPACE => null, 
				self::CLASSINFO_VAL_CLASSNAME => null
			);
			
			//checking the pattern
			if(preg_match('/^((?:[^\\\\]+\\\\)*)([^\\\\]+)$/', $className, $matches))
			{
				$buff[self::CLASSINFO_VAL_NAMESPACE] = rtrim($matches[1], '\\');
				$buff[self::CLASSINFO_VAL_CLASSNAME] = $matches[2];
			}
			
			//composing the output
			$result = array();
			if($options & self::CLASSINFO_NAMESPACE)
			{
				$result[self::CLASSINFO_VAL_NAMESPACE] = $buff[self::CLASSINFO_VAL_NAMESPACE];
			}
			if($options & self::CLASSINFO_CLASSNAME)
			{
				$result[self::CLASSINFO_VAL_CLASSNAME] = $buff[self::CLASSINFO_VAL_CLASSNAME];
			}
			
			//if more than one result is required, return an array
			if(count($result) > 1) 
			{
				return $result;
			} 
			else
			{
				//otherwise return only the required property
				$keys = array_keys($result);
				return $result[$keys[0]];
			}
		}
		
		/**
		 * This method recursively encondes an array or an object into an ArrayObject.
		 * @param mixed $arrayObject
		 * @throws HelperException
		 */
		public static function encodeIntoArrayObject($objectOrArray, $maxRecursionLevel = 5, $recursionLevel = 0)
		{
			// prevents infinite recursion
			if ($recursionLevel>$maxRecursionLevel)
			{
				return new \ArrayObject($objectOrArray);
			}
			
			if (is_array($objectOrArray) || (is_object($objectOrArray)))
			{
				$result = new \ArrayObject($objectOrArray);
				
				foreach($result as $key=>$value)
				{
					if (is_array($value) || (is_object($value)))
					{
						$result[$key] = self::encodeIntoArrayObject($value, $maxRecursionLevel, $recursionLevel+1);
					}
				}
				return $result;
			}
			else
			{
				throw new HelperException(HelperException::INVALID_DATA_TYPE, "encodeIntoObjectArray expects an array or an object.");
			}
		}
	}
}