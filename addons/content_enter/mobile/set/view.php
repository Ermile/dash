<?php
namespace addons\content_enter\pass\set;

class view extends \addons\content_enter\pass\view
{
	public function config()
	{
		parent::config();

		$this->data->page['title']   = T_('set mobile number');
		$this->data->page['desc']    = $this->data->page['title'];
	}

}
?>