<?php
// installation
$l['mygpconnect'] = "MyGoogle+ Connect";
$l['mygpconnect_pluginlibrary_missing'] = "<a href=\"http://mods.mybb.com/view/pluginlibrary\">PluginLibrary</a> is missing. Please install it before doing anything else with mygpconnect.";

// settings
$l['mygpconnect_settings'] = "Google+ login and registration settings";
$l['mygpconnect_settings_desc'] = "Here you can manage Google+ login and registration on your board, changing API keys and options to enable or disable certain aspects of MyGoogle+ Connect plugin.";
$l['mygpconnect_settings_enable'] = "Master switch";
$l['mygpconnect_settings_enable_desc'] = "Do you want to let your users login and register with Google+? If an user is already registered the account will be linked to its Google+ account.";
$l['mygpconnect_settings_clientid'] = "Client ID";
$l['mygpconnect_settings_clientid_desc'] = "Enter your Client ID token from Google+ Developers site. This will be used together with the Secret token to ask authorizations to your users through your app.";
$l['mygpconnect_settings_clientsecret'] = "Client Secret";
$l['mygpconnect_settings_clientsecret_desc'] = "Enter your Client Secret token from Google+ Developers site. This will be used together with the Key token to ask authorizations to your users through your app.";
$l['mygpconnect_settings_apikey'] = "API Key";
$l['mygpconnect_settings_apikey_desc'] = "Enter your API Key token from Google+ Developers site. This will be used together with the Key token to ask authorizations to your users through your app.";
$l['mygpconnect_settings_emailcheck'] = "Email check";
$l['mygpconnect_settings_emailcheck_desc'] = "If this option is enabled, the user's email will be fetched upon the login and the registration process and a check against the users' emails will be performed. If a match will be found, the account on MyBB will be automatically linked to the user's Google+ account. If this option is disabled, the email won't be fetched and the user will be asked to register no matter if he has the same email of an already registered user on your board. Users will still be able to connect their Google+ accounts to their MyBB's one within their User Control Panels though.<br>
<b>Please note that fetching the email requires one extra API call. This means it takes a bit more to login or register. If you experience too long waiting periods, disable this option.</b>";
$l['mygpconnect_settings_fastregistration'] = "One-click registration";
$l['mygpconnect_settings_fastregistration_desc'] = "If this option is disabled, when an user wants to register with Google+ he will be asked for permissions for your app if it's the first time he is loggin in, else he will be registered and logged in immediately without asking for username changes and what data to sync.";
$l['mygpconnect_settings_usergroup'] = "After registration usergroup";
$l['mygpconnect_settings_usergroup_desc'] = "Enter the usergroup ID you want the new users to be when they register with Google+. By default this value is set to 2, which equals to Registered usergroup.";
$l['mygpconnect_settings_requestpublishingperms'] = "Request publishing permissions";
$l['mygpconnect_settings_requestpublishingperms_desc'] = "If this option is enabled, the user will be asked for extra publishing permissions for your application. <b>This option should be left disabled (as it won't do anything in particular at the moment). In the future it will be crucial to let you post something on the user's wall when he registers or logins to your board.";
$l['mygpconnect_settings_passwordpm'] = "Send PM upon registration";
$l['mygpconnect_settings_passwordpm_desc'] = "If this option is enabled, the user will be notified with a PM telling his randomly generated password upon his registration.";
$l['mygpconnect_settings_passwordpm_subject'] = "PM subject";
$l['mygpconnect_settings_passwordpm_subject_desc'] = "Choose a default subject to use in the generated PM.";
$l['mygpconnect_settings_passwordpm_message'] = "PM message";
  $l['mygpconnect_settings_passwordpm_message_desc'] = "Write down a default message which will be sent to the registered users when they register with Google+. {user} and {password} are variables and refer to the username the former and the randomly generated password the latter: they should be there even if you modify the default message. HTML and BBCode are permitted here.";
$l['mygpconnect_settings_passwordpm_fromid'] = "PM sender";
$l['mygpconnect_settings_passwordpm_fromid_desc'] = "Insert the UID of the user who will be the sender of the PM. By default is set to 0 which is MyBB Engine, but you can change it to whatever you like.";
// custom fields support, yay!
$l['mygpconnect_settings_gpavatar'] = "Sync avatar and cover";
$l['mygpconnect_settings_gpavatar_desc'] = "If you would like to import avatar and cover from Google+ (and let users decide to sync them) enable this option.";
$l['mygpconnect_settings_gpbday'] = "Sync birthday";
$l['mygpconnect_settings_gpbday_desc'] = "If you would like to import birthday from Google+ (and let users decide to sync it) enable this option.";
$l['mygpconnect_settings_gplocation'] = "Sync location";
$l['mygpconnect_settings_gplocation_desc'] = "If you would like to import Location from Google+ (and let users decide to sync it) enable this option.";
$l['mygpconnect_settings_gplocationfield'] = "Location Custom Profile Field ID";
$l['mygpconnect_settings_gplocationfield_desc'] = "Insert the Custom Profile Field ID which corresponds to the Location field. Make sure it's the right ID while you fill it! Default to 1 (MyBB's default)";
$l['mygpconnect_settings_gpbio'] = "Sync biography";
$l['mygpconnect_settings_gpbio_desc'] = "If you would like to import Biography from Google+ (and let users decide to sync it) enable this option.";
$l['mygpconnect_settings_gpbiofield'] = "Biography Custom Profile Field ID";
$l['mygpconnect_settings_gpbiofield_desc'] = "Insert the Custom Profile Field ID which corresponds to the Biography field. Make sure it's the right ID while you fill it! Default to 2 (MyBB's default)";
$l['mygpconnect_settings_gpdetails'] = "Sync first and last name";
$l['mygpconnect_settings_gpdetails_desc'] = "If you would like to import first and last name from Google+ (and let users decide to sync it) enable this option.";
$l['mygpconnect_settings_gpdetailsfield'] = "First and last name Custom Profile Field ID";
$l['mygpconnect_settings_gpdetailsfield_desc'] = "Insert the Custom Profile Field ID which corresponds to the First and last name field. Make sure it's the right ID while you fill it! Default void (MyBB doesn't use it)";
$l['mygpconnect_settings_gpsex'] = "Sync sex";
$l['mygpconnect_settings_gpsex_desc'] = "<b>This option DOESN'T WORK PROPERLY at the moment.</b> Unfortunately, sex is one of the biggest problems I have to face off since it's a custom profile field with values which vary from board to board. I'll work on it. The synchronization works (but it causes fields to be italian, so if you are used to PHP you can modify its function in inc/plugins/mygpconnect.php). Just leave it disabled if you don't want to use an useless button.";
$l['mygpconnect_settings_gpsexfield'] = "Sex Custom Profile Field ID";
$l['mygpconnect_settings_gpsexfield_desc'] = "Insert the Custom Profile Field ID which corresponds to the Sex field. Make sure it's the right ID while you fill it! Default to 3 (MyBB's default)";

// default pm text
$l['mygpconnect_default_passwordpm_subject'] = "New password";
$l['mygpconnect_default_passwordpm_message'] = "Welcome on our Forums, dear {user}!

We are pleased you are registering with Google+. We have generated a random password for you which you should take note somewhere if you would like to change your personal infos. We require for security reasons that you specify your password when you change things such as the email, your username and the password itself, so keep it secret!

Your password is: [b]{password}[/b]

With regards,
our Team";

// errors
$l['mygpconnect_error_needtoupdate'] = "You seem to have currently installed an outdated version of MyGoogle+ Connect. Please <a href=\"index.php?module=config-settings&upgrade=mygpconnect\">click here</a> to run the upgrade script.";
$l['mygpconnect_error_nothingtodohere'] = "Ooops, MyGoogle+ Connect is already up-to-date! Nothing to do here...";

// success
$l['mygpconnect_success_updated'] = "MyGoogle+ Connect has been updated correctly from version {1} to {2}. Good job!";