<?php
namespace lib;

class view
{
	use mvc;

	use view\twigAddons;
	use view\optimize;
	/**
	 * twig options
	 * @var array
	 */
	public $twig = array();
	/**
	 * constructor
	 * @param boolean $object controller
	 */

	public $twig_include_path = array();

	public function __construct($object = false)
	{
		if(!$object) return;

		$this->controller            = $object->controller;

		$this->data                  = new view\data();
		$this->data->url             = object();
		$this->data->include         = object();
		$this->data->global          = object();
		$this->url                   = $this->data->url;
		$this->global                = $this->data->global;
		$this->include               = $this->data->include;

		// default data property
		$this->data->macro['forms']   = 'includes/macro/forms.html';
		// default display value
		$this->data->display['mvc']   = "includes/mvc/display-mvc.html";
		$this->data->display['dash']  = "includes/mvc/display-dash.html";
		$this->data->display['enter'] = "includes/mvc/display-enter.html";


		$myurl = router::get_protocol().'://'.router::get_domain().$_SERVER['REQUEST_URI'];
		if( isset($_SERVER['HTTP_REFERER']) && isset($_SESSION['debug'][md5($_SERVER['HTTP_REFERER'])]) )
			$myurl = $_SERVER['HTTP_REFERER'];


		if(isset($_SESSION['debug'][md5($myurl)]))
		{
			$this->data->debug = $_SESSION['debug'][md5($myurl)];
			// if(isset($_SESSION['debug'][md5($myurl)]['show']))
				unset($_SESSION['debug'][md5($myurl)]);
			// else
				// $_SESSION['debug'][md5($myurl)]['show'] = true;
		}

		array_push($this->twig_include_path, root);

		if(method_exists($this, 'mvc_construct'))
		{
			$this->mvc_construct();
		}
	}


	/**
	 * if controller display property was true run this function for display module
	 */
	public function corridor()
	{
		$this->display();
	}

	public function display()
	{
		$render               = false;
		$this->data->loadMode = 'normal';
		if(\dash::is_Ajax())
		{
			$this->data->display['dash']    = "includes/mvc/display-dash-xhr.html";
			$this->data->display['enter']   = "includes/mvc/display-enter-xhr.html";

			$this->data->display['main']    = "content/main/layout-xhr.html";
			$this->data->display['home']    = "content/home/display-xhr.html";
			$this->data->display['account'] = "content_account/home/layout-xhr.html";
			$this->data->loadMode           = 'ajax';

			if($this->method_exists("pushState"))
			{
				$this->ipushState();
			}
		}
		$module       = preg_replace("/^[^\/]*\/?content/", "content", get_class($this->controller));
		$module       = preg_replace("/^content\\\\|(model|view|controller)$/", "", $module);
		$module       = preg_replace("/[\\\]/", "/", $module);
		$a_repository = preg_split("/[\/]/", router::get_repository(), -1, PREG_SPLIT_NO_EMPTY);
		$repository   = end($a_repository);
		$repository   = $repository ==='content'? $repository.'/': null;
		// $tmpname      = ($this->controller()->display_name)? $this->controller()->display_name : $repository.'/'.$module.'display.html';
		$tmpname      = ($this->controller()->display_name)? $this->controller()->display_name : $repository.$module.'display.html';


		// ************************************************************************************ Twig
		// twig method
		$this->add_twig_filter('fcache');
		$this->add_twig_filter('jdate');
		$this->add_twig_filter('tdate');
		$this->add_twig_filter('sdate');
		$this->add_twig_filter('readableSize');
		$this->add_twig_filter('persian');
		$this->add_twig_filter('fitNumber');
		$this->add_twig_filter('humantime');
		$this->add_twig_filter('exist');
		$this->add_twig_filter('decode');
		$this->add_twig_filter('shortURL');
		$this->add_twig_filter('filemtime');
		$this->add_twig_function('breadcrumb');
		$this->add_twig_function('langList');
		$this->add_twig_function('posts');
		$this->add_twig_function('tags');
		$this->add_twig_function('comments');
		$this->add_twig_function('similar_post');
		$this->add_twig_function('attachment');
		$this->add_twig_function('post_search');
		$this->add_twig_function('perm');

		require_once core.'Twig/lib/Twig/Autoloader.php';
		\Twig_Autoloader::register();
		$loader		  = new \Twig_Loader_Filesystem($this->twig_include_path);
		$array_option = array();
		if($this->controller()->debug())
			$array_option['debug'] = true;

		// twig var_dump filter for dumping value
		$filter_dump       = new \Twig_SimpleFilter('dump', 'var_dump');
		// Delete a key of an array
		$filter_unset_type = new \Twig_SimpleFilter('unset_type', function ($array= null) {
			unset($array['attr']['type']);
			return $array;
		});

		$twig		          = new \Twig_Environment($loader, $array_option);
		$twig->addFilter($filter_dump);						// add a new filter to twig
		$twig->addFilter($filter_unset_type);				// add a new filter to twig
		$twig->addGlobal("session", $_SESSION);


		if($this->controller()->debug()){
			$twig->addExtension(new \Twig_Extension_Debug());

		}else{
			$this->add_twig_function('dump');
		}
		$twig->addExtension(new \Twig_Extensions_Extension_I18n());

		$this->twig_Extentions($twig);
		$template		= $twig->loadTemplate($tmpname);
		if(\dash::is_Ajax())
		{
			$this->data->global->debug = \lib\debug::compile();
			// check apache request header and use if exist
			if(function_exists('apache_request_headers'))
			{
				$req = apache_request_headers();
			}

			$xhr_render                 = $template->render($this->data->_toArray());
			// $this->data->display['mvc'] = $this->data->display['xhr'];
			$md5                        = md5(json_encode($this->data->global).$xhr_render);
			if(isset($req['Cached-MD5']) && $req['Cached-MD5'] == $md5)
			{
				echo json_encode(array("getFromCache" => true));
			}
			else
			{
				// $this->data->global->md5 = $md5;
				echo json_encode($this->data->global);
				echo "\n";
				echo $xhr_render;
			}
		}
		else
		{
			$template->display($this->data->_toArray());
		}
	}
}
?>