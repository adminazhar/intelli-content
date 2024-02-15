<?php

// Add top-level menu for Intelli Content
function intelli_content_add_menu() {
    add_menu_page(
        'Intelli Content',
        'Intelli Content',
        'manage_options',
        'intelli-content',
        'intelli_content_settings_page',
        'dashicons-admin-generic',
        20
    );

    // Add submenu for Full Content Generation
    add_submenu_page(
        'intelli-content',
        'Full Content Generation',
        'Full Content Generation',
        'manage_options',
        'intelli-content-generation',
        'intelli_content_generation_page'
    );

    // Add submenu for Settings
    add_submenu_page(
        'intelli-content',
        'Settings',
        'Settings',
        'manage_options',
        'intelli-content-settings',
        'intelli_content_settings_page'
    );
}
add_action('admin_menu', 'intelli_content_add_menu');