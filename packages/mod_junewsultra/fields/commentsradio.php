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

use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

class JFormFieldCommentsRadio extends FormField
{
	protected $type = 'CommentsRadio';

	/**
	 *
	 * @return string
	 *
	 * @since 6.0
	 */
	protected function getInput()
	{
		$html    = [];
		$class   = $this->element[ 'class' ] ? ' class="radio ' . $this->element[ 'class' ] . '"' : ' class="radio"';
		$html[]  = '<fieldset id="' . $this->id . '"' . $class . '>';
		$options = $this->getOptions();

		foreach($options as $i => $option)
		{
			$checked        = ((string) $option->value === (string) $this->value) ? ' checked="checked"' : '';
			$class          = !empty($option->class) ? ' class="' . $option->class . '"' : '';
			$commets_system = htmlspecialchars($option->value, ENT_COMPAT);
			$comments       = JPATH_SITE . '/components/com_' . $commets_system . '/' . $commets_system . '.php';

			$check    = '';
			$disabled = '';
			$color    = '';
			$tips     = '';
			if(!file_exists($comments))
			{
				$disabled = ' disabled="disabled"';
				$color    = 'color: #999;';
				$tips     = ' <sup class="label label-inverse">' . Text::_('MOD_JUNEWS_NOTINSTALL') . '</sup>';

			}
			else
			{
				$check = $checked;
			}

			$onclick = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';

			$html[] = '<input type="radio" id="' . $this->id . $i . '" name="' . $this->name . '"' . ' value="' . htmlspecialchars($option->value, ENT_COMPAT) . '"' . $check . $class . $onclick . $disabled . '/>';
			$html[] = '<label for="' . $this->id . $i . '" id="' . $this->id . $i . '" style="' . $color . '">' . Text::alt($option->text, preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)) . $tips . '</label>';
			$html[] = '<div style="clear: both;"></div>';
		}

		$html[] = '</fieldset>';

		return implode($html);
	}

	/**
	 *
	 * @return array
	 *
	 * @since 6.0
	 */
	protected function getOptions()
	{
		$options = [];

		foreach($this->element->children() as $option)
		{
			if($option->getName() !== 'option')
			{
				continue;
			}

			$tmp          = HTMLHelper::_('select.option', (string) $option[ 'value' ], trim((string) $option), 'value', 'text', (string) $option[ 'disabled' ] === 'true');
			$tmp->class   = (string) $option[ 'class' ];
			$tmp->onclick = (string) $option[ 'onclick' ];

			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}
}