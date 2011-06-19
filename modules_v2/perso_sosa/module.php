<?php
/**
 * Class for Perso Sosa module.
 * This module is used for calculating and displaying Sosa ancestors.
 *
 * @package webtrees
 * @subpackage Perso
 * @author: Jonathan Jaubart ($Author$)
 * @version: p_$Revision$ $Date$
 * $HeadURL$
*/

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

// Create tables, if not already present
try {
	WT_DB::updateSchema(WT_ROOT.WT_MODULES_DIR.'perso_sosa/db_schema/', 'PSOSA_SCHEMA_VERSION', 1);
} catch (PDOException $ex) {
	// The schema update scripts should never fail.  If they do, there is no clean recovery.
	die($ex);
}

class perso_sosa_WT_Module extends WT_Module implements WT_Module_Menu, WT_Perso_Module_HookSubscriber, WT_Perso_Module_Configurable, WT_Perso_Module_IndividualHeaderExtender {
	
	// Extend class WT_Module
	public function getTitle() {
		return WT_I18N::translate('Perso Sosa');
	}

	// Extend class WT_Module
	public function getDescription() {
		return WT_I18N::translate('Calculate and display Sosa ancestors of the root person.');
	}
	
	// Extend WT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
			case 'computesosa':
				$this->computeSosaAjax();
				break;
			case 'computesosaindi':
				$this->computeSosaFromIndiAjax();
				break;
			case 'computesosainterface':
				$this->computeSosaEditInterface();
				break;
			case 'statistics':
			case 'sosalist':
			case 'missingancestors':
				require WT_ROOT.WT_MODULES_DIR.$this->getName().'/'.$mod_action.'.php';
				break;
			default:
				header('HTTP/1.0 404 Not Found');
		}
	}
	
	// Implement WT_Module_Menu
	public function defaultMenuOrder(){
		return 5;
	}
	
	// Implement WT_Module_Menu
	public function getMenu(){	
		global $controller;
		
		$menu = null;
		if(WT_Perso_Functions_Sosa::isModuleOperational()){
			echo WT_JS_START;
			echo 'function compute_sosa(pid) {',
					'window.open(\'module.php?mod=perso_sosa&mod_action=computesosainterface&pid=\' + pid, \'_blank\', \'top=50,left=50,width=510,height=520,resizable=1,scrollbars=1\');',
					'return false;',
				'}';
			echo WT_JS_END;
			
			//-- main menu
			$menu = new WT_Menu(WT_I18N::translate('Sosa Statistics'), 'module.php?mod=perso_sosa&mod_action=statistics', 'down');
			$menu->addIcon('menu_sosa');
			$menu->addClass('menuitem', 'menuitem_hover', 'submenu', 'icon_large_menu_sosa');
			$menu->addId('menu-sosa');
	
			//-- Sosa ancestors list
			$submenu = new WT_Menu(WT_I18N::translate('Sosa Ancestors'), 'module.php?mod=perso_sosa&mod_action=sosalist');
			$submenu->addIcon('menu_sosa_list');
			$submenu->addClass('submenuitem', 'submenuitem_hover', '', 'icon_small_menu_sosa_list');
			$submenu->addId('menu-sosa-list');
			$menu->addSubMenu($submenu);
			
			//-- Missing ancestors list
			$submenu = new WT_Menu(WT_I18N::translate('Missing Ancestors'), 'module.php?mod=perso_sosa&mod_action=missingancestors');
			$submenu->addIcon('menu_missing_ancestors');
			$submenu->addClass('submenuitem', 'submenuitem_hover', '', 'icon_small_menu_missing_ancestors');
			$submenu->addId('menu-sosa-missing');
			$menu->addSubMenu($submenu);
			
			//-- Sosa statistics
			$submenu = new WT_Menu(WT_I18N::translate('Sosa Statistics'), 'module.php?mod=perso_sosa&mod_action=statistics');
			$submenu->addIcon('menu_sosa');
			$submenu->addClass('submenuitem separator_top', 'submenuitem_hover', '', 'icon_small_sosa_statistics');
			$submenu->addId('menu-sosa-stats');
			$menu->addSubMenu($submenu);
			
			//-- recompute Sosa submenu
			if (WT_USER_CAN_EDIT && !empty($controller) && $controller instanceof WT_Controller_Individual ) {
				$submenu = new WT_Menu(WT_I18N::translate('Complete Sosas'), '#');
				$submenu->addOnclick('return compute_sosa(\''.$controller->pid.'\');');
				$submenu->addIcon('recompute_sosa');
				$submenu->addClass('submenuitem separator_top', 'submenuitem_hover', '', 'icon_small_recompute_sosa');
				$submenu->addId('menu-sosa-recompute');
				$menu->addSubMenu($submenu);
			}
		}
		return $menu;
	}
	
	// Implement WT_Perso_Module_HookSubscriber
	public function getSubscribedHooks() {
		return array(
			'h_config_tab_name' => 30,
			'h_config_tab_content' => 30,
			'h_extend_indi_header_icons' => 20,
			'h_extend_indi_header_right' => 20
		);
	}
	
	// Implement WT_Perso_Module_Configurable
	public function h_config_tab_name(){
		echo '<li><a href="#'.$this->getName().'"><span>', WT_I18N::translate('Sosa'), '</span></a></li>';
	}
	
	// Implement WT_Perso_Module_Configurable
	public function h_config_tab_content(){

		require_once WT_ROOT.WT_MODULES_DIR.$this->getName().'/js/computesosa.js.htm';
		
		echo '<div id="'.$this->getName().'"><table class="gm_edit_config"><tr><td><dl>';
		// Load all available gedcoms
		$all_gedcoms = get_all_gedcoms();
		foreach($all_gedcoms as $ged_id => $ged_name){
			if(userGedcomAdmin(WT_USER_ID, $ged_id)){
				$title=PrintReady(strip_tags(get_gedcom_setting($ged_id, 'title')));
				echo '<dt>', WT_I18N::translate('Root individual for <em>%s</em>', $title), help_link('config_root_indi', $this->getName()), '</dt>',
					'<dd>', WT_Perso_Functions_Edit::edit_module_field_inline('gedcom_setting-PERSO_PS_ROOT_INDI-'.$ged_id, get_gedcom_setting($ged_id, 'PERSO_PS_ROOT_INDI')),'</dd>',
					'<dt>', WT_I18N::translate('Compute all Sosas for <em>%s</em>', $title), help_link('config_computesosa', $this->getName()), '</dt>',
					'<dd><button id="bt_'.$ged_id.'" class="progressbutton" onClick="calculateSosa(\''.$ged_id.'\');"><div id="btsosa_'.$ged_id.'">'.WT_I18N::translate('Compute').'</div></button></dd>';
			}
		}
		echo '</dl></td></tr></table></div>';
	}
	
	// Implement WT_Perso_Module_Configurable
	public function validate_config_settings($setting, $value){
		return $value;
	}
	
	//Implement WT_Perso_IndividualHeaderExtender
	public function h_extend_indi_header_icons(WT_Controller_Individual $ctrlIndi) {
		if($ctrlIndi){
			$dindi = new WT_Perso_Person($ctrlIndi->indi);
			return WT_Perso_Functions_Print::formatSosaNumbers($dindi->getSosaNumbers(), 1, 'large');
		}
		return '';
	}
	
	//Implement WT_Perso_IndividualHeaderExtender
	public function h_extend_indi_header_left(WT_Controller_Individual $ctrlIndi) {
	}
	
	//Implement WT_Perso_IndividualHeaderExtender
	public function h_extend_indi_header_right(WT_Controller_Individual $ctrlIndi) {
		if($ctrlIndi){
			$dindi = new WT_Perso_Person($ctrlIndi->indi);
			return array('indi-header-sosa',  WT_Perso_Functions_Print::formatSosaNumbers($dindi->getSosaNumbers(), 2, 'normal'));
		}
		return '';
	}
	
	/**
	 * Compute asynchronously the complete Sosa list from the root individual, and return the result.
	 *
	 * @return string HTML code result to display
	 */
	private function computeSosaAjax(){
		global $WT_IMAGES, $GEDCOM, $tmp_sosatable;
		
		$html = '<img class="progressicon" src="'.$WT_IMAGES['error'].'" alt="'.WT_I18N::translate('Error').'" />';
		
		$ged_id = safe_GET_integer('gid', 0, 1000000, WT_GED_ID);
		if($ged_id && userGedcomAdmin(WT_USER_ID, $ged_id)){		
			$old_gedcom = $GEDCOM;
			$GEDCOM = get_gedcom_from_id($ged_id);
			$pid = get_gedcom_setting($ged_id, 'PERSO_PS_ROOT_INDI');
			if($pid){
				WT_Perso_Functions_Sosa::deleteAllSosas($ged_id);
				$dindi = WT_Perso_Person::getIntance($pid);
				if($dindi){
					$tmp_sosatable = array();		
					$dindi->addAndComputeSosa(1);
					WT_Perso_Functions_Sosa::flushTmpSosaTable(true);
					$html = '<img class="progressicon" src="'.$WT_IMAGES['success'].'" alt="'.WT_I18N::translate('Success').'" />';
				}
			}
			$GEDCOM = $old_gedcom;			
		}
		
		$html .= '&nbsp;'.WT_I18N::translate('Recompute');
		
		echo $html;		
	}
	
	/**
	 * Compute asynchronously the Sosa list from the individual enteres en parameter, and return the result.
	 *
	 * @return string HTML code result to display
	 */
	private function computeSosaFromIndiAjax(){
		global $WT_IMAGES, $tmp_removeSosaTab, $tmp_sosatable;
		
		$html = '<img class="progressicon" src="'.$WT_IMAGES['error'].'" alt="'.WT_I18N::translate('Error').'" />';
		
		$pid = safe_GET_xref('pid');
		if(WT_USER_CAN_EDIT && $pid){
			$indi = WT_Person::getInstance($pid);
			if($indi){
				$tmp_removeSosaTab = array();
				$tmp_sosatable = array();
				$dindi = new WT_Perso_Person($indi);
				$current_sosa = $dindi->getSosaNumbers();
				if($current_sosa && count($current_sosa)>0){
					$dindi->removeSosas();
					WT_Perso_Functions_Sosa::flushTmpRemoveTable(true);
					foreach($current_sosa as $sosa => $gen){
						$dindi->addAndComputeSosa($sosa);
					}
					WT_Perso_Functions_Sosa::flushTmpSosaTable(true);
					$html = '<img class="progressicon" src="'.$WT_IMAGES['success'].'" alt="'.WT_I18N::translate('Success').'" />&nbsp;'.WT_I18N::translate('Computed');
				}
				else{
					$html .= '&nbsp;'.WT_I18N::translate('Individual is not a Sosa');	
				}
			}
			else{
				$html .= '&nbsp;'.WT_I18N::translate('Non existing individual');		
			}			
		}
		else{
			$html .= '&nbsp;'.WT_I18N::translate('You are not allowed to perform this operation.');	
		}
		
		echo $html;		
	}
	
	/**
	 * Display the pop-up window to compute Sosa list from an individual.
	 */
	private function computeSosaEditInterface(){
		
		print_simple_header(WT_I18N::translate('Compute Sosas'));
		
		echo '<div class="helpheader">', WT_I18N::translate('Compute Sosas'), '</div>';
		
		$pid = safe_GET_xref('pid');
		if($pid){
			require_once WT_ROOT.WT_MODULES_DIR.$this->getName().'/js/computesosaindi.js.htm';
			
			echo '<div id="loadingarea"></div>';
		}
		print_simple_footer();
		
	}
	
}

?>