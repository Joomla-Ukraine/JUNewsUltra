<?php
/**
 * JUNewsUltra Pro for Joomla!
 *
 * @package    JUNewsUltra Pro
 *
 * @copyright  Copyright (C) 2007-2025 Denys Nosov. All rights reserved.
 * @license    GNU/GPL - https://www.gnu.org/copyleft/gpl.html
 *
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

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
	public function preflight(): bool
	{
		$app = Factory::getApplication();

		if(version_compare(JVERSION, '4.0.0', 'lt'))
		{
			$app->enqueueMessage('Update for Joomla! 4.x+', 'error');

			return false;
		}

		Folder::create(JPATH_SITE . '/img');
		Folder::create(JPATH_SITE . '/images/mod_junewsultra');

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
	public function postflight($type, $parent): bool
	{
		File::copy(JPATH_SITE . '/media/mod_junewsultra/notfoundimage.png', JPATH_SITE . '/images/mod_junewsultra/notfoundimage.png');
		
		$path  = JPATH_SITE . '/modules/mod_junewsultra/';
		$files = [
			$path . 'helper/com_k2.php',
			$path . 'fields/upload.php',
			$path . 'fields/head.php',
			$path . 'fields/jumultithumbradio.php',
			$path . 'fields/colorpicker.php',
			$path . 'fields/imagesetting.php',
			$path . 'fields/juradio30.php',
			$path . 'fields/toggler25.php',
			$path . 'fields/toggler.php',
			$path . 'fields/toggler30.php',
			$path . 'fields/donate.php',
			$path . 'fields/article.php',
			$path . 'fields/uploadimg.php',
			$path . 'fields/template.php',
			$path . 'fields/edittemplate.php',
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
			$path . 'assets'
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
	private function unlinkRecursive($dir, $deleteRootToo = 1): void
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