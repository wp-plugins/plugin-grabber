<?php 
/*
Plugin Name: Plugin Grabber
Plugin URI: http://www.avdude.com/plugingrabber
Description: This wordpress plugin allows you to create and download a backup of a plugin or your entire plugins directory. Very useful to use just before updating a plugin. Adds menu item to Plugins & Tools
Author: David Fleming - Edge Technology Consulting
Author URI: http://www.avdude.com
*/

/*  Copyright 2012  DAVID FLEMING  (email : CONSULTANT@AVDUDE.COM)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/*
Todo:
 
*/
//Define path variables
define("PLGGBR_PLUGINPATH", "/" . plugin_basename(dirname(__file__)) . "/");
define("PLGGBR_PLUGINFULLURL", WP_PLUGIN_URL . PLGGBR_PLUGINPATH);
#WordPress Initialization
add_action('admin_menu', 'plggbr_admin_menu');
#Add Menu Function
function plggbr_admin_menu(){
    global $wpdb;
    $version = "PlugInGbr_1.0";
    $role = 'manage_options';
    #Create Admin Menus
    //add_menu_page($version, $version, $role, __file__, 'plugin_grabber');
    add_submenu_page( 'plugins.php', 'Plugin Grabber', 'Plugin Grabber', $role, __file__, 'plugin_grabber');
    add_submenu_page( 'tools.php', 'Plugin Grabber', 'Plugin Grabber', $role, __file__, 'plugin_grabber');
    
}
#set plugin global variables
global $plggbr_dir;
$plggbr_dir = WP_CONTENT_DIR."/uploads/pluginBU";


function plggbr_checkdir(){
    global $plggbr_dir;
    if  (file_exists($plggbr_dir)) {
        $message = 'The backup directory is '.$plggbr_dir;
        plggbr_showMessage($message, 'updated_below');
        if(!is_writable($plggbr_dir)) {
			$message = 'Backup directory is not writable.';
            plggbr_showMessage($message, 'error');

		}
    } else {
    if (wp_mkdir_p($plggbr_dir)) {
        $message =  'The backup direcotry, '.$plggbr_dir.' has been created.';
        plggbr_showMessage($message, 'updated_below');
        if(!is_writable($plggbr_dir)) {
			$message = 'Backup directory is not writable.';
            plggbr_showMessage($message, 'error');
            
		}
        }
    }
}

function plugin_grabber(){
    global $plggbr_dir;
?>
<div class="wrap">
<?php
$action = $_REQUEST['action'];
switch ($action){
    case 'backup_plugin':
    ?>
    <img src="<?php echo PLGGBR_PLUGINFULLURL ?>images/loading.gif" />
    <?php
    plggbr_getplugin( $_POST["plugin"]);
    break;
    case 'delete_bu':
    ?>
    <img src="<?php echo PLGGBR_PLUGINFULLURL ?>images/loading.gif" />
    <?php
    plggbr_deletebu( $_POST["plugin"]);
    break;    
    default:
    plggbr_checkdir();
    ?>
    <div id="container" >
      <div style="float: left;">
        <a href="http://www.avdude.com/plugingrabber">
            <img src="<?php echo PLGGBR_PLUGINFULLURL ?>images/plugingrabber.png" alt="Plugin Grabber for Wordpress" />
        </a>
      </div>
      <div style="float: left;">
        <br /><br /><?php plggbr_donate();?>
      </div>
    <div style="clear: both;"></div>
    </div>
<br />
    <?
    ?>
    <p>This will create a backup of a single plugin or all your plugins.<br />
    Backups are stored at <font color="blue"><?php echo $plggbr_dir;?></font><br />
    You can create, download or delete a backup of one or all of your sites plugins from this page.  <br />
    If a backup already exists of the plugin selected, it will overwrite it.</p>
    
        <hr /><br /><form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post">
        <strong><label for="plugin">Select plugin to backup: </label></strong>
        <select name="plugin" id="plugin">
        <option value="ALL">All Plugins</option>
    <?php
    $plugins = get_plugins();
    foreach ( $plugins as $plugin_key => $a_plugin ) {
		$plugin_name = $a_plugin['Name'];
		if ( $plugin_key == $plugin )
			$selected = " selected='selected'";
		else
			$selected = '';
		$plugin_name = esc_attr($plugin_name);
		$plugin_key = rtrim(plugin_dir_path(esc_attr($plugin_key)), '/'); 

		echo "\n\t<option value=\"$plugin_key\" $selected>$plugin_name</option>";
	}
    ?>
        </select>
        <input type="submit" name="getplugins" value="Backup PlugIn(s)" onclick="alert('Be Patient, this might take a minute!')"/>   
        <input type="hidden" name="action" id="action" value="backup_plugin"/>
        </form> 
        <br />
        <br />
    <?php 
    
    plggbr_ListZips(array('index.php'));
    }
?>
</div>
<?php
}

function plggbr_ListZips($exclude){
    global $plggbr_dir;
    $ffs = scandir($plggbr_dir); 
    echo "Current Backups Available:";
    echo '<ul class="ulli">'; 
    foreach($ffs as $ff){ 
        if(is_array($exclude) and !in_array($ff,$exclude)){ 
            if($ff != '.' && $ff != '..'){ 
            if(!is_dir($plggbr_dir.'/'.$ff)){ 
            echo '<li><a href="'.WP_CONTENT_URL.'/uploads/pluginBU/'.$ff.'"><button  style="background-color:#ccff99;width:400px" type="button"> Download Plugin Backup( '.$ff.' ) </button></a>';
             } 
        } }
        } 
        
    echo '</ul>';
    echo "Remove Backup:";
    echo '<ul class="ulli">'; 
    foreach($ffs as $ff){ 
        if(is_array($exclude) and !in_array($ff,$exclude)){ 
            if($ff != '.' && $ff != '..'){ 
            if(!is_dir($plggbr_dir.'/'.$ff)){ 
            echo '<li><form action="'.$_SERVER['REQUEST_URI'].'" method="post">';
            echo '<input type="hidden" name="action" id="action" value="delete_bu"/>';
            echo '<input type="hidden" name="plugin" id="plugin" value="'.$ff.'"/>';
            echo '<input style="background-color:pink;width:400px" type="submit" name="deletbu" value="  Delete Plugin Backup ( '.$ff.' )  " onclick="return confirm(\'Deleting File '.$ff.'\')"/>';   
            echo '</form>';

            } 
        } }
    } 
        
    echo '</ul>';
    
    
     
} 

function plggbr_deletebu($budelete){
    global $plggbr_dir;
    if (unlink( $plggbr_dir.'/'.$budelete)){
        $message = "File ".$budelete." has been removed!<br/>Returning to Plugin Grabber Main Page . . . . ";
        plggbr_showMessage($message, 'updated_below');
        echo '<META HTTP-EQUIV="refresh" CONTENT="3;URL='.$_SERVER['REQUEST_URI'].'">';
    }
}

function plggbr_getplugin($plugin){
        global $plggbr_dir;
        //set folder location to zip
        if ($plugin == 'ALL'){$wp_plugins_dir = WP_CONTENT_DIR.'/plugins';} 
        else {$wp_plugins_dir = WP_CONTENT_DIR.'/plugins/'.$plugin;}
        //verify zip class in wordpress is available
        if(!class_exists('PclZip')){require_once(ABSPATH.'/wp-admin/includes/class-pclzip.php');}
        //add last slash to the backup directory
        $upload_dir = $plggbr_dir."/";
        //create filename for the backup, must include directory path
        $filename = $upload_dir.$plugin.'.zip';
        //instantiate a new zipclass with the defined filename
        $archive = new PclZip($filename);
        echo $wp_plugins_dir;
        
        //create the zipfile
        if (!$archive->create($wp_plugins_dir,PCLZIP_OPT_REMOVE_PATH,WP_CONTENT_DIR)) { 
            $message =  "Backup Creation Failed!";
            plggbr_showMessage($message, 'error');
            }
        // Provide download link for new zip file
        $message = "File ".$plugin." has been backed up!<br/>Returning to Plugin Grabber Main Page . . . . ";
        plggbr_showMessage($message, 'updated_below');
        echo '<META HTTP-EQUIV="refresh" CONTENT="3;URL='.$_SERVER['REQUEST_URI'].'">';
}

/**
 * Just show our message (with possible checking if we only want
 * to show message to certain users.
 */
function plggbr_showMessage($message, $msg_type)
{
	switch ($msg_type){
	   case 'error':
       echo '<div id="message" class="error">';
       break;
       case 'updated':
        echo '<div id="message" class="updated">';
       break;
       case 'updated_fade':
       echo '<div id="message" class="updated fade">';
       break;
       case 'updated_below':
       echo '<div id="message" class="updated below-h2">';
       break; 
       case 'updated_highlight':
       echo '<div id="message" class="updated highlight">';
       break;
       default:
       echo '<div id="message" class="updated">';
       }
    echo "<p><strong>$message</strong></p></div>";
}    
function plggbr_showAdminMessages()
{
    // Shows as an error message. You could add a link to the right page if you wanted.
    plggbr_showMessage("You need to upgrade your database as soon as possible...", true);

    // Only show to admins
    if (user_can('manage_options')) {
       plggbr_showMessage("Hello admins!");
    }
}


/** 
  * Call showAdminMessages() when showing other admin 
  * messages. The message only gets shown in the admin
  * area, but not on the frontend of your WordPress site. 
  */
//add_action('admin_notices', 'plggbr_showAdminMessages'); 

function plggbr_donate(){
    ?>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="3NNU6H7A3G4M2">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
</form>
<?php
} 
?>