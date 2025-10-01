<?php
if (!is_user_logged_in() || !current_user_can('manage_family')) {
    wp_redirect('/family-login');
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Family Tree Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f0f0f1; padding: 20px; }
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
        <button class="tab-button" data-tab="settings">Settings</button>
    </div>

    <div class="tab-content active" id="users-tab">
        <div class="tab-section">
            <h3>Create New User</h3>
            <form id="create-user-form" class="user-form">
                <div class="form-row">
                    <div class="form-group">
                        <label for="username">Username *</label>
                        <input type="text" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="email">Email *</label>
                        <input type="email" id="email" name="email" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">First Name</label>
                        <input type="text" id="first_name" name="first_name">
                    </div>
                    <div class="form-group">
                        <label for="last_name">Last Name</label>
                        <input type="text" id="last_name" name="last_name">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="role">User Role *</label>
                    <select id="role" name="role" required>
                        <option value="">Select Role</option>
                        <option value="family_admin">Family Admin</option>
                        <option value="family_editor">Family Editor</option>
                        <option value="family_viewer">Family Viewer</option>
                    </select>
                </div>

                // In the create user form section, add password field:
<div class="form-row">
    <div class="form-group">
        <label for="password">Password *</label>
        <input type="password" id="password" name="password" required minlength="6">
        <small>Minimum 6 characters</small>
    </div>
    <div class="form-group">
        <label for="confirm_password">Confirm Password *</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
    </div>
</div>
                
                <button type="submit" class="btn btn-primary">Create User</button>
            </form>
        </div>

        <div class="tab-section">
            <h3>Existing Users</h3>
            <div class="users-list">
                <?php
                $users = get_users(array(
                    'meta_query' => array(
                        array(
                            'key' => 'wp_capabilities',
                            'value' => 'family',
                            'compare' => 'LIKE'
                        )
                    )
                ));
                
                if ($users):
                ?>
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): 
                            $roles = $user->roles;
                            $primary_role = !empty($roles) ? $roles[0] : 'No role';
                        ?>
                        <tr>
                            <td><?php echo esc_html($user->user_login); ?></td>
                            <td><?php echo esc_html($user->display_name); ?></td>
                            <td><?php echo esc_html($user->user_email); ?></td>
                            <td><span class="role-badge"><?php echo esc_html($primary_role); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p>No family users found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="tab-content" id="members-tab">
        <div class="tab-section">
            <h3>Family Members Overview</h3>
            <?php
            $members = FamilyTreeDatabase::get_members();
            $member_count = $members ? count($members) : 0;
            ?>
            <div class="stats-grid">
                <div class="stat-card">
                    <h4>Total Members</h4>
                    <div class="stat-number"><?php echo $member_count; ?></div>
                </div>
                <div class="stat-card">
                    <h4>With Parents</h4>
                    <div class="stat-number">
                        <?php
                        $with_parents = 0;
                        if ($members) {
                            foreach ($members as $member) {
                                if ($member->parent1_id || $member->parent2_id) {
                                    $with_parents++;
                                }
                            }
                        }
                        echo $with_parents;
                        ?>
                    </div>
                </div>
            </div>
            
            <div class="admin-actions">
                <a href="/add-member" class="btn btn-primary">Add New Member</a>
                <a href="/family-tree" class="btn btn-secondary">View Tree</a>
            </div>
        </div>
    </div>

    <div class="tab-content" id="settings-tab">
        <div class="tab-section">
            <h3>Family Tree Settings</h3>
            <div class="settings-info">
                <p><strong>User Registration:</strong> <?php echo get_option('users_can_register') ? 'Enabled' : 'Disabled'; ?></p>
                <p><em>To enable user registration, go to Settings → General in WordPress admin.</em></p>
            </div>
        </div>
    </div>
</div>

<div id="admin-messages"></div>

<script>
jQuery(document).ready(function($) {
    // Tab functionality
    $('.tab-button').on('click', function() {
        var tabId = $(this).data('tab');
        
        $('.tab-button').removeClass('active');
        $(this).addClass('active');
        
        $('.tab-content').removeClass('active');
        $('#' + tabId + '-tab').addClass('active');
    });
    
    // Create user form
    $('#create-user-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serializeArray();
        var data = {};
        
        formData.forEach(function(field) {
            data[field.name] = field.value;
        });
        
        data.action = 'create_family_user';
        data.nonce = family_tree.nonce;
        
        $.post(family_tree.ajax_url, data, function(response) {
            if (response.success) {
                $('#admin-messages').html('<div class="message success">' + response.data + '</div>');
                $('#create-user-form')[0].reset();
                setTimeout(function() {
                    location.reload();
                }, 2000);
            } else {
                $('#admin-messages').html('<div class="message error">' + response.data + '</div>');
            }
        });
    });
});
</script>

<?php wp_footer(); ?>
</body>
</html>