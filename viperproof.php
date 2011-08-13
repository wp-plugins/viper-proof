<?php
/*
Plugin Name: ViperProof
Description: Bloggers use this to easily show other forms of 'social proof' very easily on their blog to help other people see that their site is worth reading. Using a simple shortcode, or the widget, users can display: - How many comments their blog has - How many posts they've written - Number of Facebook fans- Number of Twitter followers (taken automatically from account page) - Alexa rank - and Google pagerank.
Author: ViperChill
Author URI: http://www.viperchill.com
Plugin URI: http://www.viperchill.com/wordpress-plugins/
Version: 1.1
*/

register_activation_hook( __FILE__, 'viperproof_activation' );
add_action( 'admin_menu', 'viperproof_menu_options' );
add_action( 'admin_init', 'viperproof_admin' );
add_action( 'init', 'viperproof_init' );
add_action( 'widgets_init', 'viperproof_load_widgets' );

function viperproof_init() {
	$options = get_option( 'viperproof_options' );

	if ( $options['viperchill_credit'] == 'yes' ) {
		add_action('wp_footer', 'viperproof_credit');
	}
	
	$style_url = WP_PLUGIN_URL . '/viper-proof/css/frontend.css';
	$style_file = WP_PLUGIN_DIR . '/viper-proof/css/frontend.css';
	if ( file_exists( $style_file ) ) {
		wp_register_style( 'viperproof_frontend', $style_url );
		wp_enqueue_style( 'viperproof_frontend' );
	}
}

function viperproof_admin() {

	$style_url = WP_PLUGIN_URL . '/viper-proof/css/admin.css';
	$style_file = WP_PLUGIN_DIR . '/viper-proof/css/style.css';
	if ( file_exists( $style_file ) ) {
		wp_register_style( 'viperproof_admin', $style_url );
		wp_enqueue_style( 'viperproof_admin' );
	}
	
	// Updates, etc., sometimes get messy and we need to make sure activation occurs.
	if( !get_option( 'viperproof_options' ) ) {
		viperproof_activation ();
	}
}

function viperproof_menu_options() {
	add_options_page( 'Viperproof', 'Viperproof', 'manage_options', 'Viperproof-settings', 'viperproof_settings', '' );
}

function viperproof_activation(){

	// ViperProof Options
	$options = array(
			'twitter_name' => '',
			'fan_page_id' => '',
			'fan_page_name' => '',
			'display_types' => 'posts,comments',
			'viperchill_credit' => 'yes',
			'icons' => 'yes',
			'refresh_hours' => 24
			);
	add_option( 'viperproof_options', $options );
	
	// ViperProof Statistics
	$stats = array (
			'followers' => '',
			'fans' => '',
			'page_rank' => '',
			'alexa_rank' => '',
			'monthly_visitors' => '',
			'blog_posts' => '',
			'blog_comments' => '',
			'last_update' => ''
			);
	add_option( 'viperproof_stats', $stats );
	
	// Add Shortcode
	if ( function_exists( 'add_shortcode' ) ) {
	    add_shortcode('viperproof', 'viperproof_shortcode');
	}
}

function viperproof_load_widgets () {
	register_widget( 'ViperProof_Widget' );
}

class ViperProof_Widget extends WP_Widget {
	function ViperProof_Widget() {
		$homeurl = get_option('home');
		$widget_ops = array( 'classname' => 'ViperProof', 'description' => __('A ViperChill Plugin that Displays Various Social Statistics.', 'ViperProof') );
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'viperproof-widget' );
		$this->WP_Widget( 'viperproof-widget', __('ViperProof', 'ViperProof'), $widget_ops, $control_ops );
	}
	function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters('widget_title', $instance['title'] );
		echo $before_widget;
		if ( $title )
			echo $before_title . $title . $after_title;
			
		echo viperproof_compile_list("vertical");
		echo $after_widget;
	}
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}
	function form( $instance ) {
		$defaults = array( 'title' => __('ViperProof', 'ViperProof'));
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'hybrid'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>
	<?php
	}
}
function viperproof_credit () {
	echo "<center>ViperProof by <a href='http://www.viperchill.com'>ViperChill</a></center>";
}
function viperproof_shortcode( $atts ){
	extract( shortcode_atts( array(
			'direction' => 'horizontal',
		), $atts ) );
	
	echo viperproof_get_statistics( $direction );
}

function viperproof_update_settings() {
	if($_POST['updateoptions'] == 'Y') {
		// Update Twitter
		$twitter_name = trim($_POST['twittername']);  
		$options['twitter_name'] =  $twitter_name;
		// Update Facebook
		$fan_page_id = trim($_POST['fanpageid']);  
		$options['fan_page_id'] = $fan_page_id;
		// Update Facebook 2   
		$fan_page_name = trim($_POST['fanpagename']); 
		$options['fan_page_name'] = $fan_page_name;
		// Monthly Stats
		$monthly_visitors = trim($_POST['monthlyvisitors']); 
		$stats['monthly_visitors'] = $monthly_visitors;
		// Which data to display.
		$display_types_arr = $_POST['displaytypes'];
		$display_types = '';
		foreach ($display_types_arr as $val) {
			$display_types .= $val . ',';
		}
		$options[ 'display_types' ] = $display_types;
		           
		if ($_POST['link'] == 'yes') { 
			$options['viperchill_credit'] = 'yes';
		} else {
			$options['viperchill_credit'] = 'no';
		}
		if ($_POST['icons'] == "yes") { 
			$options['icons'] = "yes";
		} else {
			$options['icons'] = "no";
		}
		$options [ 'refresh_hours' ] = $_POST[ 'refresh_hours' ];
		
		// Commit update.
		update_option( 'viperproof_options', $options );
		// And for the monthly visitors stats, too.
		update_option( 'viperproof_stats', $stats );
		
		// Inform the user of update. 
		echo '<div class="updated"><p><strong>Options Saved</strong></p></div>';
		
	}
}

function viperproof_settings(){

	echo '<div id="viperproof_main_container">';	
	echo file_get_contents( 'http://www.viperchill.com/rss/plugin_header.php?plugin=' . $plugin_dir );
	
	viperproof_update_settings();
	
	// General Settings
	$homeurl = get_option( 'home' );
	$options = get_option( 'viperproof_options' );
	$stats = get_option( 'viperproof_stats' );
	$plugin_dir = WP_PLUGIN_URL . '/viper-proof/';
	$display_types = split( ',', $options['display_types'] );
	
	// Social Profiles
	$twitter = $options[ 'twitter_name' ];
	$fan_page = $options[ 'fan_page_id' ];
	$fan_page_name = $options[ 'fan_page_name' ];
	
	?>
	<p class="viperproof_big_hint">Use <span style="background-color: #FFD; padding:3px;">[viperproof]</span> to embed your statistics. To align it vertically, use <span style="background-color: #FFD; padding:3px;">[viperproof direction="vertical"]</span>.<br />
	Alternatively, you will find a Widget called 'ViperProof' on your Widgets page which you can also use.</p>

	<h3>Basic Social Statistics</h3>
	<form name="viperproof_form" method="post" action="<?php echo $_SERVER[ 'REQUEST_URI' ]; ?>">
	<input type="hidden" name="updateoptions" value="Y">
	<div class="viperproof_form_element">
		<div class="viperproof_label">
			<img src="<?php echo $plugin_dir . 'images'; ?>/Twitter.png" style="margin-bottom: -12px;" /> Twitter
		</div>
		<p class="viperproof_hint">If you want to show your Twitter follower count, put your Twitter username below.</p>
		<div class="viperproof_input">
			<p>
				@ <input type="text" value=" <?php echo $twitter; ?>" name="twittername" />
			</p>
		</div>
		<div class="viperproof_label">
			<img src="<?php echo $plugin_dir . 'images'; ?>/Traffic.png" style="margin-bottom: -12px;" /> Traffic
		</div>
		<div class="viperproof_input">
			<p class="viperproof_hint">If you want to show your monthly visitor count, give a rough estimate of this data below. We don't actually count your monthly visitors.</p>
			<p><strong>Monthly Uniques</strong>: <input type="text" value="<?php echo $stats['monthly_visitors']; ?>" name="monthlyvisitors" alt="Visitors Icon" /></p>
		</div>
	</div>
	<div class="viperproof_form_element">
		<div class="viperproof_label">
			<img src="<?php echo $plugin_dir . 'images'; ?>/Facebook.png" style="margin-bottom: -12px;" alt="Facebook Icon" /> Facebook
		</div>
		<div class="viperproof_input">
			<p class="viperproof_hint">A good way to figure out what your ID or Fan page name is (if you're struggling), is to go to graph.facebook.com/[your id], and see if that is the correct information.</p>
			<p class="viperproof_hint">Examples of a Fan Page ID include: '6582852303', and 'Viperchill', which both indicate the same page.</p>
			<p>
				<div class="viperproof_label">Facebook ID or Name:</div>
				<div class="viperproof_input">
					<p><strong>http://graph.facebook.com/</strong> <input type="text" value="<?php echo $fan_page; ?>" name="fanpageid" style="width: 100px;"/></p>
				</div>
			</p>
		</div>
	</div>
	<h3>Extra Statistics to Show</h3>
	<div class="displaychoices">
		<p>
			<?php

			echo '<div class="viperproof_form_element">';
			if ( in_array( 'alexa', $display_types ) ) {
				echo '<input type="checkbox" name="displaytypes[]" value="alexa" checked="yes"/>Alexa Rank';
			} else {
				echo '<input type="checkbox" name="displaytypes[]" value="alexa" />Alexa Rank';
			}
			echo '</div><div class="viperproof_form_element">';
			
			if ( in_array( 'page_rank', $display_types ) ) {
				echo '<input type="checkbox" name="displaytypes[]" value="page_rank" checked="yes" />Page Rank';
			} else {
				echo '<input type="checkbox" name="displaytypes[]" value="page_rank" />Page Rank';	
			}
			echo '</div><div class="viperproof_form_element">';
			
			if ( in_array( 'posts', $display_types ) ) {
				echo '<input type="checkbox" name="displaytypes[]" value="posts" checked="yes" />Number of Posts';
			} else {
				echo '<input type="checkbox" name="displaytypes[]" value="posts" />Number of Posts';
			}
			echo '</div><div class="viperproof_form_element">';
			
			if ( in_array('comments', $display_types ) ) {
				echo '<input type="checkbox" name="displaytypes[]" value="comments" checked="yes" />Number of Comments';
			} else {
				echo '<input type="checkbox" name="displaytypes[]" value="comments" />Number of Comments';
			}
			echo '</div>';
			
?>
		</p>
	</div>
	<h3 style="clear: both;">Advanced Options</h3>
	<div class="viperproof_input">
	<p>
	
	<?php
	if( $options[ 'icons' ] == 'yes' ) {
		echo '<input type="checkbox" name="icons" checked="yes" value="yes" />Use Icons';
	} else {
		echo '<input type="checkbox" name="icons" value="yes" />Use Icons';
	}

	echo '<p>';
	if( $options[ 'viperchill_credit' ] == 'yes' ) {
		echo '<input type="checkbox" name="link" checked="yes" value="yes" />Include Credit Link to ViperChill in Footer';
	} else {
		echo '<input type="checkbox" name="link" value="yes" />Include Credit Link to ViperChill in Footer';
	}
	echo '</p>';
	echo '<p>Update stats every <input type="text" name="refresh_hours" value="' . $options[ 'refresh_hours' ] . '" style="width:50px;" maxlength="3" /> hours.</p>';
	?>
	</div>
	<input type="submit" name="Submit" value="Update Options" class="button-primary" />
	</form>
	<div>
	
	<h3>Current ViperProof Statistics</h3> 
	
	<?php
	
	echo viperproof_compile_list( 'vertical' );
	echo '</div>
		</div>';

}

// Get Google Pagerank.
function viperproof_get_pagerank() {
	$googleurl = 'http://toolbarqueries.google.com/search?features=Rank&sourceid=navclient-ff&client=navclient-auto-ff&googleip=O;66.249.81.104;104&ch=' . viperproof_genhash($url) . '&q=info:' . urlencode(get_option( 'home' ));
	if(function_exists('curl_init')) {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $googleurl);
		$out = curl_exec($ch);
		curl_close($ch);
	} else {
		$out = file_get_contents($googleurl);
	}
	$page_rank = number_format(substr($out, 9));
	$potential_ranks = array ( 1,2,3,4,5,6,7,8,9 );
	if ( in_array( $page_rank, $potential_ranks ) ) {
		return $page_rank;
	} else {
		return 0;
	}
}

// Get Facebook Fans
function viperproof_get_fans_by_id($fan_page_id) {
	$fans = 0;
	if ($fan_page_id){
		$graph = json_decode( file_get_contents( 'https://graph.facebook.com/' . $fan_page_id ) );
		$fans = $graph->likes;
	}
	return $fans;
}

// Get Twitter followers.
function viperproof_get_followers($username){
  $x = file_get_contents("http://twitter.com/".$username);
  $doc = new DomDocument;
  @$doc->loadHTML($x);
  $ele = $doc->getElementById('follower_count');
  $innerHTML=preg_replace('/^<[^>]*>(.*)<[^>]*>$/',"\\1", viperproof_DOMElement_getOuterHTML($doc,$ele));
  return $innerHTML;
}

// Get Alexa Rank.
function viperproof_get_alexa() {
	$response = viperproof_get_http_request(sprintf("http://data.alexa.com/data?cli=10&dat=snbamz&url=%s", get_option('home') ));
    if ($response && preg_match('/" TEXT="((\d|\,)+?)"/', $response, $matches) && $matches[1]) {
        $rank = $matches[1];
    }
    return number_format($rank);
}

// Update Stastics.
function viperproof_update_stats() {
	$options = get_option ( 'viperproof_options' );
	$display_types = explode ( ',', $options['display_types'] );
	global $wpdb;
	
	$stats = get_option ( 'viperproof_stats' );
	
	if ( in_array( 'alexa', $display_types) ) {
		$stats[ 'alexa_rank' ] = viperproof_get_alexa();
	}
	if ( trim($options[ 'twitter_name' ]) != '' ) {
		$stats[ 'followers' ] = viperproof_get_followers( $options['twitter_name'] );
	}
	if ( trim( $options[ 'fan_page_id' ] ) != '' ) {
		$stats[ 'fans' ] = viperproof_get_fans_by_id( $options[ 'fan_page_id' ] );
	}
	if ( in_array( 'page_rank', $display_types) ) {
		$stats[ 'page_rank' ] = viperproof_get_pagerank();
	}
	if ( in_array( 'posts' , $display_types)) {
		$numposts = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post'");
		if ($numposts > 0) {
			$numposts = number_format($numposts);
		}
		$stats[ 'blog_posts' ] = $numposts;
	}
	if ( in_array( 'comments', $display_types ) ) {
		$numcomms = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = '1'");
		if (0 < $numcomms) {
			$numcomms = number_format($numcomms);
		}
		$stats[ 'blog_comments' ] = $numcomms;
	}
	update_option( 'viperproof_stats', $stats );
	return $stats;

}

function viperproof_get_statistics () {

	$stats = get_option ( 'viperproof_stats' );
	$last_update = $stats['last_update'];
	if ( !$last_update || trim($last_update) == '' || strtotime( $dateFromDatabase . ' + 0 hours') <= strtotime( 'now' ) ) {
  		$stats = viperproof_update_stats();
	}
	return $stats;
}

function viperproof_compile_list ($direction) {

	$homeurl = get_option('home');
	$images_dir = WP_PLUGIN_URL . '/viper-proof/images/';
	$stats = viperproof_get_statistics();
	$options = get_option( 'viperproof_options' );

	if ($direction != 'vertical') {
		$direction = 'horizontal'; // It's very simple logic ;)
	}
	// Options
	$twitter = $options[ 'twitter_name' ];
	$fan_page = $options[ 'fan_page_id' ];
	$fan_page_name = $options[ 'fan_page_name' ];
	// Statistics
	$monthly_visitors = $stats [ 'monthly_visitors' ];
	$quick_url = 'http://www.facebook.com/' . $fan_page_name;
	$display_types = explode( ',' , $options[ 'display_types' ] );
		
	$icons = $options [ 'icons' ];
	
	if (trim($fan_page) == ''){
		$response = viperproof_get_http_request($quick_url);
        if ($response){
			$matches = explode( 'pid=', $response );
			$closer = explode( '&', $matches[1] );
			$fan_page = $closer[0];
        }
	}
	$viperproof = '<div class="viperproofstats" style="padding-top:5px;">';
	$rank = '';
      
	// -- ALEXA --
	if ( in_array( 'alexa' , $display_types ) ) {
		$viperproof .= "<span style='padding-bottom:5px' class='viperproofalexa'>";
		if ($icons == 'yes') {
			$viperproof.= '<img class="viperproof_icon" src="' . $images_dir . 'Alexa.png" alt="Alexa Icon" />';
		}
		$viperproof .= "<span class='viperproof_stat'><strong>Alexa Rank:</strong> " . $stats[ 'alaxa_rank' ];
		$viperproof .= "</span></span>";
		
		if($direction == "vertical") {
			$viperproof .= "<br/>";
		}
	}
	
	// -- TWITTER --
	 if ( trim($options [ 'twitter_name' ] ) != '' )  {
		$viperproof .= "<span style='padding-bottom:5px' class='viperprooftwitter'>";
		if ( $icons == 'yes' ) {
			$viperproof.= '<img class="viperproof_icon" src="' . $images_dir . 'Twitter.png" alt="Twitter Icon" />';
		}
			$viperproof .= "<span class='viperproof_stat'><strong>Followers:</strong> ";
		if ($twitter) {
			$viperproof .= '<a href="http://www.twitter.com/' . $twitter . '">' . trim( $stats[ 'followers' ] ) . '</a>';
		} else {
			$viperproof .= 'Twitter username not updated yet.';
		}
		$viperproof .= '</span></span>';
		if($direction == 'vertical') {
			$viperproof .= '<br/>';
		}
	}
	
	// -- FACEBOOK --
	if ( trim($options [ 'fan_page_id' ]) != '' ) {
		$viperproof .= "<span style='padding-bottom:5px' class='viperprooffacebook'>";
		if ( $icons == 'yes' ) {
			$viperproof.= '<img class="viperproof_icon" src="' . $images_dir . 'Facebook.png" alt="Facebook Icon" />';
		}
		if ( $fan_page || $fan_page_name ) {
		$viperproof .= '<span class="viperproof_stat"><strong>Fans:</strong> ';
		$viperproof .= '<a href=' . $fan_pageurl . ' >' . $stats[ 'fans' ] . '</a>';
		} else {
			$viperproof .= 'Facebook Fan Page ID not updated yet.';
		}
		$viperproof .= '</span></span>';
		if ( $direction == 'vertical' ) {
			$viperproof .= '<br/>';
		}
	}
	
	// -- PAGERANK --
	if ( in_array( 'page_rank', $display_types ) ) {
		$viperproof .= "<span style='padding-bottom:5px' class='viperproofpagerank'>";
		if ($icons == 'yes') {
			$viperproof.= '<img class="viperproof_icon" src="' . $images_dir . 'Google.png" alt="Pagerank Icon" />';
		}
		$viperproof .= '<span class="viperproof_stat"><strong>Pagerank:</strong> ';
		
		if ( $stats[ 'page_rank' ] ) {
			$viperproof .= $stats[ 'page_rank' ];
		} else {
			$viperproof .= 'none, yet...';
		}
		$viperproof .= '</span></span>';
		if ( $direction == 'vertical' ) 
			$viperproof .= '<br/>';
		}

	// -- NUMBER OF POSTS --
	if ( in_array( 'posts', $display_types ) ) {
		$viperproof .= '<span style="padding-bottom:5px" class="viperproofposts">';
		if ($icons == 'yes') {
			$viperproof .= '<img class="viperproof_icon" src="' . $images_dir . 'Wordpress.png" alt="Wordpress Posts Icon" />';
		}
		$viperproof.= '<span class="viperproof_stat"><strong>Number of Posts:</strong> ' . $stats[ 'blog_posts' ];
		$viperproof .= '</span></span>';
		
		if( $direction == 'vertical' ) {
			$viperproof .= '<br/>';
		}
	}

	// -- NUMBER OF COMMENTS --
	if ( in_array( 'comments', $display_types ) ) {
		$viperproof .= '<span style="padding-bottom:5px" class="viperproofcomments">';
		if ($icons == 'yes') {
			$viperproof.= '<img class="viperproof_icon" src="' . $images_dir . 'Comments.png" alt="Wordpress Comments Icon"  />';
		}
		$viperproof.= '<span class="viperproof_stat"><strong>Number of Comments:</strong> ' . $stats[ 'blog_comments' ];
		$viperproof .= '</span></span>';
		if ( $direction == 'vertical' ) {
			$viperproof .= '<br/>';
		}
	}
	// -- MONTHLYVISTORS --
	if ( in_array( 'monthly_visitors', $display_types ) ) {
		$viperproof .= '<span style="padding-bottom:5px" class="viperproofmonthlyvisitors">';
		if ($icons == 'yes') {
			$viperproof.= '<img class="viperproof_icon" src="' . $images_dir . 'Traffic.png" alt="Traffic Icon" />';
		}
		$viperproof .= '<span class="viperproof_stat"><strong>Monthly Visitors:</strong> ' . $stats[ 'monthly_visitors' ];
		$viperproof .= '</span></span>';
		if( $direction == 'vertical' ) {
			$viperproof .= '<br/>';
		}
	}
	// Close off the list.
	$viperproof .= '</div>';
	// Return content.
	return $viperproof;
}

function viperproof_get_http_request($url) {

        $output = false;
        if ( file_exists( ABSPATH . 'wp-includes/class-snoopy.php' ) ) {
            require_once( ABSPATH . 'wp-includes/class-snoopy.php' );
            $s = new Snoopy();
            $s->fetch( $url );
            if ( $s->status == 200 ) {
                $output = $s->results;
            } 
        } 
        if ( !$output && function_exists( 'wp_remote_fopen' ) ) {
            $output = wp_remote_fopen( $url );
        } 
        if ( !$output && function_exists( 'fsockopen' ) ) {
            $parsed_url = parse_url($url);
            $http_request = 'GET ' . $parsed_url['path'] . ( $parsed_url['query'] ? '?' . $parsed_url['query'] : '' ) . " HTTP/1.0\r\n";
            $http_request .= "Host: " . $parsed_url['host'] . "\r\n";
            $http_request .= 'Content-Type: application/x-www-form-urlencoded; charset=' . get_option( 'blog_charset' ) . "\r\n";
            $http_request .= "Connection: Close\r\n\r\n";
            $response = '';
            if ( false != ( $fs = fsockopen( $parsed_url['host'], 80, $errno, $errstr, 10 ) ) ) {
                fwrite( $fs, $http_request );
                while ( !feof( $fs ) )$response .= fgets( $fs, 1160 );
                fclose( $fs );
                $response = explode( "\r\n\r\n", $response, 2 );
                $output = $response[1];
            } 
        } 
        return $output;
    } 
	
function viperproof_string_getInsertedString( $long_string,$short_string,$is_html=false ){
  if ( $short_string>=strlen($long_string))return false;
  $insertion_length=strlen($long_string)-strlen($short_string);
  for ( $i=0; $i < strlen( $short_string ); ++$i ) {
    if ( $long_string[ $i ] != $short_string[ $i ] ) break;
  }
  $inserted_string = substr ( $long_string, $i, $insertion_length );
  if ( $is_html && $inserted_string[ $insertion_length -1 ] == '<' ){
    $inserted_string = '<' . substr( $inserted_string, 0 , $insertion_length -1 );
  }
  return $inserted_string;
}
function viperproof_DOMElement_getOuterHTML( $document,$element ) {
  $html = $document->saveHTML();
  $element->parentNode->removeChild( $element );
  $html2 = $document->saveHTML();
  return viperproof_string_getInsertedString( $html,$html2,true );
}

//Thanks to http://fusionswift.com/ for this code. 
function viperproof_genhash ($url) {
	$hash = 'Mining PageRank is AGAINST GOOGLE\'S TERMS OF SERVICE. Yes, I\'m talking to you, scammer.';
	$c = 16909125;
	$length = strlen($url);
	$hashpieces = str_split($hash);
	$urlpieces = str_split($url);
	for ($d = 0; $d < $length; $d++) {
		$c = $c ^ (ord($hashpieces[$d]) ^ ord($urlpieces[$d]));
		$c = viperproof_zerofill($c, 23) | $c << 9;
 	}
	return '8' . hexencode($c);
}
function viperproof_zerofill($a, $b) {
	$z = hexdec(80000000);
  	if ($z & $a) {
  		$a = ($a>>1);
		$a &= (~$z);
		$a |= 0x40000000;
		$a = ($a>>($b-1));
	} else {
		$a = ($a>>$b);
	}
	return $a;
}
function viperproof_hexencode($str) {
	$out  = viperproof_hex8(zerofill($str, 24));
	$out .= viperproof_hex8(zerofill($str, 16) & 255);
	$out .= viperproof_hex8(zerofill($str, 8 ) & 255);
	$out .= viperproof_hex8($str & 255);
	return $out;
}
function viperproof_hex8 ($str) {
	$str = dechex($str);
	(strlen($str) == 1 ? $str = '0' . $str: null);
	return $str;
}
?>