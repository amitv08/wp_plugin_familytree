<?php
/**
 * Plugin Name: Family Tree
 * Description: Complete family tree management system
 * Version: 1.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) {
    exit;
}

define('FAMILY_TREE_URL', plugin_dir_url(__FILE__));
define('FAMILY_TREE_PATH', plugin_dir_path(__FILE__));

class FamilyTreePlugin {
    
    public function __construct() {
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
        
        // Handle custom routes
        add_action('template_redirect', array($this, 'handle_routes'));
        
        // Set dashboard as home page
        add_action('template_redirect', array($this, 'redirect_home_to_dashboard'));
    }
    
    private function include_files() {
        require_once FAMILY_TREE_PATH . 'includes/database.php';
        require_once FAMILY_TREE_PATH . 'includes/roles.php';
    }
    
    public function activate() {
        FamilyTreeDatabase::setup_tables();
        FamilyTreeRoles::setup_roles();
        flush_rewrite_rules();
        
        // Make admin a super admin
        $admin = get_user_by('email', get_option('admin_email'));
        if ($admin) {
            $admin->add_role('family_super_admin');
        }
    }
    
    public function init() {
        // Simple initialization - no complex rewrite rules
    }
    
    public function redirect_home_to_dashboard() {
        // If accessing the site root, redirect to dashboard
        if (is_front_page() || is_home()) {
            wp_redirect('/family-dashboard');
            exit;
        }
    }
    
    public function handle_routes() {
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
        
        if (strpos($request_uri, '/family-tree') !== false) {
            if (!is_user_logged_in()) {
                wp_redirect('/family-login');
                exit;
            }
            $this->load_template('tree-view.php');
            exit;
        }
        // In the handle_routes method, add:
if (strpos($request_uri, '/edit-member') !== false) {
    if (!is_user_logged_in() || !current_user_can('edit_family_members')) {
        wp_redirect('/family-login');
        exit;
    }
    $this->load_template('edit-member.php');
    exit;
}
    }
    
    private function load_template($template) {
        $template_path = FAMILY_TREE_PATH . 'templates/' . $template;
        if (file_exists($template_path)) {
            include $template_path;
            exit;
        } else {
            wp_die('Template not found: ' . $template);
        }
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style('family-tree-style', FAMILY_TREE_URL . 'assets/css/style.css');
        wp_enqueue_script('family-tree-script', FAMILY_TREE_URL . 'assets/js/script.js', array('jquery'), '1.0', true);
        
        wp_localize_script('family-tree-script', 'family_tree', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('family_tree_nonce')
        ));
    }
    
    public function ajax_add_family_member() {
        check_ajax_referer('family_tree_nonce', 'nonce');
        
        if (!current_user_can('edit_family_members')) {
            wp_send_json_error('Insufficient permissions');
        }
        
        $data = array(
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'birth_date' => sanitize_text_field($_POST['birth_date']),
            'death_date' => sanitize_text_field($_POST['death_date']),
            'gender' => sanitize_text_field($_POST['gender']),
            'photo_url' => esc_url_raw($_POST['photo_url']),
            'biography' => sanitize_textarea_field($_POST['biography']),
            'parent1_id' => intval($_POST['parent1_id']),
            'parent2_id' => intval($_POST['parent2_id'])
        );
        
        $result = FamilyTreeDatabase::add_member($data);
        
        if ($result) {
            wp_send_json_success('Member added successfully');
        } else {
            wp_send_json_error('Failed to add member');
        }
    }
    
    public function ajax_get_tree_data() {
        if (!is_user_logged_in()) {
            wp_send_json_error('Authentication required');
        }
        
        $data = FamilyTreeDatabase::get_tree_data();
        wp_send_json_success($data);
    }

    public function ajax_debug_tree_data() {
    if (!is_user_logged_in()) {
        wp_send_json_error('Authentication required');
    }
    
    $data = FamilyTreeDatabase::get_tree_data();
    
    // Send raw data for debugging
    wp_send_json_success([
        'data' => $data,
        'count' => count($data),
        'sample' => $data[0] ?? 'No data',
        'debug' => 'Tree data debug response'
    ]);
}
    
public function ajax_create_family_user() {
    check_ajax_referer('family_tree_nonce', 'nonce');
    
    if (!current_user_can('manage_family_users')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    // Validate passwords
    if ($_POST['password'] !== $_POST['confirm_password']) {
        wp_send_json_error('Passwords do not match');
    }
    
    if (strlen($_POST['password']) < 6) {
        wp_send_json_error('Password must be at least 6 characters long');
    }
    
    $user_data = array(
        'username' => sanitize_user($_POST['username']),
        'email' => sanitize_email($_POST['email']),
        'password' => $_POST['password'], // Include password
        'first_name' => sanitize_text_field($_POST['first_name']),
        'last_name' => sanitize_text_field($_POST['last_name']),
        'role' => sanitize_text_field($_POST['role'])
    );
    
    $result = FamilyTreeRoles::create_user($user_data);
    
    if ($result['success']) {
        wp_send_json_success($result['message']);
    } else {
        wp_send_json_error($result['message']);
    }
}

// Add these to your FamilyTreePlugin class in family-tree.php

public function ajax_update_family_member() {
    check_ajax_referer('family_tree_nonce', 'nonce');
    
    if (!current_user_can('edit_family_members')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $member_id = intval($_POST['member_id']);
    
    // Validate required fields
    if (empty($_POST['first_name']) || empty($_POST['last_name'])) {
        wp_send_json_error('First name and last name are required');
    }
    
    $data = array(
        'first_name' => sanitize_text_field($_POST['first_name']),
        'last_name' => sanitize_text_field($_POST['last_name']),
        'birth_date' => sanitize_text_field($_POST['birth_date']),
        'death_date' => sanitize_text_field($_POST['death_date']),
        'gender' => sanitize_text_field($_POST['gender']),
        'photo_url' => esc_url_raw($_POST['photo_url']),
        'biography' => sanitize_textarea_field($_POST['biography']),
        'parent1_id' => !empty($_POST['parent1_id']) ? intval($_POST['parent1_id']) : null,
        'parent2_id' => !empty($_POST['parent2_id']) ? intval($_POST['parent2_id']) : null
    );
    
    error_log('Updating member ' . $member_id . ' with data: ' . print_r($data, true));
    
    $result = FamilyTreeDatabase::update_member($member_id, $data);
    
    if ($result) {
        wp_send_json_success('Member updated successfully');
    } else {
        wp_send_json_error('Failed to update member. Please try again.');
    }
}

public function ajax_delete_family_member() {
    check_ajax_referer('family_tree_nonce', 'nonce');
    
    if (!current_user_can('delete_family_members')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $member_id = intval($_POST['member_id']);
    
    error_log('Deleting member: ' . $member_id);
    
    $result = FamilyTreeDatabase::delete_member($member_id);
    
    if ($result) {
        wp_send_json_success('Member deleted successfully');
    } else {
        wp_send_json_error('Failed to delete member. Please try again.');
    }
}
}

new FamilyTreePlugin();
?>