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

require ModuleHelper::getLayoutPath('mod_junewsultra', $params->get('template'));