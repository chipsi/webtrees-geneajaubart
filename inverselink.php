<?php
// Link media items to indi, sour and fam records
//
// This is the page that does the work of linking items.
//
// webtrees: Web based Family History software
// Copyright (C) 2011 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2009  PGV Development Team.  All rights reserved.
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
// $Id: inverselink.php 11704 2011-06-04 09:09:12Z greg $

define('WT_SCRIPT_NAME', 'inverselink.php');
require './includes/session.php';
require WT_ROOT.'includes/functions/functions_edit.php';

//-- page parameters and checking
$linktoid = safe_GET_xref('linktoid');
$mediaid  = safe_GET_xref('mediaid');
$linkto   = safe_GET     ('linkto', array('person', 'source', 'family', 'manage', 'repository', 'note'));
$action   = safe_GET     ('action', WT_REGEX_ALPHA, 'choose');

// If GedFAct_assistant/_MEDIA/ installed ======================
if (WT_USER_IS_ADMIN && $linkto=='manage' && file_exists(WT_ROOT.WT_MODULES_DIR.'GEDFact_assistant/_MEDIA/media_1_ctrl.php')) {
	require WT_ROOT.WT_MODULES_DIR.'GEDFact_assistant/_MEDIA/media_0_inverselink.php';
} else {

	if (empty($linktoid) || empty($linkto)) {
		$paramok = false;
		$toitems = "";
	} else {
		switch ($linkto) {
		case 'person':
			$toitems = WT_I18N::translate('To Person');
			break;
		case 'family':
			$toitems = WT_I18N::translate('To Family');
			break;
		case 'source':
			$toitems = WT_I18N::translate('To Source');
			break;
		case 'repository':
			$toitems = WT_I18N::translate('To Repository');
			break;
		case 'note':
			$toitems = WT_I18N::translate('To Shared Note');
			break;
		}
	}

	print_simple_header(WT_I18N::translate('Link media')." ".$toitems);

	if ($ENABLE_AUTOCOMPLETE) require WT_ROOT.'js/autocomplete.js.htm';

	//-- check for admin
	$paramok =  WT_USER_CAN_EDIT;
	if (!empty($linktoid)) $paramok = WT_GedcomRecord::getInstance($linktoid)->canDisplayDetails();

	if ($action == "choose" && $paramok) {
		?>
		<script type="text/javascript">
		<!--
		var pastefield;
		var language_filter, magnify;
		language_filter = "";
		magnify = "";

		function openerpasteid(id) {
			window.opener.paste_id(id);
			window.close();
		}

		function paste_id(value) {
			pastefield.value = value;
		}

		function paste_char(value, lang, mag) {
			pastefield.value += value;
			language_filter = lang;
			magnify = mag;
		}
		//-->
		</script>
	<script src="js/webtrees.js" type="text/javascript"></script>

		<?php
		echo '<form name="link" method="get" action="inverselink.php">';
		echo '<input type="hidden" name="action" value="update" />';
		if (!empty($mediaid)) {
			echo '<input type="hidden" name="mediaid" value="', $mediaid, '" />';
		}
		if (!empty($linktoid)) {
			echo '<input type="hidden" name="linktoid" value="', $linktoid, '" />';
		}
		echo '<input type="hidden" name="linkto" value="', $linkto, '" />';
		echo '<input type="hidden" name="ged" value="', $GEDCOM, '" />';
		echo '<table class="facts_table center ', $TEXT_DIRECTION, '">';
		echo '<tr><td class="topbottombar" colspan="2">';
		echo WT_I18N::translate('Link media'), help_link('add_media_linkid'), ' ', $toitems;
		echo '</td></tr><tr><td class="descriptionbox width20 wrap">', WT_I18N::translate('Media'), '</td>';
		echo '<td class="optionbox wrap">';
		if (!empty($mediaid)) {
			//-- Get the title of this existing Media item
			$title=
				WT_DB::prepare("SELECT m_titl FROM `##media` where m_media=? AND m_gedfile=?")
				->execute(array($mediaid, WT_GED_ID))
				->fetchOne();
			if ($title) {
				echo '<b>', PrintReady($title), '</b>';
			} else {
				echo '<b>', $mediaid, '</b>';
			}
		} else {
			echo '<input type="text" name="mediaid" id="mediaid" size="5" />';
			print_findmedia_link("mediaid", "1media");
			echo "</td></tr>";
		}

		if (!isset($linktoid)) $linktoid = "";
		echo '<tr><td class="descriptionbox">';

		if ($linkto == "person") {
			echo WT_I18N::translate('Individual'), "</td>";
			echo '<td class="optionbox wrap">';
			if ($linktoid=="") {
				echo '<input class="pedigree_form" type="text" name="linktoid" id="linktopid" size="3" value="', $linktoid, '" />';
				print_findindi_link("linktopid", "");
			} else {
				$record=WT_Person::getInstance($linktoid);
				echo PrintReady($record->format_list('span', false, $record->getFullName()));
			}
		}

		if ($linkto == "family") {
			echo WT_I18N::translate('Family'), '</td>';
			echo '<td class="optionbox wrap">';
			if ($linktoid=="") {
				echo '<input class="pedigree_form" type="text" name="linktoid" id="linktofamid" size="3" value="', $linktoid, '" />';
				print_findfamily_link("linktofamid");
			} else {
				$record=WT_Family::getInstance($linktoid);
				echo PrintReady($record->format_list('span', false, $record->getFullName()));
			}
		}

		if ($linkto == "source") {
			echo WT_I18N::translate('Source'), "</td>";
			echo '<td  class="optionbox wrap">';
			if ($linktoid=="") {
				echo '<input class="pedigree_form" type="text" name="linktoid" id="linktosid" size="3" value="', $linktoid, '" />';
				print_findsource_link("linktosid");
			} else {
				$record=WT_Source::getInstance($linktoid);
				echo PrintReady($record->format_list('span', false, $record->getFullName()));
			}
		}
		if ($linkto == "repository") {
			echo WT_I18N::translate('Repository'), "</td>";
			echo '<td  class="optionbox wrap">';
			if ($linktoid=="") {
				echo '<input class="pedigree_form" type="text" name="linktoid" id="linktorid" size="3" value="', $linktoid, '" />';
			} else {
				$record=WT_Repository::getInstance($linktoid);
				echo PrintReady($record->format_list('span', false, $record->getFullName()));
			}
		}

		if ($linkto == "note") {
			echo WT_I18N::translate('Shared note'), "</td>";
			echo '<td  class="optionbox wrap">';
			if ($linktoid=="") {
				echo '<input class="pedigree_form" type="text" name="linktoid" id="linktonid" size="3" value="', $linktoid, '" />';
			} else {
				$record=WT_Note::getInstance($linktoid);
				echo PrintReady($record->format_list('span', false, $record->getFullName()));
			}
		}

		echo '</td></tr>';
		echo '<tr><td class="topbottombar" colspan="2"><input type="submit" value="', WT_I18N::translate('Set link'), '" /></td></tr>';
		echo '</table>';
		echo '</form>';
		echo '<br/><br/><center><a href="javascript:;" onclick="if (window.opener.showchanges) window.opener.showchanges(); window.close();">', WT_I18N::translate('Close Window'), '</a><br /></center>';
		print_simple_footer();
	} elseif ($action == "update" && $paramok) {
		linkMedia($mediaid, $linktoid);
		echo '<br/><br/><center><a href="javascript:;" onclick="if (window.opener.showchanges) window.opener.showchanges(); window.close();">', WT_I18N::translate('Close Window'), '</a><br /></center>';
		print_simple_footer();
	} else {
		echo '<center>nothing to do<center>';
		echo '<br/><br/><center><a href="javascript:;" onclick="if (window.opener.showchanges) window.opener.showchanges(); window.close();">', WT_I18N::translate('Close Window'), '</a><br /></center>';
		print_simple_footer();
	}
}
