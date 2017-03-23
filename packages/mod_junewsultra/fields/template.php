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


class JFormFieldTemplate extends JFormField
{

	protected $type = 'Template';

	protected function getInput()
	{
        if(!isset($_GET["id"])){
          echo JText::_('MOD_JUNEWS_NOT_EDIT_TEMPLATE');
          return;
        }

		JHtml::_('behavior.modal', 'a.modal');

		$db	= JFactory::getDBO();
		$db->setQuery(
			'SELECT params' .
			' FROM #__modules' .
			' WHERE id = '.(int) $_GET["id"]
		);
		$rows = $db->loadResult();

        if (preg_match("#\"template\":\"_:(.*?)\"#is",$rows,$ok)):
            if($ok[1] == 1){
                $tmpl = 'default';
            } else {
                $tmpl = $ok[1];
            }
        else:
            $tmpl = 'default';
        endif;

		$html	= array();
		$link	= str_replace('/administrator', '', JURI::base()).'modules/mod_junewsultra/fields/edittemplate.php?file='.$tmpl.'.php';

		if ($error = $db->getErrorMsg()) {
			JError::raiseWarning(500, $error);
		}

        if($_GET["id"] ){
       		$html[] = '<a class="modal btn"  href="'.$link.'" rel="{handler: \'iframe\', size: {x: 1000, y: 650}}"><i class="class="icon-cog""></i> '.JText::_('MOD_JUNEWS_TEMPLATE_BUTTON').'</a>';
        } else {
		    $html[] = JText::_('MOD_JUNEWS_NOT_EDIT_TEMPLATE');
        }

		if (0 == (int)$this->value) {
			$value = '';
		} else {
			$value = (int)$this->value;
		}

		$class = '';
		if ($this->required) {
			$class = ' class="required modal-value"';
		}

		$html[] = '<input type="hidden" id="'.$this->id.'_id"'.$class.' name="'.$this->name.'" value="'.$value.'" />';

		return implode("\n", $html);
	}
}