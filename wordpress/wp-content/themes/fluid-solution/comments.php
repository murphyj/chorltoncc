<?php // Do not delete these lines
	if ('comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
		die ('Please do not load this page directly. Thanks!');

        if (!empty($post->post_password)) { // if there's a password
            if ($_COOKIE['wp-postpass_'.$cookiehash] != $post->post_password) {  // and it doesn't match the cookie
				?>
				
				<p class="nocomments"><?php _e("This post is password protected. Enter the password to view comments."); ?><p>
				
				<?php
				return;
            }
        }

		/* This variable is for alternating comment background */
		$oddcomment = "graybox";
?>

<!-- You can start editing here. -->

<?php if ($comments) : ?>
	<a name="comments"></a><h2><?php comments_number('No Comments','1 Comment','% Comments' );?></h2> 

	<ol class="commentlist">
		<?php foreach ($comments as $comment) : ?>
			<li <?php echo $oddcomment; ?>id="comment-<?php comment_ID() ?>">
			
			<div class="commentheader"><span class="comment-calicon"><span class="comment-dateadded"><span class="time"><a href="#comment-<?php comment_ID() ?>" title=""><?php comment_date('F jS, Y') ?></a> at <?php comment_time() ?></span></span></div>
			<div class="commentleft">
				
				<div class="commentimage">
				
				<?php 
	   				if ( function_exists ('get_avatar')) {
	   					echo get_avatar( $email, $size = '64', $default = '<path_to_url>' );	
	   				}
	   			?>
	
				</div>
				<div class="commentauthor">
					<span class="author"><?php comment_author_link() ?></span><br />
				</div>
			</div>
			
			<div class="commenttext"><?php comment_text() ?><div class="closecomment"></div></div>
			
				
			<?php if ($comment->comment_approved == '0') : ?>
			<em>Your comment is awaiting moderation.</em>
			<?php endif; ?>
			</li>
			<?php
				/* Changes every other comment to a different class */
				$oddcomment = ( empty( $oddcomment ) ) ? 'class="alt" ' : '';
			?>
		<?php endforeach; /* end for each comment */ ?>
	</ol>

 <?php else : // this is displayed if there are no comments so far ?>

  <?php if ('open' == $post-> comment_status) : ?> 
		<!-- If comments are open, but there are no comments. -->
		
	 <?php else : // comments are closed ?>
		<!-- If comments are closed. -->
		<p class="nocomments">Comments are closed.</p>
		
	<?php endif; ?>
<?php endif; ?>


<?php if ('open' == $post-> comment_status) : ?>

<div class="commentform">
<a name="respond"></a><h3>Leave a Comment</h3>
<form action="<?php echo get_settings('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">

<p><input type="text" name="author" id="author" class="styled" value="<?php echo $comment_author; ?>" size="22" tabindex="1" />
<input type="hidden" name="comment_post_ID" value="<?php echo $id; ?>" />
<input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]); ?>" />
<label for="author"><small>Name</small></label></p>

<?php /* show_gravatar_signup(); */ ?>

<p><input type="text" name="email" id="email" value="<?php echo $comment_author_email; ?>" size="22" tabindex="2" />
<label for="email"><small>Mail (will not be published)</small></label></p>

<p><input type="text" name="url" id="url" value="<?php echo $comment_author_url; ?>" size="22" tabindex="3" />
<label for="url"><small>Website</small></label></p>

<!--<p><small><strong>XHTML:</strong> You can use these tags: <?php echo allowed_tags(); ?></small></p>-->

<p><textarea name="comment" id="comment" cols="100%" rows="10" tabindex="4"></textarea></p>

<?php if ('none' != get_settings("comment_moderation")) { ?>
	<p><small><strong>Please note:</strong> Comment moderation is enabled and may delay your comment. There is no need to resubmit your comment.</small></p>
<?php } ?>

<p><input name="submit" type="submit" id="submit" tabindex="5" value="Submit Comment" /></p>


</form>
</div>

<?php // if you delete this the sky will fall on your head
endif; ?>
