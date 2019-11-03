<?php
/**
 * JUNewsUltra Pro for Joomla!
 *
 * @package    JUNewsUltra Pro
 *
 * @copyright  Copyright (C) 2007-2019 Denys Nosov. All rights reserved.
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 *
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Installation class to perform additional changes during install/uninstall/update
 *
 * @package  JUNewsUltra Pro
 * @since    6.0
 */
class Pkg_JUNewsUltraInstallerScript
{
	protected $message;
	protected $status;
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
		if(version_compare(JVERSION, '3.8.0', 'lt'))
		{
			Factory::getApplication()
			       ->enqueueMessage('Update for Joomla! 3.8+', 'error');

			return false;
		}

		$this->MakeDirectory(JPATH_SITE . '/img');

		if(!is_dir(JPATH_SITE . '/img/'))
		{
			Factory::getApplication()
			       ->enqueueMessage('Error creating folder \'img\'. Please manually create the folder \'img\' in the root of the site where you installed Joomla!', 'message');
		}

		$cache = Factory::getCache('mod_junewsultra');
		$cache->clean();

		return true;
	}

	/**
	 * @param $parent
	 *
	 *
	 * @return bool
	 * @since 6.0
	 */
	public function uninstall($parent)
	{
		return true;
	}

	/**
	 * @param $parent
	 *
	 *
	 * @return bool
	 * @since 6.0
	 */
	public function update($parent)
	{
		return true;
	}

	/**
	 * @param $type
	 * @param $parent
	 * @param $results
	 *
	 * @return bool
	 *
	 * @throws \Exception
	 * @since 6.0
	 */
	public function postflight($type, $parent, $results)
	{
		$enabled = [];

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$app   = Factory::getApplication();

		$lang = Factory::getLanguage();
		$lang->load('mod_junewsultra', JPATH_SITE);

		foreach($results as $result)
		{
			$extension = (string) $result[ 'name' ];

			$query->clear();

			$query->select($db->quoteName('enabled'));
			$query->from($db->quoteName('#__extensions'));
			$query->where($db->quoteName('name') . ' = ' . $db->quote($extension));
			$db->setQuery($query);

			$enabled[ $extension ] = $db->loadResult();
		}

		$html = '';
		$html .= '<style type="text/css">
		.juinstall {
			clear: both;
			color: #333!important;
			font-weight: normal;
		    margin: 0!important;
		    padding: 0;
		    overflow: hidden;
		    background: #fff!important;
			position: absolute!important;
			top: 0!important;
			left: 0!important;
			width: 100%;
			height: 100%;
			z-index: 100!important;
		}
			.juinstall-content {
			    margin: 8% auto!important;
			    padding: 35px 0 18px 0;
				width: 50%;
			}
			.juinstall .newalert {
				clear: both;
				margin: 5px 10%!important;
			}
            .juinstall p {
              	margin-left: 0;
              	text-align: left;
            }
            .juinstall table td .label {
                margin: 0 auto;
            }
            .juinstall hr {
              	margin-top:6px;
              	margin-bottom:6px;
              	border:0;
              	border-top:1px solid #eee
            }
        </style>';

		$html .= '<div class="juinstall">
        	<div class="juinstall-content">
                <h2 style="padding: 0 0 8px 0; margin: 0;">' . Text::_('MOD_JUNEWS_TITLE') . '</h2>
				<h2 style="padding: 0 0 8px 0; margin: 0;"><small>' . Text::_('MOD_JUNEWS_DESCRIPTION') . '</small></h2>
        		<table class="table table-striped">
        			<thead>
        				<tr>
        					<th>' . Text::_('MOD_JUNEWS_EXTENSION') . '</th>
        					<th>' . Text::_('JSTATUS') . '</th>
        					<th>' . Text::_('JENABLED') . '</th>
        				</tr>
        			</thead>
        			<tbody>';

		foreach($results as $result)
		{
			$extension = (string) $result[ 'name' ];

			$html .= '<tr><td>';

			if($extension === 'MOD_JUNEWSULTRA')
			{
				$html .= Text::_($extension);
			}
			else
			{
				$html .= $extension;
			}

			$html .= '</td><td><strong>';

			if($result[ 'result' ] === true)
			{
				$html .= '<span class="label label-success">' . Text::_('MOD_JUNEWS_INSTALLED') . '</span>';
			}
			else
			{
				$html .= '<span class="label label-important">' . Text::_('MOD_JUNEWS_NOT_INSTALLED') . '</span>';
			}

			$html .= '</strong></td><td>';

			if($enabled[ $extension ] == 1)
			{
				$html .= '<span class="label label-success">' . Text::_('JYES') . '</span>';
			}
			else
			{
				$html .= '<span class="label label-important">' . Text::_('JNO') . '</span>';
			}

			$html .= '</td></tr>';
		}

		$html .= '</tbody></table>';

		$path  = JPATH_SITE . '/modules/mod_junewsultra/';
		$files = [
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

		$i = 0;
		foreach($files AS $file)
		{
			if(file_exists($file))
			{
				$i++;
			}
		}

		$j = 0;
		foreach($folders AS $folder)
		{
			if(is_dir($folder))
			{
				$j++;
			}
		}

		if(($i + $j) > 0)
		{
			$html .= '<h2>' . Text::_('MOD_JUNEWS_REMOVE_OLD_FILES') . '</h2><table class="table table-striped"><tbody>';

			foreach($files AS $file)
			{
				if(file_exists($file))
				{
					$filepath = str_replace($path, '', $file);
					unlink($file);

					$html .= '<tr><td><span class="label">File:</span> <code>' . $filepath . '</code></td><td><span class="label label-inverse">Delete</span></td></tr>';
				}
			}

			foreach($folders AS $folder)
			{
				if(is_dir($folder))
				{
					$folderpath = str_replace($path, '', $folder);
					$this->unlinkRecursive($folder, 1);

					$html .= '<tr><td><span class="label">Folder:</span> <code>' . $folderpath . '</code></td><td><span class="label label-inverse">Delete</span></td></tr>';
				}
			}

			$html .= '</tbody></table>';
		}

		$html .= '</div></div>';

		$app->enqueueMessage($html, 'message');

		return true;
	}

	/**
	 * @param $dir
	 * @param $mode
	 *
	 * @return bool
	 *
	 * @since version
	 */
	public function MakeDirectory($dir, $mode = 0777)
	{
		if(mkdir($dir, $mode) || is_dir($dir))
		{
			return true;
		}

		if(!$this->MakeDirectory(dirname($dir), $mode))
		{
			return false;
		}

		return @mkdir($dir, $mode);
	}

	/**
	 * @param $dir
	 * @param $deleteRootToo
	 *
	 *
	 * @since version
	 */
	public function unlinkRecursive($dir, $deleteRootToo)
	{
		if(!$dh = @opendir($dir))
		{
			return;
		}

		while(false !== ($obj = readdir($dh)))
		{
			if($obj === '.' || $obj === '..')
			{
				continue;
			}

			if(!@unlink($dir . '/' . $obj))
			{
				$this->unlinkRecursive($dir . '/' . $obj, true);
			}
		}

		closedir($dh);

		if($deleteRootToo)
		{
			@rmdir($dir);
		}
	}
}