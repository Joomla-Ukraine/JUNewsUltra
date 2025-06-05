<?php
/**
 * JUNewsUltra Pro
 *
 * @package          Joomla.Site
 * @subpackage       mod_junewsultra
 *
 * @author           Denys Nosov, denys@joomla-ua.org
 * @copyright        2007-2025 (C) Joomla! Ukraine, https://joomla-ua.org. All rights reserved.
 * @license          GNU/GPL - https://www.gnu.org/copyleft/gpl.html
 */

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\String\StringHelper;

defined('_JEXEC') or die;

/**
 * Helper for mod_junewsultra
 *
 * @since       6.0
 * @property \Joomla\CMS\User\User                       $user* @package     Joomla.Site
 * @property \Joomla\CMS\Date\Date                       $date
 * @property \Joomla\CMS\Application\CMSApplication|null $app
 * @property \JDocument|null                             $doc
 * @property \JDatabaseDriver|null                       $db
 * @property \JDatabaseQuery|string                      $q
 * @property string                                      $nulldate
 * @property string                                      $nowdate
 * @property \JUImage                                    $juimg
 * @property \Joomla\CMS\Language\Language|null          $lang
 * @subpackage  mod_junewsultra
 */
class Helper
{
	/**
	 * modJUNewsUltraHelper constructor.
	 *
	 * @throws \Exception
	 * @since 6.0
	 */
	public function __construct()
	{
		$this->app      = Factory::getApplication();
		$this->lang     = $this->app->getLanguage();
		$this->user     = $this->app->getIdentity();
		$this->date     = Factory::getDate();
		$this->doc      = $this->app->getDocument();
		$this->db       = Factory::getContainer()->get(DatabaseInterface::class);
		$this->q        = $this->db->getQuery(true);
		$this->nulldate = $this->db->getNullDate();
		$this->nowdate  = $this->date->toSql();
		$this->juimg    = new JUImage\Image();
	}

	/**
	 * @param $params
	 * @param $junews
	 *
	 * @return bool|array
	 * @since 6.0
	 */
	public function query($params, $junews): bool|array
	{
		return true;
	}

	/**
	 * @param $order
	 *
	 * @return true|string
	 * @since 6.0
	 */
	public function order($order): bool|string
	{
		return true;
	}

	/**
	 * @param $params
	 * @param $junews
	 *
	 * @return true|array
	 * @since 6.0
	 */
	public function getList($params, $junews): bool|array
	{
		return true;
	}

	/**
	 * @param $html
	 *
	 * @return bool|string
	 *
	 * @since 6.0
	 */
	public function detect_video($html): bool|string
	{
		$youtube = [
			'//www.youtube.com',
			'//youtube.com',
			'https://www.youtube.com',
			'https://youtube.com',
			'https://www.youtu.be',
			'https://youtu.be'
		];
		$html    = str_replace($youtube, 'https://www.youtube.com', $html);

		if(preg_match_all('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^>"&?/ ]{11})%i', $html, $match))
		{
			return 'https://www.youtube.com/watch?v=' . $match[ 1 ][ 0 ];
		}

		if(preg_match_all('#(player.vimeo.com)/video/(\d+)#i', $html, $match))
		{
			return 'https://vimeo.com/' . $match[ 2 ];
		}

		return false;
	}

	/**
	 * @param          $params
	 * @param          $junews
	 * @param   array  $data
	 *
	 * @return string
	 *
	 * @since 6.0
	 */
	public function image($params, $junews, array $data = []): string
	{
		$attr = [];

		if($params->get('image_loading', 1) == 1)
		{
			$attr[] = 'loading="lazy"';
		}

		if($params->get('image_decoding', 1) == 1)
		{
			$attr[] = 'decoding="async"';
		}

		if(isset($data[ 'src' ]) && $data[ 'src' ] !== '')
		{
			$src = $data[ 'src' ];
			if($junews[ 'thumb_width' ] == 1)
			{
				$src = $this->thumb($data[ 'src' ], $junews);

				if($junews[ 'usewebp' ] == 1)
				{
					if($src->webp !== '')
					{
						$source = '<source srcset="' . $src->webp . '" type="image/webp">';
					}

					$src = $src->img;
				}
			}

			$attr[] = 'src="' . $src . '"';
		}

		if($junews[ 'thumb_width' ] == 0)
		{
			$attr[] = 'width="' . $data[ 'w' ] . '"';
			$attr[] = 'height="' . $data[ 'h' ] . '"';
		}

		if($junews[ 'usesrcset' ] == 0 && $junews[ 'thumb_width' ] == 1)
		{
			if($junews[ 'zoomcrop' ] == 1)
			{
				$attr[] = 'width="' . $junews[ 'w' ] . '"';
				$attr[] = 'height="' . $junews[ 'h' ] . '"';
			}
			else
			{
				$size   = $this->juimg->size(JPATH_SITE . '/' . $src);
				$attr[] = 'width="' . $size->width . '"';
				$attr[] = 'height="' . $size->height . '"';
			}
		}

		if(isset($data[ 'alt' ]))
		{
			$attr[] = 'alt="' . $data[ 'alt' ] . '"';
		}

		if($params->get('image_css_class') !== '' && $params->get('image_css_class'))
		{
			$attr[] = 'class="' . $params->get('image_css_class') . '"';
		}

		if($params->get('image_attr') !== '')
		{
			$attr[] = $params->get('image_attr');
		}

		if($junews[ 'usesrcset' ] == 1 && $junews[ 'thumb_width' ] == 1)
		{
			$source_set = [];
			$array      = (array) $junews[ 'src_picture' ];

			foreach($array as $picture)
			{
				if($picture->picture && $picture->picture_w && $picture->picture_h)
				{
					$imgsetparams_merge = array_replace($junews, [
						'w' => $picture->picture_w,
						'h' => $picture->picture_h,
					]);
					$thumb_imgset       = $this->thumb($data[ 'src' ], $imgsetparams_merge);

					if($junews[ 'usewebp' ] == 1)
					{
						if(isset($src->webp) && $src->webp !== '')
						{
							$source_set[] = '<source media="(min-width: ' . $picture->picture . 'px)" srcset="' . $thumb_imgset->webp . '" type="image/webp">';
						}

						$source_set[] = '<source media="(min-width: ' . $picture->picture . 'px)" srcset="' . $thumb_imgset->img . '">';
					}
					else
					{
						$source_set[] = '<source media="(min-width: ' . $picture->picture . 'px)" srcset="' . $thumb_imgset . '">';
					}
				}
			}

			$source = implode($source_set);
		}

		$img = '<img ' . implode(' ', $attr) . '>';
		if(($junews[ 'usesrcset' ] == 1 || $junews[ 'usewebp' ] == 1) && $junews[ 'thumb_width' ] == 1)
		{
			$img = '<picture>' . $source . $img . '</picture>';
		}

		if($data[ 'link' ])
		{
			$img = '<a href="' . $data[ 'link' ] . '"' . ($params->get('tips') == 1 && isset($data[ 'alt' ]) ? ' title="' . $data[ 'alt' ] . '"' : '') . '>' . $img . '</a>';
		}

		return $img;
	}

	/**
	 * @param          $image
	 * @param   array  $junews
	 *
	 * @return object|string
	 *
	 * @since 6.0
	 */
	public function thumb($image, array $junews = []): object|string
	{
		if($image)
		{
			$aspect = 0;
			if($junews[ 'auto_zoomcrop' ] == 1)
			{
				$aspect = $this->aspect($image, $junews[ 'cropaspect' ]);
			}

			$newimgparams = [
				'zc' => $junews[ 'zoomcrop' ] == 1 ? $junews[ 'zoomcrop_params' ] : ''
			];
			if($aspect >= 1 && $junews[ 'auto_zoomcrop' ] == 1)
			{
				$newimgparams = [
					'far' => '1',
					'bg'  => $junews[ 'zoomcropbg' ]
				];
			}

			if($junews[ 'farcrop' ] == 1)
			{
				$newimgparams = [
					'far' => $junews[ 'farcrop_params' ],
					'bg'  => $junews[ 'farcropbg' ]
				];
			}

			$imgparams = [
				'w'     => $junews[ 'w' ],
				'h'     => $junews[ 'h' ],
				'sx'    => $junews[ 'sx' ] ? : '',
				'sy'    => $junews[ 'sy' ] ? : '',
				'sw'    => $junews[ 'sw' ] ? : '',
				'sh'    => $junews[ 'sh' ] ? : '',
				'f'     => $junews[ 'f' ],
				'q'     => $junews[ 'q' ],
				'cache' => $junews[ 'image_thumb' ]
			];

			$webp    = [];
			$webp_im = [];
			if($junews[ 'usewebp' ] == 1)
			{
				$webp = [ 'webp' => true ];

				if($junews[ 'usegd2webp' ] == 1)
				{
					$webp_im = [ 'imagemagick' => false ];
				}
			}

			$imgparams_merge = array_merge($imgparams, $newimgparams, $webp, $webp_im);

			return $this->juimg->render($image, $imgparams_merge);
		}

		return '';
	}

	/**
	 * @param $html
	 * @param $_cropaspect
	 *
	 * @return float|int
	 *
	 * @since 6.0
	 */
	public function aspect($html, $_cropaspect): float|int
	{
		$size   = $this->juimg->size(rawurldecode(JPATH_SITE . '/' . $html));
		$width  = $size->width;
		$height = ($size->height * ($_cropaspect != '' ? $_cropaspect : '0'));

		return ($height / $width);
	}

	/**
	 * @param $params
	 * @param $title
	 *
	 * @return string
	 *
	 * @since 6.0
	 */
	public function title($params, $title): string
	{
		$title             = strip_tags($title);
		$title             = htmlspecialchars($title);
		$title             = trim($title);
		$end_limit_title   = $params->get('end_limit_title', '…');
		$title_limit_count = $params->get('title_limit_count');

		if($params->get('title_prepare') == 1)
		{
			$title = HTMLHelper::_('content.prepare', $title);
		}

		if($params->get('title_limit', 0) == 1)
		{
			if($params->get('title_limit_mode') == 1)
			{
				$title_length = count(explode(' ', $title));
				$title        = trim(implode(' ', array_slice(explode(' ', $title), 0, $title_limit_count)));
			}
			else
			{
				$title_length = StringHelper::strlen($title);
				$title        = trim(StringHelper::substr($title, 0, $title_limit_count));
			}

			if(!preg_match('#([.?!])$#imu', $title))
			{
				$sufix = '';
				if($title_length > $title_limit_count)
				{
					$sufix = $end_limit_title;
				}

				$title = preg_replace('#^\s?([,;:\-])#imu', '', $title);
				$title = ($title ? $title . $sufix : '');
			}
		}

		return $title;
	}

	/**
	 * @param          $params
	 * @param   array  $data
	 *
	 * @return string
	 *
	 * @since 6.0
	 */
	public function desc($params, array $data = []): string
	{
		$description = $data[ 'description' ];

		if($params->get('content_prepare') == 1)
		{
			$description = HTMLHelper::_('content.prepare', $description);
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

		if($data[ 'cleartag' ] == 1)
		{
			$description = str_replace('&nbsp;', ' ', $description);
			$description = preg_replace('/(<\/[^>]+?>)(<[^>\/][^>]*?>)/', '$1 $2', $description);

			if($data[ 'allowed_tags' ] !== '')
			{
				$allowed_tags = str_replace([
					' ',
					'&nbsp;',
					'    '
				], '', $data[ 'allowed_tags' ]);
				$tags         = '<' . str_replace(',', '><', $allowed_tags) . '>';
				$description  = strip_tags($description, $tags);
			}
			else
			{
				$description = strip_tags($description);
			}
		}

		if($data[ 'li' ] == 1)
		{
			$dots = 1;
			switch($data[ 'lmttext' ])
			{
				case '1':
					$description_length = count(explode(' ', $description));
					$description        = trim(implode(' ', array_slice(explode(' ', $description), 0, $data[ 'text_limit' ])));
					break;

				case '2':
					$description_length = StringHelper::strlen($description);
					$description        = preg_replace('#<p(.*)>"#is', '<p>', $description);

					if(preg_match('#<p[^>]*>(.*)</p>#isU', $description, $matches))
					{
						$description = $matches[ 0 ];
						$dots        = 0;
					}
					break;

				case '3':
					$description_length = StringHelper::strlen($description);
					if(preg_match('#^.{100}.*?[.!?]#is', strip_tags($description), $matches))
					{
						$description = $matches[ 0 ];
						$dots        = 0;
					}
					break;

				default:
				case '0':
					$description_length = StringHelper::strlen($description);
					$description        = trim(StringHelper::substr($description, 0, $data[ 'text_limit' ]));
					break;
			}

			if((!preg_match('#([.?!])$#imu', $description)) && $dots == 1)
			{
				$sufix = '';
				if($description_length > $data[ 'text_limit' ])
				{
					$sufix = $data[ 'end_limit_text' ];
				}

				$description = preg_replace('#^\s?([,;:\-])#imu', '', $description);
				$description = ($description ? $description . $sufix : '');
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
	public function rating($params, $rating): string
	{
		$tpl        = explode(':', $params->get('template'));
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

		$img = str_repeat($starImageOn, $rating);
		for($i = $rating; $i < 5; $i++)
		{
			$img .= $starImageOff;
		}

		return $img;
	}

	/**
	 * @param $html
	 *
	 * @return string|null
	 *
	 * @since 6.0
	 */
	public function url($html): string|null
	{
		$root_url = Uri::base();
		$html     = preg_replace('@href="(?!http://)(?!https://)(?!mailto:)([^"]+)"@i', "href=\"$root_url\${1}\"", $html);

		return preg_replace('@src="(?!http://)(?!https://)([^"]+)"@i', "src=\"$root_url\${1}\"", $html);
	}

	/**
	 * @param $feed_url
	 * @param $xmlcount
	 * @param $cache_file
	 * @param $time
	 * @param $path
	 * @param $ordering_xml
	 *
	 * @return array|string
	 *
	 * @since 6.0
	 */
	public function xml($feed_url, $xmlcount, $cache_file, $time, $path, $ordering_xml): array|string
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
				$string = file_get_contents($feed_url);
				$string = preg_replace('/<(\/)?([a-z0-9]+):([a-z0-9]+)/i', '<$1$2_$3', $string);
				$string = preg_replace('/<feed.*?>/i', '<feed>', $string);

				if($f = fopen($cache_file, 'wb'))
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
			$items = '';
			$xml   = new SimpleXmlElement($string);

			switch($ordering_xml)
			{
				case 'rand':
					$items = $xml->xpath(sprintf($path . '[position()]', $xmlcount));
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
			echo '<div class="uk-alert-warning" data-uk-alert>' . Text::_('MOD_JUNEWS_RSSXML_ERROR') . '</div>';
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
	public function loadJQ($params): bool
	{
		if($params->get('jquery') == 1)
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
	public function loadBS($params): bool
	{
		if($params->get('bootstrap_js') == 1)
		{
			HTMLHelper::_('bootstrap.framework');
		}

		if($params->get('bootstrap_css') == 1)
		{
			$direction = ($this->lang->isRtl() ? 'rtl' : 'ltr');

			JHtmlBootstrap::loadCss(true, $direction);
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
	public function loadCSS($params): bool
	{
		if($params->get('cssstyle') == 1)
		{
			$tpl  = explode(':', $params->get('template'));
			$jtpl = $tpl[ 0 ];

			if($tpl[ 0 ] === '_')
			{
				$jtpl = $this->app->getTemplate();
			}

			if(is_file(JPATH_SITE . '/templates/' . $jtpl . '/html/mod_junewsultra/' . $tpl[ 1 ] . '/css/style.css'))
			{
				$css = 'templates/' . $jtpl . '/html/mod_junewsultra/' . $tpl[ 1 ] . '/css/style.css';
				$this->doc->addStyleSheet(Uri::base() . $css);
			}
			elseif(is_file(JPATH_SITE . '/modules/mod_junewsultra/tmpl/' . $tpl[ 1 ] . '/css/style.css'))
			{
				$css = 'modules/mod_junewsultra/tmpl/' . $tpl[ 1 ] . '/css/style.css';
				$this->doc->addStyleSheet(Uri::base() . $css);
			}
		}

		return true;
	}
}