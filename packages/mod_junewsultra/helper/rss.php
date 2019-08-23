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

class rss extends Helper
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
		return $this->xml($params->get('rssurl'), $params->get('rsscount'), 'cache/' . md5($params->get('rssurl')) . '.xml', $params->get('cache_time'), '/rss/channel/item', $params->get('ordering_xml'));
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
				$item->cattitle = strip_tags($item->category);
			}

			// rawtext
			if($junews[ 'sourcetext' ] == '1')
			{
				$item->sourcetext = (isset($item->content_encoded) ? $item->content_encoded : $item->description);
			}

			// introtext
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

			// fulltext
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

			// author
			if($junews[ 'show_author' ] == 1)
			{
				$_author      = explode('(', $item->author);
				$item->author = str_replace(')', '', $_author[ '1' ]);
			}

			// date
			if($junews[ 'show_date' ] == 1)
			{
				$item->sqldate = date('Y-m-d H:i:s', strtotime($item->pubDate));
				$_date_type    = strtotime($item->pubDate);

				$item->date = HTMLHelper::date($_date_type, $junews[ 'data_format' ]);
				$item->df_d = HTMLHelper::date($_date_type, $junews[ 'date_day' ]);
				$item->df_m = HTMLHelper::date($_date_type, $junews[ 'date_month' ]);
				$item->df_y = HTMLHelper::date($_date_type, $junews[ 'date_year' ]);
			}

			// hits
			if($junews[ 'show_hits' ] == 1)
			{
				$item->hits = '-';
			}

			// rating
			if($junews[ 'show_rating' ] == 1)
			{
				$item->rating = $this->rating($params, $item->rating);
			}

			if($junews[ 'show_image' ] == 1)
			{
				$_text = (isset($item->content_encoded) ? $item->content_encoded : $item->description);

				$title_alt = $item->title_alt;
				if($junews[ 'imglink' ] == 1)
				{
					$imlink  = '<a href="' . $item->link . '"' . ($params->get('tips') == 1 ? ' title="' . $title_alt . '"' : '') . '>';
					$imlink2 = '</a>';
				}
				else
				{
					$imlink  = '';
					$imlink2 = '';
				}

				if(isset($item->enclosure->attributes()->url))
				{
					$junuimgsource      = $item->enclosure->attributes()->url;
					$item->source_image = $junuimgsource;
				}
				else
				{
					$imgmatch = $this->url($_text);

					if(preg_match('/<img(.*?)src="(.*?)"(.*?)>\s*(<\/img>)?/', $imgmatch, $imgsource))
					{
						$item->source_image = $imgsource[ 2 ];
					}
				}

				switch($junews[ 'thumb_width' ])
				{
					case '0':
						if($junews[ 'defaultimg' ] == 1 && (!$junuimgsource))
						{
							$junuimgsource = 'media/mod_junewsultra/' . $junews[ 'noimage' ];
						}

						if($junuimgsource)
						{
							$contentimage = $imlink . '<img src="' . $junuimgsource . '" alt="' . $title_alt . '" />' . $imlink2;
						}

						if($junuimgsource)
						{
							$item->image       = $contentimage;
							$item->imagesource = $junuimgsource;
						}

						break;
					case '1':
					default:
						if($junews[ 'defaultimg' ] == 1 && (!$junuimgsource))
						{
							$junuimgsource = 'media/mod_junewsultra/' . $junews[ 'noimage' ];
						}

						$aspect = 0;
						if($junews[ 'auto_zoomcrop' ] == '1')
						{
							$aspect = $JULibs->aspect($junuimgsource);
						}

						if($aspect >= '1' && $junews[ 'auto_zoomcrop' ] == '1')
						{
							$newimgparams = [
								'far' => '1',
								'bg'  => $junews[ 'zoomcropbg' ]
							];
						}
						else
						{
							$newimgparams = [
								'zc' => $junews[ 'zoomcrop' ] == 1 ? $junews[ 'zoomcrop_params' ] : ''
							];
						}

						if($junews[ 'farcrop' ] == '1')
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
							'cache' => 'img'
						];

						$imgparams_merge = array_merge($imgparams, $newimgparams);

						$thumb_img    = $this->juimg->render($junuimgsource, $imgparams_merge);
						$contentimage = $imlink . '<img src="' . $thumb_img . '" alt="' . $title_alt . '">' . $imlink2;

						if(($junews[ 'youtube_img_show' ] == 1) && ($junews[ 'link_enabled' ] == 1) && ($junuimgsource !== ''))
						{
							//Youtube
							$_text = str_replace([
								'//www.youtube.com',
								'//youtube.com',
								'https://www.youtube.com',
								'https://youtube.com',
								'https://www.youtu.be',
								'https://youtu.be'
							], 'http://www.youtube.com', $_text);

							if(preg_match_all('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^>"&?/ ]{11})%i', $_text, $match))
							{
								$junuimgsource = 'https://www.youtube.com/watch?v=' . $match[ 1 ][ 0 ];
							}
							elseif(preg_match_all('#(player.vimeo.com)/video/(\d+)#i', $_text, $match))
							{
								$junuimgsource = 'http://vimeo.com/' . $match[ 2 ];
							}

							$thumb_img         = $this->image($params, [
								'src' => $junuimgsource,
								'alt' => $title_alt
							]);
							$item->image       = $imlink . $thumb_img . $imlink2;
							$item->imagesource = $yimg;
						}

						if($junuimgsource)
						{
							$item->image       = $contentimage;
							$item->imagesource = $junuimgsource;
						}
						break;
				}
			}
		}

		return $items;
	}
}