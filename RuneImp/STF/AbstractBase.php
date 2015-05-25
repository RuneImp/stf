<?php
/**
 * URI Parsing and Creation class base on RFC-3986
 * Some code based on STURL 2.4.1.
 *
 * @author	RuneImp <runeimp@gmail.com>
 * @version	0.1.0
 * @see	http://tools.ietf.org/html/rfc3986
 */
/*
 * Change Log:
 * -----------
 * 2014-03-19	v0.1.0		Converted to use PHP namespaces.
 */

namespace RuneImp\STF;

abstract class AbstractBase
{
	// PRIVATE CLASS PROPERTIES //
	protected $props;

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
		return null;
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
		// Do nothing
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
		return false;
	}
}
?>