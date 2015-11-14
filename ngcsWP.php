<?php
/*
Plugin Name: NGCS_WP 
Plugin Script: ngcsWP.php
Plugin URI: https://github.com/uaktags/ngcsWP
Description: Wordpress Plugin to communicate with 1and1.com's Cloud Servers
Version: 0.1
License: GPL
Author: Tim Garrity
Author URI: https://timgarrity.me

=== RELEASE NOTES ===
2015-11-14 - v0.1 - first version
*/

require 'vendor/autoload.php';

use NGCSv1\Adapter\HttpAdapter;
use NGCSv1\NGCSv1;


/*
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
Online: http://www.gnu.org/licenses/gpl.txt
*/

// uncomment next line if you need functions in external PHP script;
// include_once(dirname(__FILE__).'/some-library-in-same-folder.php');

add_action( 'admin_menu', 'ngcs_api_add_admin_menu' );
add_action( 'admin_init', 'ngcs_api_settings_init' );
function my_enqueue($hook) {
    wp_enqueue_script('jquery');
    wp_register_style( 'jqueryDataTableCSS', '//cdn.datatables.net/1.10.9/css/jquery.dataTables.css',false, '1.0.0' );
    wp_enqueue_style( 'jqueryDataTableCSS' );
    wp_register_style( '1and1CSS', plugin_dir_url( __FILE__ ) . 'inc/1and1.css',false, '1.0.0' );
    wp_enqueue_style( '1and1CSS' );
    wp_enqueue_script( 'jqueryDataTable', '//cdn.datatables.net/1.10.9/js/jquery.dataTables.js' );
    wp_enqueue_script( '1and1Jquery', plugin_dir_url( __FILE__ ) . 'inc/jquery.js' );
    wp_localize_script( '1and1Jquery', 'OneandOneParams', array('path'=>plugin_dir_url( __FILE__ )) );
}
if(isset($_GET['page'])&& $_GET['page'] == '1n1_ngcs_api')
    add_action( 'admin_enqueue_scripts', 'my_enqueue' );
else
    add_action( 'wp_enqueue_scripts', 'my_enqueue' );

add_action( 'wp_ajax_1and1_newserver', 'newserver_callback' );
add_action( 'wp_ajax_nopriv_1and1_newserver', 'newserver_callback' );
function newserver_callback() {
    $options = get_option( 'ngcs_api_settings' );
    $adapter = new HttpAdapter($options['ngcs_api_text_field_0']);
    $ngcs = new NGCSv1($adapter);
    $server = $ngcs->appliances();
    wp_die(var_dump(json_encode($server->getAll())));
    $server->create('WP_New_Server', ['vcore'=>2, 'cores_per_processor'=>1, 'ram'=>'2', 'hdds'=>array(array('size'=>2000, 'is_main'=>true))], 'B5F778B85C041347BCDCFC3172AB3F3C' );

}

function ngcs_api_add_admin_menu(  ) {

    add_menu_page( '1&1 NGCS API', '1&1 NGCS API', 'manage_options', '1n1_ngcs_api', 'ngcs_api_options_page' );

}


function ngcs_api_settings_init(  ) {

    register_setting( 'pluginPage', 'ngcs_api_settings' );

    add_settings_section(
        'ngcs_api_pluginPage_section',
        __( 'API Options', 'ngcs-api' ),
        'ngcs_api_settings_section_callback',
        'pluginPage'
    );

    add_settings_field(
        'ngcs_api_text_field_0',
        __( 'API Key', 'ngcs-api' ),
        'ngcs_api_text_field_0_render',
        'pluginPage',
        'ngcs_api_pluginPage_section'
    );


}


function ngcs_api_text_field_0_render(  ) {

    $options = get_option( 'ngcs_api_settings' );
    ?>
    <input type='text' name='ngcs_api_settings[ngcs_api_text_field_0]' value='<?php echo $options['ngcs_api_text_field_0']; ?>'>
<?php

}


function ngcs_api_settings_section_callback(  ) {

    echo __( 'This section description', 'ngcs-api' );

}

function server_management_page()
{
    ?>
    <table id="server_table" class="display">
        <thead>
            <tr>
                <th>Server Name</th>
                <th>Status</th>
                <th>OS</th>
                <th>IP</th>
                <th>CPU</th>
                <th>Ram</th>
                <th>SSD</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $options = get_option( 'ngcs_api_settings' );
        // create an adapter with your user's API Token
        // found in your CloudPanel under "Users"
        $adapter = new HttpAdapter($options['ngcs_api_text_field_0']);

        // create a ngcs object with the previous adapter
        $ngcs = new NGCSv1($adapter);
        $server = $ngcs->server();
        $servers = $server->getAll();
        foreach($servers as $k)
        {
            $hardware = $k->hardware;
            $status = '<div class="circle '.$k->status['state'].'"></div>';
            $appliance = $ngcs->appliances()->getById($k->image->id);
            $image = $appliance->os . ' ' . $appliance->architecture . 'Bit';
            $osver = $appliance->osVersion;
            $ip = $k->ips['0']->ip;
            echo "<tr style='height:30px;'>
                    <td>$k->name</td>
                    <td><center>$status</center></td>
                    <td><center><span class='os $osver'>$image</span></center></td>
                    <th>$ip</th>
                    <th>$hardware->vcore</th>
                    <th>$hardware->ram</th>
                    <th>SSD</th>
                  </tr>
                    ";
        }
        ?>
        </tbody></table>
    <?php
}

function new_server_page()
{
    ?>
    <label for="server_name">Server Name:</label>
    <input id="server_name" name="server_name" value="My WP Server"/><br/>
    <label for="ram">Ram:</label>
    <select name="ram" id="ram">    <?php
        $x = 1;
        while($x <= 128)
        {
            echo '<option value="'.$x.'">'.$x .' GB</option>';
            $x++;
        }
        ?>
    </select><br/>
    <label for="ssd">SSD:</label>
    <select name="ssd" id="ssd">
        <?php
        $x = 20;
        $y = 20;
        while($x <= 500)
        {
            echo '<option value="'.$x.'">'.$x .' GB</option>';
           $x += $y;
        }
        ?>
    </select><br/>
    <label for="cpu">Processors:</label>
    <select name="cpu" id="cpu">
        <?php
        $x = 1;
        while($x <= 16)
        {
            echo '<option value="'.$x.'">'.$x.' Processors</option>';
            $x++;
        }
        ?>
    </select><br/>
    <label for="per">Cores per Processor:</label>
    <select name="per" id="per">
        <?php
        $x = 1;
        while($x <= 16)
        {
            echo '<option value="'.$x.'">'.$x.' vCores</option>';
            $x++;
        }
        ?>
    </select><br/>
    <label for="image">Image:</label>
    <select name="image" id="image">

    </select><br/>
        <button type="submit" id="NEWSERVER-BTN">Submit</button>

    <?php
}

function ngcs_api_options_page(  )
{

    ?>
    <form action='options.php' method='post'>


    </form>

    <div class="wrap">
        <div id="icon-themes" class="icon32"></div>
        <h2>1&1 CloudServer Management</h2>
        <?php settings_errors(); ?>

        <?php
        $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'api';
        ?>

        <h2 class="nav-tab-wrapper">
            <a href="?page=1n1_ngcs_api&tab=api"
               class="nav-tab <?php echo $active_tab == 'api' ? 'nav-tab-active' : ''; ?>">1&1 API</a>
            <a href="?page=1n1_ngcs_api&tab=server_management"
               class="nav-tab <?php echo $active_tab == 'server_management' ? 'nav-tab-active' : ''; ?>">Server
                Management</a>
            <a href="?page=1n1_ngcs_api&tab=new_server"
               class="nav-tab <?php echo $active_tab == 'new_server' ? 'nav-tab-active' : ''; ?>">Create Server</a>
        </h2>




            <?php
            if ($active_tab == 'api') {
            ?>
                <form method="post" action="options.php">
            <?php
                settings_fields('pluginPage');
                do_settings_sections('pluginPage');
                submit_button();
            ?>
                </form>
            <?php
            } else if ($active_tab == 'server_management') {
                server_management_page();
            } else if ($active_tab == 'new_server') {
                new_server_page();
            }
            ?>


    </div>
    <?php
}


add_shortcode('show_servers_clientside', 'server_management_page');
?>
