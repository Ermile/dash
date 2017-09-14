<?php
namespace lib\mvc\controllers;

trait login
{
	/**
	 * Return login status without parameter
	 * If you pass the name as all return all of user session
	 * If you pass specefic user data field name return it
	 * @param  [type] $_name [description]
	 * @return [type]        [description]
	 */
	public function login($_name = null)
	{
		if(isset($_name))
		{
			if($_name === "all")
			{
				return isset($_SESSION['user'])? $_SESSION['user']: null;
			}
			else
			{
				return isset($_SESSION['user'][$_name])? $_SESSION['user'][$_name]: null;
			}
		}

		if(isset($_SESSION['user']['id']))
		{
			return true;
		}
		else
		{
			return false;
		}
	}


	/**
	* check is set remember of this user and login by this
	*
	*/
	public function check_remeber_login()
	{
		$url = \lib\utility\safe::safe($_SERVER['REQUEST_URI']);

		// check if have cookie set login by remember
		if(!$this->login())
		{
			\addons\content_enter\main\tools\login::login_by_remember();
		}

		/**
		 * if the user user 'en' language of site
		 * and her country is "IR"
		 * and no referer to this page
		 * and no cookie set from this site
		 * redirect to 'fa' page
		 * WARNING:
		 * this function work when the default lanuage of site is 'en'
		 * if the default language if 'fa'
		 * and the user work by 'en' site
		 * this function redirect to tj.com/fa/en
		 * and then redirect to tj.com/en
		 * so no change to user interface ;)
		 */
		if(\lib\define::get_language() != 'fa')
		{
			if(isset($_SERVER['HTTP_CF_IPCOUNTRY']) && mb_strtoupper($_SERVER['HTTP_CF_IPCOUNTRY']) === 'IR')
			{
				$refrer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : null;

				if(!$refrer && !$_SESSION)
				{
					$root    = $this->url('root');
					$full    = $this->url('full');
					$new_url = str_replace($root, $root. '/fa', $full);
					$this->redirector($new_url)->redirect();
				}
			}
		}
		$_SESSION['user_country_ir_redirect_fa'] = true;
	}

}
?>