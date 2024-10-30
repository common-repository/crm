<?php
include_once('../../../wp-config.php');
$link = mysql_connect(DB_HOST,DB_USER,DB_PASSWORD);
mysql_select_db(DB_NAME,$link);
$currentdate = date('Y-m-d');
$currentdate = $currentdate;
$sql = "SELECT n.notes,n.crmid,n.id,DATE_FORMAT(n.date,'%D %M %Y') as date,DATE_FORMAT(n.date,'%h:%i %p') as time,c.first_name,c.surname FROM wp_reminder as n, wp_crm as c where c.id = n.crmid and DATE_FORMAT(n.date, '%Y-%m-%d')='".$currentdate."'  ORDER BY date desc";
//echo $sql;
$res = mysql_query($sql);

require_once 'class.phpmailer.php';					
$mail = new PHPMailer();
$mail->IsSMTP();
$mail->WordWrap = 50;                                 // set word wrap to 50 characters
$mail->IsHTML(true);         
$mail->From = 'admin@crm-wp.com';
$mail->FromName = 'CRM Reminder';
$mail->AddAddress("alexsmith@bestweboutsourcing.com", "Admin");
$mail->AddReplyTo($email, $firstname." ".$lastname);
$mail->Mailer = "sendmail";
$mail->Subject = 'CRM Reminder List';

ini_set("sendmail_from",'admin@crm-wp.com');


$message = '<p><FONT face=Verdana size=2>Dear Administrator,
	<br /><br/ >Today\'s reminder list<br /><br/ ></font></p>
	<table width="800" border="0" cellpadding="0" cellspacing="1" bgcolor="#999999">
  <tr bgcolor="#FFFFFF" height="30">
    <th width="50"><FONT face=Verdana size=2>Sr.</font></th>
    <th width="150"><FONT face=Verdana size=2>Customer Name</font></th>
    <th width="200"><FONT face=Verdana size=2>Date & Time</font></th>
    <th width="400"><FONT face=Verdana size=2>Purpose</font></th>
  </tr>';
$c = 0;
while($row = mysql_fetch_object($res))
{
	$c++;
	$message .= '
	<tr bgcolor="#FFFFFF" height="30">
    <th ><FONT face=Verdana size=2>'.$c.'</font></th>
    <td ><FONT face=Verdana size=2>&nbsp;&nbsp;'.$row->first_name.' '.$row->surname.'</font></td>
    <td ><FONT face=Verdana size=2>&nbsp;&nbsp;'.$row->date.', '.$row->time.'</font></td>
    <td ><FONT face=Verdana size=2>&nbsp;&nbsp;'.$row->notes.'</font></td>
  </tr>';
	$sql = "update wp_reminder set sent ='1' where id=".$row->id;
	mysql_query($sql);

}
$message .= '</table><br/ >
	<p><FONT face=Verdana size=2>
	Thank you,<br/ >
	With Regards,<br/ >
	CRM Reminder.</font></p>';
	$message."<br>";
	
	if(mysql_num_rows($res)>0)
	{
		$mail->Body = $message;
		$mail->Send();
		ini_restore("sendmail_from");
	}
?>