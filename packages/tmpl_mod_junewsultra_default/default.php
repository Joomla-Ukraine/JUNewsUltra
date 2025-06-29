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

/********** PARAMS ************
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
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die();

?>
<div class="junewsultra <?= $params->get('moduleclass_sfx') ?>">
	<?php foreach($list as $item) : ?>
		<div class="jn">

			<?php if($params->get('pik') && $item->image): ?>
				<div class="jn-image">
					<?= $item->image ?>
				</div>
			<?php endif; ?>

			<div class="jn-card">
				<?php foreach($item->tags as $tag) : ?>
					<a href="<?= $tag[ 'link' ]; ?>"><?= $tag[ 'title' ]; ?></a>
				<?php endforeach; ?>
			</div>

			<div class="jn-card">
				<?php if($params->get('show_title')): ?>
					<a class="jn-title" href="<?= $item->link ?>">
						<?= $item->title ?>
					</a>
				<?php endif; ?>

				<div class="jn-info">
					<?php if($params->get('show_date')): ?>
						<span class="jn-span"><?= $item->date ?></span>
					<?php endif; ?>
					<?php if($params->get('showcat')): ?>
						|
						<span class="jn-small"><?= $item->cattitle ?></span>
					<?php endif; ?>
					<?php if($params->get('juauthor')): ?>
						|
						<span class="jn-small"><?= $item->author ?></span>
					<?php endif; ?>
					<?php if($params->get('showRating') || $params->get('showRatingCount') || $params->get('showHits')): ?>
						<div class="jn-hit-n-rating">
							<?php if($params->get('showRating')): ?>
								<span class="jn-small jn-rating"><?= $item->rating ?></span>
							<?php endif; ?>
							<?php if($params->get('showRatingCount') && $item->rating_count > 0): ?>
								<sup class="jn-small jn-rating-count"><?= $item->rating_count ?></sup>
							<?php endif; ?>
							<?php if($params->get('showHits')): ?>
								<span class="jn-small jn-hits"><?= Text::_('JGLOBAL_HITS') ?>: <?= $item->hits ?></span>
							<?php endif; ?>
						</div>
					<?php endif; ?>
				</div>

				<?php if($params->get('show_intro') || $params->get('show_full')): ?>
					<div class="jn-intro">
						<?php if($params->get('show_intro')): ?>
							<?= $item->introtext ?>
						<?php endif; ?>
						<?php if($params->get('show_full')): ?>
							<?= $item->fulltext ?>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<?php if($params->get('read_more') || $params->get('use_comments')): ?>
					<div class="jn-more">
						<?php if($params->get('read_more')): ?>
							<a href="<?= $item->link ?>" class="readmore"><?= $params->get('rmtext') ?></a>
						<?php endif; ?>
						<?php if($params->get('use_comments') == 1 && (isset($item->commentstext) || isset($item->commentslink))): ?>
							<a class="jn-comment-link" href="<?php echo $item->link; ?><?= $item->commentslink ?>"><?= $item->commentstext ?></a>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>

	<?php endforeach; ?>
</div>