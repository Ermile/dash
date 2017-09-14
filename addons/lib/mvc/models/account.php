<?php
namespace lib\mvc\models;
use \lib\debug;
use \lib\utility;

trait account
{
	/**
	 * check referrer and redirect to specefic service
	 * @param [type]  $_id       [description]
	 * @param boolean $_redirect [description]
	 */
	protected function setLogin($_id, $_redirect = true)
	{
		$tmp_domain = null;
		$mycode     = $this->setLoginToken($_id);
		$this->checkMainAccount($_id);
		$myreferer  = utility\cookie::read('referer');
		utility\cookie::delete('referer');

		if($_redirect)
		{
			if($myreferer === 'jibres' || $myreferer === 'talambar')
				$tmp_domain = $myreferer .'.'. $this->url('tld');

			$this->redirector()->set_domain($tmp_domain)->set_url('?ssid='.$mycode);
		}
	}


	/**
	 * Create Token and add to db for cross login
	 * if don't pass a fields name use default data for fill user session
	 * @param [type] $_id [description]
	 */
	protected function setLoginToken($_id)
	{
		// you can change the code way easily at any time!
		$mycode	= md5('^_^'.$_id.'_*Ermile*_'.date('Y-m-d H:i:s').'^_^');
		$qry		= $this->sql()->table('options')
									->set('user_id',      $_id)
									->set('cat',   'cookie_token')
									->set('key',   ClientIP)
									->set('value', $mycode);
		$sql		= $qry->insert();

		$_SESSION['ssid'] = $mycode;

		$this->commit(function()   { });
		$this->rollback(function() { });

		return $mycode;
	}


	/**
	 * Pass a datarow of userdata and field for set in user session
	 * if don't pass a fields name use default data for fill user session
	 * @param [type] $_datarow [description]
	 * @param [type] $_fields  [description]
	 */
	protected function setLoginSession($_datarow, $_fields)
	{
		// set login session
		\lib\db\users::set_login_session($_datarow, $_fields);

		if(isset($_datarow['permission']) && is_numeric($_datarow['permission']))
		{
			$this->setPermissionSession($_datarow['permission']);
		}
	}


	/**
	 * [setPermissionSession description]
	 * @param [type] $_permID [description]
	 */
	public function setPermissionSession($_permID = null, $_return = false)
	{
		// if permission is set for this user,
		// get permission detail and set in permission session
		if(!$_permID && isset($_SESSION['user']['permission']))
		{
			$_permID = $_SESSION['user']['permission'];
		}

		if(is_numeric($_permID))
		{
			if(!$_return)
			{
				$_SESSION['user']['permission'] = $_permID;
			}
			$where =
			[
				'cat'    => 'permissions',
				'key'    => $_permID,
				'post_id'       => null,
				'user_id'       => null,
				'status' => 'enable',
				'limit' 		=> 1,
			];
			$result = \lib\db\options::get($where);

			if($result && !empty($result) && is_array($result))
			{
				if(isset($result['meta']))
				{
					if($_return)
					{
						return $result['meta'];
					}
					else
					{
						$_SESSION['permission'] = $result['meta'];
					}
				}
			}
			else
			{
				// do nothing!
			}

			// $qry = $this->sql()->table('options')
			// 	->where('cat',  'permissions')
			// 	->and('key',    $_permID)
			// 	// ->and('status', 'enable')
			// 	->and('post_id',       '#NULL')
			// 	->and('user_id',       '#NULL')
			// 	->select();

			// if($qry->num() == 1)
			// {
			// 	$qry    = $qry->assoc();
			// 	$myMeta = $qry['meta'];

			// 	if(substr($myMeta, 0,1) == '{')
			// 	{
			// 		$myMeta = json_decode($myMeta, true);
			// 	}

			// 	if($_return)
			// 	{
			// 		return $myMeta;
			// 	}
			// 	else
			// 	{
			// 		$_SESSION['permission'] = $myMeta;
			// 	}
			// }
			// else
			// {
			// 	// do nothing!
			// }
		}
	}


	/**
	 * remove sessions and update ssid record in db for logout user from system
	 * @param  [type] $_status [description]
	 * @return [type]          [description]
	 */
	public function put_logout($_status = null)
	{
		$_ssid = isset($_SESSION['ssid'])? $_SESSION['ssid']: null;

		// unset and destroy session then regenerate it
		session_unset();
		if(session_status() === PHP_SESSION_ACTIVE)
		{
			session_destroy();
			// session_regenerate_id(true);
		}

		if($_ssid === null)
			return null;

		// login user to system and set status to expire
		$qry	= $this->sql()->table ('options')
							->set     ('status', 'disable')
							->where   ('cat',    'cookie_token')
							->and     ('key',    ClientIP)
							->and     ('value',  $_ssid);
		$sql	= $qry->update();


		$this->commit(function() { debug::true(T_("logout successfully")); });
		$this->rollback();
		// debug::true(T_("logout successfully out"));

		// $_SESSION['debug'][md5('http://ermile.dev')] = debug::compile();


		if($_status === 'redirect')
		{
			$this->redirector()->set_domain()->set_url(); //->redirect();
			$this->model()->_processor();
		}
		return null;
	}


	/**
	 * check ssid in get return and after check set login data for user
	 * check user permissions and validate session for disallow unwanted attack
	 * @param  [type] $_type [description]
	 * @return [type]        [description]
	 */
	public function checkMainAccount($_type = null)
	{
		$_type = $_type !== null? $_type: $this->put_ssidStatus();

		switch ($_type)
		{
			// user want to attack to our system! logout from system and show message
			case 'attack':
				$this->put_logout();
				\lib\error::bad(T_("you want hijack us!!?"));
				break;


			// only log out user from system
			case 'logout':
				$this->put_logout('redirect');
				break;


			// if user_id set in options table login user to system
			case is_numeric($_type):
				$mydatarow	= $this->sql()->tableUsers()->whereId($_type)->select()->assoc();
				$myfields = array('id',
										'mobile',
										'email',
										'displayname',
										'meta',
										'status',
										'permission',
										);
				$this->setLoginSession($mydatarow, $myfields);
				break;

			// ssid does not available on this sub domain
			case 'notlogin':
				$this->put_logout('redirect');
				break;

			default:
				break;
		}
	}


	/**
	 * check status of
	 * @return [type] [description]
	 */
	public function put_ssidStatus()
	{
		$myreferer         = isset($_SERVER['HTTP_REFERER'])? $_SERVER['HTTP_REFERER']: null;
		$mytrusturl        = $this->url('account').'/login';
		$is_trustreferer   = $mytrusturl === substr($myreferer, 0, strlen($mytrusturl))? true: false;

		if($is_trustreferer === false)
		{
			$myfrom = utility::get('from');
			$is_trustreferer = $myfrom === 'login'? true: false;
		}

		// set ssid from session
		$myssid = isset($_SESSION['ssid'])? $_SESSION['ssid']: null;

		// if ssid does not exist return null
		if($myssid === null)
			return 'notlogin';



		// ***************************************************** CHECK LOGIN TIME UNDER 1 MIN
		// whereId("<", 10)
		// whereTime('<', 2015)->andTime('>', 2014)
		$tmp_result    = $this->sql()->table('options')
									->where ('cat',    'cookie_token')
									->and   ('key',    ClientIP)
									->and   ('value',  $myssid)
									->and   ('status', 'enable')
									->select()
									->assoc();


		if(!is_array($tmp_result))
			return 'attack';

		// if user passed ssid is correct then update record and set login sessions
		if($tmp_result['status'] === 'enable')
		{
			$qry	= $this->sql()->table('options')
						->set   ('status', 'expire')
						->where ('cat',    'cookie_token')
						->and   ('key',    ClientIP)
						->and   ('value',  $myssid)
						->and   ('status', 'enable');
			$sql	= $qry->update();

			$this->commit();
			$this->rollback();

			return $tmp_result['user_id'];
		}

		// for second page user check or antoher website after login in first one
		if($tmp_result['usermeta_status'] === 'expire')
			return $tmp_result['user_id'];

		// if code is disable with logout then return logout
		// this condition is occur when user logout form main service
		if($tmp_result['usermeta_status'] === 'disable')
			return 'logout';

		return 'attack';
	}
}
?>
