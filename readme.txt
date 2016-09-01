=== Multisite User Role Sync ===

Contributors: shawn
Tags: multisite, network, blog, user, roles, access, sync, ldap
Requires at least: 3.2
Tested up to: 4.6
Stable tag: 1.0.9
License: GPL v2 or later

Automatically add users to peer blogs in a multisite network.

== Description ==

Say goodbye to the headache of managing users in a multisite network! Now when a user visits a blog in the network, the user will be added to the blog with your specified roles automatically.

Here is how this plugin works:

1. When a user visits a site, this plugin checks if the user is already a member of the site.
2. If the user is a member, do nothing.
3. Otherwise add the user to the site with the user\'s role in blog #1, or a role specified by you. *[Sync Role]*
4. If the user is not an existing member of blog #1, the user will be added with a role specified by you. *[Default Role]*
5. When a user\'s role is updated, the user\'s new role can be updated across all the bogs the user is member of. *[Role Update]*

== Screenshots ==

1. ![Plugin settings page](screenshot-1.png)

== Installation ==

You can install this plugin directly from your WordPress dashboard:

 1. Go to the *Plugins* menu and click *Add New*.
 2. Search for *Multisite User Role Sync*.
 3. Click *Install Now*.
 4. Activate the plugin.

 Alternatively, you can download the plugin and install manually:
 1. Upload the entire `/multisite-user-role-sync/` folder to the `/wp-content/plugins/` directory.
 2. Activate the plugin.

= Usage =

 1. Navigate to *Network Admin > Settings > Network Settings > Multisite User Role Sync* page.

 2. Specify a Sync Role and a Default Role.

== Frequently Asked Questions ==

= Does this plugin require a multisite installation? =

Yes.

= Where can I configure the plugin =

You can find the settings page here: *Network Admin > Settings > Network Settings > Multisite User Role Sync*

= Where can I get help? =

You can try the [WordPress Support Forum](http://wordpress.org/tags/multisite-user-role-sync).

== Changelog ==

= 1.0 =

* Initial release

