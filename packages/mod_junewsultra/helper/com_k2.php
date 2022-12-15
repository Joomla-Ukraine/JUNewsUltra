<?php
/**
 * JUNewsUltra Pro
 *
 * @package          Joomla.Site
 * @subpackage       mod_junewsultra
 *
 * @author           Denys Nosov, denys@joomla-ua.org
 * @copyright        2007-2021 (C) Joomla! Ukraine, http://joomla-ua.org. All rights reserved.
 * @license          GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Utilities\ArrayHelper;

require_once JPATH_SITE . '/components/com_k2/helpers/route.php';
require_once JPATH_SITE . '/components/com_k2/helpers/utilities.php';

/**
 * Helper for mod_junewsultra
 *
 * @since       6.0
 * @subpackage  mod_junewsultra
 * @package     Joomla.Site
 */
class com_k2 extends Helper
{
	/**
	 * @param $params
	 * @param $junews
	 *
	 * @return bool|mixed
	 *
	 * @since 6.0
	 */
	public function query($params, $junews)
	{
		$ordering       = $params->get('ordering', 'id_desc');
		$catid          = $params->get('k2_catid', null);
		$user_id        = (int) $params->get('user_id');
		$uid            = (int) $params->get('uid');
		$date_filtering = $params->get('date_filtering', 0);
		$relative_date  = $params->get('relative_date', 0);
		$date_type      = $params->get('date_type', 'created');
		$date_field     = $params->get('date_field', 'a.created');

		// Categories
		if(count($catid) > 0)
		{
			if(is_array($catid))
			{
				if($params->get('show_child_category_k2'))
				{
					if(K2_JVERSION >= '25')
					{
						$itemListModel = K2Model::getInstance('Itemlist', 'K2Model');
						$categories    = $itemListModel->getCategoryTree($catid);
					}
					else
					{
						require_once JPATH_SITE . '/components/com_k2/models/itemlist.php';
						$categories = K2ModelItemlist::getCategoryTree($catid);
					}

					$this->q->where('a.catid IN (' . @implode(',', $categories) . ')');
				}
				else
				{
					ArrayHelper::toInteger($catid);
					//$this->q->where('a.catid IN (' . implode(',', $catid) . ')');
				}
			}
			elseif($params->get('show_child_category_k2'))
			{
				if(K2_JVERSION >= '25')
				{
					$itemListModel = K2Model::getInstance('Itemlist', 'K2Model');
					$categories    = $itemListModel->getCategoryTree($catid);
				}
				else
				{
					require_once JPATH_SITE . '/components/com_k2/models/itemlist.php';
					$categories = K2ModelItemlist::getCategoryTree($catid);
				}

				$this->q->where('a.catid IN (' . @implode(',', $categories) . ')');
			}
			else
			{
				$this->q->where('a.catid = ' . (int) $catid);
			}
		}

		// Selects data
		$this->q->select([
			'a.id',
			'a.published',
			'a.alias',
			'a.publish_up',
			'a.publish_down'
		]);

		if($junews[ 'show_title' ] == 1 || $junews[ 'show_image' ] == 1)
		{
			$this->q->select([ 'a.title' ]);
		}

		if($junews[ 'sourcetext' ] == 1 || $junews[ 'show_intro' ] == 1 || ($junews[ 'show_image' ] == 1 && $junews[ 'image_source' ] == 0 && ($junews[ 'introfulltext' ] == 0 || $junews[ 'introfulltext' ] == 2)))
		{
			$this->q->select([ 'a.introtext' ]);
		}

		if($junews[ 'sourcetext' ] == 1 || $junews[ 'show_full' ] == 1 || ($junews[ 'show_image' ] == 1 && $junews[ 'image_source' ] == 0 && ($junews[ 'introfulltext' ] == 1 || $junews[ 'introfulltext' ] == 2)))
		{
			$this->q->select([ 'a.fulltext' ]);
		}

		if(Multilanguage::isEnabled())
		{
			$this->q->select([ 'a.language' ]);
			$this->q->where($this->db->quoteName('a.language') . ' IN (' . $this->db->Quote($this->lang->getTag()) . ',' . $this->db->quote('*') . ')');
		}

		if(($junews[ 'show_date' ] == 1 || $ordering === 'created_asc' || $ordering === 'created_desc') && ($ordering === 'created_asc' || $ordering === 'created_desc' || $ordering === 'modified_created_dsc' || $ordering === 'modified_touch_dsc' || $date_type === 'created' || $date_field === 'a.created' || $date_filtering == 1))
		{
			$this->q->select([ 'a.created' ]);
		}

		if(($junews[ 'show_date' ] == 1 || $ordering === 'modified_asc' || $ordering === 'modified_desc' || $ordering === 'modified_created_dsc' || $ordering === 'modified_touch_dsc') && ($ordering === 'modified_asc' || $ordering === 'modified_desc' || $ordering === 'modified_created_dsc' || $ordering === 'modified_touch_dsc' || $date_type === 'modified' || $date_field === 'a.modified' || $date_filtering == 1))
		{
			$this->q->select([ 'a.modified' ]);
		}

		if($user_id)
		{
			$this->q->select([ 'a.modified_by' ]);
		}

		if($junews[ 'featured' ] != 0)
		{
			$this->q->select([ 'a.featured' ]);
		}

		if($junews[ 'show_author' ] == 1 || $user_id || $this->user->get('id') > 0)
		{
			$this->q->select([ 'a.created_by' ]);
		}

		if($junews[ 'show_author' ] == 1)
		{
			$this->q->select([ 'a.created_by_alias' ]);
		}

		if($ordering === 'ordering_asc' || $ordering === 'ordering_desc')
		{
			$this->q->select([ 'a.ordering' ]);
		}

		if($junews[ 'show_hits' ] == 1 || $ordering === 'hits_asc' || $ordering === 'hits_desc')
		{
			$this->q->select([ 'a.hits' ]);
		}

		if(is_array($catid))
		{
			$this->q->select([ 'a.catid' ]);
		}

		// From
		$this->q->from('#__k2_items AS a');

		// Categories
		$this->q->select([ 'c.name AS category_title, c.alias AS categoryalias' ]);
		$this->q->join('LEFT', '#__k2_categories AS c ON c.id = a.catid');

		// User
		if($junews[ 'show_author' ] == 1)
		{
			$this->q->select([ 'u.name AS author' ]);
			$this->q->join('LEFT', '#__users AS u on u.id = a.created_by');
		}

		// Rating
		if($junews[ 'show_rating' ] == 1)
		{
			$this->q->select([ 'ROUND(v.rating_sum / v.rating_count, 0) AS rating' ]);
			$this->q->join('LEFT', '#__k2_rating AS v ON a.id = v.itemID');
		}

		// Where
		$this->q->where($this->db->quoteName('a.published') . ' = ' . $this->db->Quote('1'));
		$this->q->where($this->db->quoteName('a.trash') . ' = ' . $this->db->Quote('0'));
		$this->q->where('( a.publish_up = ' . $this->db->Quote($this->db->getNullDate()) . ' OR a.publish_up <= ' . $this->db->Quote($this->nowdate) . ' )');
		$this->q->where('( a.publish_down = ' . $this->db->Quote($this->db->getNullDate()) . ' OR a.publish_down >= ' . $this->db->Quote($this->nowdate) . ' )');

		// Select article or categories
		if($date_filtering == 1)
		{
			switch($relative_date)
			{
				case '1':
					$startDateRange = $this->db->Quote($params->get('start_date_range', date('Y-m-d') . ' 00:00:00'));
					$endDateRange   = $this->db->Quote($params->get('end_date_range', date('Y-m-d H:i:s')));
					$this->q->where('(' . $this->db->quoteName($date_field) . ' > ' . $this->db->Quote($startDateRange) . ' AND ' . $this->db->quoteName($date_field) . ' < ' . $this->db->Quote($endDateRange) . ')');
					break;

				case '2':
					$this->q->where($this->db->quoteName($date_field) . ' > DATE_SUB(' . $this->db->Quote($this->nowdate) . ', INTERVAL 7 DAY)');
					break;

				case '3':
					$this->q->where($this->db->quoteName($date_field) . ' > DATE_SUB(' . $this->db->Quote($this->nowdate) . ', INTERVAL 14 DAY)');
					break;

				case '4':
					$this->q->where($this->db->quoteName($date_field) . ' > DATE_SUB(' . $this->db->Quote($this->nowdate) . ', INTERVAL ' . cal_days_in_month(CAL_GREGORIAN, date('m'), date('Y')) . ' DAY)');
					break;

				case '5':
					$this->q->where($this->db->quoteName($date_field) . ' > DATE_SUB(' . $this->db->Quote($this->nowdate) . ', INTERVAL 365 DAY)');
					break;

				case '6':
					$this->q->where($this->db->quoteName($date_field) . ' > DATE_SUB(' . $this->db->Quote($this->nowdate) . ', INTERVAL ' . $params->get('custom_days', '30') . ' DAY)');
					break;

				case '0':
				default:
					$this->q->where($this->db->quoteName($date_field) . ' > DATE_SUB(' . $this->db->Quote($this->nowdate) . ', INTERVAL 1 DAY)');
					break;
			}
		}

		if($excluded_articles = $params->get('excluded_articles', null))
		{
			$excluded_articles = explode("\r\n", $excluded_articles);
			$this->q->where($this->db->quoteName('a.id') . ' NOT IN (' . implode(',', $excluded_articles) . ')');
		}

		switch($junews[ 'featured' ])
		{
			case '1':
				$this->q->where($this->db->quoteName('a.featured') . ' = ' . $this->db->Quote('1'));
				break;

			case '0':
				$this->q->where($this->db->quoteName('a.featured') . ' = ' . $this->db->Quote('0'));
				break;
		}

		switch($user_id)
		{
			case '0':
				if($uid > 0)
				{
					$this->q->where($this->db->quoteName('a.created_by') . ' = ' . $this->db->Quote($uid));
				}
				break;

			case 'by_me':
				$this->q->where('(' . $this->db->quoteName('a.created_by') . ' = ' . $this->db->Quote((int) $this->user->get('id')) . ' OR ' . $this->db->quoteName('a.modified_by') . ' = ' . $this->db->Quote((int) $this->user->get('id')) . ')');
				break;

			case 'not_me':
				$this->q->where('(' . $this->db->quoteName('a.created_by') . ' <> ' . $this->db->Quote((int) $this->user->get('id')) . ' AND ' . $this->db->quoteName('a.modified_by') . ' <> ' . $this->db->Quote((int) $this->user->get('id')) . ')');
				break;
		}

		$this->q->order($this->order($ordering));
		$this->db->setQuery($this->q, $junews[ 'count_skip' ], $junews[ 'count' ]);

		return $this->db->loadObjectList();
	}

	/**
	 * @param $order
	 *
	 * @return string
	 *
	 * @since 6.0
	 */
	public function order($order)
	{
		switch($order)
		{
			case 'title_asc':
				$ordering = 'a.title';
				break;

			case 'title_desc':
				$ordering = 'a.title DESC';
				break;

			case 'id_asc':
				$ordering = 'a.id';
				break;

			case 'id_desc':
				$ordering = 'a.id DESC';
				break;

			case 'hits_asc':
				$ordering = 'a.hits';
				break;

			case 'hits_desc':
				$ordering = 'a.hits DESC';
				break;

			case 'rating_asc':
				$ordering = 'rating';
				break;

			case 'rating_desc':
				$ordering = 'rating DESC';
				break;

			case 'created_asc':
				$ordering = 'a.created';
				break;

			case 'modified_desc':
				$ordering = 'a.modified DESC';
				break;

			case 'modified_created_dsc':
				$ordering = 'a.modified DESC, a.created';
				break;
			case 'modified_touch_dsc':
				$ordering = 'CASE WHEN (' . $this->db->quoteName('a.modified') . ' = ' . $this->db->Quote($this->nulldate) . ') THEN a.created ELSE a.modified END';
				break;

			case 'ordering_asc':
				$ordering = 'a.ordering';
				break;

			case 'ordering_desc':
				$ordering = 'a.ordering DESC';
				break;

			case 'rand':
				$ordering = 'rand()';
				break;

			case 'publish_dsc':
				$ordering = 'a.publish_up DESC';
				break;

			case 'created_desc':
			default:
				$ordering = 'a.created DESC';
				break;
		}

		return $ordering;
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
		$date_type = $params->get('date_type', 'created');

		$items = $this->query($params, $junews);

		// Comments integration
		if($params->get('use_comments') == 1 && count($items))
		{
			$comments_system = $params->get('select_comments');
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

				switch($comments_system)
				{
					case 'komento':
						$this->q->select([ 'cid', 'count(cid) AS cnt' ]);
						$this->q->from('#__komento_comments');
						$this->q->where($this->db->quoteName('published') . ' = ' . $this->db->Quote('1'));
						$this->q->where($this->db->quoteName('component') . ' = ' . $this->db->Quote('com_content'));
						$this->q->where($this->db->quoteName('cid') . ' IN (' . implode(',', $ids) . ')');
						$this->q->group('cid');
						$this->db->setQuery($this->q);

						$commentsCount  = $this->db->loadObjectList('cid');
						$comment_link   = '#section-komento';
						$comment_add    = $comment_link;
						$comment_text1  = 'COM_KOMENTO_FRONTPAGE_COMMENT';
						$comment_text2  = $comment_text1;
						$comment_plural = 0;
						break;

					case 'jcomments':
						$this->q->select([
							'object_id',
							'count(object_id) AS cnt'
						]);
						$this->q->from('#__jcomments');
						$this->q->where($this->db->quoteName('published') . ' = ' . $this->db->Quote('1'));
						$this->q->where($this->db->quoteName('object_group') . ' = ' . $this->db->Quote('com_content'));
						$this->q->where($this->db->quoteName('object_id') . ' IN (' . implode(',', $ids) . ')');
						$this->q->group('object_id');
						$this->db->setQuery($this->q);

						$commentsCount  = $this->db->loadObjectList('object_id');
						$comment_link   = '#comments';
						$comment_add    = '#addcomments';
						$comment_text1  = 'LINK_READ_COMMENTS';
						$comment_text2  = 'LINK_ADD_COMMENT';
						$comment_plural = 1;
						break;

					default:
						$this->q->select([
							'id',
							'count(id) AS cnt'
						]);
						$this->q->from('#__k2_comments');
						$this->q->where($this->db->quoteName('published') . ' = ' . $this->db->Quote('1'));
						$this->q->where($this->db->quoteName('id') . ' IN (' . implode(',', $ids) . ')');
						$this->q->group('id');
						$this->db->setQuery($this->q);

						$commentsCount  = $this->db->loadObjectList('id');
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
				Factory::getApplication()->enqueueMessage(Text::_('MOD_JUNEWS_COMMENTS_NOT_INSTALLED'), 'error');
			}
		}

		foreach($items as $item)
		{
			$introtext = (isset($item->introtext) ? $item->introtext : '');
			$fulltext  = (isset($item->fulltext) ? $item->fulltext : '');

			$slug          = $item->id . ($item->alias ? ':' . urlencode($item->alias) : '');
			$catslug       = $item->catid . ':' . urlencode($item->categoryalias);
			$item->link    = Route::_(K2HelperRoute::getItemRoute($slug, $catslug));
			$item->catlink = Route::_(K2HelperRoute::getItemRoute($catslug));

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
			}

			if($junews[ 'show_image' ] == 1)
			{
				switch($junews[ 'introfulltext' ])
				{
					case '1':
						$_text = $fulltext;
						break;

					case '2':
						$_text = $introtext . $fulltext;
						break;

					default:
					case '0':
						$_text = $introtext;
						break;
				}

				$title_alt = $item->title_alt;

				$junuimgsource = '';
				if($junews[ 'image_source' ] == 0)
				{
					if(preg_match('/<img(.*?)src="(.*?)"(.*?)>\s*(<\/img>)?/', $_text, $junuimgsource))
					{
						$junuimgsource = $junuimgsource[ 2 ];
					}
					elseif($junews[ 'youtube_img_show' ] == 1)
					{
						$junuimgsource = $this->detect_video($_text);
					}
				}

				$item->imagesource = '';
				if($junews[ 'image_source' ] == 0 || $junews[ 'image_source' ] == 1 || $junews[ 'image_source' ] == 2 || $junews[ 'image_source' ] == 3)
				{
					$k2image     = '.jpg';
					$k2imagepath = 'src';
					$k2img       = 'media/k2/items/' . $k2imagepath . '/' . md5('Image' . $item->id) . $k2image;

					if(file_exists(JPATH_SITE . '/' . $k2img))
					{
						$junuimgsource     = $k2img;
						$item->imagesource = $k2img;
					}
				}

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

				switch($junews[ 'thumb_width' ])
				{
					case '0':
						if($blank == 1)
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
						if($blank == 1)
						{
							$item->image       = $this->image($params, $junews, [
								'src'  => $junuimgsource,
								'w'    => $junews[ 'w' ],
								'h'    => $junews[ 'h' ],
								'link' => $junews[ 'imglink' ] == 1 ? $item->link : '',
								'alt'  => $title_alt
							]);
							$item->imagelink   = $this->thumb($junuimgsource, $junews);
							$item->imagesource = $junuimgsource;
						}
						break;
				}
			}

			// rawtext
			if($junews[ 'sourcetext' ] == 1)
			{
				$item->sourcetext = $introtext . $fulltext;
			}

			// introtext
			if($junews[ 'show_intro' ] == 1)
			{
				$item->introtext = $this->desc($params, [
					'description'    => $introtext,
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
					'description'    => $fulltext,
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