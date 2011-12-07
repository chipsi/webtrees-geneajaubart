<?php
// Media View Page
//
// This page displays all information about media that is selected in PHPGedView.
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
// $Id: mediaviewer.php 12900 2011-11-24 02:16:25Z nigel $

define('WT_SCRIPT_NAME', 'mediaviewer.php');
require './includes/session.php';
require_once WT_ROOT.'includes/functions/functions_print_lists.php';

$controller=new WT_Controller_Media();

if ($controller->record && $controller->record->canDisplayDetails()) {
	$controller->pageHeader();
	if ($controller->record->isMarkedDeleted()) {
		if (WT_USER_CAN_ACCEPT) {
			echo
				'<p class="ui-state-highlight">',
				/* I18N: %1$s is "accept", %2$s is "reject".  These are links. */ WT_I18N::translate(
					'This media object has been deleted.  You should review the deletion and then %1$s or %2$s it.',
					'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'accept-changes\',xref:\''.$controller->record->getXref().'\'},function(){location.reload();})">' . WT_I18N::translate_c('You should review the deletion and then accept or reject it.', 'accept') . '</a>',
					'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'reject-changes\',xref:\''.$controller->record->getXref().'\'},function(){location.reload();})">' . WT_I18N::translate_c('You should review the deletion and then accept or reject it.', 'reject') . '</a>'
				),
				' ', help_link('pending_changes'),
				'</p>';
		} elseif (WT_USER_CAN_EDIT) {
			echo
				'<p class="ui-state-highlight">',
				WT_I18N::translate('This media object has been deleted.  The deletion will need to be reviewed by a moderator.'),
				' ', help_link('pending_changes'),
				'</p>';
		}
	} elseif (find_updated_record($controller->record->getXref(), WT_GED_ID)!==null) {
		if (WT_USER_CAN_ACCEPT) {
			echo
				'<p class="ui-state-highlight">',
				/* I18N: %1$s is "accept", %2$s is "reject".  These are links. */ WT_I18N::translate(
					'This media object has been edited.  You should review the changes and then %1$s or %2$s them.',
					'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'accept-changes\',xref:\''.$controller->record->getXref().'\'},function(){location.reload();})">' . WT_I18N::translate_c('You should review the changes and then accept or reject them.', 'accept') . '</a>',
					'<a href="#" onclick="jQuery.post(\'action.php\',{action:\'reject-changes\',xref:\''.$controller->record->getXref().'\'},function(){location.reload();})">' . WT_I18N::translate_c('You should review the changes and then accept or reject them.', 'reject') . '</a>'
				),
				' ', help_link('pending_changes'),
				'</p>';
		} elseif (WT_USER_CAN_EDIT) {
			echo
				'<p class="ui-state-highlight">',
				WT_I18N::translate('This media object has been edited.  The changes need to be reviewed by a moderator.'),
				' ', help_link('pending_changes'),
				'</p>';
		}
	}
} else {
	header($_SERVER['SERVER_PROTOCOL'].' 403 Forbidden');
	$controller->pageHeader();
	echo '<p class="ui-state-error">', WT_I18N::translate('This media object does not exist or you do not have permission to view it.'), '</p>';
	exit;
}

if (WT_USE_LIGHTBOX) {
	require WT_ROOT.WT_MODULES_DIR.'lightbox/functions/lb_call_js.php';
}

echo WT_JS_START;
echo 'function show_gedcom_record() {';
echo ' var recwin=window.open("gedrecord.php?pid=', $controller->record->getXref(), '", "_blank", "top=0, left=0, width=600, height=400, scrollbars=1, scrollable=1, resizable=1");';
echo '}';
echo 'function showchanges() { window.location="'.$controller->record->getRawUrl().'"; }';
?>	jQuery(document).ready(function() {
		jQuery("#media-tabs").tabs();
		jQuery("#media-tabs").css('visibility', 'visible');
	});
<?php
echo WT_JS_END;


/* Note:
 *  if $controller->getLocalFilename() is not set, then an invalid MID was passed in
 *  if $controller->m_pid is not set, then a filename was passed in that is not in the gedcom
 */
$filename = $controller->getLocalFilename();
global $tmb;
echo '<div id="media-details">';
echo '<h2>', $controller->record->getFullName(), ' ', $controller->record->getAddName(), '</h2>';
echo '<div id="media-tabs">';
	// Media Object details ---------------------
	echo '<div id="media-edit">';
		echo '<table class="facts_table">
			<tr>
				<td align="center" width="150">';
					// display image
					if ($controller->record->canDisplayDetails()) {
						echo $controller->record->displayMedia(array('download'=>true, 'align'=>'none', 'alertnotfound'=>true));
					}
				echo '</td>
				<td valign="top">
					<table width="100%">
						<tr>
							<td>
								<table class="facts_table">';
										$facts = $controller->getFacts(WT_USER_CAN_EDIT || WT_USER_CAN_ACCEPT);
										foreach ($facts as $f=>$fact) {
											print_fact($fact, $controller->record);
										}
								echo '</table>
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
	</div>'; // close "media-edit"
	echo '<ul>';
		if ($controller->record->countLinkedIndividuals()) {
			echo '<li><a href="#indi-media"><span id="indimedia">', WT_I18N::translate('Individuals'), '</span></a></li>';
		}
		if ($controller->record->countLinkedFamilies()) {
			echo '<li><a href="#fam-media"><span id="fammedia">', WT_I18N::translate('Families'), '</span></a></li>';
		}
		if ($controller->record->countLinkedSources()) {
			echo '<li><a href="#sources-media"><span id="sourcemedia">', WT_I18N::translate('Sources'), '</span></a></li>';
		}
		if ($controller->record->countLinkedRepositories()) {
			echo '<li><a href="#repo-media"><span id="repomedia">', WT_I18N::translate('Repositories'), '</span></a></li>';
		}
		if ($controller->record->countLinkedNotes()) {
			echo '<li><a href="#notes-media"><span id="notemedia">', WT_I18N::translate('Notes'), '</span></a></li>';
		}
	echo '</ul>';

	// Individuals linked to this media object
	if ($controller->record->countLinkedIndividuals()) {
		echo '<div id="indi-media">';
		echo format_indi_table($controller->record->fetchLinkedIndividuals(), $controller->record->getFullName());
		echo '</div>'; //close "indi-media"
	}

	// Families linked to this media object
	if ($controller->record->countLinkedFamilies()) {
		echo '<div id="fam-media">';
		echo format_fam_table($controller->record->fetchLinkedFamilies(), $controller->record->getFullName());
		echo '</div>'; //close "fam-media"
	}

	// Sources linked to this media object
	if ($controller->record->countLinkedSources()) {
		echo '<div id="sources-media">';
		echo format_sour_table($controller->record->fetchLinkedSources(), $controller->record->getFullName());
		echo '</div>'; //close "source-media"
	}

	// Repositories linked to this media object
	if ($controller->record->countLinkedRepositories()) {
		echo '<div id="repo-media">';
		echo format_repo_table($controller->record->fetchLinkedRepositories(), $controller->record->getFullName());
		echo '</div>'; //close "repo-media"
	}

	// medias linked to this media object
	if ($controller->record->countLinkedNotes()) {
		echo '<div id="notes-media">';
		echo format_note_table($controller->record->fetchLinkedNotes(), $controller->record->getFullName());
		echo '</div>'; //close "notes-media"
	}
echo '</div>'; //close div "media-tabs"
echo '</div>'; //close div "media-details"


?>
<script type="text/javascript">
<!--

function ilinkitem(mediaid, type) {
	window.open('inverselink.php?mediaid='+mediaid+'&linkto='+type+'&'+sessionname+'='+sessionid, '_blank', 'top=50, left=50, width=570, height=630, resizable=1, scrollbars=1');
	return false;
}
//-->
</script>
