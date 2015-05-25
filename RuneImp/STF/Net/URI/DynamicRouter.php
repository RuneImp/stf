<?php
/**
 * SimpleThingFramework URI Route class
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

use RuneImp\STF\Util\FileTools;

class DynamicRouter extends Router implements iRouter
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
	protected $config;
	protected $content;
	protected $fileTools;
	protected $is_dir;
	protected $path;
	protected $route;
	protected $type;

	public function __construct(iURIParser &$sturl, &$config)
	{
		parent::__construct($sturl);

		$this->config = &$config;

		// $this->route = $route;
		// $this->is_dir = $is_dir;// if is_dir and path has no content list files within path
		// $this->path = $path;
		// $this->type = !empty($type) ? $type : self::ROUTE_TYPE_NORMAL;
		// if(!is_null($meta_data) && !is_array($meta_data))
		// 	throw new RouteException('The $meta_data argument must be a named array. Argument type '.gettype($meta_data).' not supported.', 1);
		// $this->meta_data = !is_null($meta_data) ? $meta_data : array();

		// // 1 = readable; 2 = writable; 4 = isset //
		// $this->props['content'] = 5;
		// $this->props['is_dir'] = 5;
		// $this->props['meta_data'] = 5;
		// $this->props['path'] = 5;
		// $this->props['route'] = 5;
		// $this->props['type'] = 5;

		foreach( $this->routes as $route => $config )
		{
			if( $route != '404_override' && $route != 'default_mvc')
			{
				$uri = substr($this->uri, 1);
				// echo "$uri<br>\n";
				// echo "$route<br>\n";

				if( strpos($route, '/(:any)') !== false )
					$routed = str_replace('/(:any)', '(/(.*))?', $route);
				else if ( substr($route, -1) !== '/' )
					$routed = $route.'/?';
				else
					$routed = $route;

				// echo "$quoted<br>\n";
				$route_test = preg_match('`'.$routed.'$`', $uri, $matches);
				if( $route_test )
				{
					echo '<pre>';
					// echo '<pre>$matches: '.print_r($matches, true)."</pre>\n";
					echo '$route: '.print_r($route, true)."\n";
					echo '$config: '.print_r($config, true);
					echo "</pre>\n";
					break;
				}
			}
		}
	}

	protected function checkContentPath()
	{
		if( !is_object($this->fileTools) )
			$this->fileTools = new FileTools;

		if( !empty($this->config['app']['content']) )
			$this->fileTools->recursePath($this->config['app']['content']);
	}

	public function getController()
	{
		$this->path = $sturl->getPath();

		$this->checkContentPath();
	}
}

?>