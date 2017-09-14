<?php
namespace addons\content_api\v1\comment\tools;
use \lib\utility;
use \lib\debug;
use \lib\db\logs;

trait get
{

	/**
	 * Gets the comment.
	 *
	 * @param      <type>  $_args  The arguments
	 *
	 * @return     <type>  The comment.
	 */
	public function get_list_comment($_args = [])
	{
		$default_args =

		[
			'pagenation' => true,
			'admin'      => false,
			'get_meta'   => false,
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
		$where               = [];
		$where['pagenation'] = $_args['pagenation'];
		$search              = utility::request('search');

		$get_args = $this->comment_make_where($_args, $where, $log_meta);

		if(!debug::$status || $get_args === false)
		{
			return false;
		}

		$result          = \lib\db\comments::search($search, $where);

		$temp            = [];

		if(is_array($result))
		{
			foreach ($result as $key => $value)
			{
				$check = $this->ready_comment($value, $_args);
				if($check)
				{
					$temp[] = $check;
				}
			}
		}
		return $temp;
	}


	/**
	 * Gets the comment.
	 *
	 * @param      <type>  $_args  The arguments
	 *
	 * @return     <type>  The comment.
	 */
	public function get_comment($_args = [])
	{
		$default_args =

		[
			'pagenation' => true,
			'admin'      => false,
			'get_meta'   => false,
		];

		if(!is_array($_args))
		{
			$_args = [];
		}

		$_args = array_merge($default_args, $_args);

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
			logs::set('api:comment:comment_id:notfound', $this->user_id, $log_meta);
			debug::error(T_("User not found"), 'comment', 'permission');
			return false;
		}


		$id = utility::request('id');
		$id = utility\shortURL::decode($id);
		if(!$id)
		{
			logs::set('api:comment:id:not:set', $this->user_id, $log_meta);
			debug::error(T_("Id not set"), 'id', 'arguments');
			return false;
		}

		$get_comment = \lib\db\comments::get(['id' => $id, 'limit' => 1]);

		$result = $this->ready_comment($get_comment, $_args);

		return $result;
	}



	/**
	 * ready data of comment to load in api result
	 *
	 * @param      <type>  $_data     The data
	 * @param      array   $_options  The options
	 *
	 * @return     array   ( description_of_the_return_value )
	 */
	public function ready_comment($_data, $_options = [])
	{
		$default_options =
		[
			'get_meta' => false,
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
				case 'post_id':
				case 'user_id':
					$result[$key] = utility\shortURL::encode($value);
					break;

				case 'meta':
					if($_options['get_meta'])
					{
						if(is_string($value) && substr($value, 0,1) === '{')
						{
							$value = json_decode($value, true);
						}
						$result['meta'] = $value;
					}
					else
					{
						continue;
					}
					break;

				case 'visitor_id':
				case 'datemodified':
				case 'datecreated':
					continue;
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