<?php
namespace lib\mvc\models;

trait breadcrumb
{
	/**
	 * create breadcrumb and location of it
	 * @return [type] [description]
	 */
	public function breadcrumb()
	{
		$_addr      = $this->url('breadcrumb');
		$breadcrumb = [];

		foreach ($_addr as $key => $value)
		{
			if($key > 0)
				$breadcrumb[] = strtolower("{$breadcrumb[$key-1]}/$value");
			else
				$breadcrumb[] = strtolower("$value");
		}

		$qry         = $this->sql()->table('posts')->where('url', 'IN' , "('".join("' , '", $breadcrumb)."')");
		$qry         = $qry->select();
		$titles = $qry->allassoc('title');
		$post_urls   = $qry->allassoc('url');

		if(count($breadcrumb) != $titles)
		{
			$terms_qry   = $this->sql()->table('terms')->where('url', 'IN' , "('".join("' , '", $breadcrumb)."')");
			$terms_qry   = $terms_qry->select();
			$term_titles = $terms_qry->allassoc('title');
			$term_urls   = $terms_qry->allassoc('url');
		}

		$br = [];
		foreach ($breadcrumb as $key => $value)
		{
			$post_key = array_search($value, $post_urls);
			$term_key = array_search($value, $term_urls);
			if($post_key !== false && isset($titles[$post_key]))
			{
				$br[] = $titles[$post_key];
			}
			elseif($term_key !== false && isset($term_titles[$term_key]))
			{
				$br[] = $term_titles[$term_key];
			}
			else
			{
				$br[] = $_addr[$key];
			}
		}
		return $br;
		$qry = $qry->select()->allassoc();
		if(!$qry)
		{
			return $_addr;
		}
		$br = [];
		foreach ($breadcrumb as $key => $value)
		{
			if ($value != $qry[$key]['url'])
			{
				$br[] = $_addr[$key];
				array_unshift($qry, '');
			}
			else
			{
				$br[] = $qry[$key]['title'];
			}
		}
		return $br;
	}


	/**
	 * get the list of pages
	 * @param  boolean $_select for use in select box
	 * @return [type]           return string or dattable
	 */
	public function sp_books_nav()
	{
		$myUrl         = \lib\router::get_url(-1);
		$result        = ['cats' => null, 'pages' => null];
		$parent_search = null;

		switch (count($myUrl))
		{
			// book/book1
			case 2:
				$myUrl  = $this->url('path');
				$parent_search = 'id';
				break;
			// book/book1/jeld1
			case 3:
				$myUrl  = $this->url('path');
				$parent_search = 'parent';
				break;
			// book/book1/jeld1/page1
			case 4:
				$myUrl = $myUrl[0]. '/'. $myUrl[1]. '/'. $myUrl[2];
				$parent_search = 'parent';
				break;
			// on other conditions return false
			default:
				return false;
		}

		// get id of current page
		$qry = $this->sql()->table('posts')
			->where('type', 'book')
			->and('url', $myUrl)
			->and('status', 'publish')
			->field('id', '#parent as parent')
			->select();
		if($qry->num() != 1)
			return;

		$datarow = $qry->assoc();

		// get list of category or jeld
		$qry = $this->sql()->table('posts')
			->where('type', 'book')
			->and('status', 'publish')
			->and('parent', $datarow[$parent_search])
			->field('id', '#title as title', '#parent as parent', '#post_url as url')
			->select();
		if($qry->num() < 1)
			return;

		$result['cats'] = $qry->allassoc();
		$catsid         = $qry->allassoc('id');
		$catsid         = implode($catsid, ', ');

		// check has page on category or only in
		$qry2 = $this->sql()->table('posts')
			->where('type', 'book')
			->and('status', 'publish')
			->and('parent', 'IN', '('. $catsid. ')')
			->field('id');

		$qry2            = $qry2->select();
		$result['pages'] = $qry2->num();

		return $result;
	}
}
?>
