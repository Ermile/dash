<?php
namespace addons\content_cp\tools\sitemap;

class controller extends \addons\content_cp\home\controller
{
	public function _route()
	{
		parent::_route();

		$this->get()->ALL();
	}
}
?>