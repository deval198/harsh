<?php
/*
 * Plugin Name: Wordpress Contributors
 * Plugin URI: #
 * Description: Plugin Description
 * Version: 1.0
 * Author: Author Name
 * Author URI: #
 * Author Email: #
 * Text Domain: wordpress-contributors
 *
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; /* Exit if it is accessed directly.*/
}


add_action( 'add_meta_boxes', 'wordpress_contributors_meta_box_add' );

function wordpress_contributors_meta_box_add()
{  
	add_meta_box( 'wordpress-contributors-meta-box-id', __( 'Contributors', 'wordpress-contributors' ), 'wordpress_contributors_meta_box_callback', 'post' ,'normal', 'high' );
}


add_action( 'save_post', 'wordpress_contributors_save_contributors_meta' );

function wordpress_contributors_save_contributors_meta( $post_id ) {  
		$is_autosave  = wp_is_post_autosave( $post_id );
		$is_revision  = wp_is_post_revision( $post_id );
		$is_valid_nonce = ( isset( $_POST['wordpress_contributors_nonce'] ) && wp_verify_nonce( $_POST['wordpress_contributors_nonce'], basename( __FILE__ ) ) ) ? 'true' : 'false';
		
		if ( $is_autosave || $is_revision || !$is_valid_nonce ) {
			return;
		}
		
		// If some checkbox is checked, save it as an array in post meta
		if ( !empty( $_POST['wp_cb_contributors'] ) ) {
			update_post_meta( $post_id, 'wp-cb-contributors', $_POST['wp_cb_contributors'] );
		}
		else { // Otherwise just delete it if it is blank value.
			delete_post_meta( $post_id, 'wp-cb-contributors' );
		}
	}


function wordpress_contributors_meta_box_callback($post)
{
	wp_nonce_field( basename( __FILE__ ), 'wordpress_contributors_nonce' );
	$postmeta = maybe_unserialize( get_post_meta( $post->ID, 'wp-cb-contributors', true ) );
		
		
	$blogusers = get_users( 'blog_id=1&orderby=nicename' );
	if ( empty( $blogusers ) ) {
		return;
	}
    ?>
    <p>
		<div id="wp-cb-contributors-all" >
			<ul id="contributors" data-wp-lists="list:meta" >
			<?php
		  	foreach ( $blogusers as $bloguser ) {
				$usr_id = $bloguser->user_login;
				$checked = is_array( $postmeta ) && in_array( $usr_id, $postmeta ) ? ' checked="checked"' : '';
				echo '<li id="wp-cb-contributors-', $usr_id, '"><label for="in-wp-cb-contributors-', $usr_id, '" class="selectit"><input value="', $usr_id, '" type="checkbox" name="wp_cb_contributors[]" id="in-wp-cb-contributors-', $usr_id, '"', $checked, '/> ', $usr_id, "</label></li>";
		  	} 
			?>
		  	</ul>
		</div>
		</p>
<?php
} 

add_filter( 'the_content', 'display_authors_after_content' ); 
 
 function display_authors_after_content( $content ) { 
    if ( is_singular('post')) {
        global $post;	
		
		$postmeta = maybe_unserialize( get_post_meta( $post->ID, 'wp-cb-contributors', true ) );
		
		$authors = "";
		
		if ( empty( $postmeta ) ) {
			return;
		}
				
		$authors .= '<div id="wp-cb-contributors-all" ><ul id="contributorchecklist">';
			
		  	foreach ( $postmeta as $bloguser ) {
								
				$user = get_user_by( 'login', $bloguser );				
								
				$authors .= '<li><a href="' . get_author_posts_url($user->ID) . '">' . get_avatar( $user->ID , 32 ) . '<h4>' . $user->user_login .'</h4></a></li>';
				
		  	} 
			
		  $authors .= 	'</ul></div>';
		
	    $content = $content . $authors;
		
		}

    return $content;
}