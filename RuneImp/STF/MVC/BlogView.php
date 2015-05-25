<?php
/**
 * STF View Interface
 *
 * @author	RuneImp <runeimp@gmail.com>
 * @version	0.1.0
 */
/*
 * ChangeLog:
 * ----------
 * 2014-03-23	v0.1.0	Initial class creation
 *
 * ToDo:
 * -----
 * [ ] _____
 * [ ] _____
 * [ ] _____
 */
namespace RuneImp\STF\MVC;
use RuneImp\STF;

class BlogView implements iView
{
	// CLASS CONSTANTS //
	const CLASS_AUTHOR = 'RuneImp <runeimp@gmail.com>';
	const CLASS_VERSION = '0.1.0';

	// CLASS PROPERTIES //

	// CLASS VARS //
	protected $stf;

	public function __construct(&$controller=null)
	{
		$this->stf = STF::getInstance();
	}

	public function init(&$controller=null)
	{
		//
	}

	public function render($settings)
	{
		//
	}

	public function update($data)
	{
		//
	}
}