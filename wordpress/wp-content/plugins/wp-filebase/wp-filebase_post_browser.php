<?php 

require("../../../wp-config.php");

if(!current_user_can('read_private_posts'))
	wp_die(__('Cheatin&#8217; uh?'));

if(!empty($_REQUEST['action']) && $_REQUEST['action'] == 'get_posts')
{
	$category = $_REQUEST['category'];
	
	if($category == 'pages')
		$articles = get_pages('numberposts=0&orderby=post_title&order=ASC');
	else 
		$articles = get_posts('numberposts=0&category='.$category.'&orderby=post_date&order=DESC');
		
	$posts = '<ul>';		
	if(empty($articles)) {
		$posts .= '<li>No posts found.</li>';
	} else {
		foreach($articles as $article)
			$posts .= '<li id="article_'.$article->ID.'"><span class="date">'.substr($article->post_date, 0, 10).'</span><a href="javascript:;" onclick="selectPost(\''.( $article->ID ).'\');">'.$article->post_title.'</a></li>';
	}
	$posts .= '</ul>';
	
	echo("document.getElementById('pages').innerHTML = '" . addslashes(str_replace("\n", '', $posts)) . "'");
	exit;
}

?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo WPFB_PLUGIN_NAME; ?> Posts Browser</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script language="javascript" type="text/javascript">

</script>
<?php wp_print_scripts( array( 'sack' )); ?>

<script language="javascript" type="text/javascript">

var catsContainer;
var pagesContainer;


function onBodyLoad()
{
	catsContainer = document.getElementById('cats_container');
	pagesContainer = document.getElementById('pages_container');
	
<?php
	$post_id = (int)$_REQUEST['post'];
	if ( ($post = &get_post( $post_id )) )
	{
		if($post->post_type == 'page')
		{
			echo "	getPosts('pages');";
		} else {
			$cats = wp_get_post_categories($post_id);
			if(!empty($cats[0]))
				echo '	getPosts('.$cats[0].');';
		}
	}
?>

}

function showCategories()
{
	catsContainer.style.display = 'block';
	pagesContainer.style.display = 'none';
	document.getElementById('pages').innerHTML = '';
}


function getPosts(category)
{
	var mysack = new sack( '<?php echo basename($_SERVER['PHP_SELF']); ?>' );
	mysack.execute = 1;
	mysack.method = 'POST';
	mysack.setVar("action", "get_posts");
	mysack.setVar("category", category);
	mysack.onError = function() { alert('AJAX error!' )};
	mysack.runAJAX();

	document.getElementById('pages').innerHTML = 'Loading posts...';
	catsContainer.style.display = 'none';
	pagesContainer.style.display = 'block';

	return true;
}

function selectPost(postId)
{
	if(postId == 0)
	{
		showCategories();
		return false;
	}
	
	pagesContainer.style.display = 'none';
	
	var inputEl = opener.document.getElementById('<?php echo $_GET['el_id']; ?>');	
	if(inputEl != null)
		inputEl.value = postId;	
	
	window.close();
	return true;
}	

</script>

<style type="text/css">
	body{
		font-family: arial, verdana, tahoma, sans-serif;
		font-size: 12px;
		background-color: #ccc;
	}
	
	h2 {
		font-size: 14px;
		margin-bottom: 8px;
	}
	
	a { text-decoration: none; }
	a:hover { text-decoration: underline; }
	
	ul {
		list-style-type: none;
		margin: 0;
		padding: 0;
	}
	
	li {
		background-color: #acf;
		margin: 2px;
		padding: 2px;
	}
	
	ul.children li {
		margin-left: 10px;
		background-color: #8ad;
	}
	
	.date {
		background-coloro: #aaa;
		margin: 2px;
		padding: 1px;
	}
</style>	

</head>
<body onload="onBodyLoad()">

<div id="cats_container">
	<h2>Categories</h2>
	<ul>
		<?php
		$categories = get_categories(array('type' => 'post', 'orderby' => 'name', 'hide_empty' => true));
		wpfilebase_tier_categories($categories, 0);

		function wpfilebase_tier_categories($data, $parent){
			foreach($data AS $cat){
				if($cat->category_parent == $parent){
					echo '<li id="category_'.$cat->cat_ID.'"><a href="javascript:;" onclick="getPosts('.$cat->cat_ID.');">'.$cat->cat_name.'</a><ul class="children">';
					echo wpfilebase_tier_categories($data, $cat->cat_ID);
					echo '</ul></li>';
				}
			}	
		}

		?>
		<li><a href="javascript:;" onclick="getPosts('pages');">Pages</a></li>
	</ul>
</div>

<div id="pages_container" style="display: none;">
	<h2>Pages</h2>
	<div id="pages"></div>
	<a href="javascript:;" onclick="showCategories();">Back to categories</a>
</div>

</body>
</html>