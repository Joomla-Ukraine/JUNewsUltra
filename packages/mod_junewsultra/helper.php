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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\String\StringHelper;

defined('_JEXEC') or die;

/**
 * Helper for mod_junewsultra
 *
 * @package     Joomla.Site
 * @subpackage  mod_junewsultra
 * @since       6.0
 */
class Helper
{
	/**
	 * modJUNewsUltraHelper constructor.
	 */
	public function __construct()
	{
		$this->lang     = Factory::getLanguage();
		$this->user     = Factory::getUser();
		$this->date     = Factory::getDate();
		$this->db       = Factory::getDbo();
		$this->query    = $this->db->getQuery(true);
		$this->nulldate = $this->db->getNullDate();
		$this->nowdate  = $this->date->toSql();
		$this->juimg    = new JUImage();
	}

	/**
	 * @param $params
	 * @param $junews
	 *
	 * @return bool
	 *
	 * @since 6.0
	 */
	public function query($params, $junews)
	{

	}

	/**
	 * @param $params
	 * @param $junews
	 *
	 * @return string items
	 *
	 * @since 6.0
	 */
	public function getList($params, $junews)
	{

	}

	/**
	 * @param array $data
	 *
	 * @return string
	 *
	 * @since 6.0
	 */
	public function image($params, array $data = [])
	{
		$attr = [];

		if(isset($data[ 'src' ]))
		{
			$attr[] = 'src="' . $data[ 'src' ] . '"';
		}

		if(isset($data[ 'alt' ]))
		{
			$attr[] = 'alt="' . $data[ 'alt' ] . '"';
		}

		if(isset($data[ 'srcset' ]))
		{
			$attr[] = $data[ 'srcset' ];
		}

		if($params->get('image_css_class') !== '')
		{
			$attr[] = 'class="' . $params->get('image_css_class') . '"';
		}

		if($params->get('image_attr') !== '')
		{
			$attr[] = $params->get('image_attr');
		}

		if($params->get('image_loading', 1) == 1)
		{
			$attr[] = 'loading="lazy"';
		}

		return '<img ' . implode(' ', $attr) . '>';
	}

	/**
	 * @param $html
	 * @param $_cropaspect
	 *
	 * @return float|int
	 *
	 * @since 6.0
	 */
	public function aspect($html, $_cropaspect)
	{
		$size   = getimagesize(rawurldecode(JPATH_SITE . '/' . $html));
		$width  = $size[ 0 ];
		$height = $size[ 1 ] * ($_cropaspect != '' ? $_cropaspect : '0');

		return $height / $width;
	}

	/**
	 * @param $params
	 * @param $title
	 *
	 * @return mixed|string|string[]|null
	 *
	 * @since 6.0
	 */
	public function title($params, $title)
	{
		$title = strip_tags($title);
		$title = htmlspecialchars($title);

		if($params->def('title_prepare') == 1)
		{
			$title = HTMLHelper::_('content.prepare', $title);
		}

		if($params->get('title_limit', 0) == 1)
		{
			if($params->get('title_limit_mode') == 1)
			{
				$title = trim(implode(' ', array_slice(explode(' ', $title), 0, $params->get('title_limit_count'))));
			}
			else
			{
				$title = trim(StringHelper::substr($title, 0, $params->get('title_limit_count')));
			}

			if(!preg_match('#(\.|\?|!)$#ismu', $title))
			{
				$title = preg_replace('#^\s?(,|;|:|-)#ismu', '', $title);
				$title = ($title ? $title . $params->get('end_limit_title', '...') : '');
			}
		}

		return $title;
	}

	/**
	 * @param       $params
	 * @param array $data
	 *
	 * @return string|string[]|null
	 *
	 * @since 6.0
	 */
	public function desc($params, array $data = [])
	{
		if($params->def('content_prepare') == 1)
		{
			$description = HTMLHelper::_('content.prepare', $data[ 'description' ]);
		}
		else
		{
			$description = preg_replace('/<p>\s*{([a-zA-Z0-9\-_]*)\s*(.*?)}\s*<\/p>/i', '', $description);
			$description = preg_replace('/{([a-zA-Z0-9\-_]*)\s*(.*?)}/i', '', $description);
			$description = preg_replace('/\[(.*?)\s?.*?].*?\[\/(.*?)]/i', '', $description);
		}

		$description = preg_replace('/::cck::(.*?)::\/cck::/i', '', $description);
		$description = preg_replace('/::introtext::(.*?)::\/introtext::/i', '\\1', $description);
		$description = preg_replace('/::fulltext::(.*?)::\/fulltext::/i', '\\2', $description);

		if(isset($data[ 'cleartag' ]))
		{
			echo $data[ 'cleartag' ];
			$description = str_replace('&nbsp;', ' ', $description);
			$description = preg_replace('/(<\/[^>]+?>)(<[^>\/][^>]*?>)/', '$1 $2', $description);

			if(isset($data[ 'allowed_tags' ]))
			{
				$allowed_tags = str_replace([
					' ',
					'&nbsp;',
					'    '
				], '', $allowed_tags);
				$tags         = '<' . str_replace(',', '><', $allowed_tags) . '>';
				$description  = strip_tags($description, $tags);
			}
			else
			{
				$description = strip_tags($description);
			}
		}

		if(isset($data[ 'li' ]))
		{
			$dots = 1;
			switch(isset($data[ 'lmttext' ]))
			{
				case '1':
					$description = trim(implode(' ', array_slice(explode(' ', $description), 0, $data[ 'text_limit' ])));
					break;
				case '2':
					$description = preg_replace('#<p(.*)>"#is', '<p>', $description);

					if(preg_match('#<p[^>]*>(.*)</p>#isU', $description, $matches))
					{
						$description = $matches[ 0 ];
						$dots        = 0;
					}
					break;
				case '3':
					if(preg_match('#^.{100}.*?[.!?]#is', strip_tags($description), $matches))
					{
						$description = $matches[ 0 ];
						$dots        = 0;
					}
					break;
				default:
				case '0':
					$description = trim(StringHelper::substr($description, 0, $data[ 'text_limit' ]));
					break;
			}

			if((!preg_match('#(\.|\?|!)$#ismu', $description)) && $dots == 1)
			{
				$description = preg_replace('#^\s?(,|;|:|-)#ismu', '', $description);
				$description = ($description ? $description . $data[ 'end_limit_text' ] : '');
			}
		}

		return $description;
	}

	/**
	 * @param $params
	 * @param $rating
	 *
	 * @return string
	 *
	 * @since 6.0
	 */
	public function rating($params, $rating)
	{
		$tpl        = explode(':', $params->def('template'));
		$rating_tpl = 'modules/mod_junewsultra/tmpl/' . $tpl[ 1 ] . '/images';

		$starImageOn = HTMLHelper::_('image', 'system/rating_star.png', $rating, null, true);
		if(is_file(JPATH_SITE . $rating_tpl . '/rating_star.png'))
		{
			$starImageOn = HTMLHelper::_('image', Uri::base() . $rating_tpl . '/rating_star.png', $rating, null, true);
		}

		$starImageOff = HTMLHelper::_('image', 'system/rating_star_blank.png', $rating, null, true);
		if(is_file(JPATH_SITE . $rating_tpl . '/rating_star_blank.png'))
		{
			$starImageOff = HTMLHelper::_('image', Uri::base() . $rating_tpl . '/rating_star_blank.png', $rating, null, true);
		}

		$img = '';
		for($i = 0; $i < $rating; $i++)
		{
			$img .= $starImageOn;
		}

		for($i = $rating; $i < 5; $i++)
		{
			$img .= $starImageOff;
		}

		return $img;
	}

	/**
	 * @param $html
	 *
	 * @return string|string[]|null
	 *
	 * @since 6.0
	 */
	public function url($html)
	{
		$root_url = Uri::base();

		$html = preg_replace('@href="(?!http://)(?!https://)(?!mailto:)([^"]+)"@i', "href=\"{$root_url}\${1}\"", $html);
		$html = preg_replace('@src="(?!http://)(?!https://)([^"]+)"@i', "src=\"{$root_url}\${1}\"", $html);

		return $html;
	}

	/**
	 * @param $feed_url
	 * @param $xmlcount
	 * @param $cache_file
	 * @param $time
	 * @param $path
	 * @param $ordering_xml
	 *
	 * @return array|\SimpleXMLElement[]
	 *
	 * @since 6.0
	 */
	public function xml($feed_url, $xmlcount, $cache_file, $time, $path, $ordering_xml)
	{
		$timedif = @(time() - filemtime($cache_file));

		if(file_exists($cache_file) && ($timedif < $time))
		{
			$string = file_get_contents(JPATH_SITE . '/' . $cache_file);
			$error  = false;
		}
		else
		{
			try
			{
				$string = @file_get_contents($feed_url);
				$string = preg_replace('/<(\/)?([a-z0-9]+):([a-z0-9]+)/i', '<$1$2_$3', $string);
				$string = preg_replace('/<feed.*?>/i', '<feed>', $string);

				if($f = @fopen($cache_file, 'wb'))
				{
					fwrite($f, $string, strlen($string));
					fclose($f);
				}

				$error = false;
			}
			catch (Exception $e)
			{
				$error = true;
			}
		}

		if($error === true && is_file(JPATH_SITE . '/' . $cache_file))
		{
			$string = file_get_contents(Uri::root() . $cache_file);
		}

		libxml_use_internal_errors(true);
		try
		{
			$xml = new SimpleXmlElement($string);

			switch($ordering_xml)
			{
				case 'rand':
					$items = $xml->xpath(sprintf($path . '[position()]'));
					shuffle($items);
					$items = array_slice($items, 0, $xmlcount);
					break;
				case 'created_asc':
					$items = $xml->xpath(sprintf($path . '[position() >= last()-%d]', $xmlcount));
					break;
				default:
				case 'created_desc':
					$items = $xml->xpath(sprintf($path . '[position() <= %d]', $xmlcount));
					break;
			}
		}
		catch (Exception $e)
		{
			echo '<div class="uk-alert-warning" uk-alert>' . Text::_('MOD_JUNEWS_RSSXML_ERROR') . '</div>';
		}

		return $items;
	}

	/**
	 * @param $params
	 *
	 * @return bool
	 *
	 * @since 6.0
	 */
	public function loadJQ($params)
	{
		if($params->def('jquery') == 1)
		{
			HTMLHelper::_('jquery.framework');
		}

		return true;
	}

	/**
	 * @param $params
	 *
	 * @return bool
	 *
	 * @since 6.0
	 */
	public function loadBS($params)
	{
		if($params->def('bootstrap_js') == 1)
		{
			HTMLHelper::_('bootstrap.framework');
		}

		if($params->def('bootstrap_css') == 1)
		{
			$direction = ($this->lang->isRtl() ? 'rtl' : 'ltr');

			JHtmlBootstrap::loadCss($includeMaincss = true, $direction);
		}

		return true;
	}

	/**
	 * @param $params
	 *
	 * @return bool
	 *
	 * @since 6.0
	 */
	public function loadCSS($params)
	{
		if($params->get('cssstyle') == 1)
		{
			$tpl  = explode(':', $params->def('template'));
			$jtpl = $tpl[ 0 ];

			if($tpl[ 0 ] === '_')
			{
				$jtpl = $app->getTemplate();
			}

			if(is_file(JPATH_SITE . '/templates/' . $jtpl . '/html/mod_junewsultra/' . $tpl[ 1 ] . '/css/style.css'))
			{
				$css = 'templates/' . $jtpl . '/html/mod_junewsultra/' . $tpl[ 1 ] . '/css/style.css';
				$doc->addStyleSheet(Uri::base() . $css);
			}
			elseif(is_file(JPATH_SITE . '/modules/mod_junewsultra/tmpl/' . $tpl[ 1 ] . '/css/style.css'))
			{
				$css = 'modules/mod_junewsultra/tmpl/' . $tpl[ 1 ] . '/css/style.css';
				$doc->addStyleSheet(Uri::base() . $css);
			}
		}

		return true;
	}
}