<?php
/**
 * JUNewsUltra Pro
 *
 * @package          Joomla.Site
 * @subpackage       mod_junewsultra
 *
 * @author           Denys Nosov, denys@joomla-ua.org
 * @copyright        2007-2017 (C) Joomla! Ukraine, http://joomla-ua.org. All rights reserved.
 * @license          GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_junewsultra
 *
 * @package     Joomla.Site
 * @subpackage  mod_junewsultra
 * @since       6.0
 */
abstract class modJUNewsUltraHelper
{
	/**
	 * @param $params
	 * @param $junews
	 *
	 * @return string items
	 *
	 * @since 6.0
	 */
	public static function getList($params, $junews)
	{
		return 'ROOT';
	}
}