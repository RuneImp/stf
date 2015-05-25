<?php
/**
 * SimpleThingFramework Base class
 *
 * @author	RuneImp <runeimp@gmail.com>
 * @version	1.1.0
 */
/*
 * Change Log:
 * -----------
 * 2014-03-19	v1.1.0		Converted to use PHP namespaces.
 * 2012-06-06	v1.0.0		Initial class creation
 *
 * ToDo:
 * -----
 * [ ] ...
 */

namespace RuneImp\STF;

class Base extends AbstractBase
{
	public function __construct()
	{
		// $this->props = array();
	}

	/**
	 * Dynamic Getter
	 *
	 * Uses bitwise check against the $props array to determine read status.
	 *
	 * @param	$prop	Property name to get.
	 * @return	Value for the property if it exists and is readable.
	 */
	public function __get($prop)
	{
		// echo '<pre>'.__METHOD__.'() $this->'.$prop.': '.print_r($this->$prop, true)."</pre>\n";
		if(array_key_exists($prop, $this->props) && (($this->props[$prop] & 1) == 1))
			return $this->$prop;
		else if(parent::__get($prop));
	}

	/**
	 * Dynamic Setter
	 *
	 * Uses bitwise check against the $props array to determine read status.
	 *
	 * @param	$prop	Property name to get.
	 * @param	$data	Property value.
	 * @return	Value for the property if it exists and is readable.
	 */
	public function __set($prop, $data)
	{
		// echo '<pre>'.__METHOD__.'() $prop: '.$prop.' => '.print_r($data, true)."</pre>\n";
		if(array_key_exists($prop, $this->props) && (($this->props[$prop] & 2) == 2))
			$this->$prop = $data;
		// echo '<pre>'.__METHOD__.'() $this->'.$prop.': '.gettype($this->$prop)."</pre>\n";
	}

	/**
	 * Dynamic Setter
	 *
	 * Uses bitwise check against the $props array to determine read status.
	 *
	 * @param	$prop	Property name to get.
	 * @param	$data	Property value.
	 * @return	Value for the property if it exists and is readable.
	 */
	public function __isset($prop)
	{
		// echo '<pre>'.__METHOD__.'() $prop: '.$prop.' => '.print_r($data, true)."</pre>\n";
		if(array_key_exists($prop, $this->props) && (($this->props[$prop] & 4) == 4))
			return isset($this->props[$prop]);
	}

	protected function loop($obj)
	{
		return Util::loop($obj);
	}
}

/**
 * Generic STF Exception
 */
class Exception extends \Exception
{
	// Redefine the exception so message isn't optional
	public function __construct($message, $code = 0, \Exception $previous = null) {
		// make sure everything is assigned properly
		parent::__construct($message, $code, $previous);
	}

	// custom string representation of object
	public function __toString() {
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}
}
?>