function openPostBrowser(inputId)
{
	var postId = document.getElementById(inputId).value;
	var browserWindow = window.open("../wp-content/plugins/wp-filebase/wp-filebase_post_browser.php?post=" + postId + "&el_id=" + inputId, "PostBrowser", "width=300,height=400,menubar=no,location=no,resizable=no,status=no,toolbar=no");
	browserWindow.focus();
}

function wpfilebaseAddTplField(select, input)
{
	if(select.selectedIndex == 0 || select.options[select.selectedIndex].value == '')
		return;
		
	var tag = '%' + select.options[select.selectedIndex].value + '%';
	var inputEl = select.form.elements[input];
	
	if (document.selection)
	{
		inputEl.focus();
		sel = document.selection.createRange();
		sel.text = tag;
	}
	else if (inputEl.type == 'textarea' && typeof(inputEl.selectionStart) != 'undefined' && (inputEl.selectionStart || inputEl.selectionStart == '0'))
	{
		var startPos = inputEl.selectionStart;
		var endPos = inputEl.selectionEnd;
		inputEl.value = inputEl.value.substring(0, startPos) + tag + inputEl.value.substring(endPos, inputEl.value.length);
	}
	else
	{
		inputEl.value += tag;
	}
	
	select.selectedIndex = 0;
}

function checkboxShowHide(checkbox, name)
{
	var chk = checkbox.checked;
	var input = checkbox.form.elements[name];
	if(input)
		elementShowHide(input, chk);
	
	// show/hide labels
	var lbs = checkbox.form.getElementsByTagName('label');
	for(var l = 0; l < lbs.length; ++l)
	{
		if(lbs[l].htmlFor == name)
			elementShowHide(lbs[l], chk);
	}
}

function elementShowHide(el, show)
{
	var newCs = '';
	var cs = el.className.split(' ');
	// remove hidden class
	for (var i = 0; i < cs.length; ++i)
	{
		if(cs[i] != 'hidden')
		newCs += cs[i] + ' ';
	}
	if(!show)
		newCs += 'hidden';
	else
		newCs = newCs.substring(0, newCs.length - 1)
	el.className = newCs;
}

/* Option tabs */
jQuery(document).ready( function() {
	try { jQuery('#wpfilebaseopttabs').tabs(); }
	catch(ex) {}
});