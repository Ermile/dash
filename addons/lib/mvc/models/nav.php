<?php
namespace lib\mvc\models;

trait nav
{
	/**
	 * return navigations of curent page and get it form posts table
	 * @return [type] datarow
	 */
	public function sp_nav()
	{
		$url = $this->url('path');
		preg_match("#^((book/|)[^\/]*).*#", $url, $like_url);
		$like_url = $like_url[1];
		if($like_url == 'book') return null;

		$current_id = $this->sql()->table('posts')->where('url', $url)
			->and('status', 'publish')->select()->assoc();


		$nav_next = $this->sql()->table('posts')->where('id','>', $current_id['id'])
			->and('url', 'LIKE', "'$like_url%'")
			->and('status', 'publish')->order('id', 'ASC')->limit(0,1)
			->select()->assoc();

		$nav_prev = $this->sql()->table('posts')->where('id','<', $current_id['id'])
			->and('url', 'LIKE', "'$like_url%'")
			->and('status', 'publish')->order('id', 'DESC')->limit(0,1)
			->select()->assoc();


		$result_nav = ['current' => $current_id['id'] ];
		$result_nav['next'] = [ 'url' => $nav_next['url'], 'title' => $nav_next['title'] ];
		$result_nav['prev'] = [ 'url' => $nav_prev['url'], 'title' => $nav_prev['title'] ];

		if($nav_prev || $nav_next)
			return $result_nav;

		return null;
	}
}
?>
