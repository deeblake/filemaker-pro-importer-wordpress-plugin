<?php
/**
 * Plugin Name: FileMakerPro Importer
 * Plugin URI: http://deeblake.com/portfolio/filemakerpro-importer-wordpress-plugin
 * Description: Imports FileMaker Pro data from a .tab file, processes uploaded images, and adds image data to posts and postmeta tables
 * Version: 1.0
 * Author: Dee Blake
 * Author URI: http://deeblake.com
 * License: GPL2
 */
 /**
    Copyright 2013  Dee Blake  (email : dee@deeblake.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
include( plugin_dir_path( __FILE__ ) . 'FilemakerProImporter.class.php');

// creates subdirectory in the wp-content/uploads dir
function createDirectory() {
	$fmpImporter = new FilemakerProImporter();
	$fmpImporter->createDirectory();
}

// add an admin menu under "Settings" in the Dashboard
function fmp_plugin_menu() {
	add_options_page(  'FilemakerPro Importer', 'FilemakerPro Importer', 'manage_options', 'fmp-importer-options', 'fmp_plugin_options');
}

// content that displays in the plugins settings page
function fmp_plugin_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	if( @$_POST['action'] == 'update' ) {
		
		$fmpImporter = new FilemakerProImporter();
		
		// check to see if the tab file exists
		if(!$fmpImporter->dataFileExists()) {
			echo '<div id="message" class="updated fade"><p><strong>FilemakerPro tab file not found. Please ensure you have uploaded it to the directory listed below.</strong></p></div>';
		} else {
			// process the tab file and all associated images
			$import_error = $fmpImporter->importFmpData();
			
			if(strlen($import_error) > 0) {
				echo '<div id="message" class="updated fade"><p><strong>'.$fmpImporter->getNumRecordsProcessed().' images processed successfully.<br><br>Data imported, but with errors:'.$import_error.'</strong></p></div>';
			} else {
				echo '<div id="message" class="updated fade"><p><strong>'.$fmpImporter->getNumRecordsProcessed().' images processed successfully.<br><br>Data successfully imported with no errors!</strong></p></div>';
			}
		}
	}
	echo '<div class="wrap">';
	echo '<h2>Filemaker Pro Importer</h2>';
	echo '<p>Steps to import your FilemakerPro data:</p>';
	echo '<p>';
	echo '<ol>';
	echo '<li>Export your data from FilemakerPro. Save as tab delimited, UFT-8. Name the file <i>newexport-utf8.tab</i> (name is case-sensitive).</li>';
	echo '<li>Create a subdirectory named <i>filemaker-pro-uploads</i> in the wp-contents/uploads directory</li>';
	echo '<li>Upload the tab file and images to the <i>filemaker-pro-uploads</i> subdirectory.</li>';
	echo '<li>Click the <i>Process</i> button below.</li>';
	echo '<ol>';
	echo '<form method="post" id="fmp-importer" action="'.$_SERVER['REQUEST_URI'].'">';
	echo '<input name="action" value="update" id="action" type="hidden" />';
	echo '<input type="submit" name="Submit" value="Process" />';
	echo '</form>';
	echo '</div>';
}

// create subdirectory in wp-content/uploads
register_activation_hook( __FILE__, 'createDirectory' );

// create admin menu
add_action( 'admin_menu', 'fmp_plugin_menu' );

/**
 * Add Artist Last and First Name fields to media uploader
 *
 * @param $form_fields array, fields to include in attachment form
 * @param $post object, attachment record in database
 * @return $form_fields, modified form fields
 */
 
function fmp_attachment_field_credit( $form_fields, $post ) {
	$form_fields['fmp-artist-first-name'] = array(
		'label' => 'Artist First Name',
		'input' => 'text',
		'value' => get_post_meta( $post->ID, 'fmp_artist_first_name', true )
	);

	$form_fields['fmp-artist-last-name'] = array(
		'label' => 'Artist Last Name',
		'input' => 'text',
		'value' => get_post_meta( $post->ID, 'fmp_artist_last_name', true )
	);

	return $form_fields;
}

add_filter( 'attachment_fields_to_edit', 'fmp_attachment_field_credit', 10, 2 );

/**
 * Save values of Artist First and Last Name in media uploader
 *
 * @param $post array, the post data for database
 * @param $attachment array, attachment fields from $_POST form
 * @return $post array, modified post data
 */

function fmp_attachment_field_credit_save( $post, $attachment ) {
	if( isset( $attachment['fmp-artist-first-name'] ) )
		update_post_meta( $post['ID'], 'fmp_artist_first_name', $attachment['fmp-artist-first-name'] );

	if( isset( $attachment['fmp-artist-last-name'] ) )
update_post_meta( $post['ID'], 'fmp_artist_last_name', esc_url( $attachment['fmp-artist-last-name'] ) );

	return $post;
}

add_filter( 'attachment_fields_to_save', 'fmp_attachment_field_credit_save', 10, 2 );
?>