<?php
/*
Plugin Name: Google Analytics MU
Plugin URI: https://github.com/foe-services/google-analytics-mu
Description: Collect network-wide Google Analytics statistics and allow site admins to use their own tracking codes
Version: 2.1
Author: Foe Services Labs
Author URI: http://labs.foe-services.de
License: GPL2
*/
?>
<?php
/*  Copyright 2011  Niklas Jonsson  (email : niklas@darturonline.se)

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
?>
<?php

add_action('network_admin_menu', 'ga_mu_plugin_network_menu');
add_action('admin_menu', 'ga_mu_plugin_menu');
add_action('wp_head', 'ga_mu_plugin_add_script_to_head');

define('UAID_OPTION','ga_mu_uaid');
define('MAINDOMAIN_OPTION', 'ga_mu_maindomain');
define('SITE_SPECIFIC_ALLOWED_OPTION','ga_mu_site_specific_allowed');
define('ANONYMIZEIP_ACTIVATED_OPTION','ga_mu_anonymizeip_activated');
define('PAGESPEED_ACTIVATED_OPTION','ga_mu_pagespeed_activated');
define('MAIN_BLOG_ID',1);

if ( !function_exists('ga_mu_plugin_network_menu') ) :
	function ga_mu_plugin_network_menu() {
		add_submenu_page('settings.php', 'Google Analytics', 'Google Analytics', 'manage_network', 'ga-mu-plugin-network-id', 'ga_mu_plugin_network_options');
	}
endif;

if ( !function_exists('ga_mu_plugin_menu') ) :
	function ga_mu_plugin_menu() {
		switch_to_blog(MAIN_BLOG_ID);
		$siteSpecificAllowed = get_option(SITE_SPECIFIC_ALLOWED_OPTION);
		restore_current_blog();
		if (isset($siteSpecificAllowed) && $siteSpecificAllowed != '' && $siteSpecificAllowed != '0') {
			add_options_page('Google Analytics', 'Google Analytics', 'manage_options', 'ga-mu-plugin-id', 'ga_mu_plugin_options');
		}
	}
endif;

if ( !function_exists('ga_mu_plugin_options') ) :
	function ga_mu_plugin_options() {
		
		global $blog_id;
                load_plugin_textdomain('ga-mu-async', null, '/google-analytics-mu/languages/');
		
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.', 'ga-mu-async') );
		}
		
		if (isset($_POST['UAID'])) {
			update_option(UAID_OPTION, preg_replace('/[^a-zA-Z\d\-]/','',$_POST['UAID']));
                        update_option(ANONYMIZEIP_ACTIVATED_OPTION, preg_replace('/[^a-zA-Z\d\-]/','',$_POST['AnonymizeIpActivated']));
                        update_option(PAGESPEED_ACTIVATED_OPTION, preg_replace('/[^a-zA-Z\d\-]/','',$_POST['PageSpeedActivated']));
			?>
			<div id="message" class="updated fade"><p><?php _e('Analytics ID saved.', 'ga-mu-async') ?></p></div>
        <?php }	?>
			    	
		<div class="wrap">
			<h2><?php _e('Google Analytics Statistics', 'ga-mu-async') ?></h2>
			<form name="form" action="" method="post">
			<table style="margin-top: 20px;">			
				<tr>
					<td style="padding-bottom: 18px;"><?php _e('Google Analytics ID', 'ga-mu-async') ?>:</td>
					<td style="padding-bottom: 18px;"><input type="text" id="UAID" name="UAID"
					<?php 
					if ($blog_id == MAIN_BLOG_ID) {
						echo 'disabled="disabled"';
					} ?>
					value="<?php echo get_option(UAID_OPTION); ?>" /> <?php _e('ex. UA-01234567-8', 'ga-mu-async') ?></td>
				</tr>
                                <tr>
                                        <td style="padding-bottom: 18px;"><?php _e('Anonymize IPs', 'ga-mu-async') ?>:</td>
                                        <td style="padding-bottom: 18px;"><input type="checkbox" id="AnonymizeIpActivated" name="AnonymizeIpActivated" value="Activated"
                                        <?php
                                        $anonymizeIp = get_option(ANONYMIZEIP_ACTIVATED_OPTION);
                                        restore_current_blog();
                                        if (isset($anonymizeIp) && $anonymizeIp != '' && $anonymizeIp != '0') {
                                                echo 'checked="checked"';
                                        }
                                        ?>
                                         /> <?php _e('Activated', 'ga-mu-async') ?> <p style="display:inline-block; vertical-align:middle;margin-left:80px;">
                                        <?php _e('If AnonymizeIP is activated all tracked IPs will be saved in shortened form.', 'ga-mu-async')?></td>
                                </tr>
                                <tr>
                                        <td style="padding-bottom: 18px;"><?php _e('Google PageSpeed', 'ga-mu-async') ?>:</td>
                                        <td style="padding-bottom: 18px;"><input type="checkbox" id="PageSpeedActivated" name="PageSpeedActivated" value="Activated"
                                        <?php
                                        $PageSpeed = get_option(PAGESPEED_ACTIVATED_OPTION);
                                        restore_current_blog();
                                        if (isset($PageSpeed) && $PageSpeed != '' && $PageSpeed != '0') {
                                                echo 'checked="checked"';
                                        }
                                        ?>
                                         /> <?php _e('Activated', 'ga-mu-async') ?> <p style="display:inline-block; vertical-align:middle;margin-left:80px;">
                                        <?php _e('Activate to track performance via Google PageSpeed.', 'ga-mu-async')?></td>
                                </tr>
				<tr>
					<td>&nbsp;</td><td><input type="submit" id="submit" name="submit" class="button-primary" value="<?php _e('Save changes', 'ga-mu-async') ?>" /></td>
				</tr>
				</table>
				<p>
					<?php
					if ($blog_id == MAIN_BLOG_ID) {
						_e('As this is the main blog it uses the same ID as the network do. Changing this would change the networkwide ID; that is why it is disabled here.', 'ga-mu-async');
					} ?>
				</p>
			</form>
		</div>
		<div style="margin-top:40px;font-size:0.8em;"><?php _e('Plugin created by', 'ga-mu-async') ?>: <a href="http://www.darturonline.se/ga-mu-async.html" target="_blank">Niklas Jonsson</a></div>
		
		<?php
	}
endif;


if ( !function_exists('ga_mu_plugin_network_options') ) :
	function ga_mu_plugin_network_options() {
	
		load_plugin_textdomain('ga-mu-async', null, '/google-analytics-mu/languages/');
		
		if (!current_user_can('manage_options'))  {
			wp_die( __('You do not have sufficient permissions to access this page.', 'ga-mu-async') );
		}
				
		if (current_user_can('manage_network'))  {
			
			if (isset($_POST['UAIDsuper'])) {
				if (isset($_POST['AllowSiteSpecificAccounts'])) {
					$allowSiteSpecificAccounts = 1;
				}
				else {
					$allowSiteSpecificAccounts = 0;
				}
                                if (isset($_POST['AnonymizeIpActivated'])) {
					$AnonymizeIpActivated = 1;
				}
				else {
					$AnonymizeIpActivated = 0;
				}
                                if (isset($_POST['PageSpeedActivated'])) {
					$AnonymizeIpActivated = 1;
				}
				else {
					$AnonymizeIpActivated = 0;
				}
				switch_to_blog(MAIN_BLOG_ID);
				update_option(UAID_OPTION, preg_replace('/[^a-zA-Z\d\-]/','',$_POST['UAIDsuper']));
                                update_option(MAINDOMAIN_OPTION, preg_replace('/[^a-zA-Z\d\-\.]/','',$_POST['MainDomain']));
                                update_option(SITE_SPECIFIC_ALLOWED_OPTION, preg_replace('/[^a-zA-Z\d\-]/','',$_POST['AllowSiteSpecificAccounts']));
                                update_option(ANONYMIZEIP_ACTIVATED_OPTION, preg_replace('/[^a-zA-Z\d\-]/','',$_POST['AnonymizeIpActivated']));
                                update_option(PAGESPEED_ACTIVATED_OPTION, preg_replace('/[^a-zA-Z\d\-]/','',$_POST['PageSpeedActivated']));
				restore_current_blog();
			?>
			<div id="message" class="updated fade"><p><?php _e('Network settings saved.', 'ga-mu-async') ?></p></div>
			<?php }	} ?>
		
		<div class="wrap">
			<h2><?php _e('Google Analytics Statistics', 'ga-mu-async') ?></h2>
			<form name="form" action="" method="post">
			<table style="margin-top: 20px;">
			<?php
				if (current_user_can('manage_network'))  {
					?>
					<tr>
						<td style="padding-bottom: 18px;"><?php _e('Network Google Analytics ID', 'ga-mu-async') ?>:</td>
						<td style="padding-bottom: 18px;"><input type="text" id="UAIDsuper" name="UAIDsuper" value="<?php 
						switch_to_blog(MAIN_BLOG_ID);
						echo get_option(UAID_OPTION);
						restore_current_blog();
						?>" /> 
						<?php _e('ex. UA-01234567-8', 'ga-mu-async')?></td>
					</tr>
					<tr>
						<td style="padding-bottom: 18px;"><?php _e('Network domain', 'ga-mu-async') ?>:</td>
						<td style="padding-bottom: 18px;"><input type="text" id="MainDomain" name="MainDomain" value="<?php 
						switch_to_blog(MAIN_BLOG_ID);
						echo get_option(MAINDOMAIN_OPTION);
						restore_current_blog();
						?>" /> 
						<?php _e('ex. ".mydomain.com". Obs! start with a dot! This value goes into', 'ga-mu-async')?> _gaq.push(['_setDomainName', 'NETWORK_DOMAIN'])</td>
					</tr>
					<tr>
						<td style="padding-bottom: 18px;"><?php _e('Site specific accounts', 'ga-mu-async') ?>:</td>
						<td style="padding-bottom: 18px;"><input type="checkbox" id="AllowSiteSpecificAccounts" name="AllowSiteSpecificAccounts" value="Allowed"
						<?php
						switch_to_blog(MAIN_BLOG_ID);
						$siteSpecificAllowed = get_option(SITE_SPECIFIC_ALLOWED_OPTION);
						restore_current_blog();
						if (isset($siteSpecificAllowed) && $siteSpecificAllowed != '' && $siteSpecificAllowed != '0') {
							echo 'checked="checked"';
						}
						?>
						 /> <?php _e('Allowed', 'ga-mu-async') ?> <p style="display:inline-block; vertical-align:middle;margin-left:80px;">
						<?php _e('If this is disallowed the Google Analytics settings page will not be visible to site admins.', 'ga-mu-async')?><br>
						<?php _e('That means they will not be able to use their own Google Analytics accounts to track statistics.', 'ga-mu-async')?></p></td>
					</tr>
                                        <tr>
                                                <td style="padding-bottom: 18px;"><?php _e('Anonymize IPs for Network-Tracking', 'ga-mu-async') ?>:</td>
                                                <td style="padding-bottom: 18px;"><input type="checkbox" id="AnonymizeIpActivated" name="AnonymizeIpActivated" value="Activated"
                                                <?php
                                                switch_to_blog(MAIN_BLOG_ID);
                                                $anonymizeIp = get_option(ANONYMIZEIP_ACTIVATED_OPTION);
                                                restore_current_blog();
                                                if (isset($anonymizeIp) && $anonymizeIp != '' && $anonymizeIp != '0') {
                                                        echo 'checked="checked"';
                                                }
                                                ?>
                                                 /> <?php _e('Activated', 'ga-mu-async') ?> <p style="display:inline-block; vertical-align:middle;margin-left:80px;">
                                                <?php _e('This option activates IP-Anonymization for the network domain on the main site and all subsites.', 'ga-mu-async')?><br>
                                                <?php _e('If AnonymizeIP is activated all tracked IPs will be saved in shortened form.', 'ga-mu-async')?></td>
                                        </tr>
                                        <tr>
                                                <td style="padding-bottom: 18px;"><?php _e('Google PageSpeed', 'ga-mu-async') ?>:</td>
                                                <td style="padding-bottom: 18px;"><input type="checkbox" id="PageSpeedActivated" name="PageSpeedActivated" value="Activated"
                                                <?php
                                                switch_to_blog(MAIN_BLOG_ID);
                                                $PageSpeed = get_option(PAGESPEED_ACTIVATED_OPTION);
                                                restore_current_blog();
                                                if (isset($PageSpeed) && $PageSpeed != '' && $PageSpeed != '0') {
                                                        echo 'checked="checked"';
                                                }
                                                ?>
                                                 /> <?php _e('Activated', 'ga-mu-async') ?> <p style="display:inline-block; vertical-align:middle;margin-left:80px;">
                                                <?php _e('Activate to track network-wide performance via Google PageSpeed.', 'ga-mu-async')?></td>
                                        </tr>
				<?php } ?>
				<tr>
					<td>&nbsp;</td><td><input type="submit" id="submit" name="submit" class="button-primary" value="<?php _e('Save changes', 'ga-mu-async') ?>" /></td>
				</tr>
				</table>
			</form>
		</div>
		<div style="margin-top:40px;font-size:0.8em;"><?php _e('Plugin created by', 'ga-mu-async') ?>: <a href="http://www.darturonline.se/ga-mu-async.html" target="_blank">Niklas Jonsson</a></div>
		
		<?php
		
    	
	}
endif;

if ( !function_exists('ga_mu_plugin_add_script_to_head') ) :
	function ga_mu_plugin_add_script_to_head() {
	
		switch_to_blog(MAIN_BLOG_ID);
		$uaidsuper = get_option(UAID_OPTION);
		$maindomain = get_option(MAINDOMAIN_OPTION);
                $anonymizeIpNetwork = get_option(ANONYMIZEIP_ACTIVATED_OPTION);
                $PageSpeedNetwork = get_option(PAGESPEED_ACTIVATED_OPTION);
		$siteSpecificAllowed = get_option(SITE_SPECIFIC_ALLOWED_OPTION);
		restore_current_blog();

		$uaid = get_option(UAID_OPTION);
		$anonymizeIp = get_option(ANONYMIZEIP_ACTIVATED_OPTION);
                $PageSpeed = get_option(PAGESPEED_ACTIVATED_OPTION);
                
		$super = false;
		$user = false;
		
		if (isset($uaidsuper) && $uaidsuper != '' && $uaidsuper != '0') {
			$super = true;
		}
		if (isset($uaid) && $uaid != '' && $uaid != '0') {
			$user = true;
		}
		
		if ($super && $user) {
			if ($uaidsuper == $uaid) {
				$user = false;
			}
		}
		
		if ($user == true && (!isset($siteSpecificAllowed) || $siteSpecificAllowed == '' || $siteSpecificAllowed == '0')) {
			$user = false;
		}
		
		if ($super || $user)
		{
                $prefix = ''
                ?>
                <script type="text/javascript">
                var _gaq = _gaq || [];
                <?php
                if ($super) {
                        ?>
                        _gaq.push(['_setAccount', '<?php echo $uaidsuper ?>']);
                        <?php
                        if ($maindomain)
                        { ?>
                        _gaq.push(['_setDomainName', '<?php echo $maindomain ?>']);
                        <?php
                        } ?>
                        _gaq.push(['_trackPageview']);
                        <?php 
                        if (isset($PageSpeedNetwork) && $PageSpeedNetwork != '' && $PageSpeedNetwork != '0')
                        { ?>
                        _gaq.push(['_trackPageLoadTime']);
                        <?php }
                        if (isset($anonymizeIpNetwork) && $anonymizeIpNetwork != '' && $anonymizeIpNetwork != '0')
                        { ?>
                        _gaq.push(['_gat._anonymizeIp']);
                        <?php
                        }                                        
                        $prefix = 'b.';
                }

                if ($user) {
                        ?>
                        _gaq.push(['<?php echo $prefix ?>_setAccount', '<?php echo $uaid ?>']);
                        _gaq.push(['<?php echo $prefix ?>_trackPageview']);
                        <?php
                        if (isset($PageSpeed) && $PageSpeed != '' && $PageSpeed != '0')
                        { ?>
                            _gaq.push(['<?php echo $prefix ?>_trackPageLoadTime']);
                        <?php }
                        if (isset($anonymizeIp) && $anonymizeIp != '' && $anonymizeIp != '0')
                        { ?>
                        _gaq.push(['<?php echo $prefix ?>_gat._anonymizeIp']);
                        <?php
                        }
                }
                ?>
                (function() {
                var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
                ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
                var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
                  })();
                </script>			
                <?php
		}
	}
endif;
?>