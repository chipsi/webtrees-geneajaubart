<?php
// Functions and logic for GEDCOM "PEDI" codes
//
// webtrees: Web based Family History software
// Copyright (C) 2012 webtrees development team.
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
// $Id: Pedi.php 13596 2012-03-17 11:48:27Z greg $

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class WT_Gedcom_Code_Pedi {
	
	private static $TYPES=array('adopted', 'birth', 'foster', 'rada', 'sealing');

	// Translate a code, for an optional record
	public static function getValue($type, $record=null) {
		if ($record instanceof WT_Person) {
			$sex=$record->getSex();
		} else {
			$sex='U';
		}

		switch ($type) {
		case 'birth':
			switch ($sex) {
			case 'U': return WT_I18N::translate_c('Pedigree',        'Birth');
			case 'M': return WT_I18N::translate_c('Male pedigree',   'Birth');
			case 'F': return WT_I18N::translate_c('Female pedigree', 'Birth');
			}
		case 'adopted':
			switch ($sex) {
			case 'U': return WT_I18N::translate_c('Pedigree',        'Adopted');
			case 'M': return WT_I18N::translate_c('Male pedigree',   'Adopted');
			case 'F': return WT_I18N::translate_c('Female pedigree', 'Adopted');
			}
		case 'foster':
			switch ($sex) {
			case 'U': return WT_I18N::translate_c('Pedigree',        'Foster');
			case 'M': return WT_I18N::translate_c('Male pedigree',   'Foster');
			case 'F': return WT_I18N::translate_c('Female pedigree', 'Foster');
			}
		case 'sealing':
			switch ($sex) {
			case 'U': return /* I18N: "sealing" is a Mormon ceremony. */ WT_I18N::translate_c('Pedigree',        'Sealing');
			case 'M': return /* I18N: "sealing" is a Mormon ceremony. */ WT_I18N::translate_c('Male pedigree',   'Sealing');
			case 'F': return /* I18N: "sealing" is a Mormon ceremony. */ WT_I18N::translate_c('Female pedigree', 'Sealing');
			}
			case 'rada':
				// Not standard GEDCOM - a webtrees extension
				// This is an arabic word which does not exist in other languages.
				// So, it will not have any inflected forms.
				return /* I18N: This is an Arabic word, pronounced "ra DAH".  It is child-to-parent pedigree, established by wet-nursing. */ WT_I18N::translate('Rada');
		default:
			return $type;
		}
	}

	// A list of all possible values for PEDI
	public static function getValues($record=null) {
		$values=array();
		foreach (self::$TYPES as $type) {
			$values[$type]=self::getValue($type, $record);
		}
		uasort($values, 'utf8_strcasecmp');
		return $values;
	}

	// A label for a parental family group
	public static function getChildFamilyLabel($pedi) {
		switch ($pedi) {
		case '':
		case 'birth':   return WT_I18N::translate('Family with parents');
		case 'adopted': return WT_I18N::translate('Family with adoptive parents');
		case 'foster':  return WT_I18N::translate('Family with foster parents');
		case 'sealing': return /* I18N: "sealing" is a Mormon ceremony. */ WT_I18N::translate('Family with sealing parents');
		case 'rada':    return /* I18N: "rada" is an Arabic word, pronounced "ra DAH".  It is child-to-parent pedigree, established by wet-nursing. */ WT_I18N::translate('Family with rada parents');
		default:        return WT_I18N::translate('Family with parents').' - '.$pedi;
		}
	}

	// Create GEDCOM for a new child-family pedigree
	public static function createNewFamcPedi($pedi, $xref) {
		switch ($pedi) {
		case '':        return "1 FAMC @$xref@";
		case 'adopted': return "1 FAMC @$xref@\n2 PEDI $pedi\n1 ADOP\n2 FAMC @$xref@\n3 ADOP BOTH";
		case 'sealing': return "1 FAMC @$xref@\n2 PEDI $pedi\n1 SLGC\n2 FAMC @$xref@";
		case 'foster':  return "1 FAMC @$xref@\n2 PEDI $pedi\n1 EVEN\n2 TYPE $pedi";
		default:        return "1 FAMC @$xref@\n2 PEDI $pedi";
		}
	}
}
