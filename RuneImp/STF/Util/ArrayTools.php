<?php
/**
 * STF ArrayTool
 *
 * Class to help managing arrays.
 *
 * @author	RuneImp <runeimp@gmail.com>
 * @version	1.1.0
 */
/*
 * ChangeLog:
 * ----------
 * 2014-06-18	v1.2.0		Added dotPath method.
 * 2014-03-19	v1.1.0		Converted to use PHP namespaces.
 * 2012-09-09	v1.0.0		Class Creation. PEAR style based on com\simplethingframework\util\ArrayTools v1.2.0
 */
namespace RuneImp\STF\Util;
use RuneImp\STF\Exception;

class ArrayTools
{
	// CLASS INFO CONSTANTS //
	const CLASS_AUTHOR = 'RuneImp';
	const CLASS_VERSION	= '1.2.0';

	/**
	 * Method to access data within a multi-dimensional array using dot syntax.
	 *
	 * @param	$data	The array to access.
	 * @param	$path	The dot syntax path to use.
	 * @return	The value or false
	 */
	public static function dotPath($data, $path)
	{
		$keys = explode('.', $path);
		foreach( $keys as $key )
			if( array_key_exists($key, $data) )
				$data = $data[$key];
			else
				throw new Exception('The dot syntax path is invalid.');

		return $data;
	}
	
	/**
	 * keyNatSort does a natural sort via key on the supplied array.
	 *
	 * @param	$array		The array to natural sort via key.
	 * @param	$saveMemory If true will delete values from the original array as it builds the sorted array.
	 * @return	Sorted array on success. Boolean false if sort failed or null if the object was not an array.
	 */
	public static function keyNatSort($array, $saveMemory=false)
	{
		if(is_array($array))
		{
			$keys = array_keys($array);
			if(natsort($keys))
			{
				$result = array();
				foreach($keys as $key)
				{
					$result[$key] = $array[$key];
					if($saveMemory)
						unset($array[$key]);
				}
			}
			else
				$result = false;
		}
		else
			$result = null;

		return $result;
	}
	
	/**
	 * Takes a mixed indexed and named array and returns just the named array.
	 *
	 * @param	$array	Data to be transformed.
	 * @return	Named array or null if the $array is in fact not an array.
	 */
	public static function mixed2Named($array)
	{
		if(is_array($array))
		{
			$result	= array();
			foreach($array as $key=>$value)
				if($key != (intval($key).''))
					$result[$key]	= $value;
		}else{
			$result = null;
		}
		
		return $result;
	}
}
