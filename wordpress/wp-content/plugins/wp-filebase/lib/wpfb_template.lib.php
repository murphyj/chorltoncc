<?php

function wpfilebase_parse_template($tpl)
{
	echo '<!-- [WPFilebase]: parsing template ... -->';
	
	// remove existing onclicks
	$tpl = preg_replace(array('/<a\s+([^>]*)onclick=".+?"\s+([^>]*)href="%file_url%"/i', '/<a\s+([^>]*)href="%file_url%"\s+([^>]*)onclick=".+?"/i'), '<a href="%file_url%" $1$2', $tpl);
	//add dl js
	$tpl = preg_replace('/<a ([^>]*)href="%file_url%"/i', '<a $1href="%file_url%" onclick="wpfilebase_dlclick(%file_id%, \'%file_url_rel%\')"', $tpl);

	//escape
	$tpl = str_replace("'", "\\'", $tpl);
	
	// parse if's
	$tpl = preg_replace(
	'/<\!\-\- IF (.+?) \-\->([\s\S]+?)<!-- ENDIF -->/e',
	"'\\'.(('.wpfilebase_parse_template_expression('$1').')?(\\''.wpfilebase_parse_template_ifblock('$2').'\\')).\\''", $tpl);
	
	// parse translation texts
	$tpl = preg_replace('/([^\w])%\\\\\'(.+?)\\\\\'%([^\w])/', '$1\'.__(\'$2\', WPFB).\'$3', $tpl);
	// parse variables
	$tpl = preg_replace('/%([a-z0-9_]+?)%/i', '\'.$f->get_tpl_var(\'$1\').\'', $tpl);
	
	// remove html comments
	$tpl = preg_replace('/<\!\-\-[\s\S]+?\-\->/', '', $tpl);
	
	$tpl = "'$tpl'";
	
	// cleanup
	$tpl = str_replace('.\s*\'\'', '', $tpl);
	
	echo '<!-- done! -->';
	
	return $tpl;
}

function wpfilebase_parse_template_expression($exp)
{
	$exp = preg_replace('/%([a-z0-9_]+?)%/i', '($f->get_tpl_var(\'$1\'))', $exp);
	$exp = preg_replace('/([^\w])AND([^\w])/', '$1&&$2', $exp);
	$exp = preg_replace('/([^\w])OR([^\w])/', '$1||$2', $exp);
	$exp = preg_replace('/([^\w])NOT([^\w])/', '$1!$2', $exp);
	return $exp;
}

function wpfilebase_parse_template_ifblock($block)
{
	static $s = '<!-- ELSE -->';
	static $r = '\'):(\'';
	if(strpos($block, $s) === false)
		$block .= $r;
	else
		$block = str_replace($s, $r, $block);
	
	// unescape "
	$block = str_replace('\"', '"', $block);
	
	return $block;
}


function wpfilebase_check_template($tpl)
{	
	$result = array('error' => false, 'msg' => '', 'line' => '');
	
	$f = new WPFilebaseFile();
	$tpl = 'return (' . $tpl . ');';
	
	if(!@eval($tpl))
	{
		$result['error'] = true;
		
		$err = error_get_last();
		if(!empty($err))
		{
			$result['msg'] = $err['message'];
			$result['line'] = $err['line'];
		}
	}
	
	return $result;
}

?>