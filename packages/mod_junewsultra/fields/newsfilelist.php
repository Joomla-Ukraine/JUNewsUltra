<?php
/**
 * JUNewsUltra Pro
 *
 * @package          Joomla.Site
 * @subpackage       mod_junewsultra
 *
 * @author           Denys Nosov, denys@joomla-ua.org
 * @copyright        2007-2018 (C) Joomla! Ukraine, http://joomla-ua.org. All rights reserved.
 * @license          GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
JFormHelper::loadFieldClass('list');

class JFormFieldNewsFileList extends JFormFieldList
{
	public $type = 'NewsFileList';

	protected function getOptions()
	{
		$options     = array();
		$filter      = (string) $this->element['filter'];
		$exclude     = (string) $this->element['exclude'];
		$stripExt    = (string) $this->element['stripext'];
		$hideNone    = (string) $this->element['hide_none'];
		$hideDefault = (string) $this->element['hide_default'];

		$path = (string) $this->element['directory'];
		if (!is_dir($path))
		{
			$path = JPATH_ROOT . '/' . $path;
		}

		if (!$hideNone)
		{
			$options[] = JHtml::_('select.option', '-1', JText::alt('JOPTION_DO_NOT_USE', preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)));
		}

		if (!$hideDefault)
		{
			$options[] = JHtml::_('select.option', '', JText::alt('JOPTION_USE_DEFAULT', preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname)));
		}

		$files = JFolder::files($path, $filter);

		if (is_array($files))
		{
			foreach ($files as $file)
			{
				if ($exclude && preg_match(chr(1) . $exclude . chr(1), $file))
				{
					continue;
				}

				if ($stripExt)
				{
					$file = JFile::stripExt($file);
				}

				$options[] = JHtml::_('select.option', $file, JText::_('MOD_JUNEWS_INTEGR_' . strtoupper($file)));
			}
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}