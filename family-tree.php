<?php
/**
 * Plugin Name: Family Tree
 * Description: Complete family tree management system
 * Version: 10102025
 * Author: Amit Vengsarkar
 */

if (!defined('ABSPATH')) {
    exit;
}

define('FAMILY_TREE_URL', plugin_dir_url(__FILE__));
define('FAMILY_TREE_PATH', plugin_dir_path(__FILE__));

class FamilyTreePlugin
{
    public function __construct()
    {
        register_activation_hook(__FILE__, array($this, 'activate'));
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));

        // Include files
        $this->include_files();

        // AJAX handlers
        add_action('wp_ajax_add_family_member', array($this, 'ajax_add_family_member'));
        add_action('wp_ajax_get_tree_data', array($this, 'ajax_get_tree_data'));
        add_action('wp_ajax_debug_tree_data', array($this, 'ajax_debug_tree_data'));
        add_action('wp_ajax_create_family_user', array($this, 'ajax_create_family_user'));
        add_action('wp_ajax_update_family_member', array($this, 'ajax_update_family_member'));
        add_action('wp_ajax_delete_family_member', array($this, 'ajax_delete_family_member'));
        add_action('wp_ajax_update_user_role', array($this, 'ajax_update_user_role'));
        add_action('wp_ajax_delete_family_user', array($this, 'ajax_delete_family_user'));

        // Clan AJAX handlers
        add_action('wp_ajax_add_clan', array($this, 'ajax_add_clan'));
        add_action('wp_ajax_update_clan', array($this, 'ajax_update_clan'));
        add_action('wp_ajax_delete_clan', array($this, 'ajax_delete_clan'));
        add_action('wp_ajax_add_clan_place', array($this, 'ajax_add_clan_place'));
        add_action('wp_ajax_delete_clan_place', array($this, 'ajax_delete_clan_place'));
        add_action('wp_ajax_add_clan_name', array($this, 'ajax_add_clan_name'));
        add_action('wp_ajax_delete_clan_name', array($this, 'ajax_delete_clan_name'));

        // Handle custom routes
        add_action('template_redirect', array($this, 'handle_routes'));

        // Set dashboard as home page
        add_action('template_redirect', array($this, 'redirect_home_to_dashboard'));
    }

    private function include_files()
    {
        require_once FAMILY_TREE_PATH . 'includes/database.php';
        require_once FAMILY_TREE_PATH . 'includes/roles.php';
    }

    public function activate()
    {
        // Suppress output during activation
        ob_start();

        try {
            FamilyTreeDatabase::setup_tables();
            FamilyTreeRoles::setup_roles();
            flush_rewrite_rules();

            // Make admin a super admin
            $admin = get_user_by('email', get_option('admin_email'));
            if ($admin) {
                $admin->add_role('family_super_admin');
            }

            // Clear any output buffer
            ob_end_clean();

        } catch (Exception $e) {
            // Clean output buffer on error too
            ob_end_clean();
            error_log('Family Tree Plugin activation error: ' . $e->getMessage());
        }
    }

    public function init()
    {
        // Simple initialization - no complex rewrite rules
    }

    public function redirect_home_to_dashboard()
    {
        // If accessing the site root, redirect to dashboard
        if (is_front_page() || is_home()) {
            wp_redirect('/family-dashboard');
            exit;
        }
    }

    public function handle_routes()
    {
        $request_uri = $_SERVER['REQUEST_URI'];

        // Handle our custom routes
        if (strpos($request_uri, '/family-dashboard') !== false) {
            $this->load_template('dashboard.php');
            exit;
        }

        if (strpos($request_uri, '/family-login') !== false) {
            $this->load_template('login.php');
            exit;
        }

        if (strpos($request_uri, '/family-admin') !== false) {
            if (!is_user_logged_in() || !current_user_can('manage_family')) {
                wp_redirect('/family-login');
                exit;
            }
            $this->load_template('admin-panel.php');
            exit;
        }

        if (strpos($request_uri, '/add-member') !== false) {
            if (!is_user_logged_in() || !current_user_can('edit_family_members')) {
                wp_redirect('/family-login');
                exit;
            }
            $this->load_template('add-member.php');
            exit;
        }

        if (strpos($request_uri, '/browse-members') !== false) {
            if (!is_user_logged_in()) {
                wp_redirect('/family-login');
                exit;
            }
            $this->load_template('browse-members.php');
            exit;
        }

        if (strpos($request_uri, '/family-tree') !== false) {
            if (!is_user_logged_in()) {
                wp_redirect('/family-login');
                exit;
            }
            $this->load_template('tree-view.php');
            exit;
        }

        if (strpos($request_uri, '/edit-member') !== false) {
            if (!is_user_logged_in() || !current_user_can('edit_family_members')) {
                wp_redirect('/family-login');
                exit;
            }
            $this->load_template('edit-member.php');
            exit;
        }

        // Clan routes

        if (strpos($request_uri, '/clans') !== false) {
            if (!is_user_logged_in()) {
                wp_redirect('/family-login');
                exit;
            }
            $this->load_template('clans.php');
            exit;
        }

        if (strpos($request_uri, '/add-clan') !== false) {
            if (!is_user_logged_in() || !current_user_can('edit_family_members')) {
                wp_redirect('/family-login');
                exit;
            }
            $this->load_template('add-clan.php');
            exit;
        }

        if (strpos($request_uri, '/edit-clan') !== false) {
            if (!is_user_logged_in() || !current_user_can('edit_family_members')) {
                wp_redirect('/family-login');
                exit;
            }
            $this->load_template('edit-clan.php');
            exit;
        }
    }

    private function load_template($template)
    {
        $template_path = FAMILY_TREE_PATH . 'templates/' . $template;
        if (file_exists($template_path)) {
            include $template_path;
            exit;
        } else {
            wp_die('Template not found: ' . $template);
        }
    }

    public function enqueue_scripts()
    {
        wp_enqueue_style('family-tree-style', FAMILY_TREE_URL . 'assets/css/style.css');
        wp_enqueue_script('family-tree-script', FAMILY_TREE_URL . 'assets/js/script.js', array('jquery'), '1.0', true);

        wp_localize_script('family-tree-script', 'family_tree', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('family_tree_nonce')
        ));
    }

    // Existing AJAX methods (keep all your existing methods here)
    public function ajax_add_family_member()
    {
        // Clear any previous output
        ob_clean();

        // Check nonce first
        if (!check_ajax_referer('family_tree_nonce', 'nonce', false)) {
            status_header(400);
            wp_send_json_error('Security verification failed');
            exit;
        }

        if (!current_user_can('edit_family_members')) {
            status_header(403);
            wp_send_json_error('Insufficient permissions');
            exit;
        }

        try {
            // Handle file upload
            $photo_url = '';
            if (!empty($_FILES['photo']['name'])) {
                $upload = wp_handle_upload($_FILES['photo'], array('test_form' => false));
                if (isset($upload['url']) && !isset($upload['error'])) {
                    $photo_url = $upload['url'];
                } else {
                    wp_send_json_error('File upload failed: ' . ($upload['error'] ?? 'Unknown error'));
                    exit;
                }
            }

            // Validate required fields
            if (empty($_POST['first_name']) || empty($_POST['last_name'])) {
                wp_send_json_error('First name and last name are required');
                exit;
            }

            $data = array(
                'first_name' => sanitize_text_field($_POST['first_name']),
                'last_name' => sanitize_text_field($_POST['last_name']),
                'birth_date' => !empty($_POST['birth_date']) ? sanitize_text_field($_POST['birth_date']) : null,
                'death_date' => !empty($_POST['death_date']) ? sanitize_text_field($_POST['death_date']) : null,
                'gender' => !empty($_POST['gender']) ? sanitize_text_field($_POST['gender']) : 'other',
                'photo_url' => $photo_url,
                'biography' => !empty($_POST['biography']) ? sanitize_textarea_field($_POST['biography']) : '',
                'parent1_id' => !empty($_POST['parent_id']) ? intval($_POST['parent_id']) : null
            );

            $result = FamilyTreeDatabase::add_member($data);

            if ($result) {
                wp_send_json_success('Member added successfully');
            } else {
                wp_send_json_error('Failed to add member to database');
            }

        } catch (Exception $e) {
            status_header(500);
            wp_send_json_error('Server error: ' . $e->getMessage());
        }

        exit; // Ensure no further output
    }

    public function ajax_get_tree_data()
    {
        // ... your existing code ...
    }

    // ... all your other existing AJAX methods ...

    // ===== CLAN AJAX METHODS =====
/*
    public function ajax_add_clan()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');

        if (!current_user_can('edit_family_members')) {
            wp_send_json_error('Insufficient permissions');
        }

        $data = array(
            'clan_name' => sanitize_text_field($_POST['clan_name']),
            'clan_description' => sanitize_textarea_field($_POST['clan_description'])
        );

        $result = FamilyTreeDatabase::add_clan($data);

        if ($result) {
            wp_send_json_success('Clan added successfully');
        } else {
            wp_send_json_error('Failed to add clan');
        }
    }
*/

    public function ajax_add_clan()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');

        if (!current_user_can('edit_family_members')) {
            wp_send_json_error('Insufficient permissions');
        }

        $clan_name = sanitize_text_field($_POST['clan_name']);
        $clan_description = sanitize_textarea_field($_POST['clan_description']);

        if (empty($clan_name)) {
            wp_send_json_error('Clan name is required');
        }

        // First, create the clan
        $clan_data = array(
            'clan_name' => $clan_name,
            'clan_description' => $clan_description
        );

        $clan_id = FamilyTreeDatabase::add_clan($clan_data);

        if (!$clan_id) {
            wp_send_json_error('Failed to create clan');
        }

        $success_count = 0;
        $error_count = 0;

        // Add places if provided
        if (!empty($_POST['places'])) {
            $places = json_decode(stripslashes($_POST['places']), true);

            if (is_array($places)) {
                foreach ($places as $place) {
                    $place_data = array(
                        'clan_id' => $clan_id,
                        'place_name' => sanitize_text_field($place['place_name']),
                        'location' => sanitize_text_field($place['location']),
                        'place_description' => sanitize_textarea_field($place['place_description'])
                    );

                    if (FamilyTreeDatabase::add_clan_place($place_data)) {
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                }
            }
        }

        // Add names if provided
        if (!empty($_POST['names'])) {
            $names = json_decode(stripslashes($_POST['names']), true);

            if (is_array($names)) {
                foreach ($names as $name) {
                    $name_data = array(
                        'clan_id' => $clan_id,
                        'last_name' => sanitize_text_field($name['last_name']),
                        'description' => sanitize_textarea_field($name['description'])
                    );

                    if (FamilyTreeDatabase::add_clan_name($name_data)) {
                        $success_count++;
                    } else {
                        $error_count++;
                    }
                }
            }
        }

        $message = 'Clan created successfully';
        if ($success_count > 0) {
            $message .= " with {$success_count} additional items";
        }
        if ($error_count > 0) {
            $message .= ". {$error_count} items failed to add";
        }

        wp_send_json_success($message);
    }
    public function ajax_update_clan()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');

        if (!current_user_can('edit_family_members')) {
            wp_send_json_error('Insufficient permissions');
        }

        $clan_id = intval($_POST['clan_id']);
        $data = array(
            'clan_name' => sanitize_text_field($_POST['clan_name']),
            'clan_description' => sanitize_textarea_field($_POST['clan_description'])
        );

        $result = FamilyTreeDatabase::update_clan($clan_id, $data);

        if ($result) {
            wp_send_json_success('Clan updated successfully');
        } else {
            wp_send_json_error('Failed to update clan');
        }
    }

    public function ajax_delete_clan()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');

        if (!current_user_can('delete_family_members')) {
            wp_send_json_error('Insufficient permissions');
        }

        $clan_id = intval($_POST['clan_id']);
        $result = FamilyTreeDatabase::delete_clan($clan_id);

        if ($result) {
            wp_send_json_success('Clan deleted successfully');
        } else {
            wp_send_json_error('Failed to delete clan');
        }
    }

    public function ajax_add_clan_place()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');

        if (!current_user_can('edit_family_members')) {
            wp_send_json_error('Insufficient permissions');
        }

        $data = array(
            'clan_id' => intval($_POST['clan_id']),
            'place_name' => sanitize_text_field($_POST['place_name']),
            'place_description' => sanitize_textarea_field($_POST['place_description']),
            'location' => sanitize_text_field($_POST['location'])
        );

        $result = FamilyTreeDatabase::add_clan_place($data);

        if ($result) {
            wp_send_json_success('Clan place added successfully');
        } else {
            wp_send_json_error('Failed to add clan place');
        }
    }

    public function ajax_delete_clan_place()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');

        if (!current_user_can('delete_family_members')) {
            wp_send_json_error('Insufficient permissions');
        }

        $place_id = intval($_POST['place_id']);
        $result = FamilyTreeDatabase::delete_clan_place($place_id);

        if ($result) {
            wp_send_json_success('Clan place deleted successfully');
        } else {
            wp_send_json_error('Failed to delete clan place');
        }
    }

    public function ajax_add_clan_name()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');

        if (!current_user_can('edit_family_members')) {
            wp_send_json_error('Insufficient permissions');
        }

        $data = array(
            'clan_id' => intval($_POST['clan_id']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'description' => sanitize_textarea_field($_POST['description'])
        );

        $result = FamilyTreeDatabase::add_clan_name($data);

        if ($result) {
            wp_send_json_success('Clan name added successfully');
        } else {
            wp_send_json_error('Failed to add clan name');
        }
    }

    public function ajax_delete_clan_name()
    {
        check_ajax_referer('family_tree_nonce', 'nonce');

        if (!current_user_can('delete_family_members')) {
            wp_send_json_error('Insufficient permissions');
        }

        $name_id = intval($_POST['name_id']);
        $result = FamilyTreeDatabase::delete_clan_name($name_id);

        if ($result) {
            wp_send_json_success('Clan name deleted successfully');
        } else {
            wp_send_json_error('Failed to delete clan name');
        }
    }

    public function ajax_update_family_member()
    {
        // Check nonce first
        if (!check_ajax_referer('family_tree_nonce', 'nonce', false)) {
            error_log('Family Tree: Nonce verification failed');
            wp_send_json_error('Security verification failed');
        }

        if (!current_user_can('edit_family_members')) {
            error_log('Family Tree: User lacks edit_family_members capability');
            wp_send_json_error('Insufficient permissions');
        }

        // Validate required fields
        if (empty($_POST['member_id'])) {
            wp_send_json_error('Member ID is required');
        }

        if (empty($_POST['first_name']) || empty($_POST['last_name'])) {
            wp_send_json_error('First name and last name are required');
        }

        $member_id = intval($_POST['member_id']);

        // Prepare data with proper validation
        $data = array(
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'birth_date' => !empty($_POST['birth_date']) ? sanitize_text_field($_POST['birth_date']) : null,
            'death_date' => !empty($_POST['death_date']) ? sanitize_text_field($_POST['death_date']) : null,
            'gender' => !empty($_POST['gender']) ? sanitize_text_field($_POST['gender']) : 'other',
            'biography' => !empty($_POST['biography']) ? sanitize_textarea_field($_POST['biography']) : '',
            'parent1_id' => !empty($_POST['parent1_id']) ? intval($_POST['parent1_id']) : null,
            'parent2_id' => !empty($_POST['parent2_id']) ? intval($_POST['parent2_id']) : null
        );

        // Handle photo_url separately since it might be empty
        if (!empty($_POST['photo_url'])) {
            $data['photo_url'] = esc_url_raw($_POST['photo_url']);
        } else {
            $data['photo_url'] = '';
        }

        error_log('Updating member ' . $member_id . ' with data: ' . print_r($data, true));

        try {
            $result = FamilyTreeDatabase::update_member($member_id, $data);

            if ($result !== false) {
                error_log('Member update successful for ID: ' . $member_id);
                wp_send_json_success('Member updated successfully');
            } else {
                error_log('Member update failed for ID: ' . $member_id);
                global $wpdb;
                error_log('Last database error: ' . $wpdb->last_error);
                wp_send_json_error('Failed to update member. Database error occurred.');
            }
        } catch (Exception $e) {
            error_log('Exception during member update: ' . $e->getMessage());
            wp_send_json_error('Server error: ' . $e->getMessage());
        }
    }
}

new FamilyTreePlugin();
?>