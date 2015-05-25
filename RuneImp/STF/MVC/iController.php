<?php
/**
 * STF Controller Interface
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

interface iController
{
	public function __construct($uri=null);

	public function render();

	public function update($data);
}