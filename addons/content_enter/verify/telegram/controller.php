<?php
namespace addons\content_enter\verify\telegram;


class controller extends \addons\content_enter\main\controller
{
	public function _route()
	{
		// bug fix two redirect to this page
		if(isset($_SERVER['HTTP_ACCEPT']) && $_SERVER['HTTP_ACCEPT'] === '*/*')
		{
			self::go_redirect('verify/telegram');
			return;
		}
		// var_dump($_SERVER['REQUEST_URI']);
		// var_dump($_SESSION);exit();
		// if this step is locked go to error page and return
		if(self::lock('verify/telegram'))
		{
			self::error_page('verify/telegram');
			return;
		}

		if(self::get_request_method() === 'get')
		{
			// if the user start my bot and wa have her chat id
			// if user start my bot try to send code to this use
			// if okay route this
			// else go to nex way
			if(!self::loaded_module('verify/telegram'))
			{
				if(isset($_SERVER['REQUEST_URI']) && preg_match("/enter\/verify\/telegram$/", urldecode($_SERVER['REQUEST_URI'])))
				{
					self::loaded_module('verify/telegram', true);

					if($this->model()->send_telegram_code())
					{
						$this->get()->ALL('verify/telegram');
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
				$this->get()->ALL('verify/telegram');
			}
		}
		elseif(self::get_request_method() === 'post')
		{
			$this->post('verify')->ALL('verify/telegram');
		}
		else
		{
			self::error_method('verify/telegram');
		}
	}
}
?>