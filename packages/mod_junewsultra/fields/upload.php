<?php
/**
 * JUNewsUltra Pro
 *
 * @package          Joomla.Site
 * @subpackage       mod_junewsultra
 *
 * @author           Denys Nosov, denys@joomla-ua.org
 * @copyright        2007-2019 (C) Joomla! Ukraine, http://joomla-ua.org. All rights reserved.
 * @license          GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;

class JFormFieldUpload extends FormField
{

	protected $type = 'Upload';

	/**
	 *
	 * @return string|void
	 *
	 * @since 6.0
	 */
	protected function getInput()
	{
		if(!isset($_GET[ 'id' ]))
		{
			return Text::_('MOD_JUNEWS_NOT_EDIT_TEMPLATE');
		}

		HTMLHelper::_('behavior.modal', 'a.modal');

		$html = [];
		$link = str_replace('/administrator', '', Uri::base()) . 'modules/mod_junewsultra/fields/uploadimg.php';

		$html[] = '<a class="modal btn"  href="' . $link . '" rel="{handler: \'iframe\', size: {x: 330, y: 180}}"><i class="icon-upload"></i> ' . Text::_('MOD_JUNEWS_IMAGE_UPLOAD') . '</a>';

		return implode("\n", $html);
	}
}