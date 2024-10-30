<?php

/**
 * Plugin Name: CRM
 * Plugin URI: http://wpcrm.com/
 * Description: A simple CRM plugin.
 * Version: 2.0
 * License: GPLv2 or any later version
 * Author: D. Rodenbaugh
 * Author URI: http://www.skylineconsult.com
 * Text Domain: crm
 */

define( 'CRM_PLUGIN_DIR', rtrim( plugin_dir_path( __FILE__ ), '/' ) );
define( 'CRM_PLUGIN_URL', rtrim( plugin_dir_url( __FILE__ ), '/' ) );

ob_start();

require( CRM_PLUGIN_DIR . '/includes/class-plugin.php' );

$crm_version = '1.0.0';

add_action( 'plugins_loaded', 'wpcrm_load_plugin' );
function wpcrm_load_plugin() {
    $plugin = new WPCRM_Plugin();

    $plugin->load_dependencies();
    $plugin->setup();
}

add_action('admin_head', 'wpcrm_adminhead');
function wpcrm_adminhead() {
	if ( isset( $_GET['page'] ) && $_GET['page'] == 'crm/crm.php' ) {
		?>
		<style type="text/css">
		.wrap h2 {margin:1em 0 0 0}
		form.crm div.line {width:95%; margin:auto}
		form.crm div.input {float:left}
		form.crm div.input label {font-size:smaller; margin:0}
		form.crm div.input input, form div.input textarea {width:100%; margin:0}
		form .submit {clear:both;border:0; text-align:right}
		table#crm-table {border-collapse:collapse}
		table#crm-table th {text-align:left}
		table#crm-table tr td {border:2px solid #e5f3ff; margin:0}
		table#crm-table tr:hover td {cursor:pointer}
		form.crm tr input {width:95%; border-color:#e5f3ff; background-color: white}
		<?php echo wpcrm_get_address_card_style() ?>
		</style>
		<?php
	}
}

add_action('admin_menu', 'wpcrm_menus');
function wpcrm_menus() {
    global $crm_basefile;

    add_menu_page( __( 'CRM', 'crm' ), 'CRM', 'edit_others_posts', 'crm/crm.php', 'wpcrm_main' );
    add_submenu_page( 'crm/crm.php', __( 'Options', 'crm' ), __( 'Options', 'crm' ), 'edit_others_posts', 'crm_options', 'wpcrm_options' );

    $crm_basefile = "admin.php";
}

function wpcrm_options() {
    echo wpcrm_render_template( CRM_PLUGIN_DIR . '/templates/admin/settings-admin-page.tpl.php' );
}

/**
 * Outputs the main administration screen, and handles installing/upgrading, saving, and deleting.
 */
function wpcrm_main() {
    global $wpdb, $crm_version, $crm_basefile;
    $show_main = true;

    $action = isset( $_GET['action'] ) ? $_GET['action'] : null;
    $tab = isset( $_GET['tab'] ) ? $_GET['tab'] : null;
    $query_string = isset( $_GET['q'] ) ? $_GET['q'] : null;

    $should_show_note = isset( $_GET['shownote'] ) && $_GET['shownote'] == 'shownote';
    $should_show_reminder = isset( $_GET['showreminder'] ) && $_GET['showreminder'] == 'showreminder';
 
    if ( isset( $_POST['new'] ) && $_POST['new'] ) wpcrm_insert_new_from_post();
	if ( $action == 'delete' ) $show_main = wpcrm_delete_address( $_GET['id'] );
    if ( $action == 'edit' ) $show_main = wpcrm_edit_address( $_GET['id'] );
	
     if ( $action == 'notes_delete' ) $show_main = wpcrm_notes_delete_notes( $_GET['id'] );
     if ( $action == 'notes_edit' ) $show_main = wpcrm_notes_edit_notes( $_GET['id'] );
	 if ( isset( $_POST['new_note'] ) && $_POST['new_note'] ) wpcrm_notes_insert_new_from_post();
	 if ( $should_show_note )
	 {
	 	$show_main = wpcrm_notes_main( $_GET['crmid'] ); 
	 	
	 }
	 
	 if ( $action == 'reminder_delete' ) $show_main = wpcrm_reminder_delete_reminder( $_GET['id'] );
     if ( $action == 'reminder_edit' ) $show_main = wpcrm_reminder_edit_reminder( $_GET['id'] );
	 if ( isset( $_POST['new_reminder'] ) && $_POST['new_reminder'] ) wpcrm_reminder_insert_new_from_post();
	 if ( $should_show_reminder )
	 {
	 	$show_main = wpcrm_reminder_main(); 
	 	
	 }
    if ( isset( $_GET['exportcsv'] ) && $_GET['exportcsv'] == 'exportcsv')
	 {
		wpcrm_exportemail();
	 }
	
	if ( $should_show_note || $should_show_reminder )
		$show_main = false;
    if ($show_main) {
    
    	// Make sure CRM is installed or upgraded.
        $table_name = $wpdb->prefix."crm";
        If ($wpdb->get_var("SHOW TABLES LIKE '$table_name'")!=$table_name
            || get_option("crm_version")!=$crm_version ) {
            // Call the install function here rather than through the more usual
            // activate_blah.php action hook so the user doesn't have to worry about
            // deactivating then reactivating the plugin.  Should happen seemlessly.
            wpcrm_install();
            wpcrm_output_message( sprintf(__('The CRM plugin (version %s) has been installed or upgraded.'), get_option("crm_version")) );
        } ?>
                
        <div class="wrap">
        <div style="text-align:center; width:47%; float:left;">
	        <p style="font-size:110%" align="left">
            <strong><a href="<?php echo $crm_basefile; ?>º?page=crm/crm.php&showreminder=showreminder"><img style="vertical-align:middle;" alt="Reminder List" title="Reminder List" align="top" src="<?php echo esc_url( CRM_PLUGIN_URL . '/resources/images/mailreminder.png' ); ?>" /></a><a href="<?php echo $crm_basefile; ?>?page=crm/crm.php&exportcsv=exportcsv&q=<?php echo $query_string; ?>&tab=<?php echo $tab; ?>"><img alt="Export to CSV" title="Export to CSV" align="top" src="<?php echo esc_url( CRM_PLUGIN_URL . '/resources/images/export.png' ); ?>" style="vertical-align:middle;"/></a></strong>
            &nbsp;&nbsp;&nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp; &nbsp;&nbsp;
            <strong><a href="#new"><?php _e('Add new Contact &darr;'); ?></a></strong>
        </div>
        <div id="contact-info" style="border:10px solid #E5F3FF; margin:0 0 0 50%; padding:5px; width:47%">
        	<em><?php _e('Select an Contact from below to see its details displayed here.'); ?></em>
        </div>
           <br /><br />
            <div style="width:100%;" ><h2 style="margin:0 0 0 0;"><?php _e('CRM'); ?></h2></div>
<?php
if( $tab == "Supplier" )
	$sclass = "current";
if( $tab == "Client" )
	$cclass = "current";
if( $tab == "Opportunity" )
	$oclass = "current";
if( $tab == "" )
	$aclass = "current";
?>
<ul class="subsubsub">
<li><a href="<?php echo $crm_basefile; ?>?page=crm/crm.php" class="<?php echo $aclass; ?>"> All </a> |</li>
<li><a href="<?php echo $crm_basefile; ?>?page=crm/crm.php&tab=Supplier" class="<?php echo $sclass; ?>"> Supplier </a> |</li>
<li><a href="<?php echo $crm_basefile; ?>?page=crm/crm.php&tab=Client" class="<?php echo $cclass; ?>">Client </a> |</li>
<li><a href="<?php echo $crm_basefile; ?>?page=crm/crm.php&tab=Opportunity" class="<?php echo $oclass; ?>">Opportunity </a></li>
</ul><br /><br />

            <div style="width:70%;margin:0 0 0 0;" align="left">
               <form class="crm" action="<?php echo $crm_basefile; ?>?page=crm/crm.php" method="get">
	        	<div style="display:none">
	        		<input type="hidden" name="page" value="crm/crm.php" />
	        		<input type="hidden" name="action" value="search" />
	        	</div>
	        	<p>
	        		<?php _e("Filter messages by search term:"); ?>
	        		<input type="text" name="q" value="<?php echo stripslashes($query_string); ?>" /><input type="submit" value="<?php _e('Search&hellip;'); ?>" />
	        	</p>
	        </form></p>
            </div>
       
        <script type="text/javascript">
        /* <![CDATA[ */
        function click_contact(row, id) {
            document.getElementById('contact-info').innerHTML=document.getElementById('contact-'+id+'-info').innerHTML;
        }
		 
		/* ]]> */
        </script>
        <table style="width:100%; margin:auto" id="crm-table">
            <tr style="background-color:#E5F3FF">
                <?php 
				if($tab == "Opportunity")
				{
				echo '<th>'.__('Name').'</th><th>'.__('Category').'</th><th>'.__('Sales Ranking').'</th>
				<th>'.__('Organisation').'</th><th>'.__('Email address').'</th><th>'.__('Phone number').'</th><th>'.__('Rung').'</th><th>'.__('Note').'</th>'; 
				}
				else
				{
				echo '<th>'.__('Name').'</th><th>'.__('Category').'</th>
				<th>'.__('Organisation').'</th><th>'.__('Email address').'</th><th>'.__('Phone number').'</th><th>'.__('Rung').'</th><th>'.__('Note').'</th>'; 
				}
				
				?>
                
            </tr>
            <?php
			if(trim($tab) != "")
			{
				$sql = "SELECT * FROM ".$wpdb->prefix."crm WHERE
					  category LIKE '%".$wpdb->escape($tab)."%'
	            	  ORDER BY first_name";
			}
            elseif ( $action == 'search' ) {
	            $sql = "SELECT * FROM ".$wpdb->prefix."crm WHERE
	            	first_name LIKE '%".$wpdb->escape($query_string)."%'
	            	OR surname LIKE '%".$wpdb->escape($query_string)."%'
					OR category LIKE '%".$wpdb->escape($query_string)."%'
	            	OR organisation LIKE '%".$wpdb->escape($query_string)."%'
	            	OR email LIKE '%".$wpdb->escape($query_string)."%'
	            	OR phone LIKE '%".$wpdb->escape($query_string)."%'
	            	OR notes LIKE '%".$wpdb->escape($query_string)."%'
					OR rung LIKE '%".$wpdb->escape($query_string)."%'
	            	ORDER BY first_name";
            } else {
	            $sql = "SELECT * FROM ".$wpdb->prefix."crm ORDER BY first_name";
            }
            $results = $wpdb->get_results($sql);
            foreach ($results as $row) {
                if($tab == "Opportunity")
				{
				echo"<tr>
                    <td  onclick='click_contact(this, ".$row->id.")'>".stripslashes($row->first_name." ".$row->surname)."&nbsp;</td><!-- nbsp is to stop collapse -->
                    <td  onclick='click_contact(this, ".$row->id.")'>".stripslashes($row->category)."</td>
					 <td  onclick='click_contact(this, ".$row->id.")'>".stripslashes($row->rank)."</td>
					<td  onclick='click_contact(this, ".$row->id.")'>".stripslashes($row->organisation)."</td>
                    <td onclick='click_contact(this, ".$row->id.")'>".stripslashes($row->email)."</td>
                    <td onclick='click_contact(this, ".$row->id.")'>".stripslashes($row->phone)."</td>
					<td onclick='click_contact(this, ".$row->id.")'>".stripslashes($row->rung)."</td>
					<td><a href='$crm_basefile?page=crm/crm.php&shownote=shownote&crmid=".$row->id."'>Add/View</a></td>
                </tr>";
				}
				else
				{
					echo"<tr>
                    <td  onclick='click_contact(this, ".$row->id.")'>".stripslashes($row->first_name." ".$row->surname)."&nbsp;</td><!-- nbsp is to stop collapse -->
                    <td  onclick='click_contact(this, ".$row->id.")'>".stripslashes($row->category)."</td>
					<td  onclick='click_contact(this, ".$row->id.")'>".stripslashes($row->organisation)."</td>
                    <td onclick='click_contact(this, ".$row->id.")'>".stripslashes($row->email)."</td>
                    <td onclick='click_contact(this, ".$row->id.")'>".stripslashes($row->phone)."</td>
					<td onclick='click_contact(this, ".$row->id.")'>".stripslashes($row->rung)."</td>
					<td><a href='$crm_basefile?page=crm/crm.php&shownote=shownote&crmid=".$row->id."'>Add/View</a></td>
                	</tr>";
				}
            } ?>
        </table>
        <?php foreach ($results as $row) {
            echo "<div class='address-label' id='contact-".$row->id."-info' style='display:none'>\n".
            	 "    <p style='text-align:center'>\n".
            	 "        <a href='$crm_basefile?page=crm/crm.php&action=edit&id=".$row->id."'>".__('[Edit]')."</a>\n".
            	 "        <a href='$crm_basefile?page=crm/crm.php&action=delete&id=".$row->id."'>".__('[Delete]')."</a>\n".
            	 "    </p>\n".
            	 wpcrm_get_address_card($row, "    ").
            	 "</div>";
        } ?>
        
        <h2 style="margin-bottom:1em"><a name="new"></a>Add Contact</h2>
        <form class="crm" action="<?php echo $crm_basefile; ?>?page=crm/crm.php" method="post">
        <?php echo wpcrm_get_address_form(); ?>
        <p class="submit">
            <input type="submit" name="new" value="<?php _e('Add Contact &raquo;'); ?>" />
        </p>
        </form>
        </div><?php
    }
}

function wpcrm_output_message($message) {
	?>
	<div id="message" class="updated fade">
	  <p><strong><?php echo $message ?></strong></p>
	</div>
	<?php
}

function wpcrm_insert_new_from_post() {
	global $wpdb, $crm_basefile;
	if($wpdb->escape($_POST['category']) == "Opportunity")
	{
		if($wpdb->escape($_POST['rank']) == "Client")
			$_POST['category'] = "Client";
		$sql = "INSERT INTO ".$wpdb->prefix."crm SET
		organisation  = '".$wpdb->escape($_POST['organisation'])."',
		first_name    = '".$wpdb->escape($_POST['first_name'])."',
		surname       = '".$wpdb->escape($_POST['surname'])."',
		category      = '".$wpdb->escape($_POST['category'])."',
		email         = '".$wpdb->escape($_POST['email'])."',
		website       = '".$wpdb->escape($_POST['website'])."',
		address_line1 = '".$wpdb->escape($_POST['address_line1'])."',
		address_line2 = '".$wpdb->escape($_POST['address_line2'])."',
		suburb        = '".$wpdb->escape($_POST['suburb'])."',
		postcode      = '".$wpdb->escape($_POST['postcode'])."',
		state         = '".$wpdb->escape($_POST['state'])."',
		country       = '".$wpdb->escape($_POST['country'])."',
		phone         = '".$wpdb->escape($_POST['phone'])."',
		rank         = '".$wpdb->escape($_POST['rank'])."',
		two_first_name    = '".$wpdb->escape($_POST['two_first_name'])."',
		two_surname       = '".$wpdb->escape($_POST['two_surname'])."',
		two_organisation  = '".$wpdb->escape($_POST['two_organisation'])."',
		two_email         = '".$wpdb->escape($_POST['two_email'])."',
		two_website       = '".$wpdb->escape($_POST['two_website'])."',
		two_address_line1 = '".$wpdb->escape($_POST['two_address_line1'])."',
		two_address_line2 = '".$wpdb->escape($_POST['two_address_line2'])."',
		two_suburb        = '".$wpdb->escape($_POST['two_suburb'])."',
		two_postcode      = '".$wpdb->escape($_POST['two_postcode'])."',
		two_state         = '".$wpdb->escape($_POST['two_state'])."',
		two_country       = '".$wpdb->escape($_POST['two_country'])."',
		two_phone         = '".$wpdb->escape($_POST['two_phone'])."',
		notes         = '".$wpdb->escape($_POST['notes'])."'";
	}
	else
	{
		$sql = "INSERT INTO ".$wpdb->prefix."crm SET
		organisation  = '".$wpdb->escape($_POST['organisation'])."',
		first_name    = '".$wpdb->escape($_POST['first_name'])."',
		surname       = '".$wpdb->escape($_POST['surname'])."',
		category      = '".$wpdb->escape($_POST['category'])."',
		email         = '".$wpdb->escape($_POST['email'])."',
		website       = '".$wpdb->escape($_POST['website'])."',
		address_line1 = '".$wpdb->escape($_POST['address_line1'])."',
		address_line2 = '".$wpdb->escape($_POST['address_line2'])."',
		suburb        = '".$wpdb->escape($_POST['suburb'])."',
		postcode      = '".$wpdb->escape($_POST['postcode'])."',
		state         = '".$wpdb->escape($_POST['state'])."',
		country       = '".$wpdb->escape($_POST['country'])."',
		phone         = '".$wpdb->escape($_POST['phone'])."',
		two_first_name    = '".$wpdb->escape($_POST['two_first_name'])."',
		two_surname       = '".$wpdb->escape($_POST['two_surname'])."',
		two_organisation  = '".$wpdb->escape($_POST['two_organisation'])."',
		two_email         = '".$wpdb->escape($_POST['two_email'])."',
		two_website       = '".$wpdb->escape($_POST['two_website'])."',
		two_address_line1 = '".$wpdb->escape($_POST['two_address_line1'])."',
		two_address_line2 = '".$wpdb->escape($_POST['two_address_line2'])."',
		two_suburb        = '".$wpdb->escape($_POST['two_suburb'])."',
		two_postcode      = '".$wpdb->escape($_POST['two_postcode'])."',
		two_state         = '".$wpdb->escape($_POST['two_state'])."',
		two_country       = '".$wpdb->escape($_POST['two_country'])."',
		two_phone         = '".$wpdb->escape($_POST['two_phone'])."',
		notes         = '".$wpdb->escape($_POST['notes'])."'";
	}	
	$wpdb->query($sql);
	wpcrm_output_message(__('The Contact has been added.'));
}

/**
 * Edit a single Contact.
 *
 * @param int $id The ID of the Contact to be edited.
 * @return bool Whether or not any more content should be added to the page after calling this.
 */
function wpcrm_edit_address($id) {
	global $wpdb, $crm_basefile;
	$sql = "SELECT * FROM ".$wpdb->prefix."crm WHERE id='".$wpdb->escape($id)."'";
	$row = $wpdb->get_row($sql);
	if ( isset( $_POST['save'] ) && $_POST['save'] ) {
		if($wpdb->escape($_POST['category']) == "Opportunity")
		{
			if($wpdb->escape($_POST['rank']) == "Client")
				$_POST['category'] = "Client";
			$wpdb->query("UPDATE ".$wpdb->prefix."crm SET
				first_name    = '".$wpdb->escape($_POST['first_name'])."',
				surname       = '".$wpdb->escape($_POST['surname'])."',
				category      = '".$wpdb->escape($_POST['category'])."',
				organisation  = '".$wpdb->escape($_POST['organisation'])."',
				email         = '".$wpdb->escape($_POST['email'])."',
				phone         = '".$wpdb->escape($_POST['phone'])."',
				address_line1 = '".$wpdb->escape($_POST['address_line1'])."',
				address_line2 = '".$wpdb->escape($_POST['address_line2'])."',
				suburb        = '".$wpdb->escape($_POST['suburb'])."',
				postcode      = '".$wpdb->escape($_POST['postcode'])."',
				state         = '".$wpdb->escape($_POST['state'])."',
				country       = '".$wpdb->escape($_POST['country'])."',
				notes         = '".$wpdb->escape($_POST['notes'])."',
				website       = '".$wpdb->escape($_POST['website'])."',
				two_first_name    = '".$wpdb->escape($_POST['two_first_name'])."',
				two_surname       = '".$wpdb->escape($_POST['two_surname'])."',
				two_organisation  = '".$wpdb->escape($_POST['two_organisation'])."',
				two_email         = '".$wpdb->escape($_POST['two_email'])."',
				two_website       = '".$wpdb->escape($_POST['two_website'])."',
				two_address_line1 = '".$wpdb->escape($_POST['two_address_line1'])."',
				two_address_line2 = '".$wpdb->escape($_POST['two_address_line2'])."',
				two_suburb        = '".$wpdb->escape($_POST['two_suburb'])."',
				two_postcode      = '".$wpdb->escape($_POST['two_postcode'])."',
				two_state         = '".$wpdb->escape($_POST['two_state'])."',
				two_country       = '".$wpdb->escape($_POST['two_country'])."',
				two_phone         = '".$wpdb->escape($_POST['two_phone'])."',
				rank          = '".$wpdb->escape($_POST['rank'])."'
				WHERE id ='".$wpdb->escape($_GET['id'])."'");
		}
		else
		{
			$wpdb->query("UPDATE ".$wpdb->prefix."crm SET
				first_name    = '".$wpdb->escape($_POST['first_name'])."',
				surname       = '".$wpdb->escape($_POST['surname'])."',
				category      = '".$wpdb->escape($_POST['category'])."',
				organisation  = '".$wpdb->escape($_POST['organisation'])."',
				email         = '".$wpdb->escape($_POST['email'])."',
				phone         = '".$wpdb->escape($_POST['phone'])."',
				address_line1 = '".$wpdb->escape($_POST['address_line1'])."',
				address_line2 = '".$wpdb->escape($_POST['address_line2'])."',
				suburb        = '".$wpdb->escape($_POST['suburb'])."',
				postcode      = '".$wpdb->escape($_POST['postcode'])."',
				state         = '".$wpdb->escape($_POST['state'])."',
				country       = '".$wpdb->escape($_POST['country'])."',
				notes         = '".$wpdb->escape($_POST['notes'])."',
				two_first_name    = '".$wpdb->escape($_POST['two_first_name'])."',
				two_surname       = '".$wpdb->escape($_POST['two_surname'])."',
				two_organisation  = '".$wpdb->escape($_POST['two_organisation'])."',
				two_email         = '".$wpdb->escape($_POST['two_email'])."',
				two_website       = '".$wpdb->escape($_POST['two_website'])."',
				two_address_line1 = '".$wpdb->escape($_POST['two_address_line1'])."',
				two_address_line2 = '".$wpdb->escape($_POST['two_address_line2'])."',
				two_suburb        = '".$wpdb->escape($_POST['two_suburb'])."',
				two_postcode      = '".$wpdb->escape($_POST['two_postcode'])."',
				two_state         = '".$wpdb->escape($_POST['two_state'])."',
				two_country       = '".$wpdb->escape($_POST['two_country'])."',
				two_phone         = '".$wpdb->escape($_POST['two_phone'])."',
				website       = '".$wpdb->escape($_POST['website'])."',
				rank          = ''
				WHERE id ='".$wpdb->escape($_GET['id'])."'");
		}
		wpcrm_output_message(__('The Contact has been updated.'));
		return true;
	} else {
		?><div class="wrap">
		<h2 style="margin-bottom:1em"><?php _e('Edit Address'); ?></h2>
		<form action="<?php echo $crm_basefile; ?>?page=crm/crm.php&action=edit&id=<?php echo $row->id; ?>"
			  method="post" class="crm">
		<?php echo wpcrm_get_address_form($row); ?>
		<p class="submit">
			<a href='<?php echo $crm_basefile; ?>?page=crm/crm.php'><?php _e('[Cancel]'); ?></a>
			<input type="submit" name="save" value="<?php _e('Save &raquo;'); ?>" />
		</p>
		</form>
		</div><?php
		return false;
	}
}

/**
 * Delete a single Contact from the database.
 *
 * @param int $id The ID of the Contact to be deleted.
 * @return bool Whether or not any more content should be added to the page after calling this.
 */
function wpcrm_delete_address($id) {
	global $wpdb, $crm_basefile;
	$sql = "SELECT * FROM ".$wpdb->prefix."crm WHERE id='".$wpdb->escape($id)."'";
	$row = $wpdb->get_row($sql);
	if ($_GET['confirm']=='yes') {
		$wpdb->query("DELETE FROM ".$wpdb->prefix."crm WHERE id='".$wpdb->escape($id)."'");
		wpcrm_output_message(__('The Contact has been deleted.'));
		return true;
	} else {
		echo  "<div class='wrap'>".
			  "    <p style='text-align:center'>".__('Are you sure you want to delete this Contact?')."</p>\n".
			  "    <div style='border:1px solid black; width:50%; margin:1em auto; padding:0.7em'>\n".
			  wpcrm_get_address_card($row, "        ").
			  "    </div>\n".
			  "    <p style='text-align:center; font-size:1.3em'>\n".
			  "        <a href='$crm_basefile?page=crm/crm.php&action=delete&id=".$row->id."&confirm=yes'>\n".
			  "            <strong>".__('[Yes]')."</strong>\n".
			  "        </a>&nbsp;&nbsp;&nbsp;&nbsp;\n".
			  "	       <a href='$crm_basefile?page=crm/crm.php'>".__('[No]')."</a>\n".
			  "    </p>\n".
			  "</div>\n";
		return false;
	}
}

function wpcrm_get_address_form($data='null') {
	// Set default values (the website field is the only one with a default value).
    if ($data=='null') $website = 'http://'; else $website = $data->website;
	if ($data=='null') $two_website = 'http://'; else $two_website = $data->two_website;

	if ( isset( $data->rung ) && $data->rung == "No" ) {
		$no = "checked";
    } else {
		$no = "";
    }

	if( isset( $data->rung ) && $data->rung == "Yes" ) {
		$yes = "checked";
    } else {
		$yes = "";
    }

    $category = isset( $data->category ) ? $data->category : null;

	$dis = "none";
    $ssel = $csel = $osel = '';

	if ( $category == "Supplier" ) {
		$ssel = "selected";
    } elseif( $category == "Client" ) {
		$csel = "selected";
    } elseif( $category == "Opportunity" ) {
		$osel = "selected";
		$dis = "block";
	}

    $rank = $category = isset( $data->rank ) ? $data->rank : null;
    $se1l = $se12 = $se13 = $se14 = $se15 = $se16 = '';

	if ( $rank == "Ready to buy") {
		$se1l = "selected";
    } elseif( $rank == "Looking for Quotes" ) {
		$se12 = "selected";
    } elseif( $rank == "Buying 6 months" ) {
		$se13 = "selected";
    } elseif( $rank == "First Contact" ) {
		$se14 = "selected";
    } elseif( $rank == "No longer interested" ) {
		$se15 = "selected";
    } elseif( $rank == "Client" ) {
		$se16 = "selected";
    }

    $out = '
           	<div style="width:99%; float:left">

			<div class="input" style="width:50%">
                <label for="first_name">'.__('Category:').'</label>
                <select name="category" style="width:100%" onchange="if(this.value==\'Opportunity\'){getElementById(\'rank\').style.display=\'block\';}else{getElementById(\'rank\').style.display=\'none\';}">			
				<option  value="Supplier" '.$ssel.'>Supplier</option>
				<option  value="Client" '.$csel.'>Client</option>
				<option  value="Opportunity" '.$osel.'>Opportunity</option>
                <option  value="Supplier" '.$ssel.'>Supplier</option>
				</select>
			</div>
			<div class="input" style="width:50%;display:'.$dis.'" id="rank">
                <label for="rank">'.__('Sales Ranking:').'</label>
                <select name="rank" style="width:100%">			
				<option value="Ready to buy" '.$se1l.'>Ready to buy</option>
				<option value="Looking for Quotes" '.$se12.'>Looking for Quotes</option>
				<option  value="Buying 6 months" '.$se13.'>Buying 6 months</option>
				<option value="First Contact" '.$se14.'>First Contact</option>
				<option value="No longer interested" '.$se15.'>No longer interested</option>
				<option value="Client" '.$se16.' >Client</option>
				</select>
            </div>
			</div>
	
	<div style="width:50%; float:left"> 
	<div style="width:99%; float:left">
        <a>'.__('Primary Contact details:').'</a>
    </div>
	<div style="width:50%; float:left">
           
		<div class="line">
            <div class="input" style="width:50%">
                <label for="first_name">'.__('First name:').'</label>
                <input type="text" name="first_name" value="'.stripslashes( isset( $data->first_name ) ? $data->first_name : '' ).'" />
            </div>
            <div class="input" style="width:50%">
                <label for="surname">'.__('Surname:').'</label>
                <input type="text" name="surname" value="'.stripslashes( isset( $data->surname ) ? $data->surname : '' ).'" />
            </div>
        </div>
       
		 <div class="line">
            <div class="input" style="width:100%">
                <label for="email">'.__('Organisation:').'</label>
                <input type="text" name="organisation" value="'.stripslashes( isset( $data->organisation ) ? $data->organisation : '' ).'" />
            </div>
        </div>
        <div class="line">
            <div class="input" style="width:100%">
                <label for="email">'.__('Email Address:').'</label>
                <input type="text" name="email" value="'.stripslashes( isset( $data->email ) ? $data->email : '' ).'" />
            </div>
        </div>
        <div class="line">
            <div class="input" style="width:100%">
                <label for="phone">'.__('Phone:').'</label>
                <input type="text" name="phone" value="'.stripslashes( isset( $data->phone ) ? $data->phone : '' ).'" />
            </div>
        </div>
		  <!--div class="line">
            <div class="input" style="width:100%">
                <table><tr><td colspan="2"><label for="rung">'.__('Rung:').'</label></td></tr>
				<tr>
				<td nowrap><input type="radio" name="rung" value="No" '.$no.'">No</td>
				<td nowrap style="padding-left:30px;"><input type="radio" name="rung" value="Yes" '.$yes.'">Yes</td>
				</tr>
				</table>
			
            </div>
        </div-->
        <div class="line">
            <div class="input" style="width:100%">
                <label for="website">'.__('Website:').'</label>
                <input type="text" name="website" value="'.stripslashes($website).'" />
            </div>
        </div>
        </div>
        <div style="width:50%; float:right">
            <div class="line">
                <div class="input" style="width:100%">
                    <label for="address_line1">'.__('Address Line 1:').'</label>
                    <input type="text" name="address_line1" value="'.stripslashes( isset( $data->address_line1 ) ? $data->address_line1 : '' ).'" />
                </div>
            </div>
            <div class="line">
                <div class="input" style="width:100%">
                    <label for="address_line2">'.__('Address Line 2:').'</label>
                    <input type="text" name="address_line2" value="'.stripslashes( isset( $data->address_line2 ) ? $data->address_line2 : '' ).'" />
                </div>
            </div>
            <div class="line">
                <div class="input" style="width:70%">
                    <label for="suburb">'.__('Suburb:').'</label>
                    <input type="text" name="suburb" value="'.stripslashes( isset( $data->suburb ) ? $data->suburb : '' ).'" />
                </div>
                <div class="input" style="width:30%">
                    <label for="postcode">'.__('Postcode:').'</label>
                    <input type="text" name="postcode" value="'.stripslashes( isset( $data->postcode ) ? $data->postcode : '' ).'" />
                </div>
            </div>
            <div class="line">
                <div class="input" style="width:100%">
                    <label for="state">'.__('State or Territory:').'</label>
                    <input type="text" name="state" value="'.stripslashes( isset( $data->state ) ? $data->state : '' ).'" />
                </div>
            </div>
            <div class="line">
                <div class="input" style="width:100%">
                    <label for="country">'.__('Country:').'</label>
                    <input type="text" name="country" value="'.stripslashes( isset( $data->country ) ? $data->country : '' ).'" />
                </div>
            </div>
        </div>
	</div>
	<div style="width:50%; float:left">
			<div style="width:99%; float:left">
        <a>'.__('Secondary Contact details:').'</a>
    </div>
	<div style="width:50%; float:left">
           
		<div class="line">
            <div class="input" style="width:50%">
                <label for="first_name">'.__('First name:').'</label>
                <input type="text" name="two_first_name" value="'.stripslashes( isset( $data->two_first_name ) ? $data->two_first_name : '' ).'" />
            </div>
            <div class="input" style="width:50%">
                <label for="surname">'.__('Surname:').'</label>
                <input type="text" name="two_surname" value="'.stripslashes( isset( $data->two_surname ) ? $data->two_surname : '' ).'" />
            </div>
        </div>
       
		 <div class="line">
            <div class="input" style="width:100%">
                <label for="email">'.__('Position:').'</label>
                <input type="text" name="two_organisation" value="'.stripslashes( isset( $data->two_organisation ) ? $data->two_organisation : '' ).'" />
            </div>
        </div>
        <div class="line">
            <div class="input" style="width:100%">
                <label for="email">'.__('Email Address:').'</label>
                <input type="text" name="two_email" value="'.stripslashes( isset( $data->two_email ) ? $data->two_email : '' ).'" />
            </div>
        </div>
        <div class="line">
            <div class="input" style="width:100%">
                <label for="phone">'.__('Phone:').'</label>
                <input type="text" name="two_phone" value="'.stripslashes( isset( $data->two_phone ) ? $data->two_phone : '' ).'" />
            </div>
        </div>
		
        <div class="line">
            <div class="input" style="width:100%">
                <label for="website">'.__('Website:').'</label>
                <input type="text" name="two_website" value="'.stripslashes($two_website).'" />
            </div>
        </div>
        </div>
        <div style="width:50%; float:right">
            <div class="line">
                <div class="input" style="width:100%">
                    <label for="address_line1">'.__('Address Line 1:').'</label>
                    <input type="text" name="two_address_line1" value="'.stripslashes( isset( $data->two_address_line1 ) ? $data->two_address_line1 : '' ).'" />
                </div>
            </div>
            <div class="line">
                <div class="input" style="width:100%">
                    <label for="address_line2">'.__('Address Line 2:').'</label>
                    <input type="text" name="two_address_line2" value="'.stripslashes( isset( $data->two_address_line2 ) ? $data->two_address_line2 : '' ).'" />
                </div>
            </div>
            <div class="line">
                <div class="input" style="width:70%">
                    <label for="suburb">'.__('Suburb:').'</label>
                    <input type="text" name="two_suburb" value="'.stripslashes( isset( $data->two_suburb ) ? $data->two_suburb : '' ).'" />
                </div>
                <div class="input" style="width:30%">
                    <label for="postcode">'.__('Postcode:').'</label>
                    <input type="text" name="two_postcode" value="'.stripslashes( isset( $data->two_postcode ) ? $data->two_postcode : '' ).'" />
                </div>
            </div>
            <div class="line">
                <div class="input" style="width:100%">
                    <label for="state">'.__('State or Territory:').'</label>
                    <input type="text" name="two_state" value="'.stripslashes( isset( $data->two_state ) ? $data->two_state : '' ).'" />
                </div>
            </div>
            <div class="line">
                <div class="input" style="width:100%">
                    <label for="country">'.__('Country:').'</label>
                    <input type="text" name="two_country" value="'.stripslashes( isset( $data->two_country ) ? $data->two_country : '' ).'" />
                </div>
            </div>
        </div>
	</div>
		<div class="line" style="width:99%">
			<div class="input" style="width:100%">
				<label for="notes">'.__('Notes:').'</label>
				<textarea name="notes" rows="3">'.stripslashes( isset( $data->notes ) ? $data->notes : '' ).'</textarea>
			</div>
        </div>';
    return $out;
}

function wpcrm_install() {
    global $wpdb, $crm_version;
    $table_name = $wpdb->prefix."crm";
	$table_name1 = $wpdb->prefix."notes";
	$table_name2 = $wpdb->prefix."reminder";
	
    $sql = "
	DROP TABLE IF EXISTS " . $table_name . ";
	CREATE TABLE " . $table_name . " (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        first_name tinytext NOT NULL,
        surname tinytext NOT NULL,
        organisation tinytext NOT NULL,
		category VARCHAR(50) NOT NULL,
        email tinytext NOT NULL,
        phone tinytext NOT NULL,
        address_line1 tinytext NOT NULL,
        address_line2 tinytext NOT NULL,
        suburb tinytext NOT NULL,
        postcode tinytext NOT NULL,
        state tinytext NOT NULL,
        country tinytext NOT NULL, 
        website VARCHAR(55) NOT NULL, 
		two_first_name tinytext NOT NULL,
        two_surname tinytext NOT NULL,
        two_organisation tinytext NOT NULL,
        two_email tinytext NOT NULL,
        two_phone tinytext NOT NULL,
        two_address_line1 tinytext NOT NULL,
        two_address_line2 tinytext NOT NULL,
        two_suburb tinytext NOT NULL,
        two_postcode tinytext NOT NULL,
        two_state tinytext NOT NULL,
        two_country tinytext NOT NULL, 
        two_website VARCHAR(55) NOT NULL,
        notes tinytext NOT NULL,
		rung int(11) NOT NULL,
		rank VARCHAR(50) NOT NULL,
        PRIMARY KEY  (id)
    );
	DROP TABLE IF EXISTS " . $table_name1 . ";
	CREATE TABLE " . $table_name1 . " (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `crmid` int(11) NOT NULL,
	  `notes` text,
	  `keyword` varchar(20) NOT NULL,
	  `date` datetime NOT NULL,
	  PRIMARY KEY (`id`)
	);
	DROP TABLE IF EXISTS " . $table_name2 . ";
	CREATE TABLE " . $table_name2 . " (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `crmid` int(11) NOT NULL,
	  `notes` text NOT NULL,
	  `date` datetime NOT NULL,
	  `sent` enum('0','1') NOT NULL DEFAULT '0',
	  PRIMARY KEY (`id`)
	);";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

    dbDelta($sql);
    update_option('crm_version', $crm_version);
}

/**
 * For other plugins, etc., to use.
 */
function wpcrm_get_select($name, $sel_id=false) {
    global $wpdb;
    $out = "<select name='$name'>";
    $rows = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."crm ORDER BY first_name, organisation");
    foreach($rows as $row) {
		if ($row->id==$sel_id) {
			$selected = " selected";
		} else {
			$selected = "";
		}
        $out .= "<option$selected value='$row->id'>$row->first_name $row->surname";
        if (!empty($row->organisation)) {
        	$out .= " ($row->organisation)";
        }
        $out .= "</option>";
    }
    $out .= "</select>";
    return $out;
}

/**
 * For other plugins, etc., to use.
 */
function wpcrm_get_id_from_email($email) {
    global $wpdb;
    $sql = "SELECT id FROM ".$wpdb->prefix."crm where email='".$wpdb->escape($email)."'";
    $res = $wpdb->get_var($sql);
    return $res;
}

/**
 * For other plugins, etc., to use.
 */
function wpcrm_get_full_name_from_id($id) {
    global $wpdb;
	$sql = "SELECT CONCAT(first_name,' ',surname) FROM ".$wpdb->prefix."crm WHERE id='".$wpdb->escape($id)."'";
    $res = $wpdb->get_var($sql);
    return $res;
}

add_action('wp_head', 'wpcrm_wphead');
function wpcrm_wphead() {
	?>
    <style type="text/css">
      ol.crm-list {padding:0; margin:0}
      li.crm-item {list-style-type:none; border:1px solid #666; padding:3px; margin:0; clear:both}
      <?php echo wpcrm_get_address_card_style() ?>
    </style>
    
    <?php
} // end wpcrm_wphead()

add_filter('the_content', 'wpcrm_list');
function wpcrm_list($content) {
    global $wpdb;
    $sql = "SELECT * FROM ".$wpdb->prefix."crm ORDER BY first_name";
    $results = $wpdb->get_results($sql);
    $out = "<ol class='crm-list'>\n\n";
    foreach ($results as $row) {
        $out .= "  <li class='crm-item'>\n".wpcrm_get_address_card($row, "    ")."  </li>\n\n";
    }
    $out .= "</ol>\n";
    return preg_replace("/<crm \/>|<crm>.*<\/crm>/", $out, $content);
}

function wpcrm_get_address_card_style() {
	return "
      .crm-card p {margin:3px}
      .crm-card .name {font-size:1.2em; font-weight:bolder}
      .crm-card .avatar {float:right; margin:0 0 0 1em}
      .crm-card .address {display:block; margin:0 0.3em 1em 1em; width:38%; float:left; font-size:smaller}
      .crm-card .address span {}
      .crm-card .notes {font-size:smaller; padding:4px}
	";
}

/**
 * @param 
 * @return string HTML to go within a containing element.
 */
function wpcrm_get_address_card($data, $pad="") {
	$out = "$pad<div class='crm-card vcard'>\n".
		"$pad    ".get_avatar($data->email)."\n".
		"$pad    <p>\n".
		wpcrm_get_if_not_empty("$pad        <strong><span class='fn name'>%s</span></strong>\n", stripslashes($data->first_name." ".$data->surname)).
		wpcrm_get_if_not_empty("$pad        <br><em><span class='org'>%s</span></em>\n", stripslashes($data->category)).		
		wpcrm_get_if_not_empty("$pad        <span class='org'>(%s)</span>\n", stripslashes($data->rank)).
		wpcrm_get_if_not_empty("$pad        <br><span class='org'>%s</span>\n", stripslashes($data->organisation)).
		wpcrm_get_if_not_empty("$pad        <a class='email' href='mailto:%1\$s'>%1\$s</a><br />\n", stripslashes($data->email)).
		wpcrm_get_if_not_empty("$pad        <span class='tel phone'>%s</span>\n", stripslashes($data->phone)).
		wpcrm_get_if_not_empty("$pad        <a class='website url' href='%1\$s'>%1\$s</a>\n", stripslashes($data->website)).
		"$pad    </p>\n";
	if ( !empty($data->address_line1) || !empty($data->suburb) || !empty($data->postcode) || !empty($data->state) || !empty($data->country) ) {
		$out .= "$pad    <div class='address adr'>\n";
		if (!empty($data->address_line1) || !empty($data->address_line2)) {
			$out .= "$pad      <span class='street-address'>\n".
				wpcrm_get_if_not_empty("$pad        <span class='address-line1'>%s</span><br />\n", stripslashes($data->address_line1)).
				wpcrm_get_if_not_empty("$pad        <span class='address-line2'>%s</span><br />\n", stripslashes($data->address_line2)).
			"$pad      </span>\n";
		}
		$out .= wpcrm_get_if_not_empty("$pad      <span class='suburb locality'>%s</span>\n", stripslashes($data->suburb)).
			wpcrm_get_if_not_empty("$pad      <span class='postcode postal-code'>%s</span><br />\n", stripslashes($data->postcode)).
			wpcrm_get_if_not_empty("$pad      <span class='state region'>%s</span>\n", stripslashes($data->state)).
			wpcrm_get_if_not_empty("$pad      <span class='country country-name'>%s</span>\n", stripslashes($data->country)).
		"$pad    </div>\n";
	}
	$out .=  "$pad    <div style='clear:both'></div>\n$pad</div>\n";
	
	
	$out .= "$pad<div class='crm-card vcard'>\n".
		
		"$pad    <p>\n".
		wpcrm_get_if_not_empty("$pad        <strong><span class='fn name'>%s</span></strong><br>", stripslashes($data->two_first_name." ".$data->two_surname)).
		wpcrm_get_if_not_empty("$pad        <span class='org'>%s</span>\n", stripslashes($data->two_organisation)).
		wpcrm_get_if_not_empty("$pad        <a class='email' href='mailto:%1\$s'>%1\$s</a><br />\n", stripslashes($data->two_email)).
		wpcrm_get_if_not_empty("$pad        <span class='tel phone'>%s</span>\n", stripslashes($data->two_phone)).
		wpcrm_get_if_not_empty("$pad        <a class='website url' href='%1\$s'>%1\$s</a>\n", stripslashes($data->two_website)).
		"$pad    </p>\n";
	if ( !empty($data->two_address_line1) || !empty($data->two_suburb) || !empty($data->two_postcode) || !empty($data->two_state) || !empty($data->two_country) ) {
		$out .= "$pad    <div class='address adr'>\n";
		if (!empty($data->two_address_line1) || !empty($data->two_address_line2)) {
			$out .= "$pad      <span class='street-address'>\n".
				wpcrm_get_if_not_empty("$pad        <span class='address-line1'>%s</span><br />\n", stripslashes($data->two_address_line1)).
				wpcrm_get_if_not_empty("$pad        <span class='address-line2'>%s</span><br />\n", stripslashes($data->two_address_line2)).
			"$pad      </span>\n";
		}
		$out .= wpcrm_get_if_not_empty("$pad      <span class='suburb locality'>%s</span>\n", stripslashes($data->two_suburb)).
			wpcrm_get_if_not_empty("$pad      <span class='postcode postal-code'>%s</span><br />\n", stripslashes($data->two_postcode)).
			wpcrm_get_if_not_empty("$pad      <span class='state region'>%s</span>\n", stripslashes($data->two_state)).
			wpcrm_get_if_not_empty("$pad      <span class='country country-name'>%s</span>\n", stripslashes($data->two_country)).
		"$pad    </div>\n";
	}
	$out .= wpcrm_get_if_not_empty("$pad    <div class='notes note'>\n$pad    %s\n$pad    </div>\n", stripslashes($data->notes)).
		 "$pad    <div style='clear:both'></div>\n$pad</div>\n";
	return $out;
}

function wpcrm_get_if_not_empty($format,$var) {
	if (!empty($var)) {
		return sprintf($format, $var);
	}
}
