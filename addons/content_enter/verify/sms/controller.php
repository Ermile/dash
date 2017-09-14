<?php
namespace addons\content_enter\verify\sms;


class controller extends \addons\content_enter\main\controller
{
	public function _route()
	{
		// bug fix two redirect to this page
		if(isset($_SERVER['HTTP_ACCEPT']) && $_SERVER['HTTP_ACCEPT'] === '*/*')
		{
			self::go_redirect('verify/sms');
			return;
		}

		// if this step is locked go to error page and return
		if(self::lock('verify/sms'))
		{
			self::error_page('verify/sms');
			return;
		}


		if(self::get_request_method() === 'get')
		{
			// if the user start my bot and wa have her chat id
			// if user start my bot try to send code to this use
			// if okay route this
			// else go to nex way
			if(!self::loaded_module('verify/sms'))
			{
				if(isset($_SERVER['REQUEST_URI']) && preg_match("/enter\/verify\/sms$/", urldecode($_SERVER['REQUEST_URI'])))
				{
					/**
					 * set this module as loaded to not send code by every refresh
					 */
					self::loaded_module('verify/sms', true);

					if($this->model()->send_sms_code())
					{
						$this->get()->ALL('verify/sms');
					}
					else
					{
						// send code way
						self::send_code_way();
					}
				}
			}
			else
			{
				$this->get()->ALL('verify/sms');
			}
		}
		elseif(self::get_request_method() === 'post')
		{
			$this->post('verify')->ALL('verify/sms');
		}
		else
		{
			self::error_method('verify/sms');
		}
	}
}
?>