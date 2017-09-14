<?php
namespace addons\content_api\v1\parent\tools;
use \lib\utility;
use \lib\debug;
use \lib\db\logs;

trait delete
{

	public function parent_cancel_request($_args = [])
	{
		$default_args =
		[
			'method' => 'put'
		];

		if(!is_array($_args))
		{
			$_args = [];
		}

		$_args = array_merge($default_args, $_args);


		$log_meta =
		[
			'data' => null,
			'meta' =>
			[
				'input' => utility::request(),
			]
		];

		if(!$this->user_id)
		{
			logs::set('api:parent:remove:request:user_id:notfound', null, $log_meta);
			debug::error(T_("User not found"), 'user', 'permission');
			return false;
		}

		$notify_id = utility::request('id');

		$notify_id = \lib\utility\shortURL::decode($notify_id);
		if(!$notify_id)
		{
			logs::set('api:parent:remove:request:notify:id:not:set', $this->user_id, $log_meta);
			debug::error(T_("Invalid request id"));
			return false;
		}


		$get_notify =
		[
			'id'              => $notify_id,
			'user_idsender'   => $this->user_id,
			'category'        => 9,
			'status'          => 'enable',
			'related_id'      => $this->user_id,
			'related_foreign' => 'users',
			'needanswer'      => 1,
			'answer'          => null,
			'limit'           => 1,
		];

		$check_ok = \lib\db\notifications::get($get_notify);
		if(!$check_ok)
		{
			logs::set('api:parent:remove:request:notify:data:invalid:access', $this->user_id, $log_meta);
			debug::error(T_("Invalid request data"));
			return false;
		}

		\lib\db\notifications::update(['status' => 'cancel'], $notify_id);
		if(debug::$status)
		{
			logs::set('api:parent:remove:request:sucsessful', $this->user_id, $log_meta);
			debug::true(T_("Your request canceled"));
		}

	}


	/**
	 * delete the parent
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	public function delete_parent($_args = [])
	{
		$default_args =
		[
			'method' => 'put'
		];

		if(!is_array($_args))
		{
			$_args = [];
		}

		$_args = array_merge($default_args, $_args);


		$log_meta =
		[
			'data' => null,
			'meta' =>
			[
				'input' => utility::request(),
			]
		];

		if(!$this->user_id)
		{
			logs::set('api:parent:delete:user_id:notfound', null, $log_meta);
			debug::error(T_("User not found"), 'user', 'permission');
			return false;
		}

		$userparents_id = utility::request('id');
		$userparents_id = \lib\utility\shortURL::decode($userparents_id);
		if(!$userparents_id)
		{
			logs::set('api:parent:delete:notify:data:invalid:access', $this->user_id, $log_meta);
			debug::error(T_("Invalid remove data"));
			return false;
		}

		$get =
		[
			'id'      => $userparents_id,
			'user_id' => $this->user_id,
			'limit'   => 1,
		];

		$check = \lib\db\userparents::get($get);
		if(!isset($check['id']))
		{
			logs::set('api:parent:delete:notify:data:invalid:access:id', $this->user_id, $log_meta);
			debug::error(T_("Invalid remove details"));
			return false;
		}

		\lib\db\userparents::update(['status' => 'deleted'], $userparents_id);
		if(debug::$status)
		{
			logs::set('api:parent:delete:sucsessful', $this->user_id, $log_meta);
			debug::true(T_("Parent removed"));
		}
	}
}
?>