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

define('_JEXEC', 1);
define('JPATH_BASE', __DIR__ . '/../../..');
define('MAX_SIZE', '500');

require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

$app_admin = Factory::getApplication('administrator');
$app_admin->loadDispatcher();

$app_site = Factory::getApplication('site');
$app      = Factory::getApplication();
$user     = Factory::getUser();
$doc      = Factory::getDocument();
$lang     = Factory::getLanguage();
$language = mb_strtolower($lang->getTag());

$lang->load('mod_junewsultra', JPATH_SITE);

$csslink = '<link href="../../../../../administrator/templates/isis/css/template.css" rel="stylesheet" type="text/css" />';

if($user->get('id') < 1)
{
	?>
	<!DOCTYPE html>
	<html lang="<?php echo $language; ?>">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title></title>
		<?php echo $csslink; ?>
	</head>
	<body><?php echo '<div class="alert alert-error">' . Text::_('MOD_JUNEWS_LOGIN') . '</div>'; ?></body>
	</html>
	<?php

	return;
}

$get_css   = $app->input->getString('css');
$get_file  = $app->input->getString('file');
$post_data = $app->input->post->getArray();

$current_tpl = explode(':', $get_file);
$jtpl        = $current_tpl[ 0 ];
$css         = '0';

if(is_file(JPATH_SITE . '/modules/mod_junewsultra/tmpl/' . str_replace('.php', '', $current_tpl[ 1 ]) . '/css/style.css'))
{
	$css_filename = JPATH_BASE . '/modules/mod_junewsultra/tmpl/' . str_replace('.php', '', $current_tpl[ 1 ]) . '/css/style.css';
	$css          = '1';
}

if(is_file(JPATH_SITE . '/templates/' . $jtpl . '/html/mod_junewsultra/' . str_replace('.php', '', $current_tpl[ 1 ]) . '/css/style.css'))
{
	$css_filename = JPATH_BASE . '/templates/' . $jtpl . '/html/mod_junewsultra/' . str_replace('.php', '', $current_tpl[ 1 ]) . '/css/style.css';
	$css          = '1';
}

if(is_file(JPATH_SITE . '/modules/mod_junewsultra/tmpl/' . $current_tpl[ 1 ]))
{
	$filename = JPATH_BASE . '/modules/mod_junewsultra/tmpl/' . $current_tpl[ 1 ];
}

if(is_file(JPATH_SITE . '/templates/' . $jtpl . '/html/mod_junewsultra/' . $current_tpl[ 1 ]))
{
	$filename = JPATH_BASE . '/templates/' . $jtpl . '/html/mod_junewsultra/' . $current_tpl[ 1 ];
}

if(isset($get_css))
{
	$filename = $css_filename;
}

if($post_data)
{
	$fw = fopen($filename, 'wb') or die('Could not open file!');
	$fb = fwrite($fw, stripslashes($_POST[ 'newd' ])) or die('Could not write to file');
	fclose($fw);
	chmod($filename, 0777);
}

$data = file_get_contents($filename);
?>
<!DOCTYPE html PUBLIC>
<html lang="<?php echo $language; ?>">
<head>
	<meta charset="utf-8" />
	<title></title>
	<?php echo $csslink; ?>
	<style>
		body {
			background: transparent;
			font-size: 102%;
			margin: 0 20px 0 20px;
		}

		.left {
			float: left;
		}

		.right {
			float: right;
		}

		.wells {
			position: fixed;
			z-index: 100;
			top: 0;
			left: 0;
			overflow: hidden;
			width: 100%;
			padding: 9px;
			background: #fff;
			border-bottom: 1px solid #ccc;
		}
	</style>
</head>
<body>
<form method="post">
	<div class="wells">
		<div class="btn-group left" style="margin-left: 10px;">
			<?php if($css == 1): ?>
				<?php if(isset($get_css)): ?>
					<?php echo '<a href="' . JUri::base() . 'edittemplate.php?file=' . $get_file . '" class="btn btn-success">Edit template: ' . $current_tpl[ 1 ] . '</a>'; ?>
					<?php echo '<span class="btn disabled">style.css</span>'; ?>
				<?php else: ?>
					<?php echo '<span class="btn disabled">' . $current_tpl[ 1 ] . '</span>'; ?>
					<?php echo '<a href="' . JUri::base() . 'edittemplate.php?file=' . $get_file . '&css=1" class="btn btn-success">Edit CSS: style.css</a>'; ?>
				<?php endif; ?>
			<?php else : ?>
				<?php echo '<span class="btn disabled">' . $current_tpl[ 1 ] . '</span>'; ?>
			<?php endif; ?>
		</div>
		<button type="submit" class="btn right" style="margin-right: 20px;">Save template</button>
	</div>
	<div style="clear: both;"></div>
	<label for="newd">
		<textarea name="newd" style="margin-top: 20px; width: 100%; height: 585px; clear: both;" id="newd"><?php echo $data; ?></textarea>
	</label>
</form>
</body>
</html>