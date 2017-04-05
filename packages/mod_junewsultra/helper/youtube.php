<?php
/**
 * JUNewsUltra Pro
 *
 * @version 	6.x
 * @package 	UNewsUltra Pro
 * @author 		Denys D. Nosov (denys@joomla-ua.org)
 * @copyright 	(C) 2007-2017 by Denys D. Nosov (http://joomla-ua.org)
 * @license 	GNU/GPL: http://www.gnu.org/copyleft/gpl.html
 *
 **/

defined('_JEXEC') or die;

class youtube extends modJUNewsUltraHelper
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
        $JULibs             = new JULibs();
        $JUImg              = new JUImg();

		switch ($params->get('yttype'))
		{
			case '2':
				$ytxml = 'https://www.youtube.com/feeds/videos.xml?user='. $params->get('ytaccount');
			    break;
			case '1':
				$ytxml = 'https://www.youtube.com/feeds/videos.xml?playlist_id='. $params->get('ytplaylist');
    			break;
			case '3':
				$ytxml = 'https://www.youtube.com/feeds/videos.xml?channel_id=='. $params->get('ytchannel');
    			break;
		}

		// Selects data
		$items = $JULibs->ParceXML(
		    $ytxml,
			$params->get('ytcount'),
			'cache/'. md5($ytxml) .'.xml',
			$params->get('cache_time'),
			'/feed/entry',
			$params->get('ordering_xml')
		);

		foreach ($items as &$item)
        {
        	$item->link = $item->link->attributes()->href;
            if($params->get('ytlink') == 1) {
                $item->link = str_replace('/watch?v=', '/embed/', $item->link);
            }

            // article title
            if($junews['show_title'] == 1) {
                $item->title = $JULibs->_Title($params, $item->title);
            }

            // title for attr title and alt
            $item->title_alt = htmlspecialchars( strip_tags( $JULibs->_Title($params, $item->title) ) );

            // category title
            if($junews['show_cat'] == 1) $item->cattitle = '';

            // rawtext
			if($junews['sourcetext'] == '1') $item->sourcetext = (isset($item->media_group->media_description) ? $item->media_group->media_description : '');

            // introtext
            if($junews['show_intro'] == '1')
            {
                $item->introtext = $JULibs->_Description(
				    $params,
					$item->media_group->media_description,
					$junews['cleartag'],
					$junews['allowed_intro_tags'],
					$junews['li'],
					$junews['introtext_limit'],
					$junews['lmttext'],
					$junews['end_limit_introtext']
				);
            }

            // fulltext
            $item->fulltext = '';

            // author
            if ($junews['show_author'] == 1) $item->author = $item->author->name;

            // date
            if ($junews['show_date'] == 1)
            {
        		$_date_type = date("Y-m-d H:i:s", strtotime($item->published));;

                $item->sqldate = $_date_type;

                $item->date = JHtml::date($_date_type, $junews['data_format']);
                $item->df_d = JHtml::date($_date_type, $junews['date_day']);
                $item->df_m = JHtml::date($_date_type, $junews['date_month']);
                $item->df_y = JHtml::date($_date_type, $junews['date_year']);
            }

            // hits
            if ($junews['show_hits'] == 1) {
                $item->hits = $item->media_group->media_community->media_statistics->attributes()->views;
            }

            // rating
            if ($junews['show_rating'] == 1) {
                $item->rating = $JULibs->_RatingStar($params, $item->media_group->media_community->media_starRating->attributes()->count);
            }

            if ($junews['show_image'] == 1)
            {
                $title_alt = $item->title_alt;
                $imlink  = '';
                $imlink2 = '';

                if ($junews['imglink'] == 1)
                {
                    $imlink  = '<a href="'. $item->link .'"'. ($params->get('tips') == 1 ? ' title="'. $title_alt .'"' : '') .'>';
                    $imlink2 = '</a>';
                }

	        	$junuimgsource = $item->media_group->media_thumbnail->attributes()->url;
	            $item->source_image = $junuimgsource;

        		switch ($junews['thumb_width'])
                {
        			case '0':

						if($junews['defaultimg'] == 1)
                        {
                            if(!$junuimgsource) {
                                $junuimgsource = 'media/mod_junewsultra/' . $junews['noimage'];
                            }
                        }

                        if($junuimgsource)
						{
                            $contentimage = $imlink .'<img src="'. $junuimgsource .'" alt="'. $title_alt .'" />'. $imlink2;
                            $item->image    = $contentimage;
                            $item->imagesource = $junuimgsource;
                        }
						elseif($junews['defaultimg'] == 1) {
                            $item->image    = $blankimage;
                        }

            			break;
                    case '1':
        			default:

                        if($junews['defaultimg'] == 1)
                        {
                            if(!$junuimgsource) {
                                $junuimgsource = 'media/mod_junewsultra/'. $junews['noimage'];
                            }
                        }

						if($junews['auto_zoomcrop'] == '1') {
							$aspect = $JULibs->aspect($junuimgsource);
						}

						if ($aspect >= '1' && $junews['auto_zoomcrop'] == '1') {
							$newimgparams = array(
							    'far' => '1',
								'bg'   => $junews['zoomcropbg']
							);
						}
                        else {
							$newimgparams = array(
							    'zc' => ($junews['zoomcrop'] == 1 ? $junews['zoomcrop_params'] : '')
							);
						}

						if ($junews['farcrop'] == '1') {
							$newimgparams = array(
							    'far' => $junews['farcrop_params'],
								'bg'   => $junews['farcropbg']
							);
						}

                        $imgparams = array(
                            'w'     => $junews['w'],
                            'h'     => $junews['h'],
				            'sx'   	=> ($junews['sx'] ? $junews['sx'] : ''),
				            'sy'   	=> ($junews['sy'] ? $junews['sy'] : ''),
				            'sw'   	=> ($junews['sw'] ? $junews['sw'] : ''),
				            'sh'   	=> ($junews['sh'] ? $junews['sh'] : ''),
                            'f'     => $junews['f'],
                            'q'     => $junews['q'],
                            'cache' => 'img'
                        );

				        $imgparams_merge = array_merge(
					        $imgparams,
					        $newimgparams
				        );

                        $thumb_img = $JUImg->Render($junuimgsource, $imgparams_merge);
                        $contentimage = $imlink .'<img src="'. $thumb_img .'" alt="'. $title_alt .'" />'. $imlink2;

                        $item->image = $contentimage;
                        $item->imagesource = $junuimgsource;

        			    break;
                }
            }
		}

		return $items;
	}
}