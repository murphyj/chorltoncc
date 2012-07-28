<?php

wpfilebase_inclib('admin');
wpfilebase_inclib('output');

function wpfilebase_admin_manage()
{
	global $wpdb, $user_ID;
	
	$_POST = stripslashes_deep($_POST);
	$_GET = stripslashes_deep($_GET);
	
	$action = (!empty($_POST['action']) ? $_POST['action'] : (!empty($_GET['action']) ? $_GET['action'] : ''));
	$clean_uri = remove_query_arg(array('message', 'action', 'file_id', 'cat_id', 'deltpl', 'hash_sync' /* , 's'*/)); // keep search keyword
	
	// switch simple/extended form
	if(isset($_GET['exform'])) {
		$exform = (!empty($_GET['exform']) && $_GET['exform'] == 1);
		update_user_option($user_ID, WPFB_OPT_NAME . '_exform', $exform); 
	} else {
		$exform = (bool)get_user_option(WPFB_OPT_NAME . '_exform');
	}
	
	wpfilebase_version_update_check();
	
	echo '<div class="wrap">';
	if(!empty($_GET['action']))
			echo '<p><a href="' . $clean_uri . '" class="button">' . __('Go back'/*def*/) . '</a></p>';
	
	switch($action)
	{
		case 'updatecat':
			$cat_id = (int)$_POST['cat_id'];
			$update = true;
		case 'addcat':
			$update = !empty($update);
			if ( !current_user_can('manage_categories') )
				wp_die(__('Cheatin&#8217; uh?'/*def*/));
			
			$result = wpfilebase_insert_category(array_merge($_POST, $_FILES));
			if(isset($result['error']) && $result['error']) {
				$message = $result['error'];
			} else {
				$message = $update?__('Category updated.'):__('Category added.');/*def*/
			}
			
			//wp_redirect($clean_uri . '&action=manage_cats&message=' . urlencode($message));
			
		case 'manage_cats':		
			if(!current_user_can('manage_categories'))
				wp_die(__('Cheatin&#8217; uh?'/*def*/));
				
			if(!empty($_POST['deleteit']))
			{
				foreach ( (array) $_POST['delete'] as $cat_id ) {
					if(is_object($cat = WPFilebaseCategory::get_category($cat_id)))
						$cat->delete();
				}
			}
?>
	<h2><?php
	printf(__('Manage Categories (<a href="%s">add new</a>)', WPFB), '#addcat');
	if ( isset($_GET['s']) && $_GET['s'] )
		printf( '<span class="subtitle">' . __('Search results for &#8220;%s&#8221;'/*def*/) . '</span>', wp_specialchars(stripslashes($_GET['s'])));
	?></h2>

	<?php if ( !empty($message) ) : ?><div id="message" class="updated fade"><p><?php echo $message; ?></p></div><?php endif; ?> 

	<form class="search-form topmargin" action="" method="get"><p class="search-box">
		<input type="hidden" value="<?php echo esc_attr($_GET['page']); ?>" name="page" />
		<input type="hidden" value="<?php echo esc_attr($_GET['action']); ?>" name="action" />
		<label class="hidden" for="category-search-input"><?php _e('Search Categories'/*def*/); ?>:</label>
		<input type="text" class="search-input" id="category-search-input" name="s" value="<?php echo(isset($_GET['s']) ? esc_attr($_GET['s']) : ''); ?>" />
		<input type="submit" value="<?php _e( 'Search Categories'/*def*/); ?>" class="button" />
	</p></form>	
	
	<br class="clear" />
	
	<form id="posts-filter" action="" method="post">
		<div class="tablenav">
			<?php
			$pagenum = max(isset($_GET['pagenum']) ? absint($_GET['pagenum']) : 0, 1);
			if(!isset($catsperpage) || $catsperpage < 0)
				$catsperpage = 20;
				
			$pagestart = ($pagenum - 1) * $catsperpage;

			$extra_sql = '';
			if(!empty($_GET['s'])) {
				$s = $wpdb->escape(trim($_GET['s']));
				$extra_sql .= "WHERE cat_name LIKE '%$s%' OR cat_description LIKE '%$s%' OR cat_folder LIKE '%$s%' ";
			}
			
			if(!empty($_GET['order']) && in_array($_GET['order'], array_keys(get_class_vars('WPFilebaseCategory'))))
				$extra_sql .= "ORDER BY " . $_GET['order'] . " " . (!empty($_GET['desc']) ? "DESC" : "ASC");		

			$cats = WPFilebaseCategory::get_categories($extra_sql . " LIMIT $pagestart, $catsperpage");

			$page_links = paginate_links(array(
				'base' => add_query_arg( 'pagenum', '%#%' ),
				'format' => '',
				'total' => ceil(count(WPFilebaseCategory::get_categories($extra_sql)) / $catsperpage),
				'current' => $pagenum
			));

			if ( $page_links )
				echo "<div class='tablenav-pages'>$page_links</div>";
			?>

			<div class="alignleft"><input type="submit" value="<?php _e('Delete'); ?>" name="deleteit" class="button delete" /><?php wp_nonce_field('bulk-categories'); ?></div>
		</div>
	
		<br class="clear" />

		<table class="widefat">
			<thead>
			<tr>
				<th scope="col" class="check-column"><input type="checkbox" /></th>
				<th scope="col"><a href="<?php echo wpfilebase_admin_table_sort_link('cat_name') ?>"><?php _e('Name'/*def*/) ?></a></th>
				<th scope="col"><a href="<?php echo wpfilebase_admin_table_sort_link('cat_description') ?>"><?php _e('Description'/*def*/) ?></a></th>
				<th scope="col" class="num"><a href="<?php echo wpfilebase_admin_table_sort_link('cat_files') ?>"><?php _e('Files', WPFB) ?></th>
				<th scope="col"><?php _e('Category Parent'/*def*/) ?></th>
			</tr>
			</thead>
			<tbody id="the-list" class="list:cat">
			<?php
			foreach($cats as $cat_id => $cat)
			{
				$parent_cat = $cat->get_parent();
			?>
			<tr id="cat-<?php echo $cat_id; ?>">
				<th scope="row" class="check-column"><input type="checkbox" name="delete[]" value="<?php echo $cat_id; ?>" /></th>
				<td><a class="row-title" href="<?php echo $clean_uri; ?>&amp;action=editcat&amp;cat_id=<?php echo $cat_id; ?>" title="&quot;<?php echo esc_attr($cat->cat_name); ?>&quot; bearbeiten"><?php echo esc_attr($cat->cat_name); ?></a></td>
				<td><?php echo wp_specialchars($cat->cat_description) ?></td>
				<td class="num"><?php echo $cat->cat_files ?></td>
				<td><?php echo wp_specialchars($parent_cat->cat_name) ?></td>
			</tr>
			<?php } ?>
			</tbody>
		</table>
		<div class="tablenav"><?php if ( $page_links ) { echo "<div class='tablenav-pages'>$page_links</div>"; } ?></div>
	</form>
	<br class="clear" />
	
	<?php if ( current_user_can('manage_categories') ) : ?>
		<p><?php _e('<strong>Note:</strong><br />Deleting a category does not delete the files in that category. Instead, files that were assigned to the deleted category are set to the parent category.', WPFB) ?></p><?php
		wpfilebase_admin_form('cat');
		endif;

	break;
	
		case 'updatefile':
			$file_id = (int)$_POST['file_id'];
			$update = true;			
		case 'addfile':
			$update = !empty($update);
			
			if ( !current_user_can('upload_files') )
				wp_die(__('Cheatin&#8217; uh?'));
				
			/* // this was causing some trouble...
			foreach ( array('aa', 'mm', 'jj', 'hh', 'mn') as $timeunit ) {
				if ( !empty($_POST['hidden_' . $timeunit] ) && $_POST['hidden_' . $timeunit] != $_POST[$timeunit] ) {
					$edit_date = true;
					break;
				}
			}*/
			
			extract($_POST);
			$jj = ($jj > 31 ) ? 31 : $jj;
			$hh = ($hh > 23 ) ? $hh -24 : $hh;
			$mn = ($mn > 59 ) ? $mn -60 : $mn;
			$ss = ($ss > 59 ) ? $ss -60 : $ss;
			$_POST['file_date'] =  sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $aa, $mm, $jj, $hh, $mn, $ss );
			
			$result = wpfilebase_insert_file(array_merge($_POST, $_FILES));
			if(isset($result['error']) && $result['error']) {
				$message = $result['error'];
			} else {
				$message = $update?__('File updated.', WPFB):__('File added.', WPFB);
			}

		case 'manage_files':
		
			if(!current_user_can('upload_files'))
				wp_die(__('Cheatin&#8217; uh?'));
				
			if(!empty($_POST['deleteit'])) {
				foreach ( (array)$_POST['delete'] as $file_id ) {					
					if(is_object($file = WPFilebaseFile::get_file($file_id)))
						$file->remove();
				}
			}
?>
	<h2><?php
	printf(__('Manage Files (<a href="%s">add new</a>)', WPFB), '#addfile');
	if ( isset($_GET['s']) && $_GET['s'] )
		printf( '<span class="subtitle">' . __('Search results for &#8220;%s&#8221;'/*def*/) . '</span>', wp_specialchars(stripslashes($_GET['s'])));
	?></h2>
	<?php if ( !empty($message) ) : ?><div id="message" class="updated fade"><p><?php echo $message; ?></p></div><?php endif; ?> 
	<form class="search-form topmargin" action="" method="get"><p class="search-box">
			<input type="hidden" value="<?php echo esc_attr($_GET['page']); ?>" name="page" />
			<input type="hidden" value="<?php echo esc_attr($_GET['action']); ?>" name="action" />
			<label class="hidden" for="file-search-input"><?php _e('Search Files', WPFB); ?>:</label>
			<input type="text" class="search-input" id="file-search-input" name="s" value="<?php echo(isset($_GET['s']) ? esc_attr($_GET['s']) : ''); ?>" />
			<input type="submit" value="<?php _e('Search Files', WPFB); ?>" class="button" />
	</p></form>
	
	<br class="clear" />

	<form id="posts-filter" action="" method="post">
		<div class="tablenav">
			<?php
			$pagenum = max(isset($_GET['pagenum']) ? absint($_GET['pagenum']) : 0, 1);
			if( !isset($filesperpage) || $filesperpage < 0 )
				$filesperpage = 20;
				
			$pagestart = ($pagenum - 1) * $filesperpage;

			$extra_sql = '';
			if(!empty($_GET['s']))
			{
				$s = $wpdb->escape(trim($_GET['s']));
				$extra_sql .= "WHERE file_name LIKE '%$s%' 
				OR file_thumbnail LIKE '%$s%'
				OR file_display_name LIKE '%$s%'
				OR file_description LIKE '%$s%'
				OR file_requirement LIKE '%$s%'
				OR file_version LIKE '%$s%' OR file_author LIKE '%$s%'
				OR file_language LIKE '%$s%' OR file_platform LIKE '%$s%' OR file_license LIKE '%$s%'	";
			}
		
			
			if(!empty($_GET['order']) && in_array($_GET['order'], array_keys(get_class_vars('WPFilebaseFile'))))
				$extra_sql .= "ORDER BY " . $_GET['order'] . " " . (!empty($_GET['desc']) ? "DESC" : "ASC");	
			else
				$extra_sql .= "ORDER BY file_id DESC";

			$files = &WPFilebaseFile::get_files($extra_sql . " LIMIT $pagestart, $filesperpage");

			$page_links = paginate_links( array(
				'base' => add_query_arg( 'pagenum', '%#%' ),
				'format' => '',
				'total' => ceil(count(WPFilebaseFile::get_files($extra_sql)) / $filesperpage),
				'current' => $pagenum
			));

			if ( $page_links )
				echo "<div class='tablenav-pages'>$page_links</div>";
			?>
			<div class="alignleft">
				<input type="submit" value="<?php _e('Delete'); ?>" name="deleteit" class="button delete" />
				<?php wp_nonce_field('bulk-files'); ?>
			</div>
		</div> <!-- tablenav -->
		
		<br class="clear" />
		
		<table class="widefat">
			<thead>
			<tr>
				<th scope="col" class="check-column"><input type="checkbox" /></th>
				<th scope="col"><a href="<?php echo wpfilebase_admin_table_sort_link('file_display_name') ?>"><?php _e('Name'/*def*/) ?></th>	
				<th scope="col"><a href="<?php echo wpfilebase_admin_table_sort_link('file_name') ?>"><?php _e('Filename', WPFB) ?></th>    
				<th scope="col"><a href="<?php echo wpfilebase_admin_table_sort_link('file_size') ?>"><?php _e('Size'/*def*/) ?></th>    		
				<th scope="col"><a href="<?php echo wpfilebase_admin_table_sort_link('file_description') ?>"><?php _e('Description'/*def*/) ?></th>
				<th scope="col"><a href="<?php echo wpfilebase_admin_table_sort_link('file_category') ?>"><?php _e('Category'/*def*/) ?></th>
				<th scope="col" class="num"><a href="<?php echo wpfilebase_admin_table_sort_link('file_hits') ?>"><?php _e('Hits', WPFB) ?></th>
				<th scope="col"><a href="<?php echo wpfilebase_admin_table_sort_link('file_last_dl_time') ?>"><?php _e('Last download', WPFB) ?></th>
				<!-- TODO <th scope="col" class="num"><a href="<?php echo wpfilebase_admin_table_sort_link('file_') ?>"><?php _e('Rating'/*def*/) ?></th> -->
			</tr>
			</thead>
			<tbody id="the-list" class="list:file wpfilebase-list">
			<?php
				foreach($files as $file_id => $file)
				{
					if($file->file_ratings > 0)
						$rating = round((float)$file->file_rating_sum / (float)$file->file_ratings, 2);
					else
						$rating = '-';
						
					$cat = $file->get_parent();
				?>
				<tr id='file-<?php echo $file_id ?>'<?php if($file->file_offline) { echo " class='offline'"; } ?>>
						   <th scope='row' class='check-column'><input type='checkbox' name='delete[]' value='<?php echo $file_id ?>' /></th>
							<td><a class='row-title' href='<?php echo $clean_uri; ?>&amp;action=editfile&amp;file_id=<?php echo $file_id; ?>' title='&quot;<?php echo esc_attr($file->file_display_name); ?>&quot; bearbeiten'><?php echo wp_specialchars($file->file_display_name); ?></a></td>
							<td><?php echo wp_specialchars($file->file_name); ?></td>
							<td><?php echo wpfilebase_format_filesize($file->file_size); ?></td>
							<td><?php echo wp_specialchars($file->file_description); ?></td>
							<td><?php echo wp_specialchars($cat->cat_name); ?></td>
							<td class='num'><?php echo $file->file_hits; ?></td>
							<td><?php echo ( (!empty($file->file_last_dl_time) && $file->file_last_dl_time > 0) ? mysql2date(get_option('date_format'), $file->file_last_dl_time) : '-') ?></td>
							<!-- TODO <td class='num'><?php echo $rating ?></td> -->
							
				</tr>
				<?php
				}
			?>
			</tbody>
		</table>		
		<div class="tablenav"><?php if ( $page_links ) { echo "<div class='tablenav-pages'>$page_links</div>"; } ?></div>		
	</form>
	
	<br class="clear" />

<?php

	unset($file);
	wpfilebase_admin_form('file', null, $exform);
	
	break; // manage_files
		
		case 'editfile':
			if(!current_user_can('upload_files'))
				wp_die(__('Cheatin&#8217; uh?'));
			$file_id = intval($_GET['file_id']);
			$file = &WPFilebaseFile::get_file($file_id);
			wpfilebase_admin_form('file', $file);
			break;			
			
		case 'editcat':
			if ( !current_user_can('manage_categories') )
				wp_die(__('Cheatin&#8217; uh?'));
				
			$cat_id = (int)$_GET['cat_id'];
			$file_category = &WPFilebaseCategory::get_category($cat_id);
			wpfilebase_admin_form('cat', $file_category);
			break;
			
			
		case 'sync':
			$result = wpfilebase_sync(!empty($_GET['hash_sync']));
			$num_changed = $num_added = $num_errors = 0;
			foreach($result as $tag => $group)
			{
				if(empty($group) || !is_array($group) || count($group) == 0)
					continue;
					
				$t = str_replace('_', ' ', $tag);
				$t{0} = strtoupper($t{0});
				
				if($tag == 'added')
					$num_added += count($group);
				elseif($tag == 'error')
					$num_errors++;
				elseif($tag != 'warnings')
					$num_changed += count($group);
				
				echo '<h2>' . __($t) . '</h2><ul>';
				foreach($group as $item)
					echo '<li>' . (is_object($item) ? $item->get_rel_path() : $item) . '</li>';
				echo '</ul>';
			}
			
			echo '<p>';
			if($num_changed == 0 && $num_added == 0)
				_e('Nothing changed!', WPFB);

			if($num_changed > 0)
				printf(__('Changed %d items.', WPFB), $num_changed);
				
			if($num_added > 0) {
				echo '<br />';
				printf(__('Added %d files.', WPFB), $num_added);
			}
			echo '</p>';
			
			if( $num_errors == 0)
				echo '<p>' . __('Filebase successfully synced.', WPFB) . '</p>';
			
			if(empty($_GET['hash_sync']))
				echo '<p><a href="' . $clean_uri . '&amp;action=sync&amp;hash_sync=1" class="button">' . __('Complete file sync', WPFB) . '</a><br /><br />' . __('Checks files for changes, so more reliable but might take much longer. Do this if you uploaded/changed files with FTP.', WPFB) . '</p>';			
			
		break; // sync
		
		case 'edit_css':
			if(!current_user_can('edit_themes'))
				wp_die(__('Cheatin&#8217; uh?'));
		
			$css_path_edit = wpfilebase_upload_dir() . '/_wp-filebase.css';
			$css_path_default = WPFB_PLUGIN_ROOT . 'wp-filebase.css';
			
			$exists = file_exists($css_path_edit) && is_file($css_path_edit);
			if( ($exists && !is_writable($css_path_edit)) || (!$exists && !is_writable(dirname($css_path_edit))) ) {
				?><div class="error default-password-nag"><p><?php printf(__('%s is not writable!', WPFB), $css_path_edit) ?></p></div><?php
				break;
			}
			
			if(!empty($_POST['restore_default'])) {
				@unlink($css_path_edit);
				$exists = false;				
			} elseif(!empty($_POST['submit']) && !empty($_POST['newcontent'])) {
				// write
				$newcontent = stripslashes($_POST['newcontent']);
				$f = fopen($css_path_edit, 'w+');
				if ($f !== false) {
					fwrite($f, $newcontent);
					fclose($f);
					$exists = true;
				}
			}

			$fpath = $exists ? $css_path_edit : $css_path_default;
			$f = fopen($fpath , 'r');
			$content = fread($f, filesize($fpath));
			fclose($f);
			$content = htmlspecialchars($content);
			?>
<form name="csseditor" id="csseditor" action="<?php echo $clean_uri ?>&amp;action=edit_css" method="post">
		 <div><textarea cols="70" rows="25" name="newcontent" id="newcontent" tabindex="1" class="codepress css" style="width: 98%;"><?php echo $content ?></textarea>
		 <input type="hidden" name="action" value="edit_css" />
		<p class="submit">
		<?php echo "<input type='submit' name='submit' class='button-primary' value='" . esc_attr__('Update File', WPFB) . "' tabindex='2' />" ?>
		<?php if($exists) { echo "<input type='submit' name='restore_default' class='button' value='" . esc_attr__('Restore Default', WPFB) . "' tabindex='3' />"; } ?>
		</p>
		</div>
</form>
<?php
		break; // edit_css
		
		
		case 'manage_tpls':			
			$tpls = get_option(WPFB_OPT_NAME . '_tpls');
			
			if(!empty($_POST['submit'])) {
				foreach($tpls as $tpl_tag => $tpl_src) {
					if(!empty($_POST['tplsrc_'.$tpl_tag]))
						$tpls[$tpl_tag] = stripslashes($_POST['tplsrc_'.$tpl_tag]);
				}
				
				if(!empty($_POST['newtpl_tag']) && !empty($_POST['newtpl_src'])) {
					$tag = preg_replace('/[^a-z0-9_-]/', '', str_replace(' ', '_', strtolower($_POST['newtpl_tag'])));
					$tpls[$tag] = stripslashes($_POST['newtpl_src']);
				}
				
				update_option(WPFB_OPT_NAME . '_tpls', $tpls);
				wpfilebase_parse_tpls();
			} elseif(!empty($_GET['deltpl']) && isset($tpls[$_GET['deltpl']])) {
				unset($tpls[$_GET['deltpl']]);
				update_option(WPFB_OPT_NAME . '_tpls', $tpls);
				wpfilebase_parse_tpls();
			}
			
			if(!empty($tpls)) {
			?>
<p>Here you can add and edit your custom templates for file lists and single files embedded in your posts. When creating a template you can use file variables in the HTML code.
<h2><?php _e('Edit Templates', WPFB) ?></h2>
<form name="addtpl" id="addtpl" action="<?php echo $clean_uri ?>&amp;action=manage_tpls" method="post">
			<?php
				foreach($tpls as $tpl_tag => $tpl_src)
				{
					?>
					<div style="margin: 10px 0 25px 0;">
						<b><?php echo esc_attr($tpl_tag) ?></b> <a href="<?php echo $clean_uri ?>&amp;action=manage_tpls&amp;deltpl=<?php echo esc_attr($tpl_tag) ?>" class="button delete"><?php _e('Delete') ?></a>
						<textarea cols="70" rows="<?php echo (substr_count($tpl_src, "\n") + 2); ?>" wrap="off" name="tplsrc_<?php echo esc_attr($tpl_tag) ?>" tabindex="1" class="codepress html wpfilebase-tpledit" style="margin-top: 5px;"><?php echo htmlspecialchars($tpl_src) ?></textarea><br />
						<?php echo wpfilebase_template_fields_select('tplsrc_'.$tpl_tag) ?>
					</div>
					<?php
				}
				?>
	<p class="submit"><?php echo "<input type='submit' name='submit' class='button-primary' value='" . esc_attr__('Submit Template Changes', WPFB) . "' tabindex='2' />" ?></p>
</form>
				<?php
			}			
			?>
			
			<h2><?php _e('Add Template') ?></h2>
<form name="addtpl" id="addtpl" action="<?php echo $clean_uri ?>&amp;action=manage_tpls" method="post">
	<p>
		<?php _e('Template Tag (a single word to describe the template):', WPFB) ?><br />
		<input type="text" name="newtpl_tag" value="" tabindex="1" maxlength="20" /><br />
		<?php _e('Template Code:', WPFB) ?><br />
		<textarea cols="70" wrap="off" rows="15" name="newtpl_src" tabindex="2" class="codepress html wpfilebase-tpledit"></textarea><br />
		<?php echo wpfilebase_template_fields_select('newtpl_src') ?>
	</p>
	<p class="submit"><?php echo "<input type='submit' name='submit' class='button-primary' value='" . esc_attr__('Add Template', WPFB) . "' tabindex='2' />" ?></p>
</form>
		<?php		
		break; // manage_tpls
		
		
		case 'donate':
		
		$lang = 'en_US';
		$supported_langs = array('en_US', 'de_DE', 'fr_FR', 'es_ES', 'it_IT', 'ja_JP', 'pl_PL', 'nl_NL');
		
		if(defined('WPLANG') && WPLANG && WPLANG != '') {
			if(in_array(WPLANG, $supported_langs))
				$lang = WPLANG;
			else {
				$l = strtolower(substr(WPLANG, 0, strpos(WPLANG, '_')));
				foreach($supported_langs as $sl) {
					$pos = strpos($sl,$l);
					if($pos !== false && $pos == 0) {
						$lang = $sl;
					}
				}
			}
		}
?>
<div id="wpfilebase-donate">
<p><?php _e('If you like WP-Filebase I would appreciate a small donation to support my work. You can additionally add an idea to make WP-Filebase even better. Just click the button below. Thank you!', WPFB) ?></p>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="AF6TBLTYLUMD2">
<input type="image" src="https://www.paypal.com/<?php echo $lang ?>/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypal.com/<?php echo $lang ?>/i/scr/pixel.gif" width="1" height="1">
</form>	
</div>
<?php  
		break;
			
		default:
			$clean_uri = remove_query_arg('pagenum', $clean_uri);
			?>
			<h2>Filebase</h2>
			<?php
				$upload_dir = wpfilebase_upload_dir();
				$upload_dir_rel = str_replace(ABSPATH, '', $upload_dir);
				$chmod_cmd = "CHMOD 777 ".$upload_dir_rel;
				if(!is_dir($upload_dir)) {
					$result = wpfilebase_mkdir($upload_dir);
					if($result['error'])
						$error_msg = sprintf(__('The upload directory <code>%s</code> does not exists. It could not be created automatically because the directory <code>%s</code> is not writable. Please create <code>%s</code> and make it writable for the webserver by executing the following FTP command: <code>%s</code>', WPFB), $upload_dir_rel, str_replace(ABSPATH, '', $result['parent']), $upload_dir_rel, $chmod_cmd);
					else
						wpfilebase_protect_upload_path();
				} elseif(!is_writable($upload_dir)) {
					$error_msg = sprintf(__('The upload directory <code>%s</code> is not writable. Please make it writable for PHP by executing the follwing FTP command: <code>%s</code>', WPFB), $upload_dir_rel, $chmod_cmd);
				}
				
				if(!empty($error_msg)) { ?><div class="error default-password-nag"><p><?php echo $error_msg ?></p></div><?php } ?>
			<div id="wpfilebase-managebtns">
			<?php
				$buttons = array(
					array('title' => __('Manage Files', WPFB),		'desc' => __('View uploaded files and edit them', WPFB),				'capability' => 'upload_files',			'action' => 'manage_files'),
					array('title' => __('Manage Categories', WPFB),	'desc' => __('Manage existing categories and add new ones.', WPFB),	'capability' => 'manage_categories',	'action' => 'manage_cats'),
					array('title' => __('Sync Filebase', WPFB),		'desc' => __('Synchronises the database with the file system. Use this to add FTP-uploaded files.', WPFB),	'action' => 'sync'),
					array('title' => __('Edit Stylesheet', WPFB),	'desc' => __('Edit the CSS for the file template', WPFB),				'capability' => 'edit_themes',			'action' => 'edit_css'),
					array('title' => __('Manage Templates', WPFB),	'desc' => __('Edit custom file list templates', WPFB),				'capability' => 'edit_themes',			'action' => 'manage_tpls'),
					array('title' => __('Donate &amp; Feature Request', WPFB), 'desc' => __('If you like this plugin and want to support my work, please donate. You can also post your ideas making the plugin better.', WPFB), 'action' => 'donate'),
				);
				foreach($buttons as $btn) {
					if(empty($btn['capability']) || current_user_can($btn['capability'])) {
						echo '<a href="' . $clean_uri . '&amp;action=' . $btn['action'] . '" class="button" title="' . $btn['desc'] . '">' . $btn['title'] . '</a>'."\n";
					}
				}
			?>
			</div>
			
			<h2><?php _e('Traffic', WPFB); ?></h2>
			<table class="form-table">
			<?php
				$traffic_stats = wpfilebase_get_traffic();					
				$limit_day = (wpfilebase_get_opt('traffic_day') * 1048576);
				$limit_month = (wpfilebase_get_opt('traffic_month') * 1073741824);
			?>
			<tr>
				<th scope="row"><?php _e('Today', WPFB); ?></th>
				<td><?php
					if($limit_day > 0)
						wpfilebase_progress_bar($traffic_stats['today'] / $limit_day, wpfilebase_format_filesize($traffic_stats['today']) . '/' . wpfilebase_format_filesize($limit_day));
					else
						echo wpfilebase_format_filesize($traffic_stats['today']);
				?></td>
			</tr>
			<tr>
				<th scope="row"><?php _e('This Month', WPFB); ?></th>
				<td><?php
					if($limit_month > 0)
						wpfilebase_progress_bar($traffic_stats['month'] / $limit_month, wpfilebase_format_filesize($traffic_stats['month']) . '/' . wpfilebase_format_filesize($limit_month));
					else
						echo wpfilebase_format_filesize($traffic_stats['month']);
				?></td>
			</tr>
			</table>
			
			<?php wpfilebase_admin_form('file', null, $exform) ?>
			
			<h2><?php _e('Copyright'); ?></h2>
			<p>
			<?php echo WPFB_PLUGIN_NAME . ' ' . WPFB_VERSION ?> Copyright &copy; 2010 by Fabian Schlieper <a href="http://fabi.me/">
			<?php if(strpos($_SERVER['SERVER_PROTOCOL'], 'HTTPS') === false) { ?><img src="http://fabi.me/misc/wpfb_icon.gif" alt="" /><?php } ?> fabi.me</a><br/>
			Includes code of the thumbnail generator <a href="http://phpthumb.sourceforge.net">phpThumb()</a> by James Heinrich
			</p><?
			break;
	}	
	?>
</div> <!-- wrap -->
<?php
}
?>