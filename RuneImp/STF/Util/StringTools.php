<?php
/**
 * STF ArrayTool
 *
 * Class to help managing arrays.
 *
 * @author	RuneImp <runeimp@gmail.com>
 * @version	0.1.0
 */
/*
 * ChangeLog:
 * ----------
 * 2014-06-18	v0.1.0		Initial class creation.
 */
namespace RuneImp\STF\Util;
use RuneImp\STF\Exception;

class StringTools
{
	/**
	 * @see http://www.php.net/manual/en/function.strpos.php#40617
	 */
	public static function countSubstrs($haystack, $needle) 
	{ 
		return ( ($p = strpos($haystack, $needle)) === false ) ? 0 : ( 1 + StringTools::countSubstrs( substr($haystack, $p + 1), $needle )); 
	} 
}
