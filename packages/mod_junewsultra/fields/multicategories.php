<?php
/**
 * JUNewsUltra Pro
 *
 * @package          Joomla.Site
 * @subpackage       mod_junewsultra
 *
 * @author           Denys Nosov, denys@joomla-ua.org
 * @copyright        2007-2021 (C) Joomla! Ukraine, http://joomla-ua.org. All rights reserved.
 * @license          GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

defined('JPATH_PLATFORM') or die;

FormHelper::loadFieldClass('list');

class JFormFieldMultiCategories extends JFormFieldList
{
	protected $type = 'MultiCategories';

	public $options = [];

	/**
	 *
	 * @return string
	 *
	 * @since 3.0
	 */
	protected function getInput()
	{
		$html = [];
		$attr = $this->element[ 'class' ] ? ' class="' . $this->element[ 'class' ] . '"' : '';

		if((string) $this->element[ 'readonly' ] === 'true' || (string) $this->element[ 'disabled' ] === 'true')
		{
			$attr .= ' disabled="disabled"';
		}

		$attr .= $this->element[ 'size' ] ? ' size="' . (int) $this->element[ 'size' ] . '"' : '';
		$attr .= $this->multiple ? ' multiple="multiple"' : '';
		$attr .= $this->element[ 'onchange' ] ? ' onchange="' . $this->element[ 'onchange' ] . '"' : '';
		$attr .= ' size="20"';

		$options = $this->getOptions();

		if((string) $this->element[ 'readonly' ] === 'true')
		{
			$html[] = HTMLHelper::_('select.genericlist', $options, '', trim($attr), 'value', 'text', $this->value, $this->id);
			$html[] = '<input type="hidden" name="' . $this->name . '" value="' . $this->value . '"/>';

		}
		else
		{
			if(isset($options[ 0 ]) != '')
			{
				$html[] = HTMLHelper::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value, $this->id);
			}
			else
			{
				return '<select style="display:none"></select><strong style="line-height: 2.6em">Component not installed or any categories are available.</strong>';
			}
		}

		return implode($html);
	}

	protected function getOptions()
	{
		$sql     = $this->element[ 'sql' ] ? : '';
		$dbtable = $this->element[ 'dbtable' ] ? : '';

		$db       = JFactory::getDBO();
		$tables   = $db->getTableList();
		$dbprefix = $db->getPrefix();

		if(in_array($dbprefix . $dbtable, $tables))
		{
			$db->setQuery($sql);
			$results = $db->loadObjectList();
		}
		else
		{
			$results = [];
		}

		if(count($results))
		{
			$temp_options = [];

			foreach($results as $item)
			{
				switch($dbtable)
				{
					case 'easyblog_category':
						$temp_options[] = [
							$item->id,
							$item->title,
							$item->parent_id
						];
						break;

					case 'icagenda_category':
						$temp_options[] = [ $item->id, $item->title, '' ];
						break;

					case 'mt_cats':
						$temp_options[] = [
							$item->cat_id,
							$item->cat_name,
							$item->cat_parent
						];
						break;

					default:
						$temp_options[] = [
							$item->id,
							$item->name,
							$item->parent
						];
						break;
				}
			}

			$this->options[] = HTMLHelper::_('select.option', '0', JText::_('JALL'));

			foreach($temp_options as $option)
			{
				if($option[ 2 ] == 0)
				{
					$this->options[] = HTMLHelper::_('select.option', $option[ 0 ], $option[ 1 ]);
					$this->recursive_options($temp_options, 1, $option[ 0 ]);
				}
			}

			return $this->options;
		}

		return $this->options;
	}

	public function bind($array, $ignore = '')
	{
		if(array_key_exists('field-name', $array) && is_array($array[ 'field-name' ]))
		{
			$array[ 'field-name' ] = implode(',', $array[ 'field-name' ]);
		}

		return parent::bind($array, $ignore);
	}

	public function recursive_options($temp_options, $level, $parent)
	{
		foreach($temp_options as $option)
		{
			if($option[ 2 ] == $parent)
			{
				$level_string    = str_repeat('- - ', $level);
				$this->options[] = HTMLHelper::_('select.option', $option[ 0 ], $level_string . $option[ 1 ]);
				$this->recursive_options($temp_options, $level + 1, $option[ 0 ]);
			}
		}
	}
}