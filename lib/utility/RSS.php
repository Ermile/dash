<?php
namespace lib\utility;

/** Create RSS Feed **/
class RSS
{
	private $rssFeed = null;
	private $siteurl = null;

	public function __construct($_protocol, $_url, $_title, $_desc)
	{
		$this->siteurl = $_protocol.$_url;
		$this->rssFeed  = '<?xml version="1.0" encoding="utf-8"?>'."\n\n";
		$this->rssFeed .= '<rss version="2.0"'
			."\n ".'xmlns:content="http://purl.org/rss/1.0/modules/content/"'
			."\n ".'xmlns:wfw="http://wellformedweb.org/CommentAPI/"'
			."\n ".'xmlns:dc="http://purl.org/dc/elements/1.1/"'
			."\n ".'xmlns:atom="http://www.w3.org/2005/Atom"'
			."\n ".'xmlns:sy="http://purl.org/rss/1.0/modules/syndication/"'
			."\n ".'xmlns:slash="http://purl.org/rss/1.0/modules/slash/"'
			."\n".'>'."\n\n";


		$this->rssFeed .= '  <channel>'."\n\n";
		$this->rssFeed .= '    <title>'.$_title.'</title>'."\n";
		$this->rssFeed .= '    <link>'.$_protocol.$_url.'</link>'."\n";
		$this->rssFeed .= '    <description>'.$_desc.'</description>'."\n";
		$this->rssFeed .= '    <language>'.str_replace('_', '-', \lib\define::get_language('default')).'</language>'."\n";
		$this->rssFeed .= '    <copyright>Copyright (C) '.date("Y").' '.$_url.'</copyright>'."\n";
		$this->rssFeed .= '    <generator>'.'ERMILE!'.'</generator>'."\n";

	}

	public function addItem($_link, $_title, $_desc, $_date)
	{
		// strip_tags(
		$_desc = \lib\utility\excerpt::extractRelevant($_desc, $_title, 500);
		$this->rssFeed .= "\n";
		$this->rssFeed .= '    <item>'."\n";
		$this->rssFeed .= '      <title>' . $_title . '</title>'."\n";
		$this->rssFeed .= '      <link>' . $this->siteurl.'/'.$_link . '</link>'."\n";
		$this->rssFeed .= '      <description>' . $_desc . '</description>'."\n";
		$this->rssFeed .= '      <pubDate>' . date("D, d M Y H:i:s O", strtotime($_date)) . '</pubDate>'."\n";
		$this->rssFeed .= '    </item>'."\n";

	}

	public function create($_title = false)
	{
		header("Content-Type: application/rss+xml; charset=utf-8");

		$this->rssFeed .= '</channel>'."\n";
		$this->rssFeed .= '</rss>'."\n";

		echo $this->rssFeed;
		// echo $this->rssFeed;
	}
}