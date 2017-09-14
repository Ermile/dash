<?php
namespace addons\content_enter\username\set;

class view extends \addons\content_enter\main\view
{
	public function config()
	{
		parent::config();

		$this->data->get_username = $this->login('username');

		$this->data->page['title']   = T_('Set username');
		$this->data->page['desc']    = $this->data->page['title'];
	}

}
?>