<?php

function wpcrm_send_reminders_cron_job() {
    return new WPCRM_Send_Reminders_Cron_Job( $GLOBALS['wpdb'] );
}

class WPCRM_Send_Reminders_Cron_Job {

    private $db;

    public function __construct( $db ) {
        $this->db = $db;
    }

    public function execute() {
        $reminders = $this->get_reminders_for_today();

        if ( empty( $reminders ) ) {
            return;
        }

        $content = $this->render_reminders_list( $reminders );

        if ( $this->send_reminders_list( $content ) ) {
            $this->mark_reminders_as_sent( $reminders );
        }
    }

    private function get_reminders_for_today() {
        $query = "SELECT n.notes,
                         n.crmid,
                         n.id,
                         DATE_FORMAT(n.date,'%%D %%M %%Y') as date,
                         DATE_FORMAT(n.date,'%%h:%%i %%p') as time,
                         c.first_name,
                         c.surname
                  FROM {$this->db->prefix}reminder as n, wp_crm as c
                  WHERE c.id = n.crmid AND n.sent = '0' AND n.date <= '%s'
                  ORDER BY date DESC";

        return $this->db->get_results( $this->db->prepare( $query, current_time( 'mysql' ) ) );
    }

    private function render_reminders_list( $reminders ) {
        return wpcrm_render_template( CRM_PLUGIN_DIR . '/templates/email/reminders.tpl.php', compact( 'reminders' ) );
    }

    private function send_reminders_list( $content ) {
        $options = get_option( 'crm-settings' );

        if ( isset( $options['reminders-email-recipient-address'] ) && ! empty( $options['reminders-email-recipient-address'] ) ) {
            $recipient_address = $options['reminders-email-recipient-address'];
        } else {
            $recipient_address = get_bloginfo( 'admin_email' );
        }

        $subject = 'CRM Reminder List';

        $headers = array(
            'Content-Type: text/html',
            // 'From: CRM Reminder <admin@crm-wp.com>',
            // "Reply-To: $first_name $last_name <$email>",
        );

        return wp_mail( $recipient_address, $subject, $content, $headers );
    }

    private function mark_reminders_as_sent( $reminders ) {
        $query = "UPDATE {$this->db->prefix}reminder SET sent = '1' WHERE id = %d";

        foreach ( $reminders as $reminder ) {
            $this->db->query( $this->db->prepare( $query, $reminder->id ) );
        }
    }
}
