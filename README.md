MyGoogle+ Connect
===============================

> **Current version** 1.0  
> **Dependencies** [PluginLibrary][1]  
> **Author** Shade  

General
-------

MyGoogle+ Connect is meant to be the missing bridge between Google+ and MyBB. It lets your users login with their Google+ account, registering if they don't have an account on your board, and linking their Google+ account to their account on your board if they have one already.

It has been built from [MyFacebook Connect][2] code and it's considered its twin plugin.

The plugin adds 21 settings into your Admin Control Panel which let you specify the Google+ Client ID, Google+ Client Secret, Google+ API Key, the post-registration usergroup the user will be inserted when registering through Google+, whether to use fast one-click registrations and other minor settings.

MyGoogle+ Connect currently comes with the following feature list:

* Connect any user to your MyBB installation with Google+
* One-click login
* One-click registration if setting "Fast registration" is enabled, else the user will be asked for a new username, a new email and data syncing permissions
* Automatically synchronizes Google+ account data with MyBB account, including avatar, cover (if Profile Pictures plugin is installed), location and biography
* Already-registered users can link to their Google+ account manually from within their User Control Panel
* Google+ linked users can choose what data to import from their Google+ account from within their User Control Panel
* Works for all MyBB 1.6 installations
* You can set a post-registration usergroup to insert the Google+ registered users, meaning a smoother user experience
* You can notify a newly registered user with a PM containing his randomly generated password. You have full control on the subject, the sender and the message of the PM that you can edit from your Admin Control Panel
* You have full control over synchronized data. You can choose what data to let your users sync with their Google+ accounts by simply enabling the settings into the Admin Control Panel
* Redirects logged in/registered users to the same page they came from
* *It works*
* *It's free*

Future updates
-------------

Would you like to see a feature developed for MyGoogle+ Connect? No problem, just open a new Issue here on GitHub and I'll do my best to accomplish your request!

It is based upon Google PHP Client 0.6.6. It is free as in freedom.

[1]: http://mods.mybb.com/view/PluginLibrary
[2]: http://github.com/Shade-/MyFacebook-Connect
