<?php
namespace content_su\sms;


class model
{
	public static function post()
	{


		$mobile            = \dash\request::post('mobile');

		$msg = \dash\request::post('msg');
		if(!$msg)
		{
			\dash\notif::error(T_("No message was sended"), 'msg');
			return false;
		}

		if(!$mobile)
		{
			$mobile = \dash\request::post('mobile');
		}

		$mobile = \dash\utility\filter::mobile($mobile);

		if(!$mobile)
		{
			\dash\notif::error(T_("Invalid mobile number"), 'mobile');
			return false;
		}

		$result = \dash\utility\sms::send($mobile, $msg);

		\dash\code::dump(\dash\temp::get('specialSMSResponse'));
		\dash\code::dump($result);
		\dash\code::boom();

	}
}
?>
