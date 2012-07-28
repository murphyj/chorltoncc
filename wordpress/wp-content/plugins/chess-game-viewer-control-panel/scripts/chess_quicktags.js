/* The following function is a slightly modified version of The MyQuicktags plugin by Thomas Norberg */

edButtons[edButtons.length] = 
new edButton('ed_class_html'
,'Chess Game'
,'<png'
,'>'
,'r'
);



function edShowButton(button, i) {
if (button.id == 'ed_img') {
document.write('<input type="button" id="' + button.id + '" accesskey="' + button.access + '" class="ed_button" onclick="edInsertImage(edCanvas);" value="' + button.display + '" />');
}
else if (button.id == 'ed_link') {
document.write('<input type="button" id="' + button.id + '" accesskey="' + button.access + '" class="ed_button" onclick="edInsertLink(edCanvas, ' + i + ');" value="' + button.display + '" />');
}
else if (button.id == 'ed_class_html') {
document.write('<input type="button" id="' + button.id + '" accesskey="' + button.access + '" class="ed_button" onclick="edInsertClassHtml(edCanvas, ' + i + ');" value="' + button.display + '" />');
}
else if (button.id == 'ed_class_mp3') {
document.write('<input type="button" id="' + button.id + '" accesskey="' + button.access + '" class="ed_button" onclick="edInsertClassMp3(edCanvas, ' + i + ');" value="' + button.display + '" />');
}
else {
document.write('<input type="button" id="' + button.id + '" accesskey="' + button.access + '" class="ed_button" onclick="edInsertTag(edCanvas, ' + i + ');" value="' + button.display + '"  />');
}
}



function edInsertClassHtml(myField, i, defaultValue) 
  { if (!defaultValue) 
    { defaultValue = '';
    } 
    if (!edCheckOpenTags(i)) 
    { edInserthtmlcode(myField);
    }
    else 
    { edInsertTag(myField, i);v 
    }
  }



function edInserthtmlcode(b){var a=prompt("Paste your chess game in PGN format here:","(must be valid PGN format)");
if(a){a='###pgn###'+encode_entities(a)+'%%%pgn%%%';}edInsertContent(b,a)
}


function $(id){ return document.getElementById(id) }

function encode_entities(s){
  var result = '';
  for (var i = 0; i < s.length; i++){
    var c = s.charAt(i);
    result += {}[c] || c;
  }
  return result;
}