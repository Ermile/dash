<?php
namespace addons\content_enter\delete;
use \lib\utility;
use \lib\debug;
use \lib\db;

class model extends \addons\content_enter\main\model
{

	/**
	 * Posts an enter.
	 * user try to delete her account
	 * save why posted and verify user account
	 *
	 * @param      <type>  $_args  The arguments
	 */
	public function post_delete($_args)
	{
		if(utility::post('why'))
		{
			self::set_enter_session('why', utility::post('why'));
		}
		// save log the user try to delete account
		\lib\db\logs::set('enter:delete:try', $this->login('id'), ['meta' => ['session' => $_SESSION, 'input' => utility::post()]]);
		// set session verify_from signup
		self::set_enter_session('verify_from', 'delete');

		self::send_code_way();
	}
}
?>