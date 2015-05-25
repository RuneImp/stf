<?php
/**
 * SimpleThingFramework MVC GeSHi Control Class
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
class STF_MVC_GeSHiControl
{
	// CLASS CONSTANTS //
	const CLASS_AUTHOR = 'RuneImp <runeimp@gmail.com>';
	const CLASS_VERSION = '1.0.0';

	// CLASS PROPERTIES //

	// CLASS VARS //
	protected $model;
	protected $log;
	protected $uri;
	protected $view;
	
	public function __construct($uri, $model, $view)
	{
		$this->uri = &$uri;
		$this->model = &$model;
		$this->view = &$view;

		$this->log = new STF_Logger(STF_Logger::LOG_LEVEL_INFO);
		$this->log->css_class = 'logger';
		$this->log->css_inline = 'color:#000';
		// $this->log->echo_output = true;
		// $this->log->echo_html = true;
		$this->log->file_output = true;

		$this->log->info('['.__FUNCTION__.']');
	}

	public function render($template_data, $mustache_data=null)
	{
		$this->log->debug(null);
		try{
			$data = $this->model->getData();
			$template_id = $data['template_id'];
			
			if(!empty($data['template_data']))
				$template_data = array_merge($template_data, $data['template_data']);

			$template_partials = $data['template_partials'];

			$this->log->debug('$data:', $data);
		}catch(Exception $e){
			$this->log->error('('.$e->getCode().') '.$e->getMessage());

			if($e->getCode() === STF_Wiki_Page::FILE_NOT_FOUND)
			{
				header("HTTP/1.0 404 Not Found");
				$template_id = 'system-message';
				$template_data['title'] = 'Resource Not Found';
				$template_data['content'] = "<p>I'm sorry but {$this->uri->path} was not found on our system.</p>";
				$template_data['page_id'] = 'resource_not_found';
			}
			else if($e->getCode() === STF_Wiki_Page::FILE_NOT_READABLE)
			{
				header("HTTP/1.0 403 Forbidden");
				$template_id = 'system-message';
				$template_data['title'] = 'Access Forbidden';
				$template_data['content'] = "<p>I'm sorry but you do not have access to {$this->uri->path}.</p>";
				$template_data['page_id'] = 'access_forbidden';
			}
		}

		if(!empty($template_data['content']))
		{
			$highlighter = new STF_GeSHi;
			$template_data['content'] = $highlighter->parse($template_data['content']);
		}

		if(empty($mustache_data['mustache_options']['partials']))		
			$mustache_data['mustache_options']['partials'] = $template_partials;
		else
			foreach($template_partials as $k=>$v)
				$mustache_data['mustache_options']['partials'][$k] = $v;

		$this->log->debug('$mustache_data:', $mustache_data);

		$this->view->init($mustache_data['views_path'], $mustache_data['partials_path'], $mustache_data['mustache_options']);
		$this->view->render($template_id, $template_data, $template_partials);
	}
}



?>