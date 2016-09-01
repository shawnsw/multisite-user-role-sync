<?php
/*
Plugin Name: Multisite User Role Sync
Plugin URI: https://shawnwang.net
Description: Say goodbye to the headache of managing users in a multisite network! Now when a user visits a blog in the network, the user will be added to the blog with your specified roles automatically.
Version: 1.0
Author: Shawn Wang
Author URI: https://shawnwang.net
License: GPLv2 or later
*/

add_action( 'network_admin_menu', 'murs_add_settings_menu' );
add_action( 'admin_init', 'murs_settings_init' );

function murs_add_settings_menu() {

    if (function_exists('add_submenu_page') && is_super_admin()) {
        // network setttings page
        add_submenu_page('settings.php', 'Multisite User Role Sync', 'Multisite User Role Sync', 'manage_network', 'multisite_user_role_sync', 'murs_options_page');
    }
}

function murs_settings_init(  ) { 

    register_setting( 'pluginPage', 'murs_settings' );

    add_settings_section(
        'murs_pluginPage_section', 
        __( '', 'wordpress' ), 
        'murs_settings_section_callback', 
        'pluginPage'
    );

    add_settings_field( 
        'murs_sync_role', 
        __( 'Sync Role:', 'wordpress' ), 
        'murs_sync_role_render', 
        'pluginPage', 
        'murs_pluginPage_section' 
    );

    add_settings_field( 
        'murs_default_role', 
        __( 'Default Role:', 'wordpress' ), 
        'murs_default_role_render', 
        'pluginPage', 
        'murs_pluginPage_section' 
    );

    add_settings_field( 
        'murs_update_role', 
        __( 'Role Update:', 'wordpress' ), 
        'murs_update_role_render', 
        'pluginPage', 
        'murs_pluginPage_section' 
    );
}

function murs_sync_role_render(  ) { 

    $sync_role = get_site_option( 'murs_sync_role' );
    ?>
    <select name='murs_settings[murs_sync_role]'>
        <option value='blog1' <?php selected( $sync_role, 'blog1' ); ?>>User's role in blog #1</option>
        <?php foreach (murs_get_all_roles() as $key => $value) : ?>
        <option value='<?php echo $key; ?>' <?php selected( $sync_role, $key ); ?>><?php echo $value; ?></option>
        <?php endforeach; ?>
    </select>
<?php
}

function murs_default_role_render(  ) { 

    $default_role = get_site_option( 'murs_default_role' );
    ?>
    <select name='murs_settings[murs_default_role]'>
        <?php foreach (murs_get_all_roles() as $key => $value) : ?>
        <option value='<?php echo $key; ?>' <?php selected( $default_role, $key ); ?>><?php echo $value; ?></option>
        <?php endforeach; ?>
    </select>
<?php
}

function murs_update_role_render(  ) { 

    $update_role = get_site_option( 'murs_update_role' );
    ?>
    <select name='murs_settings[murs_update_role]'>
        <option value='false' <?php selected( $update_role, 'false' ); ?>>No</option>
        <option value='true' <?php selected( $update_role, 'true' ); ?>>Yes</option>
    </select>
<?php
}

function murs_settings_section_callback(  ) { 

    echo __( '<div style="background-color:white;padding:15px;margin-right:20px;border:1px dashed;border-color:grey">
        <b>Here is how this plugin works:</b>
        <ol>
            <li>When a user visits a site, this plugin checks if the user is already a memeber of the site.</li>
            <li>If the user is a member, do nothing.</li>
            <li>Otherwise add the user to the site with the user\'s role in blog #1, or a role specifified by you. <b>[Sync Role]</b></li>
            <li>If the user is not an exisiting member of blog #1, the user will be added with a role specified by you. <b>[Default Role]</b></li>
            <li>When a user\'s role is updated, the user\'s new role can be updated across all the bogs the user is member of. <b>[Role Update]</b></li>
        </ol>
        <p><b>Warning:</b> Please be careful with the Role Update function, you may not want a user to be admins on blogs other than his/her own.</p></div>', 'wordpress' );
}

function murs_options_page(  ) { 
    // update options
    if (isset($_POST['murs_settings']))  {
        foreach((array)$_POST['murs_settings'] as $key => $value) {
            update_site_option($key,stripslashes($value));
        }
    }
    ?>

    <?php if(isset($_POST['murs_settings'])) : // settings saved div?>
    <div id="message" class="updated fade">
        <p>
        <?php _e( 'Settings Saved' ) ?>
        </p>
    </div>
    <?php endif; ?>

    <form method='post'>
        <h2>Multisite User Role Sync</h2>

        <?php
        // render the form
        settings_fields( 'pluginPage' );
        do_settings_sections( 'pluginPage' );
        submit_button();
        ?>
    </form>
    <?php
}

function murs_get_all_roles() {
    global $wp_roles;
    // get all roles and reverse in order
    $roles = $wp_roles->get_names();
    return array_reverse($roles);    
}

add_action( 'init', 'murs_sync_user_role', 0 );

function murs_sync_user_role () {

    // if current set up is not multisite then exit
    if (!is_multisite()) return;

    // exit if current user is not logged
    if (get_current_user_id() == 0) return;

    $default_role = get_site_option( 'murs_default_role' ) ? get_site_option( 'murs_default_role' ) : 'subscriber';
    $sync_role = get_site_option( 'murs_sync_role' ) ? get_site_option( 'murs_sync_role' ) : 'blog1';
    
    // error_log("Default role: $default_role Sync role: $sync_role");
    // error_log('Current blog id: ' . get_current_blog_id() . ' Current user id: ' . get_current_user_id());

    // if user is not a member of the current blog
    if (!is_user_member_of_blog()) {
        // if current blog is #1, add user to the blog
        if (get_current_blog_id() == 1) {
            // error_log("adding default role to blog #1: $default_role");
            add_user_to_blog(1, get_current_user_id(), $default_role);
        }
        else {
            // switch to main blog and get current user's roles
            switch_to_blog(1);
            // get user's first role on blog #1
            $roles = wp_get_current_user()->roles;
            if (empty($roles)) {
                add_user_to_blog(1, get_current_user_id(), $default_role);
                $role = $default_role;
            }
            else {
                $role = $roles[0];
            }
            restore_current_blog();

            // add user to the current blog with a sync role
            if ($sync_role == 'blog1') {
                // add current user's first role to the current blog
                // error_log("adding role to current site: $role");
                add_user_to_blog(get_current_blog_id(), get_current_user_id(), $role);
            }
            else {
                // error_log("adding default sync role to current site: $sync_role");
                add_user_to_blog(get_current_blog_id(), get_current_user_id(), $sync_role);
            }
        }
    }
}

#### Updating a role anywhere will result in the new role synced to all the blogs the user is member of. Use at your own risk ####

add_action( 'set_user_role', 'murs_update_user_role', 10, 3 );
# update user role on all user's blogs when user's role is updated
function murs_update_user_role( $user_id, $role, $old_roles )
{
    // if current set up is not multisite then exit
    if (!is_multisite()) return;
    // get the option, exit if function turned off
    $update_role = get_site_option( 'murs_update_role' ) ? get_site_option( 'murs_update_role' ) : 'false';
    if ($update_role == 'false') return;
    // get user's blogs
    $blogs = get_blogs_of_user( $user_id );

    if(!empty($blogs))
    {
        foreach($blogs as $blog)
        {
            // get user's roles in target blog
            switch_to_blog($blog->userblog_id);
            $user = get_user_by( 'ID', $user_id );
            $user_roles = $user->roles;
            restore_current_blog();
            // exit if role already exisits
            if (in_array($role, $user_roles)) {
                // error_log("role $role already exsits");
                continue;
            }
            // else add new role to the target blog
            else {
                // error_log("updating user $user_id role $role to blog $blog->userblog_id");
                add_user_to_blog($blog->userblog_id, $user_id, $role );
            }
        }
    }
}
