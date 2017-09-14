<?php
namespace lib\mvc\controllers;

trait pagnation
{
	public $pagnation = array();
	public function pagnation_config()
	{
		if(preg_match("#^([1-9][0-9]*)$#", \lib\router::get_url_property('page'), $_page))
		{
			$page = intval($_page[1]);
			\lib\router::set_storage('pagnation', $page);
			$this->pagnation['current'] = $page;
			\lib\router::remove_url_property('page');
			if($page == 1)
			{
				$redirect = new \lib\redirector($_SERVER['REQUEST_URI'], true);
				$redirect->url = preg_replace("/\/page=1/", "", $redirect->url);
				$redirect->redirect();
			}
		}
		if(preg_match("#^(\d+)$#", \lib\router::get_url_property('length'), $length))
		{
			$this->pagnation_set('length', intval($length[1]));
			$this->pagnation_set('custom_length', true);
			\lib\router::remove_url_property('length');
		}
		// set get in pagnation url
		if(\lib\utility::get())
		{
			$this->pagnation_set('get_url', '?'. \lib\utility::get());
		}
	}

	public function pagnation_set($_name, $_value)
	{
		return $this->pagnation[$_name] = $_value;
	}

	public function pagnation_make_limit($_default_length)
	{
		$current = $this->pagnation_get('current');
		$current = $current ? $current : 1;
		$length  = $this->pagnation_get('length');
		$length  = $length ? $length : $_default_length;
		$this->pagnation_set('length', $length);
		return [($current - 1) * $length, $length];
	}

	public function pagnation_make($_total_records, $_length = null)
	{
		if(!$_length && !$this->pagnation_get('custom_length') && !$this->pagnation_get('length'))
		{
			\lib\error::internal("PAGENAMTION LENGTH NOTFOUND");
			return;
		}
		else
		{
			$length = $this->pagnation_get('length') ? $this->pagnation_get('length') : intval($_length);
		}
		$total_pages 		= intval(ceil($_total_records / $length));
		$current 			= $this->pagnation_get('current') ? $this->pagnation_get('current') : 1;
		$next 				= $current +1;
		$prev 				= $current -1;
		if($current > $total_pages)
		{
			$this->pagnation_error();
		}
		$this->pagnation_set('total_pages', $total_pages);
		$this->pagnation_set('current', $current);
		$this->pagnation_set('next', ($next <= $total_pages) ? $next : false);
		$this->pagnation_set('prev', ($prev >= 1) ? $prev : false);
		$this->pagnation_set('count_link', 7);
		$this->pagnation_set('total_records', (int) $_total_records);
		$path = \lib\router::get_url()? '/'.\lib\router::get_url(): null;
		$current_url = $this->url('baseFull').$path;
		$this->pagnation_set('current_url', $this->pagnation_get('custom_length') ? $current_url."/length=$length" : $current_url);
		$this->pagnation_set('length', $length);
		\lib\temp::set('pagnation', $this->pagnation_get());

	}

	public function pagnation_get($_name = null)
	{
		if($_name)
		{
			return array_key_exists($_name, $this->pagnation) ? $this->pagnation[$_name] : null;
		}
		else
		{
			return $this->pagnation;
		}
	}

	public function pagnation_error()
	{
		if(!\lib\dash::is_ajax())
		{
			// header("HTTP/1.1 404 NOT FOUND");
		}
	}
}
?>