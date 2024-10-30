<?php

function wpcrm_exportemail() {
	global $wpdb;
		$content = "No.,Name,Organisation,Category,Sales Ranking,Email address,Phone,Address,Suburb,Postcode,State,Country,Website,Secondary Name,Position,Secondary Email address,Secondary Phone,Secondary Address,Secondary Suburb,Secondary Postcode,Secondary State,Secondary Country,Secondary Website,Notes,Rung";
		
		if(trim($_GET['tab']) != "")
		{
			$sql = "SELECT * FROM ".$wpdb->prefix."crm WHERE
				  category LIKE '%".$wpdb->escape($_GET['tab'])."%'
				  ORDER BY first_name";
		}
       elseif (trim($searcharea) != '') {
			$sql = "SELECT * FROM ".$wpdb->prefix."crm WHERE
				first_name LIKE '%".$wpdb->escape($_GET['q'])."%'
				OR surname LIKE '%".$wpdb->escape($_GET['q'])."%'
				OR category LIKE '%".$wpdb->escape($_GET['q'])."%'
				OR organisation LIKE '%".$wpdb->escape($_GET['q'])."%'
				OR email LIKE '%".$wpdb->escape($_GET['q'])."%'
				OR phone LIKE '%".$wpdb->escape($_GET['q'])."%'
				OR notes LIKE '%".$wpdb->escape($_GET['q'])."%'
				OR rung LIKE '%".$wpdb->escape($_GET['q'])."%'
				ORDER BY first_name";
		} else {
			$sql = "SELECT * FROM ".$wpdb->prefix."crm ORDER BY first_name";
		}
		
		$results = $wpdb->get_results($sql);
			$c = 1;
            foreach ($results as $row) {
			
			
			$content .= "\n".$c.",".str_replace(","," -",$row->first_name." ".$row->surname).",".str_replace(","," -",$row->organisation).",".str_replace(","," -",$row->category).",".str_replace(","," -",$row->rank).",".str_replace(","," -",$row->email).",".str_replace(","," -",$row->phone).",".str_replace(","," -",$row->address_line1)." ".str_replace(","," -",$row->address_line2).",".str_replace(","," -",$row->suburb).",".str_replace(","," -",$row->postcode).",".str_replace(","," -",$row->state).",".str_replace(","," -",$row->country).",".str_replace(","," -",$row->website).",".str_replace(","," -",$row->two_first_name." ".$row->two_surname).",".str_replace(","," -",$row->two_organisation).",".str_replace(","," -",$row->two_email).",".str_replace(","," -",$row->two_phone).",			".str_replace(","," -",$row->two_address_line1)." ".str_replace(","," -",$row->two_address_line2).",".str_replace(","," -",$row->two_suburb).",".str_replace(","," -",$row->two_postcode).",".str_replace(","," -",$row->two_state).",".str_replace(","," -",$row->two_country).",".str_replace(","," -",$row->two_website).",".str_replace(","," -",$row->notes).",".str_replace(","," -",$row->rung);
			
			$c++;
		}
		
			
		$file = WP_PLUGIN_DIR . '/email.csv';
		chmod($file,0777);
		$fp = fopen($file,'w');
		fwrite($fp,$content);
		fclose($fp);
		if (file_exists($file)) {
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename='.basename($file));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			ob_clean();
			flush();
			readfile($file);
			unlink($file);
			exit;
		}
}
