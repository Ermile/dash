<?php
namespace addons\content_enter\sessions;
use \lib\utility;
use \lib\debug;
use \lib\db;

class model extends \addons\content_enter\main\model
{
	/**
	 * Gets the enter.
	 *
	 * @param      <type>  $_args  The arguments
	 */
	public function sessions_list()
	{
		if($this->login())
		{
			$user_id = $this->login('id');
			$list = \lib\db\sessions::get_active_sessions($user_id);
			return $list;
		}
	}


	/**
	 * Posts an sessions.
	 *
	 * @param      <type>  $_args  The arguments
	 */
	public function post_sessions($_args)
	{
		if(!$this->login())
		{
			return false;
		}

		if(utility::post('type') === 'terminate' && utility::post('id') && is_numeric(utility::post('id')))
		{
			if(\lib\db\sessions::is_my_session(utility::post('id'), $this->login('id')))
			{
				\lib\db\sessions::terminate_id(utility::post('id'));
				debug::true(T_("Session terminated"));
				return true;
			}
		}
	}
}
?>