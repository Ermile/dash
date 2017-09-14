<?php
namespace lib;
class main
{
	static $controller, $url_property, $prv_class, $myrep, $prv_method = null;
	static $tracks 		= array();

	function check_controller($_controller_name){
		if(!class_exists($_controller_name))
			return NULL;
		else
			return $_controller_name;
	}

	function add_track($_name, $_function){
		array_push(self::$tracks, array($_name, $_function));
	}

	public function add_controller_tracks(){
		$this->add_track('default', function(){
			return router::get_controller();
		});

		$this->add_track('class_method', function(){
			$controller_name	= '\\'.self::$myrep.'\\'.router::get_class().'\\'.router::get_method().'\\controller';
			self::$prv_class	= router::get_class();
			return $this->check_controller($controller_name);
		});

		$this->add_track('class_home', function(){
			if((!isset(self::$url_property[1]) || self::$url_property[1] != router::get_method()) && router::get_method() != 'home')
			{
				router::add_url_property(router::get_method());
			}
			self::$prv_method = router::get_method();
			router::set_method('home');
			$controller_name = '\\'.self::$myrep.'\\'.router::get_class().'\\'.router::get_method().'\\controller';

			return $this->check_controller($controller_name);
		});

		$this->add_track('class', function(){
			router::set_class(self::$prv_class);
			$controller_name = '\\'.self::$myrep.'\\'.router::get_class().'\\controller';

			return $this->check_controller($controller_name);
		});

		$this->add_track('home_home', function(){
			if((!isset(self::$url_property[0]) || self::$url_property[0] != router::get_class()) && router::get_class() != 'home')
			{
				router::add_url_property(router::get_class());
			}
			router::set_class('home');
			$controller_name = '\\'.self::$myrep.'\\'.router::get_class().'\\'.router::get_method().'\\controller';

			return $this->check_controller($controller_name);
		});

		$this->add_track('home', function(){
			router::set_class('home');
			$controller_name = '\\'.self::$myrep.'\\'.router::get_class().'\\controller';

			return $this->check_controller($controller_name);
		});
	}

	public function controller_finder(){
		self::$url_property = router::get_url_property(-1);
		self::$myrep        = router::get_repository_name();

		$this->add_controller_tracks();

		foreach (self::$tracks as $key => $value)
		{
			$track = self::$tracks[$key][1];
			$controller_name = $track();
			if($controller_name) break;
		}
		$this->loadController($controller_name);
	}

	public function __construct()
	{

		$this->controller_finder();

	}

	public function loadController($controller_name)
	{

		router::set_controller($controller_name);
		if(!class_exists($controller_name))
		{
			error::page($controller_name);
		}


		$controller = new $controller_name;
		self::$controller = $controller;

		if(method_exists($controller, '_route'))
		{
			$controller->i_route();
		}

		if(router::get_controller() !== $controller_name)
		{
			$this->controller_finder();
			return;
		}

		if(method_exists($controller, 'config') || array_key_exists('config', $controller->Methods))
		{
			$controller->iconfig();
		}
		if(method_exists($controller, 'options'))
		{
			$controller->ioptions();
		}

		if(count(router::get_url_property(-1)) > 0 && $controller->route_check_true === false)
		{
			error::page('Unavailable');
		}
		$controller->i_corridor();
	}
}
?>