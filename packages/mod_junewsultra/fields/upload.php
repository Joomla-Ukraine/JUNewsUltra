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

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldUpload extends JFormField
{

	protected $type = 'Upload';

	/**
	 *
	 * @return string|void
	 *
	 * @since 6.0
	 */
	protected function getInput()
	{
        if(!isset($_GET["id"])){
          echo JText::_('MOD_JUNEWS_NOT_EDIT_TEMPLATE');
          return;
        }

		JHtml::_('behavior.modal', 'a.modal');

		$html	= array();
		$link	= str_replace('/administrator', '', JURI::base()).'modules/mod_junewsultra/fields/uploadimg.php';

   		$html[] = '<a class="modal btn"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 330, y: 180}}"><i class="icon-upload"></i> '.JText::_('MOD_JUNEWS_IMAGE_UPLOAD').'</a>';

		return implode("\n", $html);
	}
}