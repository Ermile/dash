<?php
namespace lib;

class redirector
{
	public $php;
	public $url;
	public function __construct($_url = null, $_php = true)
	{
		$this->cache = new router\cache;
		$this->php = $_php;
		$this->url = $_url;
		if($_url)
		{
			// do nothing
		}
		$this->cache->set_cache['url_string'] = $_url ? $_url : $this->get_real_url();
		$this->cache->set_cache['url_array']  = explode("/", $_url ? $_url : $this->get_real_url());
	}
	public function html()
	{
		$this->php = false;
		return $this;
	}
	public function redirect($_return = false)
	{
		if(isset($this->url) && $this->url)
		{
			$newLocation = $this->url;
		}
		else
		{
			$newLocation = $this->get_protocol() . '://';
			$newLocation .= $this->get_real_domain() . '/';
			$newLocation .= $this->get_url();
		}

		if($_return)
			return $newLocation;

		if(\lib\dash::is_json_accept() || \lib\temp::get('api') || \lib\dash::is_ajax())
		{
			header('Content-Type: application/json');
			debug::msg('redirect', $newLocation);
			echo json_encode(debug::compile());
			exit();
		}

		if($this->php)
		{
			if (!headers_sent())
			{
				header('Pragma: no-cache');
				header("HTTP/1.1 301 Moved Permanently");
				header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
				header('Location: '.$newLocation);
			}
		}
		else
		{
			echo '<html><head>';
			echo '<meta http-equiv="refresh" content="2; URL='.$newLocation.'">';
			echo '<meta charset="utf-8">';
			echo '<style type="text/css">body {background-color: #ffffff;background-attachment: fixed;background-repeat: repeat;font-size:12px;font-family:lato;text-align:center;line-height:14px;text-transform:none;color:#E0E0E0;}#main{position:fixed;height:494px;width:650px;top:50%;margin-top:-100px;left:50%;margin-left:-325px;font-size:50px;line-height:59px;}a{display:block;text-decoration:none;color:#a3a3a3;-webkit-transition: all 0.4s linear;-moz-transition: all 0.4s linear;transition: all 0.4s linear;}a:link, a:active, a:visited{color: #a3a3a3;padding-bottom:5px;border-bottom:2px solid #a3a3a3;}a:hover{color: #a3a3a3;}.smaller{font-size:20px;text-transform:lowercase;}</style>';
			echo '</head></body>';
			echo ' <div id="main">';
			echo '  <a href="'.$newLocation.'">REDIRECTING YOU</a>';
			echo '  <span class="smaller">'. strtok($newLocation, '?') .'</span><br>';
			echo ' </div>';
			echo '</body></html>';
		}
		exit();
	}

	public function __call($_name, $_args)
	{
		$ret = call_user_func_array(array($this->cache, $_name), $_args);
		if($ret === null)
			return $this;
		else
			return $ret;
	}
}
?>