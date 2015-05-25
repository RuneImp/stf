<?php
/**
 * SimpleThingFramework MVC Wiki Model Class
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
class STF_MVC_WikiModel
{
	// CLASS CONSTANTS //
	const CLASS_AUTHOR = 'RuneImp <runeimp@gmail.com>';
	const CLASS_VERSION = '1.0.0';
	const TEMPLATE_DEFAULT = 'index';

	// CLASS PROPERTIES //

	// CLASS VARS //
	protected $wiki_path;
	protected $log;
	protected $routes;
	protected $uri;
	
	/**
	 * An MVC model to parse wiki content.
	 * 
	 * @param STF_Net_URI $uri An instance of the STF_Net_URI class.
	 */
	public function __construct(&$uri)
	{
		$this->uri = &$uri;

		$this->routes = array();

		$this->log = new STF_Logger(STF_Logger::LOG_LEVEL_WARN);
		$this->log->css_class = 'logger';
		$this->log->css_inline = 'color:#000';
		// $this->log->echo_output = true;
		// $this->log->echo_html = true;
		$this->log->file_output = true;

		$this->log->info('null:', null);
	}

	public function addRoute($route)
	{
		// $this->log->debug('$route->getIterator():', $route->getIterator());
		$uri = $this->uri->slugPath($route->route);
		$this->routes[$uri] = $route;
	}

	/**
	 * Get the data for the supplied path or the URI path slug.
	 * 
	 * @param  string $uri_path The path to retrieve data for.
	 * @return array        A template_data array.
	 */
	public function getData($uri_path=null)
	{
		global $stf_config;

		$file_code = 0;

		$this->log->info('$this->uri->path_slug:', $this->uri->path_slug);

		if($uri_path === null)
			$uri_path = $this->uri->path_slug;

		if($uri_path === '/')
			$uri_path .= self::TEMPLATE_DEFAULT;

		$this->log->info('$uri_path:', $uri_path);
		if(!empty($this->routes[$uri_path]))
		{
			$route = $this->routes[$uri_path];
			$this->log->info('$route:', $route);
		}
		else
		{
			$path_list = explode('/', $uri_path);
			$path_count = count($path_list);

			$this->log->debug('$path_list:', $path_list);
			$this->log->debug('$path_count:', $path_count);

			while($path_count > 1)
			{
				array_pop($path_list);
				$path_count = count($path_list);
				$dynamic_path = implode('/', $path_list);
				$this->log->debug('$dynamic_path:', $dynamic_path);
				if(!empty($this->routes[$dynamic_path]) && $this->routes[$dynamic_path]->type == STF_Net_URI_Route::ROUTE_TYPE_DYNAMIC_MATCH)
				{
					$route = $this->routes[$dynamic_path];
					$this->log->debug('$route:', $route);
					break;
				}
			}

			if($route->type != STF_Net_URI_Route::ROUTE_TYPE_DYNAMIC_MATCH)
				$route->type = 'unknown';
		}
		
		$this->log->debug('$route->route:', $route->route);
		$this->log->debug('$route->type:', $route->type);
		$this->log->debug('$route->path:', $route->path);

		if($route->type == STF_Net_URI_Route::ROUTE_TYPE_DYNAMIC_MATCH)
		{
			$this->log->debug('$route->path:', $route->path);
			$this->log->debug('file_exists($route->path):', file_exists($route->path));
			$this->log->debug('is_readable($route->path):', is_readable($route->path));
			include $route->path;
		}
		else if($route->type == STF_Net_URI_Route::ROUTE_TYPE_DYNAMIC_RUN || $route->type == STF_Net_URI_Route::ROUTE_TYPE_DYNAMIC_EXIT)
		{
			$this->log->debug('$route->path:', $route->path);
			$this->log->debug('file_exists($route->path):', file_exists($route->path));
			$this->log->debug('is_readable($route->path):', is_readable($route->path));
			include $route->path;
			if($route->type == STF_Net_URI_Route::ROUTE_TYPE_DYNAMIC_EXIT)
				exit;
		}
		else if($route->type == STF_Net_URI_Route::ROUTE_TYPE_NORMAL)
		{
			try{
				$wiki_page = new STF_Wiki_Page($this->wiki_path);
				$wiki_data = $wiki_page->load($uri_path);
				$template_data = $wiki_data['template_data'];
				$template_partials = $wiki_data['template_partials'];
				$template_data['content'] = $wiki_page->render($wiki_data['content']);
				$this->log->debug('$template_data:', $template_data);

				// $this->log->debug('$wiki_data['header']:', $wiki_data['header']);
				// $this->log->debug('$wiki_data:', $wiki_data);
			}catch(STF_Wiki_PageException $e){
				$this->log->error('['.$e->getCode().'] '.$e->getMessage());

				if($e->getCode() === STF_Wiki_Page::FILE_NOT_FOUND)
					$file_code = STF_Wiki_Page::FILE_NOT_FOUND;
				else if($e->getCode() === STF_Wiki_Page::FILE_NOT_READABLE)
					$file_code = STF_Wiki_Page::FILE_NOT_READABLE;
			}

			try{
				$this->log->warn('['.__FUNCTION__.']', 'Checking STF_WikiBlog for blog data.');
				$blog = new STF_WikiBlog();
				$blog->cache_time = $stf_config['blog']['cache']['time'];
				$blog->init($stf_config['blog']['path']);
				
				$article_data = $blog->getDataForURI($this->uri->path_slug);
				$this->log->warn('['.__FUNCTION__.']', '$article_data:', $article_data);
				foreach($article_data as $k=>$v)
					$template_data[$k] = $v;

			}catch(STF_WikiBlogException $e){
				$this->log->error('['.$e->getCode().'] '.$e->getMessage());
			}
		}
		else if($route->type == STF_Net_URI_Route::ROUTE_TYPE_REDIRECT)
		{
			// Do something brilliant. Like redirect or something.
			$this->log->warn('$route:', $route);
		}
		else
		{
			$this->log->debug('$route:', $route);
			$section = !empty($this->uri->data['path']['list'][0]) ? $this->uri->data['path']['list'][0] : null;
			$category = !empty($this->uri->data['path']['list'][1]) ? $this->uri->data['path']['list'][1] : null;
			$download_file = false;
			$upOne = dirname($this->wiki_path);

			// Setup Virtual File System //
			$vfs = array();
			$vfs['docs'] = $upOne.'/api-docs';
			$vfs['download'] = $upOne.'/api-docs';

			if(array_key_exists($section, $vfs))
			{
				$this->log->debug('$section:', $section);
				$path = $vfs[$section];
				if($section == 'docs' || $section == 'download')
				{
					$list = $this->uri->data['path']['list'];
					array_shift($list);
					$file = '/'.implode('/', $list);
				}
				else
					$file = $this->uri->path;

				$download_file = !empty($section) && $section == 'download' ? true : false;
				$file_path = $path.$file;
				if($category == 'html' && empty($this->uri->data['path']['list'][2]))
				{
					// exit($this->uri->uri);
					if(substr($this->uri->path, -1) !== '/')
					{
						header('Location: '.$this->uri->uri.'/');
						exit;
					}
					$file_path .= '/index.html';
				}

				$file_result = $this->loadFile($file_path);
				if($file_result === null)
					$file_code = STF_Wiki_Page::FILE_NOT_FOUND;
				else if($file_result === false)
					$file_code = STF_Wiki_Page::FILE_NOT_READABLE;

				$this->log->debug('$file_path:', $file_path);
			}
			else
				$file_code = STF_Wiki_Page::FILE_NOT_FOUND;

		}
		$this->log->debug('$file_code:', $file_code, ':: STF_Wiki_Page::FILE_NOT_FOUND:', STF_Wiki_Page::FILE_NOT_FOUND, '::', ($file_code === STF_Wiki_Page::FILE_NOT_FOUND));

		if($file_code === STF_Wiki_Page::FILE_NOT_FOUND)
		{
			header("HTTP/1.0 404 Not Found");
			$template_id = 'index';
			$template_data['page_title'] = 'Page Not Found';
			$template_data['content'] = "<h1>Page Not Found</h1>\n";
			// $template_data['content'] .= "<p>I'm sorry but {$this->uri->path} was not found on our system.</p>\n";
			$template_data['content'] .= '<p>Sorry, this page doesn\'t seem to exist. Would you like to <a href="/">go to the home page</a>?</p>'."\n";
			$template_data['page_id'] = 'resource_not_found';
		}
		else if($file_code === STF_Wiki_Page::FILE_NOT_READABLE)
		{
			header("HTTP/1.0 403 Forbidden");
			$template_id = 'index';
			$template_data['page_title'] = 'Access Forbidden';
			$template_data['content'] = "<p>I'm sorry but you do not have access to {$this->uri->path}.</p>";
			$template_data['page_id'] = 'access_forbidden';
		}

		// Process Route Template Data //
		if(is_null($template_data))
			$template_data = array();

		if(method_exists($route, 'getMetaData'))
			$template_data = $route->getMetaData($template_data);

		// Set Missing Page IDs Dynamically //
		if(empty($template_data['page_id']))
			$template_data['page_id'] = str_replace('-', '_', $this->uri->slug);

		$this->log->debug('$template_id:', $template_id);
		$this->log->debug('$template_data:', $template_data);
		$this->log->debug('$template_partials:', $template_partials);

		if(empty($template_id))
			$template_id = self::TEMPLATE_DEFAULT;

		$result = array();
		$result['route'] = $route;
		$result['template_id'] = $template_id;
		$result['template_data'] = $template_data;
		$result['template_partials'] = $template_partials;

		return $result;
	}

	/**
	 * Class initialization method.
	 * 
	 * @param  string $uri_path The path to find wiki content.
	 * @return null
	 */
	public function init($uri_path)
	{
		$this->wiki_path = $uri_path;
		$this->log->debug();

		// Build Routes Dynamically From Wiki Content //
		$uri_paths = $this->recursePath($this->wiki_path);
		$this->log->debug('$uri_paths:', $uri_paths);
		// exit;
		$uri_path_lenth = strlen($uri_path);
		foreach($uri_paths as $wiki_path=>$is_dir)
		{
			// $this->log->warn(array('wiki_path'=>$wiki_path, 'is_dir'=>$is_dir));
			$uri = substr($wiki_path, $uri_path_lenth);
			$uri = $this->uri->slugPath($uri);
			// $this->log->debug($uri, false);
			$type = STF_Net_URI_Route::ROUTE_TYPE_NORMAL;

			$route = new STF_Net_URI_Route($uri, $is_dir, $type, $wiki_path);
			$this->routes[$uri] = $route;
		}
	}

	protected function loadFile($file_path)
	{
		$this->log->debug('$file_path:', $file_path);
		if(file_exists($file_path))
		{
			if(is_readable($file_path))
			{
				$file_extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

				switch($file_extension)
				{
					case 'css':	$mime = 'text/css; charset=utf-8';	break;
					case 'js':	$mime = 'application/x-javascript; charset=utf-8';	break;
					default:
						$fmime = new finfo(FILEINFO_MIME);
						$mime = $fmime->file($file_path);
						break;
				}
				$this->log->debug('$mime:', $mime);
				
				$length = filesize($file_path);
				$this->log->debug('$length:', $length);


				if($download_file)
				{
					header('Content-Description: File Transfer');
					header('Content-Type: application/octet-stream');
					header('Content-Disposition: attachment; filename='.basename($file_path));
					header('Content-Transfer-Encoding: binary');
					header('Expires: 0');
					header('Cache-Control: must-revalidate');
					header('Pragma: public');
					header('Content-Length: '.$length);
					ob_clean();
					flush();
				}
				else
				{
					header('Content-Type: '.$mime);
					header('Content-Length: '.$length);
				}
				
				readfile($file_path);
				exit;
			}// end if(is_readable($file_path))
			else
			{
				return false;// File exists but is not readable
			}
		}// end if(file_exists($file_path))
		else
		{
			return null;// File does not exist
		}
	}

	/**
	 * Build an array of directories and files by recursively traversing
	 * from a supplied base path.
	 * 
	 * @param  string $uri_path   The path to build from.
	 * @param  array  $result The array to build
	 * @return array          An array of path keys with a boolean value true if it's a directory.
	 */
	protected function recursePath($uri_path, &$result=array())
	{
		foreach(scandir($uri_path) as $k=>$v)
		{
			$file = $uri_path.'/'.$v;
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
}

?>