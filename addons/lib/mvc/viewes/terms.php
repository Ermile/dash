<?php
namespace lib\mvc\viewes;

trait terms
{
	/**
	 * [view_terms description]
	 * @return [type] [description]
	 */
	function view_terms()
	{
		$this->data->post = array();
		$tmp_result       = $this->model()->get_terms();
		if(!$tmp_result)
		{
			$tmp_result = $this->model()->get_terms_type();
		}

		$tmp_fields =
		[
			'id'            =>'id',
			'term_language' =>'language',
			'term_type'     =>'type',
			'term_title'    =>'title',
			'term_slug'     =>'slug',
			'term_url'      =>'url',
			'term_desc'     =>'content',
			'term_parent'   =>'parent',
			'datemodified' =>'modified'
		];

		foreach ($tmp_fields as $key => $value)
		{
			if(isset($tmp_result[$key]))
			{
				$this->data->post[$value] = html_entity_decode($tmp_result[$key]);
			}
		}
		if(isset($this->data->post['title']))
		{
			$this->data->page['title'] = $this->data->post['title'];
		}
		// get id of term
		$myId = null;
		if(isset($this->data->post['id']))
		{
			$myId = $this->data->post['id'];
		}

		// generate datatable
		$result = $this->model()->sp_postsInTerm($myId);
		$this->data->datatable = $result['result'];
		$this->data->pagenation = $result['pagenation'];
		if(empty($this->data->datatable))
		{
			$tags_list = $this->model()->get_terms_type();
			$this->data->tags_list = $tags_list;
		}

		$this->data->datatable_cats = $this->model()->sp_catsInTerm($myId);
		// switch ($this->data->module)
		// {
		// 	case 'book-index':
		// 		$this->data->datatable_cats = $this->model()->sp_catsInTerm();
		// 		break;
		// }

		// set title of page after add title
		$this->set_title();
	}
}
?>