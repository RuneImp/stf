<?php
/**
 * STF FileTools
 *
 * @author	RuneImp <runeimp@gmail.com>
 * @version	v1.3.0
 */
/*
 * ChangeLog:
 * ----------
 * 2014-03-25	v1.3.0		Added the loadScript method.
 * 2014-03-23	v1.2.0		Updated the read method with exception handling and added the filePermissions method.
 * 2014-03-19	v1.1.0		Converted to use PHP namespaces.
 * 2012-09-09	v1.0.0		Class Creation. PEAR style based on com\simplethingframework\util\FileTools v2.0.0
 */

namespace RuneImp\STF\Util;
use RuneImp\STF\Exception;

class FileTools
{
	// CLASS INFO CONSTANTS //
	const CLASS_AUTHOR = 'RuneImp';
	const CLASS_VERSION	= '1.3.0';
	
	// Class Vars //
	public $exceptions = array('.', '..');
	
	/**
	 * list
	 *
	 * Example Returned Object:
	 *	$list->dir
	 *	$list->file
	 *
	 * @param	$path		Path to search for files and directories.
	 * @param	$exceptions	Array of exceptions to allow in the list. Defaults to NULL.
	 * @return	Object of an array of directories and an array of files.
	 */
	public function fileList($path, $exceptions=null)
	{
		if(!file_exists($path)){
			throw new \Exception("File path ".'"'.$path.'"'." doesn't exist.");
		}
		
		if($exceptions === null)
			$exceptions = $this->exceptions;
		
		$path = realpath($path).DIRECTORY_SEPARATOR;
		
		$list			= new \stdClass;
		$list->dir		= array();
		$list->file		= array();
		$scanList		= scandir($path);
		foreach($scanList as $entry)
		{
			$filetype = filetype($path.$entry);
			if(!$this->match($entry, $exceptions))
			{
				if($filetype == 'dir'){
					array_push($list->dir, $entry);
				}
				else if($filetype == 'file'){
					array_push($list->file, $entry);
				}
				else{
					//array_push($unknownList, $entry);
				}
				// echo $entry.' * '.$filetype."<br/>\n";
			}
		}
		// exit();
		natcasesort($list->dir);
		natcasesort($list->file);
		return $list;
	}
	
	/**
	 * Return the files UNIX style permissions.
	 *
	 * @param string $file File to process.
	 * @return void
	 * @author Mark Gardner
	 */
	public function filePermissions($file)
	{
		$perms = fileperms($this->baseDir.$file);
		
		$result = '';
		
		if (($perms & 0xC000) == 0xC000)
			$result .= 's'; // Socket
		elseif (($perms & 0xA000) == 0xA000)
			$result .= 'l'; // Symbolic Link
		elseif (($perms & 0x8000) == 0x8000)
			$result .= '-'; // Regular
		elseif (($perms & 0x6000) == 0x6000)
			$result .= 'b'; // Block special
		elseif (($perms & 0x4000) == 0x4000)
			$result .= 'd'; // Directory
		elseif (($perms & 0x2000) == 0x2000)
			$result .= 'c'; // Character special
		elseif (($perms & 0x1000) == 0x1000)
			$result .= 'p'; // FIFO pipe
		else
			$result .= 'u'; // Unknown
		
		// Owner
		$result .= (($perms & 0x0100) ? 'r' : '-');
		$result .= (($perms & 0x0080) ? 'w' : '-');
		$result .= (($perms & 0x0040) ? (($perms & 0x0800) ? 's' : 'x' ) : (($perms & 0x0800) ? 'S' : '-'));
		
		// Group
		$result .= (($perms & 0x0020) ? 'r' : '-');
		$result .= (($perms & 0x0010) ? 'w' : '-');
		$result .= (($perms & 0x0008) ? (($perms & 0x0400) ? 's' : 'x' ) : (($perms & 0x0400) ? 'S' : '-'));
		
		// World
		$result .= (($perms & 0x0004) ? 'r' : '-');
		$result .= (($perms & 0x0002) ? 'w' : '-');
		$result .= (($perms & 0x0001) ? (($perms & 0x0200) ? 't' : 'x' ) : (($perms & 0x0200) ? 'T' : '-'));
		
		return $result;
	}

	public function loadScript($filename, $path=null)
	{
		if( file_exists($filename) )
			if( is_readable($filename) )
			{
				ob_start();
				require_once $filename;
				$localVariables = compact(array_keys(get_defined_vars()));
				$result = array();
				$result['vars'] = $localVariables;
				$result['output'] = ob_get_clean();
			}
			else
				throw new FileException($filename.' not readable.');
		else
			throw new FileException($filename." doesn't exist.");

		if( $path !== null )
			try{
				$result = ArrayTools::dotPath($result, $path);
			}catch(Exception $e){
				$result = false;
			}

		return $result;
	}
	
	/**
	 * match searches for a string in an array of strings.
	 * The array of strings may have wildcards.
	 * @param	$needle		String to find.
	 * @param	$haystack	Array of strings to match against.
	 * @return	Boolean TRUE of a match was found else FALSE.
	 */
	public function match($needle, $haystack)
	{
		$result	= FALSE;
		if($haystack != null)
		{
			foreach($haystack as $item)
			{
				// Check For Wildcards //
				if(strpos($item, '*') !== FALSE)
				{
					// Do Wildcard Match //
					$items		= array();
					$tmps		= explode('*', $item);
					foreach($tmps as $tmp)		if(!empty($tmp))	array_push($items, $tmp);
					$matches	= 0;
					foreach($items as $part)	if(strpos($needle, $part) !== FALSE)	++$matches;
					if($matches == count($items))
					{
						$result	= TRUE;
						break;
					}
				}
				else
				{
					// Do Exact Match //
					if($needle == $item)
					{
						$result	= TRUE;
						break;
					}
				}
			}
		}
		
		return $result;
	}
	
	/**
	 * Reads and returns the contents of a file.
	 *
	 * @param	$filename	Path to the file to write to.
	 * @param	$utf8		True to use UTF-8 encoding. Binary otherwise.
	 * @param	$offset		Offset to start reading the file from.
	 * @param	$length		Number of characters to read from offset.
	 *
	 * @return File contents
	 */
	public function read($filename, $utf8=FALSE, $offset=0, $length=NULL)
	{
		$flags		= $utf8 ? FILE_TEXT : FILE_BINARY;
		$context	= NULL; // Application to local files only.
		
		if( file_exists($filename) )
			if( is_readable($filename) )
				if($length == NULL)
					$result	= file_get_contents($filename, $flags, $context, $offset);
				else
					$result	= file_get_contents($filename, $flags, $context, $offset, $length);
			else
				throw new FileException($filename.' not readable.');
		else
			throw new FileException($filename." doesn't exist.");

		return $result;
	}

	/**
	 * Build an array of directories and files by recursively traversing
	 * from a supplied base path.
	 * 
	 * @param  string $uri_path   The path to build from.
	 * @param  array  $result     The array to build.
	 * @return array              An array of path keys with a boolean value true if it's a directory.
	 */
	protected function recursePath($uri_path, &$result=array())
	{
		foreach(scandir($uri_path) as $k=>$v)
		{
			$file = $uri_path.'/'.$v;
			$is_dir = is_dir($file);
			if($v[0] !== '.')
				if($is_dir)
				{
					$result[$file] = true;
					$this->recursePath($file, $result);
				}
				else
					$result[$file] = false;
		}
		return $result;
	}
	
	/**
	 * Returns an array of available drive letters on DOS and Windows systems.
	 *
	 * @return	Array of drive letters or FALSE if no drive letters found.
	 */
	public function winDrives()
	{
		$drives	= array();
		for($c='A'; $c<='Z'; $c++)
			if(is_dir($c.':'))
				array_push($drives, $c);
		
		return count($drives) > 0 ? $drives : FALSE;
	}
	
	/**
	 * Writes data to a file.
	 *
	 * @param	$filename	Path to the file to write to.
	 * @param	$data		Data to write to the specified file.
	 * @param	$append		True to append to the end of the file otherwise overwrite the file.
	 * @param	$lock		True to get an exclusive lock of the specified file.
	 *
	 * @return	Number of bytes written or FALSE on failure.
	 */
	public function write($filename, $data, $append=FALSE, $lock=FALSE)
	{
		$flags		= $append ? FILE_APPEND : 0;
		if($lock)	$flags |= LOCK_EX;
		$context	= NULL; // Application to local files only.
		
		$result	= file_put_contents($filename, $data, $flags, $context);
		return $result;
	}
}

class FileException extends \Exception{}
