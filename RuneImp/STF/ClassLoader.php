<?php
/**
 * STF ClassLoader
 *
 * Simple Thing Framework Class loader.
 *
 * @author RuneImp <runeimp@gmail.com>
 * @version 1.10.0
 */
/*
 * Change Log:
 * -----------
 * 2014-03-19	v1.10.0		Converted to use PHP namespaces.
 * 2012-09-19	v1.9.0		Added get_CACHE_KEY() method.
 * 2012-08-04	v1.8.2		Dissabled ClassLoader_Exception throwing in the load method and __autoload function.
 * 2012-07-25	v1.8.1		Fixed expectation of apc_exists in the cache_init method.
 * 							Also renamed the constant CLASS_INIT to CACHE_INIT.
 * 2012-07-18	v1.8.0		Added APC support for class path caching.
 * 2012-06-20	v1.7.0		Caching now checks to make sure the cache directory is writable.
 * 2012-06-20	v1.6.1		Updated caching code.
 * 2012-06-20	v1.6.0		Added cache timeout code.
 * 2012-06-19	v1.5.1		Commented out "STF_CACHE_PATH not defined." exception.
 * 2012-06-14	v1.5.0		Added support for classes in BASENAME/PACKAGE/CLASSNAME/BASENAME_PACKAGE_CLASSNAME.php
 * 							<?php class BASENAME_PACKAGE_CLASSNAME ?> type setups and similar.
 * 2012-06-12	v1.4.0		Added class path caching when STF_CACHE_PATH is defined.
 * 2012-06-06	v1.3.0		Initial creation based on com\simplethingframework\util\ClassLoader v1.2.1
 * 							Added resolve_include_path method and ClassLoader_Exception.
 */

namespace RuneImp\STF;
use RuneImp\STF\Util;

if(function_exists('spl_autoload_register'))
{
	spl_autoload_register(array('RuneImp\STF\ClassLoader', 'load'));
}
else
{
	function __autoload($name)
	{
		ClassLoader::load($name);
	}
}

class ClassLoader
{
	// CLASS CONSTANTS //
	const CACHE_KEY = 'SimpleThingFramework.ClassLoader';
	const CLASS_AUTHOR = 'RuneImp <runeimp@gmail.com>';
	const CLASS_VERSION = '1.10.0';
	const CACHE_INIT = 'a:0:{}';
	const CACHE_TIME = 3600;// 1 hour

	private static $cache;
	private static $class_loader_cache;
	private static $include_path;
	private static $load_trace;

	private static function cache_check($name)
	{
		// echo __METHOD__.'('.$name.")\n";

		self::cache_init();

		if(!empty(self::$class_loader_cache[$name]))
			return self::$class_loader_cache[$name];
		else
			return false;
	}

	private static function cache_init()
	{
		// echo __METHOD__."() ClassLoader::\$cache: ".ClassLoader::$cache."\n";
		if(function_exists('apc_fetch') && !isset(self::$class_loader_cache))
		{
			if(function_exists('apc_exists'))
			{
				if(apc_exists(self::get_CACHE_KEY()))
					self::$class_loader_cache = apc_fetch(self::get_CACHE_KEY());
				else
					self::$class_loader_cache = array();
			}
			else
			{
				self::$class_loader_cache = apc_fetch(self::get_CACHE_KEY());
				if(self::$class_loader_cache === false)
					self::$class_loader_cache = array();
			}
		}
		else if(defined('STF_CACHE_PATH'))
		{
			self::$cache = STF_CACHE_PATH.DIRECTORY_SEPARATOR.self::CACHE_KEY.'_cache.phpserial';
			if(!isset(self::$class_loader_cache))
			{
				self::$class_loader_cache = array();
				if(!file_exists(self::$cache))
				{
					if(is_writable(STF_CACHE_PATH))
						if(touch(self::$cache))
							file_put_contents(self::$cache, serialize(self::$class_loader_cache));
						else
							throw new ClassLoader_Exception('Can not create cache file.');
					else
						throw new ClassLoader_Exception('Can not create cache file in cache directory.');
				}
				else if(!empty(self::$cache))
				{
					$cacheAge = filemtime(self::$cache);
					$cacheTimeOut = $cacheAge + (defined('STF_CACHE_TIME') ? STF_CACHE_TIME : self::CACHE_TIME);
					
					if($cacheTimeOut < time())
						file_put_contents(self::$cache, self::CACHE_INIT);

					self::$class_loader_cache = unserialize(file_get_contents(self::$cache));
				}
			}
		}
		else
		{
			// throw new ClassLoader_Exception('STF_CACHE_PATH not defined.');
		}
	}

	private static function cache_update($name, $path)
	{
		// echo __METHOD__.'($name, $path): '.$name.", {$path}\n";
		self::cache_init();
		self::$class_loader_cache[$name] = $path;
		if(function_exists('apc_store'))
		{
			apc_store(self::get_CACHE_KEY(), self::$class_loader_cache, self::CACHE_TIME);
		}
		else
		{
			if(!empty(self::$cache))
				file_put_contents(self::$cache, serialize(self::$class_loader_cache));
		}
	}

	private static function get_CACHE_KEY()
	{
		return self::CACHE_KEY.__FILE__;
	}

	public static function load($name)
	{
		// echo __METHOD__."('$name')\n";

		self::$load_trace = debug_backtrace();

		$name = str_replace('\\', '/', $name);
		$pathInfo = pathinfo($name);
		// print_r($pathInfo); echo "\n";
		$namespace = $pathInfo['dirname'];
		$class = $pathInfo['filename'];

		if($namespace == '.')
			$namespace = '';
		else if($namespace[0] == '/')
			$namespace = substr($namespace, 1);

		if(strlen($namespace) > 1 || $namespace == '.')
			$namespace .= '/';

		// echo '$namespace: '.$namespace."<br>\n";
		// echo '$class: '.$class."<br>\n";

		$fixes = array('class', 'lib', 'inc');
		$extensions = array('.php');// , '.inc', '.php3' etc.

		$result = self::cache_check($name);

		if($result === false)
			$result = self::search($namespace, $class, $fixes, $extensions, $name);

		if($result === false)
		{
			$lowerClass = strtolower($class);
			if($class != $lowerClass)
				$result = self::search($namespace, $lowerClass, $fixes, $extensions, $name);
		}

		if($result === false)
		{
			$upperClass = strtoupper($class);
			if($class != $upperClass)
				$result = self::search($namespace, $upperClass, $fixes, $extensions, $name);
		}

		if($result === false)
		{
			$msg = "Unable to load \"{$name}\".\n";
			$msg .= Util::loop(self::$load_trace);
			// header('Content-Type: text/plain');
			// throw new ClassLoader_Exception($msg);
		}
		else
			include $result;
	}

	private static function resolve_include_path($target)
	{
		// echo __METHOD__."('$target')\n";
		
		if(empty(self::$include_path))
		{
			$incPath = explode(PATH_SEPARATOR, get_include_path());
			self::$include_path = $incPath;
		}
		else
			$incPath = self::$include_path;

		foreach($incPath as $path)
		{
			$file = $path.'/'.$target;
			if(file_exists($file))
				return $file;
		}

		return false;
	}

	private static function search($namespace, $class, $fixes, $extensions, $name)
	{
		// echo __METHOD__."('$namespace', '$class', \$fixes, \$extensions, '$name')\n";
		// echo '<pre>'.__METHOD__.' $namespace.$class: '.print_r($namespace.$class, true)."</pre>\n";

		foreach($fixes as $fix)
		{
			$files = array();
			if(strpos($class, '_') !== false)
			{
				$files[] = str_replace('_', '/', $class);// PEAR Class
				$files[] = str_replace('_', '/', $class).'/'.$class;// EyeFi Class?
			}
			$files[] = $namespace.$class;
			$files[] = $namespace.$fix.'.'.$class;
			$files[] = $namespace.$class.'.'.$fix;
			$files[] = $namespace.$fix.$class;
			$files[] = $namespace.$class.$fix;

			foreach($extensions as $ext)
			{
				foreach($files as $file)
				{
					$target = $file.$ext;
					// echo '<pre>'.__METHOD__.' $target: '.print_r($target, true)."</pre>\n";
					if(function_exists('stream_resolve_include_path'))
						$fullPath = stream_resolve_include_path($target);// PHP 5.3.2+
					else
						$fullPath = self::resolve_include_path($target);
					
					if(is_string($fullPath))
					{
						// echo '<pre>'.__METHOD__.' $fullPath: '.print_r($fullPath, true)."</pre>\n";
						self::cache_update($name, $fullPath);
						return $fullPath;
					}
				}// end foreach($files as $file)
			}// end foreach($extensions as $ext)
		}// end foreach($fixes as $fix)
		
		return false;
	}
}

class ClassLoader_Exception extends \Exception
{
	// Redefine the exception so message isn't optional
	public function __construct($message, $code = 0, Exception $previous = null) {
		// make sure everything is assigned properly
		parent::__construct($message, $code, $previous);
	}

	// custom string representation of object
	public function __toString() {
		return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
	}
}
?>