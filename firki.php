<?php


# XXX maybe move this to group_post area of BotB code base
$firkiToolbox = '
<div class="inner t0 firkiBox">
Firki Toolbox
<div class="hMiniSeperator">&nbsp</div>
<a class="inner boxLink" href="b]"><b>bold</b></a>
<a class="inner boxLink" href="o]"><i>oblique</i></a>
<a class="inner boxLink" href="u]"><u>underline</u></a>
<a class="inner boxLink" href="-s]"><strike>strike</strike></a>
<a class="inner boxLink" href="~s]">A<sup>super</sup></a>
<a class="inner boxLink" href="_s]">A<sub>sub</sub></a>
<a class="inner boxLink" href="l[URL[TEXT]" rel="noToggle">link w/text</a>
</div>
';

STACK::SetExtraGlobal('$firkiToolbox');



class firki {

	static public render($string) {
	}

	static public strip($string) {
	}

	static public lazy_links($string, $anchor_text='', $anchor_attr='')  {
		// this filter breaks if there is a > character before the protocal
		// make sure line breaks <br> go before newline characters \n
		$filter = '/((\s|^)(ht|f)tps?:\/\/[\w-?&;#~=%\(\)\+\.\/\@]+)/i';
		if ($anchor_text == '')
			return preg_replace($filter, "<b><a href=\"$1\" ".$anchor_attr.">$1</a></b>", $string);
		else
			return preg_replace($filter, "<b><a href=\"$1\" ".$anchor_attr.">".$anchor_text."</a></b>", $string);
	}
}


class firki_botb extends firki {

	static public render($string, $data) {
		firki_BotB($string, $data);
	}

	static public strip($string) {
		firkit_strip($string);
	}
}


##=======================================================================
##========= LYCEUM // COMMENTS == FIRKI MARKUP ANATOMER =================
##=======================================================================


# XXX  should be moved to botbr_class.php

function firki_enable($botbr_level, $text) {
	if ($botbr_level >= BOTBR_LEVEL_FIRKI_ENABLE) {
		return firki_BotB($text);
	}
	else {
		return firki::lazy_links(InferHTMLbreaks(htmlspecialchars($text)));
	}
}


##  archaic code from the days of firteen.com


# XXX $data is presently a single variable
#     should at least be an array the BotB
#     can pass to the interpretor and it;s
#     extended methods
function firki_BotB($text, $data)  {
	GLOBAL $Guy;
	$body = str_replace("'[']", "^[^]", $text);
	$body = htmlspecialchars($body);
	$body = str_replace("\n", "<br>\n", $body);

	$spans = 0;
	$counters = 0;
	$hunt = "";           // tik hunt loop
	$opFlags = array();   // used to close open html tags

	while ($hunt !== false) {

		for ($i=0;$i<15;$i++) $param[$i] = "";        // ClearParams();
		$op ="";

		$embed = 0;
		while ($embed==0) {
			$hunt = strpos($body,"'[");           // check for markups
			if ($hunt === false) break 2;
			$start = $hunt;

			$pos = strpos($body,"]",$start);            // check for closing ']'
			if ($pos === false) {
				$op .= "<br />opcode not closed<br />opcode santyx fail<br />";
				$hunt = false;
				break 2;
			}
			$nextp = strpos($body,"'[",$start+2);
			if (($nextp<$pos) and ($nextp!==false)) {
				$start = $nextp;
				$embed++;
			}
			if ($nextp===false) $embed++;
			if ($nextp>$pos) $embed++;
		}

		//print ".. ".$start." ".$pos." ".$nextp." ";
		$pos++;                       // grab whole opcode
		$pos = $pos-$start;
		$opcode = substr($body,$start,$pos);
		//print "found opcode:".$opcode."!!<br><br>";
		$body = substr_replace($body,"",$start,$pos);   // strip opcode from body
		$markup = substr($opcode,2,1);
		//if (substr($opcode,2,1)=='[') $op .= 'found tick <br>'; //substr_replace($opcode,"^",2,1);
		$opcode = substr_replace($opcode,"",0,2);
		$opcode = substr_replace($opcode,"[]",-1);      // []hah ahhaha trickery!!!
		//$op .= "found opcode: ".$opcode."<br><br>";


		$i = 0;                       // parse the params
		while (strpos($opcode,"]")==true) {
			$pos = strpos($opcode,"[");
			$param[$i] = substr($opcode,0,$pos);
			$opcode = substr_replace($opcode,"",0,$pos+1);
			if ($opcode=="]") $opcode = "";         // []trixxd
			//$op .= $i.'param : '.$param[$i].'<br>';
			$i++;
		}




# XXX MOVE TO BOTB EXTENSION

		if ($param[0]=='')  {                         ## LYCEUM ARTICLE LINK

			$title = $param[1];

			if ($param[2]!='')  $article = $param[2];
			else  $article = $title;

			// XXX serious issue this query in here!!
			$qr = mysqli_query(STACK::PEEK('sql_link'),"SELECT * FROM l_articles WHERE title = '$title' LIMIT 1;");
			$class = '';
			if (!@mysqli_num_rows($qr)) $class = ' class="pseudo"';
			$op .= '<b><a href="/lyceum/View/'.$title.'"'.$class.'>'.$article.'</a></b>';
		}

		if ($param[0]=='#') {                         ## LYCEUM CONTENTS ANCHOR
			$op .= '<p class="tb4" id="'.$param[1].'">'.$param[1].'</p><hr size="1" />';
			$contents[] = '<a href="#'.$param[1].'">'.$param[1].'</a>';
		}


##  a = link article attachment
# XXX LYCEUM SPECIFIC COMMAND BY THE BOTB EXTENSION IMPLEMENTATION
		if ($param[0]=='a') {
			$file = 'data/lyceum_attach/'.$var1.'/'.$param[1];
			if (is_file($file)) {
				$size = NiceFileSize(filesize($file));
				$op .= '<b><a href="/'.$file.'">'.$param[1].'</a></b>&nbsp;<span class="t0">'.$size.'</span>';
			}
			else $op .= $param[1].'<span class="t0">==MISSING FILE</span>';
		}




		if ($param[0]=='b') {                     ## b for open bold display
			$op .= '<b>';
			$opFlags['</b>']++;
		}

		if ($param[0]=='/b') {                    ## /b for close bold display
			$op .= '</b>';
			$opFlags['</b>']--;
		}

		if ($param[0]=='c') {                     ## c for open code block display
			$op .= '<pre>';
			$opFlags['</pre>']++;
		}

		if ($param[0]=='/c') {                    ## /c for close code block display
			$op .= '</pre>';
			$opFlags['</pre>']--;
		}

		if ($param[0]=='i')  {                   ## i for image
			//$img = new imgs;
			//$img->load($param[1]);
			if ($param[2]=='r') $align='align="right"';
			if ($param[2]=='l') $align='align="left"';
			$op .= '<img src="/data/lyceum_attach/'.$var1.'/'.$param[1].'" '.$align.'>';
		}

		if ($param[0]=='icon') {                  ## icon for icon  BotB specific!!!!  :X
			$op .= icon::Img($param[1]);
		}



# XXX NEED TO MAKE A SPECIFIC METHOD FOR BOTB EXTENSION
		if ($param[0]=="l") {                   ## l for link
			GLOBAL $icon;
			$outbound = (strpos($param[1], 'battleofthebits.org')) ? FALSE : TRUE;
			if ($Guy->info['anchor_blank']=='on' || $outbound === TRUE) {
				$link_attr = ' target="_blank"';
			}
			else { 
				$link_attr = '';
			}
			if ($param[2]=='') {
				$description = 'LazyLink';
			}
			else {
				$description = $param[2];
			}
			$op .= '<b><a href="'.$param[1].'"'.$link_attr.'>'.$description;
			if ($outbound === TRUE) {
				$op .= icon::Img('outbound');
			}
			$op .= '</a></b>';
		}




		if ($param[0]=='ol')  {             ## ordered list
			$op .= '<ol>'.$param[1].'</ol>';
		}
		if ($param[0]=='ul')  {             ## unordered list
			$op .= '<ul>'.$param[1].'</ul>';
		}
		if ($param[0]=='li')  {             ## list item
			$op .= '<li>'.$param[1].'</li>';
		}

		if ($param[0]=='o') {   ## italics / oblique
			$op .= '<i>';
			$opFlags['</i>']++;
		}
		if ($param[0]=='/o')  {
			$op .= '</i>';
			$opFlags['</i>']--;
		}

		if ($param[0]=='-s') {   ## strike
			$op .= '<strike>';
			$opFlags['</strike>']++;
		}
		if ($param[0]=='/-s')  {
			$op .= '</strike>';
			$opFlags['</strike>']--;
		}

		if ($param[0]=='~s') {   ## superscript
			$op .= '<sup>';
			$opFlags['</sup>']++;
		}
		if ($param[0]=='/~s')  {
			$op .= '</sup>';
			$opFlags['</sup>']--;
		}

		if ($param[0]=='_s') {   ## subscript
			$op .= '<sub>';
			$opFlags['</sub>'];
		}
		if ($param[0]=='/_s')  {
			$op .= '</sub>';
			$opFlags['</sub>']--;
		}

		if ($param[0]=='t') {                   ## t for textyle
			$op .= '<span class="t'.$param[1].'">';
			$spans++;
		}

		if ($param[0]=='/t')  {                 ## /t for kill textyle
			$op .= '</span>';
			$spans--;
		}

		if ($param[0]=='tab') {   ## creates tabular suffix space
			if ($param[2]=='') $w = 80;
			else $w = $param[2];
			$op .= '<div style="float:left;width:'.$w.'px;">'.$param[1].'</div>';
		}

		if ($param[0]=='u') {   ## underline
			$op .= '<u>';
			$opFlags['</u>']++;
		}
		if ($param[0]=='/u')  {
			$op .= '</u>';
			$opFlags['</u>']--;
		}

		//$op .= ' '.$markup.'_markup ';

		$body = substr_replace($body,$op,$start,0);
		$counters++;
	}

	while ($spans>0) {
		$body .= "</span>";
		$spans--;
	}



	$body = str_replace("^[^]", "'[", $body);
	if (count($contents)>1) {
		$menu = '::|CONTENTS<ol class="tb1" style="margin:20px; margin-top:0px;">';
		foreach($contents as $content)  {
			$menu .= '<li>'.$content.'</li>';
		}
		$body = $menu.'</ol>'.$body;
	}


	foreach ($opFlags as $tag => $count) {
		for ($i=0; $i<$count; $i++) {
			$body .= $tag;
		}
	}
	for ($i=0;$i<=spans;$i++) { 
		$body .= '</span>'; 
	}


## LAZY LINKS REGEX
	if ($Guy->info['anchor_blank']=='on') {
		$body = firki::lazy_links($body,'','target="_blank"');
	}
	else {
		$body = firki::lazy_links($body);
	}

	return $body;
}


##=======================================================================
##========= LYCEUM // COMMENTS == FIRKI MARKUP STRIPPER =================
##=======================================================================

function firki_strip($body) {

	$body = str_replace("'[']", "^[^]", $body);

	$spans = 0;
	$counters = 0;
	$hunt = "";                       // tik hunt loop
	while ($hunt !== false) {

		for ($i=0;$i<15;$i++) $param[$i] = "";        // ClearParams();
		$op ="";

		$embed = 0;
		while ($embed==0) {
			$hunt = strpos($body,"'[");           // check for markups
			if ($hunt === false) break 2;
			$start = $hunt;

			$pos = strpos($body,"]",$start);            // check for closing ']'
			if ($pos === false) {
				$op .= "<br />opcode not closed<br />opcode santyx fail<br />";
				break 2;
			}
			$nextp = strpos($body,"'[",$start+2);
			if (($nextp<$pos) and ($nextp!==false)) {
				$start = $nextp;
				$embed++;
			}
			if ($nextp===false) $embed++;
			if ($nextp>$pos) $embed++;
		}

		//print ".. ".$start." ".$pos." ".$nextp." ";
		$pos++;                       // grab whole opcode
		$pos = $pos-$start;
		$opcode = substr($body,$start,$pos);
		//print "found opcode:".$opcode."!!<br><br>";
		$body = substr_replace($body,"",$start,$pos);   // strip opcode from body
		$markup = substr($opcode,2,1);
		//if (substr($opcode,2,1)=='[') $op .= 'found tick <br>'; //substr_replace($opcode,"^",2,1);
		$opcode = substr_replace($opcode,"",0,2);
		$opcode = substr_replace($opcode,"[]",-1);      // []hah ahhaha trickery!!!
		//$op .= "found opcode: ".$opcode."<br><br>";


		$i = 0;                       // parse the params
		while (strpos($opcode,"]")==true) {
			$pos = strpos($opcode,"[");
			$param[$i] = substr($opcode,0,$pos);
			$opcode = substr_replace($opcode,"",0,$pos+1);
			if ($opcode=="]") $opcode = "";         // []trixxd
			//$op .= $i.'param : '.$param[$i].'<br>';
			$i++;
		}

		// deANATOM(s) //

		$param[0] = strtolower($param[0]);

		if (($param[0]=="d") or ($param[0]=="dropcap")) {     // d or dropcap
			$op .= $param[2];
		}

		if ($param[0]=='')  {                         ## LYCEUM ARTICLE LINK
			$op .= $param[1];
		}

		if ($param[0]=='#') $op .= $param[1];         ## LYCEUM CONTENTs header

			if ($param[0]=='i') {                         //i or image
			}

		if ($param[0]=="l" || $param[0]=="link")  {     // l or link
			if ($param[2]=="") $param[2] = "link";
			$op .= $param[2];
		}

		if (($param[0]=="s") or ($param[0]=="sidebox")) {     // s or sidebox
		}

		if (($param[0]=="t") or ($param[0]=="typeface"))  {   // t or typeface
		}

		if (($param[0]=="/t") or ($param[0]=="/typeface"))  {
		}

		$body = substr_replace($body,$op,$start,0);
		$counters++;
	}
	$body = str_replace("^[^]", "'[", $body);
	return $body;
}

?>
