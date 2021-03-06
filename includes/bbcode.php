<?php
/***************************************************************************
 *                              bbcode.php
 *                            -------------------
 *   begin                : Saturday, Feb 13, 2001
 *   copyright            : (C) 2001 The phpBB Group
 *   email                : support@phpbb.com
 *   modification         : (C) 2003 Przemo www.przemo.org/phpBB2/
 *   date modification    : ver. 1.12.4 2005/10/8 20:16
 *
 *   $Id: bbcode.php,v 1.36.2.39 2005/12/29 15:12:20 acydburn Exp $
 *
 ***************************************************************************/

/***************************************************************************
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

if ( !defined('IN_PHPBB') )
{
	die('Hacking attempt');
}

define('BBCODE_UID_LEN', 10);

// global that holds loaded-and-prepared bbcode templates, so we only have to do
// that stuff once.

$bbcode_tpl = null;

/**
 * Loads bbcode templates from the bbcode.tpl file of the current template set.
 * Creates an array, keys are bbcode names like "b_open" or "url", values
 * are the associated template.
 * Probably pukes all over the place if there's something really screwed
 * with the bbcode.tpl file.
 *
 * Nathan Codding, Sept 26 2001.
 */
function load_bbcode_template()
{
	global $template;
	$tpl_filename = $template->make_filename('bbcode.tpl');
	$tpl = fread(fopen($tpl_filename, 'r'), filesize($tpl_filename));

	// replace \ with \\ and then ' with \'.
	$tpl = str_replace('\\', '\\\\', $tpl);
	$tpl  = str_replace('\'', '\\\'', $tpl);

	// strip newlines.
	$tpl  = str_replace(array("\n", "\r"), '', $tpl);

	// Turn template blocks into PHP assignment statements for the values of $bbcode_tpls..
	$tpl = preg_replace('#<!-- BEGIN (.*?) -->(.*?)<!-- END (.*?) -->#', "\n" . '$bbcode_tpls[\'\\1\'] = \'\\2\';', $tpl);

	$bbcode_tpls = array();

	eval($tpl);

	return $bbcode_tpls;
}


/**
 * Prepares the loaded bbcode templates for insertion into preg_replace()
 * or str_replace() calls in the bbencode_second_pass functions. This
 * means replacing template placeholders with the appropriate preg backrefs
 * or with language vars. NOTE: If you change how the regexps work in
 * bbencode_second_pass(), you MUST change this function.
 *
 * Nathan Codding, Sept 26 2001
 *
 */
function prepare_bbcode_template($bbcode_tpl)
{
	global $lang;

	$bbcode_tpl['olist_open'] = str_replace('{LIST_TYPE}', '\\1', $bbcode_tpl['olist_open']);

	$bbcode_tpl['color_open'] = str_replace('{COLOR}', '\\1', $bbcode_tpl['color_open']);

	$bbcode_tpl['size_open'] = str_replace('{SIZE}', '\\1', $bbcode_tpl['size_open']);

	$bbcode_tpl['quote_open'] = str_replace('{L_QUOTE}', $lang['Quote'], $bbcode_tpl['quote_open']);

	$bbcode_tpl['quote_username_open'] = str_replace('{L_QUOTE}', $lang['Quote'], $bbcode_tpl['quote_username_open']);
	$bbcode_tpl['quote_username_open'] = str_replace('{L_WROTE}', $lang['wrote'], $bbcode_tpl['quote_username_open']);
	$bbcode_tpl['quote_username_open'] = str_replace('{USERNAME}', '\\1', $bbcode_tpl['quote_username_open']);

	$bbcode_tpl['code_open'] = str_replace('{L_CODE}', $lang['Code'], $bbcode_tpl['code_open']);

	$bbcode_tpl['img'] = str_replace('{URL}', '\\1', $bbcode_tpl['img']);

	// We do URLs in several different ways..
	$bbcode_tpl['url1'] = str_replace('{URL}', '\\1', $bbcode_tpl['url']);
	$bbcode_tpl['url1'] = str_replace('{DESCRIPTION}', '\\1', $bbcode_tpl['url1']);

	$bbcode_tpl['url2'] = str_replace('{URL}', 'http://\\1', $bbcode_tpl['url']);
	$bbcode_tpl['url2'] = str_replace('{DESCRIPTION}', '\\1', $bbcode_tpl['url2']);

	$bbcode_tpl['url3'] = str_replace('{URL}', '\\1', $bbcode_tpl['url']);
	$bbcode_tpl['url3'] = str_replace('{DESCRIPTION}', '\\2', $bbcode_tpl['url3']);

	$bbcode_tpl['url4'] = str_replace('{URL}', 'http://\\1', $bbcode_tpl['url']);
	$bbcode_tpl['url4'] = str_replace('{DESCRIPTION}', '\\3', $bbcode_tpl['url4']);

	$bbcode_tpl['email'] = str_replace('{EMAIL}', '\\1', $bbcode_tpl['email']);

	$bbcode_tpl['show'] = str_replace('{HTEXTE}', '\\1', $bbcode_tpl['show']);

	$bbcode_tpl['glow_open'] = str_replace('{GLOWCOLOR}', '\\1', $bbcode_tpl['glow_open']);
	$bbcode_tpl['shadow_open'] = str_replace('{SHADOWCOLOR}', '\\1', $bbcode_tpl['shadow_open']);

	define('BBCODE_TPL_READY', true);
	
	return $bbcode_tpl;
}

function hide_in_quote($text)
{
	$text = preg_replace("#\[hide\](.*?)\[\/hide\]#si","--- phpBB : The Protected Message is not copied in this quote ---", $text);
	return $text;
}

function bbencode_third_pass($text, $uid, $deprotect)
{
	global $bbcode_tpl, $board_config;

	$text = ' ' . $text;

	if (! (strpos($text, "[") && strpos($text, "]")) )
	{
		$text = substr($text, 1);
		return $text;
	}
	$patterns = array();
	$replacements = array();

	if ( !$board_config['button_hi'] )
	{
		$text = str_replace(array("[hide:$uid]", "[/hide:$uid]"), '', $text);
	}

	if ( $deprotect )
	{
		$patterns[0] = "#\[hide:$uid\](.*?)\[/hide:$uid\]#si";
		$replacements[0] = $bbcode_tpl['show'];
	}
	else
	{
		$patterns[0] = "#\[hide:$uid\](.*?)\[/hide:$uid\]#si";
		$replacements[0] = $bbcode_tpl['hide'];
	}

	$text = preg_replace($patterns, $replacements, $text);

	$text = substr($text, 1);

	return $text;
}

/**
 * Does second-pass bbencoding. This should be used before displaying the message in
 * a thread. Assumes the message is already first-pass encoded, and we are given the
 * correct UID as used in first-pass encoding.
 */
function bbencode_second_pass($text, $uid, $username = '')
{
	global $lang, $bbcode_tpl, $board_config;

	$text = preg_replace('#(script|about|applet|activex|chrome):#is', "\\1&#058;", $text);

	// pad it with a space so we can distinguish between FALSE and matching the 1st char (index 0).
	// This is important; bbencode_quote(), bbencode_list(), and bbencode_code() all depend on it.
	$text = " " . $text;

	// First: If there isn't a "[" and a "]" in the message, don't bother.
	if (! (strpos($text, "[") && strpos($text, "]")) )
	{
		// Remove padding, return.
		$text = substr($text, 1);
		return $text;
	}

	// Only load the templates ONCE..
	if (!defined("BBCODE_TPL_READY"))
	{
		// load templates from file into array.
		$bbcode_tpl = load_bbcode_template();

		// prepare array for use in regexps.
		$bbcode_tpl = prepare_bbcode_template($bbcode_tpl);
	}

	if ( !$board_config['allow_bbcode'] )
	{
		$board_config['button_hi'] = $board_config['allow_bbcode'] = $board_config['button_b'] = $board_config['button_l'] = $board_config['color_box'] = $board_config['size_box'] = $board_config['button_q'] = $board_config['button_f'] = $board_config['button_s'] = $board_config['button_i'] = $board_config['button_u'] = $board_config['button_ce'] = $board_config['glow_box'] = $board_config['button_im'] = $board_config['button_ur'] = '';
	}

	// [CODE] and [/CODE] for posting code (HTML, PHP, C etc etc) in your posts.
	$text = bbencode_second_pass_code($text, $uid, $bbcode_tpl);

	// [QUOTE] and [/QUOTE] for posting replies with quote, or just for quoting stuff.
	$text = ($board_config['button_q']) ? str_replace("[quote:$uid]", $bbcode_tpl['quote_open'], $text) : str_replace("[quote:$uid]", '', $text);
	$text = ($board_config['button_q']) ? str_replace("[/quote:$uid]", $bbcode_tpl['quote_close'], $text) : str_replace("[/quote:$uid]", '', $text);

	// New one liner to deal with opening quotes with usernames...
	// replaces the two line version that I had here before..
	$text = ($board_config['button_q']) ? preg_replace("/\[quote:$uid=\"(.*?)\"\]/si", $bbcode_tpl['quote_username_open'], $text) : preg_replace("/\[quote:$uid=\"(.*?)\"\]/si", '', $text);

	// [list] and [list=x] for (un)ordered lists.
	// unordered lists
	$text = ($board_config['button_l']) ? str_replace("[list:$uid]", $bbcode_tpl['ulist_open'], $text) : str_replace("[list:$uid]", '', $text);
	// li tags
	$text =  ($board_config['button_l']) ? str_replace("[*:$uid]", $bbcode_tpl['listitem'], $text) : str_replace("[*:$uid]", '', $text);
	// ending tags
	$text =  ($board_config['button_l']) ? str_replace("[/list:u:$uid]", $bbcode_tpl['ulist_close'], $text) : str_replace("[/list:u:$uid]", '', $text);
	$text =  ($board_config['button_l']) ? str_replace("[/list:o:$uid]", $bbcode_tpl['olist_close'], $text) : str_replace("[/list:o:$uid]", '', $text);
	// Ordered lists
	$text = preg_replace("/\[list=([a1]):$uid\]/si", $bbcode_tpl['olist_open'], $text);

	// colours
	$text = ($board_config['color_box']) ? preg_replace("/\[color=(\#[0-9A-F]{6}|[a-z]+):$uid\]/si", $bbcode_tpl['color_open'], $text) : preg_replace("/\[color=(\#[0-9A-F]{6}|[a-z]+):$uid\]/si", '', $text);
	$text = ($board_config['color_box']) ? str_replace("[/color:$uid]", $bbcode_tpl['color_close'], $text) : str_replace("[/color:$uid]", '', $text);

	// size
	$text = ($board_config['size_box']) ? preg_replace("/\[size=([1-2]?[0-9]):$uid\]/si", $bbcode_tpl['size_open'], $text) : preg_replace("/\[size=([1-2]?[0-9]):$uid\]/si", '', $text);
	$text = ($board_config['size_box']) ? str_replace("[/size:$uid]", $bbcode_tpl['size_close'], $text) : str_replace("[/size:$uid]", '', $text);

	// [b] and [/b] for bolding text.
	$text = ($board_config['button_b']) ? str_replace("[b:$uid]", $bbcode_tpl['b_open'], $text) : str_replace("[b:$uid]", '', $text);
	$text = ($board_config['button_b']) ? str_replace("[/b:$uid]", $bbcode_tpl['b_close'], $text) : str_replace("[/b:$uid]", '', $text);

	// [u] and [/u] for underlining text.
	$text = ($board_config['button_u']) ? str_replace("[u:$uid]", $bbcode_tpl['u_open'], $text) : str_replace("[u:$uid]", '', $text);
	$text = ($board_config['button_u']) ? str_replace("[/u:$uid]", $bbcode_tpl['u_close'], $text) : str_replace("[/u:$uid]", '', $text);

	// [i] and [/i] for italicizing text.
	$text = ($board_config['button_i']) ? str_replace("[i:$uid]", $bbcode_tpl['i_open'], $text) : str_replace("[i:$uid]", '', $text);
	$text = ($board_config['button_i']) ? str_replace("[/i:$uid]", $bbcode_tpl['i_close'], $text) : str_replace("[/i:$uid]", '', $text);

	// Fade
	$text = ($board_config['button_f']) ? str_replace("[fade:$uid]", $bbcode_tpl['fade_open'], $text) : str_replace("[fade:$uid]", '', $text);
	$text = ($board_config['button_f']) ? str_replace("[/fade:$uid]", $bbcode_tpl['fade_close'], $text) : str_replace("[/fade:$uid]", '', $text);

	// Scroll
	$text = ($board_config['button_s']) ? str_replace("[scroll:$uid]", $bbcode_tpl['scroll_open'], $text) : str_replace("[scroll:$uid]", '', $text);
	$text = ($board_config['button_s']) ? str_replace("[/scroll:$uid]", $bbcode_tpl['scroll_close'], $text) : str_replace("[/scroll:$uid]", '', $text);

	// Center
	$text = ($board_config['button_ce']) ? str_replace("[center:$uid]", $bbcode_tpl['center_open'], $text) : str_replace("[center:$uid]", '', $text);
	$text =  ($board_config['button_ce']) ? str_replace("[/center:$uid]", $bbcode_tpl['center_close'], $text) : str_replace("[/center:$uid]", '', $text);

	// Glow
	$text = ($board_config['glow_box']) ? preg_replace("/\[glow=(\#[0-9A-F]{6}|[a-z]+):$uid\]/si", $bbcode_tpl['glow_open'], $text) : preg_replace("/\[glow=(\#[0-9A-F]{6}|[a-z]+):$uid\]/si", '', $text);
	$text = ($board_config['glow_box']) ? str_replace("[/glow:$uid]", $bbcode_tpl['glow_close'], $text) : str_replace("[/glow:$uid]", '', $text);

	// Shadow
	$text = ($board_config['glow_box']) ? preg_replace("/\[shadow=(\#[0-9A-F]{6}|[a-z]+):$uid\]/si", $bbcode_tpl['shadow_open'], $text) : preg_replace("/\[shadow=(\#[0-9A-F]{6}|[a-z]+):$uid\]/si", '', $text);
	$text = ($board_config['glow_box']) ? str_replace("[/shadow:$uid]", $bbcode_tpl['shadow_close'], $text) : str_replace("[/shadow:$uid]", '', $text);

	// Patterns and replacements for URL and email tags..
	$patterns = array();
	$replacements = array();

	if ( $board_config['button_im'] )
	{
		// [img]image_url_here[/img] code..
		// This one gets first-passed..
		$patterns[] = "#\[img:$uid\]([^?].*?)\[/img:$uid\]#i";
		$replacements[] = $bbcode_tpl['img'];
	}
	else
	{
		$text = str_replace(array("[img:$uid]", "[/img:$uid]"), '', $text);
	}

	if ( $board_config['button_ur'] )
	{
		// matches a [url]xxxx://www.phpbb.com[/url] code..
		$patterns[] = "#\[url\]([\w]+?://([\w\#()$%&~/.\-;:=,?|!*@\]+]|\[(?!url=))*?)\[/url\]#is";
		$replacements[] = $bbcode_tpl['url1'];

		// [url]www.phpbb.com[/url] code.. (no xxxx:// prefix).
		$patterns[] = "#\[url\]((www|ftp)\.([\w\#()$%&~/.\-;:=,?|!*@\]+]|\[(?!url=))*?)\[/url\]#is";
		$replacements[] = $bbcode_tpl['url2'];

		// [url=xxxx://www.phpbb.com]phpBB[/url] code..
		$patterns[] = "#\[url=([\w]+?://[\w\#()$%&~/.\-;:=,?|!*@\[\]+]*?)\]([^?\n\r\t].*?)\[/url\]#is";
		$replacements[] = $bbcode_tpl['url3'];

		// [url=www.phpbb.com]phpBB[/url] code.. (no xxxx:// prefix).
		$patterns[] = "#\[url=((www|ftp)\.[\w\#()$%&~/.\-;:=,?|!*@\[\]+]*?)\]([^?\n\r\t].*?)\[/url\]#is";
		$replacements[] = $bbcode_tpl['url4'];
	}
	else
	{
		$text = str_replace(array("[url=", "[URL=", "[url]", "[/url]", "[URL]", "[/URL]"), '', $text);
	}

	// [email]user@domain.tld[/email] code..
	$patterns[] = "#\[email\]([a-z0-9&\-_.]+?@[\w\-]+\.([\w\-\.]+\.)?[\w]+)\[/email\]#si";
	$replacements[] = $bbcode_tpl['email'];

	$username = ( $username == "Anonymous" ) ? $lang['Guest'] : $username;
	$text = str_replace("[you:$uid]", $username, $text);

	$text = preg_replace($patterns, $replacements, $text);

	// Remove our padding from the string..
	$text = substr($text, 1);

	return $text;

} // bbencode_second_pass()

// Need to initialize the random numbers only ONCE
mt_srand( (double) microtime() * 1000000);

function make_bbcode_uid()
{
	// Unique ID for this message..

	$uid = md5(mt_rand());
	$uid = substr($uid, 0, BBCODE_UID_LEN);

	return $uid;
}

function bbencode_first_pass($text, $uid)
{
	// pad it with a space so we can distinguish between FALSE and matching the 1st char (index 0).
	// This is important; bbencode_quote(), bbencode_list(), and bbencode_code() all depend on it.
	$text = " " . $text;

	// [CODE] and [/CODE] for posting code (HTML, PHP, C etc etc) in your posts.
	$text = bbencode_first_pass_pda($text, $uid, '[code]', '[/code]', '', true, '');

	// [QUOTE] and [/QUOTE] for posting replies with quote, or just for quoting stuff.
	$text = bbencode_first_pass_pda($text, $uid, '[quote]', '[/quote]', '', false, '');
	$text = bbencode_first_pass_pda($text, $uid, '/\[quote=\\\\&quot;(.*?)\\\\&quot;\]/is', '[/quote]', '', false, '', "[quote:$uid=\\\"\\1\\\"]");
	$text = bbencode_first_pass_pda($text, $uid, '/\[quote=(\\\".*?\\\")\]/is', '[/quote]', '', false, '', "[quote:$uid=\\1]");

	// [list] and [list=x] for (un)ordered lists.
	$open_tag = array();
	$open_tag[0] = "[list]";

	// unordered..
	$text = bbencode_first_pass_pda($text, $uid, $open_tag, "[/list]", "[/list:u]", false, 'replace_listitems');

	$open_tag[0] = "[list=1]";
	$open_tag[1] = "[list=a]";

	// ordered.
	$text = bbencode_first_pass_pda($text, $uid, $open_tag, "[/list]", "[/list:o]",  false, 'replace_listitems');

	// [color] and [/color] for setting text color
	$text = preg_replace("#\[color=(\#[0-9A-F]{6}|[a-z\-]+)\](.*?)\[/color\]#si", "[color=\\1:$uid]\\2[/color:$uid]", $text);

	// [size] and [/size] for setting text size
	$text = preg_replace("#\[size=([1-2]?[0-9])\](.*?)\[/size\]#si", "[size=\\1:$uid]\\2[/size:$uid]", $text);

	// [b] and [/b] for bolding text.
	$text = preg_replace("#\[b\](.*?)\[/b\]#si", "[b:$uid]\\1[/b:$uid]", $text);

	// [u] and [/u] for underlining text.
	$text = preg_replace("#\[u\](.*?)\[/u\]#si", "[u:$uid]\\1[/u:$uid]", $text);

	// [i] and [/i] for italicizing text.
	$text = preg_replace("#\[i\](.*?)\[/i\]#si", "[i:$uid]\\1[/i:$uid]", $text);

	// [img]image_url_here[/img] code..
    $text = preg_replace_callback("#\[img\]((http|ftp|https|ftps)://)([^\r\n\t<\"]*?)\[/img\]#si",
		create_function('$m','return \'[img:]\'.$m[1] . str_replace(array(\' \', \'&amp;\'), array(\'%20\', \'&\'), $m[3]) . \'[/img:]\';'),
		$text);
	$text = str_replace(array('[img:]','[/img:]'), array('[img:'.$uid.']','[/img:'.$uid.']'), $text);

	// Center
	$text = preg_replace("#\[center\](.*?)\[/center\]#si", "[center:$uid]\\1[/center:$uid]", $text);

	// Glow
	$text = preg_replace("#\[glow=(\#[0-9A-F]{6}|[a-z\-]+)\](.*?)\[/glow\]#si", "[glow=\\1:$uid]\\2[/glow:$uid]", $text);

	// Shadow
	$text = preg_replace("#\[shadow=(\#[0-9A-F]{6}|[a-z\-]+)\](.*?)\[/shadow\]#si", "[shadow=\\1:$uid]\\2[/shadow:$uid]", $text);

	// Hide (Lockdown)
	$text = preg_replace("#\[hide\](.*?)\[\/hide\]#si","[hide:$uid]\\1[/hide:$uid]", $text);

	// Fade
	$text = preg_replace("#\[fade\](.*?)\[/fade\]#si", "[fade:$uid]\\1[/fade:$uid]", $text);

	// Scroll
	$text = preg_replace("#\[scroll\](.*?)\[/scroll\]#si", "[scroll:$uid]\\1[/scroll:$uid]", $text);

	// You
	$text = str_replace("[you]", "[you:$uid]", $text);

	// Remove our padding from the string..
	return substr($text, 1);

} // bbencode_first_pass()

/**
 * $text - The text to operate on.
 * $uid - The UID to add to matching tags.
 * $open_tag - The opening tag to match. Can be an array of opening tags.
 * $close_tag - The closing tag to match.
 * $close_tag_new - The closing tag to replace with.
 * $mark_lowest_level - boolean - should we specially mark the tags that occur
 * 					at the lowest level of nesting? (useful for [code], because
 *						we need to match these tags first and transform HTML tags
 *						in their contents..
 * $func - This variable should contain a string that is the name of a function.
 *				That function will be called when a match is found, and passed 2
 *				parameters: ($text, $uid). The function should return a string.
 *				This is used when some transformation needs to be applied to the
 *				text INSIDE a pair of matching tags. If this variable is FALSE or the
 *				empty string, it will not be executed.
 * If open_tag is an array, then the pda will try to match pairs consisting of
 * any element of open_tag followed by close_tag. This allows us to match things
 * like [list=A]...[/list] and [list=1]...[/list] in one pass of the PDA.
 *
 * NOTES:	- this function assumes the first character of $text is a space.
 *				- every opening tag and closing tag must be of the [...] format.
 */
function bbencode_first_pass_pda($text, $uid, $open_tag, $close_tag, $close_tag_new, $mark_lowest_level, $func, $open_regexp_replace = false)
{
	$open_tag_count = 0;

	if (!$close_tag_new || ($close_tag_new == ''))
	{
		$close_tag_new = $close_tag;
	}

	$close_tag_length = strlen($close_tag);
	$close_tag_new_length = strlen($close_tag_new);
	$uid_length = strlen($uid);

	$use_function_pointer = ($func && ($func != ''));

	$stack = array();

	if (is_array($open_tag))
	{
		if (0 == count($open_tag))
		{
			// No opening tags to match, so return.
			return $text;
		}
		$open_tag_count = count($open_tag);
	}
	else
	{
		// only one opening tag. make it into a 1-element array.
		$open_tag_temp = $open_tag;
		$open_tag = array();
		$open_tag[0] = $open_tag_temp;
		$open_tag_count = 1;
	}

	$open_is_regexp = false;

	if ($open_regexp_replace)
	{
		$open_is_regexp = true;
		if (!is_array($open_regexp_replace))
		{
			$open_regexp_temp = $open_regexp_replace;
			$open_regexp_replace = array();
			$open_regexp_replace[0] = $open_regexp_temp;
		}
	}

	if ($mark_lowest_level && $open_is_regexp)
	{
		message_die(GENERAL_ERROR, "Unsupported operation for bbcode_first_pass_pda().");
	}

	// Start at the 2nd char of the string, looking for opening tags.
	$curr_pos = 1;
	while ($curr_pos && ($curr_pos < strlen($text)))
	{
		$curr_pos = strpos($text, "[", $curr_pos);

		// If not found, $curr_pos will be 0, and the loop will end.
		if ($curr_pos)
		{
			// We found a [. It starts at $curr_pos.
			// check if it's a starting or ending tag.
			$found_start = false;
			$which_start_tag = "";
			$start_tag_index = -1;

			for ($i = 0; $i < $open_tag_count; $i++)
			{
				// Grab everything until the first "]"...
				$possible_start = substr($text, $curr_pos, strpos($text, ']', $curr_pos + 1) - $curr_pos + 1);

				//
				// We're going to try and catch usernames with "[' characters.
				//
				if( preg_match('#\[quote=\\\&quot;#si', $possible_start, $match) && !preg_match('#\[quote=\\\&quot;(.*?)\\\&quot;\]#si', $possible_start) )
				{
					// OK we are in a quote tag that probably contains a ] bracket.
					// Grab a bit more of the string to hopefully get all of it..
					if ($close_pos = strpos($text, '&quot;]', $curr_pos + 14))
					{
						if (strpos(substr($text, $curr_pos + 14, $close_pos - ($curr_pos + 14)), '[quote') === false)
						{
							$possible_start = substr($text, $curr_pos, $close_pos - $curr_pos + 7);
						}
					}
				}

				// Now compare, either using regexp or not.
				if ($open_is_regexp)
				{
					$match_result = array();
					if (preg_match($open_tag[$i], $possible_start, $match_result))
					{
						$found_start = true;
						$which_start_tag = $match_result[0];
						$start_tag_index = $i;
						break;
					}
				}
				else
				{
					// straightforward string comparison.
					if (0 == strcasecmp($open_tag[$i], $possible_start))
					{
						$found_start = true;
						$which_start_tag = $open_tag[$i];
						$start_tag_index = $i;
						break;
					}
				}
			}

			if ($found_start)
			{
				// We have an opening tag.
				// Push its position, the text we matched, and its index in the open_tag array on to the stack, and then keep going to the right.
				$match = array("pos" => $curr_pos, "tag" => $which_start_tag, "index" => $start_tag_index);
				array_push($stack, $match);
				//
				// Rather than just increment $curr_pos
				// Set it to the ending of the tag we just found
				// Keeps error in nested tag from breaking out
				// of table structure..
				//
				$curr_pos += strlen($possible_start);
			}
			else
			{
				// check for a closing tag..
				$possible_end = substr($text, $curr_pos, $close_tag_length);
				if (0 == strcasecmp($close_tag, $possible_end))
				{
					// We have an ending tag.
					// Check if we've already found a matching starting tag.
					if (sizeof($stack) > 0)
					{
						// There exists a starting tag.
						$curr_nesting_depth = sizeof($stack);
						// We need to do 2 replacements now.
						$match = array_pop($stack);
						$start_index = $match['pos'];
						$start_tag = $match['tag'];
						$start_length = strlen($start_tag);
						$start_tag_index = $match['index'];

						if ($open_is_regexp)
						{
							$start_tag = preg_replace($open_tag[$start_tag_index], $open_regexp_replace[$start_tag_index], $start_tag);
						}

						// everything before the opening tag.
						$before_start_tag = substr($text, 0, $start_index);

						// everything after the opening tag, but before the closing tag.
						$between_tags = substr($text, $start_index + $start_length, $curr_pos - $start_index - $start_length);

						// Run the given function on the text between the tags..
						if ($use_function_pointer)
						{
							$between_tags = $func($between_tags, $uid);
						}

						// everything after the closing tag.
						$after_end_tag = substr($text, $curr_pos + $close_tag_length);

						// Mark the lowest nesting level if needed.
						if ($mark_lowest_level && ($curr_nesting_depth == 1))
						{
							if ($open_tag[0] == '[code]')
							{
								$code_entities_match = array('#<#', '#>#', '#"#', '#:#', '#\[#', '#\]#', '#\(#', '#\)#', '#\{#', '#\}#');
								$code_entities_replace = array('&lt;', '&gt;', '&quot;', '&#58;', '&#91;', '&#93;', '&#40;', '&#41;', '&#123;', '&#125;');
								$between_tags = preg_replace($code_entities_match, $code_entities_replace, $between_tags);
							}
							$text = $before_start_tag . substr($start_tag, 0, $start_length - 1) . ":$curr_nesting_depth:$uid]";
							$text .= $between_tags . substr($close_tag_new, 0, $close_tag_new_length - 1) . ":$curr_nesting_depth:$uid]";
						}
						else
						{
							if ($open_tag[0] == '[code]')
							{
								$text = $before_start_tag . '&#91;code&#93;';
								$text .= $between_tags . '&#91;/code&#93;';
							}
							else
							{
								if ($open_is_regexp)
								{
									$text = $before_start_tag . $start_tag;
								}
								else
								{
									$text = $before_start_tag . substr($start_tag, 0, $start_length - 1) . ":$uid]";
								}
								$text .= $between_tags . substr($close_tag_new, 0, $close_tag_new_length - 1) . ":$uid]";
							}
						}

						$text .= $after_end_tag;

						// Now.. we've screwed up the indices by changing the length of the string.
						// So, if there's anything in the stack, we want to resume searching just after it.
						// otherwise, we go back to the start.
						if (sizeof($stack) > 0)
						{
							$match = array_pop($stack);
							$curr_pos = $match['pos'];
//							bbcode_array_push($stack, $match);
//							++$curr_pos;
						}
						else
						{
							$curr_pos = 1;
						}
					}
					else
					{
						// No matching start tag found. Increment pos, keep going.
						++$curr_pos;
					}
				}
				else
				{
					// No starting tag or ending tag.. Increment pos, keep looping.,
					++$curr_pos;
				}
			}
		}
	} // while

	return $text;

} // bbencode_first_pass_pda()

/**
 * Does second-pass bbencoding of the [code] tags. This includes
 * running xhtmlspecialchars() over the text contained between
 * any pair of [code] tags that are at the first level of
 * nesting. Tags at the first level of nesting are indicated
 * by this format: [code:1:$uid] ... [/code:1:$uid]
 * Other tags are in this format: [code:$uid] ... [/code:$uid]
 */
function bbencode_second_pass_code($text, $uid, $bbcode_tpl)
{
	global $lang;

	$code_start_html = $bbcode_tpl['code_open'];
	$code_end_html =  $bbcode_tpl['code_close'];

	// First, do all the 1st-level matches. These need an xhtmlspecialchars() run,
	// so they have to be handled differently.
	$match_count = preg_match_all("#\[code:1:$uid\](.*?)\[/code:1:$uid\]#si", $text, $matches);

	for ($i = 0; $i < $match_count; $i++)
	{
		$before_replace = $matches[1][$i];
		$after_replace = $matches[1][$i];

		// Special chars
		$code_entities_match = array('&lt;', '&gt;', '&quot;', '&#58;', '&#91;', '&#93;', '&#40;', '&#41;', '&#123;', '&#125;');
		$code_entities_replace = array('<', '>', '"', ':', '[', ']', '(', ')', '{', '}');
		$after_replace = str_replace($code_entities_match, $code_entities_replace, $after_replace);

		$after_replace = str_replace(array('&', '&amp;amp;'), '&amp;', $after_replace);

		$code_entities_match = array('<', '>', '"', ':', '[', ']', '(', ')', '{', '}');
		$code_entities_replace = array('&lt;', '&gt;', '&quot;', '&#58;', '&#91;', '&#93;', '&#40;', '&#41;', '&#123;', '&#125;');
		$after_replace = str_replace($code_entities_match, $code_entities_replace, $after_replace);


		// Replace 2 spaces with "&nbsp; " so non-tabbed code indents without making huge long lines.
		$after_replace = str_replace("  ", "&nbsp; ", $after_replace);
		// now Replace 2 spaces with " &nbsp;" to catch odd #s of spaces.
		$after_replace = str_replace("  ", " &nbsp;", $after_replace);

		// Replace tabs with "&nbsp; &nbsp;" so tabbed code indents sorta right without making huge long lines.
		$after_replace = str_replace("\t", "&nbsp; &nbsp; ", $after_replace);

		// now Replace space occurring at the beginning of a line
		$after_replace = preg_replace("/^ {1}/m", '&nbsp;', $after_replace);

		$str_to_match = "[code:1:$uid]" . $before_replace . "[/code:1:$uid]";

		$replacement = $code_start_html;
		$replacement .= $after_replace;
		$replacement .= $code_end_html;

		$text = str_replace($str_to_match, $replacement, $text);
	}

	// Now, do all the non-first-level matches. These are simple.
	$text = str_replace("[code:$uid]", $code_start_html, $text);
	$text = str_replace("[/code:$uid]", $code_end_html, $text);

	return $text;

} // bbencode_second_pass_code()

function cut_links($str)
{
	if ( strlen(strip_tags($str)) > 50 )
	{
		$str = str_replace('&amp;', '&', $str);
		return substr($str, 0, 25) . '...' . substr($str, -15);
	}
	else
	{
		return $str;
	}
}

/**
 * Rewritten by Nathan Codding - Feb 6, 2001.
 * - Goes through the given string, and replaces xxxx://yyyy with an HTML <a> tag linking
 * 	to that URL
 * - Goes through the given string, and replaces www.xxxx.yyyy[zzzz] with an HTML <a> tag linking
 * 	to http://www.xxxx.yyyy[/zzzz]
 * - Goes through the given string, and replaces xxxx@yyyy with an HTML mailto: tag linking
 *		to that email address
 * - Only matches these 2 patterns either after a space, or at the beginning of a line
 *
 * Notes: the email one might get annoying - it's easy to make it more restrictive, though.. maybe
 * have it require something like xxxx@yyyy.zzzz or such. We'll see.
 */
function make_clickable($text)
{
	global $board_config;

	$text = preg_replace('#(script|about|applet|activex|chrome):#is', "\\1&#058;", $text);

	// pad it with a space so we can match things at the start of the 1st line.
	$ret = ' ' . $text;

	// matches an "xxxx://yyyy" URL at the start of a line, or after a space.
	// xxxx can only be alpha characters.
	// yyyy is anything up to the first space, newline, comma, double quote or <
	$ret = preg_replace("#(^|[\n ])([\w]+?://\S[\w\#()$%&~/.\-;:=,?|!*@\[\]+]*)#is", "\\1<a href=\"\\2\" rel=\"nofollow\" target=\"_blank\" class=\"postlink\">\\2</a>", $ret);

	// matches a "www|ftp.xxxx.yyyy[/zzzz]" kinda lazy URL thing
	// Must contain at least 2 dots. xxxx contains either alphanum, or "-"
	// zzzz is optional.. will contain everything up to the first space, newline, 
	// comma, double quote or <.
	$ret = preg_replace("#(^|[\n ])((www|ftp)\.\S[\w\#()$%&~/.\-;:=,?|!*@\[\]+]*)#is", "\\1<a href=\"http://\\2\" rel=\"nofollow\" target=\"_blank\" class=\"postlink\">\\2</a>", $ret);

	// matches an email@domain type address at the start of a line, or after a space.
	// Note: Only the followed chars are valid; alphanums, "-", "_" and or ".".
	$ret = preg_replace("#(^|[\n ])([a-z0-9&\-_.]+?)@([\w\-]+\.([\w\-\.]+\.)*[\w]+)#i", "\\1<a href=\"mailto:\\2@\\3\" class=\"postlink\">\\2@\\3</a>", $ret);

	// Cut long links
	// Thanks to FanFataL
	$num = preg_match_all('#<a[^>](.*?)>(.*?)</a>#is', $ret, $matches);
	for($i=0; $i<$num; $i++)
	{
		if ( strpos($matches[2][$i], 'www.') !== false || strpos($matches[2][$i], 'ftp.') !== false || strpos($matches[2][$i], 'http://') !== false )
		{
			$ret = str_replace($matches[0][$i], '<a '.$matches[1][$i].'>'.cut_links($matches[2][$i]).'</a>', $ret);
		}
	}

	// Remove our padding..
	$ret = substr($ret, 1);

	$ret = ($board_config['button_ur']) ? $ret : $text;

	return($ret);
}

/**
 * This is used to change a [*] tag into a [*:$uid] tag as part
 * of the first-pass bbencoding of [list] tags. It fits the
 * standard required in order to be passed as a variable
 * function into bbencode_first_pass_pda().
 */
function replace_listitems($text, $uid)
{
	$text = str_replace("[*]", "[*:$uid]", $text);

	return $text;
}

//
// Smilies code ... would this be better tagged on to the end of bbcode.php?
// Probably so and I'll move it before B2
//
function smilies_pass($message, $path = '')
{
	static $orig, $repl, $smilies_data;

	if ( !isset($orig) )
	{
		global $db, $board_config, $smilies_data;
		$orig = $repl = array();

		$smilies = sql_cache('check', 'smilies');
		if (!isset($smilies))
		{
			$sql = "SELECT * FROM " . SMILIES_TABLE . "
				ORDER by smile_order";
			if ( !$result = $db->sql_query($sql) )
			{
				message_die(GENERAL_ERROR, 'Couldn\'t obtain smilies data', '', __LINE__, __FILE__, $sql);
			}
			$smilies = $db->sql_fetchrowset($result);
			sql_cache('write', 'smilies', $smilies);
		}

		if(is_array($smilies) && !empty($smilies)){
			$smilies_data  = $smilies;
			usort($smilies, 'smiley_sort');
			$count_smilies = count($smilies);
		}else{
			$count_smilies = 0;
			$smilies_data  = array();
		}

		for ($i = 0; $i < $count_smilies; $i++)
		{
			if ( $smilies[$i]['code'] && $smilies[$i]['code'] != ' ' )
			{
				$smilies[$i]['code'] = str_replace(array('&', '"', '&amp;lt;', '&amp;gt;'), array('&amp;', '&quot;', '&lt;', '&gt;'), $smilies[$i]['code']);

				$smilie_title = ($smilies[$i]['code']) ? str_replace(array('?', ':'), array('&#063;', '&#058;'), $smilies[$i]['code']) : '';
				$orig[] = "/ (?<=.\W|\W.|^\W)" . preg_quote($smilies[$i]['code'], "/") . "(?=.\W|\W.|\W$)/ ";

				$repl[] = ' <img src="' . $path . $board_config['smilies_path'] . '/' . $smilies[$i]['smile_url'] . '" alt="' . $smilie_title . '" title="' . $smilie_title . '" border="0" align="top" /> ';
			}
		}
	}

	if ( count($orig) )
	{
		$message = preg_replace($orig, $repl, ' ' . preg_replace("/\r\n|\n/", ' sm' . chr(5) . ' ', $message) . ' ');
		$message = substr(str_replace(' sm' . chr(5) . ' ', "\n", $message), 1, -1);
	}
	
	return $message;
}

function smiley_sort($a, $b)
{
	if ( strlen($a['code']) == strlen($b['code']) )
	{
		return 0;
	}

	return ( strlen($a['code']) > strlen($b['code']) ) ? -1 : 1;
}

function word_wrap_pass($message)
{
	$maxChars = 70;
	$curCount = 0;
	$tempText = '';
	$finalText = '';
	$inTag = false;

	for ($num = 0; $num < strlen($message); $num++)
	{
		$curChar = $message{$num};
		if ( $curChar == '<' )
		{
			$tempText .= '<';
			$inTag = true;
		}
		elseif ( $inTag && $curChar == '>' )
		{
			$tempText .= '>';
			$inTag = false;
		}
		elseif ( $inTag )
			$tempText .= $curChar;
		elseif ( $curChar == ' ' )
		{
			$finalText .= $tempText . ' ';
			$tempText = '';
			$curCount = 0;
		}
		elseif ( $curCount >= $maxChars )
		{
			$finalText .= $tempText . $curChar . ' ';
			$tempText = '';
			$curCount = 0;
		}
		else
		{
			$tempText .= $curChar;
			$curCount++;
		}
	}

	return $finalText . $tempText;
}

?>