<?php
/******
* HelpSpot to Geckboard examples.
* Care of PHP, Guzzle and the Geckoboard Push API
*
* By: Jon Davis -- http://snowulf.com
* Last Updated: 2013-02-13
* License: CC-BY-3.0 -- http://creativecommons.org/licenses/by/3.0/us/
* More details at: http://snowulf.com/2013/02/13/using-geckoboard-with-helpspot/
*******/

// Required tools and things
require 'vendor/autoload.php';
include('HelpSpotAPI.php');
use Guzzle\Http\Client;

// Globals
$GLOBALS['base'] = 'https://push.geckoboard.com/v1/send/'; // Geckoboard push API endpoint
$GLOBALS['key'] = 'XXXX'; // Geckoboard push API credential (Geckboard > Account > API)
$GLOBALS['hs'] = 'http://helpspot.company.com/'; // Your HelpSpot base URL, include trailing slash
$GLOBALS['hs-user'] = 'admin@company.com'; // Your HelpSpot Username
$GLOBALS['hs-pass'] = 'XXXpasswordXXX'; // Your HelpSpot Password
// Remember to update all cases of "WIDGET-KEY-HERE"

echo 'Setting up connection...<br/>';

// Setup a connection
$hsapi = new HelpSpotAPI(array(
				'helpSpotApiURL' => $GLOBALS['hs']."api/index.php",
				'username' => $GLOBALS['hs-user'],
				'password' => $GLOBALS['hs-pass'] ));


/**** New Request Count ****/
$item1 = array();

	$result1 = $hsapi->privateRequestSearch(array(
					'relativedate' => 'past_7',
					));
	$week1 = count($result1['requests']['request']);				
	$item1[] = array('value' => $week1, 'text' => "This week");

$item2 = array();

	$result2 = $hsapi->privateRequestSearch(array(
					'relativedate' => 'past_14',
					));
	
	$week2 = count($result2['requests']['request']);
	$lastweek = $week2 - $week1;
	
	$item2[] = array('value' => $lastweek, 'text' => "Last week");

	$item = array_merge($item1, $item2);

// Format data into Geckoboard JSON
$newreq = array(
	'api_key'=>$GLOBALS['key'],
	'data'=>array(
		'absolute' => 'true',
		'type'=>'reverse',
		'item'=>$item
	)
);

// Send data to Geckoboard
sendIt($newreq, 'WIDGET-KEY-HERE');
echo 'New Requests Done...<br/>';


/**** PIE CHART ****/
/*
* Use: Custom Chart > Pie Chart (NOT Highchart)
* Description: Generates a pie chart with slides for each user that has tickets assigned
*              also includes a slice for unassigned tickets
* Sample Screenshot: http://imgur.com/a/zMWxo#1
*/

$result = $hsapi->privateRequestSearch(array(
				'fOpen' => '1'
				));

// Read out the result from HelpSpot, load it into our own array
$people = array("Unassigned"=>0);
foreach($result['requests']['request'] as $req){
	if($req['xPersonAssignedTo'] == ''){
		$people["Unassigned"]++;
	}else{
		if(array_key_exists($req['xPersonAssignedTo'], $people)){
			$people[$req['xPersonAssignedTo']]++;
		}else{
			$people[$req['xPersonAssignedTo']] = 1;
		}
	}
}

// Read our array and convert it into what need for Geckoboard API JSON
$item = array();
foreach($people as $person => $val){
	// generates a static color code for each user. "random" but will never change
	$color = preg_replace("/[^A-F0-9]/i", '', strtoupper(sha1($person)));
	$color = substr($color,5,5);
	$color = $color.'4';
	$item[] = array('value'=>$val,'label'=>$person.' - '.$val,'colour'=>'999999,729bd9,83d17a,e4636a');
}

$pie = array(
	'api_key'=>$GLOBALS['key'],
	'data'=>array(
		'item'=>$item
	)
);

// Push to Geckoboard API
sendIt($pie, 'WIDGET-KEY-HERE');
echo 'Pie Done...<br/>';

/**** Billable Hours 7 Days ****
*
*
*
*/
$timetrack = $hsapi->privateTimetrackerSearch(array(
				'start_time' => time()-(60*60*24*7),
				));			
$outp = 0;
foreach ($timetrack as $event) {
  	foreach ($event as $keyid=>$timeid) {
		foreach ($timeid as $arkey=>$arvals) {
				$outp = $outp + $arvals['iSeconds'];
		}
    }
}

$timetrack2 = $hsapi->privateTimetrackerSearch(array(
				'start_time' => time()-(60*60*24*14),
				));			
$outp2 = 0;
foreach ($timetrack2 as $event2) {
  	foreach ($event2 as $keyid2=>$timeid2) {
		foreach ($timeid2 as $arkey2=>$arvals2) {
				$outp2 = $outp2 + $arvals2['iSeconds'];
		}
    }
}


$itema[] = array('value' => round(($outp/60)/60,2), 'text' => "This week");
$itemb[] = array('value' => round(($outp2/60)/60,2), 'text' => "Last week");

$item = array_merge($itema, $itemb);
// Format data into Geckoboard JSON
$billable = array(
	'api_key'=>$GLOBALS['key'],
	'data'=>array(
		'item'=>$item
	)
);

// Send data to Geckoboard
sendIt($billable, 'WIDGET-KEY-HERE');
echo 'Billable Hours 7 Done...<br/>';

/**** Billable Hours 30 Days ****
*
*
*
*/
$timetrack4 = $hsapi->privateTimetrackerSearch(array(
				'start_time' => time()-(60*60*24*30),
				));			
$outp4 = 0;
foreach ($timetrack4 as $event4) {
  	foreach ($event4 as $keyid4=>$timeid4) {
		foreach ($timeid4 as $arkey4=>$arvals4) {
				$outp4 = $outp4 + $arvals4['iSeconds'];
		}
    }
}

$timetrack5 = $hsapi->privateTimetrackerSearch(array(
				'start_time' => time()-(60*60*24*60),
				));			
$outp5 = 0;
foreach ($timetrack5 as $event5) {
  	foreach ($event5 as $keyid5=>$timeid5) {
		foreach ($timeid5 as $arkey5=>$arvals5) {
				$outp5 = $outp5 + $arvals5['iSeconds'];
		}
    }
}


$item4[] = array('value' => round(($outp4/60)/60,2), 'text' => "This week");
$item5[] = array('value' => round(($outp5/60)/60,2), 'text' => "Last week");

$item = array_merge($item4, $item5);
// Format data into Geckoboard JSON
$billable = array(
	'api_key'=>$GLOBALS['key'],
	'data'=>array(
		'item'=>$item
	)
);

// Send data to Geckoboard
sendIt($billable, 'WIDGET-KEY-HERE');
echo 'Billable Hours 30 Done...<br/>';


/**** FUNNEL CHART ****/
/*
* Use: Custom Chart > Funnel Chart (NOT Highchart)
* Description: Generates a funnel chart to show how old all currently open tickets are.
* Sample Screenshot: http://imgur.com/a/zMWxo#3
*/

// Define what relative times we want to check, and what they should be called
$itemfun = array();
$doIt = array(
	"today"=>"Opened Today",
	"yesterday"=>"Opened Yesterday",
	"past_7"=>"Opened in last 7 Days",
	"past_30"=>"Opened in last 30 Days",
	"past_90"=>"Opened in last 90 Days",
	"past_365"=>"Opened in last 365 Days",
	);

foreach($doIt as $date => $val){
	//Step through the list and make the requests seperately - inefficient but works
	$result = $hsapi->privateRequestSearch(array(
					'fOpen' => 1,
					'relativedate' => $date,
					));
	$itemfun[] = array('value' => count($result['requests']['request']), 'label' => "$val");
}

// Format data into Geckoboard JSON
$funnel = array(
	'api_key'=>$GLOBALS['key'],
	'data'=>array(
		'type'=>'reverse',
		'percentage'=> 'hide',
		'item'=>$itemfun
	)
);

// Send data to Geckoboard
sendIt($funnel, 'WIDGET-KEY-HERE');

echo 'Funnel Done...<br/>';

/**** LINE CHART OPEN ****/
/*
* Use: Custom Chart > Line Chart (NOT Highchart)
* Description: Generates a line chart of tickets opened over time,
		similar to HelpSpots "Requests Over Time"
* Sample Screenshot: http://imgur.com/a/zMWxo#2
*/

$dates = array();
$result = $hsapi->privateRequestSearch(array(
				'relativedate' => 'past_14'
				));

// Read the HelpSpot request and count up the tickets/day
foreach($result['requests']['request'] as $req){
	if(array_key_exists($req['dtGMTOpened'], $dates)){
		$dates[$req['dtGMTOpened']]++;
	}else{
		$dates[$req['dtGMTOpened']] = 1;
	}
}

$dates = array_reverse($dates);
$item = array();
$axisx = array();
$last = end(array_keys($dates));
$min = 9999;
$max = 0;
$s = 1;
foreach($dates as $day => $num){
	//Iterate through our collected data and re-format it
	$item[] = $num;
	if($s == 1 || $s == round(count($dates)/2,0) || $day == $last){
		$axisx[] = $day;
	}
	if($num < $min){ $min = $num; }
	if($num > $max){ $max = $num; }
	$s++;
}

// Final formatting of data for Geckboard JSON, add a few extra pieces
$line = array(
	'api_key'=>$GLOBALS['key'],
	'data'=>array(
		'item'=>$item,
		'settings'=>array(
			'axisx'=>$axisx,
			'axisy'=>array(
				'Min '.$min,
				'Max '.$max,
			),
			'colour'=>'FFFF00',
		),
	),
);

// Send data to Geckboard
sendIt($line, 'WIDGET-KEY-HERE');
echo 'Line Open Done...<br/>';


/**** LINE CHART CLOSED ****/
/*
* Use: Custom Chart > Line Chart (NOT Highchart)
* Description: Generates a line chart of tickets closed over time,
		similar to HelpSpots "Requests Over Time" (based on close date).
		Warning: Will never report "0" closed days.
* Sample Screenshot: http://imgur.com/a/zMWxo#0
*/
unset($dates); //yay for variable name re-use
$dates = array();
$result = $hsapi->privateRequestSearch(array(
				'closedAfterDate' => time()-(60*60*24*14), //Finds all requests closed in the last 14 days
				));

foreach($result['requests']['request'] as $req){
	// Iterate through the HelpSpot results, count em up
	if(array_key_exists($req['dtGMTClosed'], $dates)){
		$dates[$req['dtGMTClosed']]++;
	}else{
		$dates[$req['dtGMTClosed']] = 1;
	}
}

ksort($dates); //Data comes through out of order, sort it
$item = array();
$axisx = array();
$last = end(array_keys($dates));
$min = 9999;
$max = 0;
$s = 1;
foreach($dates as $day => $num){
	//Iterate through our collected data and re-format it
	$item[] = $num;
	if($s == 1 || $s == round(count($dates)/2,0) || $day == $last){
		$axisx[] = $day;
	}
	if($num < $min){ $min = $num; }
	if($num > $max){ $max = $num; }
	$s++;
}

// Final formatting of data for Geckboard JSON, add a few extra pieces
$line2 = array(
	'api_key'=>$GLOBALS['key'],
	'data'=>array(
		'item'=>$item,
		'settings'=>array(
			'axisx'=>$axisx,
			'axisy'=>array(
				'Min '.$min,
				'Max '.$max,
			),
			'colour'=>'00FFFF',
		),
	),
);

// Send it to Geckboard
sendIt($line2, 'WIDGET-KEY-HERE');
echo 'Line Done Done...<br/>';

function sendIt($array, $id){
	//Generate a new Gruzzle request, JSON encode our arrays, send data
	$client = new Client();
	$request = $client->post($GLOBALS['base'].$id, null, json_encode($array))->send();
}

echo 'Done!';
