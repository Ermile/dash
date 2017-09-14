<?php
namespace addons\content_enter\callback;
use \lib\utility;
use \lib\debug;
use \lib\db\logs;

class model extends \addons\content_enter\main\model
{
	public function kavenegar()
	{
		\lib\temp::set('api', true);
		$log_meta =
		[
			'data' => null,
			'meta' =>
			[
				'get'  => utility::get(),
				'post' => utility::post(),
			],
		];

		logs::set('enter:callback:sms:resieve', null, $log_meta);

		$message = utility::post('message');
		$message = trim($message);
		if(!$message || mb_strlen($message) < 1)
		{
			logs::set('enter:callback:message:empty', null, $log_meta);
			debug::error(T_("Message is empty"));
			return false;
		}


		$mobile = utility::post('from');

		if($mobile)
		{
			$mobile = \lib\utility\filter::mobile($mobile);
		}

		if(!$mobile)
		{
			logs::set('enter:callback:from:not:set', null, $log_meta);
			debug::error(T_("Mobile not set"));
			return false;
		}

		$user_data = \lib\db\users::get_by_mobile($mobile);

		if(!$user_data || !isset($user_data['id']))
		{
			return $this->first_signup_sms();
		}

		$user_id = $user_data['id'];

		$find_log =
		[
			'caller'     => 'enter:get:sms:from:user',
			'user_id'    => $user_id,
			'data'   => $message,
			'status' => 'enable',
		];

		$find_log = logs::get($find_log);

		if(!$find_log || !is_array($find_log) || count($find_log) === 0)
		{
			logs::set('enter:callback:sms:resieve:log:not:found', $user_id, $log_meta);
			debug::error(T_("Log not found"));
			return false;
		}

		if(count($find_log) > 1)
		{
			logs::set('enter:callback:sms:more:than:one:log:found', $user_id, $log_meta);
			foreach ($find_log as $key => $value)
			{
				if(isset($value['id']))
				{
					logs::update(['status' => 'expire'], $value);
				}
			}
			debug::error(T_("More than one log found"));
			return false;
		}


		if(count($find_log) === 1)
		{
			$find_log = $find_log[0];
			if(isset($find_log['id']))
			{
				logs::update(['status' => 'deliver'], $find_log['id']);
				debug::true(T_("OK"));
				return true;
			}
		}

		// {
		// 	"get":"service=kavenegar&type=recieve&uid=201700001",
		// 	"post":
		// 		{
		// 			"messageid":"308404060",
		// 			"from":"09109610612",
		// 			"to":"10006660066600",
		// 			"message":"Salamq"
		// 		}
		// 	}
	}


	/**
	 * singup user and send the regirster sms to he
	 */
	public function first_signup_sms()
	{
		$mobile = utility::post('from');

		if($mobile)
		{
			$mobile = \lib\utility\filter::mobile($mobile);
		}

		if(!$mobile)
		{
			debug::error(T_("Mobile not set"));
			return false;
		}

		$signup =
		[
			'mobile'      => $mobile,
			'password'        => null,
			'displayname' => null,
			'datecreated'  => date("Y-m-d H:i:s"),
		];

		\lib\db\users::insert($signup);
		$user_id = \lib\db::insert_id();


		$log_meta =
		[
			'data' => null,
			'meta' =>
			[
				'get'  => utility::get(),
				'post' => utility::post(),
			],
		];

		logs::set('enter:callback:signup:by:sms', $user_id, $log_meta);

		$request           = [];
		$request['mobile'] = $mobile;
		$request['msg']    = T_("Your are registered to :service", ['service' => \lib\router::get_root_domain()]);
		$request['args']   = '';
		$kavenegar_send_result = \lib\utility\sms::send($request);
		$log_meta['meta']['register_sms_result'] = $kavenegar_send_result;
		logs::set('enter:callback:sms:registe:reasult', $user_id, $log_meta);

		debug::true(T_("User signup by sms"));


	}
}
?>