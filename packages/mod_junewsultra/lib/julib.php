<?php

/**
 * JUNewsUltra Pro
 *
 * @version          6.x
 * @package          JUNewsUltra Pro
 * @author           Denys D. Nosov (denys@joomla-ua.org)
 * @copyright    (C) 2007-2017 by Denys D. Nosov (http://joomla-ua.org)
 * @license          GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 *
 **/
class JULibs
{
	/**
	 * @param $params
	 * @param $title
	 *
	 * @return title
	 *
	 * @since 6.0
	 */
	public static function _Title($params, $title)
	{
		$title = strip_tags($title);
		$title = htmlspecialchars($title);

		if($params->def('title_prepare') == 1)
		{
			$title = JHtml::_('content.prepare', $title);
		}

		if($params->get('title_limit', 0) == '1')
		{
			if($params->get('title_limit_mode') == '1')
			{
				$title = trim(implode(" ", array_slice(explode(" ", $title), 0, $params->get('title_limit_count'))));
			}
			else
			{
				$title = trim(JString::substr($title, 0, $params->get('title_limit_count')));
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
	static function _Description($params, $description, $cleartag, $allowed_tags, $li, $text_limit, $lmttext, $end_limit_text)
	{
		if($params->def('content_prepare') == 1)
		{
			$description = JHtml::_('content.prepare', $description);
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
			if($allowed_tags)
			{
				$allowed_tags = str_replace(array(' ', '&nbsp;', '    '), '', $allowed_tags);
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

			switch ($lmttext)
			{
				case '1':
					$description = trim(implode(" ", array_slice(explode(" ", $description), 0, $text_limit)));
					break;
				case '2':
					$description = preg_replace('#<p(.*)>"#is', '<p>', $description);

					if(preg_match('#<p[^>]*>(.*)<\/p>#isU', $description, $matches))
					{
						$description = $matches[0];
						$dots        = 0;
					}
					break;
				case '3':
					if(preg_match('#^.{100}.*?[.!?]#is', strip_tags($description), $matches))
					{
						$description = $matches[0];
						$dots        = 0;
					}
					break;
				default:
				case '0':
					$description = trim(JString::substr($description, 0, $text_limit));
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
		$width  = $size[0];
		$height = $size[1] * ($_cropaspect != '' ? $_cropaspect : '0');
		$aspect = $height / $width;

		return $aspect;
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
		$app = JFactory::getApplication();
		$tpl = explode(":", $params->def('template'));

		if($tpl[0] == '_')
		{
			$jtpl = $app->getTemplate();
		}
		else
		{
			$jtpl = $tpl[0];
		}

		$starImageOn = JHtml::_('image', 'system/rating_star.png', $rating, null, true);
		if(is_file(JPATH_SITE . '/modules/mod_junewsultra/tmpl/' . $tpl[1] . '/images/rating_star.png'))
		{
			$starImageOn = JHTML::_('image', JURI::base() . 'modules/mod_junewsultra/tmpl/' . $tpl[1] . '/images/rating_star.png', $rating, null, true);
		}

		$starImageOff = JHtml::_('image', 'system/rating_star_blank.png', $rating, null, true);
		if(is_file(JPATH_SITE . '/modules/mod_junewsultra/tmpl/' . $tpl[1] . '/images/rating_star_blank.png'))
		{
			$starImageOff = JHTML::_('image', JURI::base() . 'modules/mod_junewsultra/tmpl/' . $tpl[1] . '/images/rating_star_blank.png', $rating, null, true);
		}

		$img = '';
		for ($i = 0; $i < $rating; $i++)
		{
			$img .= $starImageOn;
		}

		for ($i = $rating; $i < 5; $i++)
		{
			$img .= $starImageOff;
		}

		return $img;
	}

	/**
	 * @param $feed_url
	 * @param $xmlcount
	 * @param $cache_file
	 * @param $time
	 * @param $path
	 * @param $ordering_xml
	 *
	 * @return array|SimpleXMLElement[]|void
	 *
	 * @since 6.0
	 */
	static function ParceXML($feed_url, $xmlcount, $cache_file, $time, $path, $ordering_xml)
	{
		$timedif = @(time() - filemtime($cache_file));

		if(file_exists($cache_file) && $timedif < $time)
		{
			$string = file_get_contents(JURI::root() . $cache_file);
		}
		else
		{
			try
			{
				if(strpos($http_response_header[0], "200"))
				{
					$string = @file_get_contents($feed_url);
				}
				else
				{
					$ch = curl_init($feed_url);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_USERAGENT, array('User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7) AppleWebKit/534.46 (KHTML, like Gecko) Version/5.1 Safari/534.45'));
					$string = curl_exec($ch);
				}
				$string = preg_replace('/<(\/)?([a-z0-9]+):([a-z0-9]+)/i', '<$1$2_$3', $string);
				$string = preg_replace('/<feed.*?>/i', '<feed>', $string);

				if($f = @fopen($cache_file, 'w'))
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
			$string = file_get_contents(JURI::root() . $cache_file);
		}

		libxml_use_internal_errors(true);

		try
		{
			$xml = new SimpleXmlElement($string);

			switch ($ordering_xml)
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
			return '<div class="alert alert-warning">' . JText::_('MOD_JUNEWS_RSSXML_ERROR') . '</div>';
		}

		return $items;
	}

	/**
	 * @param        $url
	 * @param string $return
	 *
	 * @return mixed|string
	 *
	 * @since 6.0
	 */
	public static function video($url, $return = 'hqthumb')
	{
		$urls = parse_url($url);

		if($urls['host'] == 'vimeo.com')
		{
			$vid = ltrim($urls['path'], '/');
		}
		elseif($urls['host'] == 'youtu.be')
		{
			$yid = ltrim($urls['path'], '/');
		}
		elseif(strpos($urls['path'], 'embed') == 1)
		{
			$yid = end(explode('/', $urls['path']));
		}
		elseif(strpos($url, '/') === false)
		{
			$yid = $url;
		}
		else
		{
			parse_str($urls['query'], $output);
			$yid = $output['v'];
			if(!empty($feature))
			{
				$yid = end(explode('v=', $urls['query']));
				$arr = explode('&', $yid);
				$yid = $arr[0];
			}
		}

		if($yid)
		{
			if($return == 'hqthumb')
			{
				return 'http://i1.ytimg.com/vi/' . $yid . '/hqdefault.jpg';
			}
			else
			{
				return $yid;
			}
		}
		elseif($vid)
		{
			$vimeoObject = json_decode(file_get_contents("http://vimeo.com/api/v2/video/" . $vid . ".json"));
			if(!empty($vimeoObject))
			{
				if($return == 'hqthumb')
				{
					return $vimeoObject[0]->thumbnail_large;
				}
				else
				{
					return $vid;
				}
			}
		}
	}

	/**
	 * @param $html
	 *
	 * @return mixed
	 *
	 * @since 6.0
	 */
	public static function absoluteURL($html)
	{
		$root_url = JURI::base();

		$html = preg_replace('@href="(?!http://)(?!https://)(?!mailto:)([^"]+)"@i', "href=\"{$root_url}\${1}\"", $html);
		$html = preg_replace('@src="(?!http://)(?!https://)([^"]+)"@i', "src=\"{$root_url}\${1}\"", $html);

		return $html;
	}
}