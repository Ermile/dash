<?php
namespace ilib;
use \lib\router;

class main extends \lib\main{
	// if content_enter go to 3 level of controller
	function add_controller_tracks()
	{
		if(router::get_repository_name() == 'content_enter' && router::get_url(2))
		{
			$this->add_track('api_childs', function()
			{
				$controller_name  = '\\'. self::$myrep;
				$controller_name .= '\\'. router::get_class();
				$controller_name .= '\\'. router::get_method();
				$controller_name .= '\\'. router::get_url(2);
				$controller_name .= '\\controller';
				return $this->check_controller($controller_name);
			});
		}
		parent::add_controller_tracks();
	}

	/**
	 * check controller
	 *
	 * @param      <type>  $_controller_name  The controller name
	 *
	 * @return     string  ( description_of_the_return_value )
	 */
	function check_controller($_controller_name)
	{
		$default_controller = parent::check_controller($_controller_name);
		if(!$default_controller)
		{
			$controller_name = '\addons'. $_controller_name;
			if(!class_exists($controller_name))
			{
				return NULL;
			}
			else
			{
				return $controller_name;
			}
		}
		return $default_controller;
	}
}
?>