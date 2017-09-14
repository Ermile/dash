<?php
namespace lib\mvc\viewes;

trait posts
{
	/**
	 * [view_posts description]
	 * @return [type] [description]
	 */
	function view_posts()
	{
		$this->data->post = [];
		$tmp_result       = $this->model()->get_posts();
		$tmp_fields       =
		[
			'id'            =>'id',
			'language'      =>'language',
			'title'         =>'title',
			'slug'          =>'slug',
			'content'       =>'content',
			'meta'          =>'meta',
			'type'          =>'type',
			'url'           =>'url',
			'comment'       =>'comment',
			'count'         =>'count',
			'status'        =>'status',
			'parent'        =>'parent',
			'user_id'       =>'user',
			'publishdate'   =>'publishdate',
			'datemodified' =>'modified'
		];

		foreach ($tmp_fields as $key => $value)
		{
			if(is_array($tmp_result[$key]))
			{
				$this->data->post[$value] = $tmp_result[$key];
			}
			else
			{
				$this->data->post[$value] = html_entity_decode(trim(html_entity_decode($tmp_result[$key])));
			}
		}

		// set page title
		$this->data->page['title'] = $this->data->post['title'];
		$this->data->page['desc'] = \lib\utility\excerpt::extractRelevant($this->data->post['content'], $this->data->page['title']);
		$this->set_title();

		$this->data->nav = $this->model()->sp_nav();
	}
}
?>