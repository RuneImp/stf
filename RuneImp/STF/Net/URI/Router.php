<?php
/**
 * SimpleThingFramework URI Router class
 *
 * @author	RuneImp <runeimp@gmail.com>
 * @version	0.1.0
 */
/*
 * Change Log:
 * -----------
 * 2012-09-06	v0.1.0	Initial class creation
 *
 * ToDo:
 * -----
 * [ ] ...
 */
namespace RuneImp\STF\Net\URI;
use RuneImp\STF\Exception;

class Router implements iRouter
{
	// CLASS CONSTANTS //
	const CLASS_AUTHOR = 'RuneImp <runeimp@gmail.com>';
	const CLASS_VERSION = '0.1.0';

	const ROUTE_TYPE_DYNAMIC_RUN = 'stf.net.uri.route.type.Dynamic.Run';
	const ROUTE_TYPE_DYNAMIC_EXIT = 'stf.net.uri.route.type.Dynamic.Exit';
	const ROUTE_TYPE_NORMAL = 'stf.net.uri.route.type.Normal';
	const ROUTE_TYPE_REDIRECT = 'stf.net.uri.route.type.Redirect';

	// CLASS PROPERTIES //

	// CLASS VARS //
	protected $data;
	protected $path;
	protected $content;
	protected $controller;
	protected $type;
	protected $route;
	protected $sturl;

	public function __construct( iURIParser &$sturl, $data )
	{
		$this->data = $data;
		// echo '<pre>'.__CLASS__.' $this->data: '.var_export($this->data, true)."</pre>\n";
		$this->sturl = &$sturl;
	}

	public function getController($stf, $uri=null)
	{
		$result = false;

		if($uri !== null)
			$this->sturl->init($uri);

		$this->path = substr($this->sturl->getPath(), 1);

		$result = $this->data['404_controller'];
		// echo '<pre>'.__METHOD__.' $this->path: '.print_r($this->path, true)."</pre>\n";

		if( $this->data === false || $this->path == '' || $this->path == 'index.php' )
		{
			// echo "<h3>[/$this->path] matches [/ or /index.php]</h3>\n";
			$result = $this->data['default_controller'];
		}
		else
		{
			$path_last = substr($this->path, -1);
			// echo '<pre>$this->path: '.print_r($this->path, true)."</pre>\n";

			if( !function_exists('fnmatch') )
				throw new Exception("PHP's fnmatch doesn't exist.");

			foreach($this->data as $k=>$v)
			{
				if( $k !== '404_controller' && $k != 'default_controller' )
					$data[$k] = $v;
			}

			$flags = FNM_CASEFOLD;// FNM_NOESCAPE | FNM_PATHNAME | FNM_PERIOD | FNM_CASEFOLD
			foreach($data as $route => $controller)
			{
				$route_last = substr($route, -2);
				if( $route_last == '/*' && $path_last != '/' )
				{
					$alt_route = true;
					$test_route = substr($route, 0, -2);
				}
				else
				{
					$alt_route = false;
					$test_route = $route;
				}

				if( fnmatch($route, $this->path, $flags) )
				{
					// echo "<h3>[$route] matches [$this->path]</h3>\n";
					$result = $controller;
					break;
				}
				else if( $alt_route && fnmatch($test_route, $this->path, $flags) )
				{
					// echo "<h3>[$route] (alt) matches [/$this->path]</h3>\n";
					$result = $controller;
					break;
				}
				else
				{
					// echo "<h4>[$route] Is not match [$this->path]</h4>\n";
				}
			}
		}
		$controller = $result;
		// echo '<pre>$controller: '.print_r($controller, true)."</pre>\n";

		$this->controller = new $controller($stf, $this->path);

		return $this->controller;
	}
}

class RouterException extends \Exception{}
