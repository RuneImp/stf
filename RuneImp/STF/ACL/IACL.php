<?php

namespace RuneImp\STF\ACL;

interface IACL
{
	public function getACLByUser($user);
	public function getACLByUserID($user_id);
}