<?php
// Redirect logic based on user status
if (!is_user_logged_in()) {
    wp_redirect('/family-login');
    exit;
}

$current_user = wp_get_current_user();
$members = FamilyTreeDatabase::get_members();
$member_count = $members ? count($members) : 0;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Family Tree Dashboard</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f0f0f1; padding: 20px; }
        
        .dashboard-welcome {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin: 30px 0;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            border-left: 4px solid #007cba;
        }
        
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #007cba;
            margin: 10px 0;
        }
        
        .user-role-info {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<div class="family-dashboard">
    <div class="dashboard-header">
        <h1>Family Tree Dashboard</h1>
        <div class="user-info">
            <span>Welcome, <?php echo esc_html($current_user->display_name); ?></span>
            <a href="<?php echo wp_logout_url(home_url()); ?>" class="btn btn-outline">Logout</a>
        </div>
    </div>

    <div class="dashboard-welcome">
        <h2>Welcome to Your Family Tree</h2>
        <div class="user-role-info">
            <p><strong>Your Role:</strong> 
                <?php 
                $roles = $current_user->roles;
                echo ucfirst(str_replace('family_', '', $roles[0] ?? 'Viewer'));
                ?>
            </p>
            <p>You can <?php 
                if (current_user_can('edit_family_members')) echo 'add, edit, and view';
                elseif (current_user_can('manage_family')) echo 'manage users and';
                echo ' view';
            ?> family members.</p>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Family Members</h3>
                <div class="stat-number"><?php echo $member_count; ?></div>
                <p>People in your family tree</p>
            </div>
            <div class="stat-card">
                <h3>Your Access</h3>
                <div class="stat-number">
                    <?php 
                    if (current_user_can('manage_family')) echo 'Full';
                    elseif (current_user_can('edit_family_members')) echo 'Edit';
                    else echo 'View';
                    ?>
                </div>
                <p>Permission level</p>
            </div>
        </div>
    </div>

    <div class="dashboard-actions">
        <h2>Quick Actions</h2>
        <div class="action-buttons">
            <a href="/family-tree" class="btn btn-primary">View Family Tree</a>
            <?php if (current_user_can('edit_family_members')): ?>
                <a href="/add-member" class="btn btn-secondary">Add Family Member</a>
            <?php endif; ?>
            <?php if (current_user_can('manage_family')): ?>
                <a href="/family-admin" class="btn btn-info">Admin Panel</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($member_count > 0): ?>
    <div class="recent-members">
        <h2>Recent Family Members</h2>
        <div class="members-grid">
            <?php
            // Show only first 6 members
            $recent_members = array_slice($members, 0, 6);
            foreach ($recent_members as $member):
                $parents = array();
                if ($member->parent1_id) {
                    $parent1 = FamilyTreeDatabase::get_member($member->parent1_id);
                    if ($parent1) $parents[] = $parent1->first_name . ' ' . $parent1->last_name;
                }
                if ($member->parent2_id) {
                    $parent2 = FamilyTreeDatabase::get_member($member->parent2_id);
                    if ($parent2) $parents[] = $parent2->first_name . ' ' . $parent2->last_name;
                }
            ?>
            <div class="member-card">
                <div class="member-photo">
                    <?php if ($member->photo_url): ?>
                        <img src="<?php echo esc_url($member->photo_url); ?>" alt="<?php echo esc_attr($member->first_name); ?>">
                    <?php else: ?>
                        <div class="photo-placeholder">
                            <?php echo strtoupper(substr($member->first_name, 0, 1)); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="member-details">
                    <h3><?php echo esc_html($member->first_name . ' ' . $member->last_name); ?></h3>
                    <p><strong>Born:</strong> <?php echo $member->birth_date ? date('M j, Y', strtotime($member->birth_date)) : 'Unknown'; ?></p>
                    <?php if ($member->death_date): ?>
                        <p><strong>Died:</strong> <?php echo date('M j, Y', strtotime($member->death_date)); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($parents)): ?>
                        <p><strong>Parents:</strong> <?php echo implode(' & ', $parents); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php if (count($members) > 6): ?>
            <div style="text-align: center; margin-top: 20px;">
                <a href="/family-tree" class="btn btn-outline">View All Members</a>
            </div>
        <?php endif; ?>
    </div>
    <?php else: ?>
    <div class="no-members-welcome">
        <div class="welcome-message">
            <h3>Start Building Your Family Tree</h3>
            <p>No family members added yet. Begin by adding your first family member to build your family tree.</p>
            <?php if (current_user_can('edit_family_members')): ?>
                <a href="/add-member" class="btn btn-primary">Add First Family Member</a>
            <?php else: ?>
                <p><em>Contact an administrator to add family members.</em></p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php wp_footer(); ?>
</body>
</html>