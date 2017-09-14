<?php
namespace addons\content_enter\email\set;

class view extends \addons\content_enter\main\view
{

	public function config()
	{
		parent::config();

		$this->data->get_email = $this->login('email');

		$this->data->page['title']   = T_('set email');
		$this->data->page['desc']    = $this->data->page['title'];
	}

}
?>