<?php

function intelli_content_settings_page() {
    ?>
    <div class="wrap">
        <h1>IntelliContent Settings</h1>
        <form method="post" action="options.php">
            <?php settings_fields('intelli_content_settings_group'); ?>
            <?php do_settings_sections('intelli-content-settings'); ?>
            <?php submit_button('Save Settings'); ?>
        </form>
    </div>
    <?php
}


function intelli_content_register_settings() {
    register_setting('intelli_content_settings_group', 'intelli_content_api_key');
    register_setting('intelli_content_settings_group', 'intelli_content_model', array(
        'type' => 'string',
        'default' => 'text-davinci-003', // Default model
        'sanitize_callback' => 'sanitize_text_field'
    ));
    register_setting('intelli_content_settings_group', 'intelli_content_temperature', array(
        'type' => 'number',
        'default' => 0.7, // Default temperature
        'sanitize_callback' => 'floatval'
    ));
    register_setting('intelli_content_settings_group', 'intelli_content_max_tokens', array(
        'type' => 'number',
        'default' => 1000, // Default max tokens
        'sanitize_callback' => 'intval'
    ));
}
add_action('admin_init', 'intelli_content_register_settings');

function intelli_content_settings_page_callback() {
    echo '<p>Enter your OpenAI API key below:</p>';
}

function intelli_content_api_key_callback() {
    $api_key = get_option('intelli_content_api_key');
    echo '<input type="text" name="intelli_content_api_key" value="' . esc_attr($api_key) . '" />';
    echo '<p class="description">Enter your OpenAI API Key. If you don\'t have an API Key, you can <a href="https://platform.openai.com/" target="_blank">sign up for one</a> on the OpenAI website.</p>';
    
}

function intelli_content_model_callback() {
    $model = get_option('intelli_content_model');
    echo '<input type="text" name="intelli_content_model" value="' . esc_attr($model) . '" />';
    echo '<p class="description">Enter the OpenAI model to use. Default is "text-davinci-003".</p>';
}

function intelli_content_temperature_callback() {
    $temperature = get_option('intelli_content_temperature');
    echo '<input type="number" step="0.1" name="intelli_content_temperature" value="' . esc_attr($temperature) . '" />';
    echo '<p class="description">Enter the temperature for content generation. Default is 0.7.</p>';
}

function intelli_content_max_tokens_callback() {
    $max_tokens = get_option('intelli_content_max_tokens');
    echo '<input type="number" name="intelli_content_max_tokens" value="' . esc_attr($max_tokens) . '" />';
    echo '<p class="description">Enter the maximum number of tokens for content generation. Default is 1000.</p>';
}

function intelli_content_settings_fields() {
    add_settings_section(
        'intelli_content_settings_section',
        'OpenAI Settings',
        'intelli_content_settings_page_callback',
        'intelli-content-settings'
    );
    add_settings_field(
        'intelli_content_api_key',
        'OpenAI API Key',
        'intelli_content_api_key_callback',
        'intelli-content-settings',
        'intelli_content_settings_section'
    );
    add_settings_field(
        'intelli_content_model',
        'OpenAI Model',
        'intelli_content_model_callback',
        'intelli-content-settings',
        'intelli_content_settings_section'
    );
    add_settings_field(
        'intelli_content_temperature',
        'Temperature',
        'intelli_content_temperature_callback',
        'intelli-content-settings',
        'intelli_content_settings_section'
    );
    add_settings_field(
        'intelli_content_max_tokens',
        'Max Tokens',
        'intelli_content_max_tokens_callback',
        'intelli-content-settings',
        'intelli_content_settings_section'
    );
}
add_action('admin_init', 'intelli_content_settings_fields');