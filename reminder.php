<?php
function wpcrm_reminder_main() {
      global $wpdb, $crm_version, $crm_basefile;
//print_r($wpdb);

    $show_main = true;
    if ($show_main) {
    
		 ?>
                
        <div class="wrap">
        <div style="text-align:center; width:50%; float:left">
	        <p style="font-size:110%"><strong><a href="#new"><?php _e('Add new Reminder &darr;'); ?></a></strong></p>
	       
        </div>
        <div style="text-align:center; width:50%; float:left">
        	 <p style="font-size:110%"><strong><a href="<?php echo $crm_basefile; ?>?page=crm/crm.php"><?php _e('Back to Contact List'); ?></a></strong></p>
        </div>
		<?php
      
	        $sql = "SELECT n.*,c.first_name,c.surname FROM ".$wpdb->prefix."reminder as n, ".$wpdb->prefix."crm as c where c.id = n.crmid ORDER BY date desc";
			//echo $sql;
            $results = $wpdb->get_results($sql);
		
		?>
        <h2 style="margin-top:0">Reminder</h2>
        <table style="width:100%; margin:auto" id="crm-table">
            
            
            <tr style="background-color:#E5F3FF">
                <?php echo '<th>'.__('Customer Name').'</th><th>'.__('Purpose').'</th><th>'.__('Date').'</th><th>'.__('Edit/Delete').'</th>'; ?>
            </tr>
            <?php
			//print_r($results);
            foreach ($results as $row) {
                echo"<tr>
                     <td width='20%'>".stripslashes($row->first_name)." ".stripslashes($row->surname)."</td>
					<td width='50%'>".stripslashes($row->notes)."</td>
                    <td width='20%'>".stripslashes($row->date)."</td>
					<td width='10%'><a href='$crm_basefile?page=crm/crm.php&action=reminder_edit&id=".$row->id."'>".__('[Edit]')."</a>&nbsp;&nbsp;<a href='$crm_basefile?page=crm/crm.php&action=reminder_delete&id=".$row->id."'>".__('[Delete]')."</a></td>
                </tr>";
            } 
			if(count($results)<=0)
			{	
				 echo"<tr>
                    	<td colspan='4' align='center'>Sorry, No records found.</td>
					</tr>";
			}
			?>

        </table>
      
        
        <h2 style="margin-bottom:1em"><a name="new"></a>Add Reminder</h2>
        <form class="crm" action="<?php echo $crm_basefile; ?>?page=crm/crm.php&showreminder=showreminder" method="post" name="form1">
        <?php echo wpcrm_get_reminder_form(); ?> 
        <p class="submit">
        	
           
            <input type="submit" name="new_reminder" value="<?php _e('Add Reminder &raquo;'); ?>" />
        </p>
        </form>
        </div><?php
    }
}
function wpcrm_get_reminder_form($data='null') {
	// Set default values (the website field is the only one with a default value).
   	 global $wpdb, $crm_version, $crm_basefile;
	
	  $crmcombo = "<select name='crmid'>";
    $rows = $wpdb->get_results("SELECT first_name,surname,id,organisation FROM ".$wpdb->prefix."crm ORDER BY first_name,surname");
    foreach($rows as $row) {
		if ( isset( $data->crmid ) && $row->id == $data->crmid ) {
			$selected = " selected";
		} else {
			$selected = "";
		}
        $crmcombo .= "<option$selected value='$row->id'>$row->first_name $row->surname";
        if (!empty($row->organisation)) {
        	$crmcombo .= " ($row->organisation)";
        }
        $crmcombo .= "</option>";
    }
    $crmcombo .= "</select>";
	
			
    $out = '
		<div class="line" style="width:97%">
			<div class="input" style="width:100%">
				<label for="reminder">'.__('Customer Name:').'</label>
				<bR>
				'.$crmcombo.'
			</div>
        </div>
		<div class="line" style="width:97%">
			<div class="input" style="width:100%">
				<label for="reminder">'.__('Purpose:').'</label>
				<textarea name="notes" rows="3">'.stripslashes( isset( $data->notes ) ? $data->notes : '' ).'</textarea>
			</div>
        </div>
		<div class="line" style="width:97%">
			<div class="input" style="width:15%">
				<label for="reminder">'.__('Date:').'</label>
				<input  name="date" value="'.stripslashes( isset( $data->date ) ? $data->date : '' ).'" size="19" readonly="readonly" onClick="if(self.gfPop)gfPop.fPopCalendar(document.form1.date);return false;">
			</div>
        </div>
		 
		<!--  PopCalendar(tag name and id must match) Tags should not be enclosed in tags other than the html body tag. -->
<iframe width=188 height=166 name="gToday:datetime:agenda.js:gfPop:plugins_time.js" id="gToday:datetime:agenda.js:gfPop:plugins_time.js" src="' . esc_url( CRM_PLUGIN_URL . '/Calender/DateTime/ipopeng.htm' ) . '" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
		';
		
    return $out;
}
function wpcrm_reminder_insert_new_from_post() {

	global $wpdb, $crm_basefile;
    $date = date( 'Y-m-d h:i:s', strtotime( $_POST['date'] ) );
	$sql = "INSERT INTO ".$wpdb->prefix."reminder SET
		crmid    = '".$wpdb->escape($_POST['crmid'])."',
		notes     = '".$wpdb->escape($_POST['notes'])."',
		date     = '".$wpdb->escape( $date )."'";
		
	$wpdb->query($sql);
	wpcrm_output_message(__('The Reminder has been added.'));
	//echo "dsdsds";die;
}
function wpcrm_reminder_delete_reminder($id) {
	global $wpdb, $crm_basefile;
	$sql = "SELECT * FROM ".$wpdb->prefix."reminder WHERE id='".$wpdb->escape($id)."'";
	$row = $wpdb->get_row($sql);
	
	if ($_GET['confirm']=='yes') {
		$wpdb->query("DELETE FROM ".$wpdb->prefix."reminder WHERE id='".$wpdb->escape($id)."'");

		wpcrm_output_message(__('The Reminder has been deleted.'));
		return true;
	} else {
		echo  "<div class='wrap'>".
			  "    <p style='text-align:center'>".__('Are you sure you want to delete this reminder?')."</p>\n".
			  "    <p style='text-align:center; font-size:1.3em'>\n".
			  "        <a href='$crm_basefile?page=crm/crm.php&showreminder=showreminder&action=reminder_delete&id=".$row->id."&confirm=yes'>\n".
			  "            <strong>".__('[Yes]')."</strong>\n".
			  "        </a>&nbsp;&nbsp;&nbsp;&nbsp;\n".
			  "	       <a href='$crm_basefile?page=crm/crm.php&showreminder=showreminder'>".__('[No]')."</a>\n".
			  "    </p>\n".
			  "</div>\n";
		return false;
	}
}
function wpcrm_reminder_edit_reminder($id) {
	global $wpdb, $crm_basefile;
	$sql = "SELECT * FROM ".$wpdb->prefix."reminder WHERE id='".$wpdb->escape($id)."'";
	$row = $wpdb->get_row($sql);
	if ( isset( $_POST['save'] ) && $_POST['save'] ) {
        $date = date( 'Y-m-d h:i:s', strtotime( $_POST['date'] ) );
		//print_r($date);die;
		$wpdb->query("UPDATE ".$wpdb->prefix."reminder SET
				crmid    = '".$wpdb->escape($_POST['crmid'])."',
				notes     = '".$wpdb->escape($_POST['notes'])."',
				date     = '".$wpdb->escape( $date )."'
			WHERE id ='".$wpdb->escape($_GET['id'])."'");
			
		wpcrm_output_message(__('The Reminder has been updated.'));
		return true;
	} else {
	
		?><div class="wrap">
		<h2 style="margin-bottom:1em"><?php _e('Edit Reminder'); ?></h2>
		<form action="<?php echo $crm_basefile; ?>?page=crm/crm.php&showreminder=showreminder&action=reminder_edit&id=<?php echo $row->id; ?>"
			  method="post" class="crm" name="form1">
		<?php echo wpcrm_get_reminder_form($row); ?>
		<p class="submit">
			<a href='<?php echo $crm_basefile; ?>?page=crm/crm.php&showreminder=showreminder'><?php _e('[Cancel]'); ?></a>
			<input type="submit" name="save" value="<?php _e('Save &raquo;'); ?>" />
		</p>
		</form>
		</div><?php
		return false;
	}
}
