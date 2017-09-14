<?php
namespace addons\content_api\v1\user\tools;
use \lib\utility;
use \lib\debug;
use \lib\db\logs;

trait get
{

	public $remote_user         = false;
	public $rule                = null;
	public $show_another_status = false;
	public $team_privacy        = 'private';

	/**
	 * Gets the user.
	 *
	 * @param      <type>  $_args  The arguments
	 *
	 * @return     <type>  The user.
	 */
	public function get_list_user($_args = [])
	{
		$default_args =

		[
			'pagenation' => true,
			'admin'  	 => false,
		];

		if(!is_array($_args))
		{
			$_args = [];
		}

		$_args = array_merge($default_args, $_args);

		$log_meta =
		[
			'data' => null,
			'meta' =>
			[
				'input' => utility::request(),
			]
		];

		if(!$this->user_id)
		{
			return false;
		}
		$where           = [];
		$search          = utility::request('search');

		$get_args = $this->user_make_where($_args, $where, $log_meta);

		if(!debug::$status || $get_args === false)
		{
			return false;
		}

		$result          = \lib\db\users::search($search, $where);

		$temp            = [];

		if(is_array($result))
		{
			foreach ($result as $key => $value)
			{
				$check = $this->ready_user($value);
				if($check)
				{
					$temp[] = $check;
				}
			}
		}
		return $temp;
	}


	/**
	 * Gets the user.
	 *
	 * @param      <type>  $_args  The arguments
	 *
	 * @return     <type>  The user.
	 */
	public function get_user($_args = [])
	{
		debug::title(T_("Operation Faild"));

		$log_meta =
		[
			'data' => null,
			'meta' =>
			[
				'input' => utility::request(),
			]
		];

		if(!$this->user_id)
		{
			logs::set('api:user:user_id:notfound', $this->user_id, $log_meta);
			debug::error(T_("User not found"), 'user', 'permission');
			return false;
		}


		$id = utility::request('id');
		$id = utility\shortURL::decode($id);
		if(!$id)
		{
			logs::set('api:user:id:not:set', $this->user_id, $log_meta);
			debug::error(T_("Id not set"), 'id', 'arguments');
			return false;
		}

		$get_user = \lib\db\users::get(['id' => $id, 'limit' => 1]);

		$result = $this->ready_user($get_user);

		return $result;
	}



	/**
	 * ready data of user to load in api result
	 *
	 * @param      <type>  $_data     The data
	 * @param      array   $_options  The options
	 *
	 * @return     array   ( description_of_the_return_value )
	 */
	public function ready_user($_data, $_options = [])
	{
		$default_options =
		[

		];

		if(!is_array($_options))
		{
			$_options = [];
		}

		$_options = array_merge($default_options, $_options);


		$result = [];

		foreach ($_data as $key => $value)
		{
			switch ($key)
			{
				case 'id':
					$result[$key] = utility\shortURL::encode($value);
					break;
				default:
					$result[$key] = $value;
					break;
			}

		}

		krsort($result);
		return $result;
	}
}
?>