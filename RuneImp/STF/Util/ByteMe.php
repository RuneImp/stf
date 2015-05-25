<?php
/**
 * STF Util ByteMe
 *
 * Class to dynamically produce a string or array with a bytes value
 * converted to KB, MB, GB, TB or PB with associated monicker.
 *
 * @author	RuneImp <runeimp@gmail.com>
 * @version	1.1.0
 */
/*
 * ChangeLog:
 * ----------
 * 2014-03-19	v1.1.0		Converted to use PHP namespaces.
 * 2012-09-09	v1.0.0		Class Creation. PEAR style based on com\simplethingframework\util\ByteMe v2.0.0
 */

namespace RuneImp\STF\UTIL;

class ByteMe
{
	// CLASS INFO CONSTANTS //
	const CLASS_AUTHOR = 'RuneImp';
	const CLASS_VERSION	= '1.0.0';
	
	// Class Properties //
	private  $bytes;
	private  $byteRef;
	public $places = 2;
	private  $sizeList;

	// Constructor
	public function __construct($places)
	{
		if(!$places)
		{
			$this->places = $places;
		}
		else
		{
			$this->places = 2;
		}
	}
	
	public function __get($prop)
	{
		$result = null;
		switch($prop)
		{
			case 'bytes':		$result = $this->bytes;		break;
			case 'byteRef':		$result = $this->byteRef;	break;
			case 'sizeList':	$result = $this->sizeList;	break;
			default:
				throw new Exception("Unrecognized property {$prop}.");
				break;
		}
		return $result;
	}
	
	/**
	 * Method to 
	 *
	 *
	 */
	public function size($bytes, $array=false)
	{
		if($bytes > 1125899906842620)
		{
			$this->bytes = round (($bytes / 1125899906842620), $this->places);
			$this->byteRef = "PB";
		}
		else if($bytes > 1099511627776)
		{
			$this->bytes = round (($bytes / 1099511627776), $this->places);
			$this->byteRef = "TB";
		}
		else if($bytes > 1073741824)
		{
			$this->bytes = round (($bytes / 1073741824), $this->places);
			$this->byteRef = "GB";
		}
		else if($bytes > 1048576)
		{
			$this->bytes = round (($bytes / 1048576), $this->places);
			$this->byteRef = "MB";
		}
		else if($bytes > 1024)
		{
			$this->bytes = round (($bytes / 1024), $this->places);
			$this->byteRef = "KB";
		}
		else
		{
			$this->bytes = $bytes;
			$this->byteRef = "B";
		}
		$this->sizeList = array();
		$this->sizeList[] = $this->bytes." ".$this->byteRef;
		$this->sizeList[] = $this->bytes;
		$this->sizeList[] = $this->byteRef;
		return $array ? $this->sizeList : $this->sizeList[0];
	}
}
