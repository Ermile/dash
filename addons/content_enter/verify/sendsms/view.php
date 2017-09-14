<?php
namespace addons\content_enter\verify\sendsms;


class view extends \addons\content_enter\verify\view
{
	public function config()
	{
		parent::config();
		// $this->data->page['title'] = ;
		// $this->data->page['desc']  = T_("Send SMS to login");
		if(self::get_enter_session('sendsms_code'))
		{
			$this->data->codeSend    = self::get_enter_session('sendsms_code');
			$this->data->codeSendNum = '+98 1000 66600 66600';
			$this->data->codeSendMsg = T_('Send ":code" to :num',
				[
					'code' => $this->data->codeSend,
					'num' => '<b><code>'. $this->data->codeSendNum. '</code></b>'
				]
				);
		}
	}
}
?>