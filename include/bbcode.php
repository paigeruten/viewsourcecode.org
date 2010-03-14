<?php

/* bbcode.php
 *
 * Changes a BBCode string into HTML.
 */

function bbcode($text) {
	// Make sure the browser doesn't display user-posted HTML.
	$text = htmlentities($text);

	// BBCode
	$bbcodes = array(
		'/\[b\](.*?)\[\/b\]/' => '<strong>$1</strong>',
		'/\[i\](.*?)\[\/i\]/' => '<em>$1</em>',
		'/\[u\](.*?)\[\/u\]/' => '<span style="text-decoration: underline;">$1</span>',
		'/\[url=(.+?)\](.+?)\[\/url\]/' => '<a href="$1">$2</a>',
	);

	foreach ($bbcodes as $search => $replace) {
		$text = preg_replace($search, $replace, $text);
	}

	// Emoticons
	$emoticons = array(
		':-D' => 'emoticon_grin.png',
		':-)' => 'emoticon_smile.png',
		':-O' => 'emoticon_surprised.png',
		':-P' => 'emoticon_tongue.png',
		':-(' => 'emoticon_unhappy.png',
		';-)' => 'emoticon_wink.png',
	);
	
	foreach ($emoticons as $emote_text => $image) {
		$text = str_replace($emote_text, ' <img src="/images/emoticons/' . $image . '" /> ', $text);
	}
	
	// Change newlines to <br />'s
	$text = nl2br($text);

	return $text;
}
