<?php
namespace addons\content_api\v1\file;

class model extends \addons\content_api\v1\home\model
{
	use tools\get;
	use tools\link;

	use \addons\content_api\v1\poll\tools\get;
	use \addons\content_api\v1\home\tools\ready;

	/**
	 * Links an upload.
	 *
	 * @param      <type>  $_args  The arguments
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public function link_upload($_args)
	{
		return $this->upload_file();
	}


	/**
	 * Posts an upload.
	 *
	 * @param      <type>  $_args  The arguments
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public function post_upload($_args)
	{
		return $this->upload_file();
	}


	/**
	 * Gets the upload.
	 *
	 * @param      <type>  $_args  The arguments
	 *
	 * @return     <type>  The upload.
	 */
	public function get_upload($_args)
	{
		return "get";
	}
}
?>