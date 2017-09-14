<?php
namespace lib\utility\upload;

trait sql
{

	/**
	 * get count of attachment in post table
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function attachment_count()
	{
		$query = "SELECT COUNT(posts.id) AS 'count' FROM posts WHERE posts.type = 'attachment' ";
		$count = \lib\db::get($query,'count', true);
		return $count;
	}

	/**
	 * check duplocate MD5 of file in database
	 *
	 * @return     boolean  ( description_of_the_return_value )
	 */
	public static function duplicate($_md5)
	{

		$qry_count = "SELECT * FROM posts WHERE posts.slug = '$_md5' LIMIT 1";
		$qry_count = \lib\db::get($qry_count, null, true);
		if($qry_count || !empty($qry_count))
		{
			$meta = [];
			$url  = null;
			$id   = null;

			if(isset($qry_count['meta']) && substr($qry_count['meta'], 0, 1) == '{')
			{
				$meta = json_decode($qry_count['meta'], true);
			}
			if(isset($meta['url']))
			{
				$url = $meta['url'];
			}

			$size = null;
			if(isset($meta['size']))
			{
				$size = $meta['size'];
			}

			if(isset($qry_count['id']))
			{
				$id = (int) $qry_count['id'];
			}
			\lib\temp::set('upload', ["id" =>  $id, 'url' => $url, 'size' => $size]);
			return true;
		}
		return false;
	}
}
?>