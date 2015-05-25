<?php
/**
 * SimpleThingFramework URI Router class
 *
 * @author	RuneImp <runeimp@gmail.com>
 * @version	1.1.0
 */
/*
 * Change Log:
 * -----------
 * 2014-03-19	v1.1.0		Converted to use PHP namespaces.
 * 2012-09-06	v1.0.0		Initial class creation
 *
 * ToDo:
 * -----
 * [ ] ...
 */

namespace RuneImp\STF\URI;
use \RuneImp\STF\Logger;
use \RuneImp\STF\Wiki\Page;
use \RuneImp\STF\Net\URI\Route;

class WikiRouter
{
	// CLASS CONSTANTS //
	const CLASS_AUTHOR = 'RuneImp <runeimp@gmail.com>';
	const CLASS_VERSION = '1.1.0';

	// CLASS PROPERTIES //

	// CLASS VARS //
	protected $content_path;
	protected $log;
	protected $uri;
	
	public function __construct(&$uri)
	{
		$this->uri = &$uri;

		$this->routes = array();

		$this->log = new Logger(Logger::LOG_LEVEL_DEBUG);
		$this->log->css_class = 'logger';
		$this->log->css_inline = 'color:#000';
		$this->log->echo_output = true;
		$this->log->echo_html = true;

		$this->log->debug(null, false);
	}

	public function addRoute($route)
	{
		$this->log->debug(null);
		$route->path = $this->uri->normalizePath($route->path);
		$this->routes[$route->path] = $route;
	}

	public function init($path)
	{
		$this->content_path = $path;
		$this->log->debug(null);

		$paths = $this->recursePath($this->content_path);
		// $this->log->debug($paths, false);
		$path_lenth = strlen($path);
		foreach($paths as $content_path=>$is_dir)
		{
			$uri = substr($content_path, $path_lenth);
			$uri = $this->uri->slugPath($uri);
			// $this->log->debug($uri, false);

			$route = new Route($uri, $is_dir, $dynamic=false, $content_path);
			$this->routes[$uri] = $route;
		}
	}

	protected function recursePath($path, &$result=array())
	{
		foreach(scandir($path) as $k=>$v)
		{
			$file = $path.'/'.$v;
			$is_dir = is_dir($file);
			if($v[0] !== '.')
				if($is_dir)
				{
					$result[$file] = true;
					$this->recursePath($file, $result);
				}
				else
					$result[$file] = false;
		}

		return $result;
	}

	public function process($path=null)
	{
		$this->log->debug($this->uri->path_slug);

		if($path === null)
			$path = $this->uri->path_slug;

		if(!empty($this->routes[$path]))
			$route = $this->routes[$path];

		$file = $this->content_path.$this->uri->path_slug;
		$this->log->debug($file);
		// $route = 
		// 
		$this->render($file);
	}

	public function render($file)
	{
		// $this->log->debug(null);
		try{
			$wiki_page = new Page($file);
			$wiki_data = $wiki_page->load($wiki_file);

			// $this->log->debug($this->log->wrap($wiki_data['header'], "\$wiki_data['header']"));
			$this->log->debug($this->log->wrap($wiki_data, "\$wiki_data"));
		}catch(\Exception $e){
			$this->log->error('('.$e->getCode().') '.$e->getMessage());

			if($e->getCode() === Page::FILE_NOT_FOUND)
			{
				header("HTTP/1.0 404 Not Found");
				$template = 'system-message';
				$template_data['title'] = 'Resource Not Found';
				$template_data['content'] = "<p>I'm sorry but {$this->uri->path} was not found on our system.</p>";
				$template_data['page_id'] = 'resource_not_found';
			}
			else if($e->getCode() === Page::FILE_NOT_READABLE)
			{
				header("HTTP/1.0 403 Forbidden");
				$template = 'system-message';
				$template_data['title'] = 'Access Forbidden';
				$template_data['content'] = "<p>I'm sorry but you do not have access to {$this->uri->path}.</p>";
				$template_data['page_id'] = 'access_forbidden';
			}
		}

		// $highlighter = new Circ_SyntaxHighlighter;
		// $content = $highlighter->parse($content);
		
		$views_path = $upOne.'/template/views';
		$partials_path = $upOne.'/template/partials';


		// $logger->info($logger->wrap($partials, '$partials'));
		// $partials = Util::loadList($partials, $partials_path);
		// $partials['page_header'] = 'home-header';
		// $logger->info($logger->wrap($partials, '$partials'));

		$mustache_options = array(
			'template_class_prefix' => '__Mustache_Dev_',
			// 'cache' => STF_CACHE,
			'loader' => new Mustache_Loader_FilesystemLoader($views_path),
			'partials_loader' => new Mustache_Loader_MutableFilesystemLoader($partials_path),
			'partials' => $partials,
			// 'helpers'=>array(
			// 	'page_header'=>function($text){
			// 		global $logger;
			// 		$logger->info($logger->wrap($text, '$text'));
			// 		// echo "<pre>\$text: {$text}</pre>\n";
			// 		return strtolower($text);
			// 	},
			// 	'page_data'=>array('header'=>'HEADER')
			// )
		);
		// $logger->info($logger->wrap($mustache_options, '$mustache_options'));

		$mustache = new Mustache_Engine($mustache_options);

		echo $mustache->render($template, $template_data);
	}
}



?>