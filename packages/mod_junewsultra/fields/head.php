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
use Joomla\Database\DatabaseInterface;

defined('JPATH_BASE') or die;

$app   = Factory::getApplication();
$doc   = $app->getDocument();
$db    = Factory::getContainer()->get(DatabaseInterface::class);
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
}

$doc->addHeadLink($adm_url . 'modules/mod_junewsultra/assets/css/junewsultra.css?v=6', 'preload', 'rel', [ 'as' => 'style' ]);