<?php
namespace lib\db;

/** codes managing **/
class codes
{

	/**
	 * set new code
	 *
	 * @param      <type>  $_args  The arguments
	 */
	public static function set($_args)
	{
		$default_args =
		[
			'code'    => null,
			'type'    => null,
			'related' => null,
			'id'      => null,
			'creator' => null,
		];

		if(!is_array($_args))
		{
			$_args = [];
		}

		$_args = array_merge($default_args, $_args);

		if(!$_args['code'] || !$_args['related'] || !$_args['id'] || !$_args['type'])
		{
			return false;
		}

		if(mb_strlen($_args['code']) > 90)
		{
			return false;
		}

		$check_exist_code =
		[
			'type'  => 'code',
			'slug'  => "(SELECT '$_args[code]')",
			'limit' => 1,
		];

		$check_exist_code = \lib\db\terms::get($check_exist_code);

		if(isset($check_exist_code['id']))
		{
			$term_id = $check_exist_code['id'];
		}
		else
		{

			$insert_term =
			[
				'type'    => 'code',
				'slug'    => "(SELECT '$_args[code]')",
				'title'   => $_args['type'],
				'user_id' => $_args['creator'],
				'status'  => 'enable',
			];
			$term_id = \lib\db\terms::insert($insert_term);
		}

		if(!$term_id)
		{
			return false;
		}

		$check_exist_usage =
		[
			// 'term_id'    => $term_id,
			'related'    => $_args['related'],
			'related_id' => $_args['id'],
			'type'       => $_args['type'],
			'status'     => 'enable',
			'limit'      => 1,
		];

		$check_exist_usage = \lib\db\termusages::get($check_exist_usage);

		if(!$check_exist_usage)
		{
			$insert_termusage =
			[
				'term_id'    => $term_id,
				'related'    => $_args['related'],
				'related_id' => $_args['id'],
				'type'       => $_args['type'],
				'status'     => 'enable',
			];
			\lib\db\termusages::insert($insert_termusage);
		}
		elseif(isset($check_exist_usage['term_id']) && isset($check_exist_usage['status']))
		{
			if(intval($check_exist_usage['term_id']) !== intval($term_id))
			{
				// term id is different
				// need to disable old and insert new\
				$where =
				[
					'term_id'    => $check_exist_usage['term_id'],
					'type'       => $_args['type'],
					'related'    => $_args['related'],
					'related_id' => $_args['id'],
					'status'     => 'enable',
				];

				\lib\db\termusages::delete($where);

				$insert_termusage =
				[
					'term_id'    => $term_id,
					'related'    => $_args['related'],
					'related_id' => $_args['id'],
					'type'       => $_args['type'],
					'status'     => 'enable',
				];
				\lib\db\termusages::insert($insert_termusage);
			}
		}
		else
		{
			return false;
		}

		return true;
	}


	/**
	 * get if exist code
	 *
	 * @param      <type>  $_args  The arguments
	 */
	public static function get($_args)
	{
		$default_args =
		[
			'type'         => null,
			'related'      => null,
			'id'           => null,
			'status'       => null,
			'multi_record' => false,
		];

		if(!is_array($_args))
		{
			$_args = [];
		}

		$_args = array_merge($default_args, $_args);

		if(!$_args['related'] || !$_args['id'])
		{
			return false;
		}

		// check status if need
		$status_query = null;

		if($_args['status'] && is_string($_args['status']))
		{
			$status_query = " AND termusages.status = '$_args[status]' ";
		}

		// check type if need
		$type_query = null;

		if($_args['type'] && is_string($_args['type']))
		{
			$type_query = " AND termusages.type = '$_args[type]' ";
		}

		// multi or single record
		if($_args['multi_record'])
		{
			$id_query = " termusages.related_id IN ($_args[id]) ";
		}
		else
		{
			$id_query = " termusages.related_id = $_args[id] ";
		}

		$query =
		"
			SELECT
				termusages.*,
				terms.*,
				terms.status AS `term_status`,
				terms.title AS `term_title`,
				termusages.type AS `termusage_type`
			FROM
				termusages
			INNER JOIN terms ON terms.id = termusages.term_id
			WHERE
				termusages.related = '$_args[related]' AND
				$id_query
				$type_query
				$status_query
		";

		return \lib\db::get($query);
	}


	/**
	 * get if exist code
	 *
	 * @param      <type>  $_args  The arguments
	 */
	public static function remove($_args)
	{
		$default_args =
		[
			'type'    => null,
			'related' => null,
			'id'      => null,
		];

		if(!is_array($_args))
		{
			$_args = [];
		}

		$_args = array_merge($default_args, $_args);


		if(!$_args['related'] || !$_args['id'] || !is_numeric($_args['id']) || !$_args['type'])
		{
			return false;
		}

		$where =
		[
			'type'       => $_args['type'],
			'related'    => $_args['related'],
			'related_id' => $_args['id'],
			'status'     => 'enable',
		];

		\lib\db\termusages::delete($where);
	}


	/**
	 * Gets the mulit codes.
	 *
	 * @param      <type>  $_args  The arguments
	 */
	public static function get_multi_codes($_args)
	{

		$default_args =
		[
			'type'    => null,
			'status'  => null,
			'related' => null,
			'ids'     => null,
		];

		if(!is_array($_args))
		{
			$_args = [];
		}

		$_args = array_merge($default_args, $_args);

		if(!$_args['related'] || !$_args['ids'] || !is_array($_args['ids']))
		{
			return false;
		}

		$get =
		[
			'status'       => $_args['status'],
			'related'      => $_args['related'],
			'id'           => implode(',', $_args['ids']),
			'multi_record' => true,
			'type'         => $_args['type'],
		];

		return self::get($get);
	}
}
?>