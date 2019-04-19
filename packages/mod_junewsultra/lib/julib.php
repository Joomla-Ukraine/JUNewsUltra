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

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\String\StringHelper;

/**
 * Static class to handle loading of libraries.
 *
 * @package          Joomla.Site
 * @subpackage       mod_junewsultra
 * @since            6.0
 */
class JULibs
{
	/**
	 * @param $params
	 * @param $title
	 *
	 * @return string
	 *
	 * @since 6.0
	 */
	public static function _Title($params, $title)
	{
		$title = strip_tags($title);
		$title = htmlspecialchars($title);

		if($params->def('title_prepare') == 1)
		{
			$title = HTMLHelper::_('content.prepare', $title);
		}

		if($params->get('title_limit', 0) == '1')
		{
			if($params->get('title_limit_mode') == '1')
			{
				$title = trim(implode(' ', array_slice(explode(' ', $title), 0, $params->get('title_limit_count'))));
			}
			else
			{
				$title = trim(StringHelper::substr($title, 0, $params->get('title_limit_count')));
			}

			if(!preg_match('#(\.|\?|\!)$#ismu', $title))
			{
				$title = preg_replace('#^\s?(\,|\;|\:|\-)#ismu', '', $title);
				$title = ($title ? $title . $params->get('end_limit_title', '...') : '');
			}
		}

		return $title;
	}

	/**
	 * @param $params
	 * @param $description
	 * @param $cleartag
	 * @param $allowed_tags
	 * @param $li
	 * @param $text_limit
	 * @param $lmttext
	 * @param $end_limit_text
	 *
	 * @return mixed
	 *
	 * @since 6.0
	 */
	public static function _Description($params, $description, $cleartag, $allowed_tags, $li, $text_limit, $lmttext, $end_limit_text)
	{
		if($params->def('content_prepare') == 1)
		{
			$description = HTMLHelper::_('content.prepare', $description);
		}
		else
		{
			$description = preg_replace('/<p>\s*{([a-zA-Z0-9\-_]*)\s*(.*?)}\s*<\/p>/i', '', $description);
			$description = preg_replace('/{([a-zA-Z0-9\-_]*)\s*(.*?)}/i', '', $description);
			$description = preg_replace('/\[(.*?)\s?.*?\].*?\[\/(.*?)\]/i', '', $description);
		}

		$description = preg_replace('/::cck::(.*?)::\/cck::/i', '', $description);
		$description = preg_replace('/::introtext::(.*?)::\/introtext::/i', '\\1', $description);
		$description = preg_replace('/::fulltext::(.*?)::\/fulltext::/i', '\\2', $description);

		if($cleartag == '1')
		{
			$description = str_replace('&nbsp;', ' ', $description);
			$description = preg_replace('/(<\/[^>]+?>)(<[^>\/][^>]*?>)/', '$1 $2', $description);
			if($allowed_tags)
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

		if($li == '1')
		{
			$dots = 1;

			switch($lmttext)
			{
				case '1':
					$description = trim(implode(' ', array_slice(explode(' ', $description), 0, $text_limit)));
					break;

				case '2':
					$description = preg_replace('#<p(.*)>"#is', '<p>', $description);

					if(preg_match('#<p[^>]*>(.*)<\/p>#isU', $description, $matches))
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
					$description = trim(StringHelper::substr($description, 0, $text_limit));
					break;
			}

			if((!preg_match('#(\.|\?|\!)$#ismu', $description)) && ($dots == 1))
			{
				$description = preg_replace('#^\s?(\,|\;|\:|\-)#ismu', '', $description);
				$description = ($description ? $description . $end_limit_text : '');
			}
		}

		return $description;
	}

	/**
	 * @param $html
	 * @param $_cropaspect
	 *
	 * @return float|int
	 *
	 * @since 6.0
	 */
	public static function aspect($html, $_cropaspect)
	{
		$size   = getimagesize(rawurldecode(JPATH_SITE . '/' . $html));
		$width  = $size[ 0 ];
		$height = $size[ 1 ] * ($_cropaspect != '' ? $_cropaspect : '0');

		return $height / $width;
	}

	/**
	 * @param $params
	 * @param $rating
	 *
	 * @return string
	 *
	 * @since v6.0
	 */
	public static function _RatingStar($params, $rating)
	{
		$tpl = explode(':', $params->def('template'));

		$starImageOn = HTMLHelper::_('image', 'system/rating_star.png', $rating, null, true);
		if(is_file(JPATH_SITE . '/modules/mod_junewsultra/tmpl/' . $tpl[ 1 ] . '/images/rating_star.png'))
		{
			$starImageOn = JHTML::_('image', Uri::base() . 'modules/mod_junewsultra/tmpl/' . $tpl[ 1 ] . '/images/rating_star.png', $rating, null, true);
		}

		$starImageOff = HTMLHelper::_('image', 'system/rating_star_blank.png', $rating, null, true);
		if(is_file(JPATH_SITE . '/modules/mod_junewsultra/tmpl/' . $tpl[ 1 ] . '/images/rating_star_blank.png'))
		{
			$starImageOff = JHTML::_('image', Uri::base() . 'modules/mod_junewsultra/tmpl/' . $tpl[ 1 ] . '/images/rating_star_blank.png', $rating, null, true);
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
	 * @param        $feed_url
	 * @param        $xmlcount
	 * @param string $cache_file
	 * @param        $time
	 * @param string $path
	 * @param        $ordering_xml
	 *
	 * @return array|SimpleXMLElement[]
	 *
	 * @since 6.0
	 */
	public static function ParceXML($feed_url, $xmlcount, $cache_file, $time, $path, $ordering_xml)
	{
		$timedif = @(time() - filemtime($cache_file));

		if(file_exists($cache_file) && ($timedif < $time))
		{
			$string = @file_get_contents(Uri::root() . $cache_file);
			$error  = 0;
		}
		else
		{
			try
			{
				if(strpos($http_response_header[ 0 ], '200'))
				{
					$string = @file_get_contents($feed_url);
				}
				else
				{
					$ch = curl_init($feed_url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Safari/534.45');
					$string = curl_exec($ch);
				}

				$string = preg_replace('/<(\/)?([a-z0-9]+):([a-z0-9]+)/i', '<$1$2_$3', $string);
				$string = preg_replace('/<feed.*?>/i', '<feed>', $string);

				if($f = @fopen($cache_file, 'wb'))
				{
					fwrite($f, $string, strlen($string));
					fclose($f);
				}

				$error = 0;
			}
			catch (Exception $e)
			{
				$error = 1;
			}
		}

		if($error == 1 && is_file(JPATH_SITE . '/' . $cache_file))
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
			echo '<div class="alert alert-warning">' . Text::_('MOD_JUNEWS_RSSXML_ERROR') . '</div>';
		}

		return $items;
	}

	/**
	 * @param string $url
	 *
	 * @return mixed|string
	 *
	 * @since 6.0
	 */
	public static function video($url)
	{
		$urls = parse_url($url);
		$yid  = '';
		$vid  = '';

		if($urls[ 'host' ] === 'vimeo.com')
		{
			$vid = ltrim($urls[ 'path' ], '/');
		}
		elseif($urls[ 'host' ] === 'youtu.be')
		{
			$yid = ltrim($urls[ 'path' ], '/');
		}
		elseif(strpos($urls[ 'path' ], 'embed') == 1)
		{
			$_yid = explode('/', $urls[ 'path' ]);
			$yid  = end($_yid);
		}
		elseif(strpos($url, '/') === false)
		{
			$yid = $url;
		}
		else
		{
			$feature = '';

			parse_str($urls[ 'query' ], $output);
			$yid = $output[ 'v' ];
			if(!empty($feature))
			{
				$_yid = explode('v=', $urls[ 'query' ]);
				$yid  = end($_yid);
				$arr  = explode('&', $yid);
				$yid  = $arr[ 0 ];
			}
		}

		if($yid)
		{
			$ytpath = 'https://img.youtube.com/vi/' . $yid;

			if(self::_http($ytpath . '/maxresdefault.jpg') === '200')
			{
				$img = $ytpath . '/maxresdefault.jpg';
			}
			elseif(self::_http($ytpath . '/hqdefault.jpg') === '200')
			{
				$img = $ytpath . '/hqdefault.jpg';
			}
			elseif(self::_http($ytpath . '/mqdefault.jpg') === '200')
			{
				$img = $ytpath . '/mqdefault.jpg';
			}
			elseif(self::_http($ytpath . '/sddefault.jpg') === '200')
			{
				$img = $ytpath . '/sddefault.jpg';
			}
			else
			{
				$img = $ytpath . '/default.jpg';
			}

			return $img;
		}

		if($vid)
		{
			$vimeoObject = json_decode(file_get_contents('http://vimeo.com/api/v2/video/' . $vid . '.json'));

			if(!empty($vimeoObject))
			{
				return $vimeoObject[ 0 ]->thumbnail_large;
			}
		}

		return true;
	}

	/**
	 * @param $url
	 *
	 * @return bool|string
	 *
	 * @since 6.0
	 */
	public static function _http($url)
	{
		if(function_exists('curl_version'))
		{
			$ch = curl_init();

			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HEADER, true);
			curl_setopt($ch, CURLOPT_NOBODY, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);

			$header = curl_exec($ch);
			$head   = substr($header, 9, 3);
		}
		else
		{
			$header = get_headers($url);
			$head   = substr($header[ 0 ], 9, 3);
		}

		return $head;
	}

	/**
	 * @param $html
	 *
	 * @return string
	 *
	 * @since 6.0
	 */
	public static function absoluteURL($html)
	{
		$root_url = Uri::base();

		$html = preg_replace('@href="(?!http://)(?!https://)(?!mailto:)([^"]+)"@i', "href=\"{$root_url}\${1}\"", $html);
		$html = preg_replace('@src="(?!http://)(?!https://)([^"]+)"@i', "src=\"{$root_url}\${1}\"", $html);

		return $html;
	}
}