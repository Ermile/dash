<?php
namespace addons\content_enter\alert;


class view extends \addons\content_enter\main\view
{
	public function config()
	{
		// read parent config to fill the mobile input and other thing
		parent::config();

		$this->data->page['title']   = T_('Alert!');
		$this->data->page['special'] = true;
		$this->data->page['desc']    = $this->data->page['title'];

		$this->data->alert_msg = self::get_alert();

		$this->data->alert_link = $this->url('baseFull'). '/enter';
		if(self::get_alert('link'))
		{
			$this->data->alert_link = self::get_alert('link');
		}

		$this->data->alert_button = T_("Go back");
		if(self::get_alert('button'))
		{
			$this->data->alert_button = self::get_alert('button');
		}
	}
}
?>