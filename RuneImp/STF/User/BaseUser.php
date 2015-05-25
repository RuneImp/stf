<?php
/**
 * Base User class
 */

namespace RuneImp\STF\User;

class BaseUser
{
	protected $id;
	protected $info;

	public function __construct($id=0, $info=null)
	{
		$this->id = $id;
		$this->info = $info;
	}
}