<?php
/**
 * SimpleThingFramework Blogging class
 *
 * @author	RuneImp <runeimp@gmail.com>
 * @version	1.0.0
 */
/*
 * Change Log:
 * -----------
 * 2012-09-09	v1.0.0	Initial class creation
 *
 * ToDo:
 * -----
 * [ ] ...
 */
class STF_WikiBlog extends STF_Base
{
	// CLASS CONSTANTS //
	const CLASS_AUTHOR = 'RuneImp <runeimp@gmail.com>';
	const CLASS_VERSION = '1.0.0';

	// CLASS PROPERTIES //
	public $cache_time = 0;// In seconds. 60 seconds = 1 minute

	// CLASS VARS //
	protected $article_last_modified = 0;
	protected $articles;
	protected $blog_path;
	protected $cache_file;
	protected $initialized = false;
	protected $limit;
	protected $log;

	public function __construct($blog_path=null, $limit=5)
	{
		parent::__construct();

		$this->props['articles'] = 5;
		$this->props['blog_path'] = 5;
		$this->props['limit'] = 5;
		$this->props['log'] = 5;

		$this->log = new STF_Logger(STF_Logger::LOG_LEVEL_WARN);
		// $this->log->echo_output = true;
		$this->log->file_output = true;

		$this->limit = $limit;

		if(is_string($blog_path))
			$this->init($blog_path);
	}

	protected function cacheCheck()
	{
		$this->log->debug('['.__METHOD__.']', '$this->blog_path:', $this->blog_path);
		// $this->log->debug('['.__METHOD__.']', 'defined("STF_CACHE"):', (defined('STF_CACHE') ? 'true' : 'false'));
		$result = null;

		if(defined('STF_CACHE') && $this->cache_time > 0)
		{
			// $this->log->debug('['.__METHOD__.']', 'defined("STF_CACHE"): true', ':: STF_CACHE:', STF_CACHE);
			// $this->log->debug('['.__METHOD__.']', 'is_writable(STF_CACHE):', (is_writable(STF_CACHE) ? 'true' : 'false'));
			$cache_file = $this->cacheName();
			// $this->log->debug('['.__METHOD__.']', '$cache_file:', $cache_file);

			$cachedir_is_writable = is_writable(STF_CACHE);
			$this->log->debug('['.__METHOD__.']', '$cachedir_is_writable:', $cachedir_is_writable);

			if($cachedir_is_writable)
			{
				if(!file_exists($cache_file))
				{
					// $this->log->debug('['.__METHOD__.']', '$cache_file does not exist');
					if(touch($cache_file))
						$result = false;
				}
				else
				{
					// $this->log->debug('['.__METHOD__.']', '$cache_file exists');
					$cache_size = filesize($cache_file);
					$cache_mtime = filemtime($cache_file);
					$cache_stale_time = $cache_mtime + $this->cache_time;

					$cache_is_stale = $cache_stale_time < time() ? true : false;
					$content_newer = $this->article_last_modified > $cache_mtime ? true : false;

					// $this->log->debug('['.__METHOD__.']', '$cache_size:', number_format($cache_size), 'bytes');
					// $this->log->debug('['.__METHOD__.']', '$cache_mtime:', number_format($cache_mtime), 'sec');
					// $this->log->debug('['.__METHOD__.']', '$cache_stale_time:', number_format($cache_stale_time), 'sec');
					$this->log->debug('['.__METHOD__.']', '$cache_is_stale:', $cache_is_stale);
					$this->log->debug('['.__METHOD__.']', '$content_newer:', $content_newer);

					if($cache_size > 100 && !$cache_is_stale && !$content_newer)
						$result = true;
					else
					{
						if($cache_is_stale)
							touch($cache_file);

						$result = false;
					}
				}
			}// end if(is_writable(STF_CACHE))
		}// end if(defined('STF_CACHE'))

		$this->log->debug('['.__METHOD__.']', '$result:', $result);

		return $result;
	}

	protected function cacheName()
	{
		// $this->log->debug('['.__METHOD__.']');
		if(defined('STF_CACHE'))
		{
			if(empty($this->cache_file))
				$this->cache_file = STF_CACHE.'/'.__CLASS__.'-'.md5($this->blog_path).'.serialized';
		}
		else
			$this->cache_file = false;

		$this->log->debug('['.__METHOD__.']', '$this->cache_file:', $this->cache_file);

		return $this->cache_file;
	}

	protected function cacheGet()
	{
		$this->log->debug('['.__METHOD__.']');
		$cache_file = $this->cacheName();
		$cache_size = file_exists($cache_file) ? number_format(@filesize($cache_file)) : -1;

		// $this->log->debug('['.__METHOD__.']', '$cache_file:', $cache_file, ':: $cache_size:', $cache_size.' bytes');

		if(is_readable($cache_file))
			$this->articles = unserialize(file_get_contents($cache_file));
	}

	protected function cacheSet()
	{
		$this->log->debug('['.__METHOD__.']');
		$cache_file = $this->cacheName();
		
		if(is_writable($cache_file) && $this->cache_time > 0)
		{
			file_put_contents($cache_file, serialize($this->articles));
			$cache_size = file_exists($cache_file) ? number_format(@filesize($cache_file)) : -1;
		}

		// $this->log->debug('['.__METHOD__.']', '$cache_file:', $cache_file, ':: $cache_size:', $cache_size.' bytes');
	}

	public function getDataForURI($uri)
	{
		$this->log->warn('['.__METHOD__.']', '$uri:', $uri);
		if($this->initialized)
			foreach($this->articles as $article)
			{
				$this->log->warn('['.__METHOD__.']', "\$article['uri']:", $article['uri']);
				if($article['uri'] == $uri)
					return $article;
			}
		else
			throw new STF_WikiBlogException(__CLASS__.' has not been initialized yet.', 1);
	}

	public function init($blog_path=null)
	{
		$start_time = microtime(true) * 1000;
		// $this->log->debug('['.__METHOD__.']', '$blog_path:', $blog_path);
		// $this->log->debug('['.__METHOD__.']', 'Start:', number_format(memory_get_usage()), 'bytes');

		$this->blog_path = $blog_path;
		$this->articles = array();
		$this->recursePath($this->blog_path, $this->articles);
		// $this->log->debug('['.__METHOD__.']', '$this->articles:', $this->articles);
		if($this->cacheCheck() === true)
		{
			// $this->log->debug('['.__METHOD__.']', '$this->articles:', $this->articles);
			$this->cacheGet();
		}
		else
		{
			$this->parseHeaders();
			// $this->log->debug('['.__METHOD__.']', '$this->articles:', $this->articles);
			$this->sortArticles();
			// $this->log->debug('['.__METHOD__.']', '$this->articles:', $this->articles);
			$this->linkArticles();
			// $this->log->debug('['.__METHOD__.']', '$this->articles:', $this->articles);
			$this->cacheSet();
		}

		$count = count($this->articles);

		if($this->limit > 0 && $count > $this->limit)
		{
			while($count > $this->limit)
			{
				array_pop($this->articles);
				$count = count($this->articles);
			}
		}
		$finish_time = microtime(true) * 1000;
		// $this->log->info('['.__METHOD__.']', '$this->articles:', $this->articles);
		// $this->log->debug('['.__METHOD__.']', 'Peak:', number_format(memory_get_peak_usage()), 'bytes');
		// $this->log->debug('['.__METHOD__.']', 'Finish:', number_format(memory_get_usage()), 'bytes');
		// $this->log->info('['.__METHOD__.']', '$start_time:', number_format($start_time, 4), 'ms');
		// $this->log->info('['.__METHOD__.']', '$finish_time:', number_format($finish_time, 4), 'ms');
		// $this->log->debug('['.__METHOD__.']', '$proc_time:', number_format($finish_time - $start_time, 2), 'ms');
		
		$this->initialized = true;
	}

	/**
	 * Link articles with prev and next data.
	 * 
	 * @return void
	 */
	protected function linkArticles()
	{
		$this->log->info('['.__METHOD__.']');
		$count = count($this->articles);

		for($i = 0; $i < $count; $i++)
		{
			if($i > 0)
			{
				$k = $i - 1;
				$this->articles[$i]['prev'] = array();
				$this->articles[$i]['prev']['page_title'] = $this->articles[$k]['page_title'];
				$this->articles[$i]['prev']['uri'] = $this->articles[$k]['uri'];
			}
			$k = $i + 1;
			if($k < $count)
			{
				$this->articles[$i]['next'] = array();
				$this->articles[$i]['next']['page_title'] = $this->articles[$k]['page_title'];
				$this->articles[$i]['next']['uri'] = $this->articles[$k]['uri'];
			}
		}
	}

	/**
	 * Load and parse headers from a WikiDoc for each article path.
	 * 
	 * @return void
	 */
	protected function parseHeaders()
	{
		$time = time() - 1;

		// Loop over each article //
		foreach($this->articles as $path=>$data)
		{
			// Parse The Meta-Data Out of the File Headers //

			// $this->log->warn('['.__METHOD__.']', '$_SERVER['HTTP_ACCEPT_LANGUAGE']:', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
			// $this->log->warn('['.__METHOD__.']', '$http_util->checkClientLanguage():', $http_util->checkClientLanguage());

			$http_util = new STF_Net_HTTP_Util(STF_Net_HTTP_Util::KEY_CASE_LOWER, STF_Net_HTTP_Util::KEY_SEPARATOR_UNDERSCORE);

			// $this->log->debug('['.__METHOD__.']', '$path:', $path);
			// $this->log->debug('['.__METHOD__.']', '$http_util->loadHeaders($path):', "\n".$http_util->loadHeaders($path));
			// $this->log->debug('['.__METHOD__.']', '$http_util->parseHeaders():', $http_util->parseHeaders());
			
			$http_util->loadHeaders($path);
			$http_util->collapse_header_values = true;
			$meta_data = $http_util->parseHeaders();
			// $this->log->warn('['.__METHOD__.']', '$meta_data:', $meta_data);

			if(!empty($meta_data['publish_date']))
			{
				$timestamp = strtotime($meta_data['publish_date']);
				// @see http://us.php.net/manual/en/class.datetime.php#datetime.constants.types
				$meta_data['publish_date'] = date('Y-m-d', $timestamp);
				$meta_data['publish_date_unix'] = $timestamp;// UNIX timestamp
				$meta_data['publish_date_timestamp'] = date('Y-m-d H:i:m', $timestamp);// DB type timestamp
				$meta_data['publish_date_timestampT'] = date('Y-m-d H:i:m T', $timestamp);// timestamp with timezone
				$meta_data['publish_date_timestampZ'] = gmdate('Y-m-d H:i:m \Z', $timestamp);// timestamp with Zulu timezone
				$meta_data['publish_date_iso8601'] = date(DATE_ISO8601, $timestamp);
				$meta_data['publish_date_iso8601Z'] = gmdate(DATE_ISO8601, $timestamp);
				$meta_data['publish_date_datetime'] = &$meta_data['publish_date_iso8601Z'];
				$meta_data['publish_date_rfc2822'] = date(DATE_RFC2822, $timestamp);
			}

			// Add File Meta-Data //
			$meta_data['file_creation'] = filectime($path);
			$meta_data['file_modified'] = filemtime($path);
			$len = strlen($this->blog_path);
			$meta_data['uri'] = '/blog'.rtrim(substr($path, $len), '.wiki');

			// If the Publish-Date is Invalid Remove It's Reference //
			// Invalid Equals Empty, Missing or In The Future //
			if(empty($meta_data['publish_date_unix']) || $meta_data['publish_date_unix'] > $time)
				unset($this->articles[$path]);
			else
				$this->articles[$path] = $meta_data;
		}
	}

	/**
	 * Build an array of directories and files by recursively traversing
	 * from a supplied base path.
	 * 
	 * @param  string $uri_path   The path to build from.
	 * @param  array  $result     The array to build.
	 * @return array              An array of path keys with a boolean value true if it's a directory or a Unix timestamp if it's a file.
	 */
	protected function recursePath($uri_path, &$result=array(), $count=0)
	{
		$count++;

		$this->log->debug('['.__METHOD__.']', '$result:', $result);
		// $this->log->debug('['.__METHOD__.']', '('.$count.') Start:', number_format(memory_get_usage(), 0, '.', ','), 'bytes');
		foreach(scandir($uri_path) as $k=>$v)
		{
			$file = $uri_path.DIRECTORY_SEPARATOR.$v;
			$is_dir = is_dir($file);
			if($v[0] !== '.')
				if($is_dir)
					$this->recursePath($file, $result, $count);
				else
				{
					$result[$file] = filemtime($file);
					if($result[$file] > $this->article_last_modified)
						$this->article_last_modified = $result[$file];
				}
		}
		// $this->log->debug('['.__METHOD__.']', '('.$count.') Peak:', number_format(memory_get_peak_usage(), 0, '.', ','), 'bytes');
		// $this->log->debug('['.__METHOD__.']', '('.$count.') Finish:', number_format(memory_get_usage(), 0, '.', ','), 'bytes');
	}

	protected function sortArticles()
	{
		usort($this->articles, array($this, 'sortArticlesByPublishDate'));
	}

	public function sortArticlesByPublishDate($a, $b)
	{
		if($a['publish_date'] == $b['publish_date'])
			return 0;
		else
			return ($a['publish_date'] > $b['publish_date']) ? -1 : 1;// > Descending order; < Ascending order
	}
}

class STF_WikiBlogException extends Exception{}
?>