<?php
/*
Plugin Name: UPODHA Plugin
Description: Plugin to add metadata to articles and send commands to Home Assistant
Version: 1.0
Author: Tao Zhou
*/

// 添加前端设置界面
add_action('admin_menu', 'upodha_admin_menu');

function upodha_admin_menu() {
    add_options_page(
        'UPODHA Plugin Settings',
        'UPODHA Settings',
        'manage_options',
        'upodha-settings',
        'upodha_settings_page'
    );
}

function upodha_settings_page() {
    ?>
    <div class="wrap">
        <h1>UPODHA Plugin Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('upodha_settings');
            do_settings_sections('upodha-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// 注册设置项
add_action('admin_init', 'upodha_settings_init');

function upodha_settings_init() {
    register_setting('upodha_settings', 'home_assistant_url');

    add_settings_section(
        'upodha_settings_section',
        'UPODHA Plugin Configuration',
        'upodha_settings_section_callback',
        'upodha-settings'
    );

    add_settings_field(
        'home_assistant_url',
        'Home Assistant URL',
        'home_assistant_url_callback',
        'upodha-settings',
        'upodha_settings_section'
    );
}

function upodha_settings_section_callback() {
    echo 'Enter your Home Assistant configuration details below:';
}

function home_assistant_url_callback() {
    $home_assistant_url = get_option('home_assistant_url');
    echo "<input type='text' name='home_assistant_url' value='{$home_assistant_url}' />";
}

// 当文章保存时，发送请求到Home Assistant
add_action('save_post', 'upodha_send_to_home_assistant');

function upodha_send_to_home_assistant($post_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    
    $metadata = get_post_meta($post_id, 'upodha_metadata', true);
    
    if ($metadata) {
        $home_assistant_url = get_option('home_assistant_url');
        
        $response = wp_remote_post($home_assistant_url, [
            'body' => json_encode(['metadata' => $metadata]),
            'headers' => ['Content-Type' => 'application/json']
        ]);
        
        // 可以在此处处理响应，例如记录错误等
    }
}
