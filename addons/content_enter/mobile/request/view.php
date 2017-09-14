<?php
namespace addons\content_enter\mobile\request;

class view extends \addons\content_enter\main\view
{
	public function config()
	{
		parent::config();

		$this->data->mobile_readonly = false;

		$this->data->page['title']   = T_('Request mobile number');
		$this->data->page['desc']    = $this->data->page['title'];
	}
}
?>