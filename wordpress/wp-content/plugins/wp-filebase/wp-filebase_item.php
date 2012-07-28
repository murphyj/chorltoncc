<?php

require_once(WPFB_PLUGIN_ROOT . 'wp-filebase_file.php');
require_once(WPFB_PLUGIN_ROOT . 'wp-filebase_category.php');

class WPFilebaseItem {

	var $is_file;
	var $is_category;
	
	var /*private (PHP 4 compatibility) */ $last_parent_id = 0;
	var /*private (PHP 4 compatibility) */ $last_parent = null;
	
	function WPFilebaseItem($db_row=null)
	{
		if(!empty($db_row))
		{
			foreach($db_row as $col => $val){
				$this->$col = $val;
			}
			$this->is_file = isset($this->file_id);
			$this->is_category = isset($this->cat_id);
		}
	}
	
	function get_id()
	{
		if($this->is_file)
			return (int)$this->file_id;
		else
			return (int)$this->cat_id;
	}
	
	function get_name()
	{
		if($this->is_file)
			return $this->file_name;
		else
			return $this->cat_name;
	}
	
	function equals($item)
	{
		if(!is_object($item))
			return false;
			
		return ( ($this->is_file == $item->is_file) && ($this->get_id() > 0) && ($this->get_id() == $item->get_id()) );
	}
	
	function get_parent_id()
	{
		if($this->is_file)
			return (int)$this->file_category;
		else if($this->is_category)
			return (int)$this->cat_parent;
			
		return -1;
	}
	
	/*public (PHP 4 compatibility) */ function get_parent()
	{
		$pid = ($this->is_file ? $this->file_category : $this->cat_parent);
		
		// caching
		if($pid != $this->last_parent_id)
		{		
			$this->last_parent = &WPFilebaseCategory::get_category($pid);
			$this->last_parent_id = $pid;
		}

		return $this->last_parent;
	}

/*
	function get_parent_cats()
	{
		$parent_cats = array();
		
		$item = $this;
		
		while( !empty($item) && ( ($parent_id = $item->get_parent_id()) > 0) )
		{
			if(!wpfilebase_category_exists($parent_id))
				break;
			$parent_cats[] = (int)$parent_id;
			$item = wpfilebase_get_category($parent_id);
		}
		
		return $parent_cats;
	}
*/
	
	function get_path()
	{			
		$path = '/' . ($this->is_file ? $this->file_name : trim($this->cat_folder, '/'));
		
		$parent = $this->get_parent();
		if($parent != null)
			$path = $parent->get_path() . $path;
		else
			$path = wpfilebase_upload_dir() . $path; 
			
		return $path;
	}
	
	function get_rel_path()
	{
		return substr($this->get_path(), strlen(wpfilebase_upload_dir()) + 1);
	}
	
	/*public (PHP 4 compatibility) */ function db_save()
	{
		global $wpdb;
		
		$values = array();
		
		$id_var = ($this->is_file?'file_id':'cat_id');
		$db_name = ($this->is_file ? $wpdb->wpfilebase_files : $wpdb->wpfilebase_cats);
		
		foreach($this as $key => $val)
		{
			$pos = strpos($key, ($this->is_file?'file_':'cat_'));
			if($pos === false || $pos != 0 || $key == $id_var || is_array($val) || is_object($val))
				continue;
			
			$values[$key] = $val;
		}
		
		$update = !empty($this->$id_var);
			
		if ($update)
		{
			if( !$wpdb->update( $db_name, $values, array($id_var => $this->$id_var) ))
			{
				if(!empty($wpdb->last_error))
					return array( 'error' => 'Failed to update DB! ' . $wpdb->last_error);
			}
		} else {		
			if( !$wpdb->insert($db_name, $values) )
				return array( 'error' =>'Unable to insert item into DB! ' . $wpdb->last_error);				
			$this->$id_var = (int)$wpdb->insert_id;		
		}
		
		return array( 'error' => false, $id_var => $this->$id_var);
	}
	
	/*public (PHP 4 compatibility) */ function is_ancestor_of($item)
	{			
		$p = &$item->get_parent();
		if ($p == null)
			return false;

		if ($this->equals($p))
			return true;

		return $this->is_ancestor_of($p);
	}
	
	/*public (PHP 4 compatibility) */ function current_user_can_access($for_tpl=false)
	{
		if($for_tpl && !wpfilebase_get_opt('hide_inaccessible'))
			return true;
		$level = intval($this->is_file ? $this->file_required_level : $this->cat_required_level) - 1;
		return ($level < 0 || current_user_can('level_'.$level));
	}
	
	function get_url()
	{		
		$ps = wpfilebase_get_opt('disable_permalinks') ? null : get_option('permalink_structure');
		
		if($this->is_file) {
			$url = trailingslashit(get_option('home'));	
			if(!empty($ps))
				$url .= str_replace(wpfilebase_upload_dir(), wpfilebase_get_opt('download_base'), $this->get_path());
			else
				$url = add_query_arg(array('wpfb_dl' => $this->file_id), $url);
		} else {
			$url = get_permalink(wpfilebase_get_opt('file_browser_post_id'));	
			if(!empty($ps)) {
				$url = str_replace(wpfilebase_upload_dir() . '/', $url, $this->get_path());
				$url = trailingslashit($url);
			} elseif($this->cat_id > 0) {
				$url = add_query_arg(array('wpfb_cat' => $this->cat_id), $url);
			}			
		}
			
		return $url;
	}
}

?>