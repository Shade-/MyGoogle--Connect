<?php

/**
 * MyGoogle+ Connect
 * 
 * Integrates MyBB with Google+, featuring login and registration.
 *
 * @package MyGoogle+ Connect
 * @page Main
 * @author  Shade <legend_k@live.it>
 * @license http://opensource.org/licenses/mit-license.php MIT license
 * @version 1.0
 */

define("IN_MYBB", 1);
define('THIS_SCRIPT', 'mygpconnect.php');
define('ALLOWABLE_PAGE', 'gplogin,gpregister,do_gplogin');

require_once "./global.php";

$lang->load('mygpconnect');

if (!$mybb->settings['mygpconnect_enabled']) {
	header("Location: index.php");
	exit;
}

if(!session_id()) session_start();

// empty configuration
if (CLIENT_ID === '' OR CLIENT_SECRET === '' OR API_KEY === '') error($lang->mygpconnect_error_noconfigfound);

/* API LOAD */
try {
	require_once MYBB_ROOT . "mygpconnect/src/Google_Client.php";
	if($mybb->settings['mygpconnect_emailcheck']) require_once MYBB_ROOT . "mygpconnect/src/contrib/Google_Oauth2Service.php";
	require_once MYBB_ROOT . "mygpconnect/src/contrib/Google_PlusService.php";
} catch(Exception $e) {
	error($lang->sprintf($lang->mygpconnect_error_report, $e->getMessage()));
}
$client = new Google_Client();
$client->setApplicationName($mybb->settings['bbname']." Google Login");
$client->setClientId(CLIENT_ID);
$client->setClientSecret(CLIENT_SECRET);
$client->setRedirectUri(REDIRECT_URI);
$client->setDeveloperKey(API_KEY);
// do not ask every time for permissions
$client->setApprovalPrompt('auto');
$client->setScopes(array("https://www.googleapis.com/auth/plus.login", "https://www.googleapis.com/auth/userinfo.email"));
if($mybb->settings['mygpconnect_emailcheck']) $oauth2 = new Google_Oauth2Service($client);
$oauth = new Google_PlusService($client);
/* END API LOAD */

// start all the magic
if ($mybb->input['action'] == "gplogin") {
	if ($mybb->user['uid']) error($lang->mygpconnect_error_alreadyloggedin);
	mygpconnect_login();
}

// don't stop the magic
if ($mybb->input['action'] == "do_gplogin") {
	
	// user detected, just tell him he his already logged in - at least he's logging in from the UCP
	if ($mybb->user['uid'] AND empty($_SESSION['authcallback'])) error($lang->mygpconnect_error_alreadyloggedin);
	
	if(!empty($mybb->input['code'])) {
		try {
			$client->authenticate();
		} catch(Exception $e) {
			error($lang->sprintf($lang->mygpconnect_error_report, $e->getMessage()));
		}
		$_SESSION['gplus_token'] = $client->getAccessToken();
	} else {
		error($lang->mygpconnect_error_noauth);
	}
	// authenticating and logging in
	$client->setAccessToken($_SESSION['gplus_token']);
	// this user is logging in from the UCP
	if(!empty($_SESSION['authcallback'])) {
		header("Location: " . $_SESSION['authcallback']);
		return;
	}
	if($client->getAccessToken()) {
		// get the data, or at least try to get it
		try {
			$email = array();
			// i know, fetching by two separate calls is a bit slow... but that's the best i could do to fetch both the email and the user's data
			if($mybb->settings['mygpconnect_emailcheck']) $email = (array) $oauth2->userinfo->get();
			$userdata = (array) $oauth->people->get("me");
			$userdata = array_merge($userdata, $email);
		} catch(Exception $e) {
			error($lang->sprintf($lang->mygpconnect_error_report, $e->getMessage()));
		}
		//error("I'm sorry, but at the moment MyGoogle+ Connect isn't capable of logging in or registering you. Please be patient and follow the development checking again this test board frequently over the next days.<br><em>Shade</em>");
		$magic = mygpconnect_run($userdata);
		if ($magic['error']) {
			$errors = $magic['data'];
			$mybb->input['action'] = "gpregister";
		}
	} else {
		error($lang->mygpconnect_error_noauth);
	}
}

// don't stop the magic, again!
if ($mybb->input['action'] == "gpregister") {
	
	// user detected, just tell him he his already logged in
	if ($mybb->user['uid']) {
		error($lang->mygpconnect_error_alreadyloggedin);
	}
	
	// get the user
	$client->setAccessToken($_SESSION['gplus_token']);
	if (!$client->getAccessToken()) {
		error($lang->mygpconnect_error_noauth);
	} else {
		try {
			$email = array();
			// i know, fetching by two separate calls is a bit slow... but that's the best i could do to fetch both the email and the user's data
			if($mybb->settings['mygpconnect_emailcheck']) $email = (array) $oauth2->userinfo->get();
			$userdata = (array) $oauth->people->get("me");
			$userdata = array_merge($userdata, $email);
			// get the user public data
		}
		// user found, but permissions denied
		catch (Exception $e) {
			error($lang->sprintf($lang->mygpconnect_error_report, $e->getMessage()));
		}
	}
	
	// came from our reg page
	if ($mybb->request_method == "post") {
		$newuser = array();
		$newuser['name'] = $mybb->input['username'];
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
			// variable variables. Yay!
			if ($mybb->input[$setting] == 1) {
				$settingsToAdd[$setting] = 1;
			} else {
				$settingsToAdd[$setting] = 0;
			}
		}
		
		// register it
		$user_info = mygpconnect_register($newuser);
		
		// insert options and extra data
		if ($db->update_query('users', $settingsToAdd, 'uid = ' . (int) $user_info['uid']) AND !($user_info['error'])) {
		
			// compatibility with third party plugins which affects registration (MyAlerts for example)
			$plugins->run_hooks("member_do_register_end");
			
			// update on-the-fly that array of data dude!
			$newUser = array_merge($user_info, $settingsToAdd);
			// oh yeah, let's sync!
			mygpconnect_sync($newUser, $userdata);
			
			// login the user normally, and we have finished.	
			$db->delete_query("sessions", "ip='" . $db->escape_string($session->ipaddress) . "' AND sid != '" . $session->sid . "'");
			$newsession = array(
				"uid" => $user_info['uid']
			);
			$db->update_query("sessions", $newsession, "sid='" . $session->sid . "'");
			
			// finally log the user in
			my_setcookie("mybbuser", $user_info['uid'] . "_" . $user_info['loginkey'], null, true);
			my_setcookie("sid", $session->sid, -1, true);
			// redirect the user to where he came from
			if ($mybb->input['redUrl'] AND strpos($mybb->input['redUrl'], "action=gplogin") === false AND strpos($mybb->input['redUrl'], "action=gpregister") === false) {
				$redirect_url = htmlentities($mybb->input['redUrl']);
			} else {
				$redirect_url = "index.php";
			}
			redirect($redirect_url, $lang->mygpconnect_redirect_registered, $lang->sprintf($lang->mygpconnect_redirect_title, $user_info['username']));
		} else {
			$errors = $user_info['data'];
		}
	}
	
	if ($errors) {
		$errors = inline_error($errors);
	}
	
	$options = "";
	
	$settingsToBuild = array(
		"gpavatar"
	);
	
	// checking if we want to sync that stuff (admin)
	$settingsToCheck = array(
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
		// variable variables. Yay!
		$tempKey = 'mygpconnect_settings_' . $setting;
		$checked = " checked=\"checked\"";
		$label = $lang->$tempKey;
		$altbg = alt_trow();
		eval("\$options .= \"" . $templates->get('mygpconnect_register_settings_setting') . "\";");
	}
		
	// if registration failed, we certainly have some custom inputs, so we have to display them instead of the Facebook ones
	if(!empty($mybb->input['username'])) {
		$userdata['name'] = $mybb->input['username'];
	}
	if(!empty($mybb->input['email'])) {
		$userdata['email'] = $mybb->input['email'];
	}
	
	$username = "<input type=\"text\" class=\"textbox\" name=\"username\" value=\"{$userdata['displayName']}\" />";
	$email = "<input type=\"text\" class=\"textbox\" name=\"email\" value=\"{$userdata['email']}\" />";
	$redirectUrl = "<input type=\"hidden\" name=\"redUrl\" value=\"{$_SERVER['HTTP_REFERER']}\" />";
	
	// output our page
	eval("\$gpregister = \"" . $templates->get("mygpconnect_register") . "\";");
	output_page($gpregister);
}

if (!$mybb->input['action']) {
	header("Location: index.php");
	exit;
}