<?php
/**
 * SimpleThingFramework MVC Mustache View Class
 *
 * @author	RuneImp <runeimp@gmail.com>
 * @version	1.0.0
 */
/*
 * Change Log:
 * -----------
 * 2012-09-07	v1.0.0	Initial class creation
 *
 * ToDo:
 * -----
 * [ ] ...
 */
namespace RuneImp\STF\MVC;
use RuneImp\STF;
use RuneImp\STF\Exception;
use RuneImp\STF\Logger;
use RuneImp\STF\Util\FileTools;

class MustacheView implements iView
{
	// CLASS CONSTANTS //
	const CLASS_AUTHOR = 'RuneImp <runeimp@gmail.com>';
	const CLASS_VERSION = '1.0.0';

	// CLASS PROPERTIES //

	// CLASS VARS //
	protected $controller;
	protected $initialized = false;
	protected $log;
	protected $mustache_options;
	protected $partials_path;
	protected $site_conf;
	protected $stf;
	protected $uri;
	protected $views_path;
	
	/**
	 * Class constructor method
	 * 
	 * @param  string $views_path       Full base path to the views
	 * @param  string $partials_path    Optional full base path to the partials
	 * @param  array  $mustache_options Optional named array of options to pass to Mustache.
	 */
	public function __construct(&$controller=null)
	{
		$this->props['initialized'] = 5;// Read & isset

		$this->log = new Logger(Logger::LOG_LEVEL_WARN);
		$this->log->css_class = 'logger';
		$this->log->css_inline = 'color:#000';
		// $this->log->echo_output = true;
		// $this->log->echo_html = true;
		$this->log->file_output = true;

		$this->log->info();

		$this->stf =& STF::getInstance();
	}

	/**
	 * Internal class initialization method
	 * 
	 * @param  string $views_path       Full base path to the views
	 * @param  string $partials_path    Optional full base path to the partials
	 * @param  array  $mustache_options Optional named array of options to pass to Mustache.
	 * @return null
	 */
	public function init(&$controller=null)
	{
		if( $controller !== null )
			$this->controller =& $controller;

		$config = $this->stf->getConfig();
		$this->cache_path = $config['app']['cache'];
		$this->views_path = $config['app']['views'];
		$this->templates_path = $config['mustache']['templates'];
		$this->partials_path = $config['mustache']['partials'];
		$this->mustache_options = $config['mustache']['options'];

		$this->site_conf = $config['site'];
		// echo '<pre>'.__METHOD__.' $this->site_conf: '.print_r($this->site_conf, true)."</pre>\n";

		// echo '<pre>'.__METHOD__.' $this->cache_path: '.print_r($this->cache_path, true)."</pre>\n";
		// echo '<pre>'.__METHOD__.' $this->views_path: '.print_r($this->views_path, true)."</pre>\n";
		// echo '<pre>'.__METHOD__.' $this->templates_path: '.print_r($this->templates_path, true)."</pre>\n";
		// echo '<pre>'.__METHOD__.' $this->partials_path: '.print_r($this->partials_path, true)."</pre>\n";
		// echo '<pre>'.__METHOD__.' $this->mustache_options: '.print_r($this->mustache_options, true)."</pre>\n";


		\Mustache_Autoloader::register($config['php']['vendor']);

		$this->log->debug('$this->cache_path:', $this->cache_path);
		$this->log->debug('$this->views_path:', $this->views_path);
		$this->log->debug('$this->templates_path:', $this->templates_path);
		$this->log->debug('$this->partials_path:', $this->partials_path);
		$this->log->debug('$this->mustache_options:', $this->mustache_options);

		if( empty($this->mustache_options) )
		{
			// $this->mustache_options = array(
			// 	'loader' => new Mustache_Loader_FilesystemLoader($this->views_path),
			// 	'partials_loader' => new Mustache_Loader_MutableFilesystemLoader($this->partials_path),
			// 	'partials' => !empty($mustache_options['template_partials']) ? $mustache_options['template_partials'] : array()
			// );

			$this->mustache_options = array();
			$this->mustache_options['cache'] = $this->cache_path;
			// $this->mustache_options['loader'] = new \Mustache_Loader_FilesystemLoader($this->templates_path);
			$this->mustache_options['logger'] = new \Mustache_Logger_StreamLogger('php://stderr');
			if( !empty($partials) )
				$this->mustache_options['partials'] = $partials;

			$this->mustache_options['partials_loader'] = new \Mustache_Loader_FilesystemLoader($this->partials_path);
		}
		
		$this->log->debug('$this->mustache_options:', $this->mustache_options);
	}

	/**
	 * View method to render the output.
	 * 
	 * @param  array $settings	Named array with indexes for id, data, partials.
	 * @return null
	 */
	public function render($settings=null)
	{
		// echo '<pre>'.__METHOD__.' $settings: '.print_r($settings, true)."</pre>\n";
		if( empty($settings['mustache']['id']) )
			throw new MustacheViewException('Missing template id.');

		if( !isset($settings['data']) )
			$settings['data'] = array();

		if( !is_array($settings['data']) )
			throw new MustacheViewException("\$settings['data'] expected to be a named array.");



		// $mustache_options = array();
		// $mustache_options['cache'] = $this->cache_path;
		// $mustache_options['loader'] = new \Mustache_Loader_FilesystemLoader($this->templates_path);
		// $mustache_options['logger'] = new \Mustache_Logger_StreamLogger('php://stderr');
		// if( !empty($partials) )
		// 	$mustache_options['partials'] = $partials;

		// $mustache_options['partials_loader'] = new \Mustache_Loader_FilesystemLoader($this->partials_path);

		if( !empty($this->site_conf) )
			$settings['data']['site'] = $this->site_conf;



		if( !isset($settings['mustache']['partials']) )
		{
			if( !isset($settings['mustache']) )
				$settings['mustache'] = array();

			$settings['mustache']['partials'] = array();
		}

		$this->log->info("\$settings['id']:", $settings['mustache']['id']);
		$this->log->info("\$settings['data']:", $settings['data']);
		$this->log->info("\$settings['partials']:", $settings['mustache']['partials']);
		$this->log->info('$this->mustache_options:', $this->mustache_options);

		// echo '<pre>'.__METHOD__.' $this->mustache_options: '.print_r($this->mustache_options, true)."</pre>\n";

		$mustache = new \Mustache_Engine($this->mustache_options);

		$file = $this->templates_path.'/'.$settings['mustache']['id'].'.mustache';
		// echo '<pre>'.__METHOD__.' $file: '.print_r($file, true)."</pre>\n";
		$this->log->debug('$file:', $file);
		$this->log->debug('file_exists($file):', file_exists($file));
		$this->log->debug('is_readable($file):', is_readable($file));

		$fileTools = new FileTools;

		$template = $fileTools->read($file);
		// echo '<pre>'.__METHOD__.' $template: '.print_r($template, true)."</pre>\n";
		echo $mustache->render($template, $settings['data'], $settings['mustache']['partials']);
	}

	/**
	 * Update method to change the view.
	 *
	 * @param	$data	Data to update the view with.
	 * @return	void
	 */
	public function update($data)
	{
		// echo '<pre>'.__METHOD__.' $data: '.print_r($data, true)."</pre>\n";
		try{
			$this->init();
			$this->render($data);
		}catch( MustacheViewException $e ){
			echo '<pre>'.__METHOD__.' $e->getMessage(): '.print_r($e->getMessage(), true)."</pre>\n";
		}
		
	}
}

/**
 * STF MustacheView Exception
 */
class MustacheViewException extends Exception
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
