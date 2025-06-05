<?php
/**
 * JUNewsUltra Pro
 *
 * @package          Joomla.Site
 * @subpackage       mod_junewsultra
 *
 * @author           Denys Nosov, denys@joomla-ua.org
 * @copyright        2007-2025 (C) Joomla! Ukraine, https://joomla-ua.org. All rights reserved.
 * @license          GNU/GPL - https://www.gnu.org/copyleft/gpl.html
 */

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

defined('JPATH_PLATFORM') or die;

class JFormFieldTemplate extends FormField
{

	protected $type = 'Template';

	/**
	 *
	 * @return string|void
	 *
	 * @throws \Exception
	 * @since 6.0
	 */
	protected function getInput()
	{
		if(!isset($_GET[ 'id' ]))
		{
			return Text::_('MOD_JUNEWS_NOT_EDIT_TEMPLATE');
		}
	}
}