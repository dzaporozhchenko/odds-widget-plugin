<?php

/**
 * The plugin bootstrap file
 *
 * @link              https://github.com/dzaporozhchenko
 * @since             1.0.0
 * @package           Odds_Widget
 *
 * @wordpress-plugin
 * Plugin Name:       Odds Widget
 * Plugin URI:        https://github.com/dzaporozhchenko/odds-widget-plugin/
 * Description:       Odds comparison plugin that will display odds from different bookmakers with corresponding widget.
 * Version:           1.0.0
 * Author:            Dmytro Zaporozhchenko
 * Author URI:        https://github.com/dzaporozhchenko
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       odds-widget
 * Domain Path:       /languages
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

define( 'ODDS_WIDGET_VERSION', '1.0.0' );

require plugin_dir_path(__FILE__) . 'includes/class-odds-widget.php';

$plugin = new Odds_Widget();
$plugin->run();