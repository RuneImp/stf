<?php
/**
 * STF Bitfield
 *
 * Class for managing a bitfield data type.
 *
 * @author	RuneImp <runeimp@gmail.com>
 * @version	1.1.0
 */
/*
 * ChangeLog:
 * ----------
 * 2014-03-19	v1.1.0		Converted to use PHP namespaces.
 *2012-09-09	v1.0.0		Class Creation. PEAR style based on com\simplethingframework\util\Bitfield v2.0.0
 */

namespace RuneImp\STF\UTIL;

class Bitfield
{
	// CLASS INFO CONSTANTS //
	const CLASS_AUTHOR = 'RuneImp';
	const CLASS_VERSION	= '1.0.0';
	
	// CLASS CONSTANTS //
	const EMPTY_FIELD = 0;			// Empty Field
	
	// CLASS VARIABLES //
	protected $_field = EMPTY_FIELD;	// The Bitfield
	
	public function __construct($value=EMPTY_FIELD)
	{
		$this->_field	= $value;
	}
	
	/**
	 * Gets a flag in the bitfield.
	 *
	 * @param	$flag	Flag possition to set.
	 * @return	Number of the flag if set or 0.
	 */
	public function getFlag($flag)
	{
		return ($this->_field & $flag); 
	}
	
	/**
	 * Checks if a flag is set in the bitfield.
	 *
	 * @param	$flag	Flag possition to set.
	 * @return	Boolean TRUE/FALSE
	 */
	public function hasFlag($flag)
	{
		return ($this->getFlag($flag) == $flag);
	}
	
	/**
	 * Sets a flag in the bitfield.
	 *
	 * @param	$flag	Flag possition to set.
	 * @param	$state	TRUE/FALSE state to use.
	 * @return	void
	 */
	public function setFlag($flag, $state)
	{
		$this->_field = ($state ? ($this->_field | $flag) : ($this->_field & ~$flag));
	}
	
	/**
	 * @return	Name of class plus list of set flags if present.
	 */
	public function __toString()
	{
		$result	= '[Bitfield Flags:';
		$flags	= '';
		
		for($flag = 1; $flag <= $this->_field; $flag *= 2)
		{
			if($this->hasFlag($flag))
			{
				$flags	.= " $flag,";
			}
		}
		$flags	= substr($flags, 0, -1);
		
		$result	= (empty($flags)) ? '[Bitfield' : '[Bitfield Flags:';
		$result	.= "$flags]";
		
		return $result;
	}
}
?>