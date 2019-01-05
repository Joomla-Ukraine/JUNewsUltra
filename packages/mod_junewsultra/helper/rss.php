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


class rss extends modJUNewsUltraHelper
{
	/**
	 * @param $params
	 * @param $junews
	 *
	 * @return array|SimpleXMLElement[]|void
	 *
	 * @since 6.0
	 */
	public static function getList($params, $junews)
	{
		// Load libs
		$JULibs = new JULibs();
		$JUImg  = new JUImg();

		// Selects data
		$items = $JULibs->ParceXML(
			$params->get('rssurl'),
			$params->get('rsscount'),
			'cache/' . md5($params->get('rssurl')) . '.xml',
			$params->get('cache_time'),
			'/rss/channel/item',
			$params->get('ordering_xml')
		);

		foreach($items as &$item)
		{
			// article title
			if($junews[ 'show_title' ] == 1)
			{
				$item->title = $JULibs->_Title($params, $item->title);
			}

			// title for attr title and alt
			$item->title_alt = htmlspecialchars(strip_tags($JULibs->_Title($params, $item->title)));

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
				$item->introtext = $JULibs->_Description(
					$params,
					$item->description,
					$junews[ 'cleartag' ],
					$junews[ 'allowed_intro_tags' ],
					$junews[ 'li' ],
					$junews[ 'introtext_limit' ],
					$junews[ 'lmttext' ],
					$junews[ 'end_limit_introtext' ]
				);
			}

			// fulltext
			if($junews[ 'show_full' ] == '1' && isset($item->content_encoded))
			{
				$item->fulltext = $JULibs->_Description(
					$params,
					$item->content_encoded,
					$junews[ 'clear_tag_full' ],
					$junews[ 'allowed_full_tags' ],
					$junews[ 'li_full' ],
					$junews[ 'fulltext_limit' ],
					$junews[ 'lmttext_full' ],
					$junews[ 'end_limit_fulltext' ]
				);
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

				$item->date = JHtml::date($_date_type, $junews[ 'data_format' ]);
				$item->df_d = JHtml::date($_date_type, $junews[ 'date_day' ]);
				$item->df_m = JHtml::date($_date_type, $junews[ 'date_month' ]);
				$item->df_y = JHtml::date($_date_type, $junews[ 'date_year' ]);
			}

			// hits
			if($junews[ 'show_hits' ] == 1)
			{
				$item->hits = '-';
			}

			// rating
			if($junews[ 'show_rating' ] == 1)
			{
				$item->rating = $JULibs->_RatingStar($params, $item->rating);
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
					$imgmatch = $JULibs->absoluteURL($_text);

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

						if($junews[ 'youtube_img_show' ] == 1 && ($junuimgsource != ''))
						{
							$regex1 = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^>"&?/ ]{11})%i';
							$regex2 = '#(player.vimeo.com)/video/([0-9]+)#i';

							if(preg_match($regex1, $_text, $match))
							{
								$yimg              = $JULibs->video('http://youtu.be/' . $match[ 1 ]);
								$item->image       = $imlink . '<img src="' . $yimg . '" width="' . $junews[ 'w' ] . '" alt="' . $title_alt . '" />' . $imlink2;
								$item->imagesource = $yimg;
							}
							elseif(preg_match($regex2, $_text, $match))
							{
								$yimg              = $JULibs->video('http://vimeo.com/' . $match[ 2 ]);
								$item->image       = $imlink . '<img src="' . $yimg . '" width="' . $junews[ 'w' ] . '" alt="' . $title_alt . '" />' . $imlink2;
								$item->imagesource = $yimg;
							}
							elseif($junuimgsource)
							{
								$item->image = $contentimage;
							}
							elseif($junews[ 'defaultimg' ] == 1)
							{
								$item->image = '';
							}
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
							$newimgparams = array(
								'far' => '1',
								'bg'  => $junews[ 'zoomcropbg' ]
							);
						}
						else
						{
							$newimgparams = array(
								'zc' => $junews[ 'zoomcrop' ] == 1 ? $junews[ 'zoomcrop_params' ] : ''
							);
						}

						if($junews[ 'farcrop' ] == '1')
						{
							$newimgparams = array(
								'far' => $junews[ 'farcrop_params' ],
								'bg'  => $junews[ 'farcropbg' ]
							);
						}

						$imgparams = array(
							'w'     => $junews[ 'w' ],
							'h'     => $junews[ 'h' ],
							'sx'    => $junews[ 'sx' ] ? : '',
							'sy'    => $junews[ 'sy' ] ? : '',
							'sw'    => $junews[ 'sw' ] ? : '',
							'sh'    => $junews[ 'sh' ] ? : '',
							'f'     => $junews[ 'f' ],
							'q'     => $junews[ 'q' ],
							'cache' => 'img'
						);

						$imgparams_merge = array_merge(
							$imgparams,
							$newimgparams
						);

						$thumb_img    = $JUImg->Render($junuimgsource, $imgparams_merge);
						$contentimage = $imlink . '<img src="' . $thumb_img . '" alt="' . $title_alt . '" />' . $imlink2;

						if(($junews[ 'youtube_img_show' ] == 1) && ($junews[ 'link_enabled' ] == 1) && ($junuimgsource != ''))
						{
							$regex1 = '%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^>"&?/ ]{11})%i';
							$regex2 = '#(player.vimeo.com)/video/([0-9]+)#i';

							$yimg = '';
							if(preg_match($regex1, $_text, $match))
							{
								$yimg = $JULibs->video('http://youtu.be/' . $match[ 1 ]);
							}
							elseif(preg_match($regex2, $_text, $match))
							{
								$yimg = $JULibs->video('http://vimeo.com/' . $match[ 2 ]);
							}

							$thumb_img         = $JUImg->Render($yimg, $imgparams);
							$item->image       = $imlink . '<img src="' . $thumb_img . '" alt="' . $title_alt . '" />' . $imlink2;
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