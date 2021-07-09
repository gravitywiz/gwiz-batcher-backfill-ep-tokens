<?php
/*
 * Plugin Name:  Gravity Wiz Batcher: Easy Passthrough Token Backfiller
 * Plugin URI:   http://gravitywiz.com
 * Description:  Batcher to process adding Easy Passthrough tokens to entries without them.
 * Author:       Gravity Wiz
 * Version:      0.1
 * Author URI:   http://gravitywiz.com
 */

add_action( 'init', 'gwiz_batcher_ep_token_backfiller' );

function gwiz_batcher_ep_token_backfiller() {

	if ( ! is_admin() && ! ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
		return;
	}

	// !!!!!!
	// CUSTOMIZE THE FOLLOWING LIST OF FORM IDs TO BACKFILL
	// !!!!!!
	$form_ids_to_backfill = array(
		'3',
		'4',
	);

	if ( current_user_can( 'manage_options' ) && isset( $_GET['page'] ) && $_GET['page'] === 'gw-batcher-ep-backfill' ) {
		if ( empty( $form_ids_to_backfill ) ) {
			wp_die( '<code>$form_ids_to_backfill</code> needs to be customized in the Easy Passthrough Token Backfiller' );
		}

		if ( ! function_exists( 'gp_easy_passthrough' ) ) {
			wp_die( 'GP Easy Passthrough needs to be activated to use the Token Backfiller.' );
		}
	}

	require_once( plugin_dir_path( __FILE__ ) . 'class-gwiz-batcher.php' );

	new Gwiz_Batcher( array(
		'title'        => 'GW Batcher: EP Backfill',
		'id'           => 'gw-batcher-ep-backfill',
		'size'         => 100,
		'get_items'    => function ( $size, $offset ) use ( $form_ids_to_backfill ) {
			$paging  = array(
				'offset'    => $offset,
				'page_size' => $size,
			);

			$entries = GFAPI::get_entries( $form_ids_to_backfill, array(), null, $paging, $total );

			return array(
				'items' => $entries,
				'total' => $total,
			);
		},
		'process_item' => function ( $entry ) {
			$token = gform_get_meta( $entry['id'], 'fg_easypassthrough_token' );

			if ( $token ) {
				return;
			}

			gp_easy_passthrough()->get_entry_token( $entry );
		},
	) );

}
