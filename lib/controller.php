<?php
namespace lib;
use \lib\router\route;
use \lib\api;
class controller
{
	use mvc;
	static $language = false;
	public $custom_language = false;
	public $api, $model, $view, $method;
	public $model_name, $view_name, $display_name;
	public $debug = true;
	public static $manifest;

	/**
	 * if display true && method get corridor run view display
	 * @var boolean
	 */
	public $display = true;

	public $route_check_true = false;

	/**
	 * [__construct description]
	 */
	public function __construct()
	{
		$manifest = new controller\manifest();
		self::$manifest = $manifest->get();
		$this->addons();
		/**
		 * register shutdown function
		 * after ending code this function is called
		 */
		// register_shutdown_function('\lib\controller::sp_shutdown');
		register_shutdown_function([$this, 'sp_shutdown']);
	}

	/**
	 * this function is calling at the end of all codes
	 * @return [type] [description]
	 */
	public function sp_shutdown()
	{
		// close writing sessions and start saving it
		// session_write_close();
		// $visitor = new \lib\utility\visitors();
		// if logvisitor on set visitors
		if(\lib\option::config('log_visitors'))
		{
			\lib\utility\visitor::save();
		}
	}


	/**
	 * [loadModel description]
	 * @return [type] [description]
	 */
	public function loadModel()
	{
		if(!isset($this->loadModel)) $this->loadModel = new \lib\load($this, "model");
		return call_user_func_array(array($this->loadModel, 'method'), func_get_args());
	}


	/**
	 * [loadView description]
	 * @return [type] [description]
	 */
	public function loadView()
	{
		if(!isset($this->loadModel)) $this->loadModel = new \lib\load($this, "view");
		return call_user_func_array(array($this->loadModel, 'method'), func_get_args());
	}


	/**
	 * [_corridor description]
	 * @return [type] [description]
	 */
	public function _corridor()
	{
		if(method_exists($this, 'corridor'))
		{
			$this->corridor();
		}
		if(!$this->method) $this->method = 'get';
		$processor_arg = false;
		if(isset($this->model_api_processor))
		{
			$name = $this->model_api_processor->method;
			$args = $this->model_api_processor->args;
			$api_callback = call_user_func_array(array($this->model(), $name), array($args));
			$this->api_callback = $api_callback;

		}
		if(isset($this->caller))
		{
			foreach ($this->caller as $key => $value) {
				$args = $value[2];
				if($value[0])
				{
					$caller_callback = call_user_func_array(array($this->model(), "api_".$value[0]), array($args));
					$this->caller[$key][2]->callback = $caller_callback;
				}
			}
		}
		if(\dash::is_json_accept())
		{
			$this->display = false;
		}
		if(!\lib\temp::get('api') && $this->method == 'get' && $this->display)
		{
			$this->view();

			if(isset($this->view_api_processor))
			{
				$name = $this->view_api_processor->method;
				$args = $this->view_api_processor->args;
				if(isset($this->api_callback)) $args->api_callback = $api_callback;
				call_user_func_array(array($this->view(), $name), array($args));

			}

			if(isset($this->caller))
			{
				foreach ($this->caller as $key => $value) {
					$args = $value[2];
					if($value[1])
					{
						$caller_callback = call_user_func_array(array($this->view(), 'caller_'.$value[1]), array($args));
					}
				}
			}

			if($this->display)
			{
				$this->view()->corridor();
			}
		}
		elseif(\lib\temp::get('api') || !$this->display)
		{
			$mycallback = isset($this->api_callback)? $this->api_callback: null;
			debug::msg('callback', $mycallback);
			if($mycallback !== false && $mycallback !== null)
			{
				debug::result($mycallback);
			}
			$processor_arg = object(array('force_json'=>true));
		}
		if($this->model)
		{
			$this->model()->_processor($processor_arg);
		}

	}


	/**
	 * [_processor description]
	 * @param  boolean $options [description]
	 * @return [type]           [description]
	 */
	public function _processor($options = false)
	{
		if(is_array($options)){
			$options = (object) $options;
		}
		$force_json   = gettype($options) == 'object' && isset($options->force_json)   && $options->force_json   ? true : false;
		$force_stop   = gettype($options) == 'object' && isset($options->force_stop)   && $options->force_stop   ? true : false;
		$not_redirect = gettype($options) == 'object' && isset($options->not_redirect) && $options->not_redirect ? true : false;

		if($not_redirect)
			$this->controller()->redirector = false;


		if(\dash::is_json_accept() || $force_json || \lib\temp::get('api'))
		{
			header('Content-Type: application/json');
			if(isset($this->controller()->redirector) && $this->controller()->redirector)
			{
				$_SESSION['debug'][md5( strtok($this->redirector()->redirect(true), '?') )] = debug::compile();
				debug::msg("redirect", $this->redirector()->redirect(true));
			}
			echo debug::compile(true);
		}
		elseif(!\lib\temp::get('api') && mb_strtolower($_SERVER['REQUEST_METHOD']) == "post")
		{
			$this->redirector();
		}
		if(isset($this->controller()->redirector) && $this->controller()->redirector && !\dash::is_json_accept())
		{
			$_SESSION['debug'][md5( strtok($this->redirector()->redirect(true), '?') )] = debug::compile();
			$this->redirector()->redirect();
		}
		if($force_stop) exit();
	}


	/**
	 * [model description]
	 * @return [type] [description]
	 */
	public function model()
	{
		if(!$this->model)
		{
			if($this->model_name)
			{
				$class_name = $this->model_name;
			}
			else
			{
				// $class_name = get_called_class();
				// $class_name = preg_replace("/\\\controller$/", '\model', $class_name);
				$class_name = $this->findParentClass(__FUNCTION__);
			}

			$object = object();
			$object->controller = $this;
			$this->model = new $class_name($object);
			$this->model->addons($this);
			if(method_exists($this->model, 'config') || array_key_exists('config', $this->model->Methods)){
				$this->model->iconfig();
			}
		}
		return $this->model;
	}


	/**
	 * [view description]
	 * @return [type] [description]
	 */
	public function view()
	{
		if(!$this->view)
		{
			if($this->view_name)
			{
				$class_name = $this->view_name;
			}
			else
			{
				// $class_name = get_called_class();
				// $class_name = preg_replace("/\\\controller$/", '\\\view', $class_name);
				$class_name = $this->findParentClass(__FUNCTION__);
			}

			$object = object();
			$object->controller = $this;
			$this->view = new $class_name($object);
			$this->view->addons($this);
			if(method_exists($this->view, 'config') || array_key_exists('config', $this->view->Methods)){
				$this->view->iconfig();
			}
		}
		return $this->view;
	}


	/**
	 * this function find parent class, if class exist return the name of parent class
	 * else find a parent folder and if class exist use the parent one
	 * @param  [type] $_className the name of class, view or model
	 * @return [type]             return the address of exist class or show error page
	 */
	protected function findParentClass($_className)
	{
		$MyClassName = get_called_class();
		$MyClassName = str_replace("\controller", '\\'.$_className, $MyClassName);

		// if class not exist remove one slash and check it
		if(!class_exists($MyClassName))
		{
			// have more than one back slash for example content\aa\bb\view
			if(substr_count($MyClassName, "\\") > 2)
			{
				$MyClassName = str_replace("\\".$_className, '', $MyClassName);
				$MyClassName = substr($MyClassName, 0, strrpos( $MyClassName, '\\')) . $_className;
			}

			// if after remove one back slash(if exist), class not exist
			if(!class_exists($MyClassName))
			{
				// have more than one back slash for example content\aa\view
				if(substr_count($MyClassName, "\\") == 2)
				{
					$MyClassName = str_replace("\\".$_className, '', $MyClassName);
					$MyClassName = substr($MyClassName, 0, strrpos( $MyClassName, '\\')) . "\home\\" . $_className;
					// var_dump($MyClassName);
				}
				// have more than one back slash for example content\home
				else
				{
					// i dont know this condtion!
					// do nothing!
				}
			}
			if(!class_exists($MyClassName))
			{
				$MyClassName = preg_replace("/\\\[^\\\]*\\\controller$/", '\home\\'.$_className, get_called_class());
			}
		}

		if(!class_exists($MyClassName))
		{
			\lib\error::page($_className . " not found");
		}

		return $MyClassName;
	}


	public function caller(...$_args)
	{
		if(count($_args) < 3)
		{
			error::internal("caller arguments count");
			return;
		}
		elseif((!$_args[0] && !$args[1]) || !$_args[2])
		{
			error::internal("caller arguments invalid");
			return;
		}
		$caller = [$_args[0], $_args[1]];
		$route = new route(false);
		if(!is_array($_args[2]))
		{
			$_args[2] = [$_args[2]];
		}
		$return_route = call_user_func_array(array($route, 'check_route'), $_args[2]);
		if($route->status)
		{
			array_push($caller, new api\args_callback(['method'=> 'caller', 'match' => $route->match]));
			if(!isset($this->caller))
			{
				$this->caller = array();
			}
			array_push($this->caller, $caller);
		}
	}


	/**
	 * [route description]
	 * @return [type] [description]
	 */
	public function route()
	{
		$route = new route(false);
		$return_route = call_user_func_array(array($route, 'check_route'), func_get_args());
		if($route->status == true)
			$this->route_check_true = true;

		return $route;
	}


	/**
	 * [check_api description]
	 * @param  [type]  $name           [description]
	 * @param  [type]  $model_function [description]
	 * @param  boolean $view_function  [description]
	 * @return [type]                  [description]
	 */
	public final function check_api($name, $model_function = null, $view_function = false)
	{
		if(!$this->api)
		{
			$this->api = new api($this);
		}
		return $this->api->$name($model_function, $view_function);
	}


	/**
	 * [__call description]
	 * @param  [type] $_name [description]
	 * @param  [type] $_args [description]
	 * @return [type]       [description]
	 */
	public function __call($_name, $_args)
	{
		if(preg_grep("/^$_name$/", array('get', 'post', 'put', 'delete', 'patch', 'link', 'unlink')))
		{
			array_unshift($_args, $_name);
			return call_user_func_array(array($this, 'check_api'), $_args);
		}
		elseif(method_exists('\lib\router', $_name))
		{
			return call_user_func_array('\lib\router::'.$_name, $_args);
		}elseif(preg_match("#^inject_((after_|before_)?.+)$#Ui", $_name, $inject)){
			return $this->inject($inject[1], $_args);
		}elseif(preg_match("#^i(.*)$#Ui", $_name, $icall)){
			return $this->mvc_inject_finder($_name, $_args, $icall[1]);
		}
		\lib\error::page(get_called_class()."->$_name()");
	}

	/**
	 * [controller description]
	 * @return [type] [description]
	 */
	public function controller()
	{
		return $this;
	}


	/**
	 * [change_model description]
	 * @param  [type] $name [description]
	 * @return [type]       [description]
	 */
	public function change_model($name)
	{
		$this->model_name = $name;
	}


	/**
	 * [change_view description]
	 * @param  [type] $name [description]
	 * @return [type]       [description]
	 */
	public function change_view($name)
	{
		$this->view_name = $name;
	}


	/**
	 * [property description]
	 * @return [type] [description]
	 */
	public function property()
	{
		$args = func_get_args();
		if(count($args) == 1)
		{
			$name = $args[0];
			return $this->$name;
		}
		elseif(count($args) == 2)
		{
			$name = $args[0];
			return $this->$name = $args[1];
		}
	}


	/**
	 * [debug description]
	 * @return [type] [description]
	 */
	public function debug()
	{
		return $this->debug;
	}


	/**
	 * [redirector description]
	 * @return [type] [description]
	 */
	public function redirector()
	{
		if(!isset($this->redirector))	$this->redirector = new \lib\redirector(...func_get_args());
		return $this->redirector;
	}


	/**
	 * clearly return url property for use
	 * @param  [type] $_type type of url you need
	 * @param  [type] $_arg  an argument for pass into some condition
	 * @return [type]        the url value
	 */
	public function url($_type = null, $_arg = null)
	{
		$tmp_result = null;
		$myprefix   = Protocol."://";

		$mytld      = router::get_root_domain('tld');

		switch ($_type)
		{
			// return the base
			case 'base':
			case 'content':
			case 'baseContent':
			case 'baseFull':
			case 'prefix':

				$request_uri  = $_SERVER['REQUEST_URI'];
				$url_language = null;
				if(\lib\utility\location\languages::check(substr($request_uri, 1, 2)))
				{
					$url_language = substr($request_uri, 1, 2);
					$request_uri  = preg_replace("/^\/". $url_language. "\//", "", $request_uri);
				}

				$content = null;
				if(router::get_repository_name() !== 'content')
				{
					preg_match("/^(\/|)([^\/]*)/", $request_uri, $content);
					if(isset($content[2]))
					{
						$content = $content[2];
					}
					else
					{
						$content = null;
					}
				}

				// add subdomain to base
				if(SubDomain)
				{
					$myprefix .= SubDomain. '.';
				}

				if($_type == 'baseContent')
				{
					$return_url = $myprefix.router::get_root_domain();
					if($content)
					{
						$return_url .= '/'. $content;
					}
					return $return_url;
				}
				elseif($_type == 'content')
				{
					if($content)
					{
						return $content;
					}
					return null;
				}
				elseif($_type == 'baseFull')
				{
					$url_language_string = \lib\define::get_current_language_string();
					$return_url = $myprefix.router::get_root_domain(). $url_language_string;
					if($content)
					{
						$return_url .= '/'. $content;
					}
					return $return_url;
				}
				elseif($_type == 'base')
				{
					$url_language_string = \lib\define::get_current_language_string();
					$return_url = $myprefix.router::get_root_domain(). $url_language_string;
					return $return_url;
				}
				elseif($_type == 'prefix')
				{
					$return = $content;
					if($url_language)
					{
						if($url_language != \lib\define::get_language('default'))
						{
							$return = $url_language . '/'. $return;
						}
					}
					return  trim($return, '/');
				}
				break;

			// sub domain like 'account'
			case 'sub':
				return router::get_sub_domain($_arg);
				break;

			case 'path':
				$myUrl = router::get_url($_arg);
				if($_arg == '_')
				{
						// filter url to delete disallow characters
					$myUrl = router::urlfilterer($myUrl);
						// dont use $ in id
					$myUrl = str_replace('$', 'dollar', $myUrl);
				}
				return $myUrl;
				break;

			case 'breadcrumb':
				$myurl      = router::get_url(-1);
				$breadcrumb = array();
				foreach ($myurl as $value)
				{
					$tmp_pos = strpos($value, '=');
					array_push($breadcrumb, $tmp_pos? substr($value, 0, $tmp_pos): $value);
				}
				return $breadcrumb;
				break;

			case 'param':
				return \lib\utility::get(null, $_arg);
				break;

			// domain tld like 'com'
			case 'tld':
				return $mytld;
				break;

			// domain name like 'ermile'
			case 'domain':
				return router::get_root_domain('domain');
				break;

			// domain name except subdomain like 'ermile.com'
			case 'raw':
				return router::get_root_domain('domain').'.'.$mytld;
				break;

			// like raw plus http[s]:// domain name except subdomain like 'http://ermile.com/'
			case 'root':
				$myurl = $myprefix;
				if(defined("subDevelop") && router::get_sub_domain() === constant("subDevelop"))
				{
					$myurl .= constant("subDevelop").".";
				}
				$myurl .= router::get_root_domain();

				return $myurl;
				break;

			// use main protocol and give it from config file if not exist use root url
			// return http or https
			case 'MainProtocol':
				if(defined('MainProtocol') && constant('MainProtocol') && is_string(constant('MainProtocol')))
					return constant('MainProtocol');
				else
					return 'http';
				break;

			// use main site and give it from config file if not exist use root url
			// like raw plus http[s]:// domain name except subdomain like 'http://ermile.com/'
			case 'MainSite':
				if(defined('MainSite') && constant('MainSite') && is_string(constant('MainSite')))
					return constant('MainSite');
				else
					return router::get_root_domain();
				break;

			// base url for user in base tag with http[s]
			case 'baseRaw':
				return router::$base;
				break;

			// full url except get parameter with http[s]
			case 'full':
				$url_language_string = \lib\define::get_current_language_string();
				$myURL = $myprefix. router::get_root_domain(). \lib\utility\safe::safe($_SERVER['REQUEST_URI']);
				return rtrim($myURL, '/');
				break;

			// return module info
			case 'module':
				if($_arg === 'prefix')
					$mymodule	= substr(router::get_url(0), 0, -1);
				elseif($_arg == 'array')
					$mymodule	= router::get_url(-1);
				elseif($_arg == 'cp')
				{
					$mymodule = router::get_url(0);
					switch ($mymodule)
					{
						case 'tags':
						case 'cats':
						$mymodule = 'terms';
						break;
						case 'pages':
						$mymodule = 'posts';
						break;
					}
				}
				else
					$mymodule = router::get_url(0);

				return $mymodule;
				break;

			case 'child':
				$mychild = router::get_url(1);
				if(strrpos($mychild,'=') !==false)
					$mychild = substr($mychild, 0, strrpos($mychild, '='));

				if(!$_arg)
					return $mychild;

				if($mychild=='add')
					return T_('add new');

				if($mychild == 'edit')
					return T_('edit');

				if($mychild == 'delete')
					return T_('delete');

				break;

			// login service and main service with full address
			case 'LoginService':
			case 'account':
				return $myprefix. AccountService. MainTld. '/'. MyAccount;
				break;

			case 'MainService':
				$_arg      = is_array($_arg)  ? $_arg     : array('com', 'dev');

				if (in_array($mytld, $_arg))
					return $myprefix. constant('MainService').'.'.$mytld;
				else
					return $myprefix. constant('MainService'). MainTld;
				break;

			default:
				return null;
				break;
		}
	}
}
?>