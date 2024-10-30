<form class="settings-form" action="<?php echo admin_url('options.php') ?>" method="post">
    <?php settings_fields( 'crm-settings' ); ?>

    <?php do_settings_sections( 'crm-general-settings' ); ?>

    <p class="submit">
        <input type="submit" value="<?php _e('Save Changes', 'crm' ) ?>" class="button-primary" id="submit-bottom" name="submit">
    </p>
</form>
