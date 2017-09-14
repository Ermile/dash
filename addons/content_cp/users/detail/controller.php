<?php
namespace addons\content_cp\users\detail;

class controller extends \mvc\controller
{
	public function _route()
	{
		\lib\permission::access('cp:user:detail', 'block');

		$this->get(false, "detail")->ALL();

		$this->get("load", "detail")->ALL("/users\/detail\/(\d+)/");

		$this->post('detail')->ALL();
		$this->post('detail')->ALL("/users\/detail\/(\d+)/");
	}
}
?>