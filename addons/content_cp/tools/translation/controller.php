<?php
namespace addons\content_cp\tools\translation;

class controller extends \addons\content_cp\home\controller
{
	public function _route()
	{
		parent::_route();

		$this->getUpdates();
		$this->get()->ALL();
	}

	function getUpdates()
	{
		parent::_permission('cp', 'tools', 'admin');

		$exist    = true;
		$mypath   = \lib\utility::get('path');
		$myupdate = \lib\utility::get('update');
		if($mypath)
		{
			echo \lib\utility\twigTrans::extract($mypath, $myupdate);
			exit();
		}
	}
}
?>