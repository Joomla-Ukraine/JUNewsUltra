<?php
/**
 * JUNewsUltra Pro
 *
 * @package          Joomla.Site
 * @subpackage       mod_junewsultra
 *
 * @author           Denys Nosov, denys@joomla-ua.org
 * @copyright        2007-2021 (C) Joomla! Ukraine, http://joomla-ua.org. All rights reserved.
 * @license          GNU General Public License version 2 or later; see LICENSE.txt
 */

define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
define('JPATH_BASE', __DIR__ . DS . '..' . DS . '..' . DS . '..');
define('MAX_SIZE', '500');

require_once JPATH_BASE . DS . 'includes' . DS . 'defines.php';
require_once JPATH_BASE . DS . 'includes' . DS . 'framework.php';

use Joomla\CMS\Factory;

$mainframe  = Factory::getApplication('administrator');
$joomlaUser = Factory::getUser();
$lang       = Factory::getLanguage();

$lang->load('mod_junewsultra', JPATH_SITE);

$language = mb_strtolower($lang->getTag());

function alert($text, $error)
{
	if($error === 'message')
	{
		$error = 'alert-info';
	}

	if($error === 'notice')
	{
		$error = 'alert-error';
	}

	return '<div class="alert ' . $error . '">' . $text . '</div>';
}

?>
<?php if($joomlaUser->get('id') < 1) : ?>
	<!DOCTYPE html>
	<html lang="<?php echo $language; ?>">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<title>Upload</title>
	</head>
	<body>
	<?php echo alert(JText::_('MOD_JUNEWS_LOGIN'), 'notice'); ?>
	</body>
	</html>
	<?php
	return;
endif;

$path        = str_replace('modules/mod_junewsultra/fields/../../..', 'media/mod_junewsultra', JPATH_BASE);
$valid_types = [
	'gif',
	'jpg',
	'png',
	'jpeg'
];

?>
<!DOCTYPE html>
<html lang="<?php echo $language; ?>">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>Upload</title>
</head>
<body>
<form enctype="multipart/form-data" method="post">
	<fieldset class="adminform">
		<legend><?php echo JText::_('MOD_JUNEWS_UPLOAD_MODULE'); ?></legend>
		<label for="juCover">
			<input id="lefile" name="userfile" type="file" autocomplete="off">
		</label>
	</fieldset>
	<button type="submit" class="btn btn-primary"><?php echo JText::_('MOD_JUNEWS_UPLOAD'); ?></button>
</form>
<?php
if(isset($_FILES[ 'userfile' ]))
{
	$alert = alert(JText::_('MOD_JUNEWS_ERROR3'), 'notice');
	if(is_uploaded_file($_FILES[ 'userfile' ][ 'tmp_name' ]))
	{
		$filename = $_FILES[ 'userfile' ][ 'tmp_name' ];
		$ext      = pathinfo($_FILES[ 'userfile' ][ 'name' ])[ 'extension' ];

		if(!in_array($ext, $valid_types))
		{
			$alert = alert(JText::_('MOD_JUNEWS_ERROR2'), 'notice');
		}
		else
		{
			$size  = getimagesize($filename);
			$alert = alert(JText::_('MOD_JUNEWS_ERROR3'), 'notice');
			if(@move_uploaded_file($filename, $path . '/jn_' . $_FILES[ 'userfile' ][ 'name' ]))
			{
				$alert = alert(JText::_('MOD_JUNEWS_NOTICE8'), 'message');
			}
		}
	}

	echo $alert;
}
?>
</body>
</html>