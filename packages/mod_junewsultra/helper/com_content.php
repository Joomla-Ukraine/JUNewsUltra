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

use Joomla\CMS\Access\Access;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Site\Helper\RouteHelper;

/**
 * Helper for mod_junewsultra
 *
 * @since       6.0
 * @subpackage  mod_junewsultra
 * @package     Joomla.Site
 */
#[AllowDynamicProperties]
class com_content extends Helper
{
	/**
	 * @param $params
	 * @param $junews
	 *
	 * @return array
	 * @since 6.0
	 */
	public function query($params, $junews): array
	{
		$input = $this->app->getInput();
		$id    = $input->getInt('id');

		$ordering           = $params->get('ordering', 'id_desc');
		$catid              = $params->get('catid', null);
		$tags               = $params->get('tag', []);
		$show_attribs       = (int) $params->get('show_attribs');
		$wheresql           = (int) $params->get('wheresql');
		$where              = $params->get('where');
		$display_article    = $params->get('display_article');
		$exclude_id         = (int) $params->get('exclude_id', 0);
		$useaccess          = (int) $params->get('useaccess', 0);
		$user_id            = (int) $params->get('user_id');
		$uid                = (int) $params->get('uid');
		$dateuser_filtering = $params->get('dateuser_filtering', 0);
		$date_filtering     = $params->get('date_filtering', 0);
		$relative_date      = $params->get('relative_date', 0);
		$date_type          = $params->get('date_type', 'created');
		$date_field         = $params->get('date_field', 'a.created');

		$access = '1';
		if($useaccess == 1)
		{
			$groups = implode(',', $this->user->getAuthorisedViewLevels());
			$access = !ComponentHelper::getParams('com_content')->get('show_noauth');
		}

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

		$this->q->select([
			'a.id',
			'a.state',
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

		if(($junews[ 'image_source' ] == 0 || $junews[ 'image_source' ] == 1) && $junews[ 'show_image' ] == 1)
		{
			$this->q->select([ 'a.images' ]);
		}

		if(($junews[ 'show_date' ] == 1 || $ordering === 'created_asc' || $ordering === 'created_desc') && ($ordering === 'created_asc' || $ordering === 'created_desc' || $ordering === 'modified_created_dsc' || $ordering === 'modified_touch_dsc' || $date_type === 'created' || $date_field === 'a.created' || $date_filtering == 1 || $dateuser_filtering == 1))
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

		if($useaccess == 1)
		{
			$this->q->select([ 'a.access' ]);
		}

		if($junews[ 'featured' ] != 0)
		{
			$this->q->select([ 'a.featured' ]);
		}

		if($junews[ 'show_author' ] == 1 || $dateuser_filtering == 1 || $user_id || $this->user->id > 0)
		{
			$this->q->select([ 'a.created_by' ]);
		}

		if($junews[ 'show_author' ] == 1)
		{
			$this->q->select([ 'a.created_by_alias' ]);
		}

		if($show_attribs == 1)
		{
			$this->q->select([ 'a.attribs' ]);
		}

		if($ordering === 'ordering_asc' || $ordering === 'ordering_desc')
		{
			$this->q->select([ 'a.ordering' ]);
		}

		if($junews[ 'show_hits' ] == 1 || $ordering === 'hits_asc' || $ordering === 'hits_desc')
		{
			$this->q->select([ 'a.hits' ]);
		}

		if(is_array($catid) || $access)
		{
			$this->q->select([ 'a.catid' ]);
		}

		// From
		$this->q->from('#__content AS a');

		if($junews[ 'show_cat' ] == 1 || $junews[ 'show_tags' ] == 1)
		{
			$this->q->select([ 'cc.title AS category_title' ]);
			$this->q->join('LEFT', '#__categories AS cc ON cc.id = a.catid');
		}

		if($junews[ 'show_author' ] == 1)
		{
			$this->q->select([ 'u.name AS author' ]);
			$this->q->join('LEFT', '#__users AS u on u.id = a.created_by');
		}

		if($junews[ 'show_rating' ] == 1)
		{
			$this->q->select([ 'ROUND(v.rating_sum / v.rating_count, 0) AS rating' ]);
			$this->q->join('LEFT', '#__content_rating AS v ON a.id = v.content_id');
		}

		if($dateuser_filtering == 1 || !empty($uid))
		{
			$ji_catids = '';
			if(is_array($cat_arr) && count($cat_arr))
			{
				$ji_catids = 'WHERE `catid` IN (' . implode(',', $cat_arr) . ')';
			}

			$this->q->join('INNER', '(SELECT max(`created`) MaxPostDate, `created_by` FROM `#__content` ' . $ji_catids . ' GROUP BY `created_by`) a2 ON a.created = a2.MaxPostDate');
		}

		$this->q->where($this->db->quoteName('a.state') . ' = ' . $this->db->Quote('1'));
		$this->q->where('(' . $this->db->quoteName('a.publish_up') . ' IS NULL OR ' . $this->db->quoteName('a.publish_up') . ' < ' . $this->db->Quote($this->nowdate) . ' )');
		$this->q->where('(' . $this->db->quoteName('a.publish_down') . ' IS NULL OR ' . $this->db->quoteName('a.publish_down') . ' > ' . $this->db->Quote($this->nowdate) . ' )');

		if($display_article == 3 && $id)
		{
			$query = $this->db->getQuery(true);
			$query->select($this->db->quoteName('metakey'));
			$query->from($this->db->quoteName('#__content'));
			$query->where($this->db->quoteName('id') . ' = ' . $this->db->Quote($id));
			$this->db->setQuery($query);

			try
			{
				$metakey = trim($this->db->loadResult());
			}
			catch (RuntimeException)
			{
				$this->app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

				return [];
			}

			$keys  = explode(',', $metakey);
			$likes = [];
			foreach($keys as $key)
			{
				$key = trim($key);

				if($key)
				{
					$likes[] = $this->db->escape($key);
				}
			}

			if(count($likes))
			{
				$wheres = [];
				foreach($likes as $keyword)
				{
					$wheres[] = $this->db->quoteName('a.metakey') . ' LIKE ' . $this->db->quote('%' . $keyword . '%');
				}

				$this->q->where('(' . implode(' OR ', $wheres) . ')');
			}
		}

		if($display_article == 1)
		{
			$this->q->where($this->db->quoteName('a.id') . ' = ' . $this->db->Quote((int) $params->get('articleid')));
		}

		if($display_article == 2)
		{
			$ids = str_replace(' ', '', $params->get('articleids'));
			$ids = trim($ids);

			$this->q->where($this->db->quoteName('a.id') . ' IN (' . $ids . ')');
		}

		if($display_article == 0)
		{
			if(is_countable($tags) && count($tags) > 0)
			{
				$this->q->join('INNER', '#__contentitem_tag_map AS m ON a.id = m.content_item_id');
				$this->q->where($this->db->quoteName('m.tag_id') . ' IN (' . implode(',', $params->get('tag', [])) . ')');
				$this->q->where($this->db->quoteName('m.type_alias') . ' = ' . $this->db->Quote('com_content.article'));
				$this->q->group($this->db->quoteName('a.id'));
			}

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

			if(is_array($cat_arr) && count($cat_arr))
			{
				$this->q->where($this->db->quoteName('a.catid') . ' IN (' . implode(',', $cat_arr) . ')');
			}

			if($excluded_articles = $params->get('excluded_articles', null))
			{
				$excluded_articles = explode("\r\n", $excluded_articles);
				$this->q->where($this->db->quoteName('a.id') . ' NOT IN (' . implode(',', $excluded_articles) . ')');
			}

			if($exclude_id)
			{
				$this->q->where($this->db->quoteName('a.id') . ' != ' . $this->db->Quote($id));
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
					$this->q->where('(' . $this->db->quoteName('a.created_by') . ' = ' . $this->db->Quote((int) $this->user->id) . ' OR ' . $this->db->quoteName('a.modified_by') . ' = ' . $this->db->Quote((int) $this->user->id) . ')');
					break;
				case 'not_me':
					$this->q->where('(' . $this->db->quoteName('a.created_by') . ' <> ' . $this->db->Quote((int) $this->user->id) . ' AND ' . $this->db->quoteName('a.modified_by') . ' <> ' . $this->db->Quote((int) $this->user->id) . ')');
					break;
			}
		}

		if($useaccess == 1)
		{
			$this->q->where($this->db->quoteName('a.access') . ' IN (' . $groups . ')');
		}

		if($wheresql == 1)
		{
			$sqls = explode("\r\n", $where);
			if($params->get('sql_operator', 1) == '1')
			{
				foreach($sqls as $sql)
				{
					$this->q->where($sql);
				}
			}
			else
			{
				$this->q->where('(' . implode(' OR ', $sqls) . ')');
			}
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
	public function order($order): string
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

	protected function tags($id)
	{
		$qtag = $this->db->getQuery(true);
		$qtag->select([
			'm.tag_id',
			't.title AS tag_title',
			't.alias AS tag_alias'
		]);
		$qtag->from('#__contentitem_tag_map AS m');
		$qtag->join('LEFT', '#__tags AS t ON t.id = m.tag_id');
		$qtag->where($this->db->quoteName('m.content_item_id') . ' = ' . $this->db->Quote($id));
		$this->db->setQuery($qtag);
		$rows = $this->db->loadObjectList();

		$jtags = [];
		foreach($rows as $row)
		{
			$jtags[] = [
				'id'    => $row->tag_id,
				'title' => $row->tag_title,
				'link'  => Route::_('index.php?option=com_tags&view=tag&id=' . $row->tag_id . ':' . $row->tag_alias),
			];
		}

		return $jtags;
	}

	/**
	 * @param $params
	 * @param $junews
	 *
	 * @return array
	 *
	 * @throws \Exception
	 * @since 6.0
	 */
	public function getList($params, $junews): array
	{
		$useaccess = (int) $params->get('useaccess', 0);
		$date_type = $params->get('date_type', 'created');

		$access     = '1';
		$authorised = [];
		if($useaccess == 1)
		{
			$access     = !ComponentHelper::getParams('com_content')->get('show_noauth');
			$authorised = Access::getAuthorisedViewLevels($this->user->id);
		}

		$items = $this->query($params, $junews);

		if($params->get('use_comments') == 1 && is_countable($items) && count($items))
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
					default:
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
				$this->app->enqueueMessage(Text::_('MOD_JUNEWS_COMMENTS_NOT_INSTALLED'), 'error');
			}
		}

		foreach($items as $item)
		{
			$item->link    = Route::_('index.php?option=com_users&view=login');
			$item->catlink = $item->link;
			$introtext     = ($item->introtext ?? '');
			$fulltext      = ($item->fulltext ?? '');

			if($access || in_array($item->access, $authorised, true))
			{
				$slug          = $item->id . ($item->alias ? ':' . $item->alias : '');
				$language      = (Multilanguage::isEnabled() ? $item->language : '');
				$catid         = (!empty($item->cmc_cat) && $junews[ 'multicat' ] == 1 ? $item->cmc_cat : $item->catid);
				$item->link    = Route::_(RouteHelper::getArticleRoute($slug, $item->catid, $language));
				$item->catlink = Route::_(RouteHelper::getCategoryRoute($catid));
			}

			if($junews[ 'show_title' ] == 1)
			{
				$item->title = $this->title($params, $item->title);
			}

			$item->title_alt = $this->title($params, $item->title);

			if(($junews[ 'show_cat' ] == 1 && isset($item->category_title)) || $junews[ 'show_tags' ] == 1)
			{
				$cattitle       = strip_tags($item->category_title);
				$item->cattitle = $cattitle;

				if($params->get('showcatlink') == 1)
				{
					$item->cattitle = '<a href="' . $item->catlink . '">' . $cattitle . '</a>';
				}
			}

			if($junews[ 'show_tags' ] == 1)
			{
				$item->tags = $this->tags($item->id);
			}

			if($junews[ 'show_image' ] == 1)
			{
				switch($junews[ 'introfulltext' ])
				{
					case '1':
						$_text = $item->fulltext;
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
					elseif(preg_match('/{gallery\s+(.*?)}/i', $_text, $junuimgsource) && $junews[ 'gallery' ] == 1)
					{
						$folder_match = $junuimgsource[ 1 ];
						$imglist      = explode('|', $folder_match);
						$imgsource    = $imglist[ 0 ];
						$root         = JPATH_BASE . '/';
						$folder       = 'images/' . $imgsource;
						$img_folder   = $root . $folder;
						$galleries    = glob($img_folder . '/{*.[jJ][pP][gG],*.[jJ][pP][eE][gG],*.[gG][iI][fF],*.[pP][nN][gG],*.[wE][eE][bB][pP],*.[bB][mM][pP]}', GLOB_BRACE);

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
						$junuimgsource = $this->detect_video($_text);
					}
				}

				$item->imagesource = '';
				if($junews[ 'image_source' ] == 0 || $junews[ 'image_source' ] == 1 || $junews[ 'image_source' ] == 2 || $junews[ 'image_source' ] == 3)
				{
					$images = json_decode($item->images);

					if(is_object($images))
					{
						$intro_image    = $this->image_source($images->image_intro ?? '');
						$fulltext_image = $this->image_source($images->image_fulltext ?? '');

						if($junews[ 'image_source' ] === '1')
						{
							if($intro_image)
							{
								$junuimgsource     = htmlspecialchars($intro_image);
								$item->imagesource = htmlspecialchars($intro_image);
							}
							elseif($fulltext_image)
							{
								$junuimgsource     = htmlspecialchars($fulltext_image);
								$item->imagesource = htmlspecialchars($fulltext_image);
							}
						}
						elseif($junews[ 'image_source' ] === '2' && $intro_image)
						{
							$junuimgsource     = htmlspecialchars($intro_image);
							$item->imagesource = htmlspecialchars($intro_image);
						}
						elseif($junews[ 'image_source' ] === '3' && $fulltext_image)
						{
							$junuimgsource     = htmlspecialchars($fulltext_image);
							$item->imagesource = htmlspecialchars($fulltext_image);
						}
					}
				}

				if(!$junuimgsource)
				{
					$junuimgsource = '';
					if($junews[ 'defaultimg' ] == 1 && $junews[ 'noimage' ])
					{
						$junuimgsource = 'media/mod_junewsultra/' . $junews[ 'noimage' ];
					}
				}

				$item->image     = '';
				$item->imagelink = '';
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

			if($junews[ 'sourcetext' ] == 1 && isset($introtext) && isset($fulltext))
			{
				$item->sourcetext = $introtext . $fulltext;
			}

			if($junews[ 'show_intro' ] == 1 && isset($item->introtext))
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

			if($junews[ 'show_full' ] == 1 && isset($item->fulltext))
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

			if($junews[ 'show_author' ] == 1 && isset($item->created_by_alias))
			{
				$item->author = $item->created_by_alias;
			}

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

			if($junews[ 'show_rating' ] == 1 && isset($item->rating))
			{
				$item->rating = $this->rating($params, $item->rating);
			}
		}

		return $items;
	}
}