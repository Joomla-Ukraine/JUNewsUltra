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

defined('_JEXEC') or die;

class JFormFieldNN_Toggler extends JFormField
{
	public $type = 'Toggler';

	protected function getLabel()
	{
		return '';
	}

	protected function getInput()
	{
		$field = new nnFieldToggler;
		return $field->getInput($this->element->attributes());
	}
}

class nnFieldToggler
{
	var $_version = 'ju_201211';

	function getInput($params)
	{
		$this->params = $params;

		$option = JFactory::getApplication()->input->get('option');

		// do not place toggler stuff on JoomFish pages
		if ($option == 'com_joomfish') {
			return '';
		}

		$param = $this->def('param');
		$value = $this->def('value');
		$nofx = $this->def('nofx');
		$method = $this->def('method');
		$div = $this->def('div', 0);

		JHtml::_('jquery.framework');
		JFactory::getDocument()->addScript(JURI::root( true ).'/modules/mod_junewsultra/assets/js/script30.js?v=' . $this->_version);
		JFactory::getDocument()->addScript(JURI::root(true) . '/modules/mod_junewsultra/assets/js/toggler30.js?v=' . $this->_version);

		$param = preg_replace('#^\s*(.*?)\s*$#', '\1', $param);
		$param = preg_replace('#\s*\|\s*#', '|', $param);

		$html = array();
		if ($param != '') {
			$param = preg_replace('#[^a-z0-9-\.\|\@]#', '_', $param);
			$param = str_replace('@', '_', $param);
			$set_groups = explode('|', $param);
			$set_values = explode('|', $value);
			$ids = array();
			foreach ($set_groups as $i => $group) {
				$count = $i;
				if ($count >= count($set_values)) {
					$count = 0;
				}
				$value = explode(',', $set_values[$count]);
				foreach ($value as $val) {
					$ids[] = $group . '.' . $val;
				}
			}

			if (!$div) {
				$html[] = '</div></div>';
			}

			$html[] = '<div id="' . rand(1000000, 9999999) . '___' . implode('___', $ids) . '" class="nntoggler';
			if ($nofx) {
				$html[] = ' nntoggler_nofx';
			}
			if ($method == 'and') {
				$html[] = ' nntoggler_and';
			}
			$html[] = '">';

			if (!$div) {
				$html[] = '<div><div>';
			}


		} else {
			$html[] = '</div>';
		}

		return implode('', $html);
	}

	private function def($val, $default = '')
	{
		return (isset($this->params[$val]) && (string) $this->params[$val] != '') ? (string) $this->params[$val] : $default;
	}
}
