<?php

$l['mygpconnect'] = "MyGoogle+ Connect";
$l['mygpconnect_login'] = "Login with Google+";

// Redirects
$l['mygpconnect_redirect_loggedin'] = "You have successfully logged in with Google+.";
$l['mygpconnect_redirect_registered'] = "You have successfully registered and logged in with Google+.";
$l['mygpconnect_redirect_title'] = "Welcome, {1}!";

// Errors
$l['mygpconnect_error_noconfigfound'] = "You haven't configured MyGoogle+ Connect plugin yet: either your Google+ Client ID, your Google+ Client Secret or your Google+ API Key are missing. If you are an administrator, please read the instructions provided in the documentation.";
$l['mygpconnect_error_noauth'] = "You didn't let us login with your Google+ account. Please authorize our application from your Google+ Application manager if you would like to login into our Forum.";
$l['mygpconnect_error_report'] = "An unknown error occurred. The output of the error is:<br>
<pre>{1}</pre><br>
Please report this error to an administrator and try again.";
$l['mygpconnect_error_alreadyloggedin'] = "You are already logged into the board.";
$l['mygpconnect_error_verifiedonly'] = "Only verified Google+ accounts are allowed to register or login. Please verify your Google+ account before tempting to register or login here again.";
$l['mygpconnect_error_unknown'] = "An unknown error occurred using MyGoogle+ Connect.";

// UserCP
$l['mygpconnect_settings_title'] = $l['mygpconnect_page_title'] = "Google+ integration";
$l['mygpconnect_settings_save'] = "Save";
$l['mygpconnect_settings_unlink'] = "Unlink my account";
$l['mygpconnect_settings_gpavatar'] = "Avatar and cover";
$l['mygpconnect_settings_gpsex'] = "Sex";
$l['mygpconnect_settings_gpbio'] = "Bio";
$l['mygpconnect_settings_gpdetails'] = "Name and last name";
$l['mygpconnect_settings_gpbday'] = "Birthday";
$l['mygpconnect_settings_gplocation'] = "Location";
$l['mygpconnect_link'] = "Click here to link your account with your Google+'s one";
$l['mygpconnect_settings_whattosync'] = "Select what info we should import from your Google+. We'll immediately synchronize your desired data on-the-fly while updating the settings, adding what should be added (but not removing what should be removed - that's up to you!).";
$l['mygpconnect_settings_linkaccount'] = "Hit the button on your right to link your Google+ account with the one on this board.";
$l['myfbconnect_settings_connected'] = "Your Google+ account is currently linked to the account on this board. Click on the button below to unlink.";

// Registration
$l['mygpconnect_register_title'] = "Google+ registration";
$l['mygpconnect_register_basicinfo'] = "Choose your basic infos on your right. They are already filled with your Google+ data, but if you want to change them you are free to do it. The account will be linked to your Google+ one immediately, automatically and regardless of your choices.";
$l['mygpconnect_register_whattosync'] = "Select what info we should import from your Google+ account. We'll immediately synchronize your desired data making an exact copy of your Google+ account, dependently of your choices.";
$l['mygpconnect_register_username'] = "Username:";
$l['mygpconnect_register_email'] = "Email:";

// Success messages
$l['mygpconnect_success_linked'] = "Your account on this board has been correctly linked to your Google+'s one.";
$l['mygpconnect_success_settingsupdated'] = "Your Google+ integration related settings have been updated correctly.";
$l['mygpconnect_success_settingsupdated_title'] = "Settings updated";
$l['mygpconnect_success_accunlinked'] = "Your Google+ account has been unlinked successfully from your MyBB's one.";
$l['mygpconnect_success_accunlinked_title'] = "Account unlinked";

// Who's online
$l['mygpconnect_viewing_loggingin'] = "<a href=\"mygpconnect.php?action=login\">Logging in with Google+</a>";
$l['mygpconnect_viewing_registering'] = "<a href=\"mygpconnect.php?action=register\">Registering with Google+</a>";

// Others
$l['mygpconnect_male'] = "Male";
$l['mygpconnect_female'] = "Female";