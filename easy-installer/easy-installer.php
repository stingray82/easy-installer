<?php
/*
Plugin Name: Easy Installer
Plugin URI: https://github.com/stingray82/easy-installer
Description: A simple plugin to download and activate WordPress plugins from a URL, including handling redirects.
Version: 1.2
Author: Stingray82 & Reallyusefulplugins
Author URI: https://Reallyusefulplugins.com
License: GPLv2 or later
Text Domain: easy-installer
Domain Path: /languages
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class EasyInstaller {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_page']);
        add_action('admin_post_ezi_install_plugin', [$this, 'handle_plugin_installation']);
        load_plugin_textdomain('easy-installer', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }

    public function add_admin_page() {
        add_menu_page(
            esc_html__('Easy Installer', 'easy-installer'),
            esc_html__('Installer', 'easy-installer'),
            'manage_options',
            'easy-installer',
            [$this, 'render_admin_page'],
            'dashicons-admin-plugins'
        );
    }

    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Install a Plugin by URL', 'easy-installer'); ?></h1>
            <form method="POST" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('ezi_install_plugin', 'ezi_nonce'); ?>
                <input type="hidden" name="action" value="ezi_install_plugin">
                <table class="form-table">
                    <tr>
                        <th><label for="plugin_url"><?php esc_html_e('Plugin URL', 'easy-installer'); ?></label></th>
                        <td>
                            <input type="url" id="plugin_url" name="plugin_url" class="regular-text" required>
                            <p class="description"><?php esc_html_e('Enter the URL to the plugin\'s ZIP file (handles redirects).', 'easy-installer'); ?></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(esc_html__('Install Plugin', 'easy-installer')); ?>
            </form>
        </div>
        <?php
    }

    public function handle_plugin_installation() {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to perform this action.', 'easy-installer'));
        }

        if (!check_admin_referer('ezi_install_plugin', 'ezi_nonce')) {
            wp_die(esc_html__('Nonce verification failed.', 'easy-installer'));
        }

        if (empty($_POST['plugin_url'])) {
            wp_die(esc_html__('Plugin URL is required.', 'easy-installer'));
        }

        $plugin_url = esc_url_raw(wp_unslash($_POST['plugin_url']));

        // Follow redirects and download the plugin
        $upload_dir = wp_upload_dir();
        $tmp_file = $upload_dir['basedir'] . '/' . uniqid('plugin_') . '.zip';

        $response = wp_remote_get($plugin_url, ['timeout' => 30, 'redirection' => 10]);
        if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
            wp_die(esc_html__('Failed to download plugin. Please check the URL.', 'easy-installer'));
        }

        WP_Filesystem();
        global $wp_filesystem;
        if (!$wp_filesystem->put_contents($tmp_file, wp_remote_retrieve_body($response))) {
            wp_die(esc_html__('Failed to save the downloaded plugin.', 'easy-installer'));
        }

        // Extract the plugin
        $plugin_dir = WP_PLUGIN_DIR;
        $result = unzip_file($tmp_file, $plugin_dir);

        if (is_wp_error($result)) {
            wp_delete_file($tmp_file);
            wp_die(esc_html__('Failed to extract plugin: ', 'easy-installer') . esc_html($result->get_error_message()));
        }

        wp_delete_file($tmp_file); // Remove the temporary file.

        // Determine the plugin's main file
        $extracted_dirs = scandir($plugin_dir);
        $plugin_main_file = null;

        foreach ($extracted_dirs as $dir) {
            if ($dir === '.' || $dir === '..') {
                continue;
            }
            $possible_plugin_path = $plugin_dir . '/' . $dir;
            if (is_dir($possible_plugin_path)) {
                $plugin_files = glob($possible_plugin_path . '/*.php');
                foreach ($plugin_files as $file) {
                    $plugin_data = get_plugin_data($file);
                    if (!empty($plugin_data['Name'])) {
                        $plugin_main_file = $file;
                        break 2; // Exit both loops when main plugin file is found
                    }
                }
            }
        }

        if (!$plugin_main_file) {
            wp_die(esc_html__('Could not determine the main plugin file.', 'easy-installer'));
        }

        // Activate the plugin
        $relative_path = str_replace($plugin_dir . '/', '', $plugin_main_file);
        $activate = activate_plugin($relative_path);

        if (is_wp_error($activate)) {
            wp_die(esc_html__('Failed to activate plugin: ', 'easy-installer') . esc_html($activate->get_error_message()));
        }

        wp_redirect(esc_url(admin_url('plugins.php?installed=1')));
        exit;
    }
}

new EasyInstaller();
