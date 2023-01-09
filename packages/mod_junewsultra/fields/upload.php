<?php
/**
 * JUNewsUltra Pro
 *
 * @package          Joomla.Site
 * @subpackage       mod_junewsultra
 *
 * @author           Denys Nosov, denys@joomla-ua.org
 * @copyright        2007-2023 (C) Joomla! Ukraine, https://joomla-ua.org. All rights reserved.
 * @license          GNU/GPL - https://www.gnu.org/copyleft/gpl.html
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class JFormFieldUpload extends FormField
{

	protected $type = 'Upload';

	/**
	 *
	 * @return string
	 *
	 * @since 6.0
	 */
	protected function getInput()
	{
		if(!isset($_GET[ 'id' ]))
		{
			return Text::_('MOD_JUNEWS_NOT_EDIT_TEMPLATE');
		}

		if(version_compare(JVERSION, '4.0.0', '<'))
		{
			HTMLHelper::_('behavior.modal', 'a.modal');

			$html = [];
			$link = str_replace('/administrator', '', Uri::base()) . 'modules/mod_junewsultra/fields/uploadimg.php';

			$html[] = '<a class="modal btn"  href="' . $link . '" rel="{handler: \'iframe\', size: {x: 330, y: 180}}"><i class="icon-upload"></i> ' . Text::_('MOD_JUNEWS_IMAGE_UPLOAD') . '</a>';

			return implode("\n", $html);
		}

		return 'Upload image to <code>media/mod_junewsultra</code> folder';
	}
}