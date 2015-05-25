<?php
/**
 * STF Abstract View
 *
 * @author RuneImp <runeimp@gmail.com>
 * @version 0.1.0
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
 * [ ] _____
 * [ ] _____
 */
namespace RuneImp\STF\MVC;

class AbstractView implements iView
{
	protected $config;
	protected $controller;

	public function __construct(&$config)
	{
		$this->config = $config;
	}

	public function init(&$controller)
	{
		$this->controller = &$controller;
	}

	public function render()
	{
		// Render the view
	}
}