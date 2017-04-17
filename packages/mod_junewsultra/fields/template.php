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

class JFormFieldTemplate extends JFormField
{

	protected $type = 'Template';

	/**
	 *
	 * @return string|void
	 *
	 * @since 6.0
	 */
	protected function getInput()
	{
		if (!isset($_GET["id"]))
		{
			return JText::_('MOD_JUNEWS_NOT_EDIT_TEMPLATE');
		}

		JHtml::_('behavior.modal', 'a.modal');

		$db = JFactory::getDBO();
		$db->setQuery(
			'SELECT params' .
			' FROM #__modules' .
			' WHERE id = ' . (int) $_GET["id"]
		);
		$rows = $db->loadResult();

		if (preg_match("#\"template\":\"_:(.*?)\"#is", $rows, $ok))
		{
			if ($ok[1] == 1)
			{
				$tmpl = 'default';
			}
			else
			{
				$tmpl = $ok[1];
			}
		}
		else
		{
			$tmpl = 'default';
		}

		$html = array();
		$link = str_replace('/administrator', '', JURI::base()) . 'modules/mod_junewsultra/fields/edittemplate.php?file=' . $tmpl . '.php';

		if ($error = $db->getErrorMsg())
		{
			JFactory::getApplication()->enqueueMessage($error, 'error');
		}

		if ($_GET["id"])
		{
			$html[] = '<a class="modal btn"  href="' . $link . '" rel="{handler: \'iframe\', size: {x: 1000, y: 650}}"><i class="class="icon-cog""></i> ' . JText::_('MOD_JUNEWS_TEMPLATE_BUTTON') . '</a>';
		}
		else
		{
			$html[] = JText::_('MOD_JUNEWS_NOT_EDIT_TEMPLATE');
		}

		if (0 == (int) $this->value)
		{
			$value = '';
		}
		else
		{
			$value = (int) $this->value;
		}

		$class = '';
		if ($this->required)
		{
			$class = ' class="required modal-value"';
		}

		$html[] = '<input type="hidden" id="' . $this->id . '_id"' . $class . ' name="' . $this->name . '" value="' . $value . '" />';

		return implode("\n", $html);
	}
}