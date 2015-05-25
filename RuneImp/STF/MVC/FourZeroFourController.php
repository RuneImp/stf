<?php
/**
 * STF Default Congroller
 *
 * @author RuneImp <runeimp@gmail.com>
 * @version 0.1.0
 */
/*
 * ChangeLog:
 * ----------
 * 2014-04-19	v0.1.0	Initial class creation
 *
 * ToDo:
 * -----
 * [ ] _____
 * [ ] _____
 * [ ] _____
 * [ ] _____
 * [ ] _____
 */
namespace RuneImp\STF\MVC;
use RuneImp\STF;

class FourZeroFourController implements iController
{
	// CLASS CONSTANTS //
	const CLASS_AUTHOR = 'RuneImp <runeimp@gmail.com>';
	const CLASS_VERSION = '0.1.0';

	// CLASS PROPERTIES //

	// CLASS VARS //
	protected $host;
	protected $model;
	protected $path;
	protected $stf;
	protected $view;

	public function __construct($uri=null)
	{
		$this->stf =& STF::getInstance();
		// echo "<h2>".__CLASS__."</h2>\n";
		// $config = $this->stf->getConfig('route.default');
		$config = $this->stf->getConfig('route.404');
		$this->host = $uri->getHost();
		$this->path = $uri->getPath();
		// echo '<pre>'.__CLASS__.' $this->host: '.print_r($this->host, true)."</pre>\n";
		// echo '<pre>'.__CLASS__.' $this->path: '.print_r($this->path, true)."</pre>\n";
		// echo '<pre>'.__CLASS__.' $config: '.print_r($config, true)."</pre>\n";
		$this->view = new $config['view']($this);
		$this->model = new $config['model']($this->view);

		// $this->view->init();
		$model_settings = array();
		$model_settings['host'] = $this->host;
		$model_settings['path'] = $this->path;
		$this->model->init($model_settings);

		// $this->model->getData();
	}

	public function render()
	{
		//
	}

	public function update($data)
	{
		//
	}
}