<?php
/**
 * JUNewsUltra Pro
 *
 * @package          Joomla.Site
 * @subpackage       mod_junewsultra
 *
 * @author           Denys Nosov, denys@joomla-ua.org
 * @copyright        2007-2025 (C) Joomla! Ukraine, https://joomla-ua.org. All rights reserved.
 * @license          GNU/GPL - https://gnu.org/copyleft/gpl.html
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;

$app       = Factory::getApplication();
$menu      = $app->getMenu();
$doc       = $app->getDocument();
$view      = $app->input->getCmd('view');
$option    = $app->input->getCmd('option');
$component = $params->get('component', 'com_content') ? trim($params->get('component', 'com_content')) : '';

if($params->get('only_article', 0) == 1 && !($option === 'com_content' && $view === 'article'))
{
	return;
}

require_once __DIR__ . '/Helper.php';
require_once __DIR__ . '/helper/' . $component . '.php';

$helper = new Helper();
$object = new $component;

$list = $object->getList($params, [
	'count'               => (int) $params->get('count', '5'),
	'count_skip'          => (int) $params->get('count_skip', '0'),
	'featured'            => $params->get('show_featured'),
	'sourcetext'          => $params->get('sourcetext', 0),
	'show_title'          => $params->get('show_title', 1),
	'show_author'         => $params->get('juauthor'),
	'show_hits'           => $params->get('showHits'),
	'show_date'           => $params->get('show_date'),
	'show_intro'          => $params->get('show_intro'),
	'show_full'           => $params->get('show_full', 0),
	'show_cat'            => $params->get('showcat'),
	'show_tags'           => $params->get('showtags'),
	'show_rating'         => $params->get('showRating'),
	'data_format'         => $params->get('data_format'),
	'date_day'            => $params->get('df_d'),
	'date_month'          => $params->get('df_m'),
	'date_year'           => $params->get('df_y'),
	'li'                  => $params->get('li'),
	'lmttext'             => $params->get('lmttext'),
	'cleartag'            => $params->get('clear_tag'),
	'introtext_limit'     => (int) $params->get('introtext_limit'),
	'end_limit_introtext' => $params->get('end_limit_introtext', '…'),
	'allowed_intro_tags'  => $params->get('allowed_intro_tags') ? trim($params->get('allowed_intro_tags')) : '',
	'li_full'             => $params->get('li_full'),
	'lmttext_full'        => $params->get('lmttext_full'),
	'clear_tag_full'      => $params->get('clear_tag_full'),
	'fulltext_limit'      => (int) $params->get('fulltext_limit'),
	'end_limit_fulltext'  => $params->get('end_limit_fulltext', '…'),
	'allowed_full_tags'   => $params->get('allowed_full_tags') ? trim($params->get('allowed_full_tags')) : '',
	'show_image'          => $params->get('pik'),
	'image_thumb'         => ltrim($params->get('image_thumb', 'img'), '/'),
	'introfulltext'       => $params->get('introfulltext', 0),
	'defaultimg'          => $params->get('defaultimg', 1),
	'image_source'        => $params->get('image_source', 0),
	'w'                   => (int) $params->get('imageWidth'),
	'h'                   => (int) $params->get('imageHeight'),
	'thumb_width'         => (int) $params->get('thumb_width'),
	'noimage'             => $params->get('noimage'),
	'imglink'             => $params->get('imglink'),
	'link_enabled'        => $params->get('link_enabled', 1),
	'zoomcrop'            => (int) $params->get('zoomcrop', 1),
	'zoomcrop_params'     => $params->get('zoomcrop_params', 1),
	'auto_zoomcrop'       => (int) $params->get('auto_zoomcrop'),
	'cropaspect'          => str_replace(',', '.', (int) $params->get('cropaspect')),
	'zoomcropbg'          => str_replace('#', '', $params->get('zoomcropbg')),
	'farcrop_params'      => $params->get('farcrop_params', 1),
	'farcrop'             => (int) $params->get('farcrop', 0),
	'farcropbg'           => str_replace('#', '', $params->get('farcropbg')),
	'q'                   => $params->get('q', '75'),
	'f'                   => $params->get('img_ext', 'jpg'),
	'usewebp'             => $params->get('usewebp', '0'),
	'usegd2webp'          => $params->get('usegd2webp', '0'),
	'usesrcset'           => $params->get('usesrcset', '0'),
	'src_picture'         => $params->get('src_picture'),
	'youtube_img_show'    => $params->get('youtube_img_show', 1),
	'gallery'             => $params->get('gallery', 1)
]);

if($params->get('empty_mod', 0) == 0 && (is_countable($list) && count($list) == 0))
{
	return;
}

$helper->loadBS($params);
$helper->loadCSS($params);

$layoutpath = ModuleHelper::getLayoutPath('mod_junewsultra', $params->get('template'));

if($params->get('all_in') == 1)
{
	if($params->get('custom_heading') == 1)
	{
		$heading      = $params->get('text_all_in') ? trim($params->get('text_all_in')) : '';
		$heading_link = $params->get('link_all_in') ? trim($params->get('link_all_in')) : '';
	}
	else
	{
		$text_all_in12 = $params->get('text_all_in12') ? trim($params->get('text_all_in12')) : '';
		$heading       = ($text_all_in12 ? : Route::_($menu->getItem($params->get('link_menuitem'))->title));
		$heading_link  = Route::_($menu->getItem($params->get('link_menuitem'))->link . '&amp;Itemid=' . $params->get('link_menuitem'));
	}

	$heading = str_replace([ '[', ']' ], [ '<', '>' ], $heading);

	if($heading_link)
	{
		$heading_link = '<a ' . ($params->get('class_all_inhref') ? ' class="' . $params->get('class_all_inhref') . '" ' : '') . 'href="' . $heading_link . '">' . $heading . '</a>';
	}
	else
	{
		$heading_link = $heading;
	}

	$item_heading = $params->get('item_heading') ? trim($params->get('item_heading')) : '';
	$class_all_in = $params->get('class_all_in') ? trim($params->get('class_all_in')) : '';
	$read_all     = '<' . $item_heading . ($class_all_in ? ' class="' . $class_all_in . '"' : '') . '>' . $heading_link . '</' . $item_heading . '>';
}

if($params->get('all_in2') == 1)
{
	if($params->get('custom_heading2') == 1)
	{
		$heading2      = $params->get('text_all_in2') ? trim($params->get('text_all_in2')) : '';
		$heading_link2 = $params->get('link_all_in2') ? trim($params->get('link_all_in2')) : '';
	}
	else
	{
		$text_all_in22 = $params->get('text_all_in22') ? trim($params->get('text_all_in22')) : '';

		$heading2      = '';
		$heading_link2 = '';
		if($text_all_in22)
		{
			$heading2 = $text_all_in22;
		}
		elseif($params->get('link_menuitem2'))
		{
			$heading2      = Route::_($menu->getItem($params->get('link_menuitem2'))->title);
			$heading_link2 = Route::_($menu->getItem($params->get('link_menuitem2'))->link . '&amp;Itemid=' . $params->get('link_menuitem2'));
		}
	}

	$heading2      = str_replace([ '[', ']' ], [ '<', '>' ], $heading2);
	$item_heading2 = $params->get('item_heading2') ? trim($params->get('item_heading2')) : '';
	$titletag2     = explode('_', $item_heading2);
	$_tag_open2    = '';
	$_tag_close2   = '';

	if(isset($titletag2[ 1 ]))
	{
		$_tag_open2  = '<' . $titletag2[ 1 ] . '>';
		$_tag_close2 = '</' . $titletag2[ 1 ] . '>';
	}

	if($heading_link2)
	{
		$heading_link2 = '<a ' . ($params->get('class_all_inhref2') ? 'class="' . $params->get('class_all_inhref2') . '" ' : '') . 'href="' . $heading_link2 . '">' . $_tag_open2 . $heading2 . $_tag_close2 . '</a>';
	}
	else
	{
		$heading_link2 = $heading2;
	}

	$class_all_in2 = $params->get('class_all_in2') ? trim($params->get('class_all_in2')) : '';
	$read_all2     = '<' . $titletag2[ 0 ] . ($class_all_in2 ? ' class="' . $class_all_in2 . '"' : '') . '>' . $heading_link2 . '</' . $titletag2[ 0 ] . '>';
}

if($params->get('all_in') == 1 && $params->get('all_in_position') == 0 && $read_all)
{
	echo $read_all;
}

if($params->get('all_in2') == 1 && $params->get('all_in_position2') == 0 && $read_all2)
{
	echo $read_all2;
}

require $layoutpath;

if($params->get('all_in') == 1 && $params->get('all_in_position') == 1 && $read_all)
{
	echo $read_all;
}

if($params->get('all_in2') == 1 && $params->get('all_in_position2') == 1 && $read_all2)
{
	echo $read_all2;
}