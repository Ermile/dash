<?php
namespace addons\content_enter\ban;


class view extends \addons\content_enter\main\view
{
	public function config()
	{
		// read parent config to fill the mobile input and other thing
		parent::config();

		$this->data->page['title']   = T_('You are Banned!!');
		$this->data->page['special'] = true;
		$this->data->page['desc']    = $this->data->page['title'];
	}
}
?>