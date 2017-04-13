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

define('_JEXEC', 1);
define('JPATH_BASE', dirname(__FILE__) . "/../../..");
define("MAX_SIZE", "500");

require_once(JPATH_BASE . '/includes/defines.php');
require_once(JPATH_BASE . '/includes/framework.php');
require_once(JPATH_BASE . '/libraries/joomla/factory.php');

$mainframe  = JFactory::getApplication('administrator');
$joomlaUser = JFactory::getUser();
$lang       = JFactory::getLanguage();

$lang->load('mod_junewsultra', JPATH_SITE);

$language = mb_strtolower($lang->getTag());

$csslink = '<link href="../../../../../administrator/templates/isis/css/template.css" rel="stylesheet" type="text/css" />';

function alert($text, $error)
{
	if ($error == 'message')
	{
		$error = 'alert-info';
	}
	if ($error == 'notice')
	{
		$error = 'alert-error';
	}

	return '<div class="alert ' . $error . '">' . $text . '</div>';
}

if ($joomlaUser->get('id') < 1)
{
	?>
    <!DOCTYPE html>
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $language; ?>" lang="<?php echo $language; ?>">
    <head>
        <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
		<?php echo $csslink; ?>
    </head>
    <body>
	<?php echo alert(JText::_('MOD_JUNEWS_LOGIN'), 'notice'); ?>
    </body>
    </html>
	<?php
	return;
}

$app         = JFactory::getApplication('site');
$current_tpl = explode(":", $_GET["file"]);

$jtpl = $current_tpl[0];

$css = '0';

if (is_file(JPATH_SITE . '/modules/mod_junewsultra/tmpl/' . str_replace('.php', '', $current_tpl[1]) . '/css/style.css'))
{
	$css_filename = JPATH_BASE . '/modules/mod_junewsultra/tmpl/' . str_replace('.php', '', $current_tpl[1]) . '/css/style.css';
	$css          = '1';
}
if (is_file(JPATH_SITE . '/templates/' . $jtpl . '/html/mod_junewsultra/' . str_replace('.php', '', $current_tpl[1]) . '/css/style.css'))
{
	$css_filename = JPATH_BASE . '/templates/' . $jtpl . '/html/mod_junewsultra/' . str_replace('.php', '', $current_tpl[1]) . '/css/style.css';
	$css          = '1';
}

if (isset($_GET["css"]))
{
	$filename = $css_filename;
}
else
{
	if (is_file(JPATH_SITE . '/modules/mod_junewsultra/tmpl/' . $current_tpl[1]))
	{
		$filename = JPATH_BASE . '/modules/mod_junewsultra/tmpl/' . $current_tpl[1];
	}

	if (is_file(JPATH_SITE . '/templates/' . $jtpl . '/html/mod_junewsultra/' . $current_tpl[1]))
	{
		$filename = JPATH_BASE . '/templates/' . $jtpl . '/html/mod_junewsultra/' . $current_tpl[1];
	}
}

if (isset($_POST['newd'])) $newdata = $_POST['newd'];

if (isset($newdata) != '')
{
	$fw = fopen($filename, 'w') or die('Could not open file!');
	$fb = fwrite($fw, stripslashes($newdata)) or die('Could not write to file');
	fclose($fw);
	chmod($filename, 0777);
}

$fh = fopen($filename, "r") or die("Could not open file!");
$data = fread($fh, filesize($filename)) or die("Could not read file!");

fclose($fh);
chmod($filename, 0777);

?>
<!DOCTYPE html PUBLIC>
<html lang="<?php echo $language; ?>">
<head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8"/>
	<?php echo $csslink; ?>
    <style type="text/css">
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
			<?php if ($css == 1): ?>
				<?php if (isset($_GET["css"])): ?>
					<?php echo '<a href="' . JURI::base() . 'edittemplate.php?file=' . $_GET['file'] . '" class="btn btn-success">Edit template: ' . $_GET['file'] . '</a>'; ?>
					<?php echo '<span class="btn disabled">style.css</span>'; ?>
				<?php else: ?>
					<?php echo '<span class="btn disabled">' . $current_tpl[1] . '</span>'; ?>
					<?php echo '<a href="' . JURI::base() . 'edittemplate.php?file=' . $_GET['file'] . '&css=1" class="btn btn-success">Edit CSS: style.css</a>'; ?>
				<?php endif; ?>
			<?php else : ?>
				<?php echo '<span class="btn disabled">' . $current_tpl[1] . '</span>'; ?>
			<?php endif; ?>
        </div>
        <button type="submit" class="btn right" style="margin-right: 20px;">Save template</button>
    </div>
    <div style="clear: both;"></div>
    <textarea name="newd" style="width: 100%; height: 585px; clear: both;" id="newd"><?php echo $data; ?></textarea>
</form>
</body>
</html>