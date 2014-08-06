<?php

/**
 * MyGoogle+ Connect
 */

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'mygpconnect.php');
define('ALLOWABLE_PAGE', 'login,register,do_login');

require_once "./global.php";

$lang->load('mygpconnect');

if (!$mybb->settings['mygpconnect_enabled']) {

	header("Location: index.php");
	exit;
	
}

// Registrations are disabled
if ($mybb->settings['disableregs'] == 1) {

	if (!$lang->registrations_disabled) {
		$lang->load("member");
	}
	
	error($lang->registrations_disabled);
	
}

// Load API
require_once MYBB_ROOT . "inc/plugins/MyGoogle+Connect/class_google.php";
$GoogleConnect = new MyGoogle();

// If the user is watching another page, fallback to login
if (!in_array($mybb->input['action'], explode(',', ALLOWABLE_PAGE))) {
	$mybb->input['action'] = 'login';
}

// Begin the authenticating process
if ($mybb->input['action'] == 'login') {

	if ($mybb->user['uid']) {
		error($lang->mygpconnect_error_alreadyloggedin);
	}
	
	$GoogleConnect->authenticate();	
	
}

// Receive the incoming data from Google and evaluate the user
if ($mybb->input['action'] == 'do_login') {
	
	// Already logged in? You should not use this
	if ($mybb->user['uid']) {
		error($lang->mgpconnect_error_alreadyloggedin);
	}
	
	if ($mybb->input['code']) {
		$GoogleConnect->obtain_tokens();
	}
	else {
		$GoogleConnect->authenticate();
	}
	
	if ($GoogleConnect->check_user()) {
	
		$user = $GoogleConnect->get_user();
		
		$process = $GoogleConnect->process($user);
		
		if ($process['error']) {
		
			$errors = $process['data'];
			$mybb->input['action'] = 'register';
			
		}
	}
	else {
		error($lang->mygpconnect_error_noauth);
	}
	
}

// Register the user
if ($mybb->input['action'] == 'register') {
	
	// Already logged in? You should not use this
	if ($mybb->user['uid']) {
		error($lang->mygpconnect_error_alreadyloggedin);
	}
	
	if (!$GoogleConnect->check_user()) {
		$GoogleConnect->authenticate();
	}
	else {
		$user = $GoogleConnect->get_user();
	}
	
	// Came from our reg page
	if ($mybb->request_method == "post") {
	
		$newuser = array();
		$newuser['displayName'] = $mybb->input['username'];
		$newuser['email'] = $mybb->input['email'];
		
		$settingsToAdd = array();
		$settingsToCheck = array(
			"gpavatar",
			"gpsex",
			"gpdetails",
			"gpbio",
			"gpbday",
			"gplocation"
		);
		
		foreach ($settingsToCheck as $setting) {
		
			if ($mybb->input[$setting] == 1) {
				$settingsToAdd[$setting] = 1;
			}
			else {
				$settingsToAdd[$setting] = 0;
			}
			
		}
		
		// Register him
		$user = $GoogleConnect->register($newuser);
		
		// insert options and extra data
		if (!$user['error']) {
			
			$db->update_query('users', $settingsToAdd, 'uid = ' . (int) $user['uid']);
			
			$newUser = array_merge($user, $settingsToAdd);
			$GoogleConnect->sync($newUser);
			
			// Login
			$GoogleConnect->login($user);
			
			// Redirect
			$GoogleConnect->redirect($mybb->input['redUrl'], $lang->sprintf($lang->mygpconnect_redirect_title, $user['username']), $lang->mygpconnect_redirect_registered);
		}
		else {
			$errors = inline_error($user['error']);
		}
	}
	
	if ($errors) {
		$errors = inline_error($errors);
	}
	
	$options = '';	
	$settingsToBuild = array();
	
	// Checking if we want to sync that stuff (admin)
	$settingsToCheck = array(
		"gpavatar",
		"gpbday",
		"gpsex",
		"gpdetails",
		"gpbio",
		"gplocation"
	);
	
	foreach ($settingsToCheck as $setting) {
	
		$tempKey = 'mygpconnect_' . $setting;
		
		if ($mybb->settings[$tempKey]) {
			$settingsToBuild[] = $setting;
		}
		
	}
	
	foreach ($settingsToBuild as $setting) {
	
		$tempKey = 'mygpconnect_settings_' . $setting;
		$checked = " checked=\"checked\"";
		
		$label = $lang->$tempKey;
		$altbg = alt_trow();
		eval("\$options .= \"" . $templates->get('mygpconnect_register_settings_setting') . "\";");
		
	}
		
	// If registration failed, we certainly have some custom inputs, so we have to display them instead of the Google ones
	if($mybb->input['username']) {
		$user['displayName'] = htmlspecialchars_uni($mybb->input['username']);
	}
	
	if($mybb->input['email']) {
		$user['email'] = htmlspecialchars_uni($mybb->input['email']);
	}
	
	$username = "<input type=\"text\" class=\"textbox\" name=\"username\" value=\"{$user['displayName']}\" />";
	$email = "<input type=\"text\" class=\"textbox\" name=\"email\" value=\"{$user['email']}\" />";
	$redirectUrl = "<input type=\"hidden\" name=\"redUrl\" value=\"{$_SERVER['HTTP_REFERER']}\" />";
	
	// Output our page
	eval("\$gpregister = \"" . $templates->get("mygpconnect_register") . "\";");
	output_page($gpregister);
	
}