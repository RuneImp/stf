<?php
/**
 * SimpleThingFramework Network HTTP Utility class
 *
 * @author	RuneImp <runeimp@gmail.com>
 * @version	1.0.0
 */
/*
 * Change Log:
 * -----------
 * 2012-09-12	v1.0.0	Initial class creation
 *
 * ToDo:
 * -----
 * [ ] ...
 */
class STF_Net_HTTP_Util extends STF_Base
{
	// CLASS CONSTANTS //
	const CLASS_AUTHOR = 'RuneImp <runeimp@gmail.com>';
	const CLASS_VERSION = '1.0.0';

	// const CASE_LOWER
	const CHARS_ASCII_CTL = "\0\a\b\t\n\v\f\r";// NULL, (audible) BEL, BS, TAB, NL, VT, FF, CR
	const CHARS_ASCII_CHAR = ' !"#$%&\'()*+,-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{|}~';
	const CHARS_TOKEN = '!#$%&\'*+-.0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ^_`abcdefghijklmnopqrstuvwxyz|~';
	const CHARS_SEPERATORS = '"(),/:;<=>?@[\]{}';

	const KEY_CASE_LOWER = 'key.case.Lower';
	const KEY_CASE_TITLE = 'key.case.Title';
	const KEY_CASE_UPPER = 'key.case.Upper';
	const KEY_SEPARATOR_SPACE = ' ';
	const KEY_SEPARATOR_HYPHEN = '-';
	const KEY_SEPARATOR_UNDERSCORE = '_';

	// CLASS PROPERTIES //
	public $collapse_header_values = false;

	// CLASS VARS //
	protected $key_case;
	protected $key_separator = '-';
	protected $log;
	protected $headers_list = array();
	protected $headers_src = '';

	public function __construct($key_case=self::KEY_CASE_TITLE, $key_separator=self::KEY_SEPARATOR_HYPHEN)
	{
		$this->key_case = $key_case;
		$this->key_separator = $key_separator;

		$this->log = new STF_Logger(STF_Logger::LOG_LEVEL_WARN);
		// $this->log->echo_output = true;
		// $this->log->file_output = true;
	}

	public function checkClientLanguage()
	{
		// $langcode = (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
		// $langcode = (!empty($langcode)) ? explode(";", $langcode) : $langcode;
		// $langcode = (!empty($langcode['0'])) ? explode(",", $langcode['0']) : $langcode;
		// $langcode = (!empty($langcode['0'])) ? explode("-", $langcode['0']) : $langcode;
		// return $langcode['0'];

		$langcode = (!empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : '';
		if(strpos(';', $langcode) !== false)
		list($langcode) = (!empty($langcode)) ? explode(";", $langcode) : $langcode;
		$langcode = (!empty($langcode['0'])) ? explode(",", $langcode['0']) : $langcode;
		$langcode = (!empty($langcode['0'])) ? explode("-", $langcode['0']) : $langcode;
		return $langcode['0'];
	}

	public function formatFieldName($field_name)
	{
		if($this->key_case == self::KEY_CASE_LOWER || $this->key_case == self::KEY_CASE_TITLE)
			$field_name = strtolower($field_name);
		else if($this->key_case == self::KEY_CASE_UPPER)
			$field_name = strtoupper($field_name);

		$field_name = str_replace('-', ' ', $field_name);

		if($this->key_case == self::KEY_CASE_TITLE)
			$field_name = ucwords($field_name);

		return str_replace(' ', $this->key_separator, $field_name);
	}

	public function parseHeaders($content=null)
	{
		if($content !== null)
			if(!is_array($content) && is_string($content))
				$content = explode("\n", $content);
			else
				throw new STF_Exception('Supplied content is not a string or array of strings');
		else if($content === null && !empty($this->headers_src))
			$content = explode("\n", $this->headers_src);

		$this->headers_list = array();
		foreach($content as $line)
		{
			$this->log->debug($this->log->wrap($line, '$line'));
			if(trim($line) !== '')
			{
				if(strpos($line, ':') !== false)
				{
					$this->log->debug('Has colon');
					list($field_name, $field_value) = explode(':', $line, 2);
					$field_name = $this->formatFieldName($field_name);
					if(array_key_exists($field_name, $this->headers_list))
					{
						$this->log->debug($field_name.' already in headers_list. :: is_array($this->headers_list[$field_name]) : '.is_array($this->headers_list[$field_name]));
						if(is_array($this->headers_list[$field_name]))
							$this->headers_list[$field_name][] = trim($field_value);
						else
							$this->headers_list[$field_name] = array(trim($field_value));
					}
					else
					{
						$this->log->debug('$this->headers_list["'.$field_name.'"] = '.trim($field_value));
						$this->headers_list[$field_name] = trim($field_value);
					}
				}
				else if($line[0] == ' ' || $line[0] == "\t")
				{
					$this->log->debug('$this->headers_list["'.$field_name.'"] .= trim('.$line.')');
					$this->headers_list[$field_name] = $this->headers_list[$field_name].' '.trim($line);
				}
			}
			else
				break;
		}

		$this->log->info($this->log->wrap($this->headers_list, '$this->headers_list'));

		foreach($this->headers_list as $k=>$params)
		{
			$this->headers_list[$k] = $this->parseParams($params);
			if($this->collapse_header_values)
				$this->headers_list[$k] = $this->headers_list[$k][0];
		}

		return $this->headers_list;
	}

	public function parseParams($params)
	{
		$params = array_filter(explode(';', $params));
		$result = array();
		foreach($params as $param)
		{
			$tokens = array_filter(explode(',', $param));
			foreach($tokens as $token)
			{
				list($k, $v) = explode('=', $token);
				$k = trim($k);
				$v = trim($v);
				if(empty($v))
					$result[] = $k;
				else
					$result[] = array($k=>$v);
			}
		}
		return $result;
	}

	public function loadHeaders($file)
	{
		$this->headers_src = '';
		foreach(file($file) as $line)
			if(trim($line) !== '')
				$this->headers_src .= $line;
			else
				break;

		return $this->headers_src;
	}

	public function parseHeaders__($header)
	{
		// @see http://www.php.net/manual/en/function.http-parse-headers.php#77241
		// $retVal = array();
		// $fields = explode("\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
		// foreach( $fields as $field ) {
		// 	if( preg_match('/([^:]+): (.+)/m', $field, $match) ) {
		// 		$match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
		// 		if( isset($retVal[$match[1]]) ) {
		// 			$retVal[$match[1]] = array($retVal[$match[1]], $match[2]);
		// 		} else {
		// 			$retVal[$match[1]] = trim($match[2]);
		// 		}
		// 	}
		// }
		// return $retVal;


		// @see http://snipplr.com/view/17242/
		// $string = $header;
		// $headers = array();
		// $content = '';
		// $str = strtok($string, "\n");
		// $h = null;
		// while($str !== false)
		// {
		// 	if($h and trim($str) === '')
		// 	{
		// 		$h = false;
		// 		continue;
		// 	}

		// 	if($h !== false and false !== strpos($str, ':'))
		// 	{
		// 		$h = true;
		// 		list($headername, $headervalue) = explode(':', trim($str), 2);
		// 		$headername = strtolower($headername);
		// 		$headervalue = ltrim($headervalue);
		// 		if (isset($headers[$headername]))
		// 			$headers[$headername] .= ',' . $headervalue;
		// 		else
		// 			$headers[$headername] = $headervalue;
		// 	}
		// 	if($h === false)
		// 	{
		// 		$content .= $str."\n";
		// 	}
		// 	$str = strtok("\n");
		// }
		// return array('headers'=>$headers, 'content'=>trim($content));

		// @see http://aldo.mx/46
		$retVal = array ();
		$fields = explode( "\r\n", preg_replace( '/\x0D\x0A[\x09\x20]+/', ' ', $header ) );
		foreach ( $fields as $field )
		{
			if ( preg_match( '/([^:]+):(.+)/m', $field, $match ) )
			{
				$match[1] = preg_replace( '/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower( trim( $match[1] ) ) );
				$match[2] = trim( $match[2] );

				if ( isset($retVal[$match[1]]) )
				{
					if ( is_array( $retVal[$match[1]] ) )
					{
						$retVal[$match[1]][] = $match[2];
					}
					else
					{
						$retVal[$match[1]] = array ( $retVal[$match[1]], $match[2] );
					}
				}
				else
				{
					$retVal[$match[1]] = $match[2];
				}
			}
			else if ( preg_match( '/([A-Za-z]+) (.*) HTTP\/([\d.]+)/', $field, $match ) )
			{
				$retVal["Request-Line"] = array (
					"Method"	   => $match[1],
					"Request-URI"  => $match[2],
					"HTTP-Version" => $match[3]
				);
			}
			else if ( preg_match( '/HTTP\/([\d.]+) (\d+) (.*)/', $field, $match ) )
			{
				$retVal["Status-Line"] = array (
					"HTTP-Version"  => $match[1],
					"Status-Code"   => $match[2],
					"Reason-Phrase" => $match[3]
				);
			}
		}
		return $retVal;

		/*
		$string = $header;
		$headers = array();
		$content = '';
		$str = strtok($string, "\n");
		$in_header = true;
		while($str !== false)
		{
			if($in_header && trim($str) === '')
			{
				$in_header = false;
				continue;
			}

			if($in_header)
			{
				if(strpos($str, ':') !== false)
				{
					$in_header = true;
					// list($headername, $headervalue) = explode(':', trim($str), 2);
					list($headername, $headervalue) = explode(':', $str, 2);
					$headername = str_replace(' ', '-', ucwords(str_replace('-', ' ', $headername)));
					$headervalue = ltrim($headervalue);
					if(isset($headers[$headername]))
						if(!is_array($headers[$headername]))
							$headers[$headername] .= $headervalue;//$headers[$headername] = array($headers[$headername]);
						else
							$headers[$headername][] = $headervalue;
					else
						$headers[$headername] = $headervalue;
				}
				else if(preg_match('/^[ \t\h]+(.+)/', $str))
					if(!isset($headers[$headername]))
						throw new STF_Exception('Continuation for an undefined header: '.$headername, 1);
					else
						if(!is_array($headers[$headername]))
							$headers[$headername] .= ' '.ltrim($str);
						else
							$headers[$headername][] = $str;
				else
					$in_header = false;

				$headervalue = null;
			}
			else
				$content .= $str."\n";

			$str = strtok("\n");
		}

		foreach($headers as $k=>$v)
		{
			// echo "$k: $v\n";
			if(strpos($v, ';'))
				$v = explode(';', $v);
			else
				$v = array($v);

			echo '$v: '.print_r($v, true)."\n";

			foreach($v as $i=>$element)
			{
				if(strpos($element, '=') !== false)
				{
					echo '$element: '.print_r($element, true)."\n";
					list($key, $value) = explode('=', $element);
					if(!is_array($v[$i]))
						$v[$i] = array();

					$v[$i][$key] = $value;
				}
			}
		}
		return array('headers'=>$headers, 'content'=>trim($content));
		 */
	}
}

?>