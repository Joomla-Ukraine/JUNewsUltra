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

defined('JPATH_BASE') or die;

use Joomla\CMS\Form\FormField;

class JFormFieldUpload extends FormField
{

	protected $type = 'Upload';

	/**
	 *
	 * @return string
	 *
	 * @since 6.0
	 */
	protected function getInput(): string
	{
		return 'Upload image to <code>media/mod_junewsultra</code> folder';
	}
}