<?php
// Classes and libraries for module system
//
// webtrees: Web based Family History software
// Copyright (C) 2011 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2010 John Finlay
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
// $Id: module.php 13034 2011-12-12 13:10:58Z greg $

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class relatives_WT_Module extends WT_Module implements WT_Module_Tab {
	// Extend WT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ WT_I18N::translate('Families');
	}

	// Extend WT_Module
	public function getDescription() {
		return /* I18N: Description of the "Families" module */ WT_I18N::translate('A tab showing the close relatives of an individual.');
	}

	// Implement WT_Module_Tab
	public function defaultTabOrder() {
		return 20;
	}

	function printFamilyHeader($url, $label) {
		global $WT_IMAGES, $SEARCH_SPIDER;

		echo '<table>
			<tr>';
			if (isset($WT_IMAGES["cfamily"])) {
				echo '<td><img src="', $WT_IMAGES["cfamily"], '" class="icon" alt=""></td>';
			}
			echo '<td><span class="subheaders">', $label, '</span>';
			if (empty($SEARCH_SPIDER)) {
				echo ' - <a href="', $url, '">', WT_I18N::translate('View Family'), '</a>';
			 }
			echo '</td>
			</tr>
		</table>';
	}

	/**
	* print parents informations
	* @param Family family
	* @param Array people
	* @param String family type
	* @return html table rows
	*/
	function printParentsRows($family, $people, $type) {
		global $personcount, $WT_IMAGES, $SHOW_PEDIGREE_PLACES, $controller;

		$elderdate = "";
		//-- new father/husband
		$styleadd = "";
		if (isset($people["newhusb"])) {
			$styleadd = "red";
			?>
			<tr>
				<td class="facts_labelblue"><?php echo $people["newhusb"]->getLabel(); ?></td>
				<td class="<?php echo $controller->getPersonStyle($people["newhusb"]); ?>">
					<?php print_pedigree_person($people["newhusb"], 2, 0, $personcount++); ?>
				</td>
			</tr>
			<?php
			$elderdate = $people["newhusb"]->getBirthDate();
		}
		//-- father/husband
		if (isset($people["husb"])) {
			?>
			<tr>
				<td class="facts_label<?php echo $styleadd; ?>"><?php echo $people["husb"]->getLabel(); ?></td>
				<td class="<?php echo $controller->getPersonStyle($people["husb"]); ?>">
					<?php print_pedigree_person($people["husb"], 2, 0, $personcount++); ?>
				</td>
			</tr>
			<?php
			$elderdate = $people["husb"]->getBirthDate();
		}
		//-- missing father
		if ($type=="parents" && !isset($people["husb"]) && !isset($people["newhusb"])) {
			if ($controller->record->canEdit()) {
				?>
				<tr>
					<td class="facts_label"><?php echo WT_I18N::translate('Add a new father'); ?></td>
					<td class="facts_value"><a href="#" onclick="return addnewparentfamily('<?php echo $controller->record->getXref(); ?>', 'HUSB', '<?php echo $family->getXref(); ?>');"><?php echo WT_I18N::translate('Add a new father'); ?></a><?php echo help_link('edit_add_parent'); ?></td>
				</tr>
				<?php
			}
		}
		//-- missing husband
		if ($type=="spouse" && !isset($people["husb"]) && !isset($people["newhusb"])) {
			if ($controller->record->canEdit()) {
				?>
				<tr>
					<td class="facts_label"><?php echo WT_I18N::translate('Add husband'); ?></td>
					<td class="facts_value"><a href="#" onclick="return addnewspouse('<?php echo $family->getXref(); ?>', 'HUSB');"><?php echo WT_I18N::translate('Add a husband to this family'); ?></a></td>
				</tr>
				<?php
			}
		}
		//-- new mother/wife
		$styleadd = "";
		if (isset($people["newwife"])) {
			$styleadd = "red";
			?>
			<tr>
				<td class="facts_labelblue"><?php echo $people["newwife"]->getLabel($elderdate); ?></td>
				<td class="<?php echo $controller->getPersonStyle($people["newwife"]); ?>">
					<?php print_pedigree_person($people["newwife"], 2, 0, $personcount++); ?>
				</td>
			</tr>
			<?php
		}
		//-- mother/wife
		if (isset($people["wife"])) {
			?>
			<tr>
				<td class="facts_label<?php echo $styleadd; ?>"><?php echo $people["wife"]->getLabel($elderdate); ?></td>
				<td class="<?php echo $controller->getPersonStyle($people["wife"]); ?>">
					<?php print_pedigree_person($people["wife"], 2, 0, $personcount++); ?>
				</td>
			</tr>
			<?php
		}
		//-- missing mother
		if ($type=="parents" && !isset($people["wife"]) && !isset($people["newwife"])) {
			if ($controller->record->canEdit()) {
				?>
				<tr>
					<td class="facts_label"><?php echo WT_I18N::translate('Add a new mother'); ?></td>
					<td class="facts_value"><a href="#" onclick="return addnewparentfamily('<?php echo $controller->record->getXref(); ?>', 'WIFE', '<?php echo $family->getXref(); ?>');"><?php echo WT_I18N::translate('Add a new mother'); ?></a><?php echo help_link('edit_add_parent'); ?></td>
				</tr>
				<?php
			}
		}
		//-- missing wife
		if ($type=="spouse" && !isset($people["wife"]) && !isset($people["newwife"])) {
			if ($controller->record->canEdit()) {
				?>
				<tr>
					<td class="facts_label"><?php echo WT_I18N::translate('Add wife'); ?></td>
					<td class="facts_value"><a href="#" onclick="return addnewspouse('<?php echo $family->getXref(); ?>', 'WIFE');"><?php echo WT_I18N::translate('Add a wife to this family'); ?></a></td>
				</tr>
				<?php
			}
		}
		//-- marriage row
		if ($family->getMarriageRecord()!="" || WT_USER_CAN_EDIT) {
			$styleadd = "";
			$date = $family->getMarriageDate();
			$place = $family->getMarriagePlace();
			$famid = $family->getXref();
			if (!$date && ($famrec = find_updated_record($famid))!==null) {
				$marrrec = get_sub_record(1, "1 MARR", $famrec);
				if ($marrrec!=$family->getMarriageRecord()) {
					$date = new WT_Date(get_gedcom_value("MARR:DATE", 1, $marrrec, '', false));
					$place = get_gedcom_value("MARR:PLAC", 1, $marrrec, '', false);
					$styleadd = "blue";
				}
			}
			?>
			<tr>
				<td class="facts_label"><br>
				</td>
				<td class="facts_value<?php echo $styleadd; ?>">
					<?php //echo "<span class=\"details_label\">".WT_Gedcom_Tag::getLabel('NCHI').": </span>".$family->getNumberOfChildren()."<br>"; ?>
					<?php $marr_type = strtoupper($family->getMarriageType());
					if ($marr_type=='CIVIL' || $marr_type=='PARTNERS' || $marr_type=='RELIGIOUS' || $marr_type=='UNKNOWN') {
						$marr_fact = WT_Gedcom_Tag::getLabel("MARR_".$marr_type);
					} else if ($marr_type) {
						$marr_fact = WT_Gedcom_Tag::getLabel("MARR").' '.$family->getMarriageType();
					} else {
						$marr_fact = WT_Gedcom_Tag::getLabel("MARR");
					}
					if ($date && $date->isOK() || $place) {
						echo '<span class="details_label">', $marr_fact, ': </span>';
						if ($date) {
							echo $date->Display(false);
							if (!empty($place)) echo ' -- ';
						}
						if (!empty($place)) {
							$html='';
							$levels = explode(',', $place);
							$tempURL = "placelist.php?action=show&amp;";
							foreach (array_reverse($levels) as $pindex=>$ppart) {
								$tempURL .= "parent[{$pindex}]=".rawurlencode($ppart).'&amp;';
							}
							$tempURL .= 'level='.count($levels);
							$html .= '<a href="'.$tempURL.'"> ';
							for ($level=0; $level<$SHOW_PEDIGREE_PLACES; $level++) {
								if (!empty($levels[$level])) {
									if ($level>0) {
										$html.=", ";
									}
									$html.=PrintReady($levels[$level]);
								}
							}
							$html.='</a>';
							echo $html;
						}
					} else if (get_sub_record(1, "1 _NMR", find_family_record($famid, WT_GED_ID))) {
						$husb = $family->getHusband();
						$wife = $family->getWife();
						if (empty($wife) && !empty($husb)) echo WT_Gedcom_Tag::getLabel('_NMR', $husb);
						else if (empty($husb) && !empty($wife)) echo WT_Gedcom_Tag::getLabel('_NMR', $wife);
						else echo WT_Gedcom_Tag::getLabel('_NMR');
					} else if (get_sub_record(1, "1 _NMAR", find_family_record($famid, WT_GED_ID))) {
						$husb = $family->getHusband();
						$wife = $family->getWife();
						if (empty($wife) && !empty($husb)) echo WT_Gedcom_Tag::getLabel('_NMAR', $husb);
						else if (empty($husb) && !empty($wife)) echo WT_Gedcom_Tag::getLabel('_NMAR', $wife);
						else echo WT_Gedcom_Tag::getLabel('_NMAR');
					} else if ($family->getMarriageRecord()=="" && $controller->record->canEdit()) {
						echo "<a href=\"#\" onclick=\"return add_new_record('".$famid."', 'MARR');\">".WT_I18N::translate('Add marriage details')."</a>";
					} else {
						$factdetail = explode(' ', trim($family->getMarriageRecord()));
						if (isset($factdetail) && count($factdetail) == 3) {
							if (strtoupper($factdetail[2]) == "Y") {
								echo '<span class="details_label">', $marr_fact, ': </span>', WT_I18N::translate('yes');
							} else if (strtoupper($factdetail[2]) == "N") {
								echo '<span class="details_label">', $marr_fact, ': </span>', WT_I18N::translate('no');
							}
						} else {
							echo '<span class="details_label">', $marr_fact, '</span>';
						}
					}
					?>
				</td>
			</tr>
			<?php
		}
	}

	/**
	* print children informations
	* @param Family family
	* @param Array people
	* @param String family type
	* @return html table rows
	*/
	function printChildrenRows($family, $people, $type) {
		global $personcount, $WT_IMAGES, $controller;

		$elderdate = $family->getMarriageDate();
		$key=0;
		foreach ($people["children"] as $child) {
			$label = $child->getLabel();
			$styleadd = "";
			?>
			<tr>
				<td class="facts_label<?php echo $styleadd; ?>"><?php if ($styleadd=="red") echo $child->getLabel(); else echo $child->getLabel($elderdate, $key+1); ?></td>
				<td class="<?php echo $controller->getPersonStyle($child); ?>">
				<?php
				print_pedigree_person($child, 2, 0, $personcount++);
				?>
				</td>
			</tr>
			<?php
			$elderdate = $child->getBirthDate();
			++$key;
		}
		foreach ($people["newchildren"] as $child) {
			$label = $child->getLabel();
			$styleadd = "blue";
			?>
			<tr>
				<td class="facts_label<?php echo $styleadd; ?>"><?php if ($styleadd=="red") echo $child->getLabel(); else echo $child->getLabel($elderdate, $key+1); ?></td>
				<td class="<?php echo $controller->getPersonStyle($child); ?>">
				<?php
				print_pedigree_person($child, 2, 0, $personcount++);
				?>
				</td>
			</tr>
			<?php
			$elderdate = $child->getBirthDate();
			++$key;
		}
		foreach ($people["delchildren"] as $child) {
			$label = $child->getLabel();
			$styleadd = "red";
			?>
			<tr>
				<td class="facts_label<?php echo $styleadd; ?>"><?php if ($styleadd=="red") echo $child->getLabel(); else echo $child->getLabel($elderdate, $key+1); ?></td>
				<td class="<?php echo $controller->getPersonStyle($child); ?>">
				<?php
				print_pedigree_person($child, 2, 0, $personcount++);
				?>
				</td>
			</tr>
			<?php
			$elderdate = $child->getBirthDate();
			++$key;
		}
		if (isset($family) && $controller->record->canEdit()) {
			if ($type == "spouse") {
				$child_u = WT_I18N::translate('Add a son or daughter');
				$child_m = WT_I18N::translate('son');
				$child_f = WT_I18N::translate('daughter');
			} else {
				$child_u = WT_I18N::translate('Add a brother or sister');
				$child_m = WT_I18N::translate('brother');
				$child_f = WT_I18N::translate('sister');
			}
		?>
			<tr>
				<td class="facts_label">
					<?php if (WT_USER_CAN_EDIT && isset($people["children"][1])) { ?>
					<a href="#" onclick="reorder_children('<?php echo $family->getXref(); ?>');tabswitch(5);"><img src="<?php echo WT_STATIC_URL; ?>images/topdown.gif" alt="" > <?php echo WT_I18N::translate('Re-order children'); ?></a>
					<?php } ?>
				</td>
				<td class="facts_value">
					<a href="#" onclick="return addnewchild('<?php echo $family->getXref(); ?>');"><?php echo $child_u; ?></a>
					<span style='white-space:nowrap;'>
						<a href="#" onclick="return addnewchild('<?php echo $family->getXref(); ?>','M');"><?php echo WT_Person::sexImage('M', 'small', '', $child_m); ?></a>
						<a href="#" onclick="return addnewchild('<?php echo $family->getXref(); ?>','F');"><?php echo WT_Person::sexImage('F', 'small', '', $child_f); ?></a>
					</span>
					<?php
						if ($type=='spouse') {
							echo help_link('add_son_daughter');
						} else {
							echo help_link('add_sibling');
						}
					?>
				</td>
			</tr>
			<?php
		}
	}

	// Implement WT_Module_Tab
	public function getTabContent() {
		global $WT_IMAGES, $SHOW_AGE_DIFF, $GEDCOM, $ABBREVIATE_CHART_LABELS, $show_full, $personcount, $controller;

		if (isset($show_full)) $saved_show_full = $show_full; // We always want to see full details here
		$show_full = 1;

		$saved_ABBREVIATE_CHART_LABELS = $ABBREVIATE_CHART_LABELS;
		$ABBREVIATE_CHART_LABELS = false; // Override GEDCOM configuration

		ob_start();
		?>
		<table class="facts_table"><tr><td colspan="2" class="descriptionbox rela">
		<input id="checkbox_elder" type="checkbox" onclick="jQuery('div.elderdate').toggle();" <?php if ($SHOW_AGE_DIFF) echo "checked=\"checked\""; ?>>
		<label for="checkbox_elder"><?php echo WT_I18N::translate('Show date differences'), help_link('age_differences'); ?></label>
		</td></tr></table>
		<?php
		$personcount=0;
		$families = $controller->record->getChildFamilies();
		if (count($families)==0) {
			if ($controller->record->canEdit()) {
				?>
				<table class="facts_table">
					<tr>
						<td class="facts_value"><a href="#" onclick="return addnewparent('<?php echo $controller->record->getXref(); ?>', 'HUSB');"><?php echo WT_I18N::translate('Add a new father'); ?></a><?php echo help_link('edit_add_parent'); ?></td>
					</tr>
					<tr>
						<td class="facts_value"><a href="#" onclick="return addnewparent('<?php echo $controller->record->getXref(); ?>', 'WIFE');"><?php echo WT_I18N::translate('Add a new mother'); ?></a><?php echo help_link('edit_add_parent'); ?></td>
					</tr>
				</table>
				<?php
			}
		}

		// parents
		foreach ($families as $family) {
			$people = $controller->buildFamilyList($family, "parents");
			$this->printFamilyHeader($family->getHtmlUrl(), $controller->record->getChildFamilyLabel($family));
			echo '<table class="facts_table">';
			$this->printParentsRows($family, $people, "parents");
			$this->printChildrenRows($family, $people, "parents");
			echo '</table>';
		}

		// step-parents
		foreach ($controller->record->getChildStepFamilies() as $family) {
			$people = $controller->buildFamilyList($family, "step-parents");
			$this->printFamilyHeader($family->getHtmlUrl(), $controller->record->getStepFamilyLabel($family));
			echo '<table class="facts_table">';
			$this->printParentsRows($family, $people, "parents");
			$this->printChildrenRows($family, $people, "parents");
			echo '</table>';
		}

		// spouses
		$families = $controller->record->getSpouseFamilies();
		foreach ($families as $family) {
			$people = $controller->buildFamilyList($family, "spouse");
			$this->printFamilyHeader($family->getHtmlUrl(), $controller->record->getSpouseFamilyLabel($family));
			echo '<table class="facts_table">';
			$this->printParentsRows($family, $people, "spouse");
			$this->printChildrenRows($family, $people, "spouse");
			echo '</table>';
		}

		// step-children
		foreach ($controller->record->getSpouseStepFamilies() as $family) {
			$people = $controller->buildFamilyList($family, "step-children");
			$this->printFamilyHeader($family->getHtmlUrl(), $family->getFullName());
			echo '<table class="facts_table">';
			$this->printParentsRows($family, $people, "spouse");
			$this->printChildrenRows($family, $people, "spouse");
			echo '</table>';
		}

		if (!$SHOW_AGE_DIFF) {
			echo WT_JS_START, "jQuery('DIV.elderdate').toggle();", WT_JS_END;
		}

		if ($controller->record->canEdit()) {
		?>
		<br><table class="facts_table">
		<?php
			if (count($families)>1) { ?>
			<tr>
				<td class="facts_value">
				<a href="#" onclick="return reorder_families('<?php echo $controller->record->getXref(); ?>');"><?php echo WT_I18N::translate('Reorder families'); ?></a>
				<?php echo help_link('reorder_families'); ?>
				</td>
			</tr>
		<?php } ?>
			<tr>
				<td class="facts_value">
				<a href="#" onclick="return add_famc('<?php echo $controller->record->getXref(); ?>');"><?php echo WT_I18N::translate('Link this person to an existing family as a child'); ?></a>
				<?php echo help_link('link_child'); ?>
				</td>
			</tr>
			<?php if ($controller->record->getSex()!="F") { ?>
			<tr>
				<td class="facts_value">
				<a href="#" onclick="return addspouse('<?php echo $controller->record->getXref(); ?>','WIFE');"><?php echo WT_I18N::translate('Add a new wife'); ?></a>
				<?php echo help_link('add_wife'); ?>
				</td>
			</tr>
			<tr>
				<td class="facts_value">
				<a href="#" onclick="return linkspouse('<?php echo $controller->record->getXref(); ?>','WIFE');"><?php echo WT_I18N::translate('Add a wife using an existing person'); ?></a>
				<?php echo help_link('link_new_wife'); ?>
				</td>
			</tr>
			<tr>
				<td class="facts_value">
				<a href="#" onclick="return add_fams('<?php echo $controller->record->getXref(); ?>','HUSB');"><?php echo WT_I18N::translate('Link this person to an existing family as a husband'); ?></a>
				<?php echo help_link('link_new_husb'); ?>
				</td>
			</tr>
			<?php }
			if ($controller->record->getSex()!="M") { ?>
			<tr>
				<td class="facts_value">
				<a href="#" onclick="return addspouse('<?php echo $controller->record->getXref(); ?>','HUSB');"><?php echo WT_I18N::translate('Add a new husband'); ?></a>
				<?php echo help_link('add_husband'); ?>
				</td>
			</tr>
			<tr>
				<td class="facts_value">
				<a href="#" onclick="return linkspouse('<?php echo $controller->record->getXref(); ?>','HUSB');"><?php echo WT_I18N::translate('Add a husband using an existing person'); ?></a>
				<?php echo help_link('link_husband'); ?>
				</td>
			</tr>
			<tr>
				<td class="facts_value">
				<a href="#" onclick="return add_fams('<?php echo $controller->record->getXref(); ?>','WIFE');"><?php echo WT_I18N::translate('Link this person to an existing family as a wife'); ?></a>
				<?php echo help_link('link_wife'); ?>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td class="facts_value">
				<a href="#" onclick="return addopfchild('<?php echo $controller->record->getXref(); ?>','U');"><?php echo WT_I18N::translate('Add a child to create a one-parent family'); ?></a>
				<?php echo help_link('add_opf_child'); ?>
				</td>
			</tr>
		</table>
		<?php } ?>
		<br>
		<?php

		$ABBREVIATE_CHART_LABELS = $saved_ABBREVIATE_CHART_LABELS; // Restore GEDCOM configuration
		unset($show_full);
		if (isset($saved_show_full)) $show_full = $saved_show_full;

		return '<div id="'.$this->getName().'_content">'.ob_get_clean().'</div>';
	}

	// Implement WT_Module_Tab
	public function hasTabContent() {
		return true;
	}
	// Implement WT_Module_Tab
	public function isGrayedOut() {
		return false;
	}
	// Implement WT_Module_Tab
	public function canLoadAjax() {
		global $SEARCH_SPIDER;

		return !$SEARCH_SPIDER; // Search engines cannot use AJAX
	}

	// Implement WT_Module_Tab
	public function getPreLoadContent() {
		return '';
	}

	// Implement WT_Module_Tab
	public function getJSCallback() {
		return '';
	}
}
