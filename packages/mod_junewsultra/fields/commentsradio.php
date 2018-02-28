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

class JFormFieldCommentsRadio extends JFormField
{
	protected $type = 'CommentsRadio';

	/**
	 *
	 * @return string
	 *
	 * @since version
	 */
	protected function getInput()
	{
		$html = array();

		$class = $this->element['class'] ? ' class="radio ' . (string) $this->element['class'] . '"' : ' class="radio"';

		$html[] = '<fieldset id="' . $this->id . '"' . $class . '>';

		$options = $this->getOptions();

		foreach ($options as $i => $option)
		{
			$checked = ((string) $option->value == (string) $this->value) ? ' checked="checked"' : '';
			$class   = !empty($option->class) ? ' class="' . $option->class . '"' : '';

			$commets_system = htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8');
			$comments       = JPATH_SITE . '/components/com_' . $commets_system . '/' . $commets_system . '.php';

			if (!file_exists($comments))
			{
				$disabled = ' disabled="disabled"';
				$color    = 'color: #999;';
				$tips     = ' <sup class="label label-inverse">' . JText::_('MOD_JUNEWS_NOTINSTALL') . '</sup>';
				$check    = '';
			}
			else
			{
				$disabled = '';
				$color    = '';
				$tips     = '';
				$check    = $checked;
			}

			$onclick = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';

			$html[] = '<input type="radio" id="' . $this->id . $i . '" name="' . $this->name . '"' . ' value="'
				. htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8') . '"' . $check . $class . $onclick . $disabled . '/>';

			$html[] = '<label for="' . $this->id . $i . '" id="' . $this->id . $i . '" style="' . $color . '">'
				. JText::alt($option->text, preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)) . $tips . '</label>';

			$html[] = '<div style="clear: both;"></div>';
		}

		$html[] = '</fieldset>';

		return implode($html);
	}

	/**
	 *
	 * @return array
	 *
	 * @since version
	 */
	protected function getOptions()
	{
		$options = array();

		foreach ($this->element->children() as $option)
		{
			if ($option->getName() !== 'option')
			{
				continue;
			}

			$tmp = JHtml::_(
				'select.option', (string) $option['value'], trim((string) $option), 'value', 'text',
				(string) $option['disabled'] == 'true'
			);

			$tmp->class   = (string) $option['class'];
			$tmp->onclick = (string) $option['onclick'];

			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}
}