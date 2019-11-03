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
use Joomla\CMS\Language\Text;

defined('JPATH_PLATFORM') or die;

class JFormFieldIntegration extends FormField
{
	protected $type = 'Integration';

	/**
	 *
	 * @return string
	 *
	 * @since 6.0
	 */
	protected function getInput()
	{
		$html = [];

		$class = ($this->element[ 'class' ] ? ' class="radio ' . $this->element[ 'class' ] . '"' : ' class="radio"');
		$path  = ($this->element[ 'path' ] ? JPATH_SITE . $this->element[ 'path' ] : '');

		if(!file_exists($path))
		{
			$tips   = ' <sup class="label label-inverse">' . Text::_('MOD_JUNEWS_NOTINSTALL') . '</sup>';
			$html[] = '<fieldset id="' . $this->id . '"' . $class . '>' . $tips . '</fieldset>';
		}
		else
		{
			$html[] = '<fieldset id="' . $this->id . '"' . $class . '>';

			$options = $this->getOptions();

			foreach($options as $i => $option)
			{
				$checked = ((string) $option->value === (string) $this->value) ? ' checked="checked"' : '';
				$class   = !empty($option->class) ? ' class="' . $option->class . '"' : '';
				$onclick = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';

				$html[] = '<input type="radio" id="' . $this->id . $i . '" name="' . $this->name . '" value="' . htmlspecialchars($option->value, ENT_COMPAT) . '"' . $checked . $class . $onclick . '/>';
				$html[] = '<label for="' . $this->id . $i . '" id="' . $this->id . $i . '">' . Text::alt($option->text, preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)) . '</label>';
			}

			$html[] = '</fieldset>';
		}

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

			$tmp          = JHtml::_('select.option', (string) $option[ 'value' ], trim((string) $option), 'value', 'text', (string) $option[ 'disabled' ] === 'true');
			$tmp->class   = (string) $option[ 'class' ];
			$tmp->onclick = (string) $option[ 'onclick' ];

			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}
}