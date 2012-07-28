<?php
function wpfilebase_referer_check()
{
	// fix (FF?): avoid caching of redirections so the file cannot be downloaded anymore
	if(!empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) || !empty($_COOKIE[WPFB_OPT_NAME]))
		return true;
		
	if(empty($_SERVER['HTTP_REFERER']))
		return ((bool)wpfilebase_get_opt('accept_empty_referers'));
		
	$referer = @parse_url($_SERVER['HTTP_REFERER']);		
	$referer = $referer['host'];
	
	$allowed_referers = explode("\n", wpfilebase_get_opt('allowed_referers'));
	$allowed_referers[] = get_option('home');
	
	foreach($allowed_referers as $ar)
	{
		if(empty($ar))
			continue;
		
		$ar_host = @parse_url($ar);
		$ar_host = $ar_host['host'];
		if(@strpos($referer, $ar) !== false || @strpos($referer, $ar_host) !== false)
			return true;
	}
	
	return false;
}

function wpfilebase_add_traffic($bytes)
{
	$traffic = wpfilebase_get_traffic();
	$traffic['month'] = $traffic['month'] + $bytes;
	$traffic['today'] = $traffic['today'] + $bytes;	
	$traffic['time'] = time();
	wpfilebase_update_opt('traffic_stats', $traffic);
}

function wpfilebase_check_traffic($file_size)
{
	$traffic = wpfilebase_get_traffic();
	
	$limit_month = (wpfilebase_get_opt('traffic_month') * 1048576);
	$limit_day = (wpfilebase_get_opt('traffic_day') * 1073741824);
	
	return ( ($limit_month == 0 || ($traffic['month'] + $file_size) < $limit_month) && ($limit_day == 0 || ($traffic['today'] + $file_size) < $limit_day) );
}


function wpfilebase_get_file_content_type($name)
{
	$pos = strrpos($name, '.');
	if($pos !== false)
		$name = substr($name, $pos + 1);
	switch ($name)
	{
		case 'zip':		return 'application/zip';
		case 'bin':
		case 'dms':
		case 'lha':
		case 'lzh':
		case 'exe':
		case 'class':
		case 'so':
		case 'dll':		return 'application/octet-stream';   
		case 'ez':  	return 'application/andrew-inset';
		case 'hqx':		return 'application/mac-binhex40';
		case 'cpt':		return 'application/mac-compactpro';
		case 'doc':		return 'application/msword';
		case 'oda':		return 'application/oda';
		case 'pdf':		return 'application/pdf';
		case 'ai':
		case 'eps':
		case 'ps':		return 'application/postscript';
		case 'smi':
		case 'smil':	return 'application/smil';
		case 'xls':		return 'application/vnd.ms-excel';
		case 'ppt':		return 'application/vnd.ms-powerpoint';
		case 'wbxml':	return 'application/vnd.wap.wbxml';
		case 'wmlc':	return 'application/vnd.wap.wmlc';
		case 'wmlsc':	return 'application/vnd.wap.wmlscriptc';
		case 'bcpio':	return 'application/x-bcpio';
		case 'vcd':		return 'application/x-cdlink';
		case 'pgn':		return 'application/x-chess-pgn';
		case 'cpio':	return 'application/x-cpio';
		case 'csh':		return 'application/x-csh';
		case 'dcr':
		case 'dir':
		case 'dxr':		return 'application/x-director';
		case 'dvi':		return 'application/x-dvi';
		case 'spl':		return 'application/x-futuresplash';
		case 'gtar':	return 'application/x-gtar';
		case 'hdf':		return 'application/x-hdf';
		case 'js':  	return 'application/x-javascript';
		case 'skp':
		case 'skd':
		case 'skt':
		case 'skm':		return 'application/x-koan';
		case 'latex':	return 'application/x-latex';
		case 'nc':
		case 'cdf':		return 'application/x-netcdf';
		case 'sh':		return 'application/x-sh';
		case 'shar':	return 'application/x-shar';
		case 'swf':		return 'application/x-shockwave-flash';
		case 'sit':		return 'application/x-stuffit';
		case 'sv4cpio':	return 'application/x-sv4cpio';
		case 'sv4crc':	return 'application/x-sv4crc';
		case 'tar':		return 'application/x-tar';
		case 'tcl':		return 'application/x-tcl';
		case 'tex':		return 'application/x-tex';
		case 'texinfo':
		case 'texi':	return 'application/x-texinfo';
		case 't':
		case 'tr':
		case 'roff':	return 'application/x-troff';
		case 'man':		return 'application/x-troff-man';
		case 'me':		return 'application/x-troff-me';
		case 'ms':		return 'application/x-troff-ms';
		case 'ustar':	return 'application/x-ustar';
		case 'src':		return 'application/x-wais-source';
		case 'xhtml':
		case 'xht':		return 'application/xhtml+xml';
		case 'au':  	return 'audio/basic';
		case 'snd':		return 'audio/basic';
		case 'mid':		return 'audio/midi';
		case 'midi':	return 'audio/midi';
		case 'kar':		return 'audio/midi';
		case 'mpga':
		case 'mp2':
		case 'mp3':		return 'audio/mpeg';
		case 'aif':
		case 'aiff':
		case 'aifc':	return 'audio/x-aiff';
		case 'm3u':		return 'audio/x-mpegurl';
		case 'ram':
		case 'rm':		return 'audio/x-pn-realaudio';
		case 'rpm':		return 'audio/x-pn-realaudio-plugin';
		case 'ra':		return 'audio/x-realaudio';
		case 'wav':		return 'audio/x-wav';
		case 'pdb':		return 'chemical/x-pdb';
		case 'xyz':		return 'chemical/x-xyz';
		case 'bmp':		return 'image/bmp';
		case 'gif':		return 'image/gif';
		case 'ief':		return 'image/ief';
		case 'jpeg':
		case 'jpg':
		case 'jpe':		return 'image/jpeg';
		case 'png':		return 'image/png';
		case 'tiff':
		case 'tif':		return 'image/tiff';
		case 'djvu':
		case 'djv':		return 'image/vnd.djvu';
		case 'wbmp':	return 'image/vnd.wap.wbmp';
		case 'ras':		return 'image/x-cmu-raster';
		case 'ico':		return 'image/x-icon';
		case 'pnm':		return 'image/x-portable-anymap';
		case 'pbm':		return 'image/x-portable-bitmap';
		case 'pgm':		return 'image/x-portable-graymap';
		case 'ppm':		return 'image/x-portable-pixmap';
		case 'rgb':		return 'image/x-rgb';
		case 'xbm':		return 'image/x-xbitmap';
		case 'xpm':		return 'image/x-xpixmap';
		case 'xwd':		return 'image/x-xwindowdump';
		case 'igs':
		case 'iges':	return 'model/iges';
		case 'msh':
		case 'mesh':
		case 'silo':	return 'model/mesh';
		case 'wrl':
		case 'vrml':	return 'model/vrml';
		case 'css':		return 'text/css';
		case 'html':
		case 'htm':		return 'text/html';
		case 'asc':
		case 'c':
		case 'cc':
		case 'cs':
		case 'h':
		case 'hh':
		case 'cpp':
		case 'hpp':
		case 'txt':		return 'text/plain';
		case 'rtx':		return 'text/richtext';
		case 'rtf':		return 'text/rtf';
		case 'sgml':
		case 'sgm':		return 'text/sgml';
		case 'tsv':		return 'text/tab-separated-values';
		case 'wml':		return 'text/vnd.wap.wml';
		case 'wmls':	return 'text/vnd.wap.wmlscript';
		case 'etx':		return 'text/x-setext';
		case 'xml':
		case 'xsl':		return 'text/xml';
		case 'mpeg':
		case 'mpg':
		case 'mpe':		return 'video/mpeg';
		case 'qt':
		case 'mov':		return 'video/quicktime';
		case 'mxu':		return 'video/vnd.mpegurl';
		case 'avi':		return 'video/x-msvideo';
		case 'movie':	return 'video/x-sgi-movie';
		case 'asf':
		case 'asx':		return 'video/x-ms-asf';
		case 'wm':		return 'video/x-ms-wm';
		case 'wmv':		return 'video/x-ms-wmv';
		case 'wvx':		return 'video/x-ms-wvx';
		case 'ice':		return 'x-conference/x-cooltalk';
		
		default:		return 'application/octet-stream';
	}
}

function wpfilebase_download_header($file_path, $file_type)
{
	if(wpfilebase_get_opt('force_download'))
		return true;
	
	$file_name = basename($file_path);
	$request_path = parse_url($_SERVER['REQUEST_URI']);
	$request_path = urldecode($request_path['path']);
	$request_file_name = basename($request_path);
	if($file_name == $request_file_name)
		return false;
		
	// types that can be viewed in the browser
	static $media = array('audio', 'image', 'text', 'video', 'application/pdf', 'application/x-shockwave-flash');	
	foreach($media as $m)
	{
		$p = strpos($file_type, $m);
		if($p !== false && $p == 0)
			return false;
	}	
	return true;
}

function wpfilebase_range_header($file_path, $file_type)
{
	static $no_range_types = array('application/pdf', 'application/x-shockwave-flash');
	foreach($no_range_types as $t)
	{
		$p = strpos($file_type, $t);
		if($p !== false && $p == 0)
			return false;
	}	
	return true;
}

function wpfilebase_send_file($file_path, $bandwidth = 0, $etag = null)
{
	// remove some headers
	if(function_exists('header_remove')) {
		header_remove();
	} else {
		header("Expires: ");
		header("X-Pingback: ");
	}

	if(!@file_exists($file_path) || !is_file($file_path))
	{
		header('HTTP/1.x 404 Not Found');
		wp_die('File ' . basename($file_path) . ' not found!');
	}
	
	$size = filesize($file_path);
	$time = filemtime($file_path);
	$file_type = wpfilebase_get_file_content_type($file_path);
	if(empty($etag))
		$etag = md5("$size|$time|$file_type");
	
	// set basic headers
	header("Pragma: public");
	header("Cache-Control: public");
	header("Connection: close");
	header("Content-Type: " . $file_type . ((strpos($file_type, 'text/') !== false) ? '; charset=' : '')); 	// charset fix
	header("Last-Modified: " . gmdate("D, d M Y H:i:s", $time) . " GMT");
	header("ETag: $etag");
	
	$if_mod_since = !empty($_SERVER['HTTP_IF_MODIFIED_SINCE']) ? $_SERVER['HTTP_IF_MODIFIED_SINCE'] : false;
	$if_none_match = !empty($_SERVER['HTTP_IF_NONE_MATCH']) ? $_SERVER['HTTP_IF_NONE_MATCH'] : false;
	
	if($if_mod_since || $if_none_match) {
		$not_modified = true;
		
		if($not_modified && $if_mod_since)
			$not_modified = (@strtotime($if_mod_since) >= $time);
			
		if($not_modified && $if_none_match)
			$not_modified = ($if_none_match == $etag);
			
		if($not_modified) {
			header("Content-Length: " . $size);
			header("HTTP/1.x 304 Not Modified");
			exit;
		}
	}
	
	if(!($fh = @fopen($file_path, 'rb')))
		wp_die(__('Could not read file!', WPFB));
		
	$begin = 0;
	$end = $size;

	$http_range = isset($_SERVER['HTTP_RANGE']) ? $_SERVER['HTTP_RANGE'] : '';
	if(!empty($http_range) && strpos($http_range, 'bytes=') !== false && strpos($http_range, ',') === false) // multi-range not supported (yet)!
	{
		$range = explode('-', trim(substr($http_range, 6)));
		$begin = 0 + trim($range[0]);
		if(!empty($range[1]))
			$end = 0 + trim($range[1]);
	} else
		$http_range = '';
	
	if($begin > 0 || $end < $size)
		header('HTTP/1.0 206 Partial Content');
	else
		header('HTTP/1.0 200 OK');
		
	$length = ($end-$begin);
	wpfilebase_add_traffic($length);
	
	
	if(wpfilebase_range_header($file_path, $file_type))
		header("Accept-Ranges: bytes");
	
	// content headers
	if(wpfilebase_download_header($file_path, $file_type)) {
		header("Content-Disposition: attachment; filename=\"" . basename($file_path) . "\"");
		header("Content-Description: File Transfer");
	}
	header("Content-Length: " . $length);
	if(!empty($http_range))
		header("Content-Range: bytes " . $begin . "-" . ($end-1) . "/" . $size);

	
	@session_destroy();
	
	// send the file!
	
	$bandwidth = (float)$bandwidth;
	if($bandwidth <= 0)
		$bandwidth = 1024 * 1024;
	
	$buffer_size = (int)(1024 * min($bandwidth, 64));
	
	// convert kib/s => bytes/ms
	$bandwidth *= 1024;
	$bandwidth /= 1000;

	$cur = $begin;
	fseek($fh,$begin,0);
	while(!@feof($fh) && $cur < $end && @connection_status() == 0)
	{		
		$nbytes = min($buffer_size, $end-$cur);
		$ts = microtime(true);
		
		print @fread($fh, $nbytes);
		@ob_flush();
		@flush();
		
		$dt = (microtime(true) - $ts) * 1000; // dt = time delta in ms		
		$st = ($nbytes / $bandwidth) - $dt;
		if($st > 0)
			usleep($st * 1000);			
		
		$cur += $nbytes;
	}
	
	@fclose($fh);	
	return true;
}

?>