<?php
namespace addons\content_api\v1\file\tools;

trait check
{
	/**
	 * user uploaded size
	 *
	 * @param      <type>  $_user_id  The user identifier
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	private static function uploaded($_user_id)
	{
		$where =
		[
			'user_id'    => $_user_id,
			'post_id'    => null,
			'cat' => 'user_detail_'. $_user_id,
			'key' => 'user_uploaded_size',
			'limit'      => 1,
		];
		$result =  \lib\db\options::get($where);
		if(isset($result['value']))
		{
			return (int) $result['value'];
		}
		return 0;
	}


	/**
	 * user vip size
	 *
	 * @param      <type>  $_user_id  The user identifier
	 */
	private static function vip_size($_user_id)
	{
		$where =
		[
			'user_id'    => $_user_id,
			'post_id'    => null,
			'cat' => 'user_vip_size_'. $_user_id,
			'key' => 'user_uploaded_size',
			'limit'      => 1,
		];
		$result =  \lib\db\options::get($where);
		if(isset($result['value']))
		{
			return (int) $result['value'];
		}
		return false;
	}


	/**
	 * check remaining user size
	 */
	public static function remaining($_user_id)
	{
		$MB = 1 * 1000000; // 1 MB
		$default_user_size = $MB / 2; // 0.5 MB

		\lib\permission::$user_id = $_user_id;

		if(\lib\permission::access('upload_1000_mb'))
		{
			$default_user_size = 1000 * $MB; // 1 TB
		}
		elseif(\lib\permission::access('upload_100_mb'))
		{
			$default_user_size = 100 * $MB; // 100 MB
		}
		elseif(\lib\permission::access('upload_10_mb'))
		{
			$default_user_size = 10 * $MB; // 10 MB
		}

		$uploaded          = self::uploaded($_user_id);
		$vip_size          = self::vip_size($_user_id);
		if(is_int($vip_size))
		{
			$default_user_size = $vip_size;
		}
		return $default_user_size;
		// return $default_user_size - $uploaded;
	}


	/**
	 * plus the user size
	 */
	public static function user_size_plus($_user_id, $_size)
	{
		$where =
		[
			'user_id'      => $_user_id,
			'post_id'      => null,
			'cat'   => 'user_detail_'. $_user_id,
			'key'   => 'user_uploaded_size',
			'value' => 'user_uploaded_size',
		];
		\lib\db\options::plus($where, (int) $_size);
	}

}
?>