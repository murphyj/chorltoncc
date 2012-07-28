<?php get_header(); ?>


	<h1>Archives</h1>

	<div class="content">
	<?php if (have_posts()) : ?>

	<?php while (have_posts()) : the_post(); ?>

	<div class="post" id="post-<?php the_ID(); ?>">	

	<h1><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title(); ?>"><?php the_title(); ?></a></h1>

	<div class="descr"> <?php the_time('F jS, Y') ?> by <?php the_author() ?> </div>

	<div class="postinfo">Filed under <?php the_category(', ') ?> <strong></strong> <?php edit_post_link('Edit','','<strong>  </strong>'); ?> having <?php comments_popup_link('No Comments &raquo;', '1 Comment &raquo;', '% Comments &raquo;'); ?></div>

	</div>

	<?php comments_template(); ?>

	<?php endwhile; ?>

	<p align="center"><?php next_posts_link('&laquo; Previous Entries') ?> <?php previous_posts_link('Next Entries &raquo;') ?></p>

	<?php else : ?>

	<h2 align="center">Not Found</h2>

	<p align="center">Sorry, but you are looking for something that isn't here.</p>

	<?php endif; ?>

		
	</div>

</div>
<?php get_sidebar(); ?>






<?php get_footer(); ?>



</body>

</html>