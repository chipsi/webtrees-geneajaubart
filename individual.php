<?php
// Individual Page
//
// Display all of the information about an individual
//
// webtrees: Web based Family History software
// Copyright (C) 2011 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2011  PGV Development Team.  All rights reserved.
//
// Sidebar controls courtesy of http://devheart.org/articles/jquery-collapsible-sidebar-layout/
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
// $Id: individual.php 12356 2011-10-21 15:03:42Z greg $
// @version: p_$Revision$ $Date$
// $HeadURL$

define('WT_SCRIPT_NAME', 'individual.php');
require './includes/session.php';


// -- array of GEDCOM elements that will be found but should not be displayed
$nonfacts = array('FAMS', 'FAMC', 'MAY', 'BLOB', 'CHIL', 'HUSB', 'WIFE', 'RFN', '_WT_OBJE_SORT', '');

$nonfamfacts = array(/*'NCHI',*/ 'UID', '');

$controller=new WT_Controller_Individual();
$controller->init();

if ($controller->indi && $controller->indi->canDisplayDetails()) {
	print_header($controller->getPageTitle());
	if ($controller->indi->isMarkedDeleted()) {
		if (WT_USER_CAN_ACCEPT) {
			echo
				'<p class="ui-state-highlight">',
				/* I18N: %1$s is "accept", %2$s is "reject".  These are links. */ WT_I18N::translate(
					'This individual has been deleted.  You should review the deletion and then %1$s or %2$s it.',
					'<a href="' . $controller->indi->getHtmlUrl() . '&amp;action=accept">' . WT_I18N::translate_c('You should review the deletion and then accept or reject it.', 'accept') . '</a>',
					'<a href="' . $controller->indi->getHtmlUrl() . '&amp;action=undo">' . WT_I18N::translate_c('You should review the deletion and then accept or reject it.', 'reject') . '</a>'
				),
				' ', help_link('pending_changes'),
				'</p>';
		} elseif (WT_USER_CAN_EDIT) {
			echo
				'<p class="ui-state-highlight">',
				WT_I18N::translate('This individual has been deleted.  The deletion will need to be reviewed by a moderator.'),
				' ', help_link('pending_changes'),
				'</p>';
		}
	} elseif (find_updated_record($controller->indi->getXref(), WT_GED_ID)!==null) {
		if (WT_USER_CAN_ACCEPT) {
			echo
				'<p class="ui-state-highlight">',
				/* I18N: %1$s is "accept", %2$s is "reject".  These are links. */ WT_I18N::translate(
					'This individual has been edited.  You should review the changes and then %1$s or %2$s them.',
					'<a href="' . $controller->indi->getHtmlUrl() . '&amp;action=accept">' . WT_I18N::translate_c('You should review the changes and then accept or reject them.', 'accept') . '</a>',
					'<a href="' . $controller->indi->getHtmlUrl() . '&amp;action=undo">' . WT_I18N::translate_c('You should review the changes and then accept or reject them.', 'reject') . '</a>'
				),
				' ', help_link('pending_changes'),
				'</p>';
		} elseif (WT_USER_CAN_EDIT) {
			echo
				'<p class="ui-state-highlight">',
				WT_I18N::translate('This individual has been edited.  The changes need to be reviewed by a moderator.'),
				' ', help_link('pending_changes'),
				'</p>';
		}
	}
} elseif ($controller->indi && $controller->indi->canDisplayName()) {
	// Just show the name.
	print_header($controller->getPageTitle());
	echo '<h2>', $controller->indi->getFullName(), '</h2>';
	echo '<p class="ui-state-highlight">', WT_I18N::translate('The details of this individual are private.'), '</p>';
	print_footer();
	exit;
} else {
	header('HTTP/1.0 403 Forbidden');
	print_header(WT_I18N::translate('Individual'));
	echo '<p class="ui-state-error">', WT_I18N::translate('This individual does not exist or you do not have permission to view it.'), '</p>';
	print_footer();
	exit;
}
// We have finished writing session data, so release the lock
Zend_Session::writeClose();
// tell tabs that use jquery that it is already loaded
define('WT_JQUERY_LOADED', 1);

$linkToID=$controller->pid; // -- Tell addmedia.php what to link to

echo WT_JS_START;
echo 'function show_gedcom_record() {';
echo ' var recwin=window.open("gedrecord.php?pid=', $controller->indi->getXref(), '", "_blank", "top=0, left=0, width=600, height=400, scrollbars=1, scrollable=1, resizable=1");';
echo '}';
echo 'function showchanges() { window.location="'.$controller->indi->getRawUrl().'"; }';

?>

jQuery('#main').addClass('use-sidebar'); // Show
jQuery('#main').removeClass('use-sidebar'); // Hide
jQuery('#main').toggleClass('use-sidebar'); // Toggle

var tabCache = new Array();

jQuery(document).ready(function() {
	jQuery('#tabs').tabs({ spinner: '<img src="<?php echo WT_STATIC_URL; ?>images/loading.gif" height="18" border="0" alt="" />' });
	jQuery("#tabs").tabs({ cache: true });
	var $tabs = jQuery('#tabs');
	jQuery('#tabs').bind('tabsshow', function(event, ui) {
		var selectedTab = ui.tab.name;
		tabCache[selectedTab] = true;
	<?php
	foreach ($controller->tabs as $tab) {
		echo $tab->getJSCallback()."\n";
	}
	?>
	});

	// sidebar settings 
	// Variables
	var objMain			= jQuery('#main');
	var objTabs			= jQuery('#indi_left');
	var objBar			= jQuery('#sidebar');
	var objSeparator	= jQuery('#separator');
	// Adjust header dimensions
	function adjHeader(){
		var indi_header_div = document.getElementById('indi_header').offsetWidth - 20;
		var indi_mainimage_div = document.getElementById('indi_mainimage').offsetWidth +20;
		var header_accordion_div = document.getElementById('header_accordion1');
		header_accordion_div.style.width = indi_header_div - indi_mainimage_div +'px';

		jQuery(window).bind("resize", function(){
			var indi_header_div = document.getElementById('indi_header').offsetWidth - 20;
			var indi_mainimage_div = document.getElementById('indi_mainimage').offsetWidth +20;
			var header_accordion_div = document.getElementById('header_accordion1');
			header_accordion_div.style.width = indi_header_div - indi_mainimage_div +'px';
		 });
	}
	// Show sidebar
	function showSidebar(){
		objMain.addClass('use-sidebar');
		objSeparator.css('height', objBar.outerHeight() + 'px');
		jQuery.cookie('sidebar-pref', 'use-sidebar', { expires: 30 });
	}
	// Hide sidebar
	function hideSidebar(){
		objMain.removeClass('use-sidebar');
		objSeparator.css('height', objTabs.outerHeight() + 'px');
		jQuery.cookie('sidebar-pref', null, { expires: 30 });
	}
	// Sidebar separator
	objSeparator.click(function(e){
		e.preventDefault();
		if ( objMain.hasClass('use-sidebar') ){
			hideSidebar();
			adjHeader();
		} else {
			showSidebar();
			adjHeader();
		}
	});

	// Load preference
	if ( jQuery.cookie('sidebar-pref') == null ){
		objMain.addClass('use-sidebar');
		objSeparator.css('height', objTabs.outerHeight() + 'px');
	} else {
		objSeparator.css('height', objBar.outerHeight() + 'px');
	}
	
	adjHeader();
	jQuery("#main").css('visibility', 'visible');
});
<?php
echo WT_JS_END;
// ===================================== header area

echo
	'<div id="main" class="use-sidebar sidebar-at-right" style="visibility:hidden;">', //overall page container
	'<div id="indi_left">',
	'<div id="indi_header">';
if ($controller->indi->canDisplayDetails()) {
	echo '<div id="indi_mainimage">'; // Display highlight image
	if ($controller->canShowHighlightedObject()) {
		echo $controller->getHighlightedObject();
	}
	echo '</div>'; // close #indi_mainimage
	$globalfacts=$controller->getGlobalFacts();
	echo '<div id="header_accordion1">'; // contain accordions for names
	echo '<h3 class="name_one ', $controller->getPersonStyle($controller->indi), '"><span>', $controller->indi->getFullName(), '</span>'; // First name accordion header
	$bdate=$controller->indi->getBirthDate();
	$ddate=$controller->indi->getDeathDate();
	echo '<span class="header_age">';
	if ($bdate->isOK() && !$controller->indi->isDead()) {
		// If living display age
		echo strip_tags(WT_Gedcom_Tag::getLabelValue('AGE', get_age_at_event(WT_Date::GetAgeGedcom($bdate), true)), '<span>');
	} elseif ($bdate->isOK() && $ddate->isOK()) {
		// If dead, show age at death
		echo strip_tags(WT_Gedcom_Tag::getLabelValue('AGE', get_age_at_event(WT_Date::GetAgeGedcom($bdate, $ddate), false)), '<span>');
	}
	echo '</span>';
	// Display summary birth/death info.
	echo '<span id="dates">', $controller->indi->getLifeSpan(), '</span>';
	//Display gender icon
	foreach ($globalfacts as $key=>$value) {
		$fact = $value->getTag();
		if ($fact=="SEX") $controller->print_sex_record($value);
	}
	//PERSO
	$dcontroller = new WT_Perso_Controller_Individual($controller);
	$dcontroller->print_extra_icons_header();
	//END PERSO
	echo '</h3>'; // close first name accordion header
	
	//Display name details
	foreach ($globalfacts as $key=>$value) {
		$fact = $value->getTag();
		if ($fact=="NAME") $controller->print_name_record($value);
	}

	echo
		'</div>', // close header_accordion1
		WT_JS_START,
		'jQuery("#header_accordion1").accordion({',
		' active: 0,',
		' icons: {"header": "ui-icon-triangle-1-s", "headerSelected": "ui-icon-triangle-1-n" },',
		' autoHeight: false,',
		' collapsible: true',
		'});',
		WT_JS_END; //accordion details
	//PERSO
	$dcontroller->print_extensions_header();
	//END PERSO
}
echo '</div>';// close #indi_header
// ===================================== main content tabs
foreach ($controller->tabs as $tab) {
	echo $tab->getPreLoadContent();
}
echo '<div id="tabs" >';
echo '<ul>';
foreach ($controller->tabs as $tab) {
	if ($tab->isGrayedOut()) {
		$greyed_out='rela';
	} else {
	$greyed_out='';
	}
	if ($tab->hasTabContent()) {
		if ($tab->canLoadAjax()) {
			// AJAX tabs load only when selected
			echo '<li class="'.$greyed_out.'"><a title="', $tab->getName(), '" href="',$controller->indi->getHtmlUrl(),'&amp;action=ajax&amp;module=', $tab->getName(), '">';
		} else {
			// Non-AJAX tabs load immediately
			echo '<li class="'.$greyed_out.'"><a title="', $tab->getName(), '" href="#', $tab->getName(), '">';
		}
		echo '<span title="', $tab->getTitle(), '">', $tab->getTitle(), '</span></a></li>';
	}
}
echo '</ul>';
foreach ($controller->tabs as $tab) {
	if ($tab->hasTabContent()) {
		if (!$tab->canLoadAjax()) {
			echo '<div id="', $tab->getName(), '">', $tab->getTabContent(), '</div>';
		}
	}
}
echo '</div>', // close #tabs
	'</div>';//close indi_left
// ===================================== sidebar area
echo '<div id="sidebar">'; // sidebar code
require './sidebar.php';
echo
	'</div>',  // close #sidebar
	'<a href="#" id="separator" title="', WT_I18N::translate('Click here to open or close the sidebar'), '"></a>',//clickable element to open/close sidebar
	'<div style="clear:both;">&nbsp;</div></div>', // close #main	
// =======================================footer and other items 
WT_JS_START,
'var catch_and_ignore; function paste_id(value) {catch_and_ignore = value;}',
'if (typeof toggleByClassName == "undefined") {',
'alert("webtrees.js: A javascript function is missing.  Please clear your Web browser cache");',
'}',
'jQuery("html, body").animate({scrollTop: jQuery("#header").offset().top});', // scroll the page to top
WT_JS_END;
print_footer();
