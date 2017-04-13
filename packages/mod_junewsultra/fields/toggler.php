<?php
/**
 * JUNewsUltra Pro
 *
 * @version          6.x
 * @package          UNewsUltra Pro
 * @author           Denys D. Nosov (denys@joomla-ua.org)
 * @copyright    (C) 2007-2015 by Denys D. Nosov (http://joomla-ua.org)
 * @license          GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 *
 **/

defined('_JEXEC') or die();

$version = new JVersion;
$joomla  = $version->getShortVersion();

include('head.php');

if (substr($joomla, 0, 3) >= '3.0')
{
	include('toggler30.php');
}