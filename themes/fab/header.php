<?php
// Header for FAB theme
//
// webtrees: Web based Family History software
// Copyright (C) 2012 webtrees development team.
//
// Derived from PhpGedView
// Modifications Copyright (c) 2010 Greg Roach
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
// $Id: header.php 14176 2012-08-16 10:48:52Z greg $

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

// Definitions to simplify logic on pages with right-to-left languages
if ($TEXT_DIRECTION=='ltr') {
	define ('WT_CSS_ALIGN',         'left');
	define ('WT_CSS_REVERSE_ALIGN', 'right');
} else {
	define ('WT_CSS_ALIGN',         'right');
	define ('WT_CSS_REVERSE_ALIGN', 'left');
}

echo
	'<!DOCTYPE html>',
	'<html ', WT_I18N::html_markup(), '>',
	'<head>',
	'<meta charset="UTF-8">',
	'<title>', htmlspecialchars($title), '</title>',
	header_links($META_DESCRIPTION, $META_ROBOTS, $META_GENERATOR, $LINK_CANONICAL),
	'<link rel="icon" href="', WT_THEME_URL, 'favicon.png" type="image/png">';
	
echo
	'<link type="text/css" rel="stylesheet" href="', WT_STATIC_URL, 'js/jquery/css/jquery-ui.custom.css">',
	'<link type="text/css" rel="stylesheet" href="', $stylesheet, '">';
	//PERSO Add extra style sheet for personal additions
	$extrastylesheet= str_replace('.css', '.extra.css', $stylesheet);
	echo '<link rel="stylesheet" type="text/css" href="', $extrastylesheet, '">';
	//END PERSO

switch ($BROWSERTYPE) {
//case 'chrome': uncomment when chrome.css file needs to be added, or add others as needed
case 'msie':
	echo '<link type="text/css" rel="stylesheet" href="', WT_THEME_URL, $BROWSERTYPE, '.css">';
	break;
}

// Additional css files required (Only if Lightbox installed)
if (WT_USE_LIGHTBOX) {
		echo '<link rel="stylesheet" type="text/css" href="', WT_STATIC_URL, WT_MODULES_DIR, 'lightbox/css/album_page.css" media="screen">';
}

echo
	'</head>',
	'<body id="body">';

if ($view!='simple') { // Use "simple" headers for popup windows
	echo '<div id="header" class="block">';
	echo '<div style="float:', WT_CSS_REVERSE_ALIGN, ';"><ul class="makeMenu">';
	if (WT_USER_ID) {
		echo '<li><a href="edituser.php">', getUserFullName(WT_USER_ID), '</a></li> <li>', logout_link(), '</li>';
		if (WT_USER_CAN_ACCEPT && exists_pending_change()) {
			echo ' <li><a href="#" onclick="window.open(\'edit_changes.php\',\'_blank\',chan_window_specs); return false;" style="color:red;">', WT_I18N::translate('Pending changes'), '</a></li>';
		}
	} else {
		echo '<li>', login_link(), '</li> ';
	}
	//PERSO Extend header
	echo '<li>';
	$hook_print_header = new WT_Perso_Hook('h_print_header');
	$hook_print_header->execute();
	echo '</li>';
	//END PERSO
	$menu=WT_MenuBar::getFavoritesMenu();
	if ($menu) {
		echo $menu->getMenuAsList();
	}
	$menu=WT_MenuBar::getLanguageMenu();
	if ($menu) {
		echo $menu->getMenuAsList();
	}
	$menu=WT_MenuBar::getThemeMenu();
	if ($menu) {
		echo $menu->getMenuAsList();
	}
	echo
		'<li><form style="display:inline;" action="search.php" method="post">',
		'<input type="hidden" name="action" value="general">',
		'<input type="hidden" name="topsearch" value="yes">',
		'<input type="search" name="query" size="20" placeholder="', WT_I18N::translate('Search'), '" dir="auto">',
		'</form></li>',
		'</ul></div>';
	$menu_items=array(
		WT_MenuBar::getGedcomMenu(),
		WT_MenuBar::getMyPageMenu(),
		WT_MenuBar::getChartsMenu(),
		WT_MenuBar::getListsMenu(),
		WT_MenuBar::getCalendarMenu(),
		WT_MenuBar::getReportsMenu(),
		WT_MenuBar::getSearchMenu(),
	);
	foreach (WT_MenuBar::getModuleMenus() as $menu) {
		$menu_items[]=$menu;
	}

	echo
		'<div style="float:', WT_CSS_ALIGN, '; clear:', WT_CSS_ALIGN, '; font-size:175%;" dir="auto">',
			htmlspecialchars($GEDCOM_TITLE),
		'</div>';

	// Print the menu bar
	echo '<div id="topMenu"><ul class="makeMenu">';
	foreach ($menu_items as $menu) {
		if ($menu) {
			echo $menu->getMenuAsList();
		}
	}
	unset($menu_items, $menu);
	echo '</ul></div>';
	// Display feedback from asynchronous actions
	echo '<div id="flash-messages">';
	foreach (Zend_Controller_Action_HelperBroker::getStaticHelper('FlashMessenger')->getMessages() as $message) {
		echo '<p class="ui-state-highlight">', $message, '</p>';
	}
	echo '</div>'; // <div id="flash_messages">
	echo '</div>'; // <div id="header">
}
echo $javascript, '<div id="content">';
