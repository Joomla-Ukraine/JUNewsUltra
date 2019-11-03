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

use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

defined('JPATH_PLATFORM') or die;

class JFormFieldNN_Toggler extends FormField
{
	public $type = 'Toggler';

	/**
	 *
	 * @return string
	 *
	 * @since 6.0
	 */
	protected function getLabel()
	{
		return '';
	}

	/**
	 *
	 * @return string
	 *
	 * @throws \Exception
	 * @since 6.0
	 */
	protected function getInput()
	{
		$field = new nnFieldToggler;

		return $field->getInput($this->element->attributes());
	}
}

/**
 * @property  params
 * @property  params
 * @property  params
 */
class nnFieldToggler
{
	/**
	 * nnFieldToggler constructor.
	 */
	public function __construct()
	{
		$this->_version = date('dmy');
	}

	/**
	 * @param $params
	 *
	 * @return string
	 *
	 * @throws \Exception
	 * @since 6.0
	 */
	public function getInput($params)
	{
		$this->params = $params;
		$param        = $this->def('param');
		$value        = $this->def('value');
		$nofx         = $this->def('nofx');
		$method       = $this->def('method');
		$div          = $this->def('div', 0);

		HTMLHelper::_('jquery.framework');
		Factory::getDocument()->addScript(JUri::root(true) . '/modules/mod_junewsultra/assets/js/script30.js?v=' . $this->_version);
		Factory::getDocument()->addScript(JUri::root(true) . '/modules/mod_junewsultra/assets/js/toggler30.js?v=' . $this->_version);

		$param = preg_replace('#^\s*(.*?)\s*$#', '\1', $param);
		$param = preg_replace('#\s*\|\s*#', '|', $param);

		$html = [];
		if($param !== '')
		{
			$param      = preg_replace('#[^a-z0-9-\.\|\@]#', '_', $param);
			$param      = str_replace('@', '_', $param);
			$set_groups = explode('|', $param);
			$set_values = explode('|', $value);

			$ids = [];
			foreach($set_groups as $i => $group)
			{
				$count = $i;
				if($count >= count($set_values))
				{
					$count = 0;
				}

				$value = explode(',', $set_values[ $count ]);
				foreach($value as $val)
				{
					$ids[] = $group . '.' . $val;
				}
			}

			if(!$div)
			{
				$html[] = '</div></div>';
			}

			$html[] = '<div id="' . mt_rand(1000000, 9999999) . '___' . implode('___', $ids) . '" class="nntoggler';
			if($nofx)
			{
				$html[] = ' nntoggler_nofx';
			}

			if($method === 'and')
			{
				$html[] = ' nntoggler_and';
			}

			$html[] = '">';

			if(!$div)
			{
				$html[] = '<div><div>';
			}
		}
		else
		{
			$html[] = '</div>';
		}

		return implode('', $html);
	}

	/**
	 * @param string $val
	 * @param string $default
	 *
	 * @return string
	 *
	 * @since 6.0
	 */
	private function def($val, $default = '')
	{
		return (isset($this->params[ $val ]) && (string) $this->params[ $val ] !== '') ? (string) $this->params[ $val ] : $default;
	}
}
