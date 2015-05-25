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

interface iView
{
	public function __construct(&$controller=null);

	public function init(&$controller=null);

	public function render($settings);

	public function update($data);
}