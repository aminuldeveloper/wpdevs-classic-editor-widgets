<?php
/**
 * Plugin Name: WPDevs Classic Editor & Widgets
 * Plugin URI:  https://wordpress.org/plugins/wpdevs-classic-editor-widgets/
 * Description: Enables the traditional WordPress classic editor, classic widgets, and the previous version of the Edit Post screen featuring TinyMCE, Meta Boxes, and more. This also supports older plugins that enhance this screen.
 * Version:     1.1
 * Author:      WPDevs
 * Author URI:  https://wpdevs.xyz
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: wpdevs-classic-editor
 * Domain Path: /languages
 * Requires PHP: 7.4
 *
 * @package WPDevs_Classic_Editor_Widgets
 */

add_action( 'plugins_loaded', 'wpdevs_ce_init' );

/**
 * Initialize the plugin.
 */
function wpdevs_ce_init() {
	load_plugin_textdomain( 'wpdevs-classic-editor', false, basename( __DIR__ ) . '/languages' );

	if ( false === get_option( 'wpdevs_classic_editor_settings' ) ) {
		$default_options = array(
			'disable_gutenberg'         => 1,
			'disable_gutenberg_widgets' => 1,
		);
		add_option( 'wpdevs_classic_editor_settings', $default_options );
	}

	$options = get_option( 'wpdevs_classic_editor_settings' );

	if ( isset( $options['disable_gutenberg'] ) && $options['disable_gutenberg'] ) {
		add_filter( 'use_block_editor_for_post', '__return_false', 10 );
		add_filter( 'use_block_editor_for_post_type', '__return_false', 10 );
	}

	if ( isset( $options['disable_gutenberg_widgets'] ) && $options['disable_gutenberg_widgets'] ) {
		add_action( 'after_setup_theme', 'wpdevs_ce_disable_widgets_editor' );
	}

	add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wpdevs_ce_add_settings_link' );
}

/**
 * Disable the block-based widgets editor.
 */
function wpdevs_ce_disable_widgets_editor() {
	remove_theme_support( 'widgets-block-editor' );
}

add_action( 'admin_init', 'wpdevs_ce_register_settings_to_writing_page' );

/**
 * Register plugin settings and add fields to the Writing settings page.
 */
function wpdevs_ce_register_settings_to_writing_page() {
	register_setting(
		'writing',
		'wpdevs_classic_editor_settings',
		array(
			'sanitize_callback' => 'wpdevs_ce_sanitize_settings',
		)
	);

	add_settings_section(
		'wpdevs_classic_editor_general_settings',
		'WPDevs Classic Editor & Widgets',
		null,
		'writing'
	);

	add_settings_field(
		'disable_gutenberg',
		'Back To Traditional Editor',
		'wpdevs_ce_disable_gutenberg_cb',
		'writing',
		'wpdevs_classic_editor_general_settings'
	);

	add_settings_field(
		'disable_gutenberg_widgets',
		'Back To Traditional Widgets',
		'wpdevs_ce_disable_gutenberg_widgets_cb',
		'writing',
		'wpdevs_classic_editor_general_settings'
	);
}

/**
 * Sanitize plugin settings input.
 *
 * @param array $input Raw input from settings form.
 * @return array Sanitized settings.
 */
function wpdevs_ce_sanitize_settings( $input ) {
	$output                              = array();
	$output['disable_gutenberg']         = ( isset( $input['disable_gutenberg'] ) && 1 === (int) $input['disable_gutenberg'] ) ? 1 : 0;
	$output['disable_gutenberg_widgets'] = ( isset( $input['disable_gutenberg_widgets'] ) && 1 === (int) $input['disable_gutenberg_widgets'] ) ? 1 : 0;
	return $output;
}

/**
 * Render the classic editor checkbox field.
 */
function wpdevs_ce_disable_gutenberg_cb() {
	$options = get_option( 'wpdevs_classic_editor_settings' );
	?>
	<input type="checkbox" name="wpdevs_classic_editor_settings[disable_gutenberg]" value="1" <?php checked( 1, isset( $options['disable_gutenberg'] ) ? $options['disable_gutenberg'] : 1, true ); ?>>
	<?php
}

/**
 * Render the classic widgets checkbox field.
 */
function wpdevs_ce_disable_gutenberg_widgets_cb() {
	$options = get_option( 'wpdevs_classic_editor_settings' );
	?>
	<input type="checkbox" name="wpdevs_classic_editor_settings[disable_gutenberg_widgets]" value="1" <?php checked( 1, isset( $options['disable_gutenberg_widgets'] ) ? $options['disable_gutenberg_widgets'] : 1, true ); ?>>
	<?php
}

/**
 * Add a Settings link to the plugin action links.
 *
 * @param array $links Existing plugin action links.
 * @return array Modified plugin action links.
 */
function wpdevs_ce_add_settings_link( $links ) {
	$settings_link = '<a href="' . esc_url( admin_url( 'options-writing.php' ) ) . '">' . __( 'Settings', 'wpdevs-classic-editor' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
}
