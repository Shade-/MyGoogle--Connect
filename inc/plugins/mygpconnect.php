<?php
/**
 * MyGoogle+ Connect
 * 
 * Integrates MyBB with Google, featuring login and registration.
 *
 * @package MyGoogle+ Connect
 * @author  Shade <legend_k@live.it>
 * @license http://opensource.org/licenses/mit-license.php MIT license
 * @version 1.0
 */

if (!defined('IN_MYBB')) {
	die('Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.');
}

if (!defined("PLUGINLIBRARY")) {
	define("PLUGINLIBRARY", MYBB_ROOT . "inc/plugins/pluginlibrary.php");
}

global $mybb, $settings;

define("CLIENT_ID", $mybb->settings['mygpconnect_clientid']);
define("CLIENT_SECRET", $mybb->settings['mygpconnect_clientsecret']);
define("REDIRECT_URI", $mybb->settings['bburl'] . "/mygpconnect.php?action=do_gplogin");
define("API_KEY", $mybb->settings['mygpconnect_apikey']);

function mygpconnect_info()
{
	return array(
		'name' => 'MyGoogle+ Connect',
		'description' => 'Integrates MyBB with Google+, featuring login and registration.',
		'website' => 'https://github.com/Shade-/MyGoogle+-Connect',
		'author' => 'Shade',
		'authorsite' => 'http://www.idevicelab.net/forum',
		'version' => '1.0',
		'compatibility' => '16*',
		'guid' => ''
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
	
	$PL->settings('mygpconnect', $lang->mygpconnect_settings, $lang->mygpconnect_settings_desc, array(
		'enabled' => array(
			'title' => $lang->mygpconnect_settings_enable,
			'description' => $lang->mygpconnect_settings_enable_desc,
			'value' => '1'
		),
		'clientid' => array(
			'title' => $lang->mygpconnect_settings_clientid,
			'description' => $lang->mygpconnect_settings_clientid_desc,
			'value' => '',
			'optionscode' => 'text'
		),
		'clientsecret' => array(
			'title' => $lang->mygpconnect_settings_clientsecret,
			'description' => $lang->mygpconnect_settings_clientsecret_desc,
			'value' => '',
			'optionscode' => 'text'
		),
		'apikey' => array(
			'title' => $lang->mygpconnect_settings_apikey,
			'description' => $lang->mygpconnect_settings_apikey_desc,
			'value' => '',
			'optionscode' => 'text'
		),
		'emailcheck' => array(
			'title' => $lang->mygpconnect_settings_emailcheck,
			'description' => $lang->mygpconnect_settings_emailcheck_desc,
			'value' => '1'
		),
		'fastregistration' => array(
			'title' => $lang->mygpconnect_settings_fastregistration,
			'description' => $lang->mygpconnect_settings_fastregistration_desc,
			'value' => '1'
		),
		'usergroup' => array(
			'title' => $lang->mygpconnect_settings_usergroup,
			'description' => $lang->mygpconnect_settings_usergroup_desc,
			'value' => '2',
			'optionscode' => 'text'
		),
		'passwordpm' => array(
			'title' => $lang->mygpconnect_settings_passwordpm,
			'description' => $lang->mygpconnect_settings_passwordpm_desc,
			'value' => '1'
		),
		'passwordpm_subject' => array(
			'title' => $lang->mygpconnect_settings_passwordpm_subject,
			'description' => $lang->mygpconnect_settings_passwordpm_subject_desc,
			'optionscode' => 'text',
			'value' => $lang->mygpconnect_default_passwordpm_subject
		),
		'passwordpm_message' => array(
			'title' => $lang->mygpconnect_settings_passwordpm_message,
			'description' => $lang->mygpconnect_settings_passwordpm_message_desc,
			'optionscode' => 'textarea',
			'value' => $lang->mygpconnect_default_passwordpm_message
		),
		'passwordpm_fromid' => array(
			'title' => $lang->mygpconnect_settings_passwordpm_fromid,
			'description' => $lang->mygpconnect_settings_passwordpm_fromid_desc,
			'optionscode' => 'text',
			'value' => ''
		),
		// avatar and cover
		'gpavatar' => array(
			'title' => $lang->mygpconnect_settings_gpavatar,
			'description' => $lang->mygpconnect_settings_gpavatar_desc,
			'value' => '1'
		),
		// birthday
		'gpbday' => array(
			'title' => $lang->mygpconnect_settings_gpbday,
			'description' => $lang->mygpconnect_settings_gpbday_desc,
			'value' => '1'
		),
		// location
		'gplocation' => array(
			'title' => $lang->mygpconnect_settings_gplocation,
			'description' => $lang->mygpconnect_settings_gplocation_desc,
			'value' => '1'
		),
		'gplocationfield' => array(
			'title' => $lang->mygpconnect_settings_gplocationfield,
			'description' => $lang->mygpconnect_settings_gplocationfield_desc,
			'optionscode' => 'text',
			'value' => '1'
		),
		// bio
		'gpbio' => array(
			'title' => $lang->mygpconnect_settings_gpbio,
			'description' => $lang->mygpconnect_settings_gpbio_desc,
			'value' => '1'
		),
		'gpbiofield' => array(
			'title' => $lang->mygpconnect_settings_gpbiofield,
			'description' => $lang->mygpconnect_settings_gpbiofield_desc,
			'optionscode' => 'text',
			'value' => '2'
		),
		// name and last name
		'gpdetails' => array(
			'title' => $lang->mygpconnect_settings_gpdetails,
			'description' => $lang->mygpconnect_settings_gpdetails_desc,
			'value' => '0'
		),
		'gpdetailsfield' => array(
			'title' => $lang->mygpconnect_settings_gpdetailsfield,
			'description' => $lang->mygpconnect_settings_gpdetailsfield_desc,
			'optionscode' => 'text',
			'value' => ''
		),
		// sex - does nothing atm!
		'gpsex' => array(
			'title' => $lang->mygpconnect_settings_gpsex,
			'description' => $lang->mygpconnect_settings_gpsex_desc,
			'value' => '0'
		),
		'gpsexfield' => array(
			'title' => $lang->mygpconnect_settings_gpsexfield,
			'description' => $lang->mygpconnect_settings_gpsexfield_desc,
			'optionscode' => 'text',
			'value' => '3'
		)
	));
	
	// insert our Google+ columns into the database
	$db->query("ALTER TABLE " . TABLE_PREFIX . "users ADD (
		`gpavatar` int(1) NOT NULL DEFAULT 1,
		`gpbday` int(1) NOT NULL DEFAULT 1,
		`gpsex` int(1) NOT NULL DEFAULT 1,
		`gpdetails` int(1) NOT NULL DEFAULT 1,
		`gpbio` int(1) NOT NULL DEFAULT 1,
		`gplocation` int(1) NOT NULL DEFAULT 1,
		`mygp_uid` varchar(30) NOT NULL DEFAULT 0
		)");
	
	// Euantor's templating system	   
	$dir = new DirectoryIterator(dirname(__FILE__) . '/MyGoogle+Connect/templates');
	$templates = array();
	foreach ($dir as $file) {
		if (!$file->isDot() AND !$file->isDir() AND pathinfo($file->getFilename(), PATHINFO_EXTENSION) == 'html') {
			$templates[$file->getBasename('.html')] = file_get_contents($file->getPathName());
		}
	}
	
	$PL->templates('mygpconnect', 'MyGoogle+ Connect', $templates);
	
	// create cache
	$info = mygpconnect_info();
	$shadePlugins = $cache->read('shade_plugins');
	$shadePlugins[$info['name']] = array(
		'title' => $info['name'],
		'version' => $info['version']
	);
	$cache->update('shade_plugins', $shadePlugins);
	
	require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';
	
	find_replace_templatesets('header_welcomeblock_guest', '#' . preg_quote('{$lang->welcome_register}</a>') . '#i', '{$lang->welcome_register}</a> &mdash; <a href="{$mybb->settings[\'bburl\']}/mygpconnect.php?action=gplogin">{$lang->mygpconnect_login}</a>');
	
	rebuild_settings();
	
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
	
	$PL->settings_delete('mygpconnect');
	
	// delete our Google+ columns
	$db->query("ALTER TABLE " . TABLE_PREFIX . "users DROP `gpavatar`, DROP `gpbday`, DROP `gpdetails`, DROP `gpsex`, DROP `gpbio`, DROP `gplocation`, DROP `mygp_uid`");
	
	$info = mygpconnect_info();
	// delete the plugin from cache
	$shadePlugins = $cache->read('shade_plugins');
	unset($shadePlugins[$info['name']]);
	$cache->update('shade_plugins', $shadePlugins);
	
	$PL->templates_delete('mygpconnect');
	
	require_once MYBB_ROOT . 'inc/adminfunctions_templates.php';
	
	find_replace_templatesets('header_welcomeblock_guest', '#' . preg_quote('&mdash; <a href="{$mybb->settings[\'bburl\']}/mygpconnect.php?action=gplogin">{$lang->mygpconnect_login}</a>') . '#i', '');
	
	// rebuild settings*/
	rebuild_settings();
}

/**
 * Main function which logins or registers any kind of gplus user, provided a valid ID.
 * 
 * @param array The user data containing all the information which are parsed and inserted into the database.
 * @param boolean (optional) Whether to simply link the profile to gp or not. Default to false.
 * @return boolean True if successful, false if unsuccessful.
 **/

function mygpconnect_run($userdata, $justlink = false)
{
	
	global $mybb, $db, $session, $lang;
	
	$user = $userdata;
	
	// verified only?
	if ($mybb->settings['mygpconnect_verifiedonly']) {
		if ($userdata['verified'] == false) {
			error($lang->mygpconnect_error_verifiedonly);
		}
	}
	
	// See if this user is already present in our database
	if (!$justlink) {
		$query = $db->simple_select("users", "*", "mygp_uid = {$user['id']}");
		$gplusID = $db->fetch_array($query);
	}
	
	// this user hasn't a linked-to-gplus account yet
	if (!$gplusID OR $justlink) {
		// link the gplus ID to our user if found, searching for the same email
		if ($user['email']) {
			$query = $db->simple_select("users", "*", "email='{$user['email']}'");
			$registered = $db->fetch_array($query);
		}
		// this user is already registered with us, just link its account with his gplus and log him in
		if ($registered OR $justlink) {
			if ($justlink) {
				$db->update_query("users", array(
					"mygp_uid" => $user['id']
				), "uid = {$mybb->user['uid']}");
				return;
			}
			$db->update_query("users", array(
				"mygp_uid" => $user['id']
			), "email = '{$user['email']}'");
			$db->delete_query("sessions", "ip='" . $db->escape_string($session->ipaddress) . "' AND sid != '" . $session->sid . "'");
			$newsession = array(
				"uid" => $registered['uid']
			);
			$db->update_query("sessions", $newsession, "sid='" . $session->sid . "'");
			
			// let it sync, let it sync
			mygpconnect_sync($registered, $user);
			
			my_setcookie("mybbuser", $registered['uid'] . "_" . $registered['loginkey'], null, true);
			my_setcookie("sid", $session->sid, -1, true);
			
			// redirect
			if ($_SERVER['HTTP_REFERER'] AND strpos($_SERVER['HTTP_REFERER'], "action=gplogin") === false) {
				$redirect_url = htmlentities($_SERVER['HTTP_REFERER']);
			} else {
				$redirect_url = "index.php";
			}
			redirect($redirect_url, $lang->mygpconnect_redirect_loggedin, $lang->sprintf($lang->mygpconnect_redirect_title, $registered['username']));
		}
		// this user isn't registered with us, so we have to register it
		else {
			
			// if we want to let the user choose some infos, then pass the ball to our custom page			
			if (!$mybb->settings['mygpconnect_fastregistration']) {
				header("Location: mygpconnect.php?action=gpregister");
				return;
			}
			
			$newUserData = mygpconnect_register($user);
			if ($newUserData['error']) {
				return $newUserData;
			} else {
				// enable all options and sync
				$newUserDataSettings = array(
					"gpavatar" => 1,
					"gpbday" => 1,
					"gpsex" => 1,
					"gpdetails" => 1,
					"gpbio" => 1,
					"gplocation" => 1
				);
				$newUserData = array_merge($newUserData, $newUserDataSettings);
				mygpconnect_sync($newUserData, $user);
				// after registration we have to log this new user in
				my_setcookie("mybbuser", $newUserData['uid'] . "_" . $newUserData['loginkey'], null, true);
				
				if ($_SERVER['HTTP_REFERER'] AND strpos($_SERVER['HTTP_REFERER'], "action=gplogin") === false AND strpos($_SERVER['HTTP_REFERER'], "action=do_gplogin") === false) {
					$redirect_url = htmlentities($_SERVER['HTTP_REFERER']);
				} else {
					$redirect_url = "index.php";
				}
				
				redirect($redirect_url, $lang->mygpconnect_redirect_registered, $lang->sprintf($lang->mygpconnect_redirect_title, $user['name']));
			}
		}
	}
	// this user has already a linked-to-gplus account, just log him in and update session
	else {
		$db->delete_query("sessions", "ip='" . $db->escape_string($session->ipaddress) . "' AND sid != '" . $session->sid . "'");
		$newsession = array(
			"uid" => $gplusID['uid']
		);
		$db->update_query("sessions", $newsession, "sid='" . $session->sid . "'");
		
		// eventually sync data
		mygpconnect_sync($gplusID, $user);
		
		// finally log the user in
		my_setcookie("mybbuser", $gplusID['uid'] . "_" . $gplusID['loginkey'], null, true);
		my_setcookie("sid", $session->sid, -1, true);
		// redirect the user to where he came from
		if ($_SERVER['HTTP_REFERER'] AND strpos($_SERVER['HTTP_REFERER'], "action=gplogin") === false) {
			$redirect_url = htmlentities($_SERVER['HTTP_REFERER']);
		} else {
			$redirect_url = "index.php";
		}
		redirect($redirect_url, $lang->mygpconnect_redirect_loggedin, $lang->sprintf($lang->mygpconnect_redirect_title, $gplusID['username']));
	}
	
}

/**
 * Syncronizes any Google account with any MyBB account, importing all the infos.
 * 
 * @param array The existing user data. UID is required.
 * @param array The Google user data to sync.
 * @param int Whether to bypass any existing user settings or not. Disabled by default.
 * @return boolean True if successful, false if unsuccessful.
 **/

function mygpconnect_sync($user, $gpdata = array(), $bypass = false)
{
	
	global $mybb, $db, $session, $lang, $plugins;
	
	if(!$lang->mygpconnect) {
		$lang->load("mygpconnect");
	}
	
	$userData = array();
	$userfieldsData = array();
	
	$detailsid = "fid" . $mybb->settings['mygpconnect_gpdetailsfield'];
	$locationid = "fid" . $mybb->settings['mygpconnect_gplocationfield'];
	$bioid = "fid" . $mybb->settings['mygpconnect_gpbiofield'];
	$sexid = "fid" . $mybb->settings['mygpconnect_gpsexfield'];
	
	// ouch! empty Google data!
	if (empty($gpdata)) error($lang->mygpconnect_error_unknown);
	
	$query = $db->simple_select("userfields", "ufid", "ufid = {$user['uid']}");
	$userfields = $db->fetch_array($query);
	if (empty($userfields)) $userfieldsData['ufid'] = $user['uid'];
	
	// Google id, if empty we need to sync it
	if (empty($user["mygp_uid"])) $userData["mygp_uid"] = $gpdata["id"];
	
	// begin our checkes comparing mybb with Google stuff, syntax:
	// (USER SETTINGS AND !empty(Google VALUE)) OR $bypass (eventually ADMIN SETTINGS)
	
	// avatar
	if ((($user['gpavatar'] AND !empty($gpdata['image']['url'])) OR $bypass) AND $mybb->settings['mygpconnect_gpavatar']) {
		
		list($maxwidth, $maxheight) = explode("x", my_strtolower($mybb->settings['maxavatardims']));
		$avatar = str_ireplace("?sz=50", "?sz=$maxwidth", $gpdata['image']['url']);
		$userData["avatar"] = $db->escape_string($avatar);
		$userData["avatartype"] = "remote";
		$userData["avatardimensions"] = $maxheight . "|" . $maxwidth;
	}
	// birthday
	if ((($user['gpbday'] AND !empty($gpdata['birthday'])) OR $bypass) AND $mybb->settings['mygpconnect_gpbday']) {
		$birthday = explode("/", $gpdata['birthday']);
		$birthday['0'] = ltrim($birthday['0'], '0');
		$userData["birthday"] = $birthday['1'] . "-" . $birthday['0'] . "-" . $birthday['2'];
	}
	// cover, if Profile Picture plugin is installed
	if ((($user['gpavatar'] AND !empty($gpdata['cover']['source'])) OR $bypass) AND $db->field_exists("profilepic", "users")) {
		$userData["profilepic"] = $gpdata['cover']['coverPhoto']['url'];
		$userData["profilepictype"] = "remote";
		if ($mybb->usergroup['profilepicmaxdimensions']) {
			list($maxwidth, $maxheight) = explode("x", my_strtolower($mybb->usergroup['profilepicmaxdimensions']));
			$userData["profilepicdimensions"] = $maxwidth . "|" . $maxheight;
		} elseif(!empty($gpdata['cover']['coverPhoto']['width']) AND !empty($gpdata['cover']['coverPhoto']['height'])) {
			$userData["profilepicdimensions"] = $gpdata['cover']['coverPhoto']['width'] . "|" . $gpdata['cover']['coverPhoto']['height'];
		} else {
			$userData["profilepicdimensions"] = "";
		}
	}	
	// sex
	if ((($user['gpsex'] AND !empty($gpdata['gender'])) OR $bypass) AND $mybb->settings['mygpconnect_gpsex']) {
		if ($db->field_exists($sexid, "userfields")) {
			if ($gpdata['gender'] == "male") {
				$userfieldsData[$sexid] = $lang->mygpconnect_male;
			} elseif ($gpdata['gender'] == "female") {
				$userfieldsData[$sexid] = $lang->mygpconnect_female;
			}
		}
	}
	// name and last name
	if ((($user['gpdetails'] AND !empty($gpdata['displayName'])) OR $bypass) AND $mybb->settings['mygpconnect_gpdetails']) {
		if ($db->field_exists($detailsid, "userfields")) {
			$userfieldsData[$detailsid] = $db->escape_string($gpdata['displayName']);
		}
	}
	// bio
	if ((($user['gpbio'] AND !empty($gpdata['aboutMe'])) OR $bypass) AND $mybb->settings['mygpconnect_gpbio']) {
		if ($db->field_exists($bioid, "userfields")) {
			$userfieldsData[$bioid] = $db->escape_string(htmlspecialchars_decode(my_substr($gpdata['aboutMe'], 0, 400, true)));
		}
	}
	// location
	if ((($user['gplocation'] AND !empty($gpdata['placesLived'])) OR $bypass) AND $mybb->settings['mygpconnect_gplocation']) {
		if ($db->field_exists($locationid, "userfields")) {
			foreach($gpdata['placesLived'] as $place) {
				// we found our needle in the haystack, break
				if(array_key_exists("primary", $place)) {
					$location = $place['value'];
					break;
				}
			}
			$userfieldsData[$locationid] = $db->escape_string($location);
		}
	}
	
	$plugins->run_hooks("mygpconnect_sync_end", $userData);
	
	// let's do it!
	if (!empty($userData) AND !empty($user['uid'])) {
		$db->update_query("users", $userData, "uid = {$user['uid']}");
	}
	// make sure we can do it
	if (!empty($userfieldsData) AND !empty($user['uid'])) {
		if (isset($userfieldsData['ufid'])) {
			$db->insert_query("userfields", $userfieldsData);
		} else {
			$db->update_query("userfields", $userfieldsData, "ufid = {$user['uid']}");
		}
	}
	
	return true;
}

/**
 * Unlink any Google account from the corresponding MyBB account.
 * 
 * @param int The UID of the user you want to unlink.
 * @return boolean True if successful, false if unsuccessful.
 **/

function mygpconnect_unlink($uid)
{
	
	global $db;
	
	$uid = (int) $uid;
	
	$reset = array(
		"mygp_uid" => 0
	);
	
	$db->update_query("users", $reset, "uid = {$uid}");
	
}

/**
 * Registers an user, provided an array with valid data.
 * 
 * @param array The data of the user to register. name and email keys must be present.
 * @return boolean True if successful, false if unsuccessful.
 **/

function mygpconnect_register($user = array())
{
	
	global $mybb, $session, $plugins, $lang;
	
	require_once MYBB_ROOT . "inc/datahandlers/user.php";
	$userhandler = new UserDataHandler("insert");
	
	$plength = !empty($mybb->settings['minpasswordlength']) ? $mybb->settings['minpasswordlength'] : 8;
	
	$password = random_str($plength);
	
	$newUser = array(
		"username" => $user['name'],
		"password" => $password,
		"password2" => $password,
		"email" => $user['email'],
		"email2" => $user['email'],
		"usergroup" => $mybb->settings['mygpconnect_usergroup'],
		"displaygroup" => $mybb->settings['mygpconnect_usergroup'],
		"regip" => $session->ipaddress,
		"longregip" => my_ip2long($session->ipaddress),
		"hideemail" => 1,
	);
		
	/* Registration might fail for custom profile fields required at registration... workaround = IN_ADMINCP defined.
	 Placed straight before the registration process to avoid conflicts with third party plugins messying around with
	 templates (I'm looking at you, PHPTPL) */
	define("IN_ADMINCP", 1);
	
	$userhandler->set_data($newUser);
	if ($userhandler->validate_user()) {
		$user_info = $userhandler->insert_user();
		
		if ($mybb->settings['mygpconnect_passwordpm']) {
			require_once MYBB_ROOT . "inc/datahandlers/pm.php";
			$pmhandler = new PMDataHandler();
			$pmhandler->admin_override = true;
			
			// just make sure the admins didn't make something wrong in configuration
			if (empty($mybb->settings['mygpconnect_passwordpm_fromid']) OR !user_exists($mybb->settings['mygpconnect_passwordpm_fromid'])) {
				$fromid = 0;
			} else {
				$fromid = (int) $mybb->settings['mygpconnect_passwordpm_fromid'];
			}
			
			$message = $mybb->settings['mygpconnect_passwordpm_message'];
			$subject = $mybb->settings['mygpconnect_passwordpm_subject'];
			
			$thingsToReplace = array(
				"{user}" => $user_info['username'],
				"{password}" => $password
			);
			
			// replace what needs to be replaced
			foreach ($thingsToReplace as $find => $replace) {
				$message = str_replace($find, $replace, $message);
			}
			
			$pm = array(
				"subject" => $subject,
				"message" => $message,
				"fromid" => $fromid,
				"toid" => array(
					$user_info['uid']
				)
			);
			
			// some defaults :)
			$pm['options'] = array(
				"signature" => 1,
				"disablesmilies" => 0,
				"savecopy" => 0,
				"readreceipt" => 0
			);
			
			$pmhandler->set_data($pm);
			
			// Now let the pm handler do all the hard work
			if ($pmhandler->validate_pm()) {
				$pmhandler->insert_pm();
			} else {
				error($lang->sprintf($lang->mygpconnect_error_report, $pmhandler->get_friendly_errors()));
			}
		}
		
		// return our newly registered user data
		return $user_info;
	} else {
		$errors['error'] = true;
		$errors['data'] = $userhandler->get_friendly_errors();
		return $errors;
	}
}

/**
 * Logins any Google user, prompting a permission page and redirecting to the URL they came from.
 * 
 * @param string The URL to redirect at the end of the process. Relative URL.
 * @return redirect Redirects with an header() call to the specified URL.
 **/

function mygpconnect_login($url="")
{
	global $client, $mybb, $lang, $_SESSION;
	if(!$lang->mygpconnect) {
		$lang->load("mygpconnect");
	}
	if(!empty($url) OR empty($client)) {
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
		/* END API LOAD */
		$oauth = new Google_PlusService($client);
		$_SESSION['authcallback'] = $mybb->settings['bburl'] . $url;
	}
	$authUrl = $client->createAuthUrl();
	header('Location: ' . $authUrl);
	return;
}

/**
 * Displays peekers in settings. Technique ripped from MySupport, please don't blame on me :(
 * 
 * @return boolean True if successful, false either.
 **/

function mygpconnect_settings_footer()
{
	global $mybb, $db;
	if ($mybb->input["action"] == "change" && $mybb->request_method != "post") {
		$gid = mygpconnect_settings_gid();
		if ($mybb->input["gid"] == $gid || !$mybb->input['gid']) {
			echo '<script type="text/javascript">
	Event.observe(window, "load", function() {
	loadMygpConnectPeekers();
});
function loadMygpConnectPeekers()
{
	new Peeker($$(".setting_mygpconnect_passwordpm"), $("row_setting_mygpconnect_passwordpm_subject"), /1/, true);
	new Peeker($$(".setting_mygpconnect_passwordpm"), $("row_setting_mygpconnect_passwordpm_message"), /1/, true);
	new Peeker($$(".setting_mygpconnect_passwordpm"), $("row_setting_mygpconnect_passwordpm_fromid"), /1/, true);
	new Peeker($$(".setting_mygpconnect_gpbio"), $("row_setting_mygpconnect_gpbiofield"), /1/, true);
	new Peeker($$(".setting_mygpconnect_gplocation"), $("row_setting_mygpconnect_gplocationfield"), /1/, true);
	new Peeker($$(".setting_mygpconnect_gpdetails"), $("row_setting_mygpconnect_gpdetailsfield"), /1/, true);
	new Peeker($$(".setting_mygpconnect_gpsex"), $("row_setting_mygpconnect_gpsexfield"), /1/, true);
}
</script>';
		}
	}
}

global $mybb, $settings;

if ($settings['mygpconnect_enabled']) {
	$plugins->add_hook('global_start', 'mygpconnect_global');
	$plugins->add_hook('usercp_menu', 'mygpconnect_usercp_menu', 40);
	$plugins->add_hook('usercp_start', 'mygpconnect_usercp');
	$plugins->add_hook("admin_page_output_footer", "mygpconnect_settings_footer");
	$plugins->add_hook("fetch_wol_activity_end", "mygpconnect_fetch_wol_activity");
	$plugins->add_hook("build_friendly_wol_location_end", "mygpconnect_build_wol_location");
}

function mygpconnect_global()
{
	
	global $mybb, $lang, $templatelist;
	
	if (!$lang->mygpconnect) {
		$lang->load("mygpconnect");
	}
	
	if (isset($templatelist)) {
		$templatelist .= ',';
	}
	
	if (THIS_SCRIPT == "mygpconnect.php") {
		$templatelist .= 'mygpconnect_register';
	}
	
	if (THIS_SCRIPT == "usercp.php") {
		$templatelist .= 'mygpconnect_usercp_menu';
	}
	
	if (THIS_SCRIPT == "usercp.php" AND $mybb->input['action'] == "mygpconnect") {
		$templatelist .= ',mygpconnect_usercp_settings,mygpconnect_usercp_settings_linkprofile,mygpconnect_usercp_showsettings,mygpconnect_usercp_settings_setting';
	}
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
	
	if(!session_id()) session_start();

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
	
	if ($mybb->input['action'] == ("do_gplink" OR "mygpconnect") OR ($mybb->input['action'] == ("do_gplink" OR "mygpconnect") AND $mybb->request_method == 'post')) {
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
	}
	
	// linking accounts
	if ($mybb->input['action'] == "gplink") {
		$loginUrl = "/usercp.php?action=do_gplink";
		mygpconnect_login($loginUrl);
	}
	
	// truly link accounts
	if ($mybb->input['action'] == "do_gplink") {
		// unsetting it as we won't use it again
		unset($_SESSION['authcallback']);
		// authenticating
		$client->setAccessToken($_SESSION['gplus_token']);
		if ($client->getAccessToken()) {
			try {
				$userdata = (array) $oauth->people->get("me");
			}
			// user found, but permissions denied
			catch (Exception $e) {
				error($lang->sprintf($lang->mygpconnect_error_report, $e->getMessage()));
			}
			// true means only link
			mygpconnect_run($userdata, true);
			redirect("usercp.php?action=mygpconnect", $lang->mygpconnect_success_linked);
		} else {
			error($lang->mygpconnect_error_noauth);
		}
	}
	
	// settings page
	if ($mybb->input['action'] == 'mygpconnect') {
		global $db, $lang, $theme, $templates, $headerinclude, $header, $footer, $plugins, $usercpnav;
		// unsetting it as we won't use it again
		unset($_SESSION['authcallback']);
		
		add_breadcrumb($lang->nav_usercp, 'usercp.php');
		add_breadcrumb($lang->mygpconnect_page_title, 'usercp.php?action=mygpconnect');
		
		// 2 situations provided: the user is logged in with Facebook, two user isn't logged in with Facebook but it's loggin in.
		if ($mybb->request_method == 'post' OR $_SESSION['gp_isloggingin']) {
			if ($mybb->request_method == 'post') {
				verify_post_check($mybb->input['my_post_key']);
			}
			
			// unlinking his gp account... what a pity! :(
			if ($mybb->input['unlink']) {
				mygpconnect_unlink($mybb->user['uid']);
				redirect('usercp.php?action=mygpconnect', $lang->mygpconnect_success_accunlinked, $lang->mygpconnect_success_accunlinked_title);
			} else {						
				$settings = array();
				
				// having some fun with variable variables
				foreach ($settingsToCheck as $setting) {
					if ($mybb->input[$setting] == 1) {
						$settings[$setting] = 1;
					} else {
						$settings[$setting] = 0;
					}
					// building the extra data passed to the redirect url of the login function
					$loginUrlExtra .= "&{$setting}=" . $settings[$setting];
				}
				
				$client->setAccessToken($_SESSION['gplus_token']);
				if (!$client->getAccessToken()) {
					$loginUrl = "/usercp.php?action=mygpconnect" . $loginUrlExtra;
					// used for recognizing an active settings update process later on
					$_SESSION['gp_isloggingin'] = true;
					mygpconnect_login($loginUrl);
				}
				
				if ($db->update_query('users', $settings, 'uid = ' . (int) $mybb->user['uid'])) {
					// update on-the-fly that array of data dude!
					$newUser = array_merge($mybb->user, $settings);
					try {
						$email = array();
						// i know, fetching by two separate calls is a bit slow... but that's the best i could do to fetch both the email and the user's data
						if($mybb->settings['mygpconnect_emailcheck']) $email = (array) $oauth2->userinfo->get();
						$userdata = (array) $oauth->people->get("me");
						$userdata = array_merge($userdata, $email);
					}
					catch (Exception $e) {
						error($lang->sprintf($lang->mygpconnect_error_report, $e->getMessage()));
					}
					// oh yeah, let's sync!
					mygpconnect_sync($newUser, $userdata);
					
					// we don't need gp_isloggingin anymore
					unset($_SESSION['gp_isloggingin']);
					redirect('usercp.php?action=mygpconnect', $lang->mygpconnect_success_settingsupdated, $lang->mygpconnect_success_settingsupdated_title);
				}
			}
		}
		
		$query = $db->simple_select("users", "mygp_uid", "uid = " . $mybb->user['uid']);
		$alreadyThere = $db->fetch_field($query, "mygp_uid");
		$options = "";
		
		if ($alreadyThere) {
			
			$text = $lang->mygpconnect_settings_whattosync;
			$unlink = "<input type=\"submit\" class=\"button\" name=\"unlink\" value=\"{$lang->mygpconnect_settings_unlink}\" />";
			// checking if we want to sync that stuff			
			foreach ($settingsToCheck as $setting) {
				$tempKey = 'mygpconnect_' . $setting;
				if ($mybb->settings[$tempKey]) {
					$settingsToSelect[] = $setting;
				}
			}
			
			// join pieces into a string
			if (!empty($settingsToSelect)) {
				$settingsToSelect = implode(",", $settingsToSelect);
			}
			
			$query = $db->simple_select("users", $settingsToSelect, "uid = " . $mybb->user['uid']);
			$userSettings = $db->fetch_array($query);
			$settings = "";
			foreach ($userSettings as $setting => $value) {
				// variable variables. Yay!
				$tempKey = 'mygpconnect_settings_' . $setting;
				if ($value == 1) {
					$checked = " checked=\"checked\"";
				} else {
					$checked = "";
				}
				$label = $lang->$tempKey;
				$altbg = alt_trow();
				eval("\$options .= \"" . $templates->get('mygpconnect_usercp_settings_setting') . "\";");
			}
		} else {
			$text = $lang->mygpconnect_settings_linkaccount;
			eval("\$options = \"" . $templates->get('mygpconnect_usercp_settings_linkprofile') . "\";");
		}
		
		eval("\$content = \"" . $templates->get('mygpconnect_usercp_settings') . "\";");
		output_page($content);
	}
}

/**
 * Gets the gid of MyFacebook Connect settings group.
 * 
 * @return mixed The gid.
 **/

function mygpconnect_settings_gid()
{
	global $db;
	
	$query = $db->simple_select("settinggroups", "gid", "name = 'mygpconnect'", array(
		"limit" => 1
	));
	$gid = $db->fetch_field($query, "gid");
	
	return intval($gid);
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

/**
 * Debugs any type of data.
 * 
 * @param mixed The data to debug.
 * @return mixed The debugged data.
 **/

function mygpconnect_debug($data)
{
	echo "<pre>";
	echo print_r($data);
	echo "</pre>";
	exit;
}