<?php
/**
 * HTTP Header Parsing and Creation class base on RFC-2616
 *
 * @author	RuneImp <runeimp@gmail.com>
 * @version	0.6.0
 * @see	http://tools.ietf.org/html/rfc1945 - Hypertext Transfer Protocol -- HTTP/1.0
 * @see	http://tools.ietf.org/html/rfc2817 - Hypertext Transfer Protocol -- HTTP/1.1
 * @see	http://tools.ietf.org/html/rfc4918 - HTTP Extensions for Web Distributed Authoring and Versioning (WebDAV)
 * @see	http://tools.ietf.org/html/rfc6266 - Use of the Content-Disposition Header Field in HTTP
 */
/*
 * Change Log:
 * -----------
 * 2014-03-19	v0.6.0		Converted to use PHP namespaces.
 * 2012-06-13	v0.5.0	Initial class creation with Matrix URI support
 *
 * ToDo:
 * -----
 * [ ] Meet all RFC 1945 header requirements
 * [ ] Meet all RFC 2817 header requirements
 * [ ] Meet all RFC 4918 header requirements - At least handle PROPFIND WebDAV method in some reasonable fashion.
 * [ ] Meet all RFC 6266 header requirements
 */

namespace RuneImp\STF\Net;

class HTTPRequestHeaders
{
	// CLASS CONSTANTS //
	const CLASS_AUTHOR = 'RuneImp <runeimp@gmail.com>';
	const CLASS_VERSION = '0.5.0';

	// PRIVATE VARIABLES //
	protected $headers;
	protected $host;
	protected $method = 'GET';
	protected $props;
	protected $requestLine;
	protected $rest;
	protected $scheme = 'http';
	protected $server;
	protected $ssl;
	private $signature;

	public function __construct($init=false)
	{
		// Setup Properties RWI //
		$this->props = array();// 1 = read, 2 = write, 4 = isset
		$this->props['headers'] = 5;
		$this->props['host'] = 5;
		$this->props['method'] = 5;
		$this->props['requestLine'] = 5;
		$this->props['scheme'] = 5;
		$this->props['server'] = 5;
		$this->props['signature'] = 5;
		$this->props['ssl'] = 5;

		// Class Signature //
		$this->signature = 'Web-App: SimpleThingFramework HTTPRequestHeaders/'.self::CLASS_VERSION;

		if($init)
			$this->init();
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
		else if(array_key_exists($prop, $this->headers))
			return $this->headers[$prop];
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
		// return $this->signature.' - Data: '.Util::loop(array('headers'=>$this->headers, 'rest'=>$this->rest, 'server'=>$this->server, '_SERVER'=>$_SERVER))."\n";
		// return $this->signature.' - Data: '.Util::loop(array('headers'=>$this->headers, 'rest'=>$this->rest, 'server'=>$this->server, 'ssl'=>$this->ssl))."\n";
		return $this->signature.' - Data: '.Util::loop(array('headers'=>$this->headers, 'rest'=>$this->rest, 'server'=>$this->server))."\n";
		// return $this->signature.' - Data: '.Util::loop(array('headers'=>$this->headers, 'rest'=>$this->rest))."\n";
	}

	/**
	 * Method to parse all headers from the $_SERVER array.
	 *
	 * @see http://us.php.net/manual/en/function.getallheaders.php#104307
	 */
	public function getAllHeaders()
	{
		if(!empty($this->headers))
		{
			$this->headers = array();

			foreach ($_SERVER as $name => $value)
			{
				if(substr($name, 0, 5) == 'HTTP_')
				{
					$name = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
					$headers[$name] = $value;
				}
				else if($name == "CONTENT_TYPE")
					$headers['Content-Type'] = $value;
				else if($name == "CONTENT_LENGTH")
					$headers['Content-Length'] = $value;
			}
		}
	}

	/**
	 * Class initialization method
	 *
	 * @return	void
	 * @see http://php.net/manual/en/reserved.variables.server.php
	 */
	public function init()
	{
		// CONTENT_TYPE  CONTENT_LENGTH
		if(empty($_SERVER['REQUEST_TIME']))
			$_SERVER['REQUEST_TIME'] = time();

		if(function_exists('getallheaders'))
			$headers = getallheaders();

		if(is_array($headers))
		{
			$this->headers = array();

			foreach($headers as $k=>$v)
			{
				// $k = preg_replace('/([a-z]+)/e', "strtoupper('$1')", $k);
				$k = str_replace('-', '_', strtolower($k));
				$this->headers[$k] = $v;
			}
			unset($headers);
		}

		$this->rest = array();
		$this->server = array();
		$this->ssl = array();

		// $this->rest['document_root'] = getenv('DOCUMENT_ROOT');
		$this->rest['query_data'] = array();

		foreach($_SERVER as $k=>$v)
		{
			$lower_k = strtolower($k);
			$underscore = strpos($k, '_');
			$prefix = $underscore !== false ? substr($k, 0, $underscore) : $k;
			// echo '$prefix: '.$prefix; exit();
			if($prefix == 'HTTP' && $k != 'HTTP_CLIENT_IP' && $k != 'HTTP_METHOD' && $k != 'HTTP_X_FORWARDED_FOR')
			{
				$key = substr($lower_k, 5);
				$this->headers[$key] = $v;
			}
			else if($prefix == 'SERVER')
			{
				switch($k)
				{
					case 'SERVER_NAME':
						if(empty($this->headers['host']))
							$this->headers['host'] = $v;
						else
							$this->rest['server_name'] = $v;
						break;
					case 'SERVER_PROTOCOL':
						$this->rest['protocol'] = $v;
						break;
					case 'SERVER_ADDR':
						$this->rest['local_ip'] = $v;
						break;
					case 'SERVER_PORT':
						$this->rest['local_port'] = $v;
						break;
					case 'SERVER_SIGNATURE':
						$this->server[substr($lower_k, 7)] = trim($v);
						break;
					default:
						$this->server[substr($lower_k, 7)] = $v;
						break;
				}
			}
			else if($prefix == 'REMOTE')
			{
				if($k == 'REMOTE_ADDR')
					$this->rest['remote_ip'] = $v;
				else
					$this->rest[$lower_k] = $v;
			}
			else if($prefix == 'REQUEST')
			{
				if($k == 'REQUEST_METHOD' && empty($this->rest['method']))
					$this->rest['method'] = $v;
				else if($k == 'REQUEST_URI')
					if(empty($this->rest['uri_path']))
						$this->rest['uri_path'] = $v;
				else
					$this->rest[$lower_k] = $v;

			}
			else if($prefix == 'SSL')
				$this->ssl[substr($lower_k, 4)] = trim($v);
			else
			{
				switch($k)
				{
					case 'SSL_TLS_SNI':
						if(empty($this->headers['host']))
							$this->headers['host'] = $v;
						break;
					case 'HTTP_METHOD':
						if(empty($this->rest['method']))
							$this->rest['method'] = $v;
						break;
					case 'HTTP_CLIENT_IP':// check ip from share internet
					case 'HTTP_X_FORWARDED_FOR':// to check ip is pass from proxy
						if(!empty($this->rest['remote_ip']))
							$this->rest['remote_ip'] = $v;
							$this->rest[$lower_k] = $v;
						break;
					case 'HTTPS':
						$this->rest['https'] = strtolower($v) == 'on' ? true : false;
						break;
					case 'ORIG_PATH_INFO':
						if(empty($this->rest['uri_path']))
							$this->rest['uri_path'] = $v;
						break;
					case 'QUERY_STRING':
						parse_str($v, $this->rest['query_data']);
						break;
					default:
						$this->server[$lower_k] = $v;
						break;
				}
			}
		}

		ksort($this->headers);
		ksort($this->rest);
		ksort($this->server);
		ksort($this->ssl);
	}
	/*
	Proxy Headers:
	--------------
	HTTP_PRAGMA, HTTP_XONNECTION, HTTP_CACHE_INFO, HTTP_XPROXY, HTTP_PROXY, HTTP_PROXY_CONNECTION,
	HTTP_CLIENT_IP, HTTP_VIA, HTTP_X_COMING_FROM, HTTP_X_FORWARDED_FOR, HTTP_X_FORWARDED,
	HTTP_COMING_FROM, HTTP_FORWARDED_FOR, HTTP_FORWARDED, ZHTTP_CACHE_CONTROL
	*/
}
?>