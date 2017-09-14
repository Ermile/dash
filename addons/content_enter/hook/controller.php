<?php
namespace addons\content_enter\hook;

class controller extends  \mvc\controller
{

	public function _route()
	{
		$this->post('user')->ALL();

		$this->delete('chat_id')->ALL();
	}

}
?>