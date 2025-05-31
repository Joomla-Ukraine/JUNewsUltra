<?php
/**
 * JUNewsUltra Pro for Joomla!
 *
 * @package    JUNewsUltra Pro
 *
 * @copyright  Copyright (C) 2007-2023 Denys Nosov. All rights reserved.
 * @license    GNU/GPL - https://www.gnu.org/copyleft/gpl.html
 *
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;

/**
 * Installation class to perform additional changes during install/uninstall/update
 *
 * @since    6.0
 * @package  JUNewsUltra Pro
 */
class Pkg_JUNewsUltraInstallerScript
{
	/**
	 * @since 6.0
	 * @var
	 */
	protected $message;

	/**
	 * @since 6.0
	 * @var
	 */
	protected $status;
	/**
	 * @since 6.0
	 * @var
	 */
	protected $sourcePath;

	/**
	 * @param $type
	 * @param $parent
	 *
	 * @return bool
	 *
	 * @throws \Exception
	 * @since 6.0
	 */
	public function preflight($type, $parent)
	{
		$app = Factory::getApplication();

		if(version_compare(JVERSION, '3.8.0', 'lt'))
		{
			$app->enqueueMessage('Update for Joomla! 3.8+', 'error');

			return false;
		}

		Folder::create(JPATH_SITE . '/img', 0777);

		if(!is_dir(JPATH_SITE . '/img/'))
		{
			$app->enqueueMessage('Error creating folder \'img\'. Please manually create the folder \'img\' in the root of the site where you installed Joomla!');
		}

		$cache = Factory::getCache('mod_junewsultra');
		$cache->clean();

		return true;
	}

	/**
	 * @param $type
	 * @param $parent
	 *
	 * @return bool
	 *
	 * @since 6.0
	 */
	public function postflight($type, $parent)
	{
		if(version_compare(JVERSION, '4.0.0', '>='))
		{
			$xml = file_get_contents(JPATH_SITE . '/modules/mod_junewsultra/mod_junewsultra.xml');
			$xml = str_replace([
				'class="btn-group"',
				'addfieldpath="/administrator/components/com_content/models/fields/modal"'
			], [
				'layout="joomla.form.field.radio.switcher"',
				'addfieldprefix="Joomla\Component\Content\Administrator\Field"'
			], $xml);

			file_put_contents(JPATH_SITE . '/modules/mod_junewsultra/mod_junewsultra.xml', $xml);
		}

		$path  = JPATH_SITE . '/modules/mod_junewsultra/';
		$files = [
			$path . 'helper/com_k2.php',
			$path . 'assets/donate2.gif',
			$path . 'assets/gear.png',
			$path . 'assets/toggler.js',
			$path . 'assets/script.js',
			$path . 'assets/btn_donate.gif',
			$path . 'assets/junewsultra.jpg',
			$path . 'assets/js/script.js',
			$path . 'assets/js/toggler.js',
			$path . 'assets/js/javascript.js',
			$path . 'assets/js/clike.js',
			$path . 'assets/js/codemirror.js',
			$path . 'assets/js/css.js',
			$path . 'assets/js/overlay.js',
			$path . 'assets/js/php.js',
			$path . 'assets/js/runmode.js',
			$path . 'assets/js/xml.js',
			$path . 'assets/js/toggler30.js',
			$path . 'assets/js/script30.js',
			$path . 'assets/js/jquery.custom-input-file.js',
			$path . 'assets/css/codemirror.css',
			$path . 'assets/css/csscolors.css',
			$path . 'assets/css/default.css',
			$path . 'assets/css/elegant.css',
			$path . 'assets/css/docs.css',
			$path . 'fields/jumultithumbradio.php',
			$path . 'fields/colorpicker.php',
			$path . 'fields/imagesetting.php',
			$path . 'fields/juradio30.php',
			$path . 'fields/toggler25.php',
			$path . 'fields/toggler.php',
			$path . 'fields/toggler30.php',
			$path . 'fields/donate.php',
			$path . 'fields/article.php',
			$path . 'tmpl/default/images/bg.jpg',
			$path . 'tmpl/default/images/rating_star.png_',
			$path . 'tmpl/default/images/rating_star_blank.png_',
			$path . 'img/.htaccess',
			$path . 'img/config.php',
			$path . 'img/img.php',
			$path . 'img/imgsetting.php',
			$path . 'img/phpThumb.config.php',
			$path . 'img.php'
		];

		$folders = [
			$path . 'img',
			$path . 'lib/phpthumb',
			$path . 'assets/js/minicolors'
		];

		foreach($files as $file)
		{
			if(file_exists($file))
			{
				File::delete($file);
			}
		}

		foreach($folders as $folder)
		{
			if(is_dir($folder))
			{
				$this->unlinkRecursive($folder);
			}
		}

		return true;
	}

	/**
	 * @param $dir
	 * @param $deleteRootToo
	 *
	 *
	 * @since version
	 */
	private function unlinkRecursive($dir, $deleteRootToo = 1)
	{
		if(!$dh = opendir($dir))
		{
			return;
		}

		while(($obj = readdir($dh)) !== false)
		{
			if($obj === '.' || $obj === '..')
			{
				continue;
			}

			if(!unlink($dir . '/' . $obj))
			{
				$this->unlinkRecursive($dir . '/' . $obj, true);
			}
		}

		closedir($dh);

		if($deleteRootToo)
		{
			rmdir($dir);
		}
	}
}