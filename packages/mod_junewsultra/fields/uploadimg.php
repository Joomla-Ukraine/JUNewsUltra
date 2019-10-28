<?php
/**
 * JUNewsUltra Pro
 *
 * @package          Joomla.Site
 * @subpackage       mod_junewsultra
 *
 * @author           Denys Nosov, denys@joomla-ua.org
 * @copyright        2007-2019 (C) Joomla! Ukraine, http://joomla-ua.org. All rights reserved.
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

$csslink = '
<link href="../../../../../administrator/templates/isis/css/template.css" rel="stylesheet" type="text/css" />
<link href="../../../../../media/jui/css/bootstrap.css" rel="stylesheet" type="text/css" />

<script src="../../../../../media/jui/js/jquery.min.js" type="text/javascript"></script>
<script src="../../../../../modules/mod_junewsultra/assets/js/jquery.custom-input-file.js" type="text/javascript"></script>
<script type="text/javascript">
    jQuery.noConflict();
    (function($) {
        $(function() {
        $("#lefile").customInputFile({
            filename: "#juCover",
            replacementClass       : "customInputFile",
            replacementClassHover  : "customInputFileHover",
            replacementClassActive : "customInputFileActive",
            filenameClass          : "customInputFileName",
            wrapperClass           : "customInputFileWrapper",
            replacement : $(\'<button />\', {
                "text" : "Select",
                "class": "btn"
            })
        });
        });
    })(jQuery);
</script>
';

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
		<?php echo $csslink; ?>
	</head>
	<body>
	<?php echo alert(JText::_('MOD_JUNEWS_LOGIN'), 'notice'); ?>
	</body>
	</html>
	<?php
	return;
endif;

$path             = str_replace('modules' . DS . 'mod_junewsultra' . DS . 'fields' . DS . '..' . DS . '..' . DS . '..', 'media/mod_junewsultra', JPATH_BASE);
$max_image_width  = 800;
$max_image_height = 800;
$max_image_size   = 1024 * 1024;
$valid_types      = [
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
	<?php echo $csslink; ?>
</head>
<body>
<fieldset class="adminform">
	<legend><?php echo JText::_('MOD_JUNEWS_UPLOAD_MODULE'); ?></legend>
	<form enctype="multipart/form-data" method="post">
            <span class="input-append">
               <input id="juCover" class="input-mini disabled" style="width:110px!important;" value="" type="text">
               <input id="lefile" name="userfile" type="file" autocomplete="off">
               <button type="submit" class="btn btn-primary"><?php echo JText::_('MOD_JUNEWS_UPLOAD'); ?></button>
            </span>
		<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $max_image_size; ?>">
	</form>
</fieldset>
<?php
if(isset($_FILES[ 'userfile' ]))
{
	if(is_uploaded_file($_FILES[ 'userfile' ][ 'tmp_name' ]))
	{
		$filename = $_FILES[ 'userfile' ][ 'tmp_name' ];
		$ext      = substr($_FILES[ 'userfile' ][ 'name' ], 1 + strrpos($_FILES[ 'userfile' ][ 'name' ], '.'));

		if(filesize($filename) > $max_image_size)
		{
			echo alert(JText::_('MOD_JUNEWS_ERROR1') . $max_image_size . ' KB', 'notice');
		}
		elseif(!in_array($ext, $valid_types))
		{
			echo alert(JText::_('MOD_JUNEWS_ERROR2'), 'notice');
		}
		else
		{
			$size = getimagesize($filename);
			if($size && ($size[ 0 ] < $max_image_width) && ($size[ 1 ] < $max_image_height))
			{
				if(@move_uploaded_file($filename, $path . '/jn_' . $_FILES[ 'userfile' ][ 'name' ]))
				{
					echo alert(JText::_('MOD_JUNEWS_NOTICE8'), 'message');
				}
				else
				{
					echo alert(JText::_('MOD_JUNEWS_ERROR3'), 'notice');
				}
			}
			else
			{
				echo alert(JText::_('MOD_JUNEWS_ERROR4'), 'notice');
			}
		}
	}
	else
	{
		echo alert(JText::_('MOD_JUNEWS_ERROR3'), 'notice');
	}
}
?>
</body>
</html>