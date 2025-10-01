<?php

class FamilyTreeDatabase {
    
    public static function setup_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        $table_name = $wpdb->prefix . 'family_members';
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id mediumint(9),
            first_name varchar(100) NOT NULL,
            last_name varchar(100) NOT NULL,
            birth_date date,
            death_date date,
            gender varchar(20) NOT NULL,
            photo_url varchar(255),
            biography text,
            parent1_id mediumint(9),
            parent2_id mediumint(9),
            created_by mediumint(9) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public static function add_member($data) {
        global $wpdb;
        
        $result = $wpdb->insert(
            $wpdb->prefix . 'family_members',
            array_merge($data, array(
                'created_by' => get_current_user_id()
            )),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d')
        );
        
        return $result ? $wpdb->insert_id : false;
    }
    
    public static function get_members() {
        global $wpdb;
        
        return $wpdb->get_results("
            SELECT * FROM {$wpdb->prefix}family_members 
            ORDER BY last_name, first_name
        ");
    }
    
    public static function get_member($id) {
        global $wpdb;
        
        return $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}family_members WHERE id = %d
        ", $id));
    }
    
   public static function get_tree_data() {
    global $wpdb;
    
    $members = $wpdb->get_results("
        SELECT 
            id,
            first_name as firstName,
            last_name as lastName, 
            birth_date as birthDate,
            death_date as deathDate,
            gender,
            photo_url as photo,
            parent1_id as parent1,
            parent2_id as parent2
        FROM {$wpdb->prefix}family_members 
        ORDER BY last_name, first_name
    ");
    
    // Debug: Log what we're returning
    error_log('Family Tree Data: ' . print_r($members, true));
    
    return $members;
}
public static function update_member($id, $data) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'family_members';
    
    // Convert empty strings to null for parent IDs
    if (isset($data['parent1_id']) && $data['parent1_id'] === '') {
        $data['parent1_id'] = null;
    }
    if (isset($data['parent2_id']) && $data['parent2_id'] === '') {
        $data['parent2_id'] = null;
    }
    
    $result = $wpdb->update(
        $table_name,
        $data,
        array('id' => $id),
        array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d'), // data formats
        array('%d') // where format
    );
    
    error_log('Update result: ' . ($result !== false ? 'Success' : 'Failed'));
    error_log('Last error: ' . $wpdb->last_error);
    
    return $result !== false;
}

public static function delete_member($id) {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'family_members';
    
    // First, remove this member as parent from other members
    $wpdb->update(
        $table_name,
        array('parent1_id' => null),
        array('parent1_id' => $id),
        array('%d'),
        array('%d')
    );
    
    $wpdb->update(
        $table_name,
        array('parent2_id' => null),
        array('parent2_id' => $id),
        array('%d'),
        array('%d')
    );
    
    // Then delete the member
    $result = $wpdb->delete(
        $table_name,
        array('id' => $id),
        array('%d')
    );
    
    return $result !== false;
}
}
?>