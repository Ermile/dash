<?php
namespace lib\mvc\models;

trait tools
{
	/**
	 * [get_feeds description]
	 * @param  boolean $_forcheck [description]
	 * @return [type]             [description]
	 */
	public function get_feeds($_forcheck = false)
	{
		$start    = \lib\utility::get('start');
		$lenght   = \lib\utility::get('lenght');
		// search in url field if exist return row data
		$qry = $this->sql()->table('posts')
				->field(
					'#language as `lang`',
					'#title as `title`',
					'#content as `desc`',
					'#url as `link`',
					'#publishdate as `date`'
					)
				->where('type', 'post')
				->and('status', 'publish')
				->limit(0, 10);

		$qry = $qry->groupOpen('g_language');
		$qry = $qry->and('language', \lib\define::get_language());
		$qry = $qry->or('language', 'IS', 'NULL');
		$qry = $qry->groupClose('g_language');
		$qry = $qry->select();

		return $qry->allassoc();
	}
}
?>
