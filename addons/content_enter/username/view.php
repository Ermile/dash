<?php
namespace addons\content_enter\username;


class view extends \addons\content_enter\main\view
{
	public function config()
	{
		// read parent config to fill the mobile input and other thing
		parent::config();

		$this->data->page['title']   = T_('Enter to :name with username', ['name' => $this->data->site['title']]);
		$this->data->page['special'] = true;
		$this->data->page['desc']    = $this->data->page['title'];
	}
}
?>