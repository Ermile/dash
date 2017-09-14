<?php
namespace addons\content_cp\home;

class view extends \mvc\view
{
	public function config()
	{
		// $this->data->list             = $this->cpModlueList('all');
		$this->data->bodyclass        = 'fixed unselectable siftal';
		$this->include->css           = false;
		$this->include->js            = false;
		// $this->include->fontawesome   = true;
		// $this->include->datatable     = true;
		// $this->include->chart         = true;
		// $this->include->introjs       = true;
		// $this->include->lightbox      = true;
		// $this->include->editor        = true;
		// $this->include->cp            = true;
		// $this->include->uploader      = true;
		$this->global->js             = [];

		$this->data->display['cp_posts']  = "content_cp/posts/layout.html";
		$this->data->display['cpSample'] = "content_cp/sample/layout.html";


		$this->data->dash['version']    = \lib\dash::getLastVersion();
		$this->data->dash['lastUpdate'] = \lib\dash::getLastUpdate();
		$this->data->dash['langlist']   = ['fa_IR' => 'Persian - فارسی',
											 'en_US' => 'English',
											 'ar_SU' => 'Arabic - العربية'];

		$this->data->modules 		  = $this->controller::$manifest['modules']->get_modules();
		// $this->global->js             = [$this->url->myStatic.'js/highcharts/highcharts.js'];
		// $this->data->page['desc']  = 'salam';
		$mymodule = $this->module();

		$this->data->page['title']    = T_(ucfirst(\lib\router::get_url(' ')));


		// $this->data->cpModule         = $this->cpModule();

		$this->data->dir['right']     = $this->global->direction == 'rtl'? 'left':  'right';
		$this->data->dir['left']      = $this->global->direction == 'rtl'? 'right': 'left';
	}


	public function view_child()
	{
		$mytable                = $this->cpModule('table');
		$mychild                = $this->child();
	}
}
?>