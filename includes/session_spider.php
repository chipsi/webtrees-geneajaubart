<?php
// Startup and session logic for handling Bots and Spiders
//
// webtrees: Web based Family History software
// Copyright (C) 2012 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2008 to 2009  PGV Development Team.  All rights reserved.
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//
// $Id: session_spider.php 13431 2012-02-11 18:14:19Z greg $

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

// Block sites by IP address.
// Convert user-friendly such as '123.45.*.*' into SQL '%' wildcards.
// Note: you may need to blcok IPv6 addresses as well as IPv4 ones.
try {
	$banned_ip=WT_DB::prepare(
		"SELECT ip_address, comment FROM `##ip_address`".
		" WHERE category='banned' AND ? LIKE REPLACE(ip_address, '*', '%') LIMIT 1"
	)->execute(array($_SERVER['REMOTE_ADDR']))->fetchOneRow();
	if ($banned_ip) {
		$log_msg='session_spider.php blocked IP Address: '.$_SERVER['REMOTE_ADDR'].' by regex: '.$banned_ip->ip_address;
		if ($banned_ip->comment) {
			$log_msg.=' ('.$banned_ip->comment.')';
		}
		AddToLog($log_msg, 'auth');
		header('HTTP/1.1 403 Access Denied');
		exit;
	}
} catch (PDOException $ex) {
	// Initial installation?  Site Down?  Fail silently.
}

// Search Engines are treated special, and receive only core data, without the
// pretty bells and whistles.  Recursion is also going to be kept to a minimum.
// Max uncompressed page output has to be under 100k.  Spiders do not index the
// rest of the file.

global $SEARCH_SPIDER;
$SEARCH_SPIDER = false; // set empty at start

$ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : "";

$worms = array(
	'Gigabot',    // http://www.gigablast.com/spider.html           - Ignores robots.txt
	'Sogou',      // http://www.sogou.com/docs/help/webmasters.htm  - Ignores robots.txt
	'facebook',   // http://www.facebook.com/externalhit_uatext.php - Ignores robots.txt
	'SeznamBot',  // http://fulltext.sblog.cz - Fetches robots.txt, but then ignores it!
	'oBot',
	'Indy Library',
	'XXX',
	'Super_Ale',
	'Wget',
	'DataCha',
	'libwww-perl',
	'LWP::Simple',
	'lwp-trivial',
	'MJ.*bot',
	'DotBot',
	'HTTrack',
	'AISearchBot',
	'panscient.com',
	'Plonebot',
	'Mozilla[^\/]',  // legitimate Mozilla-based browsers have something like "Mozilla/5.0"
	'Mozilla\/[^456]', // legitimate Mozilla-based browsers have something like "Mozilla/5.0"
	'^Mozilla\/[456]\.0$', // legitimate Mozilla-based browsers have something following "Mozilla/5.0"
	'Speedy.*Spider',
	'KaloogaBot',  // Image search engines have no business searching a Genealogy site
	'DBLBot',
	'TurnitinBot',  // Plagiarism detectors have no business searching a Genealogy site
	'(Microsoft)|(Internet)|(Explorer)'  // Internet Explorer self-identifies with "MSIE"
	);

$quitReason = "";

// check for worms and bad bots
if ($quitReason == "") {
	foreach ($worms as $worm) {
		if (preg_match('/'.$worm.'/', $ua)) {
			$quitReason = "Blocked crawler detected";
			break;
		}
	}
}

// Do we have a reason to quit now?
if ($quitReason != "") {
	if ((!ini_get('register_globals'))||(strtolower(ini_get('register_globals'))=="off")) {
		//-- load common functions
		require_once WT_ROOT.'includes/functions/functions.php';
		//-- load db specific functions
		require_once WT_ROOT.'includes/functions/functions_db.php';
		require_once WT_ROOT.'includes/authentication.php';      // -- load the authentication system
		AddToLog("MSG>{$quitReason}; script terminated. UA>{$ua}< >{$_SERVER["REQUEST_URI"]}<", 'auth');
	}
	header("HTTP/1.0 403 Forbidden");
	echo "Hackers are not welcome here.";
	exit;
}


// The search list has been reversed.  Whitelist all browsers, and
// mark everything else as a spider/bot.
$real_browsers = array(
	'MSIE ',
	'Opera',
	'Firefox',
	'Konqueror',
	'Gecko',
	'Safari',
	'http://www.avantbrowser.com',
	'BlackBerry',
	'Lynx',
	'MSFrontPage',
	'RssReader',
	'Liferea/',
	'W3C_Validator',
	'TotalValidator',
	);

// Here we list the search engines whose accesses we don't need to log.
// This avoids cluttering the log files with useless entries
$known_spiders = array(
	'Googlebot',
	'Yahoo Slurp',
	'YahooCacheSystem',
	'msnbot',
	'bingbot',
	'Ask Jeeves',
	'Mediapartners-Google',
	'Feedfetcher-Google',
	'Twiceler',
	'Netcraft Web Server Survey', // Long-established, respectable survey tool
	'ia_archive',                 // http://www.archive.org/
	'YandexBot',                  // Popular Russian/cyrillic search engine
	'Baiduspider',                // Popular Chinese search engine
);

// We overlay the following name with carefully selected characters.
// This is to avoid XSS problems.  Alpha : . / - _ only.  Yes, the following string is 72 chars.
$spider_name = '                                                                        ';

// If you want to disable spider detection, set real to true here.
$real = false;

if ($ua != "") {
	foreach ($real_browsers as $browser_check) {
		if (strpos($ua, $browser_check)!==false) {
			$real = true;
			break;
		}
	}
	// check for old Netscapes.
	if (strpos($ua, "Mozilla")!==false) {
		if (strpos($ua, "compatible")===false) {
			if (preg_match("/\[..\]/i", $ua)!==false) {
				$real = true;
			}
			if (strpos($ua, "Macintosh")!==false) {
				$real = true;
			}
		}
	}
} else {
	// For the people who firewall identifying information
	// Switch real to false if you wish to restrict these connections.
	$ua = "Browser User Agent Empty";
	$real = true;
}

if (!$real) {
	$SEARCH_SPIDER = $ua;
}

// Manual Search Engine IP Address tagging
//   Allow an admin to mark IP addresses as known search engines even if
//   they are not automatically detected above.   Setting his own IP address
//   in the ip_address table allows him to see exactly what the search engine receives.
//   To return to normal, the admin MUST use a different IP to get to admin
//   mode or update the table wt_ip_address directly.
try {
	$search_engine=WT_DB::prepare(
		"SELECT ip_address, comment FROM `##ip_address`".
		" WHERE category='search-engine' AND ? LIKE REPLACE(ip_address, '*', '%') LIMIT 1"
	)->execute(array($_SERVER['REMOTE_ADDR']))->fetchOneRow();
	if ($search_engine) {
		if (empty($SEARCH_SPIDER)) {
			if ($search_engine->comment) {
				$SEARCH_SPIDER = 'Manual Search Engine entry of '.$_SERVER['REMOTE_ADDR'].' ('.$search_engine->comment.')';
			} else {
				$SEARCH_SPIDER = 'Manual Search Engine entry of '.$_SERVER['REMOTE_ADDR'];
			}
		}
	}
} catch (PDOException $ex) {
	// Initial installation?  Site Down?  Fail silently.
}

if (!empty($SEARCH_SPIDER)) {
	$spidertime = time();
	$spiderdate = date("d.m.Y", $spidertime);
	// Do we need to log this spider access?
	$outstr = preg_replace('/\s+/', ' ', $SEARCH_SPIDER);  // convert tabs etc. to blanks; trim extra blanks
	$outstr = str_replace(' - ', ' ', $outstr);            // Don't allow ' - ' because that is the log separator
	$logSpider = true;
	foreach ($known_spiders as $spider) {
		if (strpos($outstr, $spider) !== false) {
			$logSpider = false;
			break;
		}
	}
	if (isset($_SESSION['spider_count']))
	$spidercount = $_SESSION['spider_count'] + 1;
	else {
		$spidercount = 1;
		if ($logSpider) {
			//adds a message to the log that a new spider session is starting
			require_once WT_ROOT.'includes/authentication.php';      // -- Loaded early so AddToLog works
			AddToLog("New search engine encountered: ->".$outstr."<- UA>{$ua}< >{$_SERVER["REQUEST_URI"]}<", 'auth');
		}
	}
	if (isset($_SESSION['last_spider_date'])) {
		if ($spiderdate != $_SESSION['last_spider_date']) {
			//adds a message to the log that a new spider session is starting
			if ($logSpider) {
				require_once WT_ROOT.'includes/authentication.php';      // -- Loaded early so AddToLog works
				AddToLog("Returning search engine last seen ".$_SESSION['spider_count']." times on ".$_SESSION['last_spider_date']." from ".$_SESSION['last_spider_ip']." ->".$outstr."<-", 'auth');
			}
			$_SESSION['last_spider_date'] = $spiderdate;
			$spidercount = 1;
		}
	}
	$_SESSION['last_spider_date'] = $spiderdate;
	$_SESSION['spider_count'] = $spidercount;
	if (isset($_SERVER['REMOTE_ADDR']))
	$_SESSION['last_spider_ip'] = $_SERVER['REMOTE_ADDR'];
	$_SESSION['last_spider_name'] = $SEARCH_SPIDER;
	if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))
	$_SESSION['last_spider_lang'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];

	$_SESSION['wt_user'] = ""; // Don't allow search engine into user/admin mode.
}
