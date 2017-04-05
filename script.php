<?php
/**
 * JUNewsUltra Pro for Joomla!
 *
 * @package    JUNewsUltra Pro
 *
 * @copyright  Copyright (C) 2007-2017 Denys Nosov. All rights reserved.
 * @license    GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 *
 */

/**
 * Installation class to perform additional changes during install/uninstall/update
 *
 * @package  JUNewsUltra Pro
 * @since    6.0
 */
class Pkg_JUNewsUltraInstallerScript
{
	protected $dbSupport = array('mysql', 'mysqli', 'postgresql', 'sqlsrv', 'sqlazure');
	protected $message;
	protected $status;
	protected $sourcePath;

	public function preflight($type, $parent)
	{
		if (version_compare(JVERSION, '3.1.0', 'lt'))
		{
            JFactory::getApplication()->enqueueMessage('Update for Joomla! 3.4+', 'error');

			return false;
		}

		// Check to see if the database type is supported
		if (!in_array(JFactory::getDbo()->name, $this->dbSupport))
		{
            JFactory::getApplication()->enqueueMessage(JText::_('MOD_JUNEWS_ERROR_DB_SUPPORT'), 'error');
			return false;
		}

		$this->MakeDirectory($dir = JPATH_SITE .'/img', $mode = 0777);

		if (!is_dir(JPATH_SITE .'/img/')) {
			JError::raiseNotice(null, "Error creating folder 'img'. Please manually create the folder 'img' in the root of the site where you installed Joomla!");
		}

        $cache = JFactory::getCache('mod_junewsultra');
    	$cache->clean();

		return true;
	}

	public function uninstall($parent)
	{

	}

	public function update($parent)
	{

	}

	public function postflight($type, $parent, $results)
	{
		// Determine whether each extension is enabled or not
		$enabled    = array();

		$db         = JFactory::getDbo();
		$query      = $db->getQuery(true);
		$app        = JFactory::getApplication();

        $adm_url	= str_replace('/administrator', '', JURI::base());

		$version    = new JVersion;
        $joomla     = substr($version->getShortVersion(), 0, 3);

        $lang = JFactory::getLanguage();
        $lang->load('mod_junewsultra', JPATH_SITE);

		foreach ($results as $result)
		{
			$extension = (string) $result['name'];
			$query->clear();
			$query->select($db->quoteName('enabled'));
			$query->from($db->quoteName('#__extensions'));
			$query->where($db->quoteName('name') . ' = ' . $db->quote($extension));
			$db->setQuery($query);
			$enabled[$extension] = $db->loadResult();
		}

		$html = '';
		$html .= '<style type="text/css">
		.juinstall {
			clear: both;
			color: #333!important;
			font-weight: normal;
		    margin: 0!important;
		    padding: 0;
		    overflow: hiden;
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
                <h2 style="padding: 0 0 8px 0; margin: 0;">'. JText::_('MOD_JUNEWS_TITLE') .'</h2>
				<h2 style="padding: 0 0 8px 0; margin: 0;"><small>'. JText::_('MOD_JUNEWS_DESCRIPTION') .'</small></h2>
        		<table class="table table-striped" width="100%">
        			<thead>
        				<tr>
        					<th>'. JText::_('MOD_JUNEWS_EXTENSION') .'</th>
        					<th>'. JText::_('JSTATUS') .'</th>
        					<th>'. JText::_('JENABLED') .'</th>
        				</tr>
        			</thead>
        			<tbody>';

		foreach ($results as $result)
		{
        	$extension = (string) $result['name'];

        	$html .= '<tr><td>';
			
			if($extension == 'MOD_JUNEWSULTRA') {
            	$html .= JText::_($extension);
            } else {
            	$html .= $extension;
            }

			$html .= '</td><td>
			<strong>';

			if ($result['result'] == true) {
            	$html .= '<span class="label label-success">'. JText::_('MOD_JUNEWS_INSTALLED') .'</span>';
        	} else {
            	$html .= '<span class="label label-important">'. JText::_('MOD_JUNEWS_NOT_INSTALLED') .'</span>';
			}

			$html .= '</strong>
        	</td><td>';

			if ($enabled[$extension] == 1) {
        		$html .= '<span class="label label-success">'. JText::_('JYES') .'</span>';
        	} else {
        		$html .= '<span class="label label-important">'. JText::_('JNO') .'</span>';
        	}

			$html .= '</td></tr>';
        }

		$html .= '</tbody></table>';

		$path  = JPATH_SITE .'/modules/mod_junewsultra/';
        $files = array(
                    $path .'assets/gear.png',
                    $path .'assets/toggler.js',
                    $path .'assets/script.js',
                    $path .'assets/btn_donate.gif',
                    $path .'assets/junewsultra.jpg',
			        $path .'assets/js/script.js',
			        $path .'assets/js/toggler.js',
			        $path .'assets/js/javascript.js',
			        $path .'assets/js/clike.js',
			        $path .'assets/js/codemirror.js',
			        $path .'assets/js/css.js',
			        $path .'assets/js/overlay.js',
			        $path .'assets/js/php.js',
			        $path .'assets/js/runmode.js',
			        $path .'assets/js/xml.js',
			        $path .'assets/css/codemirror.css',
			        $path .'assets/css/csscolors.css',
			        $path .'assets/css/default.css',
			        $path .'assets/css/elegant.css',
			        $path .'assets/css/docs.css',

                    $path .'fields/colorpicker.php',
                    $path .'fields/imagesetting.php',
                    $path .'fields/juradio30.php',
                    $path .'fields/toggler25.php',
                    $path .'fields/donate.php',

                    $path .'tmpl/default/images/bg.jpg',
                    $path .'tmpl/default/images/rating_star.png_',
                    $path .'tmpl/default/images/rating_star_blank.png_',

                    $path .'img/.htaccess',
                    $path .'img/config.php',
                    $path .'img/img.php',
                    $path .'img/imgsetting.php',
                    $path .'img/phpThumb.config.php',
                    $path .'img.php'
                );

        $folders = array(
                    $path .'img',
                    $path .'lib/phpthumb',
                    $path .'assets/js/minicolors'
                );

        $i = 0;
        foreach ($files AS $file) {
        	if (file_exists($file)) $i++;
        }

        $j = 0;
        foreach ($folders AS $folder) {
        	if (is_dir($folder)) $j++;
        }

        if(($i+$j) > 0)
		{
        	$html .= '<h2>'. JText::_('MOD_JUNEWS_REMOVE_OLD_FILES') .'</h2>
        	<table class="table table-striped"><tbody>';

			foreach ($files AS $file)
            {
            	if (file_exists($file))
                {
                	$filepath = str_replace($path, '', $file);
                    unlink($file);

            		$html .= '<tr>
            		<td><span class="label">File:</span> <code>'. $filepath .'</code></td>
            		<td><span class="label label-inverse">Delete</span></td>
            		</tr>';
                 }
            }

            foreach ($folders AS $folder)
            {
	            if (is_dir($folder))
                {
                	$folderpath = str_replace($path, '', $folder);
                    $this->unlinkRecursive($folder, 1);

            		$html .= '<tr>
            		<td><span class="label">Folder:</span> <code>'. $folderpath .'</code></td>
            		<td><span class="label label-inverse">Delete</span></td>
            		</tr>';
                 }
            }

            $html .= '</tbody></table>';
		}

        $html .= '</div>
        </div>';

		if($joomla < '3.4') {
			echo $html;
		}
        else {
			$app->enqueueMessage($html, 'message');
		}

		return true;
	}

    public function MakeDirectory($dir, $mode)
    {
        if (is_dir($dir) || @mkdir($dir,$mode))
        {
            $indexfile    = $dir .'/index.html';
            if(!file_exists($indexfile))
            {
                $file = fopen($indexfile, 'w');
                fputs($file, '<!DOCTYPE html><title></title>');
                fclose($file);
            }
            return TRUE;
        }

        if (!$this->MakeDirectory(dirname($dir),$mode)) return FALSE;

        return @mkdir($dir,$mode);
    }

    public function unlinkRecursive($dir, $deleteRootToo)
    {
        if(!$dh = @opendir($dir)) return;

        while (false !== ($obj = readdir($dh)))
        {
            if($obj == '.' || $obj == '..') continue;

            if (!@unlink($dir .'/'. $obj)) $this->unlinkRecursive($dir .'/'. $obj, true);
        }

        closedir($dh);

        if ($deleteRootToo) @rmdir($dir);

        return;
    }
}