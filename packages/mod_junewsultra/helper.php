<?php
/**
 * JUNewsUltra Pro
 *
 * @version          6.x
 * @package          UNewsUltra Pro
 * @author           Denys D. Nosov (denys@joomla-ua.org)
 * @copyright    (C) 2007-2017 by Denys D. Nosov (http://joomla-ua.org)
 * @license          GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 *
 **/

defined('_JEXEC') or die;

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
		$items = 'ROOT';

		return $items;
	}
}