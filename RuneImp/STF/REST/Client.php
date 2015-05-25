<?php
/**
 * SimpleThingFramework REST Client
 *
 * @author	RuneImp <runeimp@eye.fi>
 * @version	1.1.0
 */
/*
 * Change Log:
 * -----------
 * 2012-07-17	v1.1.0	Updated defaults for options not past via GPC.
 * 2012-06-00	v1.0.0	Initial class creation
 *
 * ToDo:
 * -----
 * [ ] Setup notification header to log or echo.
 */
class STF_REST_Client extends STF_Base
{
	// CLASS CONSTANTS //
	const CLASS_AUTHOR = 'runeimp@gmail.com';
	const CLASS_VERSION = '1.1.0';

	// PUBLIC CLASS PROPERTIES //
	protected $default_lang = 'en-US';

	// PRIVATE CLASS PROPERTIES //
	protected $content;
	protected $follow_location;
	protected $header_delimiter;
	protected $header;
	protected $host;
	protected $ignore_errors;
	protected $initialized = false;
	protected $max_redirects;
	protected $method;
	protected $protocol_version = '1.0';
	protected $proxy;
	protected $user_agent;
	protected $request_fulluri;
	protected $request_line;
	protected $response_content;
	protected $response_headers;
	protected $response_length;
	protected $response_status;
	protected $response_type;
	private $signature;
	protected $timeout;

	public function __construct()
	{
		$this->props = array();
		// $this->props['_____'] = 5;// 1 = read, 2 = write, 4 = isset
		$this->props['content'] = 5;// 1 = read, 2 = write, 4 = isset
		$this->props['default_lang'] = 7;// 1 = read, 2 = write, 4 = isset
		$this->props['follow_location'] = 5;
		$this->props['header'] = 5;
		$this->props['header_delimiter'] = 5;
		$this->props['host'] = 5;
		$this->props['ignore_errors'] = 5;
		$this->props['method'] = 5;
		$this->props['max_redirects'] = 5;
		$this->props['protocol_version'] = 5;
		$this->props['proxy'] = 5;
		$this->props['request_fulluri'] = 5;
		$this->props['request_line'] = 5;
		$this->props['response_content'] = 5;
		$this->props['response_headers'] = 5;
		$this->props['response_length'] = 5;
		$this->props['response_status'] = 5;
		$this->props['response_type'] = 5;
		$this->props['timeout'] = 5;
		$this->props['user_agent'] = 5;

		// Class Signature //
		$this->signature = 'SimpleThingFramework STF_REST_Client/'.self::CLASS_VERSION;
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
		$props = new stdClass;
		foreach($this->props as $k=>$v)
		{
			if(($this->props[$k] & 1) == 1)
				if($k == 'response_content' && strlen($this->$k) > 38)
					$props->$k = substr($this->$k, 0, 38).'...';
				else
					$props->$k = $this->$k;
		}
		return $this->signature.' - '.substr($this->loop($props), 9)."\n";
	}

	/**
	 * Method to initialize the class with the supplied $opts or values found in $_REQUEST.
	 *
	 * @param	$opts	Named array of stream options.
	 * @return void
	 */
	public function init($opts=null)
	{
		// echo __METHOD__.'($opts) $opts: '.$this->loop($opts)."\n";
		if(!is_array($opts))
		{
			$this->method = !empty($_REQUEST['method']) ? strtoupper($_REQUEST['method']) : 'GET';// Default: GET
			$this->header_delimiter = !empty($_REQUEST['header_delimiter']) ? $_REQUEST['header_delimiter'] : '|';// Additional or override headers as a string or indexed array.
			$this->user_agent = !empty($_REQUEST['user_agent']) ? $ $_REQUEST['user_agent'] : 'STF REST Client '.self::CLASS_VERSION;// Default: php.ini user_agent
			$this->content = !empty($_REQUEST['content']) ? $_REQUEST['content'] : '';// Default: empty string
			$this->proxy = !empty($_REQUEST['proxy']) ? $_REQUEST['proxy'] : '';// 'proxy'=>'tcp://proxy.example.com:5100' Default: empty string – PHP 5.1.0 added HTTPS proxying through HTTP proxies
			$this->request_fulluri = !empty($_REQUEST['request_fulluri']) ? (bool) $_REQUEST['request_fulluri'] : false;// Default: false – Required for some non-standard proxies
			$this->follow_location = !empty($_REQUEST['follow_location']) ? (int) $_REQUEST['follow_location'] : 1;// Default: 1 – PHP 5.3.4
			$this->max_redirects = !empty($_REQUEST['max_redirects']) ? (int) $_REQUEST['max_redirects'] : 20;// Default: 20 – PHP 5.1.0
			$this->protocol_version = !empty($_REQUEST['protocol_version']) ? $_REQUEST['protocol_version'] : '1.0';// Default: 1.0 – PHP 5.1.0 though chunked transfer decoding not added until 5.3.0
			$this->timeout = !empty($_REQUEST['timeout']) ? floatval($_REQUEST['timeout']) : 30;// Default: php.ini default_socket_timeout – PHP 5.2.1
			$this->ignore_errors = !empty($_REQUEST['ignore_errors']) ? (bool) $_REQUEST['ignore_errors'] : false;// PHP 5.2.10

			if(!empty($_REQUEST['headers']))
			{
				$headers = $_REQUEST['headers'];
				// echo __METHOD__.'($opts) $headers: '.$this->loop($headers)."\n";

				if(is_array($headers))
				{
					// Do nothing
				}
				else if(strpos($headers, '=') !== false)
				{
					if(strpos($headers, $this->header_delimiter) !== false || strpos($headers, '&') !== false)
					{
						if($this->header_delimiter != '&')
							$headers = str_replace($this->header_delimiter, '&', $_REQUEST['headers']);

						parse_str($headers, $data);
						$headers = $data;
					}
					else
					{
						parse_str($headers, $data);
						$headers = $data;
					}
				}
				else if(strpos($headers, ':') !== false)
				{
					list($k, $v) = explode(':', $_REQUEST['headers']);
					$headers = array($k=>trim($v));
				}

				if(is_array($headers) && !array_key_exists('Accept-Language', $headers))
					$headers['Accept-Language'] = $this->default_lang;

				$this->header = array();
				foreach($headers as $k=>$v)
				{
					$k = preg_replace('/\b([a-z])([a-z]*)/e', "strtoupper($1).strtolower($2)", $k);
					$this->header[$k] = $v;
				}
			}
			else
			{
				$this->user_agent = !empty($opts['http']['user_agent']) ? $ $opts['http']['user_agent'] : 'STF REST Client '.self::CLASS_VERSION;// Default: php.ini user_agent
				$this->content = !empty($opts['http']['content']) ? $opts['http']['content'] : '';// Default: empty string
				$this->proxy = !empty($opts['http']['proxy']) ? $opts['http']['proxy'] : '';// 'proxy'=>'tcp://proxy.example.com:5100' Default: empty string – PHP 5.1.0 added HTTPS proxying through HTTP proxies
				$this->request_fulluri = !empty($opts['http']['request_fulluri']) ? (bool) $opts['http']['request_fulluri'] : false;// Default: false – Required for some non-standard proxies
				$this->follow_location = !empty($opts['http']['follow_location']) ? (int) $opts['http']['follow_location'] : 1;// Default: 1 – PHP 5.3.4
				$this->max_redirects = !empty($opts['http']['max_redirects']) ? (int) $opts['http']['max_redirects'] : 20;// Default: 20 – PHP 5.1.0
				$this->protocol_version = !empty($opts['http']['protocol_version']) ? $opts['http']['protocol_version'] : '1.0';// Default: 1.0 – PHP 5.1.0 though chunked transfer decoding not added until 5.3.0
				$this->timeout = !empty($opts['http']['timeout']) ? floatval($opts['http']['timeout']) : 30;// Default: php.ini default_socket_timeout – PHP 5.2.1
				$this->ignore_errors = !empty($opts['http']['ignore_errors']) ? (bool) $opts['http']['ignore_errors'] : false;// PHP 5.2.10
				$this->header = !empty($opts['http']['header']) ? $opts['http']['header'] : '';// Default: empty string
			}

			if(!empty($opts['http']['method']))
				$this->method = strtoupper($opts['http']['method']);
			else if(!empty($_REQUEST['method']))
				$this->method = strtoupper($_REQUEST['method']);
			else
				$this->method = 'GET';// stream_context default: GET

			if(!empty($_REQUEST['header_delimiter']))
				$this->header_delimiter = $_REQUEST['header_delimiter'];
			else
				$this->header_delimiter = '&';// Additional or override headers as a string or indexed array.

			// echo __METHOD__."() \$this->header: ".$this->loop($this->header)."\n";
		}
		else
		{
			$this->user_agent = !empty($opts['http']['user_agent']) ? $ $opts['http']['user_agent'] : 'STF REST Client '.self::CLASS_VERSION;// Default: php.ini user_agent
			$this->content = !empty($opts['http']['content']) ? $opts['http']['content'] : '';// Default: empty string
			$this->proxy = !empty($opts['http']['proxy']) ? $opts['http']['proxy'] : '';// 'proxy'=>'tcp://proxy.example.com:5100' Default: empty string – PHP 5.1.0 added HTTPS proxying through HTTP proxies
			$this->request_fulluri = !empty($opts['http']['request_fulluri']) ? (bool) $opts['http']['request_fulluri'] : false;// Default: false – Required for some non-standard proxies
			$this->follow_location = !empty($opts['http']['follow_location']) ? (int) $opts['http']['follow_location'] : 1;// Default: 1 – PHP 5.3.4
			$this->max_redirects = !empty($opts['http']['max_redirects']) ? (int) $opts['http']['max_redirects'] : 20;// Default: 20 – PHP 5.1.0
			$this->protocol_version = !empty($opts['http']['protocol_version']) ? $opts['http']['protocol_version'] : '1.0';// Default: 1.0 – PHP 5.1.0 though chunked transfer decoding not added until 5.3.0
			$this->timeout = !empty($opts['http']['timeout']) ? floatval($opts['http']['timeout']) : 30;// Default: php.ini default_socket_timeout – PHP 5.2.1
			$this->ignore_errors = !empty($opts['http']['ignore_errors']) ? (bool) $opts['http']['ignore_errors'] : false;// PHP 5.2.10
			$this->header = !empty($opts['http']['header']) ? $opts['http']['header'] : '';// Default: empty string
		}

		$this->setProperty('method', 'GET', 'strtoupper');

		$this->initialized = true;
	}

	protected function setProperty($name, $default, $function=null)
	{
		if(!empty($this->opts['http'][$name]))
		{
			if($function !== null)
				$this->opts['http'][$name] = call_user_func($function, $this->opts['http'][$name]);

			$this->$name = $this->opts['http'][$name];
		}
		else if(!empty($_REQUEST[$name]))
		{
			if($function !== null)
				$this->$name = call_user_func($function, $_REQUEST[$name]);
			else
				$this->$name = $_REQUEST[$name];
		}
		else
			$this->$name = $default;// stream_context default?
	}

	/**
	 * Echo based notification handler
	 *
	 * @param	$notification_code	Integer representing the notification type.
	 * @param	$severity	The severity of the notification.
	 * @param	$message	The notification message.
	 * @param	$message_code	The notification code.
	 * @param	$bytes_transferred	The current amount of bytes transferred.
	 * @param	$bytes_max	The max bytes that will be transferred.
	 * @return	void
	 */
	public function notification_handler($notification_code, $severity, $message, $message_code, $bytes_transferred, $bytes_max)
	{
		$data = array(
			'notification_code'=>$notification_code,
			'severity'=>$severity,
			'message'=>$message,
			'message_code'=>$message_code,
			'bytes_transferred'=>$bytes_transferred,
			'bytes_max'=>$bytes_max
		);

		switch($notification_code)
		{
			case STREAM_NOTIFY_RESOLVE:
				echo __METHOD__.'(STREAM_NOTIFY_RESOLVE) $data: '.$this->loop($data)."\n";
			case STREAM_NOTIFY_AUTH_REQUIRED:
				echo __METHOD__.'(STREAM_NOTIFY_AUTH_REQUIRED) $data: '.$this->loop($data)."\n";
			case STREAM_NOTIFY_COMPLETED:
				echo __METHOD__.'(STREAM_NOTIFY_COMPLETED) $data: '.$this->loop($data)."\n";
			case STREAM_NOTIFY_FAILURE:
				echo __METHOD__.'(STREAM_NOTIFY_FAILURE) $data: '.$this->loop($data)."\n";
			case STREAM_NOTIFY_AUTH_RESULT:
				echo __METHOD__.'(STREAM_NOTIFY_AUTH_RESULT) $data: '.$this->loop($data)."\n";
				break;
			case STREAM_NOTIFY_REDIRECTED:
				echo __METHOD__.'(STREAM_NOTIFY_REDIRECTED) Being redirected to:', $message."\n";
				echo __METHOD__.'() $data: '.$this->loop($data)."\n";
				break;
			case STREAM_NOTIFY_CONNECT:
				echo __METHOD__.'(STREAM_NOTIFY_CONNECT) Connected...'."\n";
				// echo __METHOD__.'() $data: '.$this->loop($data)."\n";
				break;
			case STREAM_NOTIFY_FILE_SIZE_IS:
				echo __METHOD__.'(STREAM_NOTIFY_FILE_SIZE_IS) Got the filesize: '."{$bytes_max} Bytes. {$message}\n";
				// echo __METHOD__.'() $data: '.$this->loop($data)."\n";
				break;
			case STREAM_NOTIFY_MIME_TYPE_IS:
				echo __METHOD__.'(STREAM_NOTIFY_MIME_TYPE_IS) Found the mime-type: '."{$message}\n";
				// echo __METHOD__.'() $data: '.$this->loop($data)."\n";
				break;
			case STREAM_NOTIFY_PROGRESS:
				echo __METHOD__.'(STREAM_NOTIFY_PROGRESS) Made some progress, downloaded '."{$bytes_transferred} so far...\n";
				// echo __METHOD__.'() $data: '.$this->loop($data)."\n";
				break;
			default:
				echo __METHOD__.'() Unknown Notification Code - $data: '.$this->loop($data)."\n";
				break;
		}
	}

	/**
	 * Method to send an HTTP Request
	 *
	 * @param	$url	URL to send the request to.
	 * @param	$notification_handler	Callback function to handle the stream notifications.
	 * @return	Contents of the request.
	 */
	public function request($url=null, $notification_handler=null)
	{
		if(!$this->initialized)
			$this->init();

		if(!empty($url))
			$url;
		else if($url === null && !empty($_REQUEST['url']))
			$url = $_REQUEST['url'];
		else
			throw new STF_REST_Client_Exception(__METHOD__.'($url) $url not passed via parameter and "url" defined as a key in $_REQUEST.', 100);

		$parsedURL = parse_url($url);
		$this->host = !empty($parsedURL['host']) ? $parsedURL['host'] : '';
		$uri = !empty($parsedURL['path']) ? $parsedURL['path'] : '/';
		if(!empty($parsedURL['query']))
			$uri .= '?'.$parsedURL['query'];
		$this->request_line = "{$this->method} {$uri} HTTP/{$this->protocol_version}";

		// HTTP Stream Context Options //
		$opts = array(
			'http'=>array(
				'method'=>$this->method,
				'header'=>array(),
				'user_agent'=>$this->user_agent,
				'content'=>$this->content,
				'proxy'=>$this->proxy,
				'request_fulluri'=>$this->request_fulluri,
				'follow_location'=>$this->follow_location,
				'max_redirects'=>$this->max_redirects,
				'protocol_version'=>(float) $this->protocol_version,
				'timeout'=>$this->timeout, 
				'ignore_errors'=>$this->ignore_errors
			)
		);

		switch($this->method)
		{
			case 'POST':
				// HTTP POST Method
				$count = count($opts['http']['header']);
				$contentType = 'application/x-www-form-urlencoded';
				for($i = 0; $i < $count; $i++)
				{
					$header = strtolower($opts['http']['header'][$i]);
					if(strpos($header, 'content-type') !== false)
					{
						$index = strpos($header, ':') + 1;
						$contentType = trim(substr($header, $index));
						break;
					}
				}
				if($contentType == 'application/x-www-form-urlencoded')
				{
					$opts['http']['header'][$i] = 'Content-Type: application/x-www-form-urlencoded';
					if(strpos($_REQUEST['content'], '=') !== false)
						if(is_array($_REQUEST['content']))
							$opts['http']['content'] = http_build_query($_REQUEST['content']);
						else
							$opts['http']['content'] = $_REQUEST['content'];
					else
						$opts['http']['content'] = $_REQUEST['content'];
				}
				else
					$opts['http']['content'] = $_REQUEST['content'];
				
				break;
			case 'PUT':
				// HTTP PUT Method
				break;
			case 'DELETE':
				// HTTP DELETE Method
				break;
			case 'HEAD':
				// HTTP HEAD Method
				break;
			case 'OPTIONS':
				// HTTP OPTIONS Method
				break;
			case 'GET':
			default:
				// HTTP GET Method
				break;
		}

		if(is_array($this->header))
			foreach($this->header as $k=>$v)
				$opts['http']['header'][] = "{$k}: {$v}";

		if($notification_handler === null)
			$notification_handler = array($this, 'notification_handler');

		if(empty($opts['http']['header']))
			$opts['http']['header'] = null;

		// unset($opts['http']['header']);
		// unset($opts['http']['method']);
		// unset($opts['http']['user_agent']);
		// unset($opts['http']['content']);
		// unset($opts['http']['proxy']);
		// unset($opts['http']['request_fulluri']);
		// unset($opts['http']['follow_location']);
		// unset($opts['http']['max_redirects']);
		// unset($opts['http']['protocol_version']);
		// unset($opts['http']['timeout']);
		// unset($opts['http']['ignore_errors']);

		// echo __METHOD__.'($url) $opts: '.$this->loop($opts)."\n";
		$context = stream_context_create($opts);
		if($notification_handler !== false)
		{
			$contextParams = array('notification'=>$notification_handler);
			stream_context_set_params($context, $contextParams);
		}
		try{
			$this->response_content = file_get_contents($url, false, $context);
		}catch(Exception $e){
			throw new STF_REST_Client_Exception($e->getMessage(), $e->getCode());
		}
		if(!empty($http_response_header))
			$this->response_headers = $http_response_header;
		else
			$this->response_headers = array();
		$headers = '';
		if(function_exists('http_parse_headers'))
		{
			foreach($this->response_headers as $header)
				$headers .= "{$header}\r\n";
			$this->response_headers = http_parse_headers($headers);
		}
		else
		{
			$this->response_headers = array();
			if(!empty($http_response_header))
			{
				foreach($http_response_header as $header)
				{
					if(strpos($header, ':') !== false)
						list($k, $v) = explode(':', $header);
					else if(substr($header, 0, 4) == 'HTTP')
					{
						$v = $header;
						$k = 0;
					}
					$this->response_headers[$k] = trim($v);
				}
			}
		}

		if(!empty($this->response_headers[0]))
		{
			preg_match('/(?P<protocol>[^ ]+) +(?P<code>\d+) +(?P<message>.*)/', $this->response_headers[0], $status);
			$this->response_status = array();
			$this->response_status['protocol'] = $status['protocol'];
			$this->response_status['code'] = $status['code'];
			$this->response_status['message'] = $status['message'];
		}
		else
			$this->response_status = 'unknown';

		if(!empty($this->response_headers['Content-Length']))
			$this->response_length = (int) $this->response_headers['Content-Length'];
		else
			$this->response_length = -1;

		if(!empty($this->response_headers['Content-Type']))
			$this->response_type = $this->response_headers['Content-Type'];
		else
			$this->response_type = 'unknown';

		return $this->response_content;
	}
}

class STF_REST_Client_Exception extends Exception
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