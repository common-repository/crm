<?php
function wpcrm_notes_main($crmid) {
      global $wpdb, $crm_version, $crm_basefile;
    $show_main = true;
   
    if ($show_main) {
    
		 ?>
                
        <div class="wrap">
        <div style="text-align:center; width:50%; float:left">
	        <p style="font-size:110%"><strong><a href="#new"><?php _e('Add new Note &darr;'); ?></a></strong></p>
	       
        </div>
         <div style="text-align:center; width:50%; float:left">

        	 <p style="font-size:110%"><strong><a href="<?php echo $crm_basefile; ?>?page=crm/crm.php"><?php _e('Back to Contact List'); ?></a></strong></p>
        </div>
		<?php
      
            $sql = "SELECT n.*,c.first_name,c.surname FROM ".$wpdb->prefix."notes as n, ".$wpdb->prefix."crm as c where c.id = '".$crmid."' and c.id = n.crmid ORDER BY date desc";
            $results = $wpdb->get_results($sql);
		
		?>

        <?php if ( ! empty( $results ) ): ?>
        <h2 style="margin-top:0"><?php echo $results[0]->first_name." ".$results[0]->surname; ?></h2>
        <?php endif; ?>

        <script type="text/javascript">
        /* <![CDATA[ */
       
		 function click_notes(row, id) {
            document.getElementById('notes-info').innerHTML=document.getElementById('notes-'+id+'-info').innerHTML;
        }
		/* ]]> */
        </script>
        <table style="width:100%; margin:auto" id="crm-table">
            
            
            <tr style="background-color:#E5F3FF">
                <?php echo '<th>'.__('Note').'</th><th>'.__('Keyword').'</th><th>'.__('Date').'</th><th>'.__('Edit/Delete').'</th>'; ?>
            </tr>
            <?php
			//print_r($results);
            foreach ($results as $row) {
                echo"<tr>
                    <td width='55%'>".stripslashes($row->notes)."</td>
                    <td width='15%'>".stripslashes($row->keyword)."</td>
                    <td width='20%'>".stripslashes($row->date)."</td>
					<td width='10%'><a href='$crm_basefile?page=crm/crm.php&action=notes_edit&id=".$row->id."'>".__('[Edit]')."</a>&nbsp;&nbsp;<a href='$crm_basefile?page=crm/crm.php&action=notes_delete&id=".$row->id."'>".__('[Delete]')."</a></td>
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
      
        
        <h2 style="margin-bottom:1em"><a name="new"></a>Add Notes</h2>
        <form class="crm" action="<?php echo $crm_basefile; ?>?page=crm/crm.php&shownote=shownote&crmid=<?php echo $crmid; ?>" method="post" name="form1">
        <?php echo wpcrm_get_notes_form(); ?> 
        <p class="submit">
        	
           
            <input type="submit" name="new_note" value="<?php _e('Add Note &raquo;'); ?>" />
        </p>
        </form>
        </div><?php
    }
}
function wpcrm_get_notes_form($data='null') {
    $keyword = isset( $data->keyword ) ? $data->keyword : '';

	// Set default values (the website field is the only one with a default value).
    $psel = $vsel = $lsel = $esel = '';
   	if ( $keyword == "Phone Call" )
			$psel = "selected";
   	elseif ( $keyword == "Visit" )
			$vsel = "selected";
	elseif ( $keyword == "Letter" )
			$lsel = "selected";
	elseif ( $keyword == "Email" )
			$esel = "selected";
		
	if ( isset( $data->crmid ) && $data->crmid != "" ) {
		$crmid = $data->crmid;
	} else if ( isset( $_GET['crmid'] ) ) {
		$crmid = $_GET['crmid'];
    } else {
        $crmid = '';
    }

    $out = '
		<div class="line" style="width:97%">
			<div class="input" style="width:100%">
				<label for="notes">'.__('Notes:').'</label>
				<textarea name="notes" rows="3">'.stripslashes( isset( $data->notes ) ? $data->notes : '' ).'</textarea>
			</div>
        </div>
		<div class="line" style="width:97%">
			<div class="input" style="width:15%">
				<label for="notes">'.__('Date:').'</label>
				<input  name="date" value="'.stripslashes( isset( $data->date ) ? $data->date : '' ).'" size="19" readonly="readonly" onClick="if(self.gfPop)gfPop.fPopCalendar(document.form1.date);return false;">
			</div>
        </div>
		<div class="line" style="width:97%">
			<div class="input" style="width:100%">
				<label for="notes">'.__('Keyword:').'</label>
				<bR>
				<select name="keyword">			
				<option '.$psel.' value="Phone Call">Phone Call</option>
				<option '.$vsel.' value="Visit">Visit</option>
				<option '.$lsel.' value="Letter">Letter</option>
				<option '.$esel.' value="Email">Email</option></select>
			</div>
        </div>
		<input type="hidden" name="crmid" value="'.$crmid.'" />
		<!--  PopCalendar(tag name and id must match) Tags should not be enclosed in tags other than the html body tag. -->
<iframe width=188 height=166 name="gToday:datetime:agenda.js:gfPop:plugins_time.js" id="gToday:datetime:agenda.js:gfPop:plugins_time.js" src="' . esc_url( CRM_PLUGIN_URL . '/Calender/DateTime/ipopeng.htm' ) . '" scrolling="no" frameborder="0" style="visibility:visible; z-index:999; position:absolute; top:-500px; left:-500px;">
</iframe>
		';
		
    return $out;
}
function wpcrm_notes_insert_new_from_post() {

	global $wpdb, $crm_basefile;
	$date = split("[\ \:-]",$_POST['date']);
	$sql = "INSERT INTO ".$wpdb->prefix."notes SET
		crmid    = '".$wpdb->escape($_POST['crmid'])."',
		notes     = '".$wpdb->escape($_POST['notes'])."',
		keyword  = '".$wpdb->escape($_POST['keyword'])."',
		date     = '".$wpdb->escape($date[0]."-".$date[1]."-".$date[2]." ".$date[3].":".$date[4])."'";
		
	$wpdb->query($sql);
	$sql = "Update ".$wpdb->prefix."crm SET	rung    = `rung`+1 where id = '".$wpdb->escape($_POST['crmid'])."'";
	$wpdb->query($sql);
	wpcrm_output_message(__('The Note has been added.'));
	//echo "dsdsds";die;
}
function wpcrm_notes_delete_notes($id) {
	global $wpdb, $crm_basefile;
	$sql = "SELECT * FROM ".$wpdb->prefix."notes WHERE id='".$wpdb->escape($id)."'";
	$row = $wpdb->get_row($sql);
	
	if ($_GET['confirm']=='yes') {
		$wpdb->query("DELETE FROM ".$wpdb->prefix."notes WHERE id='".$wpdb->escape($id)."'");
		$sql = "Update ".$wpdb->prefix."crm SET	rung    = `rung`-1 where id = '".$row->crmid."'";
		$wpdb->query($sql);
		wpcrm_output_message(__('The Notes has been deleted.'));
		return true;
	} else {
		echo  "<div class='wrap'>".
			  "    <p style='text-align:center'>".__('Are you sure you want to delete this Notes?')."</p>\n".
			  "    <p style='text-align:center; font-size:1.3em'>\n".
			  "        <a href='$crm_basefile?page=crm/crm.php&shownote=shownote&crmid=".$row->crmid."&action=notes_delete&id=".$row->id."&confirm=yes'>\n".
			  "            <strong>".__('[Yes]')."</strong>\n".
			  "        </a>&nbsp;&nbsp;&nbsp;&nbsp;\n".
			  "	       <a href='$crm_basefile?page=crm/crm.php&shownote=shownote&crmid=".$row->crmid."'>".__('[No]')."</a>\n".
			  "    </p>\n".
			  "</div>\n";
		return false;
	}
}
function wpcrm_notes_edit_notes($id) {
	global $wpdb, $crm_basefile;
	$sql = "SELECT * FROM ".$wpdb->prefix."notes WHERE id='".$wpdb->escape($id)."'";
	$row = $wpdb->get_row($sql);
	if ( $_POST['save'] ) {
		$date =  split("[\ \:-]",$_POST['date']);
		//print_r($date);die;
		$wpdb->query("UPDATE ".$wpdb->prefix."notes SET
				crmid    = '".$wpdb->escape($_POST['crmid'])."',
				notes     = '".$wpdb->escape($_POST['notes'])."',
				keyword  = '".$wpdb->escape($_POST['keyword'])."',
				date     = '".$wpdb->escape($date[0]."-".$date[1]."-".$date[2]." ".$date[3].":".$date[4])."'
			WHERE id ='".$wpdb->escape($_GET['id'])."'");
			
		wpcrm_output_message(__('The Note has been updated.'));
		return true;
	} else {
		if($row->crmid != "")
			$crmid = $row->crmid;
		else
			$crmid = $_GET['crmid'];
	
		?><div class="wrap">
		<h2 style="margin-bottom:1em"><?php _e('Edit Note'); ?></h2>
		<form action="<?php echo $crm_basefile; ?>?page=crm/crm.php&shownote=shownote&crmid=<?php echo $crmid; ?>&action=notes_edit&id=<?php echo $row->id; ?>"
			  method="post" class="crm" name="form1">
		<?php echo wpcrm_get_notes_form($row); ?>
		<p class="submit">
			<a href='<?php echo $crm_basefile; ?>?page=crm/crm.php&shownote=shownote&crmid=<?php echo $crmid; ?>'><?php _e('[Cancel]'); ?></a>
			<input type="submit" name="save" value="<?php _e('Save &raquo;'); ?>" />
		</p>
		</form>
		</div><?php
		return false;
	}
}
