<?php
// Installation
$l['mygpconnect'] = "MyGoogle+ Connect";
$l['mygpconnect_pluginlibrary_missing'] = "<a href=\"http://mods.mybb.com/view/pluginlibrary\">PluginLibrary</a> is missing. Please install it before doing anything else with mygpconnect.";

// Settings
$l['setting_group_mygpconnect'] = "Google+ login and registration settings";
$l['setting_group_mygpconnect_desc'] = "Here you can manage Google+ login and registration on your board, changing API keys and options to enable or disable certain aspects of MyGoogle+ Connect plugin.";
$l['setting_mygpconnect_enable'] = "Master switch";
$l['setting_mygpconnect_enable_desc'] = "Do you want to let your users login and register with Google+? If an user is already registered the account will be linked to its Google+ account.";
$l['setting_mygpconnect_clientid'] = "Client ID";
$l['setting_mygpconnect_clientid_desc'] = "Enter your Client ID token from Google+ Developers site. This will be used together with the other tokens to ask authorizations to your users through your app.";
$l['setting_mygpconnect_clientsecret'] = "Client Secret";
$l['setting_mygpconnect_clientsecret_desc'] = "Enter your Client Secret token from Google+ Developers site. This will be used together with the other tokens to ask authorizations to your users through your app.";
$l['setting_mygpconnect_apikey'] = "API Key";
$l['setting_mygpconnect_apikey_desc'] = "Enter your API Key token from Google+ Developers site. This will be used together with the other tokens to ask authorizations to your users through your app.";
$l['setting_mygpconnect_emailcheck'] = "Email check";
$l['setting_mygpconnect_emailcheck_desc'] = "If this option is enabled, the user's email will be fetched upon the login and the registration processes and a check against the user's emails will be performed. If a match will be found, the account on MyBB will be automatically linked to the user's Google+ account. If this option is disabled, the email won't be fetched and the user will be asked to register no matter if he has the same email of an already registered user on your board. Users will still be able to connect their Google+ accounts to their MyBB's one within their User Control Panels though.<br>
<b>Please note that fetching the email requires one extra API call. This means it takes a bit more to login or register. If you experience too long waiting periods, disable this option.</b>";
$l['setting_mygpconnect_fastregistration'] = "One-click registration";
$l['setting_mygpconnect_fastregistration_desc'] = "If this option is disabled, when an user wants to register with Google+ he will be asked for permissions for your app if it's the first time he is loggin in, else he will be registered and logged in immediately without asking for username changes and what data to sync.";
$l['setting_mygpconnect_usergroup'] = "After registration usergroup";
$l['setting_mygpconnect_usergroup_desc'] = "Select the after-registration usergroup. The user will be inserted directly into this usergroup upon registering. Also, if an existing user links his account to Google+, this usergroup will be added to his additional groups list.";
$l['setting_mygpconnect_requestpublishingperms'] = "Request publishing permissions";
$l['setting_mygpconnect_requestpublishingperms_desc'] = "If this option is enabled, the user will be asked for extra publishing permissions for your application. <b>This option should be left disabled (as it won't do anything in particular at the moment). In the future it will be crucial to let you post something on the user's wall when he registers or logins to your board.";
$l['setting_mygpconnect_passwordpm'] = "Send PM upon registration";
$l['setting_mygpconnect_passwordpm_desc'] = "If this option is enabled, the user will be notified with a PM telling his randomly generated password upon his registration.";
$l['setting_mygpconnect_passwordpm_subject'] = "PM subject";
$l['setting_mygpconnect_passwordpm_subject_desc'] = "Choose a default subject to use in the generated PM.";
$l['setting_mygpconnect_passwordpm_message'] = "PM message";
  $l['setting_mygpconnect_passwordpm_message_desc'] = "Write down a default message which will be sent to the registered users when they register with Google+. {user} and {password} are variables and refer to the username the former and the randomly generated password the latter: they should be there even if you modify the default message. HTML and BBCode are permitted here.";
$l['setting_mygpconnect_passwordpm_fromid'] = "PM sender";
$l['setting_mygpconnect_passwordpm_fromid_desc'] = "Insert the UID of the user who will be the sender of the PM. By default is set to 0 which is MyBB Engine, but you can change it to whatever you like.";

// Custom fields
$l['setting_mygpconnect_gpavatar'] = "Sync avatar and cover";
$l['setting_mygpconnect_gpavatar_desc'] = "If you would like to import avatar and cover from Google+ (and let users decide to sync them) enable this option.";
$l['setting_mygpconnect_gpbday'] = "Sync birthday";
$l['setting_mygpconnect_gpbday_desc'] = "If you would like to import birthday from Google+ (and let users decide to sync it) enable this option.";
$l['setting_mygpconnect_gplocation'] = "Sync location";
$l['setting_mygpconnect_gplocation_desc'] = "If you would like to import Location from Google+ (and let users decide to sync it) enable this option.";
$l['setting_mygpconnect_gplocationfield'] = "Location Custom Profile Field";
$l['setting_mygpconnect_gplocationfield_desc'] = "Select the Custom Profile Field that will be filled with Google+'s location.";
$l['setting_mygpconnect_gpbio'] = "Sync biography";
$l['setting_mygpconnect_gpbio_desc'] = "If you would like to import Biography from Google+ (and let users decide to sync it) enable this option.";
$l['setting_mygpconnect_gpbiofield'] = "Biography Custom Profile Field";
$l['setting_mygpconnect_gpbiofield_desc'] = "Select the Custom Profile Field that will be filled with Google+'s biography.";
$l['setting_mygpconnect_gpdetails'] = "Sync first and last name";
$l['setting_mygpconnect_gpdetails_desc'] = "If you would like to import first and last name from Google+ (and let users decide to sync it) enable this option.";
$l['setting_mygpconnect_gpdetailsfield'] = "First and last name Custom Profile Field ID";
$l['setting_mygpconnect_gpdetailsfield_desc'] = "Select the Custom Profile Field that will be filled with Google+'s name and last name.";
$l['setting_mygpconnect_gpsex'] = "Sync sex";
$l['setting_mygpconnect_gpsex_desc'] = "If you would like to import sex from Google+ (and let users decide to sync it) enable this option.";
$l['setting_mygpconnect_gpsexfield'] = "Sex Custom Profile Field";
$l['setting_mygpconnect_gpsexfield_desc'] = "Select the Custom Profile Field that will be filled with Google+'s sex.";

// Default pm text
$l['mygpconnect_default_passwordpm_subject'] = "New password";
$l['mygpconnect_default_passwordpm_message'] = "Welcome on our Forums, dear {user}!

We are pleased you are registering with Google+. We have generated a random password for you which you should take note somewhere if you would like to change your personal infos. We require for security reasons that you specify your password when you change things such as the email, your username and the password itself, so keep it secret!

Your password is: [b]{password}[/b]

With regards,
our Team";

// Errors
$l['mygpconnect_error_needtoupdate'] = "You seem to have currently installed an outdated version of MyGoogle+ Connect. Please <a href=\"index.php?module=config-settings&update=mygpconnect\">click here</a> to run the upgrade script.";
$l['mygpconnect_error_nothingtodohere'] = "Ooops, MyGoogle+ Connect is already up-to-date! Nothing to do here...";

// Success
$l['mygpconnect_success_updated'] = "MyGoogle+ Connect has been updated correctly from version {1} to {2}. Good job!";