<p>
    <font face=Verdana size=2>
        Dear Administrator,<br />
        <br/ >
        Today's reminder list<br />
        <br/ >
    </font>
</p>

<table width="800" border="0" cellpadding="0" cellspacing="1" bgcolor="#999999">
    <tr bgcolor="#FFFFFF" height="30">
        <th width="150"><font face=Verdana size=2>Customer Name</font></th>
        <th width="200"><font face=Verdana size=2>Date & Time</font></th>
        <th width="400"><font face=Verdana size=2>Purpose</font></th>
    </tr>
    <?php foreach ( $params['reminders'] as $i => $reminder ): ?>
    <tr bgcolor="#FFFFFF" height="30">
        <td ><font face=Verdana size=2>&nbsp;&nbsp;<?php echo $reminder->first_name; ?> <?php echo $reminder->surname; ?></font></td>
        <td ><font face=Verdana size=2>&nbsp;&nbsp;<?php echo $reminder->date; ?>, <?php echo $reminder->time; ?></font></td>
        <td ><font face=Verdana size=2>&nbsp;&nbsp;<?php echo $reminder->notes; ?></font></td>
    </tr>
    <?php endforeach; ?>
</table>
<br/>
<p>
    <font face=Verdana size=2>
        Thank you,<br/ >
        With Regards,<br/ >
        CRM Reminder.
    </font>
</p>
<br/>
