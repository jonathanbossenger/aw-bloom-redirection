<?php
/*
 * Plugin Name: AW Bloom Redirection
 * Version: 1.0
 * Plugin URI: http://atlanticwave.co/
 * Description: Redirect to a URL on successful Bloom subscription.
 * Author: Atlantic Wave
 * Author URI: http://atlanticwave.co/
 * Requires at least: 4.0
 * Tested up to: 4.0
 *
 * Text Domain: aw-bloom-redirection
 * Domain Path: /lang/
 *
 * @package WordPress
 * @author Atlantic Wave
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'AW_BLOOM_REDIRECTION_PLUGIN_DIR', trailingslashit( dirname(__FILE__) ) );
define( 'AW_BLOOM_REDIRECTION_PLUGIN_URI', plugins_url('', __FILE__) );

function aw_enqueue_scripts() {
	$aw_enable_bloom_redirection = get_option( 'aw_enable_bloom_redirection', '' );
	if ( 'on' == $aw_enable_bloom_redirection ){
		$protocol = is_ssl() ? 'https' : 'http';

		wp_enqueue_script( 'functions', AW_BLOOM_REDIRECTION_PLUGIN_URI . '/js/functions.js', array(), false, true );
		$aw_bloom_redirections = get_option( 'aw_bloom_redirection', array() );

		wp_localize_script( 'functions', 'bloomRedirectionSettings', array(
			'redirections' 	=> $aw_bloom_redirections,
			'site_url' 		=> site_url(),
			'ajaxurl'       => admin_url( 'admin-ajax.php', $protocol )
		) );
	}
}

add_action('wp_enqueue_scripts', 'aw_enqueue_scripts', 99);

function aw_add_bloom_redirection_submenu(){
	add_menu_page( esc_html__( 'Bloom Redirects', 'bloom_redirection' ), esc_html__( 'Bloom Redirects', 'bloom_redirection' ), 'manage_options', 'aw_bloom_redirection_editor', 'aw_display_bloom_redirection_editor', 'dashicons-migrate' );
}
add_action('admin_menu', 'aw_add_bloom_redirection_submenu', 11);

function aw_display_bloom_redirection_editor() {

	if ( !current_user_can( 'manage_options' ) ) {
		wp_die( 'You are not allowed to perform this action.' );
	}

	if ( isset( $_POST['aw_bloom_redirection_editor'] ) && wp_verify_nonce( $_POST['aw_bloom_redirection_editor'], 'aw_display_bloom_redirection_editor' ) ) {
		if ( isset ( $_REQUEST ) && !empty ( $_REQUEST ) && isset( $_REQUEST['action'] ) && !empty ( $_REQUEST['action'] ) && $_REQUEST['action'] == 'asu_social_media_update' ) {
			$bloom_redirection = $_REQUEST['bloom_redirection'];
			update_option( 'aw_bloom_redirection', $bloom_redirection );
			if ( isset( $_REQUEST['enable_bloom_redirection'] ) && 'on' == $_REQUEST['enable_bloom_redirection'] ){
				update_option( 'aw_enable_bloom_redirection', $_REQUEST['enable_bloom_redirection'] );
			}else {
				update_option( 'aw_enable_bloom_redirection', '' );
			}
			echo '<div id="message" class="updated fade"><p><strong>Bloom redirection urls saved.</strong></p></div>';
		}
	} else {
		// show some error message here?
	}

	$aw_enable_bloom_redirection = get_option( 'aw_enable_bloom_redirection', '' );
	$aw_bloom_redirections = get_option( 'aw_bloom_redirection', array() );

	$et_bloom_options = get_option( 'et_bloom_options', array() );

	$bloom_optins = array();
	foreach ($et_bloom_options as $key => $value){
		$is_bloom_optin = strpos($key, 'optin');
		if ($is_bloom_optin === false) {
			continue;
		} else {
			$bloom_optins[$key] = $value;
		}
	}

	?>
	<div class="wrap" id="aw_bloom_redirection_editor">
		<h2>Edit Bloom optin redirects</h2>
		<form method="post" action="admin.php?page=aw_bloom_redirection_editor" enctype="multipart/form-data">
			<input type="hidden" name="action" value="asu_social_media_update">
			<?php wp_nonce_field('aw_display_bloom_redirection_editor', 'aw_bloom_redirection_editor'); ?>
			<h2>Bloom options</h2>
			<p>Add a redirection url for your Bloom optins.</p>
			<table class="form-table">
				<tbody>
				<tr>
					<th scope="row">Enable redirection</th>
					<td><input id="enable_bloom_redirection" type="checkbox" name="enable_bloom_redirection" value="on" <?php if ('on' == $aw_enable_bloom_redirection) { echo 'checked="checked"'; } ?>>
						<label for="text_field">
							<span class="description">Enable or disable redirection. If redirection is disabled, the standard Bloom subscribe action fires.</span>
						</label>
					</td>
				</tr>

				<?php foreach ( $bloom_optins as $option_key => $optin ) { ?>
				<tr>
					<th scope="row"><?php echo $optin['optin_name'] ?></th>
					<td><input id="bloom_redirection_<?php echo $option_key ?>" type="text" name="bloom_redirection[<?php echo $option_key ?>]" value="<?php echo ( isset( $aw_bloom_redirections[$option_key] ) ) ? $aw_bloom_redirections[$option_key] : ''; ?>">
						<label for="text_field">
							<span class="description">Enter the Redirection URL for <?php echo $optin['optin_name'] ?>.</span>
						</label>
					</td>
				</tr>
				<?php } ?>

				</tbody>
			</table>
			<p class="submit">
				<input type="hidden" name="tab" value="">
				<input name="Submit" type="submit" class="button-primary" value="Save Settings">
			</p>
		</form>
	</div>
	<?php
}