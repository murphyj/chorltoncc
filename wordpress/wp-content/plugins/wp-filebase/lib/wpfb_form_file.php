<?php
$update = isset($item) && is_object($item) && !empty($item->file_id);
if($update) {
	$file = &$item;
	$exform = true;
} else
	$file = new stdClass();
$action = ($update ? 'updatefile' : 'addfile');
$title = $update ? __('Edit File', WPFB) : __('Upload File', WPFB);

$file_members_only = ($file->file_required_level > 0);
?>
<div class="wrap">
<h2><?php echo $title ?></h2>
<?php if(!$update) { ?><a href="<?php echo remove_query_arg('exform') ?>&amp;exform=<?php echo ($exform ? '0' : '1') ?>" class="button"><?php _e($exform ? 'Simple Form' : 'Extended Form') ?></a><?php } ?>
<?php echo '<form enctype="multipart/form-data" name="' . $action . '" id="' . $action . '" method="post" action="' . remove_query_arg(array('file_id', 'action')) . '&amp;action=manage_files" class="validate">' ?>
<input type="hidden" name="action" value="<?php echo $action ?>" />
<?php if($update) { ?><input type="hidden" name="file_id" value="<?php echo $file->file_id ?>" /><?php } ?>
<?php wp_nonce_field($action . ($update ? $file->file_id : '')); ?>
<table class="form-table">
	<tr>
		<th scope="row" valign="top"><label for="file_upload"><?php _e('Choose File', WPFB) ?></label></th>
		<td class="form-field" colspan="3"><input type="file" name="file_upload" id="file_upload" /><br />
		<?php printf(__('Maximum file size: %s', WPFB), wpfilebase_format_filesize(wpfilebase_max_upload_size())) ?>
		<?php if($update) { echo '<br /><b>' . $file->file_name . '</b> (' . $file->get_formatted_size() . ')'; } ?>
		</td>
	</tr>
	<tr>		
		<?php if($exform) { ?>		
		<th scope="row" valign="top"><label for="file_upload_thumb"><?php _e('Thumbnail'/*def*/) ?></label></th>
		<td class="form-field" colspan="3"><input type="file" name="file_upload_thumb" id="file_upload_thumb" />
		<br /><?php _e('You can optionally upload a thumbnail here. If the file is a valid image, a thumbnail is generated automatically.', WPFB); ?>
		<?php if($update && !empty($file->file_thumbnail)) { ?>
			<br /><img src="<?php echo $file->get_icon_url(); ?>" /><br />
			<b><?php echo $file->file_thumbnail; ?></b> <label for="file_delete_thumb"><input type="checkbox" value="1" name="file_delete_thumb" id="file_delete_thumb" /> <?php _e('Delete'); ?></label>
		<?php } ?>
		</td>
		<?php } else { ?><th scope="row"></th><td colspan="3"><?php _e('The following fields are optional.', WPFB) ?></td><?php } ?>
	</tr>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="file_display_name"><?php _e('Title') ?></label></th>
		<td><input name="file_display_name" id="file_display_name" type="text" value="<?php echo esc_attr($file->file_display_name); ?>" size="40" /></td>
		<th scope="row" valign="top"><label for="file_version"><?php _e('Version') ?></label></th>
		<td><input name="file_version" id="file_version" type="text" value="<?php echo esc_attr($file->file_version); ?>" size="20" /></td>
	</tr>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="file_author"><?php _e('Author') ?></label></th>
		<td><input name="file_author" id="file_author" type="text" value="<?php echo esc_attr($file->file_author); ?>" size="40" /></td>
		<?php if($exform) { ?>
		<th scope="row" valign="top"><label for="file_date"><?php _e('Date') ?></label></th>
		<td><?php
			//create a comment object for the touch_time function
			global $comment;
			$comment = new stdClass();
			$comment->comment_date = false;
			if( $file != null)					
				$comment->comment_date = $file->file_date;
			?><div class="wpfilebase-date-edit"><?php
			touch_time(($file != null),0); ?></div></td>
	</tr>
	<tr class="form-field">
		<?php } ?>
		<th scope="row" valign="top"><label for="file_category"><?php _e('Category') ?></label></th>
		<td><select name="file_category" id="file_category" class="postform"><?php echo wpfilebase_cat_selection_tree($update ? $file->file_category : 0) ?></select></td>
		<?php if($exform) { ?>
		<th scope="row" valign="top"><label for="file_license"><?php _e('License', WPFB) ?></label></th>
		<td><select name="file_license" id="file_license" class="postform"><?php echo wpfilebase_make_options_list('licenses', $file ? $file->file_license : null, true) ?></select></td>
		<?php } ?>
	</tr>

	<tr class="form-field">
		<th scope="row" valign="top"><label for="file_post_id"><?php _e('Post') ?> ID</label></th>
		<td><input type="text" name="file_post_id" class="small-text" id="file_post_id" value="<?php echo esc_attr($file->file_post_id); ?>" /> <a href="javascript:;" class="button" onclick="openPostBrowser('file_post_id');"><?php _e('Browse') ?>...</a></td>
		<?php if($exform) { ?>
		<th scope="row" valign="top"><label for="file_hits"><?php _e('Download Counter', WPFB) ?></label></th>
		<td><input type="text" name="file_hits" class="small-text" id="file_hits" value="<?php echo (int)$file->file_hits; ?>" /></td>
	</tr>
	<tr class="form-field">
		<?php if(wpfilebase_get_opt('platforms')) { ?>
		<th scope="row" valign="top"><label for="file_platforms[]"><?php _e('Platforms', WPFB) ?></label></th>
		<td><select name="file_platforms[]" size="40" multiple="multiple" id="file_platforms[]" style="height: 80px;"><?php echo wpfilebase_make_options_list('platforms', $file ? $file->file_platform : null, true) ?></select></td>
		<?php } else { ?><th></th><td></td><?php }
		if(wpfilebase_get_opt('requirements')) { ?>
		<th scope="row" valign="top"><label for="file_requirements[]"><?php _e('Requirements', WPFB) ?></label></th>
		<td><select name="file_requirements[]" size="40" multiple="multiple" id="file_requirements[]" style="height: 80px;"><?php echo wpfilebase_make_options_list('requirements', $file ? $file->file_requirement : null, true) ?></select></td>
		<?php } else { ?><th></th><td></td><?php } ?>
	</tr>
	<tr>
	<?php if(wpfilebase_get_opt('languages')) { ?>
		<th scope="row" valign="top"><label for="file_languages[]"><?php _e('Languages') ?></label></th>
		<td  class="form-field"><select name="file_languages[]" size="40" multiple="multiple" id="file_languages[]" style="height: 80px;"><?php echo wpfilebase_make_options_list('languages', $file ? $file->file_language : null, true) ?></select></td>
		<?php } else { ?><th></th><td></td><?php } ?>
		
		<th scope="row" valign="top"><label for="file_direct_linking"><?php _e('Direct linking', WPFB) ?></label></th>
		<td>
			<fieldset><legend class="hidden"><?php _e('Direct linking') ?></legend>
				<label title="<?php _e('Yes') ?>"><input type="radio" name="file_direct_linking" value="1" <?php checked('1', $file->file_direct_linking); ?>/> <?php _e('Allow direct linking', WPFB) ?></label><br />
				<label title="<?php _e('No') ?>"><input type="radio" name="file_direct_linking" value="0" <?php checked('0', $file->file_direct_linking); ?>/> <?php _e('Redirect to post', WPFB) ?></label>
			</fieldset>
		</td>
		<?php } ?>
	</tr>
	<tr class="form-field">
		<th scope="row" valign="top"><label for="file_description"><?php _e('Description') ?></label></th>
		<td colspan="3"><textarea name="file_description" id="file_description" rows="5" cols="50" style="width: 97%;"><?php echo wp_specialchars($file->file_description); ?></textarea></td>
	</tr>
	<?php if($exform) { ?>
	<tr>
		<th scope="row" valign="top"><label for="file_offline"><?php _e('Offline', WPFB) ?></label></th>
		<td><input type="checkbox" name="file_offline" value="1" <?php checked('1', $file->file_offline); ?>/></td>
		
		<th scope="row" valign="top"><label for="file_members_only"><?php _e('For members only', WPFB) ?></label></th>
		<td>
			<input type="checkbox" name="file_members_only" value="1" <?php checked(true, $file_members_only) ?> onclick="checkboxShowHide(this, 'file_required_level')" />
			<label for="file_required_level"<?php if(!$file_members_only) { echo ' class="hidden"'; } ?>><?php printf(__('Minimum user level: (see %s)', WPFB), '<a href="http://codex.wordpress.org/Roles_and_Capabilities#Role_to_User_Level_Conversion" target="_blank">Role to User Level Conversion</a>') ?> <input type="text" name="file_required_level" class="small-text<?php if(!$file_members_only) { echo ' hidden'; } ?>" id="file_required_level" value="<?php echo max(0, intval($file->file_required_level) - 1); ?>" /></label>
		</td>
	</tr>
	<?php } ?>
</table>
<p class="submit"><input type="submit" class="button-primary" name="submit" value="<?php echo $title ?>" /></p>
</form>
</div>