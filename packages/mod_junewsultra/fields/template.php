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
use Joomla\CMS\HTML\HTMLHelper;
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

		if(version_compare(JVERSION, '4.0.0', '<'))
		{
			HTMLHelper::_('behavior.modal', 'a.modal');

			$db = JFactory::getDBO();
			$db->setQuery('SELECT params' . ' FROM #__modules' . ' WHERE id = ' . (int) $_GET[ 'id' ]);
			$rows = $db->loadResult();

			$tmpl = 'default';
			if(preg_match('#"template":"_:(.*?)"#is', $rows, $ok))
			{
				$tmpl = $ok[ 1 ];
				if($ok[ 1 ] == 1)
				{
					$tmpl = 'default';
				}
			}

			$html = [];
			$link = str_replace('/administrator', '', JUri::base()) . 'modules/mod_junewsultra/fields/edittemplate.php?file=' . $tmpl . '.php';

			$html[] = Text::_('MOD_JUNEWS_NOT_EDIT_TEMPLATE');
			if($_GET[ 'id' ])
			{
				$html[] = '<a class="modal btn"  href="' . $link . '" rel="{handler: \'iframe\', size: {x: 1000, y: 650}}"><i class="icon-cog"></i> ' . Text::_('MOD_JUNEWS_TEMPLATE_BUTTON') . '</a>';
			}

			$value = (int) $this->value;
			if((int) $this->value == 0)
			{
				$value = '';
			}

			$class = '';
			if($this->required)
			{
				$class = ' class="required modal-value"';
			}

			$html[] = '<input type="hidden" id="' . $this->id . '_id"' . $class . ' name="' . $this->name . '" value="' . $value . '" />';

			return implode("\n", $html);
		}
	}
}