<?php
/**
 * STF Logger Class
 *
 * Requires PHP 5.3.0+
 *
 * @author	RuneImp <runeimp@gmail.com>
 * @version	0.10.0
 */
/*
 * ChangeLog:
 * ----------
 * 2012-10-23	v0.10.0	Updaetd argLoop method to handle a maximum recursion depth.
 * 2012-09-06	v0.9.1	Added a few label comments.
 * 2012-09-04	v0.9.0	Setup parameters echo_html, echo_output, file_html, and file_output to manage output channel and style.
 * 2012-09-02	v0.8.0	Fixed $backtrace creation for the __exception_handler method. Added HTML output and CSS handling.
 * 2012-09-01	v0.7.0	Fixed $backtrace creation for the __error_handler method.
 * 2012-09-31	v0.6.0	Initial Script Creation
 *
 * ToDo:
 * -----
 * [ ] Optionaly parse error_reporting() for the __error_handler method.
 * [ ] Make factory method to turn Logger into a Singleton with instances that can have
 * 		unique settings per instance which access the Singleton.
 * [ ] Use bitwise logic to allow unique log level options.
 * [*] Implement http://php.net/manual/en/class.errorexception.php#95415
 */

namespace RuneImp\STF;

class Logger
{
	// CLASS CONSTANTS //
	const CLASS_AUTHOR = 'RuneImp <runeimp@gmail.com>';
	const CLASS_VERSION = '0.10.0';

	const LOG_LEVEL_DEBUG = 0xF;
	const LOG_LEVEL_INFO = 0x8;
	const LOG_LEVEL_WARN = 0x4;
	const LOG_LEVEL_ERROR = 0x2;
	const LOG_LEVEL_FATAL = 0x1;

	// CLASS PROPERTIES //
	public $css_class = '';
	public $css_inline = 'margin:0;';
	public $debug_object = false;
	public $echo_html = false;
	public $echo_output = false;
	public $file_html = false;
	public $file_output = false;

	// CLASS VARIABLES //
	protected $include_functions = array('include', 'include_once', 'require', 'require_once');
	protected $log_file;
	protected $log_level;
	protected $max_recursion_depth = 10;
	private static $old_error_handler = null;
	private static $old_exception_handler = null;

	public function __construct($log_level=null, $log_file=null)
	{
		$this->props = array();
		$this->props['log_file'] = 5;
		$this->props['log_level'] = 5;

		// $version_lower = (version_compare(phpversion(), '5.3.6', '<') ? 'true' : 'false');

		$this->setLogLevel($log_level, $log_file);


		// Set the User Defined Error and Exception Handler //
		if(Logger::$old_error_handler === null)
			Logger::$old_error_handler = set_error_handler(array($this, '__error_handler'), (E_ALL & ~E_NOTICE));

		if(Logger::$old_exception_handler === null)
			Logger::$old_exception_handler = set_exception_handler(array($this, '__exception_handler'));
	}

	public function argLoop($obj, $depth=0)
	{
		if($depth > $this->max_recursion_depth)
			return 'Max recursion '.$this->max_recursion_depth.' already reached.';

		$result = '';
		$indent = str_repeat("        ", $depth);

		switch(gettype($obj))
		{
			case 'boolean':
				$result = $obj ? 'TRUE' : 'FALSE';
				break;
			case 'integer':
			case 'double':
			case 'float':
			case 'string':
				$result = $obj;
				break;
			case 'null':
			case 'NULL':
				$result = 'NULL';
				break;
			case 'array':
				$result = "Array";
				$propStr = '';
				foreach($obj as $k=>$v)
				{
					$propStr .= $indent.'    [';
					if(gettype($k) == 'string')
						$propStr .= "'".$k."'";
					else
						$propStr .= $k;
					$propStr .= '] => '.$this->argLoop($v, $depth+1)."\n";
				}
				
				if(!empty($propStr))
					$result .= "\n".$indent."(\n".$propStr.$indent.')';
				else
					$result .= '(empty)';
				break;
			case 'object':
				$className = get_class($obj);
				$result = "{$className} Object";
				$props = array();
				if($obj instanceof IteratorAggregate || method_exists($obj, 'getIterator'))
					$props = $obj->getIterator();
				else if($obj instanceof Traversable)
					$props = &$obj;
				else
					$props = get_object_vars($obj);

				$propStr = '';
				// $props = get_class_vars($className);
				foreach($props as $k=>$v)
					$propStr .= $indent.'    ['.$k.'] => '.$this->argLoop($v, $depth+1)."\n";
				
				if(!empty($propStr))
					$result .= "\n".$indent."(\n".$propStr.$indent.')';
				else
					$result .= '(no properties)';
				break;
			case 'resource':
				$result = get_resource_type($obj).' Resource';
				break;
			default:
				$result = 'unknown type';
		}
		return $result;
	}

	public function setLogLevel($log_level=null, $log_file=null)
	{
		if($log_level === null)
			$log_level = defined('STF_LOG_LEVEL') ? STF_LOG_LEVEL : self::LOG_LEVEL_WARN;

		if($log_file === null && defined('STF_LOG_PATH'))
		{
			$log_file = STF_LOG_PATH.DIRECTORY_SEPARATOR;
			if(defined('STF_LOG_FILE'))
				$log_file .= STF_LOG_FILE;
			else
				$log_file .= 'stf_common.log';
		}

		$this->log_level = $log_level;
		
		$this->log_file = $log_file;
	}

	public function debug()
	{
		$args = func_get_args();
		$backtrace = debug_backtrace($this->debug_object);
		// echo "<pre>".__METHOD__."(".print_r($args, true).")</pre>\n";

		$this->log($args, self::LOG_LEVEL_DEBUG, $backtrace);
	}

	public function __error_handler($errno, $errstr, $errfile, $errline, $errcontext)
	{
		// TODO //
		// Error Levels: E_ERROR, E_WARNING, E_PARSE, E_NOTICE, E_ALL, E_STRICT (not part of E_ALL), E_RECOVERABLE_ERROR (5.2.0), E_DEPRECATED and E_USER_DEPRECATED (5.3.0)
		// error_reporting(E_ERROR | E_WARNING | E_PARSE);
		// $error_reporting = error_reporting();

		$backtrace = array();
		$backtrace[] = array(
			'class'=>null,
			'type'=>null,
			'file'=>$errfile,
			'line'=>$errline,
			'function'=>null,
			'args'=>null
		);
		$msg = '['.$errno.'] '.$errstr;
		// $msg = $errstr;
		$args = array($msg);
		$this->log($args, self::LOG_LEVEL_ERROR, $backtrace);
	}

	public function __exception_handler($e)
	{
		$backtrace = array(
			'class'=>null,
			'type'=>null,
			'file'=>$e->getFile(),
			'line'=>$e->getLine(),
			'function'=>null,
			'args'=>null
		);

		$traces = $e->getTrace();
		// echo '$backtrace: '.Util::loop($backtrace)."\n";
		// echo '$traces: '.Util::loop($traces)."\n";

		foreach($traces as $k=>$trace)
			if(array_key_exists('function', $trace))
				if(in_array($trace['function'], $this->include_functions))
					if(empty($traces[$k]['args']))
						$traces[$k]['args'] = array($backtrace['file']);
					else if(!in_array($backtrace['file'], $traces[$k]['args']))
						$traces[$k]['args'][] = $backtrace['file'];

		if(!$this->debug_object && array_key_exists('object', $trace))
			unset($trace['object']);

		$backtrace = array($backtrace);
		$backtrace = array_merge($backtrace, $traces);

		// echo '$backtrace: '.Util::loop($backtrace)."\n";
		$msg = '['.$e->getCode().'] '.$e->getMessage();
		$args = array($msg);
		$this->log($args, self::LOG_LEVEL_FATAL, $backtrace);

		return false;
	}

	public function error()
	{
		$args = func_get_args();
		$backtrace = debug_backtrace($this->debug_object);
		// echo "<pre>".__METHOD__."(".print_r($args, true).")</pre>\n";

		$this->log($args, self::LOG_LEVEL_ERROR, $backtrace);
	}

	public function fatal()
	{
		$args = func_get_args();
		$backtrace = debug_backtrace($this->debug_object);
		// echo "<pre>".__METHOD__."(".print_r($args, true).")</pre>\n";

		$this->log($args, self::LOG_LEVEL_FATAL, $backtrace);
	}

	public function info()
	{
		$args = func_get_args();
		$backtrace = debug_backtrace($this->debug_object);
		// echo "<pre>".__METHOD__."(".print_r($args, true).")</pre>\n";

		$this->log($args, self::LOG_LEVEL_INFO, $backtrace);
	}

	protected function formatLogHTML($log, $level=null)
	{
		$log_msg = htmlentities($log, ENT_COMPAT|ENT_IGNORE, 'UTF-8');
		$log = '<pre class="';
		$classes = array();
		if(!empty($this->css_class))
			$classes[] = $this->css_class;
		if(!empty($level))
			$classes[] = strtolower($level);

		$log .= implode(' ', $classes);

		if(!empty($this->css_inline))
			$log .= '" style="'.$this->css_inline.'"';

		$log .= '>'."{$log_msg}</pre>\n";
		return $log;
	}

	protected function getClassMethod($backtrace, $show_args)
	{
		$logger_methods = array('debug', 'info', 'log', 'warn', 'error', 'fatal');

		// echo 'getClassMethod($backtrace): '.Util::loop($backtrace)."\n";
		$class_method = '';
		foreach($backtrace as $trace)
		{
			if(!empty($trace['class']) && !empty($trace['function']))
			{
				if($trace['class'] !== __CLASS__)
				{
					if(!in_array($trace['function'], $this->include_functions))
					{
						$class_method .= $trace['class'].'::'.$trace['function'];
						if($show_args && !empty($class_method) && !empty($trace['args']))
							$class_method .= '('.Util::args($trace['args']).')';
						else
							$class_method .= '()';
						break;
					}
				}
			}
		}
		if(!empty($class_method))
			$class_method .= ' | ';

		return $class_method;
	}
	
	protected function log($args, $level=1, $backtrace=null)
	{
		// echo '<pre>'.__METHOD__.'() $this->log_level: '.Util::loop($this->log_level)."</pre>\n";
		if($level <= $this->log_level)
		{
			if($args instanceof LogMessage)
			{
				$msg = $args->getMessage();
			}
			else if(is_array($args))
			{
				$args = array_map(array($this, 'argLoop'), $args);
				// $args = array_map(array('Util', 'loop'), $args);
				$msg = implode(' ', $args);
				// foreach($args as $arg)
				// 	$msg .= Util::loop($arg);
			}
			else
			{
				$msg = '';
				// throw new Exception('Unknown log type '.gettype($args));
			}

			// echo '<pre>'.__METHOD__.'('.$msg.')'."</pre>\n";

			if($backtrace === null)
				$backtrace = debug_backtrace($this->debug_object);

			$time = microtime(true);
			$fulltime = gmstrftime("%F %T Z", $time);// RFC-3339 Timestamp (ISO-8601 Extended Format bastardization)

			switch($level)
			{
				case self::LOG_LEVEL_DEBUG:	$level = 'DEBUG';	break;
				case self::LOG_LEVEL_INFO:	$level = 'INFO';	break;
				case self::LOG_LEVEL_WARN:	$level = 'WARN';	break;
				case self::LOG_LEVEL_ERROR:	$level = 'ERROR';	break;
				case self::LOG_LEVEL_FATAL:	$level = 'FATAL';	break;
			}

			if(!empty($backtrace[0]['line']))
				$file = $backtrace[0]['file'].':'.$backtrace[0]['line'];
			else
				$file = ' ';

			$classFunc = '';//$this->getClassMethod($backtrace, $show_args);

			// if(strlen($classFunc) > 2)//  && strpos($msg, $classFunc) === false
			// 	$msg .= $classFunc."\n";

			$log = sprintf("%-20s | %5s | %-5s | %s | %s", $fulltime, getmypid(), $level, $file, $classFunc.$msg);

			if($this->echo_output)
			{
				if($this->echo_html)
					echo $this->formatLogHTML($log, $level);
				else
				{
					header('Content-Type: text/plain');
					echo $log."\n";
				}
			}

			if($this->file_output)
			{
				$log = $this->file_html ? $this->formatLogHTML($log) : $log."\n";

				if($this->log_file !== null)
					error_log($log, 3, $this->log_file);
				else
					error_log($log, 0);
			}
		}
	}

	public function warn()
	{
		$args = func_get_args();
		$backtrace = debug_backtrace($this->debug_object);
		// echo "<pre>".__METHOD__."(".print_r($args, true).")</pre>\n";

		$this->log($args, self::LOG_LEVEL_WARN, $backtrace);
	}

	public function wrap($msg, $label=null, $level=null)
	{
		return new LogMessage($msg, $label, $level);
	}
}

class LogMessage
{
	protected $label;
	protected $level;
	protected $msg;

	public function __construct($msg, $label=null, $level=null)
	{
		$this->msg = $msg;
		$this->label = $label;
		$this->level = $level;
	}

	public function getLevel()
	{
		return !empty($this->level) ? $this->level : Logger::LOG_LEVEL_DEBUG;
	}

	public function getMessage()
	{
		$msg = !empty($this->label) ? $this->label.': ' : '';
		$msg .= Util::loop($this->msg);
		return $msg;
	}

	public function __toString()
	{
		return 'Level: '.$this->level.' - '.$this->getMessage();
	}
}

?>