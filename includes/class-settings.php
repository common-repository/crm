<?php

function wpcrm_settings() {
    return new WPCRM_Settings();
}

class WPCRM_Settings {

    public function register_settings() {
        register_setting( 'crm-settings', 'crm-settings' );

        add_settings_section(
            'reminders-settings',
            __( 'Reminders Settings', 'crm' ),
            false,
            'crm-general-settings'
        );

        add_settings_field(
            'reminders-email-recipient-address',
            __( 'Email recipient address for reminders message', 'crm' ),
            array( $this, 'render_reminders_email_recipient_address_field' ),
            'crm-general-settings',
            'reminders-settings'
        );
    }

    public function render_reminders_email_recipient_address_field() {
        $options = get_option( 'crm-settings' );

        if ( isset( $options['reminders-email-recipient-address'] ) ) {
            $value = $options['reminders-email-recipient-address'];
        } else {
            $value = '';
        }

        $output = '<input id="reminders-email-recipient-address" value="{value}" type="text" name="crm-settings[reminders-email-recipient-address]" />';
        $output = str_replace( '{value}', $value, $output );

        echo $output;
    }
}
