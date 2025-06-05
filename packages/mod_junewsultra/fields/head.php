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

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

defined('JPATH_BASE') or die;

$app     = Factory::getApplication();
$doc     = $app->getDocument();
$adm_url = str_replace('/administrator', '', Uri::base());

$doc->addHeadLink($adm_url . 'modules/mod_junewsultra/assets/css/junewsultra.css?v=6', 'preload', 'rel', [ 'as' => 'style' ]);