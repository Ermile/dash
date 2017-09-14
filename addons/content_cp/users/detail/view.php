<?php
namespace addons\content_cp\users\detail;

class view extends \addons\content_cp\main\view
{
	public function view_detail($_args)
	{
		if(isset($_args->api_callback))
		{
			$data = $_args->api_callback;
			if(isset($data['user_id']))
			{
				$this->data->get_mobile = \lib\db\users::get_mobile($data['user_id']);
			}
			$this->data->user_record = $data;
		}
	}
}
?>