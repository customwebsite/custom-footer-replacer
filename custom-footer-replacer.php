<?php
/*
Plugin Name: Custom Footer Replacer
Plugin URI: http://www.customwebsite.com.au/pr-checker/
Description: Replaces site-generator text with Powered by Custom Website link
Version: 2.0
Author: Shaun Haddrill
Author URI: http://www.customwebsite.com.au
License: Copyright to Custom Website
*/


/*
echo 'pr: ' . customGooglePageRankChecker::getRank('customwebsite.com.au');
customGooglePageRankChecker::getRank(get_bloginfo('wpurl')); 
*/
add_action('wp_head', 'customwebsite_frontend_scripts');
add_action('wp_footer', 'customwebsite_footer'); 
add_action('login_enqueue_scripts','customwebsite_wplogin_css');
add_action('admin_head','customwebsite_admin_scripts');
function customwebsite_footer(){
	$wpurl = get_bloginfo('wpurl');
	$url="http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$currentPage = customGooglePageRankChecker::getRank($url);
	if ($currentPage >= 1 ){
		echo '<div id="custom-bar" onClick ="hideCustomDiv();" style="text-align:right;z-index:999999;font:10px arial,sans-serif;border-top: 1px solid #B0BAC9;position:fixed;left: 0px; right: 0px; bottom: 0px;width:100%;background:#CFDBEC;height:15px;padding:4px 0 10px;">';
		echo '<a style="color:black;background: url(http://www.customwebsite.com.au/favicon+p.php) center left no-repeat; font:10px arial,sans-serif;color:#878787; display: inline-block; line-height: 16px; padding-left: 20px; text-decoration: none;" href="http://www.customwebsite.com.au" alt="Designed and developed by the Custom Website Team, an international Website Agency offering a wide variety of services.">Custom Website Design</a>';
		echo ' | ';
		echo '<a style="color:black;" href="' . $wpurl . '/wp-admin/">Admin Area</a>';
		echo ' | <span style="float:left;margin:0 10px 0 10px;">Copyright ';
		echo bloginfo('name');
		echo ' ' . date('Y',time());
		echo '</span></div>';
	}else{
		echo '<div id="custom-bar" onClick ="hideCustomDiv();" style="text-align:right;z-index:999999;background:#CFDBEC;font:10px arial,sans-serif;color:#878787;border-top: 1px solid #B0BAC9;position:fixed;left: 0px; right: 0px; bottom: 0px;width:100%;padding-right:10px;">';
		echo 'Powered by Custom Website';
		echo ' | ';
		echo ' <a href="' . $wpurl . '/wp-admin/" style="color:black;background: url(http://www.customwebsite.com.au/favicon-p.php) center left no-repeat;padding:4px 4px 4px 20px;font:10px arial,sans-serif;color:#878787;display: inline-block; line-height: 16px; text-decoration: none;">Admin Area</a>';
		echo ' | <span style="float:left;margin:0 10px 0 10px;">Copyright ';
		echo bloginfo('name');
		echo ' ' . date('Y',time());
		echo '</span></div>';

	}
}

function customwebsite_frontend_scripts(){
	$scripts = '<style type="text/css">';
	$scripts .= '#site-generator{display:none;}';
	$scripts .= '.site-info{display:none;}';
	$scripts .= '#copyright{display:none;}';
	$scripts .= '#site-credits{display:none;}';
	$scripts .= '</style>';
	$scripts .= '<script type="text/javascript">jQuery(document).ready(function($) {
if(navigator.platform == "iPad" || navigator.platform == "iPhone" || navigator.platform == "iPod")
{
$("#custom-bar").css("position", "static");
};
});</script>';
	echo $scripts;
}

function customwebsite_wplogin_css(){
	$css = '<style type="text/css">';
	$css .= 'h1 a{width:300px;background: url("http://www.customwebsite.com.au/logo.php") no-repeat scroll center top transparent;}';
	$css .= '.login h1 a {width:300px;background: url("http://www.customwebsite.com.au/logo.php") no-repeat scroll center top transparent;}';
	$css .= '</style>';
	echo $css;
	}


function customwebsite_admin_scripts(){
	$css = '<style type="text/css">';
	$css .= '#url{display:none;}';
	$css .= '</style>';
	echo $css;
}
// Declare the class
class customGooglePageRankChecker {
	
	// Track the instance
	private static $instance;
	
	// Constructor
	function getRank($page) {
		// Create the instance, if one isn't created yet
		if(!isset(self::$instance)) {
			self::$instance = new self();
		}
		// Return the result
		return self::$instance->check($page);
	}
	
	
	// Convert string to a number
	function stringToNumber($string,$check,$magic) {
		$int32 = 4294967296;  // 2^32
	    $length = strlen($string);
	    for ($i = 0; $i < $length; $i++) {
	        $check *= $magic; 	
	        //If the float is beyond the boundaries of integer (usually +/- 2.15e+9 = 2^31), 
	        //  the result of converting to integer is undefined
	        //  refer to http://www.php.net/manual/en/language.types.integer.php
	        if($check >= $int32) {
	            $check = ($check - $int32 * (int) ($check / $int32));
	            //if the check less than -2^31
	            $check = ($check < -($int32 / 2)) ? ($check + $int32) : $check;
	        }
	        $check += ord($string{$i}); 
	    }
	    return $check;
	}
	
	// Create a url hash
	function createHash($string) {
		$check1 = $this->stringToNumber($string, 0x1505, 0x21);
	    $check2 = $this->stringToNumber($string, 0, 0x1003F);
	
		$factor = 4;
		$halfFactor = $factor/2;

	    $check1 >>= $halfFactor;
	    $check1 = (($check1 >> $factor) & 0x3FFFFC0 ) | ($check1 & 0x3F);
	    $check1 = (($check1 >> $factor) & 0x3FFC00 ) | ($check1 & 0x3FF);
	    $check1 = (($check1 >> $factor) & 0x3C000 ) | ($check1 & 0x3FFF);	

	    $calc1 = (((($check1 & 0x3C0) << $factor) | ($check1 & 0x3C)) << $halfFactor ) | ($check2 & 0xF0F );
	    $calc2 = (((($check1 & 0xFFFFC000) << $factor) | ($check1 & 0x3C00)) << 0xA) | ($check2 & 0xF0F0000 );

	    return ($calc1 | $calc2);
	}
	
	// Create checksum for hash
	function checkHash($hashNumber)
	{
	    $check = 0;
		$flag = 0;

		$hashString = sprintf('%u', $hashNumber) ;
		$length = strlen($hashString);

		for ($i = $length - 1;  $i >= 0;  $i --) {
			$r = $hashString{$i};
			if(1 === ($flag % 2)) {			  
				$r += $r;	 
				$r = (int)($r / 10) + ($r % 10);
			}
			$check += $r;
			$flag ++;	
		}

		$check %= 10;
		if(0 !== $check) {
			$check = 10 - $check;
			if(1 === ($flag % 2) ) {
				if(1 === ($check % 2)) {
					$check += 9;
				}
				$check >>= 1;
			}
		}

		return '7'.$check.$hashString;
	}
	
	function check($page) {

		// Open a socket to the toolbarqueries address, used by Google Toolbar
		$socket = fsockopen("toolbarqueries.google.com", 80, $errno, $errstr, 30);

		// If a connection can be established
		if($socket) {
			// Prep socket headers
			$out = "GET /tbr?client=navclient-auto&ch=".$this->checkHash($this->createHash($page))."&features=Rank&q=info:".$page."&num=100&filter=0 HTTP/1.1\r\n";
			$out .= "Host: toolbarqueries.google.com\r\n";
			$out .= "User-Agent: Mozilla/4.0 (compatible; GoogleToolbar 2.0.114-big; Windows XP 5.1)\r\n";
			$out .= "Connection: Close\r\n\r\n";

			// Write settings to the socket
			fwrite($socket, $out);

			// When a response is received...
			$result = "";
			while(!feof($socket)) {
				$data = fgets($socket, 128);
				$pos = strpos($data, "Rank_");
				if($pos !== false){
					$pagerank = substr($data, $pos + 9);
					$result += $pagerank;
				}
			}
			// Close the connection
			fclose($socket);
			
			// Return the rank!
			return $result;
		}
	}
}
?>