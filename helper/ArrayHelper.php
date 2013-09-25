<?php
/**
 * @package nutshell
 * @author guillaume
 */
namespace nutshell\helper
{
	/**
	 * The Array helper class.
	 * 
	 * This class has specialized methods for dealing 
	 * with arrays.
	 *  
	 * @package nutshell
	 * @static
	 */
	class ArrayHelper
	{
		/**
		 * This function adds a column to a 2 dimensional array.
		 * @param array $data
		 * @param string $column_name
		 * @param mixed $value
		 */
		public static function addColumnWithValue(&$data, $column_name, $value)
		{
			foreach($data as &$row)
			{
				$row[$column_name] = $value;
			}
		}
		
		/**
		 * This function renames a field in a 1 dimensional array.
		 * @param array  $row
		 * @param string $old_column_name
		 * @param string $new_column_name
		 */		
		public static function renameField(array &$row, $old_field_name, $new_field_name)
		{
			$row[$new_field_name] = $row[$old_field_name];
			unset($row[$old_field_name]);
		}
		
		/**
		 * This function renames a multiple fields in a 1 dimensional array.
		 * @param array $row Data to be modified.
		 * @param array $aRenaming Array with multiple renamings. This parameter is passed as reference because it's faster. This array won't be modified.
		 */
		public static function renameFields(array &$row, array &$aRenaming)
		{
			foreach($aRenaming as $old_field_name => $new_field_name)
			{
				self::renameField($row, $old_field_name, $new_field_name);
			}
		}

		/**
		 * This function renames a column in a 2 dimensional array.
		 * @param array
		 * @param string $old_column_name
		 * @param string $new_column_name
		 */
		public static function renameColumn(array &$data, $old_column_name, $new_column_name)
		{
			if (($old_column_name != $new_column_name) && (strlen($old_column_name)>0) && (strlen($new_column_name)>0) )
			{
				foreach($data as &$row)
				{
					self::renameField($row, $old_column_name, $new_column_name);
				}
			}
		}
		
		/**
		 * This function returns an array where the $key is the same 
		 * @param array $aArrayToBeTransformed
		 */
		public static function createKeyEqualsValueArray(array $aArrayToBeTransformed)
		{
			$result = array ();
			foreach($aArrayToBeTransformed as $value)
			{
				$result[$value] = $value;
			}
			return $result;
		}
		
		static public function flatten(Array $array)
		{
			$newArray=array();
			foreach ($array as $val)
			{
				if (is_array($val))
				{
					$newArray=array_merge($newArray,self::flatten($val));
				}
				else
				{
					$newArray[]=$val;
				}
			}
			return $newArray;
		}
		
		static public function without(Array &$array,$without)
		{
			if (!is_array($without))
			{
				$without=array($without);
			}
			for ($i=0,$j=count($without); $i<$j; $i++)
			{
				foreach ($array as $key=>$val)
				{
					if ($without[$i]==$val)
					{
						unset($array[$key]);
					}
				}
				ksort($array);
			}
		}
		
		static public function withoutKey(Array $array,$without)
		{
			if (!is_array($without))
			{
				$without=array($without);
			}
			$newArray=array();
			foreach ($array as $key=>$val)
			{
				if (!in_array($key,$without))
				{
					$newArray[$key]=$val;
				}
			}
			return $newArray;
		}
		
		static public function closest(Array $array,$value)
		{
			sort($array);
			$closest=$array[0];
			for ($i=1,$j=count($array),$k=0; $i<$j; $i++,$k++)
			{
				$middleValue=($array[$i]-$array[$k])/2+$array[$k];
				if ($value>=$middleValue)
				{
					$closest=$array[$i];
				}
			}
			return $closest;
		}
		
		static public function trim(Array $array)
		{
			foreach($array as $key=>$val)
			{
				if (is_string($val))
				{
					$array[$key]=trim($val);
				}
			}
			return $array;
		}
		
		static public function trimKeys(Array $array)
		{
			$newArray=array();
			foreach($array as $key=>$val)
			{
				if (is_string($key))
				{
					$newArray[trim($key)]=$val;
				}
				else
				{
					$newArray[$key]=$val;
				}
			}
			return $newArray;
		}
	}
}