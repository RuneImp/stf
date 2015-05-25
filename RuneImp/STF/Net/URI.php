<?php
/**
 * URI Parsing and Creation class base on RFC-3986
 * Some code based on STURL 2.4.1.
 *
 * @author	RuneImp <runeimp@gmail.com>
 * @version	0.9.0
 * @see	http://tools.ietf.org/html/rfc3986
 */
/*
 * Change Log:
 * -----------
 * 2014-03-19	v0.9.0		Converted to use PHP namespaces.
 * 2012-10-30	v0.8.1		Updated slugIt method to not remove numbers
 * 2012-09-06	v0.8.0		Merged getPathSlug into getPathNormalized
 * 2012-07-25	v0.6.0		Changed resourcePath to simply path. Fixed scheme parsing bug.
 *							Finished parsedURI ToDo.
 * 2012-06-03	v0.5.0		Initial class creation with Matrix URI support
 *
 * ToDo:
 * -----
 * [ ] Implement normalization as per RFC-3986 http://tools.ietf.org/html/rfc3986
 * [ ] Implement checkResource method
 * [ ] Implement protocol support
 * [ ] Add Request-Line support
 * [ ] Handle base-path info
 * [ ] Normalize . and .. paths
 * [ ] Setup resource slugification with default such as STF_URI_INDEX constant for potential directory paths
 * [ ] Setup path slugification
 * [*] Setup properties for all or most values within $parsedURI
 * [ ] Handle Joomla type path info via URI Template
 * [ ] Handle locale info, et. al. via URI Template and header review
 */

namespace RuneImp\STF\Net;

class URI extends \RuneImp\STF\Base
{
	// CLASS CONSTANTS //
	const CLASS_AUTHOR = 'RuneImp <runeimp@gmail.com>';
	const CLASS_VERSION = '0.9.0';

	// CLASS PROPERTIES //
	public $default_index = array();

	// PRIVATE VARIABLES //
	protected $authority;
	protected $data;
	protected $fragment;
	protected $host;
	protected $is_dir = false;
	protected $matrix;
	protected $method = 'GET';
	protected $props;
	protected $path = '/';
	protected $port;
	protected $query;
	protected $resource = '';
	protected $resource_extension = null;
	protected $scheme;
	private $signature;
	private $slug;
	protected $uri_depth = 0;
	protected $path_normalized;
	protected $path_slug;
	protected $uri;

	public function __construct($init=false)
	{
		// Setup Properties RWI //
		$this->props = array();// 1 = read, 2 = write, 4 = isset
		$this->props['authority'] = 5;
		$this->props['data'] = 5;
		$this->props['fqd'] = 5;
		$this->props['fragment'] = 5;
		$this->props['host'] = 5;
		$this->props['is_dir'] = 5;
		$this->props['matrix'] = 5;
		$this->props['method'] = 5;
		$this->props['path'] = 5;
		$this->props['port'] = 5;
		$this->props['query'] = 5;
		$this->props['resource'] = 5;
		$this->props['resource_extension'] = 5;
		$this->props['scheme'] = 5;
		$this->props['signature'] = 5;
		$this->props['slug'] = 5;
		$this->props['uri'] = 5;
		$this->props['path_normalized'] = 5;
		$this->props['path_slug'] = 5;

		// Class Signature //
		$this->signature = 'Web-App: SimpleThingFramework URI/'.self::CLASS_VERSION;

		if($init !== false)
			$this->init($init);
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

	/**
	 * Generated content when converting this class to a string.
	 */
	public function __toString()
	{
		// return $this->signature.'  uri: '.$this->loop(parse_url($uri));
		return $this->signature.' - Data: '.substr($this->loop($this->data), 6)."\nmethod: {$this->method}\n";
	}

	public function buildURI($uri=null)
	{
		if(!empty($uri))
			$this->parseURI($uri);

		$this->data['uri'] = '';
		if(!empty($this->data['scheme']))
			$this->data['uri'] .= $this->data['scheme'].':';

		if(!empty($this->data['authority']['component']) || !empty($this->data['host']['component']))
		{
			if(!empty($this->data['authority']['component']) && !empty($this->data['host']['component']))
				$this->data['uri'] .= '//'.$this->data['authority']['component'].'@'.$this->data['host']['component'];
			else if(!empty($this->data['authority']['component']))
				$this->data['uri'] .= '//'.$this->data['authority']['component'];
			else
				$this->data['uri'] .= '//'.$this->data['host']['component'];
		}
		if(!empty($this->data['port']))
			$this->data['uri'] .= ':'.$this->data['port'];

		if(!empty($this->data['path']['component']))
			$this->data['uri'] .= $this->data['path']['component'];

		if(!empty($this->data['query']['component']))
			$this->data['uri'] .= '?'.$this->data['query']['component'];

		if(!empty($this->data['fragment']))
			$this->data['uri'] .= '#'.$this->data['fragment'];

		return $this->data['uri'];
	}
	
	protected function checkResource($resource=null)
	{
		$last = substr($this->path, -1);
		if($this->is_dir)
		{
			$this->resource = 'index';
			$this->type = 'html';
		}
		else
		{
			$this->resource = $resource;
			$this->type = pathinfo($resource, PATHINFO_EXTENSION);
		}
		$this->slugIt($this->resource, true);
	}

	protected function getScheme()
	{
		if($this->scheme == null)
		{
			if(!empty($_SERVER['HTTPS']))
				$this->scheme = ( strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == true ) ? 'https' : 'http'; // || test matches string or number 1 as well as string or bool true.
			else if(!empty($_SERVER['SSL_TLS_SNI']) || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') || !empty($_SERVER['HTTPS_KEYSIZE']))
				$this->scheme = 'https';
			else
				$this->scheme = $this->protocol !== null ? strtolower($this->protocol) : 'http';
		}
		return $this->scheme;
	}

	public function getPathNormalized($path=null)
	{
		if($path === null)
		if($this->path_normalized === null)
		{
			$this->path_normalized = $this->slugPath($this->data['path']['uri']);
			$this->data['path_normalized'] = &$this->path_normalized;
			$this->path_slug = &$this->path_normalized;
			$this->data['path_slug'] = &$this->path_slug;
		}
		
		return $this->path_normalized;
	}

	/**
	 * Class initialization method
	 *
	 * @param	$uri	URI to parse. Dynamically generated if not given.
	 * @return	$parsedURI array.
	 */
	public function init($uri=true)
	{
		$this->data = array();
		$this->parseURI($uri);

		$this->authority = & $this->data['authority']['component'];
		$this->uri = & $this->data['uri'];
		$this->host = & $this->data['host']['component'];
		$this->matrix = & $this->data['path']['matrix'];
		$this->port = & $this->data['port'];
		$this->query = & $this->data['query']['parameters'];
		$this->fragment = & $this->data['fragment'];
		// $this->_____ = & $this->data['_____'];
	}

	protected function isDefined($array, $keys){
		$result = null;
		if(is_array($keys)){
			foreach($keys as $key){
				if(isset($array[$key])){
					$result = $array[$key];
					break;
				}
			}
		}else if(isset($array[$keys])){
			$result = $array[$keys];
		}
		return $result;
	}

	public function slugPath($path)
	{
		$path_normalized = explode('/', $path);
		$path_normalized = array_filter($path_normalized);
		$path_normalized = array_filter($path_normalized, function($var){
			return ($var === '.' || $var === '..') ? false : true;
		});
		$path_normalized = array_map(array($this, 'slugIt'), $path_normalized);
		
		return '/'.implode('/', $path_normalized);
	}

	protected function parseAuthority($authority=null)
	{
		if($authority === true)
		{
			$authority = array();
		}

		if(!empty($authority['user']))
		{
			if(!empty($this->data['scheme']))
			$this->data['authority']['component'] = $authority['user'];
			$this->data['authority']['info'] = array();
			$this->data['authority']['user'] = $authority['user'];
			$this->data['authority']['info'][] = $authority['user'];
			$this->data['authority']['pass'] = '';
			if(!empty($authority['pass']))
			{
				$info = explode(':', $authority['pass']);
				$count = count($info);
				for($i = 0; $i < $count; $i++)
				{
					$this->data['authority']['info'][] = $info[$i];
					$this->data['authority']['component'] .= ':'.$info[$i];
					if($i == 0)
						$this->data['authority']['pass'] = $info[$i];
				}
			}
		}
	}

	protected function parseHost($host)
	{
		if($host === true)
			$host = $this->isDefined($_SERVER, array('HTTP_HOST', 'SERVER_NAME'));

		$this->data['host']['component'] = $host;
		$parsedHost = explode('.', $host);
		$count = count($parsedHost);
		if($count > 1)
		{
			$this->data['host']['tld'] = array_pop($parsedHost);
			$this->data['host']['domain'] = array_pop($parsedHost);
			if($count > 2)
				$this->data['host']['subdomain'] = array_pop($parsedHost);
			if($count > 3)
			{
				$this->data['host']['subdomains'] = array();
				for($i = 3; $i < $count; $i++)
					$this->data['host']['subdomains'][] = array_pop($parsedHost);
			}
		}
	}

	protected function parsePath($path)
	{
		if($path === true)
			$path = $this->isDefined($_SERVER, array('REQUEST_URI', 'ORIG_PATH_INFO'));

		$index = strpos($path, '?');
		if($index !== false)
			$path = substr($path, 0, $index);

		$this->is_dir = substr($path, -1) == '/' ? true : false;

		$this->data['path']['component'] = $path;
		preg_match_all('/(?P<segments>\/[^;]*)(;?(?P<matrix_uri>[^\/?#]*))/', $path, $match);
		
		$this->data['path']['uri'] = implode('', $match['segments']);

		if(count($match['matrix_uri']) > 0)
		{
			$this->data['path']['matrix'] = array();
			foreach($match['matrix_uri'] as $k=>$v)
			{
				if(strlen($v) > 0)
				{
					$relative_path = '';
					for($j = 0; $j <= $k; $j++)
						$relative_path .= $match['segments'][$j];
					$parameters = explode(';', $v);
					foreach($parameters as $param)
					{
						// name = value pairs //
						if(strpos($param, '='))
						{
							list($pK, $pV) = explode('=', $param);
							$this->data['path']['matrix'][$relative_path][$pK] = $pV == '' ? 'DELETE_VALUE' : $pV;
							if(strpos($this->data['path']['matrix'][$relative_path][$pK], ',') !== false)
							{
								$values = explode(',', $this->data['path']['matrix'][$relative_path][$pK]);

								// type conversion //
								foreach($values as $key => $value)
									if(is_numeric($value))
										$values[$key] = strpos($value, '.') !== false ? floatval($value) : intval($value);

								$this->data['path']['matrix'][$relative_path][$pK] = $values;
							}
							else
							{
								// type conversion //
								foreach($this->data['path']['matrix'][$relative_path] as $key => $value)
									if(is_numeric($value))
										$this->data['path']['matrix'][$relative_path][$key] = strpos($value, '.') !== false ? floatval($value) : intval($value);
							}
						}
						else
							$this->data['path']['matrix'][$relative_path][$param] = null;
					}
				}
			}
		}
		// $this->data['path']['match'] = $match;

		if(strpos($this->data['path']['uri'], '/') !== false)
		{
			$parts = explode('/', $this->data['path']['uri']);
			$parts = array_values(array_filter($parts));
			$this->uri_depth = count($parts);
			$this->data['path']['depth'] = $this->uri_depth;
			if($this->uri_depth > 0)
			{
				if(!$this->is_dir)
					$this->resource = array_pop($parts);

				$this->data['path']['list'] = $parts;
				$this->data['path']['list'][] = $this->resource;
			}
			else
			{
				$this->data['path']['list'] = array();
			}
			$this->path = $this->data['path']['uri'];
		}
		else
			$this->resource = '/';

		$this->data['resource'] = $this->resource;
	}

	protected function parsePort($port=null)
	{
		$guess = false;
		if($port === true || empty($port))
		{
			if($port)
			if(!empty($_SERVER['SERVER_PORT']))
				$port = intval($_SERVER['SERVER_PORT']);
			
			switch($this->data['scheme'])
			{
				case 'https':	$port = 443;	break;
				case 'http':
					// 80 is the default to don't list it.
					if($port != 80)
					{
						break;
					}
				default:		$port = null;		break;
			}
		}
		else if(empty($port))
			$guess = true;

		if($guess)
		{
			
		}
		
		if(empty($port))
			$this->data['port'] = null;
		else
			$this->data['port'] = intval($port);
	}

	protected function parseQuery($query=null)
	{
		if($query === true)
			$query = $_SERVER['QUERY_STRING'];

		if(!empty($query))
		{
			$this->data['query'] = array('component'=>$query, 'parameters'=>array());
			parse_str($query, $this->data['query']['parameters']);
		}
	}

	protected function parseScheme($scheme=null)
	{
		if($scheme === true)
			$scheme = $this->getScheme();

		if(empty($scheme))
			unset($this->data['scheme']);
		else
			$this->data['scheme'] = $scheme;
	}

	protected function parseURI($uri=null)
	{
		$this->method = $this->isDefined($_SERVER, array('HTTP_METHOD', 'REQUEST_METHOD')); // IIS / Apache ?
		$this->data = array('uri'=>'');
		$this->data['scheme'] = null;
		$this->data['authority'] = array('component'=>'');
		$this->data['host'] = array('component'=>'');
		$this->data['port'] = null;
		$this->data['path'] = array('component'=>'');
		$this->data['query'] = array('component'=>'', 'parameters'=>array());
		$this->data['fragment'] = array('component'=>'');

		if($uri === true)
		{
			$this->parseScheme(true);
			$this->data['authority'] = array('component'=>'');
			$this->parseHost(true);
			$this->parsePort(true);
			$this->parsePath(true);
			$this->parseQuery(true);
			$this->data['fragment'] = '';
			$this->buildURI();
		}
		else if(!empty($uri))
		{
			$this->data['uri'] = $uri;
			$parsedURL = parse_url($uri);

			// Scheme //
			$this->parseScheme($parsedURL['scheme']);

			// Authority - User-Info //
			$authority = array();
			if(!empty($parsedURL['user']))
				$authority['user'] = $parsedURL['user'];
			if(!empty($parsedURL['pass']))
				$authority['pass'] = $parsedURL['pass'];
			$this->parseAuthority($authority);

			// Host //
			$this->parseHost($parsedURL['host']);

			// Port //
			$this->parsePort($parsedURL['port']);

			// Path //
			$this->parsePath($parsedURL['path']);

			// Query //
			$this->parseQuery($parsedURL['query']);

			// Fragment //
			if(!empty($parsedURL['fragment']))
				$this->data['fragment'] = $parsedURL['fragment'];
		}

		$this->getPathNormalized();
		$this->checkResource($this->resource);
		$this->data['resource_extension'] = $this->resource_extension;
		$this->data['is_dir'] = $this->is_dir;
	}

	/**
	 * Method to slugify a string
	 *
	 * @param	$item	String to slugify.
	 * @return	Slugified string
	 */
	public function slugIt($item, $set_data=false)
	{
		$path_info = pathinfo($item); // Backup for older versions of PHP
		$filename = $path_info['filename'];
		$filename = strtolower($filename);
		$filename = urldecode($filename);
		$filename = str_replace(' ', '-', $filename);
		$filename = str_replace('&', '-and-', $filename);
		$filename = preg_replace('/[^a-z0-9_-]/', '', $filename);
		$filename = preg_replace('/-+/', '-', $filename);

		if($set_data)
		{
			$this->slug = $filename;
			$this->data['slug'] = $filename;
			$this->resource_extension = $path_info['extension'];
			$this->data['resource_extension'] = $path_info['extension'];
		}

		return $filename;
	}
}
?>