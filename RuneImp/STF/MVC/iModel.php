<?php
/**
 * STF Model Interface
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

interface iModel
{
	public function __construct(&$view=null);

	public function init($settings);

	public function getData($key);

	public function update($data);
}