<?php
namespace addons\content_enter\delete;


class view extends \addons\content_enter\main\view
{
	/**
	 * view enter
	 *
	 * @param      <type>  $_args  The arguments
	 */
	public function config()
	{
		parent::config();

		if(self::get_enter_session('request_delete_msg'))
		{
			$this->data->get_why = self::get_enter_session('request_delete_msg');
		}


		$this->data->page['title']   = T_('Delete account');
		$this->data->page['desc']    = $this->data->page['title'];
	}
}
?>