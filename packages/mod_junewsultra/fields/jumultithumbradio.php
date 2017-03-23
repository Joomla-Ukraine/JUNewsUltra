<?php
/**
 * JUNewsUltra Pro
 *
 * @version 	6.x
 * @package 	UNewsUltra Pro
 * @author 		Denys D. Nosov (denys@joomla-ua.org)
 * @copyright 	(C) 2007-2015 by Denys D. Nosov (http://joomla-ua.org)
 * @license 	GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 *
 **/

defined('JPATH_PLATFORM') or die;

class JFormFieldJUMultiThumbRadio extends JFormField
{
	protected $type = 'JUMultiThumbRadio';

	protected function getInput()
	{
		$html = array();

		$class = $this->element['class'] ? ' class="radio ' . (string) $this->element['class'] . '"' : ' class="radio"';

        $jumultithumb   = JPATH_SITE . '/plugins/content/jumultithumb_gallery/jumultithumb_gallery.php';
        if (!file_exists($jumultithumb)) {
            $tips       = ' <sup class="label label-inverse">'. JText::_('MOD_JUNEWS_NOTINSTALL') .'</sup>';

            $html[] = '<fieldset id="' . $this->id . '"' . $class . '>'. $tips .'</fieldset>';
        } else {
		   	$html[] = '<fieldset id="' . $this->id . '"' . $class . '>';

			$options = $this->getOptions();

			foreach ($options as $i => $option)
			{
				$checked        = ((string) $option->value == (string) $this->value) ? ' checked="checked"' : '';
				$class          = !empty($option->class) ? ' class="' . $option->class . '"' : '';

				$onclick = !empty($option->onclick) ? ' onclick="' . $option->onclick . '"' : '';

				$html[] = '<input type="radio" id="' . $this->id . $i . '" name="' . $this->name . '"' . ' value="'
					. htmlspecialchars($option->value, ENT_COMPAT, 'UTF-8') . '"' . $checked . $class . $onclick .'/>';

				$html[] = '<label for="' . $this->id . $i . '" id="' . $this->id . $i . '">'
					. JText::alt($option->text, preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)) .'</label>';

			}

		   	$html[] = '</fieldset>';
        }

		return implode($html);
	}

	protected function getOptions()
	{
		$options = array();

		foreach ($this->element->children() as $option)
		{
			if ($option->getName() != 'option')
			{
				continue;
			}

			$tmp = JHtml::_(
				'select.option', (string) $option['value'], trim((string) $option), 'value', 'text',
				((string) $option['disabled'] == 'true')
			);

			$tmp->class = (string) $option['class'];

			$tmp->onclick = (string) $option['onclick'];

			$options[] = $tmp;
		}

		reset($options);

		return $options;
	}
}
