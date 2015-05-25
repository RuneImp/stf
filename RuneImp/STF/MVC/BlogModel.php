<?php
/**
 * STF Blog Model
 *
 * @author RuneImp <runeimp@gmail.com>
 * @version 0.1.0
 */
/*
 * ChangeLog:
 * ----------
 * 2014-06-18	v0.1.0	Initial class creation
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

class BlogModel implements iModel
{
	// CLASS CONSTANTS //
	const CLASS_AUTHOR = 'RuneImp <runeimp@gmail.com>';
	const CLASS_VERSION = '0.1.0';

	// CLASS PROPERTIES //

	// CLASS VARS //
	protected $stf;

	public function __construct(&$view=null)
	{
		$this->stf = STF::getInstance();
		if( $view !== null )
			$this->view =& $view;
		$this->content_path = $this->stf->getConfig('app.content');
		echo '<pre>'.__CLASS__.' $this->content_path: '.print_r($this->content_path, true)."</pre>\n";
	}

	public function init($settings)
	{
		echo '<pre>'.__CLASS__.' $settings: '.print_r($settings, true)."</pre>\n";
		
		if( StringTools::countSubstrs($settings['host'], '.') == 1 )
			$yaml_path = $this->content_path.'/www.'.$settings['host'];
		else
			$yaml_path = $this->content_path.'/'.$settings['host'];

		$yaml_path .= ( substr($settings['path'], -1) == '/' ) ? substr($settings['path'], 0, -1).'.yml' : $settings['path'].'.yml';

		echo '<pre>'.__CLASS__.' $yaml_path: '.print_r($yaml_path, true)."</pre>\n";
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