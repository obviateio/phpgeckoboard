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


// Setup a connection
$hsapi = new HelpSpotAPI(array(
				"helpSpotApiURL" => $GLOBALS['hs']."api/index.php",
				'username' => $GLOBALS['hs-user'],
				'password' => $GLOBALS['hs-pass'] ));



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
	$color = substr($color,2,4);
	$color = "FF".$color;
	$item[] = array('value'=>$val,'label'=>$person.' - '.$val,'colour'=>$color);
}

$pie = array(
	'api_key'=>$GLOBALS['key'],
	'data'=>array(
		'item'=>$item
	)
);

// Push to Geckoboard API
sendIt($pie, 'WIDGET-KEY-HERE');



/**** FUNNEL CHART ****/
/*
* Use: Custom Chart > Funnel Chart (NOT Highchart)
* Description: Generates a funnel chart to show how old all currently open tickets are.
* Sample Screenshot: http://imgur.com/a/zMWxo#3
*/

// Define what relative times we want to check, and what they should be called
$item = array();
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
	$item[] = array('value' => count($result['requests']['request']), 'label' => $val);
}

// Format data into Geckoboard JSON
$funnel = array(
	'api_key'=>$GLOBALS['key'],
	'data'=>array(
		'type'=>'reverse',
		'item'=>$item
	)
);

// Send data to Geckoboard
sendIt($funnel, 'WIDGET-KEY-HERE');


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

function sendIt($array, $id){
	//Generate a new Gruzzle request, JSON encode our arrays, send data
	$client = new Client();
	$request = $client->post($GLOBALS['base'].$id, null, json_encode($array))->send();
}

