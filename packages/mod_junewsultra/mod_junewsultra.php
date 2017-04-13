<?php
/**
 * JUNewsUltra Pro
 *
 * @version          6.x
 * @package          UNewsUltra Pro
 * @author           Denys D. Nosov (denys@joomla-ua.org)
 * @copyright    (C) 2007-2017 by Denys D. Nosov (http://joomla-ua.org)
 * @license          GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 *
 **/

defined('_JEXEC') or die;

$app      = JFactory::getApplication('site');
$document = JFactory::getDocument();

require_once(JPATH_SITE . '/modules/mod_junewsultra/lib/julib.php');
require_once(JPATH_SITE . '/libraries/julib/image.php');

$junews = array(
	'count'       => (int) $params->get('count', '5'),
	'count_skip'  => (int) $params->get('count_skip', '0'),
	'featured'    => $params->get('show_featured'),
	'sourcetext'  => $params->get('sourcetext', 0),
	'show_title'  => $params->get('show_title', 1),
	'show_author' => $params->def('juauthor'),
	'show_hits'   => $params->get('showHits'),
	'show_date'   => $params->get('show_date'),
	'show_intro'  => $params->get('show_intro'),
	'show_full'   => $params->get('show_full', 0),
	'show_cat'    => $params->get('showcat'),
	'show_rating' => $params->get('showRating'),

	'data_format' => $params->get('data_format'),
	'date_day'    => $params->get('df_d'),
	'date_month'  => $params->get('df_m'),
	'date_year'   => $params->get('df_y'),

	'li'                  => $params->get('li'),
	'lmttext'             => $params->get('lmttext'),
	'cleartag'            => $params->get('clear_tag'),
	'introtext_limit'     => intval($params->get('introtext_limit')),
	'end_limit_introtext' => $params->get('end_limit_introtext', '...'),
	'allowed_intro_tags'  => trim($params->get('allowed_intro_tags')),
	'li_full'             => $params->get('li_full'),
	'lmttext_full'        => $params->get('lmttext_full'),
	'clear_tag_full'      => $params->get('clear_tag_full'),
	'fulltext_limit'      => intval($params->get('fulltext_limit')),
	'end_limit_fulltext'  => $params->get('end_limit_fulltext', '...'),
	'allowed_full_tags'   => trim($params->get('allowed_full_tags')),

	'show_image'    => $params->def('pik'),
	'introfulltext' => $params->def('introfulltext', 0),
	'defaultimg'    => $params->def('defaultimg', 1),
	'image_source'  => $params->def('image_source', 0),
	'w'             => intval($params->get('imageWidth')),
	'h'             => intval($params->get('imageHeight')),
	'thumb_width'   => intval($params->get('thumb_width')),
	'noimage'       => $params->def('noimage'),
	'imglink'       => $params->def('imglink'),
	'link_enabled'  => $params->get('link_enabled', 1),

	'sx'              => intval($params->get('sx')),
	'sy'              => intval($params->get('sy')),
	'sw'              => intval($params->get('sw')),
	'sh'              => intval($params->get('sh')),
	'zoomcrop'        => intval($params->get('zoomcrop', 1)),
	'zoomcrop_params' => $params->get('zoomcrop_params', 1),
	'auto_zoomcrop'   => intval($params->get('auto_zoomcrop')),
	'cropaspect'      => str_replace(',', '.', intval($params->get('cropaspect'))),
	'zoomcropbg'      => str_replace('#', '', $params->def('zoomcropbg')),
	'farcrop_params'  => $params->get('farcrop_params', 1),
	'farcrop'         => intval($params->get('farcrop', 0)),
	'farcropbg'       => str_replace('#', '', $params->def('farcropbg')),
	'q'               => $params->get('q', '75'),
	'f'               => $params->def('img_ext', 'jpg'),

	'usesrcset'          => $params->def('usesrcset', '0'),
	'srcsetviewport'     => $params->def('srcsetviewport', '480'),
	'srcsetpixeldensity' => $params->def('srcsetpixeldensity', '1'),

	'youtube_img_show' => $params->def('youtube_img_show', 1),
	'gallery'          => $params->def('gallery', 1),
	'multicat'         => $params->def('contentmulticategories', 0)
);

$component = trim($params->def('component', 'com_content'));

require_once(__DIR__ . '/helper.php');
require_once(__DIR__ . '/helper/' . $component . '.php');

$object = new $component;
$list   = $object->getList($params, $junews);

if($params->get('empty_mod', 0) == 0) if(count($list) == 0) return;

$layoutpath = JModuleHelper::getLayoutPath('mod_junewsultra', $params->def('template'));

if($params->def('jquery') == 1)
{
	JHtml::_('jquery.framework');
}

if($params->def('bootstrap_js') == 1)
{
	JHtml::_('bootstrap.framework');
}

if($params->def('bootstrap_css') == 1)
{
	$lang      = JFactory::getLanguage();
	$direction = ($lang->isRTL() ? 'rtl' : 'ltr');
	JHtmlBootstrap::loadCss($includeMaincss = true, $direction);
}

if($params->get('cssstyle') == 1)
{
	$tpl = explode(":", $params->def('template'));

	if($tpl[0] == '_')
	{
		$jtpl = $app->getTemplate();
	}
	else
	{
		$jtpl = $tpl[0];
	}

	if(is_file(JPATH_SITE . '/modules/mod_junewsultra/tmpl/' . $tpl[1] . '/css/style.css'))
	{
		$css = 'modules/mod_junewsultra/tmpl/' . $tpl[1] . '/css/style.css';
		$document->addStylesheet(JURI::base() . $css);
	}

	if(is_file(JPATH_SITE . '/templates/' . $jtpl . '/html/mod_junewsultra/' . $tpl[1] . '/css/style.css'))
	{
		$css = 'templates/' . $jtpl . '/html/mod_junewsultra/' . $tpl[1] . '/css/style.css';
		$document->addStylesheet(JURI::base() . $css);
	}
}

if(file_exists($layoutpath))
{
	if($params->def('all_in') == 1)
	{
		if($params->def('custom_heading') == 1)
		{
			$heading      = trim($params->get('text_all_in'));
			$heading_link = trim($params->get('link_all_in'));
		}
		else
		{
			$application = JFactory::getApplication();
			$menu        = $application->getMenu();

			$text_all_in12 = trim($params->get('text_all_in12'));
			$heading       = ($text_all_in12 ? $text_all_in12 : JRoute::_($menu->getItem($params->get('link_menuitem'))->title));
			$heading_link  = JRoute::_($menu->getItem($params->get('link_menuitem'))->link . '&amp;Itemid=' . $params->get('link_menuitem'));
		}

		$heading = str_replace('[', '<', $heading);
		$heading = str_replace(']', '>', $heading);

		if($heading_link)
		{
			$heading_link = '<a ' . ($params->get('class_all_inhref') ? 'class="' . $params->get('class_all_inhref') . '" ' : '') . 'href="' . $heading_link . '">' . $heading . '</a>';
		}
		else
		{
			$heading_link = $heading;
		}

		$item_heading = trim($params->get('item_heading'));
		$class_all_in = trim($params->get('class_all_in'));
		$read_all     = '<' . $item_heading . ($class_all_in ? ' class="' . $class_all_in . '"' : '') . '>' . $heading_link . '</' . $item_heading . '>';
	}

	if($params->def('all_in2') == 1)
	{
		if($params->def('custom_heading2') == 1)
		{
			$heading2      = trim($params->get('text_all_in2'));
			$heading_link2 = trim($params->get('link_all_in2'));
		}
		else
		{
			$application = JFactory::getApplication();
			$menu        = $application->getMenu();

			$text_all_in22 = trim($params->get('text_all_in22'));
			$heading2      = ($text_all_in22 ? $text_all_in22 : JRoute::_($menu->getItem($params->get('link_menuitem2'))->title));
			$heading_link2 = JRoute::_($menu->getItem($params->get('link_menuitem2'))->link . '&amp;Itemid=' . $params->get('link_menuitem2'));
		}

		$heading2 = str_replace('[', '<', $heading2);
		$heading2 = str_replace(']', '>', $heading2);

		$item_heading2 = trim($params->get('item_heading2'));
		$titletag2     = explode("_", $item_heading2);
		if($titletag2[1])
		{
			$_tag_open2  = '<' . $titletag2[1] . '>';
			$_tag_close2 = '</' . $titletag2[1] . '>';
		}
		else
		{
			$_tag_open2  = '';
			$_tag_close2 = '';
		}

		$class_all_inhref2 = trim($params->get('class_all_inhref2'));

		if($heading_link2)
		{
			$heading_link2 = '<a ' . ($params->get('class_all_inhref2') ? 'class="' . $params->get('class_all_inhref2') . '" ' : '') . 'href="' . $heading_link2 . '">' . $_tag_open2 . $heading2 . $_tag_close2 . '</a>';
		}
		else
		{
			$heading_link2 = $heading2;
		}

		$class_all_in2 = trim($params->get('class_all_in2'));

		$read_all2 = '<' . $titletag2[0] . ($class_all_in2 ? ' class="' . $class_all_in2 . '"' : '') . '>' . $heading_link2 . '</' . $titletag2[0] . '>';
	}

	if($params->def('all_in') == 1 && $params->def('all_in_position') == 0) echo $read_all;

	if($params->def('all_in2') == 1 && $params->def('all_in_position2') == 0) echo $read_all2;

	require($layoutpath);

	if($params->def('all_in') == 1 && $params->def('all_in_position') == 1) echo $read_all;

	if($params->def('all_in2') == 1 && $params->def('all_in_position2') == 1) echo $read_all2;

}
else
{
	$tpl = explode(":", $params->def('template'));

	echo "<strong>Template <span style=\"color: green;\">$tpl</span> do is not found!</strong><br />Please, upload new template to <em>modules/mod_junewsultra/tmpl</em> or <em>templates/$tpl[0]/html/mod_junewsultra/</em> folder or select other template from back-end!";
}