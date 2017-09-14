<?php
namespace addons\attachments;
class model
{
	public function get_search_attachments($_args)
	{
		return $this->query_search([
			"search" => $_args->get_search(0),
			"image" => $_args->get_image(0),
			"video" => $_args->get_video(0),
			"audio" => $_args->get_audio(0),
			"other" => $_args->get_other(0),
			'pagnation' => true
			]);
	}

	public function caller_attachments_list($_args)
	{
		return $this->query_search(['pagnation' => false]);
	}
	public function query_search($_parameter = array())
	{
		$search = array_key_exists('search', $_parameter) ? $_parameter['search'] : null;
		$image = array_key_exists('image', $_parameter) ? $_parameter['image'] : null;
		$video = array_key_exists('video', $_parameter) ? $_parameter['video'] : null;
		$audio = array_key_exists('audio', $_parameter) ? $_parameter['audio'] : null;
		$other = array_key_exists('other', $_parameter) ? $_parameter['other'] : null;
		$where = '';
		if($search)
		{
			$where .= "(title LIKE '%$search%' OR content LIKE '%$search%')";
		}

		$_type = ['image', 'audio', 'video'];
		$type = array();
		if($image)
		{
			array_push($type, 'image');
		}
		if($video)
		{
			array_push($type, 'video');
		}
		if($audio)
		{
			array_push($type, 'audio');
		}
		if($other)
		{
			array_push($type, 'other');
		}
		if(count($type) > 0 && count($type) < 4)
		{
			$where .= empty($where) ? '' : " AND ";
			if($other)
			{
				if(count($type) == 1)
				{
					$_type = join("\"' ,'\"", $_type);
					$where .= "json_extract(meta, '$.type') NOT IN ('\"$_type\"')";
				}
				else
				{
					$_type = join("\"' ,'\"", array_diff($_type, $type));
					$type = count($type) > 1 ? "\"" . join("\"' ,'\"", $type) . "\"" : $type[0];
					$where .= "(json_extract(meta, '$.type') IN ('$type')";
					$where .= " OR json_extract(meta, '$.type') NOT IN ('\"$_type\"'))";

				}
			}
			else
			{
				$type = count($type) > 1 ? "\"" . join("\"' ,'\"", $type) . "\"" : $type[0];
				$where .= "json_extract(meta, '$.type') in ('$type')";
			}
		}
		$where .= empty($where) ? '' : " AND ";
		$where .= "type = 'attachment'";

		$length = 5;
		$start = 0;
		if($_parameter['pagnation'])
		{
			list($start, $length) = $this->controller->pagnation_make_limit($length);
		}

		$query = "SELECT SQL_CALC_FOUND_ROWS posts.*, FOUND_ROWS() FROM posts WHERE $where LIMIT $start, $length";
		$result = \lib\db::query($query);

		$query_rows = "SELECT FOUND_ROWS() as rows";
		$result_rows = \lib\db::query($query_rows);
		$rows = $result_rows->fetch_assoc()['rows'];
		if($_parameter['pagnation'])
		{
			$this->controller->pagnation_make($rows);
			$pagnation = $this->controller->pagnation;
		}
		else
		{
			$pagnation['total_pages'] = intval(ceil($rows/ $length));
			$pagnation['current'] = 1;
			$pagnation['next'] = ($pagnation['current']+1 <= $pagnation['total_pages']) ? $pagnation['current']+1 : false;
			$pagnation['prev']= ($pagnation['current']-1 >= 1) ? $pagnation['current']-1 : false;
			$pagnation['count_link']= 7;
			$pagnation['current_url'] = \lib\router::get_class(). '/attachments_data';
			$pagnation['length'] = $length;
		}

		$decode_result = \lib\utility\filter::meta_decode(\lib\db::fetch_all($result));

		return ['data' => $decode_result, 'pagnation' => $pagnation ];
	}
}
?>