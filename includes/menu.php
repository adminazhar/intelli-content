<?php

// Add top-level menu for Intelli Content
function intelli_content_add_menu() {
    // Add main menu item for IntelliContent
    add_menu_page(
        'IntelliContent',              // Page title
        'IntelliContent',              // Menu title
        'manage_options',               // Capability required
        'intelli-content-generation',   // Menu slug for Content Generation page
        'intelli_content_generation_page', // Callback function for Content Generation page
        'dashicons-superhero',      // Icon
        20                              // Position
    );

    // Add submenu for Content Generation
    add_submenu_page(
        'intelli-content-generation',  // Parent slug (Content Generation page)
        'Content Generation',          // Page title
        'Content Generation',          // Menu title
        'manage_options',              // Capability required
        'intelli-content-generation',  // Menu slug for Content Generation page
        'intelli_content_generation_page' // Callback function for Content Generation page
    );

    // Add submenu for Settings
    add_submenu_page(
        'intelli-content-generation',  // Parent slug (Content Generation page)
        'Settings',                    // Page title
        'Settings',                    // Menu title
        'manage_options',              // Capability required
        'intelli-content-settings',    // Menu slug for Settings page
        'intelli_content_settings_page' // Callback function for Settings page
    );


}
add_action('admin_menu', 'intelli_content_add_menu');