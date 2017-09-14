<?php
namespace addons\content_enter\verify\sendsms;


class controller extends \addons\content_enter\main\controller
{
	public function _route()
	{
		// // bug fix two redirect to this page
		// if(isset($_SERVER['HTTP_ACCEPT']) && $_SERVER['HTTP_ACCEPT'] === '*/*')
		// {
		// 	self::go_redirect('verify/sendsms');
		// 	return;
		// }

		// // if this step is locked go to error page and return
		// if(self::lock('verify/sendsms'))
		// {
		// 	self::error_page('verify/sendsms');
		// 	return;
		// }

		if(self::get_request_method() === 'get')
		{
			// if the user start my bot and wa have her chat id
			// if user start my bot try to send code to this use
			// if okay route this
			// else go to nex way
			if(!self::loaded_module('verify/sendsms'))
			{
				if(isset($_SERVER['REQUEST_URI']) && preg_match("/enter\/verify\/sendsms$/", urldecode($_SERVER['REQUEST_URI'])))
				{
					self::loaded_module('verify/sendsms', true);

					if($this->model()->send_sendsms_code())
					{
						$this->get()->ALL('verify/sendsms');
					}
					else
					{
						// send code way
						// self::send_code_way();
					}
				}
			}
			else
			{
				$this->get()->ALL('verify/sendsms');
			}
		}
		elseif(self::get_request_method() === 'post')
		{
			$this->post('verify')->ALL('verify/sendsms');
		}
		else
		{
			self::error_method('verify/sendsms');
		}
	}
}
?>