<?php
/**
 * ACL PrivilegedUser
 *
 * @see http://www.sitepoint.com/role-based-access-control-in-php/
 */

namespace RuneImp\STF\ACL;

use RuneImp\STF\User\BaseUser;

class PrivilegedUser extends BaseUser
{
	private $roles;
 
	public function __construct($id) {
		parent::__construct($id);
	}
 
	// override User method
	public static function getByUsername($username) {
		$sql = "SELECT * FROM users WHERE username = :username";
		$sth = $GLOBALS["DB"]->prepare($sql);
		$sth->execute(array(":username" => $username));
		$result = $sth->fetchAll();
 
		if (!empty($result)) {
			$privUser = new PrivilegedUser();
			$privUser->user_id = $result[0]["user_id"];
			$privUser->username = $username;
			$privUser->password = $result[0]["password"];
			$privUser->email_addr = $result[0]["email_addr"];
			$privUser->initRoles();
			return $privUser;
		} else {
			return false;
		}
	}
 
	// populate roles with their associated permissions
	protected function initRoles() {
		$this->roles = array();
		$sql = "SELECT t1.role_id, t2.role_name FROM user_role as t1
				JOIN roles as t2 ON t1.role_id = t2.role_id
				WHERE t1.user_id = :user_id";
		$sth = $GLOBALS["DB"]->prepare($sql);
		$sth->execute(array(":user_id" => $this->user_id));
 
		while($row = $sth->fetch(PDO::FETCH_ASSOC)) {
			$this->roles[$row["role_name"]] = Role::getRolePerms($row["role_id"]);
		}
	}
 
	// check if user has a specific privilege
	public function hasPrivilege($perm) {
		foreach ($this->roles as $role) {
			if ($role->hasPerm($perm)) {
				return true;
			}
		}
		return false;
	}
}