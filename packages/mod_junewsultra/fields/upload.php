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

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldUpload extends JFormField
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
		if (!isset($_GET[ 'id' ]))
		{
			return JText::_('MOD_JUNEWS_NOT_EDIT_TEMPLATE');
		}

		JHtml::_('behavior.modal', 'a.modal');

		$html = array();
		$link = str_replace('/administrator', '', JURI::base()) . 'modules/mod_junewsultra/fields/uploadimg.php';

		$html[] = '<a class="modal btn"  href="' . $link . '" rel="{handler: \'iframe\', size: {x: 330, y: 180}}"><i class="icon-upload"></i> ' . JText::_('MOD_JUNEWS_IMAGE_UPLOAD') . '</a>';

		return implode("\n", $html);
	}
}