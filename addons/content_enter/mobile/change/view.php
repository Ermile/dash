<?php
namespace addons\content_enter\pass\change;

class view extends \addons\content_enter\pass\view
{

	public function config()
	{
		parent::config();

		$this->data->page['title']   = T_('change mobile number');
		$this->data->page['desc']    = $this->data->page['title'];
	}
}
?>