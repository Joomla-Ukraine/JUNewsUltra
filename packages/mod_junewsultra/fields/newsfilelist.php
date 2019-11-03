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

use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\FormHelper;

defined('JPATH_PLATFORM') or die;

FormHelper::loadFieldClass('list');

class JFormFieldNewsFileList extends JFormFieldList
{
	public $type = 'NewsFileList';

	/**
	 *
	 * @return array
	 *
	 * @since 6.0
	 */
	protected function getOptions()
	{
		$options     = [];
		$filter      = (string) $this->element[ 'filter' ];
		$exclude     = (string) $this->element[ 'exclude' ];
		$stripExt    = (string) $this->element[ 'stripext' ];
		$hideNone    = (string) $this->element[ 'hide_none' ];
		$hideDefault = (string) $this->element[ 'hide_default' ];
		$path        = JPATH_ROOT . '/' . $this->element[ 'directory' ];

		if(!$hideNone)
		{
			$options[] = HTMLHelper::_('select.option', '-1', JText::alt('JOPTION_DO_NOT_USE', preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)));
		}

		if(!$hideDefault)
		{
			$options[] = HTMLHelper::_('select.option', '', JText::alt('JOPTION_USE_DEFAULT', preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)));
		}

		$files = Folder::files($path, $filter);
		if(is_array($files))
		{
			foreach($files as $file)
			{
				if($exclude && preg_match(chr(1) . $exclude . chr(1), $file))
				{
					continue;
				}

				if($stripExt)
				{
					$file = File::stripExt($file);
				}

				$options[] = HTMLHelper::_('select.option', $file, JText::_('MOD_JUNEWS_INTEGR_' . strtoupper($file)));
			}
		}

		return array_merge(parent::getOptions(), $options);
	}
}