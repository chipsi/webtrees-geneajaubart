<?php
// Searches based on user query.
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
// $Id: search.php 12823 2011-11-20 01:53:14Z nigel $

define('WT_SCRIPT_NAME', 'search.php');
require './includes/session.php';
require_once WT_ROOT.'includes/functions/functions_print_lists.php';

$controller=new WT_Controller_Search();
$controller->pageHeader();

if ($ENABLE_AUTOCOMPLETE) require WT_ROOT.'js/autocomplete.js.htm';
?>
<script type="text/javascript">
<!--
	function checknames(frm) {
		action = "<?php echo $controller->action; ?>";
		if (action == "general")
		{
			if (frm.query.value.length<2) {
				alert("<?php echo WT_I18N::translate('Please enter more than one character'); ?>");
				frm.query.focus();
				return false;
			}
		}
		else if (action == "soundex")
		{
			year = frm.year.value;
			fname = frm.firstname.value;
			lname = frm.lastname.value;
			place = frm.place.value;

			// display an error message if there is insufficient data to perform a search on
			if (year == "") {
				message = true;
				if (fname.length >= 2)
					message = false;
				if (lname.length >= 2)
					message = false;
				if (place.length >= 2)
					message = false;
				if (message) {
					alert("<?php echo WT_I18N::translate('Please enter more than one character'); ?>");
					return false;
				}
			}

			// display a special error if the year is entered without a valid Given Name, Last Name, or Place
			if (year != "") {
				message = true;
				if (fname != "")
					message = false;
				if (lname != "")
					message = false;
				if (place != "")
					message = false;
				if (message) {
					alert("<?php echo WT_I18N::translate('Please enter a Given name, Last name, or Place in addition to Year'); ?>");
					frm.firstname.focus();
					return false;
				}
			}
			return true;
		}
		return true;
	}

//-->
</script>
<div id="search-page">
<h2 class="center"><?php echo $controller->getPageTitle(); ?></h2>
<!-- /*************************************************** Search Form Outer Table **************************************************/ -->
<form method="post" name="searchform" onsubmit="return checknames(this);" action="search.php">
<input type="hidden" name="action" value="<?php echo $controller->action; ?>" />
<input type="hidden" name="isPostBack" value="true" />
<script type="text/javascript">
	function paste_char(value) {
		document.searchform.query.value+=value;
	}
</script>
<table class="list_table" width="35%">
	<tr>

<!-- /**************************************************** General search Form *************************************************************/ -->
			<?php if ($controller->action == "general") { ?>
				<td colspan="3" class="facts_label03" style="text-align:center;">
					<?php echo WT_I18N::translate('General search'); ?>
				</td>
	</tr>
	<!-- // search terms -->
	<tr>
		<td class="list_label" style="padding: 5px;">
			<label for="firstfocus"><?php echo WT_I18N::translate('Search for'); ?></label>
		</td>
		<td class="list_value" style="padding: 5px;">
			<input tabindex="1" id="firstfocus" type="text" name="query" value="<?php if (isset($controller->myquery)) echo $controller->myquery; ?>" size="40" autofocus/>
			<?php print_specialchar_link('firstfocus', false); ?>
		</td>
		<td class="list_value" style="vertical-align: middle; text-align: center; padding: 5px;"  rowspan="4">
			<input tabindex="2" type="submit" value="<?php echo WT_I18N::translate('Search'); ?>" />
		</td>
	</tr>
	<!-- // Choice where to search -->
	<tr>
		<td class="list_label" style="padding: 5px;">
			<?php echo /* I18N: Genealogy records, such as Individual, Source, Repository */ WT_I18N::translate('Records'); ?>
		</td>
		<td class="list_value" style="padding: 5px;">
			<input type="checkbox"<?php
	if (isset ($controller->srindi) || !$controller->isPostBack)
		echo ' checked="checked"'; ?> value="yes" id="srindi" name="srindi" />
				<label for="srindi"><?php echo WT_I18N::translate('Individuals'); ?></label><br >
			<input type="checkbox"<?php
	if (isset ($controller->srfams))
		echo ' checked="checked"'; ?> value="yes" id="srfams" name="srfams" />
				<label for="srfams"><?php echo WT_I18N::translate('Families'); ?></label><br />
			<input type="checkbox"<?php
	if (isset ($controller->srsour))
		echo ' checked="checked"'; ?> value="yes" id="srsour" name="srsour" />
				<label for="srsour"><?php echo WT_I18N::translate('Sources'); ?></label><br />
			<input type="checkbox"<?php
	if (isset ($controller->srnote))
		echo ' checked="checked"'; ?> value="yes" id="srnote" name="srnote" />
				<label for="srnote"><?php echo WT_I18N::translate('Shared notes'); ?></label><br />
		</td>
	</tr>
	<!-- Choice to Exclude non-genealogical data -->
	<tr>
		<td class="list_label" style="padding: 5px;">
			<?php echo WT_I18N::translate('Exclude filter'), help_link('search_exclude_tags'); ?>
		</td>
		<td class="list_value" style="padding: 5px;">
			<input type="radio" id="tagfilter_on" name="tagfilter" value="on"<?php
	if (($controller->tagfilter == "on") || ($controller->tagfilter == ""))
		echo ' checked="checked" '; ?>/>
				<label for="tagfilter_on"><?php echo WT_I18N::translate('Exclude some non-genealogical data'); ?></label><br />
			<input type="radio" id="tagfilter_off" name="tagfilter" value="off"<?php
	if ($controller->tagfilter == "off")
		echo ' checked="checked" ';?>/>
				<label for="tagfilter_off"><?php echo WT_I18N::translate('Off'); ?></label>
		</td>
	</tr>
	<!-- Choice to show related persons/families (associates) -->
	<tr>
		<td class="list_label" style="padding: 5px;">
			<?php echo WT_I18N::translate('Associates'), help_link('search_include_ASSO'); ?>
		</td>
		<td class="list_value" style="padding: 5px;">
			<input type="checkbox" id="showasso" name="showasso" value="on"<?php
	if ($controller->showasso == "on") echo ' checked="checked" '; ?>/>
				<label for="showasso"><?php echo WT_I18N::translate('Show related persons/families'); ?></label>
		</td>
	</tr>
			<?php

}
/**************************************************** Search and replace Search Form ****************************************************/
if ($controller->action == "replace")
{
	if (WT_USER_CAN_EDIT) {
?>
				<td colspan="3" class="facts_label03" style="text-align: center;">
					<?php echo WT_I18N::translate('Search and replace'); ?>
				</td>
	</tr>
	<!-- // search terms -->
	<tr>
		<td class="list_label" style="padding: 5px;"><?php echo WT_I18N::translate('Search for'); ?></td>
		<td class="list_value" style="padding: 5px;"><input tabindex="1" id="firstfocus" name="query" value="" type="text" autofocus/></td>
			<td class="list_value" style="vertical-align: middle; text-align: center; padding: 5px;"  rowspan="3">
			<input tabindex="2" type="submit" value="<?php echo WT_I18N::translate('Search'); ?>" />
		</td>
	</tr>
	<tr>
		<td class="list_label" style="padding: 5px;"><?php echo WT_I18N::translate('Replace with'); ?></td>
		<td class="list_value" style="padding: 5px;"><input tabindex="1" name="replace" value="" type="text"/></td>
	</tr>
	<!-- // Choice where to search -->
	<tr>
		<td class="list_label" style="padding: 5px;"><?php echo WT_I18N::translate('Search'); ?></td>
		<td class="list_value" style="padding: 5px;">
			<script type="text/javascript">
			<!--
				function checkAll(box) {
					if (!box.checked) {
						box.form.replaceNames.disabled = false;
						box.form.replacePlaces.disabled = false;
						box.form.replacePlacesWord.disabled = false;
					}
					else {
						box.form.replaceNames.disabled = true;
						box.form.replacePlaces.disabled = true;
						box.form.replacePlacesWord.disabled = true;
					}
				}
			//-->
			</script>
			<input checked="checked" onclick="checkAll(this);" value="yes" name="replaceAll" type="checkbox"/><?php echo WT_I18N::translate('Entire record'); ?>
			<br/>
			<hr />
			<input checked="checked" disabled="disabled" value="yes" name="replaceNames" type="checkbox"/><?php echo WT_I18N::translate('Individuals'); ?>
			<br/>
			<input checked="checked" disabled="disabled" value="yes" name="replacePlaces" type="checkbox"/><?php echo WT_I18N::translate('Place'); ?>
			<input checked="checked" disabled="disabled" value="yes" name="replacePlacesWord" type="checkbox"/><?php echo WT_I18N::translate('Whole words only'); ?>
			<br/>

		</td>
	</tr>
<?php
}
}

/**************************************************** Phonetic search Form *************************************************************/
if ($controller->action == "soundex") {
?>
				<td colspan="3" class="facts_label03" style="text-align:center;">
					<?php echo WT_I18N::translate('Phonetic search'); ?>
				</td>
	</tr>
	<!-- // search terms -->
	<tr>
		<td class="list_label" width="35%">
			<?php echo WT_I18N::translate('Given name'); ?>
		</td>
		<td class="list_value">
			<input tabindex="3" type="text" id="firstfocus" name="firstname" value="<?php echo $controller->myfirstname; ?>" autofocus/>
		</td>
		<td class="list_value" style="vertical-align: middle; text-align: center; padding: 5px;"  rowspan="6">
			<input tabindex="7" type="submit" value="<?php echo WT_I18N::translate('Search'); ?>" />
		</td>
	</tr>
	<tr>
		<td class="list_label">
			<?php echo WT_I18N::translate('Last name'); ?>
		</td>
		<td class="list_value">
			<input tabindex="4" type="text" name="lastname" value="<?php echo $controller->mylastname; ?>" />
		</td>
	</tr>
	<tr>
		<td class="list_label">
			<?php echo WT_I18N::translate('Place'); ?>
		</td>
		<td class="list_value">
			<input tabindex="5" type="text" name="place" value="<?php echo $controller->myplace; ?>" />
		</td>
	</tr>
	<tr>
		<td class="list_label">
			<?php echo WT_I18N::translate('Year'); ?>
		</td>
		<td class="list_value">
			<input tabindex="6" type="text" name="year" value="<?php echo $controller->myyear; ?>" />
		</td>
	</tr>
	<!-- Soundex type options (Russell, DaitchM) -->
	<tr>
		<td class="list_label">
			<?php echo /* I18N: rules for matching names by sound, rather than spelling */ WT_I18N::translate('Phonetic algorithm'); ?>
		</td>
		<td class="list_value" >
			<input type="radio" name="soundex" value="Russell"
				<?php if ($controller->soundex == "Russell") echo ' checked="checked" '; ?>/>
			<?php echo /* I18N: http://en.wikipedia.org/wiki/Soundex */ WT_I18N::translate('Russell'); ?><br />
			<input type="radio" name="soundex" value="DaitchM"
				<?php if ($controller->soundex == "DaitchM" || $controller->soundex == "") echo ' checked="checked" '; ?>/>
			<?php echo /* I18N: http://en.wikipedia.org/wiki/Daitch–Mokotoff_Soundex */ WT_I18N::translate('Daitch-Mokotoff'); ?>
		</td>
	</tr>

	<!-- Individuals' names to print options (Names with hit, All names) -->
	<!-- <tr>
		<td class="list_label">
			<?php  echo WT_I18N::translate('Individuals\'<br />names to print:'); ?>
		</td>
		<td class="list_value">
			<input type="radio" name="nameprt" value="hit"
				<?php if (($controller->nameprt == "hit") || ($controller->nameprt == "")) echo ' checked="checked" '; ?>/>
				<?php echo WT_I18N::translate('Names with hit'); ?><br />
			<input type="radio" name="nameprt" value="all"
				<?php if ($controller->nameprt == "all") echo ' checked="checked" '; ?>/>
				<?php echo WT_I18N::translate('All names'); ?>
		</td>
	</tr> -->
	<tr>
		<td class="list_label" style="padding: 5px;">
			<?php echo WT_I18N::translate('Associates'); ?>
		</td>
		<td class="list_value" style="padding: 5px;">
			<input type="checkbox" name="showasso" value="on"
				<?php if ($controller->showasso == "on") echo ' checked="checked" '; ?>/>
				<?php echo WT_I18N::translate('Show related persons/families'); ?>
		</td>
	</tr>
				<?php

}

// If the search is a general or soundex search then possibly display checkboxes for the gedcoms
if ($controller->action == "general" || $controller->action == "soundex") {
	$all_gedcoms=get_all_gedcoms();
	// If more than one GEDCOM, switching is allowed AND DB mode is set, let the user select
	if ((count($all_gedcoms) > 1) && get_site_setting('ALLOW_CHANGE_GEDCOM')) {
?>
	<tr>
		<td class="list_label" style="padding: 5px;">
			<?php echo WT_I18N::translate('Family trees'); ?>
		</td>
		<td class="list_value"colspan="2">
			<?php
		if (count($all_gedcoms)>3) {
			echo '<div style="border-bottom:1px solid;"><input type="button" value="', WT_I18N::translate('select all'), '" onclick="jQuery(\'#trees :checkbox\').each(function(){jQuery(this).attr(\'checked\', true);});return false;"/>';
			echo ' <input type="button" value="', WT_I18N::translate('select none'), '" onclick="jQuery(\'#trees :checkbox\').each(function(){jQuery(this).attr(\'checked\', false);});return false;"/>';
			if (count($all_gedcoms)>10) {
			 	echo ' <input type="button" value="', WT_I18N::translate('invert selection'), '" onclick="jQuery(\'#trees :checkbox\').each(function(){jQuery(this).attr(\'checked\', !jQuery(this).attr(\'checked\'));});return false;"/>';
			}
		}
		echo '</div><div id="trees" style="max-height:300px; overflow-x:auto;">', "\n";
		//-- sorting menu by gedcom filename
		asort($all_gedcoms);
		foreach ($all_gedcoms as $ged_id=>$gedcom) {
			$str = str_replace(array (".", "-", " "), array ("_", "_", "_"), $gedcom);
			$controller->inputFieldNames[] = "$str";
			echo '<input type="checkbox" ';
			if (isset ($_REQUEST["$str"])) {
				echo 'checked="checked" ';
			}
			echo 'value="yes" id="checkbox_', $ged_id , '" name="', $str, '" /><label for="checkbox_', $ged_id , '">', get_gedcom_setting($ged_id, 'title'), '</label><br />', "\n";
		}
		echo '</div></td>';
?>
	</tr>
	<?php
	}
}
?>
	<tr>
		<td class="list_label" style="padding: 5px;" >
			<?php echo WT_I18N::translate('Other Searches'); ?>
		</td>
		<td class="list_value" style="padding: 5px; text-align:center;" colspan="2" >
			<?php

if ($controller->action == "general") {
	echo '<a href="?action=soundex">', WT_I18N::translate('Phonetic search'), '</a>';
	echo ' | <a href="search_advanced.php">', WT_I18N::translate('Advanced search'), '</a>';
	if (WT_USER_CAN_EDIT) {
		echo ' | <a href="?action=replace">', WT_I18N::translate('Search and replace'), '</a>';
	}
} else if ($controller->action == "replace") {
	echo '<a href="?action=general">', WT_I18N::translate('General search'), '</a> | ';
	echo '<a href="?action=soundex">', WT_I18N::translate('Phonetic search'), '</a>';
	echo ' | <a href="search_advanced.php">', WT_I18N::translate('Advanced search'), '</a>';
} else if ($controller->action == "soundex") {
		echo '<a href="?action=general">', WT_I18N::translate('General search'), '</a>';
		echo ' | <a href="search_advanced.php">', WT_I18N::translate('Advanced search'), '</a>';
		if (WT_USER_CAN_EDIT) {
			echo ' | <a href="?action=replace">', WT_I18N::translate('Search and replace'), '</a>';
		}
	}
?>
		</td>
	</tr>
</table>
</form>

<?php $somethingPrinted = $controller->printResults(); ?>

</div> <!-- close div id "search-page" -->
