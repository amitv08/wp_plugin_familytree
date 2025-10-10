<?php
if (!is_user_logged_in() || !current_user_can('manage_family')) {
    wp_redirect('/family-login');
    exit;
}

// Get all family users
$family_users = get_users(array(
    'meta_query' => array(
        array(
            'key' => 'wp_capabilities',
            'value' => 'family',
            'compare' => 'LIKE'
        )
    )
));

$current_user_id = get_current_user_id();

// Get clan and member data
$members = FamilyTreeDatabase::get_members();
$member_count = $members ? count($members) : 0;
$clans = FamilyTreeDatabase::get_clans();
$clan_count = $clans ? count($clans) : 0;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Family Tree Admin</title>
    <style>
        /* ... (keep all existing admin panel styles) ... */
        
        /* Add clan specific styles */
        .clan-stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .clan-stat-card {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-left: 4px solid #007cba;
        }

        .clan-stat-number {
            font-size: 1.8em;
            font-weight: bold;
            color: #007cba;
            margin: 5px 0;
        }

        .clan-stat-label {
            font-size: 0.8em;
            color: #666;
        }
    </style>
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

    <div class="family-admin-panel">
        <div class="admin-header">
            <h1>Family Tree Administration</h1>
            <div class="admin-actions">
                <a href="/family-dashboard" class="btn btn-secondary">← Back to Dashboard</a>
                <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-outline">Logout</a>
            </div>
        </div>

        <div class="admin-tabs">
            <button class="tab-button active" data-tab="users">User Management</button>
            <button class="tab-button" data-tab="members">Family Members</button>
            <button class="tab-button" data-tab="clans">Family Clans</button>
            <button class="tab-button" data-tab="settings">Settings</button>
        </div>

        <!-- Users Tab (keep existing) -->
        <div class="tab-content active" id="users-tab">
            <!-- ... existing user management content ... -->
        </div>

        <!-- Members Tab (keep existing) -->
        <div class="tab-content" id="members-tab">
            <!-- ... existing member management content ... -->
        </div>

        <!-- NEW Clans Tab -->
        <div class="tab-content" id="clans-tab">
            <div class="tab-section">
                <h3>Family Clans Overview</h3>
                
                <div class="clan-stats-grid">
                    <div class="clan-stat-card">
                        <div class="clan-stat-number"><?php echo $clan_count; ?></div>
                        <div class="clan-stat-label">Total Clans</div>
                    </div>
                    <div class="clan-stat-card">
                        <div class="clan-stat-number">
                            <?php
                            $active_clans = 0;
                            foreach ($clans as $clan) {
                                if ($clan->members_count > 0) $active_clans++;
                            }
                            echo $active_clans;
                            ?>
                        </div>
                        <div class="clan-stat-label">Active Clans</div>
                    </div>
                    <div class="clan-stat-card">
                        <div class="clan-stat-number">
                            <?php
                            $total_places = 0;
                            foreach ($clans as $clan) {
                                $total_places += $clan->places_count;
                            }
                            echo $total_places;
                            ?>
                        </div>
                        <div class="clan-stat-label">Clan Places</div>
                    </div>
                    <div class="clan-stat-card">
                        <div class="clan-stat-number">
                            <?php
                            $total_names = 0;
                            foreach ($clans as $clan) {
                                $total_names += $clan->names_count;
                            }
                            echo $total_names;
                            ?>
                        </div>
                        <div class="clan-stat-label">Last Names</div>
                    </div>
                </div>

                <div class="admin-actions">
                    <a href="/add-clan" class="btn btn-primary">Add New Clan</a>
                    <a href="/clans" class="btn btn-secondary">Manage Clans</a>
                </div>
            </div>

            <?php if ($clan_count > 0): ?>
            <div class="tab-section">
                <h3>Recent Clans</h3>
                <div style="background: white; border-radius: 8px; padding: 20px;">
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>Clan Name</th>
                                <th>Members</th>
                                <th>Places</th>
                                <th>Last Names</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $display_clans = array_slice($clans, 0, 5);
                            foreach ($display_clans as $clan): 
                            ?>
                                <tr>
                                    <td><strong><?php echo esc_html($clan->clan_name); ?></strong></td>
                                    <td><?php echo $clan->members_count; ?></td>
                                    <td><?php echo $clan->places_count; ?></td>
                                    <td><?php echo $clan->names_count; ?></td>
                                    <td>
                                        <a href="<?php echo home_url("/edit-clan?id={$clan->id}"); ?>" class="btn btn-primary btn-sm">Manage</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php if ($clan_count > 5): ?>
                        <div style="text-align: center; margin-top: 15px;">
                            <a href="/clans" class="btn btn-outline">View All Clans</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php else: ?>
            <div class="tab-section">
                <div style="text-align: center; padding: 40px; color: #666;">
                    <h4>No Clans Created Yet</h4>
                    <p>Start organizing your family tree by creating clans.</p>
                    <a href="/add-clan" class="btn btn-primary">Create First Clan</a>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Settings Tab (keep existing) -->
        <div class="tab-content" id="settings-tab">
            <!-- ... existing settings content ... -->
        </div>
    </div>

    <!-- ... rest of admin panel code (modals, scripts) ... -->
</body>
</html>