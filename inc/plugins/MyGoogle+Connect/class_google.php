<?php

/**
 * A bridge between MyBB with Google, featuring login, registration and more.
 *
 * @package Main API class
 * @version 2.0
 */

class MyGoogle
{
	// The fallback URL where Google redirects users
	private $fallback;
	
	// The $google object populated upon initialization
	public $google;
	public $oauth;
	public $plus;
	
	// md5 sumcheck of concatenated $key and $secret to enhance security across multiple boards (prevents logging in other boards provided access within another)
	private $security_key = '';
	
	/**
	 * Contructor
	 */
	public function __construct()
	{
		global $mybb, $lang;
		
		$this->id = $mybb->settings['mygpconnect_clientid'];
		$this->secret = $mybb->settings['mygpconnect_clientsecret'];
		$this->key = $mybb->settings['mygpconnect_apikey'];
		$this->security_key = md5($mybb->settings['mygpconnect_clientid'].$mybb->settings['mygpconnect_clientsecret']);
		
		if (!session_id()) {
			session_start();
		}
		
		if (!$lang->mygpconnect) {
			$lang->load('mygpconnect');
		}
		
		$this->load_api();
		$this->set_fallback();
		$this->load_token();
	}
	
	/**
	 * Loads the necessary API classes
	 */
	private function load_api()
	{
		global $mybb, $lang;
		
		if ($this->google) {
			return false;
		}
		
		if (!$this->id or !$this->secret or !$this->key) {
			error($lang->mygpconnect_error_noconfigfound);
		}
		
		try {
		
			require_once MYBB_ROOT . "mygpconnect/src/Google_Client.php";
			require_once MYBB_ROOT . "mygpconnect/src/contrib/Google_PlusService.php";
			
			if ($mybb->settings['mygpconnect_emailcheck']) {
				require_once MYBB_ROOT . "mygpconnect/src/contrib/Google_Oauth2Service.php";
			}
		}
		catch (Exception $e) {
			error($lang->sprintf($lang->mygpconnect_error_report, $e->getMessage()));
		}
		
		$this->google = new Google_Client();
		$this->google->setApplicationName($mybb->settings['bbname']." Google Login");
		$this->google->setClientId($this->id);
		$this->google->setClientSecret($this->secret);
		$this->google->setDeveloperKey($this->key);
		$this->google->setApprovalPrompt('auto');
		$this->google->setScopes(array('https://www.googleapis.com/auth/plus.login', 'https://www.googleapis.com/auth/userinfo.email'));
		
		if ($mybb->settings['mygpconnect_emailcheck']) {
			$this->oauth = new Google_Oauth2Service($this->google);
		}
		
		$this->plus = new Google_PlusService($this->google);
		
		return true;
	}
	
	/**
	 * Sets the fallback URL where the app should redirect to when finished authenticating
	 */
	public function set_fallback($url = '')
	{
		global $mybb;
		
		if (!$url) {
			$this->fallback = $mybb->settings['bburl'] . "/mygpconnect.php?action=do_login";
		}
		else {
			$this->fallback = $mybb->settings['bburl'] . "/" . $url;
		}
		
		$this->google->setRedirectUri($this->fallback);
		
		return true;
	}
	
	/**
	 * Starts the login process, creating the authorize URL
	 */
	public function authenticate()
	{
		header("Location: " . $this->google->createAuthUrl());
		
		return true;
	}
	
	/**
	 * Checks the incoming request and exchanges temporary tokens with permanent auth tokens
	 */
	public function obtain_tokens()
	{
		global $lang;
		
		try {
			$this->google->authenticate();
		}
		catch (Exception $e) {
			error($lang->sprintf($lang->mygpconnect_error_report, $e->getMessage()));
		}		
		
		$this->set_token();
		$this->load_token();
		
		return true;
	}
	
	/**
	 * Checks if a token is present and sets it on the client
	 */
	public function load_token()
	{
		if ($_SESSION[$this->security_key]['gplus_token']) {
			return $this->google->setAccessToken($_SESSION[$this->security_key]['gplus_token']);
		}
		
		return false;
	}
	
	/**
	 * Sets a token in the user's session
	 */
	public function set_token()
	{
		$_SESSION[$this->security_key]['gplus_token'] = $this->google->getAccessToken();
		
		return true;
	}
	
	/**
	 * Attempts to get the authenticated user's data
	 */
	public function get_user($email = true)
	{
		global $mybb, $lang;
		
		$details = array();
		
		try {
			
			if ($mybb->settings['mygpconnect_emailcheck'] and $email) {
				$details = (array) $this->oauth->userinfo->get();
			}
			
			$user = array_merge((array) $this->plus->people->get('me'), $details);
		
		}
		catch (Exception $e) {
			error($lang->sprintf($lang->mygpconnect_error_report, $e->getMessage()));
		}
		
		if ($user) {
			return $user;
		}
		
		return false;
	}
	
	/**
	 * Checks if the authenticated user is available
	 */
	public function check_user()
	{
		global $lang;
		
		if ($this->google->getAccessToken()) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Logins an user by adding a cookie into his browser and updating his session
	 */
	public function login($user = '')
	{
		global $mybb, $session, $db;
		
		if (!$user) {
			$user = $mybb->user;
		}
		
		if (!$user['uid'] or !$user['loginkey'] or !$session) {
			return false;
		}
		
		// Delete all the old sessions
		$db->delete_query("sessions", "ip='" . $db->escape_string($session->ipaddress) . "' and sid != '" . $session->sid . "'");
		
		// Create a new session
		$db->update_query("sessions", array(
			"uid" => $user['uid']
		), "sid='" . $session->sid . "'");
		
		// Set up the login cookies
		my_setcookie("mybbuser", $user['uid'] . "_" . $user['loginkey'], null, true);
		my_setcookie("sid", $session->sid, -1, true);
		
		return true;
	}
	
	/**
	 * Registers an user with Google data
	 */
	public function register($user)
	{
		if (!$user) {
			return false;
		}
		
		global $mybb, $session, $plugins, $lang;
		
		require_once MYBB_ROOT . "inc/datahandlers/user.php";
		$userhandler = new UserDataHandler("insert");
		
		$plength = 8;
		if ($mybb->settings['minpasswordlength']) {
			$plength = (int) $mybb->settings['minpasswordlength'];
		}
		
		// No email? Create a fictional one
		if (!$user['email']) {
			$email = $user['id'] . '@' . str_replace(' ', '', strtolower($mybb->settings['bbname'])) . '.com';
		}
		else {
			$email = $user['email'];
		}
		
		$password = random_str($plength);
		
		$new_user = array(
			"username" => $user['displayName'],
			"password" => $password,
			"password2" => $password,
			"email" => $email,
			"email2" => $email,
			"usergroup" => (int) $mybb->settings['mygpconnect_usergroup'],
			"regip" => $session->ipaddress,
			"longregip" => my_ip2long($session->ipaddress),
			"options" => array(
				"hideemail" => 1
			)
		);
		
		/* Registration might fail for custom profile fields required at registration... workaround = IN_ADMINCP defined.
		Placed straight before the registration process to avoid conflicts with third party plugins messying around with
		templates (I'm looking at you, PHPTPL) */
		define("IN_ADMINCP", 1);
		
		$userhandler->set_data($new_user);
		if ($userhandler->validate_user()) {
			
			$user_info = $userhandler->insert_user();
			
			$plugins->run_hooks("member_do_register_end");
			
			// Deliver a welcome PM
			if ($mybb->settings['mygpconnect_passwordpm']) {
				
				require_once MYBB_ROOT . "inc/datahandlers/pm.php";
				$pmhandler                 = new PMDataHandler();
				$pmhandler->admin_override = true;
				
				// Make sure admins haven't done something bad
				$fromid = (int) $mybb->settings['mygpconnect_passwordpm_fromid'];
				if (!$mybb->settings['mygpconnect_passwordpm_fromid'] or !user_exists($mybb->settings['mygpconnect_passwordpm_fromid'])) {
					$fromid = 0;
				}
				
				$message = $mybb->settings['mygpconnect_passwordpm_message'];
				$subject = $mybb->settings['mygpconnect_passwordpm_subject'];
				
				$thingsToReplace = array(
					"{user}" => $user_info['username'],
					"{password}" => $password
				);
				
				// Replace what needs to be replaced
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
				
				// Some defaults :)
				$pm['options'] = array(
					"signature" => 1
				);
				
				$pmhandler->set_data($pm);
				
				// Now let the PM handler do all the hard work
				if ($pmhandler->validate_pm()) {
					$pmhandler->insert_pm();
				}
				else {
					error($lang->sprintf($lang->mygpconnect_error_report, $pmhandler->get_friendly_errors()));
				}
			}
			
			// Finally return our new user data
			return $user_info;
			
		}
		else {
			return array(
				'error' => $userhandler->get_friendly_errors()
			);
		}
		
		return true;
	}
	
	/**
	 * Links an user with Google
	 */
	public function link_user($user = '', $id)
	{
		global $mybb, $db;
		
		if (!$id) {
			return false;
		}
		
		if (!$user) {
			$user = $mybb->user;
		}
		
		// Still no user?
		if (!$user) {
			return false;
		}
		
		$update = array(
			"mygp_uid" => $id
		);
		
		$db->update_query("users", $update, "uid = {$user['uid']}");
		
		// Add to the usergroup
		if ($mybb->settings['mygpconnect_usergroup']) {
			$this->join_usergroup($user, $mybb->settings['mygpconnect_usergroup']);
		}
		
		return true;
	}
	
	/**
	 * Unlinks an user from Google
	 */
	public function unlink_user($user = '')
	{
		global $mybb, $db;
		
		if (!$user) {
			$user = $mybb->user;
		}
		
		// Still no user?
		if (!$user) {
			return false;
		}
		
		$update = array(
			"mygp_uid" => 0
		);
		
		$db->update_query("users", $update, "uid = {$user['uid']}");
		
		// Remove from the usergroup
		if ($mybb->settings['mygpconnect_usergroup']) {
			$this->leave_usergroup($user, $mybb->settings['mygpconnect_usergroup']);
		}
		
		return true;
	}
	
	/**
	 * Processes an user
	 */
	public function process($user)
	{
		global $mybb, $db, $session, $lang;
		
		if (!$user['id']) {
			error($lang->mygpconnect_error_noidprovided);
		}
		
		$sql = '';
		if ($user['email']) {
			$sql = " OR email = '" . $db->escape_string($user['email']) . "'";
		}
		
		// Let's see if you are already with us
		$query   = $db->simple_select("users", "*", "mygp_uid = {$user['id']}{$sql}", array(
			"limit" => 1
		));
		$account = $db->fetch_array($query);
		$db->free_result($query);
		
		$message = $lang->mygpconnect_redirect_loggedin;
		
		// Link
		if ($user['email'] and $account['email'] == $user['email'] and !$account['mygp_uid']) {
			$this->link_user($account, $user['id']);
		}
		// Register
		else if (!$account) {
			
			if (!$mybb->settings['mygpconnect_fastregistration']) {
				header("Location: mygpconnect.php?action=register");
				return false;
			}
			
			global $plugins;
			$account = $this->register($user);
			
			if ($account['error']) {
				return $account;
			}
			else {
			
				// Set some defaults
				$toCheck = array('gpavatar', 'gpbday', 'gpsex', 'gpdetails', 'gpbio', 'gplocation');
				foreach ($toCheck as $setting) {
				
					$tempKey = 'mygpconnect_' . $setting;
					$new_settings[$setting] = $mybb->settings[$tempKey];
					
				}
				
				$account = array_merge($account, $new_settings);
				
			}
			
			$message = $lang->mygpconnect_redirect_registered;
			
		}
		
		// Login
		$this->login($account);
		
		// Sync
		$this->sync($account, $user);
		
		$title = $lang->sprintf($lang->mygpconnect_redirect_title, $account['username']);
		
		// Redirect
		$this->redirect('', $title, $message);
		
		return true;
	}
	
	/**
	 * Synchronizes Google's data with MyBB's data
	 */
	public function sync($user, $data = array())
	{
		if (!$user['uid']) {
			return false;
		}
		
		global $mybb, $db, $session, $lang;
		
		$update         = array();
		$userfield = array();
		
		$detailsid  = "fid" . (int) $mybb->settings['mygpconnect_gpdetailsfield'];
		$locationid = "fid" . (int) $mybb->settings['mygpconnect_gplocationfield'];
		$bioid      = "fid" . (int) $mybb->settings['mygpconnect_gpbiofield'];
		$sexid      = "fid" . (int) $mybb->settings['mygpconnect_gpsexfield'];
		
		// No data available? Let's get some
		if (!$data) {
			$data = $this->get_user();
		}
		
		$query      = $db->simple_select("userfields", "ufid", "ufid = {$user['uid']}");
		$check = $db->fetch_field($query, "ufid");
		$db->free_result($query);
		
		if (!$check) {
			$userfield['ufid'] = $user['uid'];
		}
		
		// No Google ID? Sync it too!
		if (!$user['mygp_uid'] and $data['id']) {
			$update['mygp_uid'] = $data['id'];
		}
		
		// Avatar
		if ($user['gpavatar'] and $data['image']['url'] and $mybb->settings['mygpconnect_gpavatar']) {
			
			list($maxwidth, $maxheight) = explode('x', my_strtolower($mybb->settings['maxavatardims']));
			
			$update['avatar'] = str_ireplace("?sz=50", "?sz=$maxwidth", $data['image']['url']);
			$update['avatartype'] = "remote";
			$update["avatardimensions"] = $maxheight . "|" . $maxwidth;
		}
		
		// Birthday
		if ($user['gpbday'] and $data['birthday'] and $mybb->settings['mygpconnect_gpbday']) {
			
			$birthday           = explode("-", $data['birthday']);
			$update["birthday"] = $birthday['2'] . "-" . $birthday['1'] . "-" . $birthday['0'];
			
		}
		
		// Cover, if Profile Picture plugin is installed
		if ($user['gpavatar'] and $data['cover']['coverPhoto']['url'] and $mybb->settings['mygpconnect_gpavatar'] and $db->field_exists("profilepic", "users")) {
			
			$update['profilepic']     = $data['cover']['coverPhoto']['url'];
			$update['profilepictype'] = 'remote';
			
			if ($mybb->usergroup['profilepicmaxdimensions']) {
			
				list($maxwidth, $maxheight) = explode("x", my_strtolower($mybb->usergroup['profilepicmaxdimensions']));
				$update['profilepicdimensions'] = $maxwidth . "|" . $maxheight;
				
			}
			else if ($data['cover']['coverPhoto']['width'] and $data['cover']['coverPhoto']['height']) {
				$update['profilepicdimensions'] = $data['cover']['coverPhoto']['width'] . "|" . $data['cover']['coverPhoto']['height'];
			}
			else {
				$update['profilepicdimensions'] = '';
			}
			
		}
		
		// Sex
		if ($user['gpsex'] and $data['gender'] and $mybb->settings['mygpconnect_gpsex']) {
			
			if ($db->field_exists($sexid, "userfields")) {
				
				if ($data['gender'] == 'male') {
					$userfield[$sexid] = $lang->mygpconnect_male;
				}
				else if ($data['gender'] == 'female') {
					$userfield[$sexid] = $lang->mygpconnect_female;
				}
				
			}
		}
		
		// Name and last name
		if ($user['gpdetails'] and $data['displayName'] and $mybb->settings['mygpconnect_gpdetails']) {
			
			if ($db->field_exists($detailsid, "userfields")) {
				$userfield[$detailsid] = $db->escape_string($data['displayName']);
			}
			
		}
		
		// Bio
		if ($user['gpbio'] and $data['aboutMe'] and $mybb->settings['mygpconnect_gpbio']) {
			
			if ($db->field_exists($bioid, "userfields")) {
				$userfield[$bioid] = $db->escape_string(htmlspecialchars_uni(my_substr($data['aboutMe'], 0, 400, true)));
			}
			
		}
		
		// Location
		if ($user['gplocation'] and $data['placesLived'] and $mybb->settings['mygpconnect_gplocation']) {
			
			if ($db->field_exists($locationid, "userfields")) {
			
				foreach($data['placesLived'] as $place) {
				
					if ($place['primary']) {
					
						$location = $place['value'];
						break;
						
					}
					
				}
				
				$userfield[$locationid] = $db->escape_string($location);
				
			}
			
		}
		
		if ($update) {			
			$query = $db->update_query("users", $update, "uid = {$user['uid']}");
		}
		
		// Make sure we can do it
		if ($userfield) {
			
			if ($userfield['ufid']) {
				$query = $db->insert_query("userfields", $userfield);
			}
			else {
				$query = $db->update_query("userfields", $userfield, "ufid = {$user['uid']}");
			}
			
		}
		
		return true;
	}
	
	/**
	 * Adds the logged in user to an additional group without losing the existing values
	 */
	public function join_usergroup($user, $gid)
	{
		global $mybb, $db;
		
		if (!$gid) {
			return false;
		}
		
		if (!$user) {
			$user = $mybb->user;
		}
		
		if (!$user) {
			return false;
		}
		
		$gid = (int) $gid;
		
		// Is this user already in that group?
		if ($user['usergroup'] == $gid) {
			return false;
		}
		
		$groups = explode(",", $user['additionalgroups']);
		
		if (!in_array($gid, $groups)) {
			
			$groups[] = $gid;
			$update   = array(
				"additionalgroups" => implode(",", array_filter($groups))
			);
			$db->update_query("users", $update, "uid = {$user['uid']}");
			
		}
		
		return true;
	}
	
	/**
	 * Removes the logged in user from an additional group without losing the existing values
	 */
	public function leave_usergroup($user, $gid)
	{
		global $mybb, $db;
		
		if (!$gid) {
			return false;
		}
		
		if (!$user) {
			$user = $mybb->user;
		}
		
		if (!$user) {
			return false;
		}
		
		$gid = (int) $gid;
		
		// Is this user already in that group?
		if ($user['usergroup'] == $gid) {
			return false;
		}
		
		$groups = explode(",", $user['additionalgroups']);
		
		if (in_array($gid, $groups)) {
			
			// Flip the array so we have gid => keys
			$groups = array_flip($groups);
			unset($groups[$gid]);
			
			// Restore the array flipping it again (and filtering it)
			$groups = array_filter(array_flip($groups));
			
			$update = array(
				"additionalgroups" => implode(",", $groups)
			);
			$db->update_query("users", $update, "uid = {$user['uid']}");
			
		}
		
		return true;
	}
	
	/**
	 * Redirects the user to the page he came from
	 */
	public function redirect($url = '', $title = '', $message = '')
	{
		if (!$url) {
			$url = $_SERVER['HTTP_REFERER'];
		}
		
		if (!strpos($url, "action=login") and !strpos($url, "action=do_login") and !strpos($url, "action=register")) {
			$url = htmlspecialchars_uni($url);
		}
		else {
			$url = "index.php";
		}
		
		redirect($url, $message, $title);
		
		return true;
	}
	
	/**
	 * Debugs any type of data, printing out an array and immediately killing the execution of the currently running script
	 */
	public function debug($data)
	{
		// Fallback for arrays
		if (is_array($data)) {
			$data = array_map('htmlspecialchars_uni', $data);
		}
		// Fallback for strings
		else if (is_string($data)) {
			$data = htmlspecialchars_uni($data);
		}
		
		echo "<pre>";
		print_r($data);
		echo "</pre>";
		
		exit;
	}
}
