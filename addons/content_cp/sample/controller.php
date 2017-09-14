<?php
namespace addons\content_cp\sample;

class controller extends \addons\content_cp\home\controller
{

	function _route()
	{
		// $this->get()->all();
		// check permission to access to cp
		parent::_permission('cp');

		$addr = \lib\router::get_url(1);

		if(!$addr)
		{
			return;
		}

		if(is_file(addons.'content_cp/sample/template/'.$addr.'.html'))
		{
			$this->display_name     = 'content_cp/sample/template/'.$addr.'.html';
			$this->route_check_true = true;
			$this->get()->ALL();
		}
	}

}
?>