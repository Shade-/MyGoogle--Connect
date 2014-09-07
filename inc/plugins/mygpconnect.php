<?php
/**
 * A bridge between MyBB and Google+, featuring login, registration and more.
 *
 * @package MyGoogle+ Connect
 * @author  Shade <legend_k@live.it>
 * @license http://opensource.org/licenses/mit-license.php MIT license
 * @version 2.1
 */

if (!defined('IN_MYBB')) {
	die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

if (!defined("PLUGINLIBRARY")) {
	define("PLUGINLIBRARY", MYBB_ROOT . "inc/plugins/pluginlibrary.php");
}

function mygpconnect_info()
{
	return array(
		'name' => 'MyGoogle+ Connect',
		'description' => 'Integrates MyBB with Google+, featuring login and registration.',
		'website' => 'https://github.com/Shade-/MyGoogle+-Connect',
		'author' => 'Shade',
		'authorsite' => '',
		'version' => '2.1',
		'compatibility' => '16*,17*,18*',
		'guid' => 'cfcca7b3bd3317058eaec5d1b760c0fe'
	);
}

function mygpconnect_is_installed()
{
	global $cache;
	
	$info = mygpconnect_info();
	$installed = $cache->read("shade_plugins");
	if ($installed[$info['name']]) {
		return true;
	}
}

function mygpconnect_install()
{
	global $db, $PL, $lang, $mybb, $cache;
	
	if (!$lang->mygpconnect) {
		$lang->load('mygpconnect');
	}
	
	if (!file_exists(PLUGINLIBRARY)) {
		flash_message($lang->mygpconnect_pluginlibrary_missing, "error");
		admin_redirect("index.php?module=config-plugins");
	}
	
	$PL or require_once PLUGINLIBRARY;
	
	$PL->settings('mygpconnect', $lang->setting_group_mygpconnect, $lang->setting_group_mygpconnect_desc, array(
		'enabled' => array(
			'title' => $lang->setting_mygpconnect_enable,
			'description' => $lang->setting_mygpconnect_enable_desc,
			'value' => '1'
		),
		'clientid' => array(
			'title' => $lang->setting_mygpconnect_clientid,
			'description' => $lang->setting_mygpconnect_clientid_desc,
			'value' => '',
			'optionscode' => 'text'
		),
		'clientsecret' => array(
			'title' => $lang->setting_mygpconnect_clientsecret,
			'description' => $lang->setting_mygpconnect_clientsecret_desc,
			'value' => '',
			'optionscode' => 'text'
		),
		'apikey' => array(
			'title' => $lang->setting_mygpconnect_apikey,
			'description' => $lang->setting_mygpconnect_apikey_desc,
			'value' => '',
			'optionscode' => 'text'
		),
		'emailcheck' => array(
			'title' => $lang->setting_mygpconnect_emailcheck,
			'description' => $lang->setting_mygpconnect_emailcheck_desc,
			'value' => '1'
		),
		'fastregistration' => array(
			'title' => $lang->setting_mygpconnect_fastregistration,
			'description' => $lang->setting_mygpconnect_fastregistration_desc,
			'value' => '1'
		),
		'usergroup' => array(
			'title' => $lang->setting_mygpconnect_usergroup,
			'description' => $lang->setting_mygpconnect_usergroup_desc,
			'value' => '2',
			'optionscode' => 'text'
		),
		
		// PM delivery
		'passwordpm' => array(
			'title' => $lang->setting_mygpconnect_passwordpm,
			'description' => $lang->setting_mygpconnect_passwordpm_desc,
			'value' => '1'
		),
		'passwordpm_subject' => array(
			'title' => $lang->setting_mygpconnect_passwordpm_subject,
			'description' => $lang->setting_mygpconnect_passwordpm_subject_desc,
			'optionscode' => 'text',
			'value' => $lang->mygpconnect_default_passwordpm_subject
		),
		'passwordpm_message' => array(
			'title' => $lang->setting_mygpconnect_passwordpm_message,
			'description' => $lang->setting_mygpconnect_passwordpm_message_desc,
			'optionscode' => 'textarea',
			'value' => $lang->mygpconnect_default_passwordpm_message
		),
		'passwordpm_fromid' => array(
			'title' => $lang->setting_mygpconnect_passwordpm_fromid,
			'description' => $lang->setting_mygpconnect_passwordpm_fromid_desc,
			'optionscode' => 'text',
			'value' => ''
		),
		
		// Avatar and cover
		'gpavatar' => array(
			'title' => $lang->setting_mygpconnect_gpavatar,
			'description' => $lang->setting_mygpconnect_gpavatar_desc,
			'value' => '1'
		),
		
		// Birthday
		'gpbday' => array(
			'title' => $lang->setting_mygpconnect_gpbday,
			'description' => $lang->setting_mygpconnect_gpbday_desc,
			'value' => '1'
		),
		
		// Location
		'gplocation' => array(
			'title' => $lang->setting_mygpconnect_gplocation,
			'description' => $lang->setting_mygpconnect_gplocation_desc,
			'value' => '1'
		),
		'gplocationfield' => array(
			'title' => $lang->setting_mygpconnect_gplocationfield,
			'description' => $lang->setting_mygpconnect_gplocationfield_desc,
			'optionscode' => 'text',
			'value' => '1'
		),
		
		// Bio
		'gpbio' => array(
			'title' => $lang->setting_mygpconnect_gpbio,
			'description' => $lang->setting_mygpconnect_gpbio_desc,
			'value' => '1'
		),
		'gpbiofield' => array(
			'title' => $lang->setting_mygpconnect_gpbiofield,
			'description' => $lang->setting_mygpconnect_gpbiofield_desc,
			'optionscode' => 'text',
			'value' => '2'
		),
		
		// Name and last name
		'gpdetails' => array(
			'title' => $lang->setting_mygpconnect_gpdetails,
			'description' => $lang->setting_mygpconnect_gpdetails_desc,
			'value' => '0'
		),
		'gpdetailsfield' => array(
			'title' => $lang->setting_mygpconnect_gpdetailsfield,
			'description' => $lang->setting_mygpconnect_gpdetailsfield_desc,
			'optionscode' => 'text',
			'value' => ''
		),
		
		// Sex
		'gpsex' => array(
			'title' => $lang->setting_mygpconnect_gpsex,
			'description' => $lang->setting_mygpconnect_gpsex_desc,
			'value' => '0'
		),
		'gpsexfield' => array(
			'title' => $lang->setting_mygpconnect_gpsexfield,
			'description' => $lang->setting_mygpconnect_gpsexfield_desc,
			'optionscode' => 'text',
			'value' => '3'
		)
	));
	
	// Insert our Google+ columns into the database
	$db->query("ALTER TABLE " . TABLE_PREFIX . "users ADD (
		`gpavatar` int(1) NOT NULL DEFAULT 1,
		`gpbday` int(1) NOT NULL DEFAULT 1,
		`gpsex` int(1) NOT NULL DEFAULT 1,
		`gpdetails` int(1) NOT NULL DEFAULT 1,
		`gpbio` int(1) NOT NULL DEFAULT 1,
		`gplocation` int(1) NOT NULL DEFAULT 1,
		`mygp_uid` varchar(30) NOT NULL DEFAULT 0
		)");
		
	// Insert our templates   
	$dir = new DirectoryIterator(dirname(__FILE__) . '/MyGoogle+Connect/templates');
	$templates = array();
	foreach ($dir as $file) {
		if (!$file->isDot() and !$file->isDir() and pathinfo($file->getFilename(), PATHINFO_EXTENSION) == 'html') {
			$templates[$file->getBasename('.html')] = file_get_contents($file->getPathName());
		}
	}
	
	$PL->templates('mygpconnect', 'MyGoogle+ Connect', $templates);
	
	// Create cache
	$info = mygpconnect_info();
	$shadePlugins = $cache->read('shade_plugins');
	$shadePlugins[$info['name']] = array(
		'title' => $info['name'],
		'version' => $info['version']
	);
	$cache->update('shade_plugins', $shadePlugins);
	
	// Try to update templates
	require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';
	find_replace_templatesets('header_welcomeblock_guest', '#' . preg_quote('{$lang->welcome_register}</a>') . '#i', '{$lang->welcome_register}</a> &mdash; <a href="{$mybb->settings[\'bburl\']}/mygpconnect.php?action=login">{$lang->mygpconnect_login}</a>');
	
}

function mygpconnect_uninstall()
{
	global $db, $PL, $cache, $lang;
	
	if (!$lang->mygpconnect) {
		$lang->load('mygpconnect');
	}
	
	if (!file_exists(PLUGINLIBRARY)) {
		flash_message($lang->mygpconnect_pluginlibrary_missing, "error");
		admin_redirect("index.php?module=config-plugins");
	}
	
	$PL or require_once PLUGINLIBRARY;
	
	// Drop settings
	$PL->settings_delete('mygpconnect');
	
	// Delete our columns
	$db->query("ALTER TABLE " . TABLE_PREFIX . "users DROP `gpavatar`, DROP `gpbday`, DROP `gpdetails`, DROP `gpsex`, DROP `gpbio`, DROP `gplocation`, DROP `mygp_uid`");
	
	// Delete the plugin from cache
	$info = mygpconnect_info();
	$shadePlugins = $cache->read('shade_plugins');
	unset($shadePlugins[$info['name']]);
	$cache->update('shade_plugins', $shadePlugins);
	
	$PL->templates_delete('mygpconnect');
	
	// Try to update templates
	require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';	
	find_replace_templatesets('header_welcomeblock_guest', '#' . preg_quote('&mdash; <a href="{$mybb->settings[\'bburl\']}/mygpconnect.php?action=login">{$lang->mygpconnect_login}</a>') . '#i', '');
	
}

if ($settings['mygpconnect_enabled']) {
	
	// Global
	$plugins->add_hook('global_start', 'mygpconnect_global');
	
	// User CP
	$plugins->add_hook('usercp_menu', 'mygpconnect_usercp_menu', 40);
	$plugins->add_hook('usercp_start', 'mygpconnect_usercp');
	
	// Who's Online
	$plugins->add_hook("fetch_wol_activity_end", "mygpconnect_fetch_wol_activity");
	$plugins->add_hook("build_friendly_wol_location_end", "mygpconnect_build_wol_location");
	
	// Admin CP
	if (defined('IN_ADMINCP')) {
		$plugins->add_hook("admin_page_output_header", "mygpconnect_update");
		$plugins->add_hook("admin_page_output_footer", "mygpconnect_settings_footer");
		
		// Replace text inputs to select boxes dinamically
		$plugins->add_hook("admin_config_settings_change", "mygpconnect_settings_saver");
		$plugins->add_hook("admin_formcontainer_output_row", "mygpconnect_settings_replacer");
	}
	
}

function mygpconnect_global()
{
	
	global $mybb, $lang, $templatelist;
	
	if ($templatelist) {
		$templatelist = explode(',', $templatelist);
	}
	// Fixes common warnings (due to $templatelist being void)
	else {
		$templatelist = array();
	}
	
	if (THIS_SCRIPT == 'mygpconnect.php') {
	
		$templatelist[] = 'mygpconnect_register';
		$templatelist[] = 'mygpconnect_register_settings_setting';
		
	}
	
	if (THIS_SCRIPT == 'usercp.php') {
		$templatelist[] = 'mygpconnect_usercp_menu';
	}
	
	if (THIS_SCRIPT == 'usercp.php' and $mybb->input['action'] == 'mygpconnect') {
	
		$templatelist[] = 'mygpconnect_usercp_settings';
		$templatelist[] = 'mygpconnect_usercp_settings_linkprofile';
		$templatelist[] = 'mygpconnect_usercp_settings_setting';
		
	}
	
	$templatelist = implode(',', array_filter($templatelist));
	
	$lang->load('mygpconnect');
	
}

function mygpconnect_usercp_menu()
{
	
	global $mybb, $templates, $theme, $usercpmenu, $lang, $collapsed, $collapsedimg;
	
	if (!$lang->mygpconnect) {
		$lang->load("mygpconnect");
	}
	
	eval("\$usercpmenu .= \"" . $templates->get('mygpconnect_usercp_menu') . "\";");
}

function mygpconnect_usercp()
{
	
	global $mybb, $lang, $inlinesuccess;
	
	// Load API in certain areas
	if (in_array($mybb->input['action'], array('gplink', 'do_gplink')) or $_SESSION['gplogin'] or ($mybb->input['action'] == 'mygpconnect' and $mybb->request_method == 'post')) {
		require_once MYBB_ROOT . "inc/plugins/MyGoogle+Connect/class_google.php";
		$GoogleConnect = new MyGoogle();
	}

	$settingsToCheck = array(
		"gpavatar",
		"gpbday",
		"gpsex",
		"gpdetails",
		"gpbio",
		"gplocation"
	);
	
	if (!$lang->mygpconnect) {
		$lang->load('mygpconnect');
	}
	
	// Authenticate
	if ($mybb->input['action'] == 'gplink') {
	
		$GoogleConnect->set_fallback('usercp.php?action=do_gplink');
		$GoogleConnect->authenticate();
		
	}
	
	// Link account to his Google's one
	if ($mybb->input['action'] == 'do_gplink') {
		
		// Set the fallback here to ensure the loaded API is the same as in gplink (prevents mismatch_redirect_uri error)
		$GoogleConnect->set_fallback('usercp.php?action=do_gplink');
		$GoogleConnect->obtain_tokens();
		
		if (!$GoogleConnect->check_user()) {
			error($lang->mygpconnect_error_noauth);			
		}
		
		$user = $GoogleConnect->get_user(false);
		
		if ($user) {
			$GoogleConnect->link_user('', $user['id']);
		}
		
		$GoogleConnect->redirect('usercp.php?action=mygpconnect', '', $lang->mygpconnect_success_linked);
	}
	
	// Settings page
	if ($mybb->input['action'] == 'mygpconnect') {
	
		global $db, $lang, $theme, $templates, $headerinclude, $header, $footer, $plugins, $usercpnav;
				
		add_breadcrumb($lang->nav_usercp, 'usercp.php');
		add_breadcrumb($lang->mygpconnect_page_title, 'usercp.php?action=mygpconnect');
		
		// The user is changing his settings
		if ($mybb->request_method == 'post' or $_SESSION['gplogin']) {
		
			if ($mybb->request_method == 'post') {
				verify_post_check($mybb->input['my_post_key']);
			}
			
			// He's unlinking his account
			if ($mybb->input['unlink']) {
			
				$GoogleConnect->unlink_user();
				redirect('usercp.php?action=mygpconnect', $lang->mygpconnect_success_accunlinked, $lang->mygpconnect_success_accunlinked_title);
				
			}
			// He's updating his settings
			else {
									
				$settings = array();
				
				foreach ($settingsToCheck as $setting) {
					
					$settings[$setting] = 0;
					
					if ($mybb->input[$setting] == 1) {
						$settings[$setting] = 1;
					}
					
				}
				
				if ($_SESSION['gplogin']) {
				
					$settings = $_SESSION['gpsettings'];
					
					unset($_SESSION['gplogin'], $_SESSION['gpsettings']);
				
					$GoogleConnect->set_fallback("usercp.php?action=mygpconnect");
					$GoogleConnect->obtain_tokens();
					
				}
				
				if (!$GoogleConnect->check_user()) {
				
					// Store a token in the session, we will check for it in the next call
					$_SESSION['gplogin'] = true;
					
					// We can't store it in the fallback URL because Google wants it to match the ones specified in their Dev Console... we ain't got time for that
					$_SESSION['gpsettings'] = $settings;
					
					$GoogleConnect->set_fallback("usercp.php?action=mygpconnect");
					$GoogleConnect->authenticate();
					
					return;
										
				}
				
				if ($db->update_query('users', $settings, 'uid = ' . (int) $mybb->user['uid'])) {
					
					$newUser = array_merge($mybb->user, $settings);
					$GoogleConnect->sync($newUser, $user);
					
					redirect('usercp.php?action=mygpconnect', $lang->mygpconnect_success_settingsupdated, $lang->mygpconnect_success_settingsupdated_title);
					
				}
			}
		}
		
		$options = '';		
		if ($mybb->user['mygp_uid']) {
		
			// Checking if admins and users want to sync that stuff
			foreach ($settingsToCheck as $setting) {
				
				$tempKey = 'mygpconnect_' . $setting;
				
				if (!$mybb->settings[$tempKey]) {
					continue;
				}
				
				$userSettings[$setting] = 0;
				
				if ($mybb->user[$setting]) {
					$userSettings[$setting] = 1;
				}
				
			}
			
			$text = $lang->setting_mygpconnect_whattosync;
			$unlink = "<input type=\"submit\" class=\"button\" name=\"unlink\" value=\"{$lang->setting_mygpconnect_unlink}\" />";
			
			if ($userSettings) {
			
				foreach ($userSettings as $setting => $value) {
					
					$tempKey = 'mygpconnect_settings_' . $setting;
					
					$checked = '';
					
					if ($value) {
						$checked = " checked=\"checked\"";
					}
					
					$label = $lang->$tempKey;
					$altbg = alt_trow();
					
					eval("\$options .= \"" . $templates->get('mygpconnect_usercp_settings_setting') . "\";");
					
				}
				
			}
			else {
				$text = $lang->setting_mygpconnect_connected;
			}
			
		} else {
		
			$text = $lang->setting_mygpconnect_linkaccount;
			eval("\$options = \"" . $templates->get('mygpconnect_usercp_settings_linkprofile') . "\";");
			
		}
		
		eval("\$content = \"" . $templates->get('mygpconnect_usercp_settings') . "\";");
		output_page($content);
	}
}

function mygpconnect_update()
{
	global $mybb, $db, $cache, $lang;
	
	$file = MYBB_ROOT . "inc/plugins/MyGoogle+Connect/class_update.php";
	
	if (file_exists($file)) {
		require_once $file;
	}
}

/**
 * Displays peekers in settings.
 **/
function mygpconnect_settings_footer()
{
	global $mybb, $db;
	
	if ($mybb->input["action"] == "change" and $mybb->request_method != "post") {
	
		$gid = mygpconnect_settings_gid();
		
		if ($mybb->input["gid"] == $gid or !$mybb->input['gid']) {
		
			// 1.8 has jQuery, not Prototype
			if ($mybb->version_code >= 1700) {
				echo '<script type="text/javascript">
	$(document).ready(function() {
		loadMyGPConnectPeekers();
		loadStars();
	});
	function loadMyGPConnectPeekers()
	{
		new Peeker($(".setting_mygpconnect_passwordpm"), $("#row_setting_mygpconnect_passwordpm_subject"), /1/, true);
		new Peeker($(".setting_mygpconnect_passwordpm"), $("#row_setting_mygpconnect_passwordpm_message"), /1/, true);
		new Peeker($(".setting_mygpconnect_passwordpm"), $("#row_setting_mygpconnect_passwordpm_fromid"), /1/, true);
		new Peeker($(".setting_mygpconnect_gpbio"), $("#row_setting_mygpconnect_gpbiofield"), /1/, true);
		new Peeker($(".setting_mygpconnect_gplocation"), $("#row_setting_mygpconnect_gplocationfield"), /1/, true);
		new Peeker($(".setting_mygpconnect_gpdetails"), $(" #row_setting_mygpconnect_gpdetailsfield"), /1/, true);
		new Peeker($(".setting_mygpconnect_gpsex"), $("#row_setting_mygpconnect_gpsexfield"), /1/, true);
	}
	function loadStars()
	{
		add_star("row_setting_mygpconnect_clientid");
		add_star("row_setting_mygpconnect_clientsecret");
		add_star("row_setting_mygpconnect_apikey");
	}
	</script>';
			}
			else {
				echo '<script type="text/javascript">
	Event.observe(window, "load", function() {
		loadMyGPConnectPeekers();
		loadStars();
	});
	function loadMyGPConnectPeekers()
	{
		new Peeker($$(".setting_mygpconnect_passwordpm"), $("row_setting_mygpconnect_passwordpm_subject"), /1/, true);
		new Peeker($$(".setting_mygpconnect_passwordpm"), $("row_setting_mygpconnect_passwordpm_message"), /1/, true);
		new Peeker($$(".setting_mygpconnect_passwordpm"), $("row_setting_mygpconnect_passwordpm_fromid"), /1/, true);
		new Peeker($$(".setting_mygpconnect_gpbio"), $("row_setting_mygpconnect_gpbiofield"), /1/, true);
		new Peeker($$(".setting_mygpconnect_gplocation"), $("row_setting_mygpconnect_gplocationfield"), /1/, true);
		new Peeker($$(".setting_mygpconnect_gpdetails"), $("row_setting_mygpconnect_gpdetailsfield"), /1/, true);
		new Peeker($$(".setting_mygpconnect_gpsex"), $("row_setting_mygpconnect_gpsexfield"), /1/, true);
	}
	function loadStars()
	{
		add_star("row_setting_mygpconnect_clientid");
		add_star("row_setting_mygpconnect_clientsecret");
		add_star("row_setting_mygpconnect_apikey");
	}
	</script>';
			}
		}
	}
}

/**
 * Gets the gid of MyFacebook Connect settings group.
 **/
function mygpconnect_settings_gid()
{
	global $db;
	
	$query = $db->simple_select("settinggroups", "gid", "name = 'mygpconnect'", array(
		"limit" => 1
	));
	$gid = $db->fetch_field($query, "gid");
	
	return (int) $gid;
}

function mygpconnect_fetch_wol_activity(&$user_activity)
{
    global $mybb;

    // get the base filename
    $split_loc = explode(".php", $user_activity['location']);
    if($split_loc[0] == $user['location'])
    {
        $filename = '';
    }
    else
    {
        $filename = my_substr($split_loc[0], -my_strpos(strrev($split_loc[0]), "/"));
    }

    // get parameters of the URI
    if($split_loc[1])
    {
        $temp = explode("&amp;", my_substr($split_loc[1], 1));
        foreach($temp as $param)
        {
            $temp2 = explode("=", $param, 2);
            $temp2[0] = str_replace("amp;", '', $temp2[0]);
            $parameters[$temp2[0]] = $temp2[1];
        }
    }
    
	// if our plugin is found, store our custom vars in the main $user_activity array
    switch($filename)
    {
        case "mygpconnect":
            if($parameters['action'])
            {
				$user_activity['activity'] = $parameters['action'];
            }
			break;
    }
    
    return $user_activity;
} 

function mygpconnect_build_wol_location(&$plugin_array)
{
    global $db, $lang, $mybb;
    
    $lang->load('mygpconnect');
	
	// let's see what action we are watching
    switch($plugin_array['user_activity']['activity'])
    {
        case "gplogin":
		case "do_gplogin":
            $plugin_array['location_name'] = $lang->mygpconnect_viewing_loggingin;
			break;
		case "gpregister":
            $plugin_array['location_name'] = $lang->mygpconnect_viewing_registering;
            break;
    }
    return $plugin_array;
}

$GLOBALS['replace_custom_fields'] = array('gplocationfield', 'gpbiofield', 'gpdetailsfield', 'gpsexfield');

function mygpconnect_settings_saver()
{
	global $mybb, $page, $replace_custom_fields;

	if ($mybb->request_method == "post" and $mybb->input['upsetting'] and $page->active_action == "settings" and $mybb->input['gid'] == mygpconnect_settings_gid()) {
	
		foreach ($replace_custom_fields as $setting) {
		
			$parentfield = str_replace('field', '', $setting);
			
			$mybb->input['upsetting']['mygpconnect_'.$setting] = $mybb->input['mygpconnect_'.$setting.'_select'];
			
			// Reset parent field if empty
			if (!$mybb->input['upsetting']['mygpconnect_'.$setting]) {
				$mybb->input['upsetting']['mygpconnect_'.$parentfield] = 0;
			}
		}
		
		$mybb->input['upsetting']['mygpconnect_usergroup'] = $mybb->input['mygpconnect_usergroup_select'];
			
	}
}

function mygpconnect_settings_replacer($args)
{
	global $db, $lang, $form, $mybb, $page, $replace_custom_fields;

	if ($page->active_action != "settings" and $mybb->input['action'] != "change" and $mybb->input['gid'] != mygpconnect_settings_gid()) {
		return false;
	}
        
	$query = $db->simple_select('profilefields', 'name, fid');
	
	$profilefields = array('' => '');
	
	while ($field = $db->fetch_array($query)) {
		$profilefields[$field['fid']] = $field['name'];
	}
	$db->free_result($query);
	
	foreach ($replace_custom_fields as $setting) {
	
		if ($args['row_options']['id'] == "row_setting_mygpconnect_".$setting) {
	
			if (!$profilefields) {
				
				$args['content'] = $lang->mygpconnect_select_nofieldsavailable;
				
				continue;
				
			}
			
			$tempKey = 'mygpconnect_'.$setting;
			
			// Replace the textarea with a cool selectbox
			$args['content'] = $form->generate_select_box($tempKey."_select", $profilefields, $mybb->settings[$tempKey]);
			
		}
		
	}
		
	if ($args['row_options']['id'] == "row_setting_mygpconnect_usergroup") {
			
		$tempKey = 'mygpconnect_usergroup';
			
		// Replace the textarea with a cool selectbox
		$args['content'] = $form->generate_group_select($tempKey."_select", array($mybb->settings[$tempKey]));
			
	}
}