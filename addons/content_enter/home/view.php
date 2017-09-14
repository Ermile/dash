<?php
namespace addons\content_enter\home;

class view extends \addons\content_enter\main\view
{
	/**
	 * config view
	 */
	public function config()
	{
		// read parent config to fill the mobile input and other thing
		parent::config();
		// just on this page the mobile is not read only
		$this->data->mobile_readonly = false;
	}


	/**
	 * view enter
	 *
	 * @param      <type>  $_args  The arguments
	 */
	public function view_enter($_args)
	{
		$this->data->page['special'] = true;
		$this->data->page['title']   = T_('Enter to :name with mobile', ['name' => $this->data->site['title']]);
		$this->data->page['desc']    = $this->data->page['title'];

		if(isset($_SESSION['main_account']))
		{
			$this->data->main_account = true;
		}
		else
		{
			$this->data->main_account = false;

		}

		$mobile = \lib\utility::get('mobile');
		if($mobile)
		{
			if($this->data->main_account)
			{
				$this->data->get_mobile = $mobile;

			}
			else
			{
				$this->data->get_mobile = \lib\utility\filter::mobile($mobile);
			}
		}
	}
}
?>