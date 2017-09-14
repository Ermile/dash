<?php
namespace lib\utility;

/** Excerpt input text and return shoet word **/
class excerpt
{
	// find the locations of each of the words
	// Nothing exciting here. The array_unique is required
	// unless you decide to make the words unique before passing in
	private static function _extractLocations($_words, $_fulltext)
	{
		$locations = array();
		if(!is_array($_words))
		{
			if(mb_strlen($_words)>7)
				$_words = explode(' ', $_words);
			else
				$_words = [$_words];
		}

		foreach($_words as $word)
		{
			$wordlen = mb_strlen($word);
			$loc = stripos($_fulltext, $word);
			while($loc !== FALSE) {
				$locations[] = $loc;
				$loc = stripos($_fulltext, $word, $loc + $wordlen);
			}
		}
		$locations = array_unique($locations);
		sort($locations);


		return $locations;
	}

	// Work out which is the most relevant portion to display
	// This is done by looping over each match and finding the smallest distance between two found
	// strings. The idea being that the closer the terms are the better match the snippet would be.
	// When checking for matches we only change the location if there is a better match.
	// The only exception is where we have only two matches in which case we just take the
	// first as will be equally distant.
	private static function _determineSnipLocation($_locations, $_prevcount)
	{
		// If we only have 1 match we dont actually do the for loop so set to the first
		$startpos = isset($_locations[0])? $_locations[0]: 0;
		$loccount = count($_locations);
		$smallestdiff = PHP_INT_MAX;

		// If we only have 2 skip as its probably equally relevant
		if(count($_locations) > 2) {
			// skip the first as we check 1 behind
			for($i=1; $i < $loccount; $i++) {
				if($i == $loccount-1) { // at the end
					$diff = $_locations[$i] - $_locations[$i-1];
				}
				else {
					$diff = $_locations[$i+1] - $_locations[$i];
				}

				if($smallestdiff > $diff) {
					$smallestdiff = $diff;
					$startpos = $_locations[$i];
				}
			}
		}

		$startpos = $startpos > $_prevcount ? $startpos - $_prevcount : 0;
		return $startpos;
	}

	// 1/6 ratio on _prevcount tends to work pretty well and puts the terms
	// in the middle of the extract
	public static function extractRelevant($_fulltext, $_words=null, $_rellength=300, $_prevcount=50, $_indicator='...', $_strip=true)
	{
		if($_strip)
		{
			$_fulltext = str_replace('<br>', "\n", $_fulltext);
			$_fulltext = strip_tags($_fulltext);
			$_fulltext = str_replace(array("\n", "\r", "\t"), ' ', $_fulltext);
			$_fulltext = preg_replace('/\s+/', ' ', $_fulltext);
		}

		$textlength = mb_strlen($_fulltext);
		if($textlength <= $_rellength) {
			return $_fulltext;
		}

		$locations = self::_extractLocations($_words, $_fulltext);
		$startpos  = self::_determineSnipLocation($locations,$_prevcount);

		// if we are going to snip too much...
		if($textlength-$startpos < $_rellength) {
			$startpos = $startpos - ($textlength-$startpos)/2;
		}

		$reltext = substr($_fulltext, $startpos, $_rellength);

		// check to ensure we dont snip the last word if thats the match
		if( $startpos + $_rellength < $textlength) {
			$reltext = substr($reltext, 0, strrpos($reltext, " ")).$_indicator; // remove last word
		}

		// If we trimmed from the front add ...
		if($startpos != 0) {
			$reltext = $_indicator.substr($reltext, strpos($reltext, " ") + 1); // remove first word
		}

		return $reltext;
	}

	/**
	 * Get excerpt from string
	 *
	 * @param String $_str String to get an excerpt from
	 * @param Integer $startPos Position int string to start excerpt from
	 * @param Integer $_maxLength Maximum length the excerpt may be
	 * @return String excerpt
	 */
	public static function get($_str, $_startPos=0, $_maxLength=155, $_strip=true )
	{
		if($_strip)
		{
			$_str = strip_tags($_str);
			$_str = str_replace(array("\n", "\r", "\t"), ' ', $_str);
			$_str = preg_replace('/\s+/', ' ', $_str);
		}

		if(mb_strlen($_str) > $_maxLength)
		{
			$excerpt   = substr($_str, $_startPos, $_maxLength-3);
			$lastSpace = strrpos($excerpt, ' ');
			$excerpt   = substr($excerpt, 0, $lastSpace);
			$excerpt  .= '...';
		}
		else
		{
			$excerpt = $_str;
		}

		return $excerpt;
	}
}
?>