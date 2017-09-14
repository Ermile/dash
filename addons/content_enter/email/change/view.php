<?php
namespace addons\content_enter\email\change;

class view extends \addons\content_enter\main\view
{
	public function config()
	{
		parent::config();

		$this->data->get_email = $this->login('email');

		$this->data->page['title']   = T_('Change email');
		$this->data->page['desc']    = $this->data->page['title'];
	}

}
?>