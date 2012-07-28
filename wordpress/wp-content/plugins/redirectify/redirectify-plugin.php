<?php
/*
Plugin Name: Redirectify
Plugin URI: http://redalt.com/Resources/Plugins/Redirectify
Description: A plugin that redirects posts and pages that contain a certain meta tag.
Author: Owen Winkler
Version: 1.2
Author URI: http://www.asymptomatic.net
*/
?>
<?php
/*
Redirectify - redirects posts and pages that contain
 a certain meta tag.
Copyright (c) 2004 Owen Winkler

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated
documentation files (the "Software"), to deal in the
Software without restriction, including without limitation
the rights to use, copy, modify, merge, publish,
distribute, sublicense, and/or sell copies of the Software,
and to permit persons to whom the Software is furnished to
do so, subject to the following conditions:

The above copyright notice and this permission notice shall
be included in all copies or substantial portions of the
Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY
KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE
WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS
OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR
OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE
SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/

$redirect_meta_key = 'redirect';

add_action('template_redirect', 'redirectify');

function redirectify($nothing)
{
	global $wp_query, $redirect_meta_key;
	
	if(is_single() || is_page()) {
		$redirect = get_post_meta($wp_query->post->ID, $redirect_meta_key, true);
		if('' != $redirect) {
			wp_redirect($redirect);
			header("Status: 302");
			exit;
		}
	}
}

?>
