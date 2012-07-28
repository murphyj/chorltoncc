<?php
/*
Plugin Name: Gravatar Signup
Version: 2.0.1
Plugin URI: http://txfx.net/wordpress-plugins/gravatar-signup/
Description: Allows commenters to sign up for a Gravatar by clicking a checkbox when they submit a comment
Author: Mark Jaquith
Author URI: http://coveredwebservices.com/
*/

function show_gravatar_signup( $post_id ) {
?>
	<div id="cws-gravatar-signup">
	<?php if ( is_user_logged_in() ) : $user = wp_get_current_user(); ?>
		<p style="display:none"><input type="hidden" name="email" id="email" value="<?php echo attribute_escape( $user->user_email ); ?>" /></p>
	<?php endif; ?>
	<p id="ajax_get_avatar" style="display:none"></p>
	</p>
	</div>
<?php
	return $post_id;
}

function run_gravatar_signup() {
	$user = wp_get_current_user();
	if ( is_email( $user->user_email ) )
		gravatar_signup( $user->user_email );
	elseif ( is_email( $_POST['email']) )
		gravatar_signup( $_POST['email'] );
}

function gravatar_signup( $email ) {
	if ( !class_exists( 'Snoopy' ) )
		require_once( ABSPATH . 'wp-includes/class-snoopy.php' );
	$s = new Snoopy;
	$s->maxredirs = 0;
	$s->referer = 'http://en.gravatar.com/site/signup';
	return $s->submit( 'http://en.gravatar.com/accounts/signup', array( 'email' => $email ) );
}

function cws_gs_js() {
/*
The below is packed with YUI Compressor, so here is the source:

jQuery(document).ready(function ($) {
	function gravatar(email) {
		return 'http://www.gravatar.com/avatar/' + $.md5(email);
	}
 
	$('#email').blur(function() {
		var size = "48";
		var email = $(this).val().toLowerCase();
		if (email.indexOf('@') == -1) { return; }
		var img = new Image();
		$(img)
		.load(function() {
			$('#ajax_get_avatar').hide();
		})
		.error(function() {
			$('#ajax_get_avatar').html('<input style="width:auto;" name="get_gravatar" id="get_gravatar" value="1" type="checkbox" /> <label for="get_gravatar">Sign me up for a free Gravatar image to appear next to my comments</label>').show();
		})
		.attr('src',gravatar(email)+"?s="+size+"&d=404"); 
	});
	$('#email').trigger('blur');
});
*/
?>
<script type="text/javascript">
jQuery(document).ready(function(b){function a(c){return"http://www.gravatar.com/avatar/"+b.md5(c)}b("#email").blur(function(){var e="48";var d=b(this).val().toLowerCase();if(d.indexOf("@")==-1){return}var c=new Image();b(c).load(function(){b("#ajax_get_avatar").hide()}).error(function(){b("#ajax_get_avatar").html('<input style="width:auto;" name="get_gravatar" id="get_gravatar" value="1" type="checkbox" /> <label for="get_gravatar">Sign me up for a free Gravatar image to appear next to my comments</label>').show()}).attr("src",a(d)+"?s="+e+"&d=404")});b("#email").trigger("blur")});
</script>
<?php
}

function cws_gs_init() {
	if ( is_admin() || !is_singular() )
		return;
	wp_enqueue_script( 'jquery.md5', plugin_dir_url( __FILE__ ) . 'jquery.md5.pack.js', array( 'jquery' ) );
	add_action( 'comment_form', 'show_gravatar_signup' );
	add_action( 'wp_head', 'cws_gs_js' );
}

add_action( 'template_redirect', 'cws_gs_init' );

if ( $_POST['get_gravatar'] )
	add_action( 'plugins_loaded', 'run_gravatar_signup' );
