<?php

require_once("../phplib/Core.php");
User::mustHave(User::PRIV_EDIT);

# Select random definition to search.
$count = DB::getSingleValue("select count(*) from Definition where status = 0 and length(internalRep) > 250;");

$nr = rand(1, $count);

$definition = DB::getSingleValue("select htmlRep from Definition where status = 0 and length(internalRep) > 200 limit $nr ,1;");

# Parse definition and create string to search
$v = explode(" ", strip_tags($definition));

$to_search = "\"";

# Set string to search start + end
$WORD_START = 5;
$WORD_NO = 16;

$to_search .= implode( " ",array_slice($v, $WORD_START,$WORD_NO )) ;
 
$to_search .= "\"";

$to_search = str_replace ( [ ",", "(", ")", "[", "]", "-", ";", "◊", "♦", "<", ">", "?", "\\", "/"] ,
				array_pad( [], 14 ,'') ,$to_search) ; 

$urlGoogle = "https://ajax.googleapis.com/ajax/services/search/web?v=1.0";
$apiKey = Config::get('global.googleSearchApiKey');
$url = $urlGoogle . "&q=". urlencode($to_search) . "&key=" . $apiKey;


$body = Util::fetchUrl($url) ;

# now, process the JSON string
$json = json_decode($body);

$rezultate = $json->responseData->results;

$listAll = [];
$content = "";
$messageAlert = [];
$blackList = [];


foreach($rezultate as $iter) {
	# Skip dexonline.ro from results
	#if(stripos($iter->url, "dexonline.ro") == true)
	#	continue;

	$components = parse_url($iter->url);
	if (Str::endsWith($components['host'], 'dexonline.ro'))
		continue;
	
	$listAll[] = $iter ->url ;
	# Search for "licenta GPL" or "dexonline.ro" in resulted page
	$content = @file_get_contents($iter->url);
	
	$poslink = stripos($content, "dexonline.ro");
	$posGPL = stripos($content, "GPL");

	if($poslink === false && $posGPL === false) {
		$blackList[] = $iter->url;
		$messageAlert[] = "Licenta GPL sau link catre dexonline.ro negasite in site-ul $iter->url ";
	} else {
		$messageAlert[] = "A fost gasita o mentiune catre licenta GPL sau un link catre dexonline.ro in site-ul $iter->url ";
	}

}


SmartyWrap::assign('definition', $definition);

SmartyWrap::assign('listAll', $listAll);
SmartyWrap::assign('alert', $messageAlert);

# Print Blacklist items if any
SmartyWrap::assign("blackList", $blackList);
SmartyWrap::display("siteClones.tpl");
