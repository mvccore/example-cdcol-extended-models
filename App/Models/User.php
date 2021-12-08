<?php

namespace App\Models;

/** 
 * @table users
 */
class User 
extends \App\Models\Base
implements \MvcCore\Ext\Auths\Basics\IUser {

	use \MvcCore\Ext\Auths\Basics\User\Features;


	/**
	 * Get user model instance from database or any other users list
	 * resource by submitted and cleaned `$userName` field value.
	 * @param string $userName Submitted and cleaned username. Characters `' " ` < > \ = ^ | & ~` are automatically encoded to html entities by default `\MvcCore\Ext\Auths\Basic` sign in form.
	 * @return \App\Models\User|NULL
	 */
	public static function GetByUserName ($userName) {
		return self::GetConnection()
			->Prepare([
				"SELECT *						",
				"FROM users u					",
				"WHERE u.user_name = :user_name;",
			])
			->FetchOne([':user_name' => $userName])
			->ToInstance(
				get_called_class(),
				self::PROPS_INHERIT |
				self::PROPS_PROTECTED |
				self::PROPS_INITIAL_VALUES
			);
	}
}