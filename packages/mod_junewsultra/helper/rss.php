<?php
/**
 * JUNewsUltra Pro
 *
 * @package          Joomla.Site
 * @subpackage       mod_junewsultra
 *
 * @author           Denys Nosov, denys@joomla-ua.org
 * @copyright        2007-2025 (C) Joomla! Ukraine, https://joomla-ua.org. All rights reserved.
 * @license          GNU/GPL - https://gnu.org/copyleft/gpl.html
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

/**
 * Helper for mod_junewsultra
 *
 * @since       6.0
 * @subpackage  mod_junewsultra
 * @package     Joomla.Site
 */
#[AllowDynamicProperties]
class rss extends Helper
{
	/**
	 * @param $params
	 * @param $junews
	 *
	 * @return array
	 *
	 * @since 6.0
	 */
	public function query($params, $junews): array
	{
		return $this->xml($params->get('rssurl'), $params->get('rsscount'), 'cache/' . md5($params->get('rssurl')) . '.xml', $params->get('cache_time'), '/rss/channel/item', $params->get('ordering_xml'));
	}

	/**
	 * @param $params
	 * @param $junews
	 *
	 * @return array
	 *
	 * @since 6.0
	 */
	public function getList($params, $junews): array
	{
		$items = $this->query($params, $junews);

		foreach($items as $item)
		{
			if($junews[ 'show_title' ] == 1)
			{
				$item->title = $this->title($params, $item->title);
			}

			$item->title_alt = htmlspecialchars(strip_tags($this->title($params, $item->title)));

			if($junews[ 'show_cat' ] == 1)
			{
				$item->cattitle = strip_tags($item->category);
			}

			if($junews[ 'sourcetext' ] == '1')
			{
				$item->sourcetext = ($item->content_encoded ?? $item->description);
			}

			if($junews[ 'show_intro' ] == '1')
			{
				$item->introtext = $this->desc($params, [
					'description'    => $item->description,
					'cleartag'       => $junews[ 'cleartag' ],
					'allowed_tags'   => $junews[ 'allowed_intro_tags' ],
					'li'             => $junews[ 'li' ],
					'text_limit'     => $junews[ 'introtext_limit' ],
					'lmttext'        => $junews[ 'lmttext' ],
					'end_limit_text' => $junews[ 'end_limit_introtext' ]
				]);
			}

			if($junews[ 'show_full' ] == '1' && isset($item->content_encoded))
			{
				$item->fulltext = $this->desc($params, [
					'description'    => $item->content_encoded,
					'cleartag'       => $junews[ 'clear_tag_full' ],
					'allowed_tags'   => $junews[ 'allowed_full_tags' ],
					'li'             => $junews[ 'li_full' ],
					'text_limit'     => $junews[ 'fulltext_limit' ],
					'lmttext'        => $junews[ 'lmttext_full' ],
					'end_limit_text' => $junews[ 'end_limit_fulltext' ]
				]);
			}

			if($junews[ 'show_author' ] == 1)
			{
				$_author      = explode('(', $item->author);
				$item->author = str_replace(')', '', $_author[ '1' ]);
			}

			if($junews[ 'show_date' ] == 1)
			{
				$item->sqldate = date('Y-m-d H:i:s', strtotime($item->pubDate));
				$_date_type    = strtotime($item->pubDate);
				$item->date    = HTMLHelper::date($_date_type, $junews[ 'data_format' ]);
				$item->df_d    = HTMLHelper::date($_date_type, $junews[ 'date_day' ]);
				$item->df_m    = HTMLHelper::date($_date_type, $junews[ 'date_month' ]);
				$item->df_y    = HTMLHelper::date($_date_type, $junews[ 'date_year' ]);
			}

			if($junews[ 'show_hits' ] == 1)
			{
				$item->hits = '-';
			}

			if($junews[ 'show_rating' ] == 1)
			{
				$item->rating = $this->rating($params, $item->rating);
			}

			if($junews[ 'show_image' ] == 1)
			{
				$_text     = ($item->content_encoded ?? $item->description);
				$title_alt = $item->title_alt;

				$junuimgsource = '';
				if(isset($item->enclosure, $item->enclosure->attributes()->url))
				{
					$junuimgsource      = (string) $item->enclosure->attributes()->url;
					$item->source_image = $junuimgsource;
				}
				elseif(preg_match('/<img[^>]+>/i', $this->url($_text), $imgsource))
				{
					preg_match('/src="(.*?)"/i', $imgsource[ 0 ], $img);
					$junuimgsource      = $img[ 1 ];
					$item->source_image = $junuimgsource;
				}

				if(!$junuimgsource && $junews[ 'defaultimg' ] == 1 && $junews[ 'noimage' ])
				{
					$junuimgsource = 'media/mod_junewsultra/' . $junews[ 'noimage' ];
				}

				$item->image       = '';
				$item->imagelink   = '';
				$item->imagesource = '';
				switch($junews[ 'thumb_width' ])
				{
					case '0':
						if($junuimgsource)
						{
							$item->image       = $this->image($params, $junews, [
								'src'  => $junuimgsource,
								'w'    => $junews[ 'w' ],
								'h'    => $junews[ 'h' ],
								'link' => $junews[ 'imglink' ] == 1 ? $item->link : '',
								'alt'  => $title_alt
							]);
							$item->imagelink   = $junuimgsource;
							$item->imagesource = $junuimgsource;
						}
						break;
					case '1':
					default:
						if($junuimgsource)
						{
							$item->image       = $this->image($params, $junews, [
								'src'    => $junuimgsource,
								'w'      => $junews[ 'w' ],
								'h'      => $junews[ 'h' ],
								'link'   => $junews[ 'imglink' ] == 1 ? $item->link : '',
								'alt'    => $title_alt,
								'srcset' => $junews[ 'usesrcset' ]
							]);
							$item->imagelink   = $this->thumb($junuimgsource, $junews);
							$item->imagesource = $junuimgsource;
						}
						break;
				}
			}
		}

		return $items;
	}
}