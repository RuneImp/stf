<?php
/**
 * STF Util
 *
 * Simple Thing Framework Utilities
 *
 * @author RuneImp <runeimp@gmail.com>
 * @version 1.11.0
 */
/*
 * ChangeLog:
 * ----------
 * 2014-03-22	v1.11.0		Added cleanArray and keyIsDefined methods.
 * 2014-03-19	v1.10.0		Converted to use PHP namespaces.
 * 2012-08-31	v1.1.0		Added the scalar and args methods.
 * 2012-06-??	v1.0.0		Initial class creation.
 *
 * ToDo:
 * -----
 * [ ] ...
 */

namespace RuneImp\STF;

class Util
{
	const UNDEFINED = 'RuneImp.SimpleThingFramework.Undefined';

	/**
	 * Converts an array into a string as a simplified arguments list
	 * or arguments type list.
	 *
	 * @param	$args	The arguments array to process
	 * @param	$gettype	Boolean true to return an arguments type list.
	 * @return	Arguments string
	 */
	public static function args($args, $gettype=false)
	{
		$method = $gettype ? 'gettype' : array(Util, 'scalar');
		$args = array_map($method, $args);
		return implode(', ', $args);
	}

	/**
	 * Method to recursively filter empty array elements
	 *
	 * @param	$array	The array to filter
	 * @return	Recursively filtered array
	 * @see http://us3.php.net/manual/en/function.array-filter.php#87581
	 */
	public static function cleanArray($array)
	{
		foreach($array as $k => $v)
			if( is_array($v) )
				$array[$k] = self::cleanArray($v);

		return array_filter($array);
	}

	/**
	 * Searches an array for a key or array of keys and returns the value if a value for the key(s) is defined.
	 *
	 * @param	$array		The array to check for defined keys.
	 * @param	$keys		The key or array of keys to check against the array.
	 * @param	$undefined	The value to ruturn if the keys tested are not defined in the array.
	 * @return	The value for the key if defined, Util::UNDEFINED, or what was supplied for the $undefined parameter.
	 */
	public static function keyIsDefined($array, $keys, $undefined=self::UNDEFINED){
		$result = self::UNDEFINED;

		if(is_array($keys)){
			foreach($keys as $key){
				if(isset($array[$key])){
					$result = $array[$key];
					break;
				}
			}
		}else if(isset($array[$keys])){
			$result = $array[$keys];
		}

		return ( $result === self::UNDEFINED ) ? $undefined : $result;
	}

	/**
	 * Loads a list of file names and return the content.
	 *
	 * @param	$list	Array of file names.
	 * @param	$path	Base path to prepend to file names.
	 * @return	Array of file contents.
	 */
	public static function loadList($list, $path)
	{
		global $logger;

		if(!isset($logger))
			$logger = new STF_Logger(STF_Logger::LOG_LEVEL_INFO);

		$result = array();

		foreach($list as $k=>$v)
		{
			$file = $path.DIRECTORY_SEPARATOR.$v;
			if(file_exists($file))
			{
				$logger->debug($file, false);
				$result[$k] = file_get_contents($file);
			}
			else
				$logger->warn($file);
		}

		return $result;
	}

	public static function scalar($obj)
	{
		$result = '';

		switch(gettype($obj))
		{
			case 'boolean':
				$result = $obj ? 'TRUE' : 'FALSE';
				break;
			case 'integer':
			case 'double':
			case 'float':
				$result = $obj;
				break;
			case 'string':
				if(strpos($obj, "\n") !== false)
					$obj = str_replace("\n", "\n{$indent}", $obj);
				$has_doublequotes = strpos($obj, '"') === false ? false : true;
				$has_singlequotes = strpos($obj, "'") === false ? false : true;
				if($has_doublequotes)
				{
					if($has_singlequotes)
					{
						$quote = '"';
						$obj = addcslashes($obj, '"');
					}
					else
						$quote = "'";
				}
				else
					$quote = '"';

				$result = $quote.$obj.$quote;
				break;
			case 'null':
			case 'NULL':
				$result = 'NULL';
				break;
			case 'array':
				$result = 'Array';
				break;
			case 'object':
				$result = get_class($obj);
				if($result == 'stdClass')
					$result = 'Object';
				break;
			case 'resource':
				$result = 'resource';
				break;
			default:
				$result = 'unknown';
		}
		return $result;
	}

	public static function loop($obj, $depth=0)
	{
		$result = '';
		$indent = str_repeat("        ", $depth);

		switch(gettype($obj))
		{
			case 'boolean':
				$result = $obj ? 'TRUE' : 'FALSE';
				break;
			case 'integer':
			case 'double':
			case 'float':
				$result = $obj;
				break;
			case 'string':
				if(strpos($obj, "\n") !== false)
					$obj = str_replace("\n", "\n{$indent}", $obj);
				$has_doublequotes = strpos($obj, '"') === false ? false : true;
				$has_singlequotes = strpos($obj, "'") === false ? false : true;
				if($has_doublequotes)
				{
					if($has_singlequotes)
					{
						$quote = '"';
						$obj = addcslashes($obj, '"');
					}
					else
						$quote = "'";
				}
				else
					$quote = '"';

				$result = $quote.$obj.$quote;
				break;
			case 'null':
			case 'NULL':
				$result = 'NULL';
				break;
			case 'array':
				$result = "Array";
				$propStr = '';
				// $result .= $indent."(\n";
				foreach($obj as $k=>$v)
				{
					$propStr .= $indent.'    [';
					if(gettype($k) == 'string')
						$propStr .= '"'.$k.'"';
					else
						$propStr .= $k;
					$propStr .= '] => '.Util::loop($v, $depth+1)."\n";
				}
				// $result .= $indent.")";
				
				if(!empty($propStr))
					$result .= "\n".$indent."(\n".$propStr.$indent.')';
				else
					$result .= '(empty)';
				break;
			case 'object':
				$className = get_class($obj);
				$result = "{$className} Object";
				$props = array();
				if($obj instanceof IteratorAggregate || method_exists($obj, 'getIterator'))
					$props = $obj->getIterator();
				else if($obj instanceof Traversable)
					$props = &$obj;
				else
					$props = get_object_vars($obj);

				$propStr = '';
				// $props = get_class_vars($className);
				foreach($props as $k=>$v)
					$propStr .= $indent.'    ['.$k.'] => '.Util::loop($v, $depth+1)."\n";
				
				if(!empty($propStr))
					$result .= "\n".$indent."(\n".$propStr.$indent.')';
				else
					$result .= '(no properties)';
				break;
			case 'resource':
				$result = get_resource_type($obj).' Resource';
				break;
			default:
				$result = 'unknown type';
		}
		return $result;
	}
}
?>