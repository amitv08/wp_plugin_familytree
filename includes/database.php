<?php

class FamilyTreeDatabase
{

    public static function setup_tables()
    {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Clan tables (keep existing)
        $clan_table = $wpdb->prefix . 'family_clans';
        $clan_sql = "CREATE TABLE $clan_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        clan_name varchar(255) NOT NULL,
        clan_description text,
        created_by mediumint(9) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

        // Clan places table (keep existing)
        $clan_places_table = $wpdb->prefix . 'family_clan_places';
        $clan_places_sql = "CREATE TABLE $clan_places_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        clan_id mediumint(9) NOT NULL,
        place_name varchar(255) NOT NULL,
        place_description text,
        location varchar(255),
        created_by mediumint(9) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (clan_id) REFERENCES $clan_table(id) ON DELETE CASCADE
    ) $charset_collate;";

        // Clan names table (keep existing)
        $clan_names_table = $wpdb->prefix . 'family_clan_names';
        $clan_names_sql = "CREATE TABLE $clan_names_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        clan_id mediumint(9) NOT NULL,
        last_name varchar(100) NOT NULL,
        description text,
        created_by mediumint(9) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (clan_id) REFERENCES $clan_table(id) ON DELETE CASCADE
    ) $charset_collate;";

        // Updated family members table - REMOVED user_id
        $members_table = $wpdb->prefix . 'family_members';
        $members_sql = "CREATE TABLE $members_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        first_name varchar(100) NOT NULL,
        last_name varchar(100) NOT NULL,
        birth_date date,
        death_date date,
        gender varchar(20) NOT NULL,
        photo_url varchar(255),
        biography text,
        parent1_id mediumint(9),
        parent2_id mediumint(9),
        clan_id mediumint(9),
        created_by mediumint(9) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($clan_sql);
        dbDelta($clan_places_sql);
        dbDelta($clan_names_sql);
        dbDelta($members_sql);
    }

    // Clan methods
    public static function add_clan($data)
    {
        global $wpdb;

        $result = $wpdb->insert(
            $wpdb->prefix . 'family_clans',
            array_merge($data, array(
                'created_by' => get_current_user_id()
            )),
            array('%s', '%s', '%d')
        );

        return $result ? $wpdb->insert_id : false;
    }

    public static function get_clans()
    {
        global $wpdb;

        return $wpdb->get_results("
            SELECT c.*, 
                   COUNT(DISTINCT p.id) as places_count,
                   COUNT(DISTINCT n.id) as names_count,
                   COUNT(DISTINCT m.id) as members_count
            FROM {$wpdb->prefix}family_clans c
            LEFT JOIN {$wpdb->prefix}family_clan_places p ON c.id = p.clan_id
            LEFT JOIN {$wpdb->prefix}family_clan_names n ON c.id = n.clan_id
            LEFT JOIN {$wpdb->prefix}family_members m ON c.id = m.clan_id
            GROUP BY c.id
            ORDER BY c.clan_name
        ");
    }

    public static function get_clan($id)
    {
        global $wpdb;

        return $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}family_clans WHERE id = %d
        ", $id));
    }

    public static function update_clan($id, $data)
    {
        global $wpdb;

        return $wpdb->update(
            $wpdb->prefix . 'family_clans',
            $data,
            array('id' => $id),
            array('%s', '%s'),
            array('%d')
        );
    }

    public static function delete_clan($id)
    {
        global $wpdb;

        return $wpdb->delete(
            $wpdb->prefix . 'family_clans',
            array('id' => $id),
            array('%d')
        );
    }

    // Clan places methods
    public static function add_clan_place($data)
    {
        global $wpdb;

        $result = $wpdb->insert(
            $wpdb->prefix . 'family_clan_places',
            array_merge($data, array(
                'created_by' => get_current_user_id()
            )),
            array('%d', '%s', '%s', '%s', '%d')
        );

        return $result ? $wpdb->insert_id : false;
    }

    public static function get_clan_places($clan_id)
    {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}family_clan_places 
            WHERE clan_id = %d 
            ORDER BY place_name
        ", $clan_id));
    }

    public static function delete_clan_place($id)
    {
        global $wpdb;

        return $wpdb->delete(
            $wpdb->prefix . 'family_clan_places',
            array('id' => $id),
            array('%d')
        );
    }

    // Clan names methods
    public static function add_clan_name($data)
    {
        global $wpdb;

        $result = $wpdb->insert(
            $wpdb->prefix . 'family_clan_names',
            array_merge($data, array(
                'created_by' => get_current_user_id()
            )),
            array('%d', '%s', '%s', '%d')
        );

        return $result ? $wpdb->insert_id : false;
    }

    public static function get_clan_names($clan_id)
    {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}family_clan_names 
            WHERE clan_id = %d 
            ORDER BY last_name
        ", $clan_id));
    }

    public static function delete_clan_name($id)
    {
        global $wpdb;

        return $wpdb->delete(
            $wpdb->prefix . 'family_clan_names',
            array('id' => $id),
            array('%d')
        );
    }

    public static function get_members_by_clan($clan_id)
    {
        global $wpdb;

        return $wpdb->get_results($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}family_members 
            WHERE clan_id = %d 
            ORDER BY last_name, first_name
        ", $clan_id));
    }

    // Update existing member methods to include clan_id
    public static function add_member($data)
    {
        global $wpdb;

        $result = $wpdb->insert(
            $wpdb->prefix . 'family_members',
            array_merge($data, array(
                'created_by' => get_current_user_id()
            )),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%d', '%d') // Removed user_id format
        );

        return $result ? $wpdb->insert_id : false;
    }

    public static function get_members()
    {
        global $wpdb;

        return $wpdb->get_results("
            SELECT * FROM {$wpdb->prefix}family_members 
            ORDER BY last_name, first_name
        ");
    }

    public static function get_member($id)
    {
        global $wpdb;

        return $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}family_members WHERE id = %d
        ", $id));
    }

    public static function get_tree_data()
    {
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
        biography,
        parent1_id as parent1,
        parent2_id as parent2
    FROM {$wpdb->prefix}family_members 
    ORDER BY last_name, first_name
    ");

        // Debug: Log what we're returning
        error_log('Family Tree Data: ' . print_r($members, true));

        return $members;
    }
    /*
        public static function update_member($id, $data)
        {
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
    */

    public static function update_member($id, $data)
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'family_members';

        // Log the incoming data
        error_log('Database update called for member ID: ' . $id);
        error_log('Update data: ' . print_r($data, true));

        // Convert empty strings to null for parent IDs and dates
        if (isset($data['parent1_id']) && ($data['parent1_id'] === '' || $data['parent1_id'] === '0')) {
            $data['parent1_id'] = null;
        }
        if (isset($data['parent2_id']) && ($data['parent2_id'] === '' || $data['parent2_id'] === '0')) {
            $data['parent2_id'] = null;
        }
        if (isset($data['birth_date']) && $data['birth_date'] === '') {
            $data['birth_date'] = null;
        }
        if (isset($data['death_date']) && $data['death_date'] === '') {
            $data['death_date'] = null;
        }

        // Define format for each field
        $format = array();
        foreach ($data as $key => $value) {
            if ($key === 'parent1_id' || $key === 'parent2_id') {
                $format[] = '%d'; // integer
            } elseif ($key === 'birth_date' || $key === 'death_date') {
                $format[] = '%s'; // date string
            } else {
                $format[] = '%s'; // string
            }
        }

        error_log('Formats: ' . print_r($format, true));

        $result = $wpdb->update(
            $table_name,
            $data,
            array('id' => $id),
            $format,
            array('%d') // where format
        );

        error_log('Update result: ' . ($result !== false ? 'Success' : 'Failed'));
        error_log('Last error: ' . $wpdb->last_error);
        error_log('Last query: ' . $wpdb->last_query);

        return $result !== false;
    }
    public static function delete_member($id)
    {
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

    public static function calculate_generations($members)
    {
        if (empty($members)) {
            return 0;
        }

        $member_map = [];
        foreach ($members as $member) {
            $member_map[$member->id] = $member;
        }

        // Find root members (those without parents)
        $root_members = array_filter($members, function ($member) {
            return !$member->parent1_id && !$member->parent2_id;
        });

        if (empty($root_members)) {
            // If no clear roots, use the oldest member by birth date
            $oldest_member = null;
            foreach ($members as $member) {
                if ($member->birth_date) {
                    if (!$oldest_member || $member->birth_date < $oldest_member->birth_date) {
                        $oldest_member = $member;
                    }
                }
            }
            $root_members = $oldest_member ? [$oldest_member] : [$members[0]];
        }

        $max_depth = 1;

        // Calculate depth for each root member
        foreach ($root_members as $root) {
            $depth = self::calculate_member_depth($root->id, $member_map);
            $max_depth = max($max_depth, $depth);
        }

        return $max_depth;
    }

    private static function calculate_member_depth($member_id, $member_map, $visited = [])
    {
        if (in_array($member_id, $visited)) {
            return 1; // Prevent infinite loops
        }

        $visited[] = $member_id;
        $member = $member_map[$member_id] ?? null;

        if (!$member) {
            return 1;
        }

        $max_child_depth = 0;

        // Find all children of this member
        foreach ($member_map as $child) {
            if ($child->parent1_id == $member_id || $child->parent2_id == $member_id) {
                $child_depth = self::calculate_member_depth($child->id, $member_map, $visited);
                $max_child_depth = max($max_child_depth, $child_depth);
            }
        }

        return 1 + $max_child_depth;
    }

    public static function update_tables()
    {
        global $wpdb;

        $table_name = $wpdb->prefix . 'family_members';

        // Check if user_id column exists
        $columns = $wpdb->get_col("DESCRIBE $table_name", 0);

        if (in_array('user_id', $columns)) {
            // Remove the user_id column
            $wpdb->query("ALTER TABLE $table_name DROP COLUMN user_id");
            error_log('Family Tree: Removed user_id column from family_members table');
        }

        // Also ensure clan_id exists (if you added it recently)
        if (!in_array('clan_id', $columns)) {
            $wpdb->query("ALTER TABLE $table_name ADD COLUMN clan_id mediumint(9) AFTER parent2_id");
            error_log('Family Tree: Added clan_id column to family_members table');
        }
    }
}
?>