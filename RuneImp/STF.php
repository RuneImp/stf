<?php
/**
 * SimpleThingFramework Front Controller
 *
 * @author	RuneImp <runeimp@gmail.com>
 * @version	1.0.0
 */
/*
 * ChangeLog:
 * ----------
 * 2014-06-18	1.0.0	Initial script creation.
 */
namespace RuneImp;
use RuneImp\STF\Exception;

require_once __DIR__.'/STF/ClassLoader.php';

use RuneImp\STF\ClassLoader;
use RuneImp\STF\Net\URI\STURL;
use RuneImp\STF\Util;
use RuneImp\STF\Util\ArrayTools;
use RuneImp\STF\Util\FileTools;
use RuneImp\STF\Util\YAML;

class STF
{
	// CLASS CONSTANTS //
	const CLASS_AUTHOR = 'RuneImp <runeimp@gmail.com>';
	const CLASS_VERSION = '1.0.0';
	const UNDEFINED = 'RuneImp.SimpleThingFramework.Undefined';

	// CLASS PROPERTIES //
	protected static $config;
	protected $controller;
	protected $factory_list;
	protected $fileTools;
	protected $initial;
	protected $initialized = false;
	protected static $instance;
	protected static $instances = array();
	protected $path;
	protected $routes;
	protected $sturl;
	protected $yaml;

	/**
	 * Class Constructor
	 *
	 * @return STF instance
	 */
	public function __construct(&$config=null)
	{
		$this->config =& $config;
		$this->sturl = new STURL(true);
		$this->yaml = new YAML;

		// echo '<h1>'.__CLASS__." - Front Controlled</h1>\n";
	}

	public static function factory($reference)
	{
		if(array_key_exists($reference, $this->factory_list))
			return $list[$reference];
		else
			return false;
	}

	/**
	 * Method to access all config info.
	 *
	 * This method does not return by reference so that the config data is unmodified for all requests.
	 *
	 * @param	$item	Optional configuration item specifier or dot syntax specifier.
	 * @return	Config item or the entire config.
	 */
	public function getConfig($item=null)
	{
		if($item === null)
			return $this->config;
		else
			try {
				return ArrayTools::dotPath($this->config, $item);
			} catch (Exception $e) {
				echo "<pre>".__METHOD__.' '.$e->getMessage()."</pre>\n";
			}
			
	}

	public static function getInstance(&$config=null, $start=false)
	{
		if( self::$instance === null )
			self::$instance = new self($config);

		// Allow for multiple instances for PHP as server...
		self::$instances[] = self::$instance;

		if($start)
			self::$instance->handleRequest();

		return self::$instance;
	}

	public function getInstanceCount()
	{
		return count(self::$instances);
	}

	public function getSTURL()
	{
		return $this->sturl;
	}

	public function handleRequest($uri=null)
	{
		// echo "<h2>Request Handler</h2>\n";
		// echo "<pre>".__METHOD__." \$uri: $uri</pre>\n";

		$this->handleRoute($uri);
	}

	protected function handleRoute($uri=null)
	{
		$this->uri = $uri;
		// echo '<h2>'.__METHOD__." \$this->uri: {$this->uri}</h2>\n";
		
		if( $this->fileTools === null )
			$this->fileTools = new FileTools;

		$this->routes = $this->fileTools->loadScript($this->config['route']['list'], 'vars.routes');

		$this->router = $this->config['route']['default']['router'];
		$this->router = new $this->router($this->sturl, $this->routes);
		$this->controller = $this->router->getController($this->sturl);
	}

	public function yamlDecode($yaml)
	{
		return $this->yaml->decode($yaml);
	}

	public function yamlEncode($yaml)
	{
		return $this->yaml->encode($yaml);
	}
}

namespace RuneImp\STF;

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
