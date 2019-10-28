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

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

/**
 * Helper for mod_junewsultra
 *
 * @package     Joomla.Site
 * @subpackage  mod_junewsultra
 * @since       6.0
 */
class youtube extends Helper
{
	/**
	 * @param $params
	 * @param $junews
	 *
	 * @return array|bool|\SimpleXMLElement[]
	 *
	 * @since 6.0
	 */
	public function query($params, $junews)
	{
		switch($params->get('yttype'))
		{
			case '2':
				$ytxml = 'https://www.youtube.com/feeds/videos.xml?user=' . $params->get('ytaccount');
				break;
			case '1':
				$ytxml = 'https://www.youtube.com/feeds/videos.xml?playlist_id=' . $params->get('ytplaylist');
				break;
			case '3':
				$ytxml = 'https://www.youtube.com/feeds/videos.xml?channel_id=' . $params->get('ytchannel');
				break;
		}

		return $this->xml($ytxml, $params->get('ytcount'), 'cache/' . md5($ytxml) . '.xml', $params->get('cache_time'), '/feed/entry', $params->get('ordering_xml'));
	}

	/**
	 * @param $params
	 * @param $junews
	 *
	 * @return array|SimpleXMLElement[]|void
	 *
	 * @since 6.0
	 */
	public function getList($params, $junews)
	{
		$items = $this->query($params, $junews);

		foreach($items as &$item)
		{
			$item->link = $item->link->attributes()->href;
			if($params->get('ytlink') == 1)
			{
				$item->link = str_replace('/watch?v=', '/embed/', $item->link);
			}

			// article title
			if($junews[ 'show_title' ] == 1)
			{
				$item->title = $this->title($params, $item->title);
			}

			// title for attr title and alt
			$item->title_alt = htmlspecialchars(strip_tags($this->title($params, $item->title)));

			// category title
			if($junews[ 'show_cat' ] == 1)
			{
				$item->cattitle = '';
			}

			// rawtext
			if($junews[ 'sourcetext' ] == 1)
			{
				$item->sourcetext = (isset($item->media_group->media_description) ? $item->media_group->media_description : '');
			}

			// introtext
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

			// fulltext
			$item->fulltext = '';

			// author
			if($junews[ 'show_author' ] == 1)
			{
				$item->author = $item->author->name;
			}

			// date
			if($junews[ 'show_date' ] == 1)
			{
				$item->sqldate = date('Y-m-d H:i:s', strtotime($item->published));
				$_date_type    = strtotime($item->published);

				$item->date = HTMLHelper::date($_date_type, $junews[ 'data_format' ]);
				$item->df_d = HTMLHelper::date($_date_type, $junews[ 'date_day' ]);
				$item->df_m = HTMLHelper::date($_date_type, $junews[ 'date_month' ]);
				$item->df_y = HTMLHelper::date($_date_type, $junews[ 'date_year' ]);
			}

			// hits
			if($junews[ 'show_hits' ] == 1)
			{
				$item->hits = $item->media_group->media_community->media_statistics->attributes()->views;
			}

			// rating
			if($junews[ 'show_rating' ] == 1)
			{
				$item->rating = $this->rating($params, $item->media_group->media_community->media_starRating->attributes()->count);
			}

			if($junews[ 'show_image' ] == 1)
			{
				$title_alt          = $item->title_alt;
				$junuimgsource      = $item->media_group->media_thumbnail->attributes()->url;
				$item->source_image = $junuimgsource;

				$blank = 1;
				if(!$junuimgsource || !file_exists($junuimgsource))
				{
					$blank = 0;
					if($junews[ 'defaultimg' ] == 1)
					{
						$junuimgsource = 'media/mod_junewsultra/' . $junews[ 'noimage' ];
						$blank         = 1;
					}
				}

				$item->image       = '';
				$item->imagelink   = '';
				$item->imagesource = '';

				switch($junews[ 'thumb_width' ])
				{
					case '0':
						if($blank == 1)
						{
							$item->image       = $this->image($params, $junews, [
								'src'  => $junuimgsource,
								'link' => $junews[ 'imglink' ] == 1 ? $item->link : '',
								'alt'  => $title_alt
							]);
							$item->imagelink   = $junuimgsource;
							$item->imagesource = $junuimgsource;
						}
						break;

					case '1':
					default:
						if($blank == 1)
						{
							$item->image       = $this->image($params, $junews, [
								'src'    => $junuimgsource,
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