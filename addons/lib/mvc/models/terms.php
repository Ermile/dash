<?php
namespace lib\mvc\models;

trait terms
{
	/**
	 * return the detail of term
	 * @param  boolean $_forcheck [description]
	 * @return [type]             [description]
	 */
	public function get_terms($_forcheck = false, $_url = null)
	{
		if(!$_url)
		{
			$_url = $this->url('path');
		}
		$qry = $this->sql()->tableTerms()->whereTerm_url($_url)->select();

		if($qry->num())
		{
			$datarow             = $qry->allAssoc();
			$current_language    = \lib\define::get_language();
			$check_lanuage_terms = false;

			if(is_array($datarow))
			{
				foreach ($datarow as $key => $value)
				{
					if(array_key_exists('language', $value))
					{
						if(
							$value['language'] == $current_language ||
							is_null($value['language']) ||
							$value['language'] === ''
						  )
						{
							$datarow             = $value; // to load temp type
							$check_lanuage_terms = true;
							break;
						}
					}
				}
			}

			if(!$check_lanuage_terms)
			{
				return false;
			}

			if($_forcheck)
			{
				// set type of terms and remove prefix from it
				$mytype = $datarow['type'];
				if(substr($mytype, 0, 4) === 'cat_')
				{
					$mytype = substr($mytype, 4);
				}
				// set cat of this term and remove prefix
				$cat = $datarow['url'];
				if($mytype === substr($cat, 0, strlen($mytype)))
				{
					$cat = substr($cat, strlen($mytype)+1);
				}

				return
				[
					'table' => 'terms',
					'type' => $mytype,
					'cat'  => $cat,
					'slug' => $datarow['slug'],
				];
			}
			else
			{
				return $datarow;
			}
		}
		return false;
	}


	/**
	 * return list of cats in custom term like cat or tag
	 * @return [type] datarow
	 */
	public function sp_catsInTerm($_id)
	{
		$url = $this->url('path');
		if($_id)
		{
			$datatable = $this->sql()->table('terms')->where('parent', $_id)->select()->allassoc();
		}
		else
		{
			$qry_id = $this->sql()->table('terms')->where('url', $url)->select()->assoc('id');
			$datatable = $this->sql()->table('terms')->where('parent', $qry_id)->select()->allassoc();
		}
		return $datatable;
	}


	/**
	 * return list of posts in custom term like cat or tag
	 * @return [type] datarow
	 */
	public function sp_postsInTerm($_id = null ,$_limit = null)
	{
		$domain = \lib\router::get_domain();
		$sarshomar = false;
		if(preg_match("/sarshomar/", $domain))
		{
			$sarshomar = true;
		}

		$url = $this->url('path');
		if(substr($url, 0, 4) === 'tag/')
		{
			$url = substr($url, 4, (int) $url);
		}

		if(substr($url, 0, 11) === 'book-index/')
		{
			preg_match("#^book-index/([^\/]*)(.*)$#", $url, $m);
			$url_raw = "book/$m[1]";


			if($m[2] !== '')
			{
				$qry = $this->sql()->table('posts')->where('status', 'publish')->and('privacy', 'public')->order('id', 'ASC');
				$qry->join('termusages')->on('termusage_id', '#posts.id')->and('termusage_foreign', '#"posts"');
				$qry->join('terms')->on('id', '#termusages.term_id')->and('url', $url)->groupby('#posts.id');
				if($sarshomar)
				{
					$qry->and('privacy', 'public');
				}
			}
			else
			{
				$parent_id = $this->sql()->table('posts')->where('url', $url_raw)
					->and('status', 'publish')->select()->assoc('id');
				$qry = $this->sql()->table('posts')->where('parent', $parent_id)
					->and('status', 'publish')->order('id', 'ASC');
					if($sarshomar)
					{
						$qry->and('privacy', 'public');
					}
			}


			return $qry->select()->allassoc();
		}

		$qry = $this->sql()->table('posts')->where('status', 'publish')->order('id', 'DESC');
		if($sarshomar)
		{
			$qry->and('privacy', 'public');
		}
		if($_id)
		{
			$qry->join('termusages')->on('termusage_id', '#posts.id')
				// ->and('termusage_foreign', '#"posts"')
				->and('id', $_id)
				->field(false)->groupby('#posts.id')->field(false);
		}
		else
		{
			$qry->join('termusages')->on('termusage_id', '#posts.id')->and('termusage_foreign', '#"posts"')->field(false);
			$qry->join('terms')->on('id', '#termusages.term_id')->and('url', $url)->groupby('#posts.id')->field(false);
		}

		// hasan :|
		$pagenation = [];
		if($_limit){
			$qryCount = clone $qry;
			$qryCount->field("#count(posts.id)");
			$count = $qryCount->select()->num();
			$pagenationPages 	= ceil($count / $_limit);
			$pagenationCurrent 	=  \lib\router::get_storage("pagenation");
			$pagenationNext 	= \lib\router::get_storage("pagenation") +1;
			$pagenationPrev 	= \lib\router::get_storage("pagenation") -1;
			if($pagenationCurrent !== null
				AND ($pagenationCurrent < 1 || $pagenationCurrent > $pagenationPages)){
					\lib\error::page(T_("Does not exist!"));
					return;
			}
			$pagenation = [
			"num_page"		=> $pagenationPages,
			"pages" 		=> intval($pagenationPages),
			"current" 		=> ($pagenationCurrent == 0)? 1 : intval($pagenationCurrent),
			"next" 			=> ($pagenationNext <= $pagenationPages) ? $pagenationNext : false,
			"prev" 			=> ($pagenationPrev >= 1) ? $pagenationPrev : false,
			"count_link"	=> 7,
			"current_url" 	=> \lib\router::get_url(),
			];
			$start = (\lib\router::get_storage("pagenation")) ? (\lib\router::get_storage("pagenation") -1) * $_limit : 0;


			$qry->limit($start, $_limit);
		}
		return ["pagenation" => $pagenation, "result" => $qry->select()->allassoc()];
	}
}
?>
