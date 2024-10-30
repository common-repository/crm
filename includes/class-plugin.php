<?php

class WPCRM_Plugin {

    public function __construct() {

    }

    public function load_dependencies() {
        require( CRM_PLUGIN_DIR . '/notes.php' );
        require( CRM_PLUGIN_DIR . '/reminder.php' );
        require( CRM_PLUGIN_DIR . '/exportemail.php' );

        require( CRM_PLUGIN_DIR . '/includes/functions.php' );

        require( CRM_PLUGIN_DIR . '/includes/class-send-reminders-cron-job.php' );
        require( CRM_PLUGIN_DIR . '/includes/class-settings.php' );
    }

    public function setup() {
        add_action( 'init', array( $this, 'init' ) );
        add_action( 'admin_init', array( $this, 'admin_init' ) );
        add_filter( 'cron_schedules', array( $this, 'add_cron_schedules' ) );
    }

    public function init() {
        if ( !wp_next_scheduled( 'crm-send-reminders-cron-job' ) ) {
            wp_schedule_event( time(), 'twicedaily', 'crm-send-reminders-cron-job' );
        }

        if ( defined( 'DOING_CRON' ) && DOING_CRON ) {
            $job = wpcrm_send_reminders_cron_job();
            add_action( 'crm-send-reminders-cron-job', array( $job, 'execute' ) );
        }
    }

    public function admin_init() {
        $settings = wpcrm_settings();
        $settings->register_settings();
    }

    public function add_cron_schedules( $schedules ) {
        $schedules['every-10-minutes'] = array(
            'interval' => 600,
            'display' => __( 'Every 10 Minutes', 'crm' ),
        );

        return $schedules;
    }
}
