<?php
namespace addons\attachments;
class view{
	function pushState()
	{
		if(!isset($this->controller->on_search_attachments))
		{
			return;
		}
		$this->controller->display_name= 'addons/attachments/files-list.html';
		unset($this->data->global->title);
	}

	function view_search_attachments($_args)
	{
		$this->attachments_list($_args->api_callback);
	}

	function caller_attachments_list($_args)
	{
		$this->attachments_list($_args->callback);
	}

	function attachments_list($_lists)
	{
		$this->data->attachments          = $_lists['data'];
		$this->data->attachment_pagnation = $_lists['pagnation'];
	}
}
?>