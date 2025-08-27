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

use Joomla\CMS\HTML\HTMLHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Helper for mod_junewsultra
 *
 * @since       6.0
 * @subpackage  mod_junewsultra
 * @package     Joomla.Site
 */
#[AllowDynamicProperties]
class youtube extends Helper
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
		$ytxml = '';
		switch($params->get('yttype'))
		{
			case '2':
				$ytxml = 'https://youtube.com/feeds/videos.xml?user=' . $params->get('ytaccount');
				break;
			case '1':
				$ytxml = 'https://youtube.com/feeds/videos.xml?playlist_id=' . $params->get('ytplaylist');
				break;
			case '3':
				$ytxml = 'https://youtube.com/feeds/videos.xml?channel_id=' . $params->get('ytchannel');
				break;
		}

		return $this->xml($ytxml, $params->get('ytcount'), 'cache/' . md5($ytxml) . '.xml', $params->get('cache_time'), '/feed/entry', $params->get('ordering_xml'));
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
		try
		{
			$items = $this->query($params, $junews);

			foreach($items as $item)
			{
				$item->link = $item->link->attributes()->href;
				if($params->get('ytlink') == 1)
				{
					$item->link = str_replace('/watch?v=', '/embed/', $item->link);
				}

				if($junews[ 'show_title' ] == 1)
				{
					$item->title = $this->title($params, $item->title);
				}

				$item->title_alt = htmlspecialchars(strip_tags($this->title($params, $item->title)));

				if($junews[ 'show_cat' ] == 1)
				{
					$item->cattitle = '';
				}

				if($junews[ 'sourcetext' ] == 1)
				{
					$item->sourcetext = $item->media_group->media_description ?? '';
				}

				if($junews[ 'show_intro' ] == 1)
				{
					$item->introtext = $this->desc($params, [
						'description'    => $item->introtext,
						'cleartag'       => $junews[ 'cleartag' ],
						'allowed_tags'   => $junews[ 'allowed_intro_tags' ],
						'li'             => $junews[ 'li' ],
						'text_limit'     => $junews[ 'introtext_limit' ],
						'lmttext'        => $junews[ 'lmttext' ],
						'end_limit_text' => $junews[ 'end_limit_introtext' ]
					]);
				}

				$item->fulltext = '';

				if($junews[ 'show_author' ] == 1)
				{
					$item->author = $item->author->name;
				}

				if($junews[ 'show_date' ] == 1)
				{
					$item->sqldate = date('Y-m-d H:i:s', strtotime($item->published));
					$_date_type    = strtotime($item->published);

					$item->date = HTMLHelper::date($_date_type, $junews[ 'data_format' ]);
					$item->df_d = HTMLHelper::date($_date_type, $junews[ 'date_day' ]);
					$item->df_m = HTMLHelper::date($_date_type, $junews[ 'date_month' ]);
					$item->df_y = HTMLHelper::date($_date_type, $junews[ 'date_year' ]);
				}

				if($junews[ 'show_hits' ] == 1)
				{
					$item->hits = $item->media_group->media_community->media_statistics->attributes()->views;
				}

				if($junews[ 'show_rating' ] == 1)
				{
					$item->rating = $this->rating($params, $item->media_group->media_community->media_starRating->attributes()->count);
				}

				if($junews[ 'show_image' ] == 1)
				{
					$title_alt = $item->title_alt;

					$junuimgsource = '';
					if(isset($item->media_group->media_thumbnail, $item->media_group->media_thumbnail->attributes()->url))
					{
						$junuimgsource      = $item->media_group->media_thumbnail->attributes()->url;
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
		}
		catch (Exception)
		{
			$items = [];
		}

		return $items;
	}
}