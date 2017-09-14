<?php
namespace addons\content_enter\delete\request;

class view extends \addons\content_enter\main\view
{
	public function config()
	{
		parent::config();

		$this->data->page['title']   = T_('Request delete account');
		$this->data->page['desc']    = $this->data->page['title'];
	}

}
?>