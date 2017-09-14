<?php
namespace addons\content_api\v1\parent\tools;
use \lib\utility;
use \lib\debug;
use \lib\db\logs;

trait add
{
	public $parent_id;

	/**
	 * Adds a parent.
	 *
	 * @param      array    $_args  The arguments
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	public function add_parent($_args = [])
	{
		$default_args =
		[
			'method' => 'post'
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
			logs::set('api:parent:user_id:notfound', null, $log_meta);
			debug::error(T_("User not found"), 'user', 'permission');
			return false;
		}

		$parent_id = null;

		$mobile = utility::request('mobile');
		if(!$mobile)
		{
			logs::set('api:parent:mobile:not:set', $this->user_id, $log_meta);
			debug::error(T_("Please set the parent mobile"), 'mobile');
			return false;
		}

		$mobile = \lib\utility\filter::mobile($mobile);

		if(!$mobile)
		{
			logs::set('api:parent:mobile:invalid', $this->user_id, $log_meta);
			debug::error(T_("Invalid mobile number"), 'mobile');
			return false;
		}

		if(!utility::request('title'))
		{
			logs::set('api:parent:title:not:set', $this->user_id, $log_meta);
			debug::error(T_("Please select one title"));
			return false;
		}

		$get_parent_data = \lib\db\users::get_by_mobile($mobile);

		if(!isset($get_parent_data['id']))
		{
			$parent_id = \lib\db\users::signup_quick(['mobile' => $mobile]);
			$get_parent_data['mobile'] = $mobile;
		}
		else
		{
			$parent_id = $get_parent_data['id'];
		}

		$this->parent_id = $parent_id;

		if(intval($parent_id) === intval($this->user_id))
		{
			logs::set('api:parent:parent:yourself', $this->user_id, $log_meta);
			debug::error(T_("You can not set parent yourself"));
			return false;
		}

		$titles =
		[
			'father',
			'mother',
			'sister',
			'brother',
			'grandfather',
			'grandmother',
			'aunt',
			'husband',
			'uncle',
			'boy',
			'girl',
			'spouse',
			'stepmother',
			'stepfather',
			'neighbor',
			'teacher',
			'friend',
			'boss',
			'supervisor',
			'child',
			'grandson',
			'custom',
		];

		if(!in_array(utility::request('title'), $titles))
		{
			logs::set('api:parent:title:inavalid', $this->user_id, $log_meta);
			debug::error(T_("Invalid title"));
			return false;
		}

		if(utility::request('title') === 'custom' && !utility::request('othertitle'))
		{
			logs::set('api:parent:title:othertitle:not:set', $this->user_id, $log_meta);
			debug::error(T_("Plase set the other title field"));
			return false;
		}

		if(utility::request('othertitle') && mb_strlen(utility::request('othertitle')) > 50)
		{
			logs::set('api:parent:title:othertitle:max:lenght', $this->user_id, $log_meta);
			debug::error(T_("You must set other title less than 50 character"));
			return false;
		}

		if($this->check_duplicate_request())
		{
			logs::set('api:parent:title:othertitle:max:lenght', $this->user_id, $log_meta);
			debug::error(T_("Your request was sended to user, wait for answer user"));
			return ;
		}

		$get_user_data = \lib\db\users::get_by_id($this->user_id);

		$meta                       = [];
		$meta['user_id']            = $this->user_id;
		$meta['displayname']   = isset($get_user_data['displayname']) ? $get_user_data['displayname'] : null;
		$meta['fileurl']      = isset($get_user_data['fileurl']) ? $get_user_data['fileurl'] : null;;
		$meta['parent_id']          = isset($get_parent_data['id']) ? $get_parent_data['id'] : null;
		$meta['parent_mobile']      = isset($get_parent_data['mobile']) ? $get_parent_data['mobile'] : null;
		$meta['parent_displayname'] = isset($get_parent_data['displayname']) ? $get_parent_data['displayname'] : null;
		$meta['parent_fileurl']    = isset($get_parent_data['fileurl']) ? $get_parent_data['fileurl'] : null;
		$meta['title']              = utility::request('title');
		$meta['othertitle']         = utility::request('othertitle');

		$send_notify =
		[
			'from'            => $this->user_id,
			'to'              => $parent_id,
			'cat'             => 'set_parent',
			'related_foreign' => 'users',
			'status'		  => 'enable',
			'related_id'      => $this->user_id,
			'meta'            => json_encode(\lib\utility\safe::safe($meta), JSON_UNESCAPED_UNICODE),
			'needanswer'      => 1,
			'content'         => T_("Are you :title of this user?", ['title' => T_(utility::request('title'))]),
		];

		$a = \lib\db\notifications::set($send_notify);

		if(debug::$status)
		{
			debug::true(T_("Your request was sended"));
		}

	}


	/**
	 * { function_description }
	 */
	public function check_duplicate_request()
	{
		$get =
		[
			'user_idsender'   => $this->user_id,
			'user_id'         => $this->parent_id,
			'category'        => 9,
			'status'          => 'enable',
			'related_id'      => $this->user_id,
			'related_foreign' => 'users',
			'needanswer'      => 1,
			'answer'          => null,
			'limit'           => 1,
		];

		$check_notify = \lib\db\notifications::get($get);

		if($check_notify && is_array($check_notify))
		{
			if(isset($check_notify['status']))
			{
				if(array_key_exists('answer', $check_notify) && !$check_notify['answer'])
				{
					return $check_notify;
				}
			}
		}
		return false;

	}

}
?>