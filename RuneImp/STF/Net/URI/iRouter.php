<?php
/**
 * STF Router Interface
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
namespace RuneImp\STF\Net\URI;

interface iRouter
{
	public function __construct(iURIParser &$sturl, $data);

	public function getController($stf, $uri=null);
}