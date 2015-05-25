<?php
/**
 * SimpleThing URL
 *
 * URL Parsing Class
 *
 * Can handle Matrix URIs and will give valid query data if Matrix URIs are
 * used as PHP (at least PHP 5.3.28 and probably all others before) gets
 * confused on what is a query string in the URL when Matrix URIs are used.
 *
 * @author RuneImp <runeimp@gmail.com>
 * @version 3.0.0
 */
/*
 * ChangeLog
 * ---------
 * 2014-03-22	v3.0.0		Complete rewrite using namespacing and new methodology.
 *
 * ToDo:
 * -----
 * [ ] ....
 * [ ] ....
 * [ ] ....
 * [ ] ....
 */

namespace RuneImp\STF\Net\URI;
use \RuneImp\STF;
use \RuneImp\STF\Util;

class STURL implements iURIParser
{
	// CLASS CONSTANTS //
	const CLASS_AUTHOR = 'RuneImp <runeimp@gmail.com>';
	const CLASS_VERSION = '3.0.0';

	// CLASS PROPERTIES //
	protected $authority = false;
	protected $data = false;
	protected $fragment = false;
	protected $host = false;
	protected $http_method = false;
	private $initialized = false;
	protected $matrix = false;
	protected $path = false;
	protected $protocol = false;
	protected $scheme = false;
	protected $signature;
	protected $uri = false;

	public function __construct($url=false)
	{
		// Class Signature //
		$this->signature = 'Web-App: SimpleThingFramework URI/'.self::CLASS_VERSION;

		if($url !== false)
			$this->init($url);
	}

	public function __toString()
	{
		// return $this->signature.' - Data: '.substr(Util::loop($this->data), 6)."\nHTTP Method: {$this->http_method}\n";
		return $this->signature.' - Data: '.Util::loop($this->data)."\nHTTP Method: ".$this->getMethod()."\n";
	}

	public function getAuthority()
	{
		if( $this->authority === false )
			if( $this->initialized )
				$this->parseURI();
			else
				$this->authority = Util::keyIsDefined($_SERVER, array('HTTP_HOST', 'SERVER_NAME'));

		return $this->authority;
	}

	public function getData($key=null)
	{
		$result = null;
		if( !empty($key) )
			if( isset($this->data[$key]) )
				$result = $this->data[$key];
			else
				$result = false;
		else
			$result = array_slice($this->data);

		return $result;
	}

	public function getHost()
	{
		if( $this->host === false )
			if( $this->initialized )
				$this->parseURI();
			else
				$this->host = Util::keyIsDefined($_SERVER, array('HTTP_HOST', 'SERVER_NAME'));

		return $this->host;
	}

	public function getMethod()
	{
		if( $this->http_method === false )
			if( $this->initialized )
				$this->parseURI();
			else
				$this->http_method = Util::keyIsDefined($_SERVER, array('HTTP_METHOD', 'REQUEST_METHOD'), null);

		return $this->http_method;
	}

	public function getPath()
	{
		if( $this->path === false )
			if( $this->initialized )
				$this->parseURI();
			else
			{
				$this->path = Util::keyIsDefined($_SERVER, array('PATH_INFO', 'SCRIPT_URL', 'ORIG_PATH_INFO', 'REQUEST_URI'));
				$tmp = $this->parseMatrix($this->path);
				if( !empty($tmp['matrix']) )
				{
					$this->path = $tmp['path'];
					$this->matrix = $tmp['matrix'];
				}
			}

		return $this->path;
	}

	public function getProtocol()
	{
		if( $this->protocol === false )
			if( $this->initialized )
				$this->parseURI();
			else
				$this->protocol = Util::keyIsDefined($_SERVER, 'SERVER_PROTOCOL');

		return $this->protocol;
	}

	public function getScheme()
	{
		if( $this->scheme === false )
		{
			if( $this->initialized )
				$this->parseURI();
			else
			{
				if(!empty($_SERVER['HTTPS']))
					$this->scheme = ( strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == true ) ? 'https' : 'http'; // || test matches string or number 1 as well as string or bool true.
				else if( !empty($_SERVER['SSL_TLS_SNI']) || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443') || !empty($_SERVER['HTTPS_KEYSIZE']) )
					$this->scheme = 'https';
				else
				{
					$this->scheme = $this->getProtocol();
					if( $this->scheme === STF::UNDEFINED )
						$this->scheme = 'http';
					else
						$this->scheme = strtolower(preg_filter('/(HTTPS?)\/[0-9.]+/', "$1", $this->scheme));
				}
			}
		}

		return $this->scheme;
	}

	protected function buildURI()
	{
		if( !empty($_SERVER['SCRIPT_URI']) )
			$uri = $_SERVER['SCRIPT_URI'];
		else
		{
			$uri = '';
			$uri .= $this->getScheme().'://';
			$uri .= $this->getHost();
			$uri .= $this->getPath();
		}
		return $uri;
	}

	public function init($url=true)
	{
		if( $url === true )
			$url = $this->buildURI();

		$this->data = $this->parseURL($url);

		$this->initialized = true;

		return $this->data;
	}

	public function parseMatrix($path)
	{
		$matrix_re = '/([^;]*);?([^\/]*)/';
		preg_match_all($matrix_re, $path, $matches);
		$match_length = count($matches);

		$result = array();
		$result['path'] = '';
		$result['matrix'] = array();

		for($i = 0; $i < $match_length; $i++)
		{
			$result['path'] .= ( !empty($matches[1][$i]) ) ? $matches[1][$i] : '';
			$j = 0;
			$namespace = '';
			if( !empty($matches[2][$i]) )
			{
				while($j <= $i)
				{
					$namespace .= $matches[1][$j];
					$j++;
				}

				$tmp = str_replace(';', '&', $matches[2][$i]);
				parse_str($tmp, $result['matrix'][$namespace]);
				$result['matrix'][$namespace] = array_map('urldecode', $result['matrix'][$namespace]);
			}
		}
		// echo '<pre>parseMatrix("'.$path.'"): '; print_r($result); echo "</pre>\n";
		return $result;
	}

	public function parseURI($uri=null)
	{
		if( $uri === null )
			$uri = $this->buildURI();

		return $this->parseURL($uri);
	}

	/**
	 * Parse A URL into it's constituent parts
	 *
	 * @param	$url	The URL string to parse or true to use the current URL accessing the script.
	 * @return	Array with all active info.
	 */
	public function parseURL($url)
	{
		$result = array('uri'=>'');
		$uri_re = '/^(([^:\/?#]+):)?(\/\/([^\/?#]*))?([^?#]*)(\?([^#]*))?(#(.*))?/';

		// echo '<pre>$_SERVER: '.print_r($_SERVER, true)."</pre>\n";

		preg_match($uri_re, $url, $matches);
		// echo '<pre>$matches: '.print_r($matches, true)."</pre>\n";

		if( isset($matches[2]) )
			$result['scheme'] = $matches[2];

		// Parse Authority Section
		if( isset($matches[4]) )
		{
			$result['authority'] = array();
			$result['authority']['full'] = null;
			$result['authority']['userinfo'] = array();
			$authority = $matches[4];

			// Check for UserInfo
			if( strpos($authority, '@') !== false )
			{
				$parts = explode('@', $authority);

				// Set Host
				$result['host'] = array_pop($parts);
				$result['authority']['host'] = $result['host'];

				// Prep UserInfo
				$userinfo = explode(':', implode('', $parts));

				// Parse Port
				if( strpos($result['host'], ':') !== false )
				{
					$parts = explode(':', $result['host']);
					$result['host'] = $parts[0];
					$result['authority']['host'] = $parts[0];
					$result['port'] = $parts[1];
					$result['authority']['port'] = $parts[1];
				}
				else
				{
					$result['port'] = '80';
					$result['authority']['port'] = '80';
				}

				$userinfo_length = count($userinfo);

				// Set Full Authority Reference
				$result['authority']['full'] = $matches[4].':'.$result['port'];

				// Parse UserInfo
				if($userinfo_length > 0)
				{
					$result['authority']['userinfo']['full'] = implode(':', $userinfo);
					$result['authority']['userinfo']['user'] = array_shift($userinfo);
					if($userinfo_length > 1)
					{
						$result['authority']['userinfo']['pass'] = array_shift($userinfo);
						if($userinfo_length > 2)
						{
							$result['authority']['userinfo']['data'] = $userinfo;
						}
					}
				}
				else
				{
					unset($result['authority']['userinfo']);
				}
			}
			else
			{
				// Set Minimal Authority Info
				$result['authority']['full'] = $authority;
				$result['authority']['host'] = $authority;
				$result['host'] = $authority;
			}
		}
		if( !empty($result['authority']['full']) )
			$this->authority = $result['authority']['full'];
		if( !empty($result['host']) )
			$this->host = $result['host'];

		// Parse Path Info
		if( !empty($matches[5]) )
		{
			$result['path'] = $matches[5];

			$tmp = $this->parseMatrix($result['path']);
			if( !empty($tmp['matrix']) )
			{
				$result['path'] = $tmp['path'];
				$result['matrix'] = $tmp['matrix'];
				$this->matrix = $result['matrix'];
			}
			$path = array();
			$path['full'] = $result['path'];
			$path['segments'] = explode('/', $path['full']);
			array_shift($path['segments']);
			$result['path'] = $path;
			$this->path = $path['full'];
		}
			

		// Parse Query String
		if( isset($matches[7]) )
		{
			parse_str($matches[7], $result['query']);
			$result['query'] = array_map('urldecode', $result['query']);
			$this->query = $result['query'];
		}
			

		// Set Fragment if Available
		if( isset($matches[9]) )
		{
			$result['fragment'] = $matches[9];
			$this->fragment = $result['fragment'];
		}

		// Set Normalized URI Value
		$result['uri'] = $result['scheme'].'://'.$result['host'];
		if( !empty($result['port']) && $result['port'] !== '80' )
			$result['uri'] .= ':'.$result['port'];

		if( !empty($result['path']) )
			$result['uri'] .= $result['path'];

		if( !empty($matches[7]) )
			$result['uri'] .= '?'.$matches[7];

		if( !empty($result['fragment']) )
			$result['uri'] .= '#'.$result['fragment'];

		$this->uri = $result['uri'];

		$result = Util::cleanArray($result);

		return $result;
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