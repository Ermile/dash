<?php
namespace dash\utility;

class pagination
{
	private static $have_pages = false;
	public static $detail      = [];


	/**
	 * save every thing in temp to get every where
	 *
	 * @param      <type>  $_key    The key
	 * @param      <type>  $_value  The value
	 *
	 * @return     <type>  ( description_of_the_return_value )
	 */
	public static function detail($_key = null, $_value = null)
	{
		if(isset($_value))
		{
			self::$detail[$_key] = $_value;
		}
		else
		{
			if($_key)
			{
				if(array_key_exists($_key, self::$detail))
				{
					return self::$detail[$_key];
				}
				else
				{
					return null;
				}
			}
			else
			{
				return self::$detail;
			}

		}
	}

	public static function api_detail()
	{
		if(!self::detail('total_rows'))
		{
			return null;
		}

		$detail = self::detail();
		if(!$detail)
		{
			return null;
		}


		$result = [];

		if(isset($detail['desc']))
		{
			$result['desc'] = $detail['desc'];
		}

        if(isset($detail['page']))
        {
        	$result['page'] = $detail['page'];
        }

        if(isset($detail['total_page']))
        {
        	$result['total_page'] = $detail['total_page'];
        }

        if(isset($detail['limit']))
        {
        	$result['limit'] = $detail['limit'];
        }

        if(isset($detail['total_rows']))
        {
        	$result['total_rows'] = $detail['total_rows'];
        }


		return $result;
	}


	private static function destroy()
	{
		self::$have_pages = false;
		self::$detail     = [];
	}


	/**
	 * Gets the query limit.
	 *
	 * @param      <type>   $_total_rows  The total rows
	 * @param      integer  $_page        The page
	 * @param      integer  $_limit       The limit
	 *
	 * @return     <type>   The query limit.
	 */
	public static function init($_total_rows, $_limit = 10)
	{
		self::destroy();

		self::$have_pages = true;
		$page             = \dash\request::get('page');
		$url_get_length   = \dash\request::get('length');

		$page             = $page && ctype_digit($page) ? $page : 1;

		if(mb_strlen($page) > 12)
		{
			$page = 1;
		}

		$page             = intval($page) > 0 ? intval($page) : 1;
		$_total_rows      = intval($_total_rows);

		if($url_get_length && ctype_digit($url_get_length) && intval($url_get_length) <= 1000)
		{
			$limit = intval($url_get_length);
		}
		else
		{
			$limit = intval($_limit);
		}

		$limit = $limit ? $limit : 10;

		if($page > 0)
		{
			$start_limit = $limit * ($page - 1);
		}
		else
		{
			$start_limit = 0;
		}

		$end_limit = $limit;

		$total_page = ceil($_total_rows / $limit);

		// save some detail
		self::detail('start_limit', $start_limit);
		self::detail('end_limit', $end_limit);
		self::detail('page', $page);
		self::detail('total_page', $total_page);
		self::detail('limit', $limit);
		self::detail('total_rows', $_total_rows);

		// set from and to
		$record_from = $start_limit + 1;
		$record_to = $start_limit + $end_limit;
		if($record_to > $_total_rows)
		{
			$record_to = $_total_rows;
		}

		self::detail('from', $record_from);
		self::detail('to', $record_to);

		// create desc of pagination
		// page 3 of 20 - Show record 21 to 30 from 200
		$desc = T_("page :current of :total", ['current' => $page, 'total' => $total_page]);
		$desc .= ' - ';
		$desc .= T_("Show record :min to :max of :total", ['min' => $record_from, 'max' => $record_to, 'total' => $_total_rows]);
		$desc = \dash\utility\human::fitNumber($desc);
		self::detail('desc', $desc);

		return [$start_limit, $end_limit];
	}


	private static function make($_type = null, $_page_number = null)
	{

		$page   = $_page_number;
		$text   = $_page_number;
		$title  = null;
		$class  = null;
		$link   = true;

		switch ($_type)
		{
			case 'first':
				$class  = 'first';
				break;

			case 'spliter':
				$link   = false;
				$page   = null;
				$text   = '...';
				$class  = 'spliter';
				break;

			case 'end':
				$class  = 'end';
				break;

			case 'current':
				$link   = false;
				$class  = 'active';
				break;

			case 'next':
				if(\dash\language::dir() === 'ltr')
				{
					$text = '<span class="sf-chevron-right"></span>';
				}
				else
				{
					$text = '<span class="sf-chevron-left"></span>';
				}
				$class  = 'next s0';
				break;

			case 'prev':
				if(\dash\language::dir() === 'ltr')
				{
					$text = '<span class="sf-chevron-left"></span>';
				}
				else
				{
					$text = '<span class="sf-chevron-right"></span>';
				}
				$class  = 'prev s0';
				break;

		}

		$result =
		[
			'page'   => $page,
			'link'	 => $link,
			'text'   => $text ? \dash\utility\human::fitNumber($text) : null,
			'title'  => $title,
			'class'  => $class,
		];
		return $result;
	}


	public static function page_number()
	{
		if(!self::$have_pages)
		{
			return null;
		}

		$current    = intval(self::detail('page'));
		$limit      = intval(self::detail('limit'));
		$total_rows = intval(self::detail('total_rows'));
		$limit      = $limit ? $limit : 1;
		$first      = ($current - 1) >= 1  ? ($current - 1) : 1;
		$total_page = intval(self::detail('total_page'));

		if(\dash\option::config('pagination_count_link') && ctype_digit(\dash\option::config('pagination_count_link')))
		{
			$count_link = intval(\dash\option::config('pagination_count_link'));
		}
		else
		{
			$count_link = 5;
		}

		$result = [];

		if($total_page <= 1)
		{
			// no pagination needed
		}
		elseif($total_page === 2)
		{
			if($current === 1)
			{
				$result[] = self::make('current', $current);
				$result[] = self::make(null, 2);
			}
			elseif ($current === 2)
			{
				$result[] = self::make(null, 1);
				$result[] = self::make('current', $current);
			}
		}
		else
		{
			$count_link_fill = 0;
			$sb              = [];
			$sa              = [];
			$i               = 0;
			$pages           = [];

			while ($count_link_fill < $count_link)
			{
				$i++;

				if($i > $count_link)
				{
					break;
				}

				if($current - $i + 1 > 0)
				{
					if($current - $i +1 !== $current)
					{
						array_push($pages, $current - $i + 1);
						array_push($sb, $current - $i + 1);
					}
					$count_link_fill++;
				}

				if($count_link_fill < $count_link)
				{
					if($current + $i <= $total_page)
					{
						array_push($pages, $current + $i);
						array_push($sa, $current + $i );
						$count_link_fill++;
					}
				}
			}

			asort($pages);

			$sb = array_reverse($sb);

			if($current !== 1)
			{
				$result[] = self::make('prev', $current -1);
			}

			if(current($pages) - 1 == 1)
			{
				if(in_array(1, $pages) || $current === 1)
				{
					// needless to make first page
				}
				else
				{
					$result[] = self::make(null, 1);
				}
			}
			elseif(current($pages) - 1 >= 2)
			{
				$result[] = self::make('first', 1);
				$result[] = self::make('spliter');
			}

			foreach ($sb as $key => $value)
			{
				$result[] = self::make(null, $value);
			}

			$result[] = self::make('current', $current);

			foreach ($sa as $key => $value)
			{
				$result[] = self::make(null, $value);
			}

			if(end($pages) + 1 <= $total_page)
			{
				if(in_array($total_page, $pages) || $current === $total_page)
				{
					// needless to make end page
				}
				else
				{
					if(end($pages) + 1 < $total_page)
					{
						$result[] = self::make('spliter');
					}

					$result[] = self::make('end', $total_page);
				}
			}

			if($current !== $total_page)
			{
				$result[] = self::make('next', $current + 1);
			}
		}

		$this_link = \dash\url::current();
		$get       = \dash\request::get();
		unset($get['page']);

		foreach ($result as $key => $value)
		{
			if(isset($value['link']) && $value['link'])
			{
				$temp_get             = $get;
				$temp_get['page']     = $value['page'];
				$temp_link            = $this_link . '?'. http_build_query($temp_get);
				$result[$key]['link'] = $temp_link;
			}
			$result[$key]['total_rows'] = self::detail('total_rows');
		}

		return $result;
	}

	public static function init_np($_limit = 10)
	{
		self::destroy();

		self::$have_pages = true;
		$page             = \dash\request::get('page');
		$url_get_length   = \dash\request::get('length');

		$page             = $page && ctype_digit($page) ? $page : 1;

		if(mb_strlen($page) > 12)
		{
			$page = 1;
		}

		$page             = intval($page) > 0 ? intval($page) : 1;

		$limit = intval($_limit);


		if($page > 0)
		{
			$start_limit = $limit * ($page - 1);
		}
		else
		{
			$start_limit = 0;
		}

		$end_limit = $limit;

		self::detail('page', $page);

		return [$end_limit, $start_limit];
	}


	public static function np($_result = null, $_filter = false)
	{
		$current    = intval(self::detail('page'));

		$result = [];
		if($current -1 > 0)
		{
			$result[] = self::make('prev', $current -1);
		}

		if($_result || $current -1 > 0)
		{
			$result[] = self::make('current', $current);

		}


		if($_result)
		{
			$result[] = self::make('next', $current +1);
		}

		$this_link = \dash\url::current();
		$get       = \dash\request::get();
		unset($get['page']);

		foreach ($result as $key => $value)
		{
			if(isset($value['link']) && $value['link'])
			{
				$temp_get             = $get;
				$temp_get['page']     = $value['page'];
				$temp_link            = $this_link . '?'. http_build_query($temp_get);
				$result[$key]['link'] = $temp_link;
			}
		}

		\dash\data::paginationnp($result);

		return $result;
	}
}
?>
