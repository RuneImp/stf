<?php
/**
 * URI Parsing and Creation class base on RFC-3986
 * Some code based on STURL 2.4.1.
 *
 * @author	RuneImp <runeimp@gmail.com>
 * @version	1.0.0
 */
/*
 * Change Log:
 * -----------
 * 2012-09-06	v1.0.0	Initial class creation
 *
 * ToDo:
 * -----
 * [ ] ...
 */
class STF_Wiki_Page extends STF_Base
{
	// CLASS CONSTANTS //
	const CLASS_AUTHOR = 'RuneImp <runeimp@gmail.com>';
	const CLASS_VERSION = '1.0.0';
	const FILE_NOT_FOUND = 1;
	const FILE_NOT_READABLE = 2;

	// CLASS PROPERTIES //

	// PRIVATE VARIABLES //
	protected $log;
	protected $reserved_headers;
	protected $wiki_path;
	
	public function __construct($wiki_path)
	{
		$this->wiki_path = $wiki_path;

		$this->log = new STF_Logger(STF_Logger::LOG_LEVEL_INFO);
		$this->log->css_class = 'logger';
		$this->log->css_inline = 'color:#000';
		// $this->log->echo_output = true;
		// $this->log->echo_html = true;
		$this->log->file_output = true;

		$this->reserved_headers = array('content_type');

		$this->log->info(null, false);
	}

	public function load($wiki_file)
	{
		$wiki_file = $this->wiki_path.$wiki_file.'.wiki';
		$file_exists = file_exists($wiki_file);
		if($file_exists)
			$is_readable = is_readable($wiki_file);
		else
			$is_readable = false;

		$this->log->debug($this->log->wrap($wiki_file, '$wiki_file'), false);
		$this->log->debug($this->log->wrap($file_exists, '$file_exists'), false);
		$this->log->debug($this->log->wrap($is_readable, '$is_readable'), false);

		if($file_exists && $is_readable)
		{
			$file_array = file($wiki_file);
			$file_array = array_map('rtrim', $file_array);
			$is_metadata = true;
			$wiki_meta_data = array();
			$wiki_content = '';
			foreach($file_array as $line)
			{
				if($is_metadata)
				{
					if(strpos($line, ':') === false)
					{
						$is_metadata = false;
					}
					else
					{
						list($key, $value) = explode(':', $line);
						$key = strtolower(trim($key));
						$key = str_replace('-', '_', $key);
						$value = trim($value);

						if(strpos($key, '.') === false)
						{
							$wiki_meta_data[$key] = $value;
						}
						else
						{
							$keys = explode('.', $key);
							$ref = &$wiki_meta_data;
							// $this->log->debug($this->log->wrap($keys, '$keys'), false);
							for($i = count($keys); $i > -1; $i--)
							{
								$key = array_shift($keys);
								if(!isset($ref[$key]) && $i > 1)
									$ref[$key] = array();

								if($i === 0)
									if(is_array($ref))
										$ref[] = $value;
									else if(!empty($ref))
										$ref = array($ref, $value);
									else
										$ref = $value;
								else
									$ref = &$ref[$key];
							}

							$this->log->debug($this->log->wrap($wiki_meta_data, '$wiki_meta_data'), false);
						}
					}
				}
				else
				{
					$wiki_content .= "{$line}\n";
				}
			}
		}
		else if(!$file_exists)
		{
			throw new STF_Wiki_PageException('Content does not exist.', self::FILE_NOT_FOUND);
			return false;
		}
		else if(!$is_readable)
		{
			throw new STF_Wiki_PageException('Content is not readable.', self::FILE_NOT_READABLE);
			return false;
		}

		$partials = array();
		if(!empty($wiki_meta_data['partials']))
		{
			foreach($wiki_meta_data['partials'] as $k=>$v)
				$partials[$k] = $v;

			unset($wiki_meta_data['partials']);
		}

		$template_data = array();
		foreach($wiki_meta_data as $k=>$v)
			if(!in_array($k, $this->reserved_headers))
				$template_data[$k] = $v;

		$result = array();
		$result['meta_data'] = $wiki_meta_data;
		$result['content'] = $wiki_content;
		$result['template_data'] = $template_data;
		$result['template_partials'] = $partials;

		return $result;
	}

	public function render($wiki_content)
	{
		// $debug = array();
		// $debug['file_array'] = $file_array;
		// $debug['wiki_header'] = $wiki_meta_data;
		// $debug['wiki_content'] = $wiki_content;
		// header('Content-Type: text/plain');
		// echo Util::loop($debug);
		// exit();

		$textile = new Textile('html5');
		
		$lite = false;
		$encode = false;
		$noimage = false;
		$strict = true;
		$rel = '';
		$content = $textile->TextileThis($wiki_content, $lite, $encode, $noimage, $strict, $rel); // s($text, $lite='', $encode='', $noimage='', $strict='', $rel='')
		// $content = "\t\t\t".str_replace("\n\t", "\n\t\t\t\t", $content);
		
		return $content;
	}
}


/**
 * STF Wiki Page Exception
 */
class STF_Wiki_PageException extends Exception
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