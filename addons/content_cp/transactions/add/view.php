<?php
namespace addons\content_cp\transactions\add;

class view extends \addons\content_cp\main\view
{
	public function view_add($_args)
	{
		if(isset($_args->api_callback))
		{
			$data = $_args->api_callback;
			if(isset($data['user_id']))
			{
				$this->data->get_mobile = \lib\db\users::get_mobile($data['user_id']);
			}
			$this->data->transaction_record = $data;
		}

		if(\lib\utility::get('search'))
		{
			$url = $this->url('full');
			$url = preg_replace("/search\=(.*)(\/|)/", "search=". \lib\utility::get('search'), $url);
			$this->redirector($url)->redirect();
		}

		if(isset($_args->get("search")[0]))
		{
			$this->data->get_search = $_args->get("search")[0];
		}

		if(\lib\utility::get('mobile'))
		{
			$this->data->get_mobile = \lib\utility\filter::mobile(\lib\utility::get('mobile'));
		}
	}

}
?>