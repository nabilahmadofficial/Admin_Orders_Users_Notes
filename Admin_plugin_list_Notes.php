
/**
 * Plugin Name: Admin Users and Orders Notes
 * Plugin URI: https://nabilahmad.com	
 * Description: Add a notes column to the admin Users and Orders table
 * Version: 1.0
 * Author: Nabil Ahmad
 * Author URI: https://nabilahmad.com
 */

class Admin_Plugins_Notes {
    /**
     * Admin_Plugins_Notes constructor.
     */
    public function __construct() {
        add_filter('manage_plugins_columns', array($this, 'add_notes_column'));
        add_action('manage_plugins_custom_column', array($this, 'display_notes_column'), 10, 3);
        add_action('admin_footer', array($this, 'add_js_to_plugins_page'));
        add_action('wp_ajax_save_plugin_note', array($this, 'save_plugin_note'));
    }

    /**
     * Add "Notes" column to plugins table.
     *
     * @param $columns
     * @return mixed
     */
    public function add_notes_column($columns) {
        $columns['notes'] = __('Notes', 'codewp');
        return $columns;
    }

    /**
     * Display the notes column.
     *
     * @param $column_name
     * @param $plugin_file
     * @param $plugin_data
     */
    public function display_notes_column($column_name, $plugin_file, $plugin_data) {
        if ($column_name == 'notes') {
            $notes = get_option('plugin_notes_' . $plugin_file, '');
            if (current_user_can('activate_plugins')) {
                echo '<textarea class="plugin_notes" data-plugin="'.$plugin_file.'">' . esc_textarea($notes) . '</textarea>';
            } else {
                echo '<textarea class="plugin_notes" data-plugin="'.$plugin_file.'" readonly>' . esc_textarea($notes) . '</textarea>';
            }
        }
    }

    /**
     * Add JavaScript to plugins page.
     */
    public function add_js_to_plugins_page() {
        if (get_current_screen()->base !== 'plugins') {
            return;
        }
        ?>
        <script type="text/javascript">
            jQuery(function($) {
                $('.plugin_notes').on('blur', function() {
                    var plugin = $(this).data('plugin');
                    var note = $(this).val();
                    $.post(ajaxurl, {
    action: 'save_plugin_note',
    plugin: plugin,
    note: note,
    security: '<?php echo wp_create_nonce('save_plugin_note'); ?>'
});
                });
            });
        </script>
        <?php
    }

    /**
     * Save plugin note.
     */
    public function save_plugin_note() {
        check_ajax_referer('save_plugin_note', 'security');
        $plugin = sanitize_text_field($_POST['plugin']);
        $note = sanitize_textarea_field($_POST['note']);
        update_option('plugin_notes_' . $plugin, $note);
        wp_die();
    }
}

new Admin_Plugins_Notes();