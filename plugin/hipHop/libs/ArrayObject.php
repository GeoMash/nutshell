<?php
/**
 * This class has been written to give HipHop PHP
 * ArrayObject support.
 * 
 * @author Timothy Chandler <tim.chandler@spinifexgroup.com>
 * @since 21/03/2011
 * @version 1.0
 * @package nutshell-plugin
 * 
 * 
 * @todo - Support setting with [].
 * @todo - Fully support indexes.
 * 
 */
if (!class_exists('ArrayObject'))
{
	/**
	 * @package nutshell-plugin
	 */
	class ArrayObject implements IteratorAggregate,ArrayAccess,Serializable,Countable
	{
		private $__private_data=array();
		/**
		 * Construct a new array object.
		 * 
		 * @access public
		 * @param Array $data - The input parameter accepts an array or an Object.
		 * @param Int $flags - Flags to control the behaviour of the ArrayObject object.
		 * @param String $__iteratorClass - Specify the class that will be used for iteration of the ArrayObject object. ArrayIterator is the default class used.
		 * @return ArrayObject - An instance of ArrayObject.
		 */
		public function __construct($data,$flags=self::STD_PROP_LIST,$iteratorClass='ArrayIterator')
		{
			if (is_array($data) || is_object($data))
			{
				$this->setFlags($flags);
				$this->setData($data);
			}
			else
			{
				throw new NutshellException('ArrayObject expects parameter 1 to be an Array or Object.');
			}
		}
		
		private function normalizeData($data)
		{
			if (is_array($data) || is_object($data))
			{
				if ($this->__private_flags==self::STD_PROP_LIST)
				{
					$this->__private_data=array();
					foreach ($data as $key=>$val)
					{
						$this->__private_data[$key]=$val;
					}
				}
				else if ($this->__private_flags==self::ARRAY_AS_PROPS)
				{
					$this->__private_data=new stdClass();
					foreach ($data as $key=>$val)
					{
						$this->__private_data->{$key}=$val;
					}
				}
				else
				{
					throw new NutshellException('Unable to set data based on the current set flag. The flag is "'.$this->__private_flags.'".');
				}
			}
			else
			{
				throw new NutshellException('Invalid data set. The data set must be an array or object.');
			}
			return $this;
		}
		
		private function setData($data)
		{
			$this->normalizeData($data);
			$this->__private_length		=count($this->__private_data);
			return $this;
		}
		
		// Magic to make it all work.
		
		public function __set($key,$value)
		{
			$this->offsetSet($key,$value);
		}
		
		public function __get($key)
		{
			return $this->offsetGet($key);
		}
		
		public function __isset($key)
		{
			return $this->offsetExists($key);
		}
		
		public function __unset($key)
		{
			$this->offsetUnset($key);
		}
		
		/*** [START ArrayObject specifics] ***/
		
		private $__private_iteratorClass	='ArrayIterator';
		private $__private_flags			=self::STD_PROP_LIST;
		
		/**
		 * @var Int - Properties of the object have their normal functionality when accessed as list (var_dump, foreach, etc.).
		 */
		const STD_PROP_LIST		=1;
		/**
		 * @var Int - Entries can be accessed as properties (read and write).
		 */
		const ARRAY_AS_PROPS	=2;
		
		// FLAGS
		
		/**
		 * Sets the behavior flags.
		 * 
		 * Set the flags that change the behavior of the ArrayObject.
		 * 
		 * @access public
		 * @param Int $flags - See http://www.php.net/manual/en/arrayobject.setflags.php
		 */
		public function setFlags($flags)
		{
			if ($flags==self::STD_PROP_LIST)
			{
				$this->__private_flags=self::STD_PROP_LIST;
			}
			else if ($flags==self::ARRAY_AS_PROPS)
			{
				$this->__private_flags=self::ARRAY_AS_PROPS;
			}
			else
			{
				throw new NutshellException('Invalid Flag.');
			}
			if ($this->count())
			{
				$this->normalizeData($this->__private_data);
			}
		}
		
		/**
		 * Gets the behavior flags.
		 * 
		 * Gets the behavior flags of the ArrayObject. See the ArrayObject::setFlags method for a list of the available flags.
		 * 
		 * @access public
		 * @return Int - The behavior flags of the ArrayObject.
		 */
		public function getFlags()
		{
			return $this->__private_flags;
		}
		
		// SORTING
		
		public function asort()
		{
			asort($this->__private_data);
		}
		
		public function ksort()
		{
			ksort($this->__private_data);
		}
		
		public function natcasesort()
		{
			natcasesort($this->__private_data);
		}
		
		public function natsort()
		{
			natsort($this->__private_data);
		}
		
		public function uasort($callback)
		{
			uasort($this->__private_data,$callback);
		}
		
		public function uksort($callback)
		{
			uksort($this->__private_data,$callback);
		}
		
		// ITERATOR CLASS
		
		/**
		 * Sets the iterator classname for the ArrayObject.
		 * 
		 * @access public
		 * @param String $iteratorClass - The classname of the array iterator to use when iterating over this object.
		 * @return - No value is returned.
		 */
		public function setIteratorClass($iteratorClass)
		{
			$this->__private_iteratorClass=$iteratorClass;
		}
		
		/**
		 * Gets the iterator classname for the ArrayObject.
		 * 
		 * @access public
		 * @return String - The iterator class name that is used to iterate over this object.
		 */
		public function getIteratorClass()
		{
			return $this->__private_iteratorClass;
		}
		
		// ARRAY SPECIFICS
		
		/**
		 * Appends a new value as the last element.
		 * 
		 * @access public
		 * @param Mixed $value - The value being appended.
		 * @return - No value is returned.
		 */
		public function append($value)
		{
			$this->__private_data[]=$value;
		}
		
		/**
		 * Exchange the array for another one.
		 * 
		 * @access public
		 * @param Mixed $array - The new array or object to exchange with the current array.
		 * @return Array - The old array.
		 */
		public function exchangeArray($array)
		{
			if (is_array($array) || is_object($array))
			{
				$old=$this->__private_data;
				$this->setData($array);
				return $old;
			}
			else
			{
				throw new NutshellException('ArrayObject::exchangeArray expects parameter 1 to be an Array or Object.');
			}
		}
		
		/**
		 * Creates a copy of the ArrayObject.
		 * 
		 * @access public
		 * @return Array - A copy of the array. When the ArrayObject refers to an object an array of the public properties of that object will be returned.
		 */
		public function getArrayCopy()
		{
			return $this->__private_data;
		}
		
		/*** [END ArrayObject specifics] ***/
		
		/*** [START Implementation of IteratorAggregate & Traversable] ***/
		
		public function getIterator()
		{
			return new $this->__private_iteratorClass($this);
		}
		
		/*** [END Implementation of IteratorAggregate & Traversable] ***/
		/*** [START Implementation of ArrayAccess] ***/
		
		public function offsetSet($offset,$value)
		{
			if ($this->__private_flags==self::STD_PROP_LIST)
			{
				if (is_null($offset))
				{
					$this->__private_data[$offset][]=$value;
				}
				else
				{
					$this->__private_data[$offset]=$value;
				}
			}
			else if ($this->__private_flags==self::ARRAY_AS_PROPS)
			{
				if (is_null($offset))
				{
					throw new NutshellException('Sorry, unable to set values like this. This feature is currently not implemented.');
				}
				else
				{
					$this->__private_data->{$offset}=$value;
				}
			}
			else
			{
				throw new NutshellException('Unable to set data based on the current set flag. The flag is "'.$this->__private_flags.'".');
			}
		}
		
		public function offsetGet($offset)
		{
			if ($this->__private_flags==self::STD_PROP_LIST)
			{
				return $this->__private_data[$offset];
			}
			else if ($this->__private_flags==self::ARRAY_AS_PROPS)
			{
				return $this->__private_data->{$offset};
			}
			else
			{
				throw new NutshellException('Unable to set data based on the current set flag. The flag is "'.$this->__private_flags.'".');
			}
		}
		
		public function offsetExists($offset)
		{
			if ($this->__private_flags==self::STD_PROP_LIST)
			{
				return isset($this->__private_data[$offset]);
			}
			else if ($this->__private_flags==self::ARRAY_AS_PROPS)
			{
				return isset($this->__private_data->{$offset});
			}
			else
			{
				throw new NutshellException('Unable to set data based on the current set flag. The flag is "'.$this->__private_flags.'".');
			}
		}
		
		public function offsetUnset($offset)
		{
			if ($this->__private_flags==self::STD_PROP_LIST)
			{
				unset($this->__private_data[$offset]);
			}
			else if ($this->__private_flags==self::ARRAY_AS_PROPS)
			{
				unset($this->__private_data->{$offset});
			}
			else
			{
				throw new NutshellException('Unable to set data based on the current set flag. The flag is "'.$this->__private_flags.'".');
			}
		}
		
		/*** [END Implementation of ArrayAccess] ***/
		/*** [START Implementation of Serializable] ***/
		
		public function serialize()
		{
			return serialize($this->__private_data);
		}
		
		public function unserialize($data)
		{
			$data=unserialize($data);
			if (is_array($data) || is_object($data))
			{
				$this->setData($data);
			}
			else
			{
				throw new NutshellException('ArrayObject::unserialize expects unserialized string to be a Array or Object.');
			}
		}
		
		/*** [END Implementation of Serializable] ***/
		/*** [START Implementation of Countable] ***/
		
		public $__private_length=0;
		
		public function count()
		{
			return $this->__private_length;
		}
		
		/*** [END Implementation of Countable] ***/
	}
}
?>