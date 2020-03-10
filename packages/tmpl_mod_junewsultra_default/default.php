<?php
/**
 * JUNewsUltra Pro
 *
 * @package          Joomla.Site
 * @subpackage       mod_junewsultra
 *
 * @author           Denys Nosov, denys@joomla-ua.org
 * @copyright        2007-2020 (C) Joomla! Ukraine, http://joomla-ua.org. All rights reserved.
 * @license          GNU General Public License version 2 or later; see LICENSE.txt
 */

/******************* PARAMS (update 05.12.2016) ************
 *
 * $params->get('moduleclass_sfx') - module class suffix
 *
 * $item->link           - article link for [href="..."] attribute
 * $item->title          - title
 * $item->title_alt      - for attribute title or alt
 *
 * $item->cattitle       - category title
 * $item->catlink        - category link for [href="..."] attribute
 *
 * $item->image          - display image thumb
 * $item->imagelink      - image thumb link for [src="..."] attribute
 * $item->imagesource    - raw image source (original image)
 *
 * $item->sourcetext     - display raw intro and fulltext
 *
 * $item->introtext      - display introtext
 * $item->fulltext       - display fulltext
 *
 * $item->author         - display author or created by alias
 * $item->created_by_alias - display created by alias (author)
 *
 * $item->sqldate        - raw date [display format: 0000-00-00 00:00:00]
 * $item->date           - display date & time with date format
 * $item->df_d           - display day from date
 * $item->df_m           - display mounth from date
 * $item->df_y           - display year from date
 *
 * $item->hits           - display hits
 *
 * $item->rating         - display rating with stars
 *
 * $item->comments        - display comments couner
 * $item->commentslink   - comment link for [href="..."] attribute
 * $item->commentstext   - display comments text
 * $item->commentscount  - comments couner (alias)
 *
 * $item->readmore       - display 'Read more...' or other text
 * $item->rmtext         - display 'Read more...' or other text
 *
 ************************************************************/

defined('_JEXEC') or die('Restricted access');

?>
<div class="junewsultra <?php echo $params->get('moduleclass_sfx'); ?>">
	<?php foreach($list as $item) : ?>
		<div class="jn">
			<div class="jn-head">
				<div class="jn-left">
					<?php if($params->get('pik')): ?>
						<?php echo $item->image; ?>
					<?php endif; ?>
				</div>
				<div class="jn-right">
					<?php if($params->get('show_title')): ?>
						<h4>
							<a href="<?php echo $item->link; ?>">
								<?php echo $item->title; ?>
							</a>
						</h4>
					<?php endif; ?>
					<div class="jn-info">
						<?php if($params->get('show_date')): ?>
							<span class="jn-small"><?php echo $item->date; ?></span>
						<?php endif; ?>
						<?php if($params->get('showcat')): ?>
							| <span class="jn-small"><?php echo $item->cattitle; ?></span>
						<?php endif; ?>
						<?php if($params->get('juauthor')): ?>
							| <span class="jn-small"><?php echo $item->author; ?></span>
						<?php endif; ?>
						<?php if($params->get('showRating') || $params->get('showRatingCount') || $params->get('showHits')): ?>
							<div class="jn-hit-n-rating">
								<?php if($params->get('showRating')): ?>
									<span class="jn-small jn-rating"><?php echo $item->rating; ?></span>
								<?php endif; ?>
								<?php if($params->get('showRatingCount') && $item->rating_count > 0): ?>
									<sup class="jn-small jn-rating-count"><?php echo $item->rating_count; ?></sup>
								<?php endif; ?>
								<?php if($params->get('showHits')): ?>
									<span class="jn-small jn-hits"><?php echo JText::_('JGLOBAL_HITS'); ?>: <?php echo $item->hits; ?></span>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<?php if($params->get('show_intro') || $params->get('show_full')): ?>
				<div class="jn-intro">
					<?php if($params->get('show_intro')): ?>
						<?php echo $item->introtext; ?>
					<?php endif; ?>
					<?php if($params->get('show_full')): ?>
						<?php echo $item->fulltext; ?>
					<?php endif; ?>
				</div>
			<?php endif; ?>
			<?php if($params->get('read_more') || $params->get('use_comments')): ?>
				<div class="jn-more">
					<?php if($params->get('read_more')): ?>
						<a href="<?php echo $item->link; ?>" class="readmore" title="<?php echo $item->text_alt; ?>"><?php echo $params->get('rmtext'); ?></a>
					<?php endif; ?>
					<?php if($params->get('use_comments')): ?>
						<a class="jn-comment-link" href="<?php echo $item->link; ?><?php echo $item->commentslink; ?>"><?php echo $item->commentstext; ?></a>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
	<?php endforeach; ?>
</div>