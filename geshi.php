<?php
/**
	* @package     Joomla.Plugin
 * @subpackage  Content.geshi
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
use Joomla\Utilities\ArrayHelper;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * GeSHi Content Plugin
 *
 * @package     Joomla.Plugin
 * @subpackage  Content.geshi
 */
class plgContentGeshi extends CMSPlugin
{
	/**
	 * @param   string	The context of the content being passed to the plugin.
	 * @param   object	The article object.  Note $article->text is also available
	 * @param   object	The article params
	 * @param   integer  The 'page' number
	 */
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		// Simple performance check to determine whether bot should process further.
		if (strpos($article->text, 'pre>') === false)
		{
			return true;
		}

		// Define the regular expression for the bot.
		$regex = "#<pre xml:\s*(.*?)>(.*?)</pre>#s";

		// Perform the replacement.
		$article->text = preg_replace_callback($regex, array(&$this, '_replace'), $article->text);

		return true;
	}

	/**
	 * Replaces the matched tags.
	 *
	 * @param   array  An array of matches (see preg_match_all)
	 * @return  string
	 */
	protected function _replace($matches)
	{
		jimport('joomla.utilities.utility');

		require_once __DIR__ . '/geshi/geshi.php';

		$args = JUtility::parseAttributes($matches[1]);
		$text = $matches[2];

		$lang = ArrayHelper::getValue($args, 'lang', 'php');
		$lines = ArrayHelper::getValue($args, 'lines', 'false');

		$html_entities_match = array("|\<br \/\>|", "#<#", "#>#", "|&#39;|", '#&quot;#', '#&nbsp;#');
		$html_entities_replace = array("\n", '&lt;', '&gt;', "'", '"', ' ');

		$text = preg_replace($html_entities_match, $html_entities_replace, $text);

		$text = str_replace('&lt;', '<', $text);
		$text = str_replace('&gt;', '>', $text);

		$text = str_replace("\t", '  ', $text);
		
		$text = ltrim(rtrim( $text ));		
		
		$geshi = new GeSHi($text, $lang);
		if ($lines == 'true')
		{
			$geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
		}
		$geshi->set_header_type(GESHI_HEADER_DIV);
		$geshi->set_overall_class('mw-geshi');
		$geshi->set_code_style('font-family:monospace;font-size: 13px;line-height: normal;', true);		
		$text = $geshi->parse_code();		
		
		return $text;
	}
}
