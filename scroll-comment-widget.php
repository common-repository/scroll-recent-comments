<?php
/*
Plugin Name: Scroll Comments Widget
Plugin URI: http://pakhermawan.com/
Description: Show Recent Comment white scrolling down text Like Twiter.
Version: 0.2
Author: Pakhermawan
Author URI: http://pakhermawan.com/
*/

/*
Based on v0.2.3 of the Widget Simple Recent Comments-plugin by Mika Perälä
http://www.raoul.shacknet.nu/

License: GPL
Compatibility: WordPress 2.0 and heigher 

Installation:
Put the scroll-comment-widget.php file in your /wp-content/plugins/ directory
and activate through the administration panel, and then go to the widget panel and
drag it to where you would like to have it!
*/

/*  Copyright pakhermawan http://pakhermawan.com

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
/* Changelog
* Sat Apr 16 2010 - v0.2 (pakhermawan)
*/

function scroll_recent_comments_init() {
	// Check for the required plugin functions. This will prevent fatal
	// errors occurring when you deactivate the dynamic-sidebar plugin.
	if ( !function_exists('register_sidebar_widget') )
		return;
	// This is the function that outputs our little widget
	function scroll_recent_comments($args) {
	  extract($args);
	// Fetch our parameters
	  $options = get_option('scroll_recent_comments');
	  $title = $options['title'];
	  $src_count = $options['src_count'];
	  $src_length = $options['src_length'];
	  $pre_HTML = '<li style=\"overflow:hidden;\">';
	  $post_HTML = '</li><div style=\"clear: both;\"></div>';

	  global $wpdb;
	
	?><script src="<?php bloginfo('url'); ?>/wp-content/plugins/scroll-recent-comments/jquery.vticker.1.4.js" type="text/javascript"></script><?php
	?><script src="<?php bloginfo('url'); ?>/wp-content/plugins/scroll-recent-comments/jquery-1.5.2.js" type="text/javascript"></script><?php
	?><style type="text/css">
	.atas-gambar {float: left;
			display: block;
			vertical-align: middle;
			margin : 2px;
			text-align: left; 
			}
	</style>
	<?php
	// Build the query and fetch the results
	  $sql = "SELECT DISTINCT ID, post_title, post_password, comment_ID, comment_post_ID, comment_author, comment_author_email, comment_date_gmt, comment_approved, comment_type, 
			SUBSTRING(comment_content,1,$src_length) AS com_excerpt 
		FROM $wpdb->comments
		LEFT OUTER JOIN $wpdb->posts ON ($wpdb->comments.comment_post_ID = $wpdb->posts.ID) 
		WHERE comment_approved = '1' AND comment_type = '' AND post_password = '' 
		ORDER BY comment_date_gmt DESC 
		LIMIT $src_count";
	  $comments = $wpdb->get_results($sql);
	// Generate the output string, prepend and append the HTML specified
	$output = $pre_HTML;
	$output .= "<script type=\"text/javascript\"> 
		jQuery(document).ready(function(){
		jQuery('.komenjalan').vTicker({ 
		speed: 500,
		pause: 3000,
		animation: 'fade',
		mousePause: true,
		showItems: 2,
		direction: 'down',
		height:244
			});
		});
		</script><div class=\"komenjalan\"  style=\"margin-left:10px;\"><ul>";
	  if (!empty($comments)) {
	    foreach ($comments as $comment) {
	      // Make a check if we need to print out '...' after the selected
	      // comment text. This needs to be done if the text is longer than
	      // the specified length.
		   $output .="<li><div class=\"atas-gambar\">".get_avatar($comment->comment_author_email, '24')."</div><b><font color=\"red\">" . $comment->comment_author . "</font></b> say :<br>" . strip_tags($comment->com_excerpt) . "... <br><div align=\"right\"><font size=\"1\"><a href=\"" . get_permalink($comment->ID) . "#comment-" . $comment->comment_ID  . "\" title=\"on " . apply_filters('the_title_rss', $comment->post_title) . "\" style=\"margin-bottom: 2px;\">- In This Post - </a></font></div><hr color=\"#3b5988\"></li>";
	    }
	  } else {
	    $output .= "No comments.";
	  }
	  $output .= "</ul>";
	  $output .= $post_HTML;
	  
	  // These lines generate the output
	  echo $before_widget . $before_title . $title . $after_title;
	  echo $output;
	  echo $after_widget;
	}


	// This is the function that outputs the form to let the users edit
	// the widget's parameters.
	function scroll_recent_comments_control() {

	  // Fetch the options, check them and if need be, update the options array
	  $options = $newoptions = get_option('scroll_recent_comments');
	  if ( $_POST["src-submit"] ) {
	    $newoptions['title'] = strip_tags(stripslashes($_POST["src-title"]));
	    $newoptions['src_count'] = (int) $_POST["src_count"];
	    $newoptions['src_length'] = (int) $_POST["src_length"];
	  }
	  if ( $options != $newoptions ) {
	    $options = $newoptions;
	    update_option('scroll_recent_comments', $options);
	  }

	  // Default options to the parameters
	  if ( !$options['src_count'] ) $options['src_count'] = 20;
	  if ( !$options['src_length'] ) $options['src_length'] = 250;

	  $src_count = $options['src_count'];
	  $src_length = $options['src_length'];
	  
	  // Deal with HTML in the parameters
	  $title = htmlspecialchars($options['title'], ENT_QUOTES);

?>
	    <?php _e('Title:'); ?> <input style="width: 450px;" id="src-title" name="src-title" type="text" value="<?php echo $title; ?>" />
            <p style="text-align: left;"><?php _e('Number of comments :'); ?> <input style="width: 20px;" id="src_count" name="src_count" type="text" value="<?php echo $src_count; ?>" /> <br />
   	    <?php _e('Length of comments:'); ?> <input style="width: 20px;" id="src_length" name="src_length" type="text" value="<?php echo $src_length; ?>" /></p>
        <input type="hidden" id="src-submit" name="src-submit" value="1" />
<?php
	 }
	
	// This registers our widget so it appears with the other available
	// widgets and can be dragged and dropped into any active sidebars.
	register_sidebar_widget('Scroll Recent Comments', 'scroll_recent_comments');

	// This registers our optional widget control form. Because of this
	// our widget will have a button that reveals a 520x480 pixel form.
	register_widget_control('Scroll Recent Comments', 'scroll_recent_comments_control', 180, 180);
}
// Run our code later in case this loads prior to any required plugins.
add_action('plugins_loaded', 'scroll_recent_comments_init');

?>
