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

use Joomla\CMS\Access\Access;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

require_once JPATH_SITE . '/components/com_content/router.php';
require_once JPATH_SITE . '/components/com_content/helpers/route.php';

/**
 * Helper for mod_junewsultra
 *
 * @package     Joomla.Site
 * @subpackage  mod_junewsultra
 * @since       6.0
 */
class com_content extends Helper
{
	public function query($params, $junews)
	{
		$ordering           = $params->get('ordering', 'id_desc');
		$catid              = $params->get('catid', null);
		$show_attribs       = (int) $params->get('show_attribs');
		$wheresql           = (int) $params->get('wheresql');
		$where              = $params->get('where');
		$display_article    = $params->def('display_article');
		$useaccess          = (int) $params->get('useaccess', 0);
		$user_id            = (int) $params->get('user_id');
		$uid                = (int) $params->get('uid');
		$dateuser_filtering = $params->get('dateuser_filtering', 0);
		$date_filtering     = $params->get('date_filtering', 0);
		$relative_date      = $params->get('relative_date', 0);
		$date_type          = $params->get('date_type', 'created');
		$date_field         = $params->get('date_field', 'a.created');

		if($useaccess == 1)
		{
			$groups = implode(',', $this->user->getAuthorisedViewLevels());
		}

		// Ordering
		switch($ordering)
		{
			case 'title_asc':
				$orderBy = 'a.title';
				break;
			case 'title_desc':
				$orderBy = 'a.title DESC';
				break;
			case 'id_asc':
				$orderBy = 'a.id';
				break;
			case 'id_desc':
				$orderBy = 'a.id DESC';
				break;
			case 'hits_asc':
				$orderBy = 'a.hits';
				break;
			case 'hits_desc':
				$orderBy = 'a.hits DESC';
				break;
			case 'rating_asc':
				$orderBy = 'rating';
				break;
			case 'rating_desc':
				$orderBy = 'rating DESC';
				break;
			case 'created_asc':
				$orderBy = 'a.created';
				break;
			case 'modified_desc':
				$orderBy = 'a.modified DESC';
				break;
			case 'modified_created_dsc':
				$orderBy = 'a.modified DESC, a.created';
				break;
			case 'modified_touch_dsc':
				$orderBy = 'CASE WHEN (' . $this->db->qn('a.modified') . ' = ' . $this->db->q($this->nulldate) . ') THEN a.created ELSE a.modified END';
				break;
			case 'ordering_asc':
				$orderBy = 'a.ordering';
				break;
			case 'ordering_desc':
				$orderBy = 'a.ordering DESC';
				break;
			case 'rand':
				$orderBy = 'rand()';
				break;
			case 'publish_dsc':
				$orderBy = 'a.publish_up DESC';
				break;
			case 'created_desc':
			default:
				$orderBy = 'a.created DESC';
				break;
		}

		// Access filter
		$access = '1';
		if($useaccess == 1)
		{
			$access = !ComponentHelper::getParams('com_content')
			                          ->get('show_noauth');
		}

		// Category
		$cat_arr = [];
		if($catid)
		{
			$cat_arr[] = $catid;
		}

		if(is_array($catid))
		{
			$cat_arr = [];
			foreach($catid as $key => $curr)
			{
				if((int) $curr)
				{
					$cat_arr[ $key ] = (int) $curr;
				}
			}
		}

		// Selects data
		$this->query->select([
			'a.id',
			'a.state',
			'a.alias',
			'a.publish_up',
			'a.publish_down'
		]);

		if($junews[ 'show_title' ] == 1 || $junews[ 'show_image' ] == 1)
		{
			$this->query->select([ 'a.title' ]);
		}

		if($junews[ 'sourcetext' ] == 1 || $junews[ 'show_intro' ] == 1 || ($junews[ 'show_image' ] == 1 && $junews[ 'image_source' ] == 0 && ($junews[ 'introfulltext' ] == 0 || $junews[ 'introfulltext' ] == 2)))
		{
			$this->query->select([ 'a.introtext' ]);
		}

		if($junews[ 'sourcetext' ] == 1 || $junews[ 'show_full' ] == 1 || ($junews[ 'show_image' ] == 1 && $junews[ 'image_source' ] == 0 && ($junews[ 'introfulltext' ] == 1 || $junews[ 'introfulltext' ] == 2)))
		{
			$this->query->select([ 'a.fulltext' ]);
		}

		if(Multilanguage::isEnabled())
		{
			$this->query->select([ 'a.language' ]);
			$this->query->where($this->db->qn('a.language') . ' IN (' . $this->db->q($this->lang->getTag()) . ',' . $this->db->quote('*') . ')');
		}

		if($junews[ 'image_source' ] > 0 && $junews[ 'show_image' ] == 1)
		{
			$this->query->select([ 'a.images' ]);
		}

		if(($junews[ 'show_date' ] == 1 || $ordering === 'created_asc' || $ordering === 'created_desc') && ($ordering === 'created_asc' || $ordering === 'created_desc' || $ordering === 'modified_created_dsc' || $ordering === 'modified_touch_dsc' || $date_type === 'created' || $date_field === 'a.created' || $date_filtering == 1 || $dateuser_filtering == 1))
		{
			$this->query->select([ 'a.created' ]);
		}

		if(($junews[ 'show_date' ] == 1 || $ordering === 'modified_asc' || $ordering === 'modified_desc' || $ordering === 'modified_created_dsc' || $ordering === 'modified_touch_dsc') && ($ordering === 'modified_asc' || $ordering === 'modified_desc' || $ordering === 'modified_created_dsc' || $ordering === 'modified_touch_dsc' || $date_type === 'modified' || $date_field === 'a.modified' || $date_filtering == 1))
		{
			$this->query->select([ 'a.modified' ]);
		}

		if($user_id)
		{
			$this->query->select([ 'a.modified_by' ]);
		}

		if($useaccess == 1)
		{
			$this->query->select([ 'a.access' ]);
		}

		if($junews[ 'featured' ] != 0)
		{
			$this->query->select([ 'a.featured' ]);
		}

		if($junews[ 'show_author' ] == 1 || $dateuser_filtering == 1 || $user_id || $this->user->get('id') > 0)
		{
			$this->query->select([ 'a.created_by' ]);
		}

		if($junews[ 'show_author' ] == 1)
		{
			$this->query->select([ 'a.created_by_alias' ]);
		}

		if($show_attribs == 1)
		{
			$this->query->select([ 'a.attribs' ]);
		}

		if($ordering === 'ordering_asc' || $ordering === 'ordering_desc')
		{
			$this->query->select([ 'a.ordering' ]);
		}

		if($junews[ 'show_hits' ] == 1 || $ordering === 'hits_asc' || $ordering === 'hits_desc')
		{
			$this->query->select([ 'a.hits' ]);
		}

		if(is_array($catid) || $access)
		{
			$this->query->select([ 'a.catid' ]);
		}

		// From
		$this->query->from('#__content AS a');

		// Categories
		if($junews[ 'show_cat' ] == 1)
		{
			$this->query->select([ 'cc.title AS category_title' ]);
			$this->query->join('LEFT', '#__categories AS cc ON cc.id = a.catid');
		}

		// Multicategories plugin integration
		if($junews[ 'multicat' ] == 1)
		{
			$this->query->select([ 'cmc.category_id AS cmc_cat' ]);
			$this->query->join('LEFT', '#__contentmulticategories_categories AS cmc ON cmc.article_id = a.id');
		}

		// User
		if($junews[ 'show_author' ] == 1)
		{
			$this->query->select([ 'u.name AS author' ]);
			$this->query->join('LEFT', '#__users AS u on u.id = a.created_by');
		}

		// Rating
		if($junews[ 'show_rating' ] == 1)
		{
			$this->query->select([ 'ROUND(v.rating_sum / v.rating_count, 0) AS rating' ]);
			$this->query->join('LEFT', '#__content_rating AS v ON a.id = v.content_id');
		}

		// Uniq author post
		if($dateuser_filtering == 1 || !empty($uid))
		{
			$ji_catids = '';
			if(is_array($cat_arr) && count($cat_arr))
			{
				$ji_catids = 'WHERE `catid` IN (' . implode(',', $cat_arr) . ')';
			}

			$this->query->join('INNER', '(SELECT max(`created`) MaxPostDate, `created_by` FROM `#__content` ' . $ji_catids . ' GROUP BY `created_by`) a2 ON a.created = a2.MaxPostDate');
		}

		// Where
		$this->query->where($this->db->qn('a.state') . ' = ' . $this->db->q('1'));
		$this->query->where('( a.publish_up = ' . $this->db->q($this->nulldate) . ' OR a.publish_up < ' . $this->db->q($this->nowdate) . ' )');
		$this->query->where('( a.publish_down = ' . $this->db->q($this->nulldate) . ' OR a.publish_down > ' . $this->db->q($this->nowdate) . ' )');

		// Select article or categories
		if($display_article == 1)
		{
			$this->query->where($this->db->qn('a.id') . ' = ' . $this->db->q((int) $params->def('articleid')));
		}
		else
		{
			if($date_filtering == 1)
			{
				switch($relative_date)
				{
					case '1':
						$startDateRange = $this->db->q($params->get('start_date_range', date('Y-m-d') . ' 00:00:00'));
						$endDateRange   = $this->db->q($params->get('end_date_range', date('Y-m-d H:i:s')));
						$this->query->where('(' . $this->db->qn($date_field) . ' > ' . $this->db->q($startDateRange) . ' AND ' . $this->db->qn($date_field) . ' < ' . $this->db->q($endDateRange) . ')');
						break;

					case '2':
						$this->query->where($this->db->qn($date_field) . ' > DATE_SUB(' . $this->db->q($this->nowdate) . ', INTERVAL 7 DAY)');
						break;

					case '3':
						$this->query->where($this->db->qn($date_field) . ' > DATE_SUB(' . $this->db->q($this->nowdate) . ', INTERVAL 14 DAY)');
						break;

					case '4':
						$this->query->where($this->db->qn($date_field) . ' > DATE_SUB(' . $this->db->q($this->nowdate) . ', INTERVAL ' . cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y')) . ' DAY)');
						break;

					case '5':
						$this->query->where($this->db->qn($date_field) . ' > DATE_SUB(' . $this->db->q($this->nowdate) . ', INTERVAL 365 DAY)');
						break;

					case '6':
						$this->query->where($this->db->qn($date_field) . ' > DATE_SUB(' . $this->db->q($this->nowdate) . ', INTERVAL ' . $params->get('custom_days', '30') . ' DAY)');
						break;

					case '0':
					default:
						$this->query->where($this->db->qn($date_field) . ' > DATE_SUB(' . $this->db->q($this->nowdate) . ', INTERVAL 1 DAY)');
						break;
				}
			}

			// Standart categories or multicategories (plugin integration)
			if(is_array($cat_arr) && count($cat_arr))
			{
				if($junews[ 'multicat' ] == 1)
				{
					$this->query->where('(' . $this->db->qn('a.catid') . ' IN (' . implode(',', $cat_arr) . ') OR cmc.category_id IN (' . implode(',', $cat_arr) . ') )');
				}
				else
				{
					$this->query->where($this->db->qn('a.catid') . ' IN (' . implode(',', $cat_arr) . ')');
				}
			}

			if($excluded_articles = $params->get('excluded_articles', null))
			{
				$excluded_articles = explode("\r\n", $excluded_articles);
				$this->query->where($this->db->qn('a.id') . ' NOT IN (' . implode(',', $excluded_articles) . ')');
			}

			switch($junews[ 'featured' ])
			{
				case '1':
					$this->query->where($this->db->qn('a.featured') . ' = ' . $this->db->q('1'));
					break;

				case '0':
					$this->query->where($this->db->qn('a.featured') . ' = ' . $this->db->q('0'));
					break;
			}

			switch($user_id)
			{
				case '0':
					if($uid > 0)
					{
						$this->query->where($this->db->qn('a.created_by') . ' = ' . $this->db->q($uid));
					}
					break;
				case 'by_me':
					$this->query->where('(' . $this->db->qn('a.created_by') . ' = ' . $this->db->q((int) $this->user->get('id')) . ' OR ' . $this->db->qn('a.modified_by') . ' = ' . $this->db->q((int) $this->user->get('id')) . ')');
					break;
				case 'not_me':
					$this->query->where('(' . $this->db->qn('a.created_by') . ' <> ' . $this->db->q((int) $this->user->get('id')) . ' AND ' . $this->db->qn('a.modified_by') . ' <> ' . $this->db->q((int) $this->user->get('id')) . ')');
					break;
			}
		}

		if($useaccess == 1)
		{
			$this->query->where($this->db->qn('a.access') . ' IN (' . $groups . ')');
		}

		// Custom WHERE SQL
		if($wheresql == 1)
		{
			$sqls = explode("\r\n", $where);
			if($params->get('sql_operator', 1) == '1')
			{
				foreach($sqls as $sql)
				{
					$this->query->where($sql);
				}
			}
			else
			{
				$this->query->where('(' . implode(' OR ', $sqls) . ')');
			}
		}

		$this->query->order($orderBy);
		$this->db->setQuery($this->query, $junews[ 'count_skip' ], $junews[ 'count' ]);

		return $this->db->loadObjectList();
	}

	/**
	 * @param $params
	 * @param $junews
	 *
	 * @return mixed
	 *
	 * @throws \Exception
	 * @since 6.0
	 */
	public function getList($params, $junews)
	{
		$useaccess = (int) $params->get('useaccess', 0);
		$date_type = $params->get('date_type', 'created');

		// Access filter
		$access     = '1';
		$authorised = [];
		if($useaccess == 1)
		{
			$access = !ComponentHelper::getParams('com_content')
			                          ->get('show_noauth');

			$authorised = Access::getAuthorisedViewLevels($this->user->get('id'));
		}

		$items = $this->query($params, $junews);

		// Comments integration
		if($params->def('use_comments') == 1 && count($items))
		{
			$comments_system = $params->def('select_comments');
			$comments        = JPATH_SITE . '/components/com_' . $comments_system . '/' . $comments_system . '.php';

			if(file_exists($comments))
			{
				$ids = [];
				foreach($items as $item)
				{
					$ids[] = $item->id;
				}

				if($comments_system === 'jcomments')
				{
					$this->lang->load('com_' . $comments_system, JPATH_SITE);
				}

				$this->query = $this->db->getQuery(true);

				switch($comments_system)
				{
					case 'komento':
						$this->query->select('cid, count(cid) AS cnt');
						$this->query->from('#__komento_comments');
						$this->query->where('component = "com_content" AND cid IN (' . implode(',', $ids) . ') AND published = "1"');
						$this->query->group('cid');
						$this->db->setQuery($this->query);

						$commentsCount  = $this->db->loadObjectList('cid');
						$comment_link   = '#section-komento';
						$comment_add    = $comment_link;
						$comment_text1  = 'COM_KOMENTO_FRONTPAGE_COMMENT';
						$comment_text2  = $comment_text1;
						$comment_plural = 0;
						break;

					default:
					case 'jcomments':
						$this->query->select('object_id, count(object_id) AS cnt');
						$this->query->from('#__jcomments');
						$this->query->where('object_group = "com_content" AND object_id IN (' . implode(',', $ids) . ') AND published = "1"');
						$this->query->group('object_id');
						$this->db->setQuery($this->query);

						$commentsCount  = $this->db->loadObjectList('object_id');
						$comment_link   = '#comments';
						$comment_add    = '#addcomments';
						$comment_text1  = 'LINK_READ_COMMENTS';
						$comment_text2  = 'LINK_ADD_COMMENT';
						$comment_plural = 1;
						break;
				}

				foreach($items as $item)
				{
					$item->comments      = isset($commentsCount[ $item->id ]) ? $commentsCount[ $item->id ]->cnt : 0;
					$item->commentslink  = $comment_link;
					$item->commentstext  = ($comment_plural == 1 ? Text::plural($comment_text1, $item->comments) : Text::_($comment_text1) . ' (' . $item->comments . ')');
					$item->commentscount = $item->comments;

					if($item->comments == 0)
					{
						$item->comments     = '';
						$item->commentslink = $comment_add;
						$item->commentstext = Text::_($comment_text2);
					}
				}
			}
			else
			{
				Factory::getApplication()
				       ->enqueueMessage(Text::_('MOD_JUNEWS_COMMENTS_NOT_INSTALLED'), 'error');
			}
		}

		foreach($items as &$item)
		{
			$item->link    = Route::_('index.php?option=com_users&view=login');
			$item->catlink = $item->link;

			if($access || in_array($item->access, $authorised, true))
			{
				$item->slug = $item->id . ($item->alias ? ':' . $item->alias : '');
				$language   = (Multilanguage::isEnabled() ? $item->language : '');
				$catid      = (!empty($item->cmc_cat) && $junews[ 'multicat' ] == 1 ? $item->cmc_cat : $item->catid);

				$item->link    = Route::_(ContentHelperRoute::getArticleRoute($item->slug, $catid, $language));
				$item->catlink = Route::_(ContentHelperRoute::getCategoryRoute($catid));
			}

			// article title
			if($junews[ 'show_title' ] == 1)
			{
				$item->title = $this->title($params, $item->title);
			}

			// title for attr title and alt
			$item->title_alt = $this->title($params, $item->title);

			// category title
			if($junews[ 'show_cat' ] == 1)
			{
				$cattitle       = strip_tags($item->category_title);
				$item->cattitle = $cattitle;

				if($params->get('showcatlink') == 1)
				{
					$item->cattitle = '<a href="' . $item->catlink . '">' . $cattitle . '</a>';
				}
			}

			if($junews[ 'show_image' ] == 1)
			{
				switch($junews[ 'introfulltext' ])
				{
					case '1':
						$_text = $item->fulltext;
						break;
					case '2':
						$_text = $item->introtext . $item->fulltext;
						break;
					default:
					case '0':
						$_text = $item->introtext;
						break;
				}

				$title_alt = $item->title_alt;
				$imlink    = '';
				$imlink2   = '';

				if($junews[ 'imglink' ] == 1)
				{
					$imlink  = '<a href="' . $item->link . '"' . ($params->get('tips') == 1 ? ' title="' . $title_alt . '"' : '') . '>';
					$imlink2 = '</a>';
				}

				$junuimgsource = '';

				if($junews[ 'image_source' ] == 0)
				{
					if(preg_match('/<img(.*?)src="(.*?)"(.*?)>\s*(<\/img>)?/', $_text, $junuimgsource))
					{
						$junuimgsource = $junuimgsource[ 2 ];
					}
					elseif(preg_match('/{gallery\s+(.*?)}/i', $_text, $junuimgsource) && $junews[ 'gallery' ] == 1)
					{
						$folder_match = $junuimgsource[ 1 ];
						$imglist      = explode('|', $folder_match);
						$imgsource    = $imglist[ 0 ];
						$root         = JPATH_BASE . '/';
						$folder       = 'images/' . $imgsource;
						$img_folder   = $root . $folder;
						$galleries    = glob($img_folder . '/{*.[jJ][pP][gG],*.[jJ][pP][eE][gG],*.[gG][iI][fF],*.[pP][nN][gG],*.[bB][mM][pP],*.[tT][iI][fF],*.[tT][iI][fF][fF]}', GLOB_BRACE);

						$junuimgsource = '';
						if(count($galleries) > 0 && is_dir($img_folder))
						{
							natcasesort($galleries);

							$i    = 0;
							$html = [];
							foreach($galleries as $gallery)
							{
								if($i > 0)
								{
									break;
								}

								$html[] = str_replace(JPATH_BASE . '/', '', $gallery);
								$i++;
							}

							$junuimgsource = $html[ 0 ];
						}
					}
					elseif($junews[ 'youtube_img_show' ] == 1)
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
					}
				}

				if($junews[ 'image_source' ] == 1 || $junews[ 'image_source' ] == 2 || $junews[ 'image_source' ] == 3)
				{
					// images from article parameters
					$images = json_decode($item->images);

					if((isset($images->image_intro) && !empty($images->image_intro)) || (isset($images->image_fulltext) && !empty($images->image_fulltext)))
					{
						if($junews[ 'image_source' ] === 1)
						{
							if(isset($images->image_intro) && !empty($images->image_intro))
							{
								$junuimgsource = htmlspecialchars($images->image_intro);

								// raw image source
								$item->imagesource = htmlspecialchars($images->image_intro);
							}
							elseif(isset($images->image_fulltext) && !empty($images->image_fulltext))
							{
								$junuimgsource = htmlspecialchars($images->image_fulltext);

								// raw image source
								$item->imagesource = htmlspecialchars($images->image_fulltext);
							}
						}
						elseif($junews[ 'image_source' ] === 2 && isset($images->image_intro) && !empty($images->image_intro))
						{
							$junuimgsource = htmlspecialchars($images->image_intro);

							// raw image source
							$item->imagesource = htmlspecialchars($images->image_intro);
						}
						elseif($junews[ 'image_source' ] === 3 && isset($images->image_fulltext) && !empty($images->image_fulltext))
						{
							$junuimgsource = htmlspecialchars($images->image_fulltext);

							// raw image source
							$item->imagesource = htmlspecialchars($images->image_fulltext);
						}
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
							$thumb_img = $this->image($params, [
								'src' => $junuimgsource,
								'alt' => $title_alt
							]);

							$item->image       = $imlink . $thumb_img . $imlink2;
							$item->imagelink   = $junuimgsource;
							$item->imagesource = $junuimgsource;
						}
						elseif($junews[ 'defaultimg' ] == 1)
						{
							$item->image       = '';
							$item->imagelink   = '';
							$item->imagesource = '';
						}
						break;

					case '1':
					default:
						$blank = 1;
						if(!$junuimgsource)
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

						if($blank == 1)
						{
							$aspect = 0;
							if($junews[ 'auto_zoomcrop' ] == 1)
							{
								$aspect = $this->aspect($junuimgsource, $junews[ 'cropaspect' ]);
							}

							if($aspect >= 1 && $junews[ 'auto_zoomcrop' ] == 1)
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
								'cache' => 'img'
							];

							$imgparams_merge = array_merge($imgparams, $newimgparams);

							switch($junews[ 'usesrcset' ])
							{
								case '1':
									$imgset      = $junews[ 'srcsetviewport' ];
									$attr_imgset = [];

									for($i = 0; $i <= 4; $i++)
									{
										if($imgset[ $i ])
										{
											switch($imgset[ $i ])
											{
												case '480':
													$zoom = 1.5;
													break;
												case '768':
													$zoom = 2;
													break;
												case '992':
													$zoom = 2.5;
													break;
												case '1200':
													$zoom = 3;
													break;
												case '360':
												default:
													$zoom = 1;
													break;
											}

											$imgsetparams = [
												'w'     => round($junews[ 'w' ] * $zoom),
												'h'     => round($junews[ 'h' ] * $zoom),
												'sx'    => $junews[ 'sx' ] ? : '',
												'sy'    => $junews[ 'sy' ] ? : '',
												'sw'    => $junews[ 'sw' ] ? : '',
												'sh'    => $junews[ 'sh' ] ? : '',
												'f'     => $junews[ 'f' ],
												'q'     => $junews[ 'q' ],
												'cache' => 'img'
											];

											$imgsetparams_merge = array_merge($imgsetparams, $newimgparams);
											$thumb_imgset       = Uri::base() . $this->juimg->render($junuimgsource, $imgsetparams_merge);
											$attr_imgset[]      = $thumb_imgset . ' ' . $imgset[ $i ] . 'w';
										}
									}

									$srcset = ' srcset="' . implode(', ', $attr_imgset) . '" ';
									break;

								case '2':
									$imgset      = $junews[ 'srcsetpixeldensity' ];
									$attr_imgset = [];
									for($i = 0; $i <= 2; $i++)
									{
										if($imgset[ $i ])
										{
											switch($imgset[ $i ])
											{
												case '2':
													$zoom = 2;
													break;
												case '3':
													$zoom = 3;
													break;
												default:
												case '1':
													$zoom = 1;
													break;
											}

											$imgsetparams = [
												'w'     => round($junews[ 'w' ] * $zoom),
												'h'     => round($junews[ 'h' ] * $zoom),
												'sx'    => $junews[ 'sx' ] ? : '',
												'sy'    => $junews[ 'sy' ] ? : '',
												'sw'    => $junews[ 'sw' ] ? : '',
												'sh'    => $junews[ 'sh' ] ? : '',
												'f'     => $junews[ 'f' ],
												'q'     => $junews[ 'q' ],
												'cache' => 'img'
											];

											$imgsetparams_merge = array_merge($imgsetparams, $newimgparams);
											$thumb_imgset       = Uri::base() . $this->juimg->render($junuimgsource, $imgsetparams_merge);
											$attr_imgset[]      = $thumb_imgset . ' ' . $imgset[ $i ] . 'x';
										}
									}

									$srcset = ' srcset="' . implode(', ', $attr_imgset) . '" ';
									break;

								default:
								case '0':
									$srcset = ' ';
									break;
							}

							$thumb_img = $this->image($params, [
								'src'    => Uri::base() . $this->juimg->render($junuimgsource, $imgparams_merge),
								'alt'    => $title_alt,
								'srcset' => $srcset
							]);

							$item->image       = $imlink . $thumb_img . $imlink2;
							$item->imagelink   = $thumb_img;
							$item->imagesource = $junuimgsource;
						}
						break;
				}
			}

			// rawtext
			if($junews[ 'sourcetext' ] == 1)
			{
				$item->sourcetext = $item->introtext . $item->fulltext;
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
			if($junews[ 'show_full' ] == 1)
			{
				$item->fulltext = $this->desc($params, [
					'description'    => $item->fulltext,
					'cleartag'       => $junews[ 'clear_tag_full' ],
					'allowed_tags'   => $junews[ 'allowed_full_tags' ],
					'li'             => $junews[ 'li_full' ],
					'text_limit'     => $junews[ 'fulltext_limit' ],
					'lmttext'        => $junews[ 'lmttext_full' ],
					'end_limit_text' => $junews[ 'end_limit_fulltext' ]
				]);
			}

			// author
			if($junews[ 'show_author' ] == 1 && $item->created_by_alias)
			{
				$item->author = $item->created_by_alias;
			}

			// date
			if($junews[ 'show_date' ] == 1)
			{
				switch($date_type)
				{
					case 'modified':
						$_date_type = $item->modified;
						break;
					case 'publish_up':
						$_date_type = $item->publish_up;
						break;
					default:
					case 'created':
						$_date_type = $item->created;
						break;
				}

				$item->sqldate = $_date_type;
				$item->date    = HTMLHelper::date($_date_type, $junews[ 'data_format' ]);
				$item->df_d    = HTMLHelper::date($_date_type, $junews[ 'date_day' ]);
				$item->df_m    = HTMLHelper::date($_date_type, $junews[ 'date_month' ]);
				$item->df_y    = HTMLHelper::date($_date_type, $junews[ 'date_year' ]);
			}

			// rating
			if($junews[ 'show_rating' ] == 1)
			{
				$item->rating = $this->rating($params, $item->rating);
			}
		}

		return $items;
	}
}