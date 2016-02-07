<?php namespace Fisharebest\Localization\Locale;

use Fisharebest\Localization\Territory\TerritoryCx;

/**
 * Class LocaleEnCx
 *
 * @author        Greg Roach <fisharebest@gmail.com>
 * @copyright (c) 2015 Greg Roach
 * @license       GPLv3+
 */
class LocaleEnCx extends LocaleEn {
	public function territory() {
		return new TerritoryCx;
	}
}
