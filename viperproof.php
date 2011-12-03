<?php
/*
Plugin Name: ViperProof
Description: Bloggers use this to easily show other forms of 'social proof' very easily on their blog to help other people see that their site is worth reading. Using a simple shortcode, or the widget, users can display: - How many comments their blog has - How many posts they've written - Number of Facebook fans- Number of Twitter followers (taken automatically from account page) - Alexa rank - and Google pagerank.
Author: ViperChill
Author URI: http://www.viperchill.com
Plugin URI: http://www.viperchill.com/wordpress-plugins/
Version: 1.1
*/
add_action('admin_menu', 'menu_options');
register_activation_hook( __FILE__, 'viperproof_activation' );
if ( function_exists( 'add_shortcode' ) ) {
    add_shortcode('viperproof', 'viper_proof_shortcode');
}
if (get_option("link") == "yes")
	add_action('wp_footer', 'viperlink');	
function viperproof_activation(){
add_option("twittername", "", "user twitter name");
add_option("fanpageid", "", "user facebook fanpage id");
add_option("fanpagename", "", "user facebook fanpage name");
add_option("displaytypes", "", "Alexa Rank, Facebook fans, etc.");
add_option("link", "yes", "Credit Link to ViperChill");
add_option("icons", "yes", "Boolean to Use Icons");
add_option("monthlyvisitors", "", "Number of Monthly Visitors");
}
add_action( 'widgets_init', 'viperproof_load_widgets' );
function viperproof_load_widgets() {
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
			
		echo get_statistics("vertical");
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
function viperlink() {
	echo "<center>ViperProof by <a href='http://www.viperchill.com'>ViperChill</a></center>";
}
function viper_proof_shortcode($atts){
extract( shortcode_atts( array(
		'direction' => 'horizontal',
	), $atts ) );

echo get_statistics($direction);
}
function setup_social_proof(){
	$homeurl = get_option('home');

?>
<link href='http://fonts.googleapis.com/css?family=Gruppo' rel='stylesheet' type='text/css'>
<link href='http://fonts.googleapis.com/css?family=Puritan' rel='stylesheet' type='text/css'>
<style type="text/css">
	h2.heading {
	font-family: 'Gruppo', arial, serif;
	text-align: center;
	font-size: 2.4em;
	color: #006993;
	padding: 5px;
	padding-right:50px;
	}
	h2.heading span {
	padding: 5px;
	font-weight: bolder;
	}
	h3{
	font-size: 1.5em;
	font-family: 'Puritan', arial, serif;
	}
	.displaychoices input {
	margin-left: 10px;
	}
	.displaychoices {
	padding-bottom: 5px;
	}
	.viperproofstats img {
	padding-right: 10px;
	height:16px;
	vertical-align: middle;
	}
	.viperproofstats{
	padding-top:5px;
	}
	.viperproofstats .stat {
	padding-top: 5px;
	}

	/*Common Viper Products */
		h2 {
			padding-top: 20px;
		}
	
		#ViperFeed_main_container {
			width: 700px;
			border: 1px solid #DCDCDC;
			margin: 10px;
			padding: 10px;
		}
		
		.ViperFeed_input,.ViperFeed_label {
			margin: 3px;
			display: block;
			width: 95%;
		}
		
		.ViperFeed_form_element {
			float: left;
			width: 49%;
		}
		
		.ViperFeed_label {
			font-weight: bold;
			margin-top: 20px;
		}
		
		.ViperFeed_input {
			margin-left: 20px;
		}
	
	/* End Common Viper Products */
</style>
<div id="ViperFeed_main_container">
	<?php
		$plugin_dir = str_replace("/".basename(__FILE__),"",plugin_basename(__FILE__));
		echo file_get_contents("http://www.viperchill.com/rss/plugin_header.php?plugin=".$plugin_dir);
	?>
	<p style="text-align:center;">Use <span style="background-color: #FFD; padding:3px;">[viperproof]</span> to embed your statistics. To align it vertically, use <span style="background-color: #FFD; padding:3px;">[viperproof direction="vertical"]</span>.<br />
	Alternatively, you will find a Widget called 'ViperProof' on your Widgets page which you can also use.</p>
	<?php  
		if($_POST['updateoptions'] == 'Y') {
			$twittername = trim($_POST['twittername']);  
			update_option('twittername', $twittername);  
			$fanpageid = trim($_POST['fanpageid']);  
			update_option('fanpageid', $fanpageid);       
			$fanpagename = trim($_POST['fanpagename']); 
			update_option('fanpagename', $fanpagename);
			$monthlyvisitors = trim($_POST['monthlyvisitors']); 
			update_option('monthlyvisitors', $monthlyvisitors);         
			$displaytypesarr = $_POST['displaytypes'];      
			while (list ($key,$val) = @each ($displaytypesarr))
				$displaytypes .= $val.",";        
			update_option('displaytypes', $displaytypes);        
			if ($_POST['link'] == "yes") 
				$link = "yes";
			else
				$link = "no";
			update_option('link', $link);         
			if ($_POST['icons'] == "yes") 
				$icons = "yes";
			else
				$icons = "no";
			update_option('icons', $icons); 
			?>  
			<div class="updated"><p><strong><?php _e('Options saved.' ); ?></strong></p></div>  
			<?php }
		$twitter = get_option('twittername');
		$fanpage = get_option('fanpageid');
		$displaytypes = split(",", get_option('displaytypes'));
		$fanpagename = get_option('fanpagename');
	?>  
	<h2>External Social Network Settings</h2>
	<form name="viperproof_form" method="post" action="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>">
	<input type="hidden" name="updateoptions" value="Y">
	<div class="ViperFeed_form_element">
		<div class="ViperFeed_label">
			<img src="<?php echo WP_PLUGIN_URL."/".$plugin_dir."/images" ?>/Twitter.png" style="margin-bottom: -12px;" /> Twitter
		</div>
		<div class="ViperFeed_input">
			<p>
				@ <input type="text" value="<?php echo $twitter ?>" name="twittername" />
			</p>
		</div>
		<div class="ViperFeed_label">
			<img src="<?php echo WP_PLUGIN_URL."/".$plugin_dir."/images" ?>/Traffic.png" style="margin-bottom: -12px;" /> Traffic
		</div>
		<div class="ViperFeed_input">
			<p><strong>Monthly Uniques</strong>: <input type="text" value="<?php echo $monthlyvisitors ?>" name="monthlyvisitors" /></p>
		</div>
	</div>
	<div class="ViperFeed_form_element">
		<div class="ViperFeed_label">
			<img src="<?php echo WP_PLUGIN_URL."/".$plugin_dir."/images" ?>/Facebook.png" style="margin-bottom: -12px;" /> Facebook
		</div>
		<div class="ViperFeed_input">
			<p>
				Type in either your Page Name in the box below, or your Page ID in the box below that. If you know your ID, rather use this
				(it's faster).
			</p>
			<p>
				<div class="ViperFeed_label">Name</div>
				<div class="ViperFeed_input">
					http://www.facebook.com/<input type="text" value="<?php echo $fanpagename ?>" name="fanpagename" style="width: 100px;"/>
				</div>
			</p>
			<p>
				<div class="ViperFeed_label">ID</div>
				<div class="ViperFeed_input">
					(E.g. 6582852303): <input type="text" value="<?php echo $fanpage ?>" name="fanpageid" />
				</div>
			</p>
		</div>
	</div>
	<h2 style="clear: both;">Statistic Display Options</h2>
	<div class="displaychoices">
		<p>
			<?php 
			echo "<div class=\"ViperFeed_form_element\">";
			if (in_array("displayalexa",$displaytypes))
				echo '<input type="checkbox" name="displaytypes[]" value="displayalexa" checked="yes"/>Alexa Rank';
			 else echo '<input type="checkbox" name="displaytypes[]" value="displayalexa" />Alexa Rank';
			
			echo "</div><div class=\"ViperFeed_form_element\">";
			 if (in_array("displaytwitter",$displaytypes)) 
				echo '<input type="checkbox" name="displaytypes[]" value="displaytwitter" checked="yes"/>Twitter Followers';
			 else echo '<input type="checkbox" name="displaytypes[]" value="displaytwitter" />Twitter Followers';
			echo "</div><div class=\"ViperFeed_form_element\">";
			
			if (in_array("displayfacebook",$displaytypes))
			echo '<input type="checkbox" name="displaytypes[]" value="displayfacebook" checked="yes" />Facebook Fans';
			 else echo '<input type="checkbox" name="displaytypes[]" value="displayfacebook" />Facebook Fans';
			echo "</div><div class=\"ViperFeed_form_element\">";
			
			if (in_array("displaypagerank",$displaytypes))
			echo '<input type="checkbox" name="displaytypes[]" value="displaypagerank" checked="yes" />Page Rank';
			else echo '<input type="checkbox" name="displaytypes[]" value="displaypagerank" />Page Rank';
			echo "</div><div class=\"ViperFeed_form_element\">";
			
			if (in_array("displayposts",$displaytypes))
			echo '<input type="checkbox" name="displaytypes[]" value="displayposts" checked="yes" />Number of Posts';
			else echo '<input type="checkbox" name="displaytypes[]" value="displayposts" />Number of Posts';
			echo "</div><div class=\"ViperFeed_form_element\">";
			
			if (in_array("displaycomments",$displaytypes))
			echo '<input type="checkbox" name="displaytypes[]" value="displaycomments" checked="yes" />Number of Comments';
			else echo '<input type="checkbox" name="displaytypes[]" value="displaycomments" />Number of Comments';
			echo "</div><div class=\"ViperFeed_form_element\">";
			if (in_array("displaymonthlyvisitors",$displaytypes))
			echo '<input type="checkbox" name="displaytypes[]" value="displaymonthlyvisitors" checked="yes" />Monthly Visitors';
			else echo '<input type="checkbox" name="displaytypes[]" value="displaymonthlyvisitors" />Monthly Visitors';
			echo "</div>";
			?>
		</p>
	</div>
	<h2 style="clear: both;">Other Display Options</h2>
	<div class="ViperFeed_input">
	<p>
	<?php
	if(get_option("icons") == "yes"){
		echo '<input type="checkbox" name="icons" checked="yes" value="yes" />Use Icons';
	}
	else {
		echo '<input type="checkbox" name="icons" value="yes" />Use Icons';
	}
	?>
	</p>
	<p>
	<?php
	if(get_option("link") == "yes"){
		echo '<input type="checkbox" name="link" checked="yes" value="yes" />Include Credit Link to ViperChill in Footer';
	}else {
		echo '<input type="checkbox" name="link" value="yes" />Include Credit Link to ViperChill in Footer';
	}?>
	</p>
	</div>
	<div style="text-align: center;"><input type="submit" name="Submit" value="Update Options" class="button-primary" /></div>
	</form>
	<div>
	<h2>Current ViperProof Statistics</h2> <?php echo get_statistics("vertical");
	?> </div>
</div><?php }
function get_statistics ($direction) {
		$plugin_dir = str_replace("/".basename(__FILE__),"",plugin_basename(__FILE__));
	$homeurl = get_option('home');

if ($direction != "vertical")
	$direction = "horizontal";
$twitter = get_option('twittername');
$fanpage = get_option('fanpageid');
$monthlyvisitors = get_option('monthlyvisitors');
$fanpagename = get_option('fanpagename');
$quickurl = "http://www.facebook.com/".$fanpagename;
$displaytypes = split(",", get_option('displaytypes'));
$icons = get_option ('icons');
if ($fanpage == ""){
$response = get_http_request($quickurl);
        if ($response){}
         $matches = split('pid=', $response);
         $closer = split("&",$matches[1]);
         $fanpage = $closer[0];
        }
$stats = "<div class='viperproofstats' style='padding-top:5px;'>";
$rank = "";
$response = get_http_request(sprintf("http://data.alexa.com/data?cli=10&dat=snbamz&url=%s",$homeurl));
        if ($response && preg_match('/" TEXT="((\d|\,)+?)"/', $response, $matches) && $matches[1]) {
            $rank = $matches[1];
        }        
// -- ALEXA --
if (in_array("displayalexa",$displaytypes))
{
$stats .= "<span style='padding-bottom:5px' class='viperproofalexa'>";
if ($icons == 'yes')
	$stats.="<img alt=\"Alexa\" style='vertical-align:middle; padding-right:5px; height:16px;' src='".WP_PLUGIN_URL."/".$plugin_dir."/images/Alexa.png' />";
	$stats .= "<span class='stat'><strong>Alexa Rank:</strong> ".number_format_i18n($rank);
$stats .= "</span></span>";
if($direction == "vertical")
	$stats .= "<br/>";
}
// -- TWITTER --
 if (in_array("displaytwitter",$displaytypes)) 
{
$stats .= "<span style='padding-bottom:5px' class='viperprooftwitter'>";
if ($icons == 'yes')
	$stats.="<img style='vertical-align:middle; padding-right:5px; height:16px;' src='".WP_PLUGIN_URL."/".$plugin_dir."/images/Twitter.png' />";
$stats .= "<span class='stat'><strong>Followers:</strong> ";
if ($twitter)
	$stats .= "<a href='http://www.twitter.com/".$twitter."'>".trim(getFollowers($twitter))."</a>";
else $stats .= "Twitter username not updated yet.";
$stats .= "</span></span>";
if($direction == "vertical")
	$stats .= "<br/>";
}
// -- FACEBOOK --
if (in_array("displayfacebook",$displaytypes))
{
	$stats .= "<span style='padding-bottom:5px' class='viperprooffacebook'>";
	if ($icons == 'yes')
		$stats.="<img style='vertical-align:middle; padding-right:5px; height:16px;' src='".WP_PLUGIN_URL."/".$plugin_dir."/images/Facebook.png' />";
	$stats .= "<span class='stat'><strong>Fans:</strong> ";
if ($fanpage){
	$xml = @simplexml_load_file("http://api.facebook.com/restserver.php?method=facebook.fql.query&query=SELECT%20fan_count%20FROM%20page%20WHERE%20page_id=".$fanpage) or die ("too many");
	$fans = $xml->page->fan_count;
	$xml2 = json_decode(file_get_contents("https://graph.facebook.com/".$fanpage));
	$fanpageurl = $xml2->link;
	$stats .= "<a href=".$fanpageurl." >".$fans."</a>";
} else $stats .= "Facebook Fan Page ID not updated yet.";
$stats .= "</span></span>";
	if($direction == "vertical")
	$stats .= "<br/>";
}
// -- PAGERANK --
if (in_array("displaypagerank",$displaytypes))
{
$stats .= "<span style='padding-bottom:5px' class='viperproofpagerank'>";
if ($icons == 'yes')
	$stats.="<img style='vertical-align:middle; padding-right:5px; height:16px;' src='".WP_PLUGIN_URL."/".$plugin_dir."/images/Google.png' />";
$stats .= "<span class='stat'><strong>Pagerank:</strong> ";
if (pagerank($homeurl))
	$stats .= pagerank($homeurl);
else $stats .= "none, yet...";
$stats .= "</span></span>";
if($direction == "vertical")
	$stats .= "<br/>";
}
global $wpdb;
$numposts = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'publish' AND post_type = 'post'");
if (0 < $numposts) $numposts = number_format($numposts); 
$numcomms = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = '1'");
if (0 < $numcomms) $numcomms = number_format($numcomms);	
// -- NUMBER OF POSTS --
if (in_array("displayposts",$displaytypes)) {
$stats .= "<span style='padding-bottom:5px' class='viperproofposts'>";
if ($icons == 'yes')
	$stats.="<img style='vertical-align:middle; padding-right:5px; height:16px;' src='".WP_PLUGIN_URL."/".$plugin_dir."/images/Wordpress.png' />";
$stats.= "<span class='stat'><strong>Number of posts:</strong> ".$numposts;
$stats .= "</span></span>";
if($direction == "vertical")
	$stats .= "<br/>";
}

// -- NUMBER OF COMMENTS --
if (in_array("displaycomments",$displaytypes))
{
$stats .= "<span style='padding-bottom:5px' class='viperproofcomments'>";
if ($icons == 'yes')
	$stats.="<img style='vertical-align:middle; padding-right:5px; height:16px;' src='".WP_PLUGIN_URL."/".$plugin_dir."/images/Comments.png' />";
$stats.= "<span class='stat'><strong>Number of Comments:</strong> ".$numcomms;
$stats .= "</span></span>";
if($direction == "vertical")
	$stats .= "<br/>";
}
// -- MONTHLYVISTORS --
if (in_array("displaymonthlyvisitors",$displaytypes))
{
$stats .= "<span style='padding-bottom:5px' class='viperproofmonthlyvisitors'>";
if ($icons == 'yes')
	$stats.="<img style='vertical-align:middle; padding-right:5px; height:16px;' src='".WP_PLUGIN_URL."/".$plugin_dir."/images/Traffic.png' />";
	$stats .= "<span class='stat'><strong>Monthly Visitors:</strong> ".$monthlyvisitors;
$stats .= "</span></span>";
if($direction == "vertical")
	$stats .= "<br/>";
}
$stats .= "</div>";
return $stats;
}
function get_http_request($url)
    {

        $output = false;
        if (file_exists(ABSPATH . 'wp-includes/class-snoopy.php')) {
            require_once(ABSPATH . 'wp-includes/class-snoopy.php');
            $s = new Snoopy();
            $s->fetch($url);
            if ($s->status == 200) {
                $output = $s->results;
            } 
        } 
        if (!$output && function_exists('wp_remote_fopen')) {
            $output = wp_remote_fopen($url);
        } 
        if (!$output && function_exists('fsockopen')) {
            $parsed_url = parse_url($url);
            $http_request = 'GET ' . $parsed_url['path'] . ($parsed_url['query'] ? '?' . $parsed_url['query'] : '') . " HTTP/1.0\r\n";
            $http_request .= "Host: " . $parsed_url['host'] . "\r\n";
            $http_request .= 'Content-Type: application/x-www-form-urlencoded; charset=' . get_option('blog_charset') . "\r\n";
            $http_request .= "Connection: Close\r\n\r\n";
            $response = '';
            if (false != ($fs = fsockopen($parsed_url['host'], 80, $errno, $errstr, 10))) {
                fwrite($fs, $http_request);
                while (!feof($fs))$response .= fgets($fs, 1160);
                fclose($fs);
                $response = explode("\r\n\r\n", $response, 2);
                $output = $response[1];
            } 
        } 
        return $output;
    } 
	function menu_options(){
	  add_options_page("ViperProof", "ViperProof ", 1, "ViperProof", "setup_social_proof");  
	}
function string_getInsertedString($long_string,$short_string,$is_html=false){
  if($short_string>=strlen($long_string))return false;
  $insertion_length=strlen($long_string)-strlen($short_string);
  for($i=0;$i<strlen($short_string);++$i){
    if($long_string[$i]!=$short_string[$i])break;
  }
  $inserted_string=substr($long_string,$i,$insertion_length);
  if($is_html && $inserted_string[$insertion_length-1]=='<'){
    $inserted_string='<'.substr($inserted_string,0,$insertion_length-1);
  }
  return $inserted_string;
}
function DOMElement_getOuterHTML($document,$element){
if ($element) {
  $html=$document->saveHTML();
  $element->parentNode->removeChild($element);
  $html2=$document->saveHTML();
  return string_getInsertedString($html,$html2,true);
  }
}
function updateFollowers($username) { // void, updates options
	$url = 'http://api.twitter.com/1/users/show.xml?screen_name='. $username;
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	$data = curl_exec($ch);
	curl_close($ch);
	$xml = new SimpleXMLElement($data);
	$count = $xml->followers_count;
	$count = (float) $count;
	$count = number_format($count);
	
	update_option('viperproof_followers', $count);
	update_option('viperproof_followers_update', time());
	update_option('viperproof_last_twitterer', $username);
}
function getFollowers($username){
	$last_update = get_option('viperproof_followers_update');
	$current_time = time();
	$max_difference = 30;
	$last_twitterer = get_option('viperproof_last_twitterer');
	if ((($last_update / 60) + $max_difference) < (time() / 60)) {
		// If the update was made more than some minutes ago,
		updateFollowers($username);
	} else if ($last_twitterer != $username) { 
		// or if the user has changed usernames
		updateFollowers($username);
	}
	$num_followers = get_option('viperproof_followers');
	return $num_followers;
}
//Thanks to http://fusionswift.com/ for this code, lol. 
function genhash ($url) {
	$hash = 'Mining PageRank is AGAINST GOOGLE\'S TERMS OF SERVICE. Yes, I\'m talking to you, scammer.';
	$c = 16909125;
	$length = strlen($url);
	$hashpieces = str_split($hash);
	$urlpieces = str_split($url);
	for ($d = 0; $d < $length; $d++) {
		$c = $c ^ (ord($hashpieces[$d]) ^ ord($urlpieces[$d]));
		$c = zerofill($c, 23) | $c << 9;
 	}
	return '8' . hexencode($c);
}
function zerofill($a, $b) {
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
function curl($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}
function hexencode($str) {
	$out  = hex8(zerofill($str, 24));
	$out .= hex8(zerofill($str, 16) & 255);
	$out .= hex8(zerofill($str, 8 ) & 255);
	$out .= hex8($str & 255);
	return $out;
}
function hex8 ($str) {
	$str = dechex($str);
	(strlen($str) == 1 ? $str = '0' . $str: null);
	return $str;
}
function pagerank($url) {
	$googleurl = 'http://toolbarqueries.google.com/search?features=Rank&sourceid=navclient-ff&client=navclient-auto-ff&googleip=O;66.249.81.104;104&ch=' . genhash($url) . '&q=info:' . urlencode($url);
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
	return substr($out, 9);
}
?>