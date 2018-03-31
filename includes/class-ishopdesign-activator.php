<?php

/**
 * Fired during plugin activation
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    iShopDesign
 * @subpackage iShopDesign/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    iShopDesign
 * @subpackage iShopDesign/includes
 * @author     Tuan Lu <tuan.mrbean@gmail.com>
 */
class iShopDesign_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		create_isd_pages();
	}

}
