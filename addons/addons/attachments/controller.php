<?php
namespace addons\attachments;
class controller{
	function before__route()
	{
		if(!$this->access('cp:attachments:view'))
		{
			return;
		}
		$this->get('search_attachments', 'search_attachments')->ALL([
			'url'	   => [\lib\router::get_class(), 'attachments_data'],
			'property' => [
				'attachments_search_q'     => ['/^.*$/', true, 'search'],
				'attachments_search_image' => ['/^(on|off)$/', true, 'image'],
				'attachments_search_video' => ['/^(on|off)$/', true, 'video'],
				'attachments_search_audio' => ['/^(on|off)$/', true, 'audio'],
				'attachments_search_other' => ['/^(on|off)$/', true, 'other']
			],
			'max'		=> 1
			], function()
			{
				$this->on_search_attachments = true;
			});
	}
	function after__route(){
		if(!$this->access('cp:attachments:view') || isset($this->on_search_attachments)){
			return;
		}
		$this->caller('attachments_list', "attachments_list", "/.*/");
	}
}
?>