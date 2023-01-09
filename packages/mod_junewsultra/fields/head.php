<?php
/**
 * JUNewsUltra Pro
 *
 * @package          Joomla.Site
 * @subpackage       mod_junewsultra
 *
 * @author           Denys Nosov, denys@joomla-ua.org
 * @copyright        2007-2023 (C) Joomla! Ukraine, https://joomla-ua.org. All rights reserved.
 * @license          GNU/GPL - https://www.gnu.org/copyleft/gpl.html
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

defined('JPATH_BASE') or die;

$doc   = Factory::getDocument();
$db    = Factory::getDBO();
$query = $db->getQuery(true);

$adm_url = str_replace('/administrator', '', Uri::base());
$tmpl    = $adm_url . 'modules/mod_junewsultra/fields/edittemplate.php?file=';

$query->select($db->quoteName([ 'params' ]));
$query->from($db->quoteName('#__modules'));
$query->where($db->quoteName('id') . ' = ' . $db->quote((int) $_GET[ 'id' ]));
$db->setQuery($query);
$rows = $db->loadResult();

$curent_tmp = json_decode($rows, true);

if(isset($curent_tmp[ 'template' ]))
{
	$tmpl_link = $tmpl . $curent_tmp[ 'template' ] . '.php';

	$snipets = '
    jQuery.noConflict();
    (function($)
	{
        $(function()
		{
            // Change template
            $("#jform_params_template").bind("change", function () {
                var tpl = $(this).val();
                if(tpl) {
                    $("#change_tmp").attr({href: "' . $tmpl . '"+tpl+".php"});
                    $("#change_tmp .edit-template-now").remove();
                    $("#change_tmp").append(" <span style=\"color: #750000;\" class=\"edit-template-now\">' . Text::_('MOD_JUNEWS_EDIT_TEMPLATE') . '</span>");
                }
                return false;
            });
            $("#jform_params_template").css("float","left");
            $("#jform_params_template_chzn").after("<a class=\"modal btn\" id=\"change_tmp\" style=\"margin: 0 0 0 10px; \" href=\"' . $tmpl_link . '\" rel=\"{handler: \'iframe\', size: {x: 1000, y: 700}}\" title=\"' . Text::_('MOD_JUNEWS_TEMPLATE_BUTTON') . '\"><span class=\"icon-cog\"></span></a>");

			$("p.readmore a").addClass("btn");

            SqueezeBox.initialize({});
			SqueezeBox.assign($$(\'a#change_tmp\'), {
			    parse: \'rel\'
    		});

            // Placeholder
            $("#jform_params_rmtext").attr({placeholder: "' . Text::_('MOD_JUNEWS_READ_MORE_TITLE') . '"});
            $("#jform_params_text_all_in, #jform_params_text_all_in2, #jform_params_text_all_in12, #jform_params_text_all_in22").attr({placeholder: "' . Text::_('MOD_JUNEWS_ALL_NEWS_TITLE') . '"});
        });
    })(jQuery);
';

	$doc->addScriptDeclaration($snipets);
}

if(version_compare(JVERSION, '4.0.0', '<'))
{
	HTMLHelper::_('jquery.framework');
}

$doc->addHeadLink($adm_url . 'modules/mod_junewsultra/assets/css/junewsultra.css?v=6', 'preload', 'rel', [ 'as' => 'style' ]);