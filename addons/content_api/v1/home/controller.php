<?php
namespace addons\content_api\v1\home;

class controller extends  \mvc\controller
{
	/**
	 * get storage api to show json in every view
	 */
	public function __construct()
	{
		$url = \lib\router::get_url();
		if($url == 'v1')
		{
			\lib\temp::set('api', false);
		}
		else
		{
			\lib\temp::set('api', true);
		}

		parent::__construct();
	}


	/**
	 * route
	 */
	public function _route()
	{
		$url = \lib\router::get_url();

		if($url == 'v1')
		{
			$this->redirector('v1/doc')->redirect();
			return;
		}
	}


	/**
	 * method GET just allowed
	 */
	public function corridor()
	{
		if(!$this->method && $_SERVER['REQUEST_METHOD'] !== 'GET')
		{
			\lib\error::method($_SERVER['REQUEST_METHOD'] . " not allowed");
		}
	}
}
?>