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
use RuneImp\STF\Util\StringTools;
use RuneImp\STF\Util\YAML;

class ContentModel implements iModel
{
	// CLASS CONSTANTS //
	const CLASS_AUTHOR = 'RuneImp <runeimp@gmail.com>';
	const CLASS_VERSION = '0.1.0';

	// CLASS PROPERTIES //

	// CLASS VARS //
	protected $content_path;
	protected $data;
	protected $stf;
	protected $view;

	public function __construct(&$view=null)
	{
		$this->stf = STF::getInstance();
		if( $view !== null )
			$this->view =& $view;
		$this->content_path = $this->stf->getConfig('app.content');
		// echo '<pre>'.__CLASS__.' $this->content_path: '.print_r($this->content_path, true)."</pre>\n";
	}

	public function init($settings)
	{
		// echo '<pre>'.__METHOD__.' $settings: '.print_r($settings, true)."</pre>\n";
		
		if( StringTools::countSubstrs($settings['host'], '.') == 1 )
			$host_path = $this->content_path.'/www.'.$settings['host'];
		else
			$host_path = $this->content_path.'/'.$settings['host'];

		if( $settings['path'] == '/' )
			$yaml_path = $host_path.'/root.yml';
		else
			$yaml_path = $host_path.( ( substr($settings['path'], -1) == '/' ) ? substr($settings['path'], 0, -1).'.yml' : $settings['path'].'.yml' );

		if( !is_readable($yaml_path) )
			if( !file_exists($yaml_path) )
				$yaml_path = $this->stf->getConfig('app.error.404').'.yml';
		
		// echo '<pre>'.__METHOD__.' $yaml_path: '.print_r($yaml_path, true)."</pre>\n";
		$this->data = $this->stf->yamlDecode($yaml_path);
		// echo '<pre>'.__METHOD__.' Line: '.__LINE__.' -- $this->data: '.print_r($this->data, true)."</pre>\n";

		if( $this->view !== null )
			$this->view->update($this->data);
	}

	public function getData($key)
	{
		//
	}

	public function update($data)
	{
		//
	}
}