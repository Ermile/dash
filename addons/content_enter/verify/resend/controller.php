<?php
namespace addons\content_enter\verify\resend;


class controller extends \addons\content_enter\main\controller
{
	public function _route()
	{
		// bug fix two redirect to this page
		// if(isset($_SERVER['HTTP_ACCEPT']) && $_SERVER['HTTP_ACCEPT'] === '*/*')
		// {
		// 	return ;
		// }

		// if this step is locked go to error page and return
		if(self::lock('verify/resend'))
		{
			self::error_page('verify/resend');
			return;
		}

		// check method
		if(self::get_request_method() === 'get')
		{
			// if the user start my bot and wa have her chat id
			// if user start my bot try to send code to this use
			// if okay route this
			// else go to nex way

			if(self::get_enter_session('send_code_at_time'))
			{
				if(time() - intval(self::get_enter_session('send_code_at_time')) < self::$resend_after)
				{
					self::error_page('verify/resend/why/harry?');
					return;
				}
				else
				{
					// send code way
					self::send_code_way();

					$args['force_json']   = false;
					$args['force_stop']   = true;
					$args['not_redirect'] = false;

					$this->_processor($args);
				}
			}
			else
			{
				self::error_page('verify/resend/time?');
			}
		}
		else
		{
			self::error_method('verify/resend');
		}

	}
}
?>