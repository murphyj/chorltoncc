<div class="right">

	<div class="round">		
		<div class="roundtl"><span></span></div>
		<div class="roundtr"><span></span></div>
		<div class="clearer"><span></span></div>
	</div>

	<div class="subnav">

<h1><?php _e("Recent Posts"); ?></h1>

<ul>
	<?=mdv_recent_posts(8);?>
</ul>

<h1><?php _e('Categories:'); ?></h1>

<ul>

<?php wp_list_cats('sort_column=name&hierarchical=0'); ?>

</ul>

<?php if ( is_home() ) : ?>

<h1><?php _e('Blogroll'); ?></h1>

<ul>

<?php wp_list_bookmarks('title_li=&categorize=0'); ?>

</ul>

<?php endif; ?>

 <h1><?php _e('Archives:'); ?></h1>

<ul>

<?php wp_get_archives('type=monthly'); ?>

</ul>

 <h1><?php _e('Search:'); ?></h1>

<ul>

	<li>
		<form method="get" id="searchform" action="<?php echo $_SERVER['PHP_SELF']; ?>">
		<div>
			<input type="text" value="<?php echo wp_specialchars($s, 1); ?>" name="s" id="s" /><input type="submit" id="sidebarsubmit" value="Search" />
		</div>
		 </form>
	</li>
 </ul>

 <?php if ( is_home() ) : ?>

<h1><?php _e('Meta:'); ?></h1>

<ul>

<li><a href="<?php bloginfo('rss2_url'); ?>" title="<?php _e('Syndicate this site using RSS'); ?>"><?php _e('<abbr title="Really Simple Syndication">RSS</abbr>'); ?></a></li>

<li><a href="<?php bloginfo('comments_rss2_url'); ?>" title="<?php _e('The latest comments to all posts in RSS'); ?>"><?php _e('Comments <abbr title="Really Simple Syndication">RSS</abbr>'); ?></a></li>

<li><a href="http://validator.w3.org/check/referer" title="<?php _e('This page validates as XHTML 1.0 Transitional'); ?>"><?php _e('Valid <abbr title="eXtensible HyperText Markup Language">XHTML</abbr>'); ?></a></li>

<li><a href="http://gmpg.org/xfn/"><abbr title="XHTML Friends Network">XFN</abbr></a></li>

<?php wp_meta(); ?>

</ul>

<?php endif; ?>

	</div>

	<div class="round">
		<div class="roundbl"><span></span></div>
		<div class="roundbr"><span></span></div>
		<span class="clearer"></span>
	</div>

</div>