<?php
namespace addons\content_api\v1\user\tools;
use \lib\utility;
use \lib\debug;
use \lib\db\logs;

trait add
{

	use user_check_args;
	/**
	 * Adds a user.
	 *
	 * @param      array    $_args  The arguments
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	public function add_user($_args = [])
	{

		// ready to insert userteam or userbranch record
		$args                  = [];

		// default args
		$default_args =
		[
			'method'   => 'post',
			'debug'    => true,
			'save_log' => true,
		];

		if(!is_array($_args))
		{
			$_args = [];
		}
		// merge default args and args
		$_args = array_merge($default_args, $_args);

		// set default title of debug
		if($_args['debug']) debug::title(T_("Operation Faild"));

		// set the log meta
		$log_meta =
		[
			'data' => null,
			'meta' =>
			[
				'user_id' => $this->user_id,
				'input'   => utility::request(),
			]
		];

		// check user id is exist
		if(!$this->user_id)
		{
			if($_args['save_log']) logs::set('addon:api:user:user_id:notfound', $this->user_id, $log_meta);
			if($_args['debug']) debug::error(T_("User not found"), 'user', 'permission');
			return false;
		}


		$mobile           = null;
		$mobile_syntax    = null;


		// get mobile of user
		$mobile           = utility::request("mobile");
		$mobile_syntax    = \lib\utility\filter::mobile($mobile);

		if($mobile && !$mobile_syntax)
		{
			if($_args['save_log']) logs::set('addon:api:user:mobile:not:set', $this->user_id, $log_meta);
			if($_args['debug']) debug::error(T_("Invalid mobile number"), 'mobile', 'arguments');
			return false;
		}
		elseif($mobile && $mobile_syntax && ctype_digit($mobile))
		{
			$mobile = $mobile_syntax;
		}
		else
		{
			$mobile_syntax = $mobile = null;
		}

		if($mobile)
		{
			$check_duplicate =
			[
				'mobile' => $mobile,
				'status' => ["IN", "('active', 'awaiting')"],
				'limit'  => 1,
			];

			$check_duplicate = \lib\db\users::get($check_duplicate);

			if(isset($check_duplicate['id']))
			{
				if($_args['method'] === 'post')
				{
					if($_args['save_log']) logs::set('addon:api:user:mobile:duplicate', $this->user_id, $log_meta);
					if($_args['debug']) debug::error(T_("Duplicate mobile"), 'mobile', 'arguments');
					return false;
				}
				else
				{
					$id = utility::request('id');
					$id = utility\shortURL::decode($id);
					if(intval($id) === intval($check_duplicate['id']))
					{
						// no problem this is current user
					}
					else
					{
						if($_args['save_log']) logs::set('addon:api:user:mobile:duplicate:update', $this->user_id, $log_meta);
						if($_args['debug']) debug::error(T_("Duplicate mobile"), 'mobile', 'arguments');
						return false;
					}
				}
			}
		}

		$args['mobile'] = $mobile;

		/**
		 * check and set the args
		 */
		$return_function = $this->user_check_args($_args, $args, $log_meta);

		if(!debug::$status || $return_function === false)
		{
			return false;
		}

		// insert new user team
		if($_args['method'] === 'post')
		{
			\lib\db\users::insert($args);
			return \lib\db::insert_id();
		}
		elseif($_args['method'] === 'patch')
		{

			$id = utility::request('id');
			$id = utility\shortURL::decode($id);
			if(!$id)
			{
				if($_args['save_log']) logs::set('addon:api:user:pathc:id:not:set', $this->user_id, $log_meta);
				if($_args['debug']) debug::error(T_("Id not set"), 'id', 'arguments');
				return false;
			}

			if(!utility::isset_request('passportexpire'))      unset($args['passportexpire']);
			if(!utility::isset_request('postion'))             unset($args['postion']);
			if(!utility::isset_request('personnelcode'))       unset($args['personnelcode']);
			if(!utility::isset_request('firstname'))           unset($args['name']);
			if(!utility::isset_request('lastname'))            unset($args['lastname']);
			if(!utility::isset_request('status'))              unset($args['status']);
			if(!utility::isset_request('displayname'))         unset($args['displayname']);
			if(!utility::isset_request('nationalcode'))        unset($args['nationalcode']);
			if(!utility::isset_request('father'))              unset($args['father']);
			if(!utility::isset_request('birthday'))            unset($args['birthday']);
			if(!utility::isset_request('gender'))              unset($args['gender']);
			if(!utility::isset_request('type'))                unset($args['type']);
			if(!utility::isset_request('marital'))             unset($args['marital']);
			if(!utility::isset_request('child'))               unset($args['childcount']);
			if(!utility::isset_request('brithcity'))           unset($args['brithplace']);
			if(!utility::isset_request('shfrom'))              unset($args['from']);
			if(!utility::isset_request('shcode'))              unset($args['shcode']);
			if(!utility::isset_request('education'))           unset($args['education']);
			if(!utility::isset_request('job'))                 unset($args['job']);
			if(!utility::isset_request('passportcode'))        unset($args['pasportcode']);
			if(!utility::isset_request('paymentaccountnumber'))unset($args['cardnumber']);
			if(!utility::isset_request('shaba'))               unset($args['shaba']);

			if(!empty($args))
			{
				\lib\db\users::update($args, $id);
			}
		}

		if(debug::$status)
		{
			if($_args['debug']) debug::title(T_("Operation Complete"));

			if($_args['method'] === 'post')
			{
				if($_args['debug']) debug::true(T_("user successfully added"));
			}
			elseif($_args['method'] === 'patch')
			{
				if($_args['debug']) debug::true(T_("user successfully updated"));
			}
		}
	}
}
?>