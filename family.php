<?php
// Parses gedcom file and displays information about a family.
//
// You must supply a $famid value with the identifier for the family.
//
// webtrees: Web based Family History software
// Copyright (C) 2011 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2010  PGV Development Team.  All rights reserved.
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
// $Id: family.php 11785 2011-06-11 22:08:12Z greg $

define('WT_SCRIPT_NAME', 'family.php');
require './includes/session.php';

$controller = new WT_Controller_Family();
$controller->init();

if ($controller->family && $controller->family->canDisplayName()) {
	print_header($controller->getPageTitle());
	if ($controller->family->isMarkedDeleted()) {
		if (WT_USER_CAN_ACCEPT) {
			echo '<p class="ui-state-highlight">', WT_I18N::translate('This family has been deleted.  You should review the deletion and then <a href="%1$s">accept</a> or <a href="%2$s">reject</a> it.', $controller->family->getHtmlUrl().'&amp;action=accept', $controller->family->getHtmlUrl().'&amp;action=undo'), '</p>';
		} elseif (WT_USER_CAN_EDIT) {
			echo '<p class="ui-state-highlight">', WT_I18N::translate('This family has been deleted.  The deletion will need to be reviewed by a moderator.'), '</p>';
		}
	} elseif (find_updated_record($controller->family->getXref(), WT_GED_ID)!==null) {
		if (WT_USER_CAN_ACCEPT) {
			echo '<p class="ui-state-highlight">', WT_I18N::translate('This family has been edited.  You should review the changes and then <a href="%1$s">accept</a> or <a href="%2$s">reject</a> them.', $controller->family->getHtmlUrl().'&amp;action=accept', $controller->family->getHtmlUrl().'&amp;action=undo'), '</p>';
		} elseif (WT_USER_CAN_EDIT) {
			echo '<p class="ui-state-highlight">', WT_I18N::translate('This family has been edited.  The changes need to be reviewed by a moderator.'), '</p>';
		}
	} elseif ($controller->accept_success) {
		echo '<p class="ui-state-highlight">', WT_I18N::translate('The changes have been accepted.'), '</p>';
	} elseif ($controller->reject_success) {
		echo '<p class="ui-state-highlight">', WT_I18N::translate('The changes have been rejected.'), '</p>';
	}
} else {
	print_header(WT_I18N::translate('Family'));
	echo '<p class="ui-state-error">', WT_I18N::translate('This family does not exist or you do not have permission to view it.'), '</p>';
	print_footer();
	exit;
}

// We have finished writing session data, so release the lock
Zend_Session::writeClose();

if (WT_USE_LIGHTBOX) {
	require_once WT_ROOT.WT_MODULES_DIR.'lightbox/functions/lb_call_js.php';
}

$PEDIGREE_FULL_DETAILS = "1"; // Override GEDCOM configuration
$show_full = "1";

?>
<script type="text/javascript">
<!--
	function show_gedcom_record(shownew) {
		fromfile="";
		if (shownew=="yes") fromfile='&fromfile=1';
		var recwin = window.open("gedrecord.php?pid=<?php echo $controller->getFamilyID(); ?>"+fromfile, "_blank", "top=50, left=50, width=600, height=400, scrollbars=1, scrollable=1, resizable=1");
	}
	function showchanges() {
		window.location = '<?php echo $controller->family->getRawUrl(); ?>';
	}
//-->
</script>
<table align="center" width="95%">
	<tr>
		<td>
			<p class="name_head"><?php echo $controller->family->getFullName(); ?></p>
		</td>
	</tr>
</table>
<table align="center" width="95%">
	<tr valign="top">
		<td valign="top" style="width: <?php echo $pbwidth+30; ?>px;"><!--//List of children//-->
			<?php print_family_children($controller->getFamilyID()); ?>
		</td>
		<td> <!--//parents pedigree chart and Family Details//-->
			<table width="100%">
				<tr>
					<td class="subheaders" valign="top"><?php echo WT_I18N::translate('Parents'); ?></td>
					<td class="subheaders" valign="top"><?php echo WT_I18N::translate('Grandparents'); ?></td>
				</tr>
				<tr>
					<td colspan="2">
						<table><tr><td> <!--//parents pedigree chart //-->
						<?php
						echo print_family_parents($controller->getFamilyID());
						if (WT_USER_CAN_EDIT) {
							if ($controller->difffam) {
								$husb=$controller->difffam->getHusband();
							} else {
								$husb=$controller->family->getHusband();
							}
							if (!$husb) {
								echo '<a href="javascript: ', WT_I18N::translate('Add a new father'), '" onclick="return addnewparentfamily(\'\', \'HUSB\', \'', $controller->famid, '\');">', WT_I18N::translate('Add a new father'), help_link('edit_add_parent'), '</a><br />';
							}
							if ($controller->difffam) {
								$wife=$controller->difffam->getWife();
							} else {
								$wife=$controller->family->getWife();
							}
							if (!$wife)  {
								echo '<a href="javascript: ', WT_I18N::translate('Add a new mother'), '" onclick="return addnewparentfamily(\'\', \'WIFE\', \'', $controller->famid, '\');">', WT_I18N::translate('Add a new mother'), help_link('edit_add_parent'), '</a><br />';
							}
						}
						?>
						</td></tr></table>
					</td>
				</tr>
				<tr>
					<td colspan="2">
						<br /><hr />
						<?php print_family_facts($controller->family); ?>
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
<br />
<?php
print_footer();
