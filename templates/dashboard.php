<?php
// Redirect logic based on user status
if (!is_user_logged_in()) {
    wp_redirect('/family-login');
    exit;
}

$current_user = wp_get_current_user();
$members = FamilyTreeDatabase::get_members();
$member_count = $members ? count($members) : 0;

// Get clan data
$clans = FamilyTreeDatabase::get_clans();
$clan_count = $clans ? count($clans) : 0;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Family Tree Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f0f0f1;
            padding: 20px;
        }

        .family-dashboard {
            max-width: 1200px;
            margin: 0 auto;
        }

        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }

        .user-info {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .dashboard-welcome {
            background: white;
            padding: 40px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .dashboard-welcome h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 2em;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 40px 0;
        }

        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-card.primary {
            background: linear-gradient(135deg, #007cba 0%, #0056b3 100%);
        }

        .stat-card.secondary {
            background: linear-gradient(135deg, #6c757d 0%, #545b62 100%);
        }

        .stat-card.success {
            background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
        }

        .stat-card.warning {
            background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        }

        .stat-number {
            font-size: 3em;
            font-weight: bold;
            margin: 10px 0;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
        }

        .stat-card h3 {
            margin: 0;
            font-size: 1.2em;
            opacity: 0.9;
        }

        .user-role-info {
            background: #e7f3ff;
            padding: 20px;
            border-radius: 8px;
            margin: 30px 0;
            border-left: 4px solid #007cba;
        }

        .dashboard-actions {
            background: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .action-buttons {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .btn {
            display: inline-block;
            padding: 15px 25px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-size: 16px;
            font-weight: 500;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .btn-primary {
            background: #007cba;
            color: white;
        }

        .btn-primary:hover {
            background: #005a87;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #545b62;
            transform: translateY(-2px);
        }

        .btn-info {
            background: #17a2b8;
            color: white;
        }

        .btn-info:hover {
            background: #138496;
            transform: translateY(-2px);
        }

        .btn-warning {
            background: #ffc107;
            color: #212529;
        }

        .btn-warning:hover {
            background: #e0a800;
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #6c757d;
            color: #6c757d;
        }

        .btn-outline:hover {
            background: #6c757d;
            color: white;
            transform: translateY(-2px);
        }

        .no-members-welcome {
            background: white;
            padding: 50px;
            border-radius: 10px;
            text-align: center;
            margin-top: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .welcome-message h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 1.5em;
        }

        .welcome-message p {
            color: #666;
            margin-bottom: 20px;
            font-size: 1.1em;
            line-height: 1.6;
        }

        .quick-stats {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin: 30px 0;
            flex-wrap: wrap;
        }

        .quick-stat {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            min-width: 150px;
        }

        .quick-stat .number {
            font-size: 2em;
            font-weight: bold;
            color: #007cba;
            display: block;
        }

        .quick-stat .label {
            color: #666;
            font-size: 0.9em;
        }

        /* Clan specific styles */
        .clan-stats {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            justify-content: center;
        }

        .clan-stat {
            background: rgba(255, 255, 255, 0.2);
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8em;
        }

        .recent-clans {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin: 30px 0;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .recent-clans h3 {
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }

        .clans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }

        .clan-card {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .clan-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .clan-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .clan-name {
            font-size: 1.2em;
            font-weight: bold;
            color: #333;
            margin: 0;
        }

        .clan-description {
            color: #666;
            font-size: 0.9em;
            margin-bottom: 15px;
            line-height: 1.4;
        }

        .clan-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.8em;
            color: #888;
        }

        .clan-actions {
            margin-top: 15px;
            display: flex;
            gap: 10px;
        }

        .btn-small {
            padding: 5px 10px;
            font-size: 0.8em;
            text-decoration: none;
            border-radius: 4px;
        }

        @media (max-width: 768px) {
            .dashboard-header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .user-info {
                flex-direction: column;
            }

            .action-buttons {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .quick-stats {
                flex-direction: column;
                align-items: center;
            }

            .clans-grid {
                grid-template-columns: 1fr;
            }
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
            <p>Manage and explore your family history in one place</p>

            <div class="quick-stats">
                <div class="quick-stat">
                    <span class="number"><?php echo $member_count; ?></span>
                    <span class="label">Family Members</span>
                </div>
                <div class="quick-stat">
                    <span class="number"><?php echo $clan_count; ?></span>
                    <span class="label">Family Clans</span>
                </div>
                <div class="quick-stat">
                    <span class="number">
                        <?php
                        if (current_user_can('manage_family'))
                            echo 'Admin';
                        elseif (current_user_can('edit_family_members'))
                            echo 'Editor';
                        else
                            echo 'Viewer';
                        ?>
                    </span>
                    <span class="label">Your Role</span>
                </div>
            </div>

            <div class="user-role-info">
                <p><strong>Your Role:</strong>
                    <?php
                    $roles = $current_user->roles;
                    echo ucfirst(str_replace('family_', '', $roles[0] ?? 'Viewer'));
                    ?>
                </p>
                <p>
                    <?php
                    if (current_user_can('manage_family')) {
                        echo 'You have full access to manage users, edit family members, clans, and view the entire family tree.';
                    } elseif (current_user_can('edit_family_members')) {
                        echo 'You can add, edit, and view family members and clans in the family tree.';
                    } else {
                        echo 'You can view the family tree, family members, and clan details.';
                    }
                    ?>
                </p>
            </div>
        </div>

        <div class="stats-grid">
            <div class="stat-card primary">
                <h3>👨‍👩‍👧‍👦 Family Members</h3>
                <div class="stat-number"><?php echo $member_count; ?></div>
                <p>Total in your tree</p>
                <?php if ($member_count > 0): ?>
                    <div style="margin-top: 10px; font-size: 0.9em; opacity: 0.8;">
                        <?php
                        $genders = ['male' => 0, 'female' => 0, 'other' => 0];
                        $living = 0;
                        foreach ($members as $member) {
                            $genders[$member->gender] = ($genders[$member->gender] ?? 0) + 1;
                            if (!$member->death_date || strtotime($member->death_date) > time()) {
                                $living++;
                            }
                        }
                        echo "♂️ {$genders['male']} • ♀️ {$genders['female']}";
                        if ($genders['other'] > 0)
                            echo " • ⚧️ {$genders['other']}";
                        ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="stat-card secondary">
                <h3>🏛️ Family Clans</h3>
                <div class="stat-number"><?php echo $clan_count; ?></div>
                <p>Total clans</p>
                <?php if ($clan_count > 0): ?>
                    <div class="clan-stats">
                        <?php
                        $active_clans = 0;
                        $total_places = 0;
                        $total_names = 0;
                        
                        foreach ($clans as $clan) {
                            if ($clan->members_count > 0) $active_clans++;
                            $total_places += $clan->places_count;
                            $total_names += $clan->names_count;
                        }
                        ?>
                        <span class="clan-stat">🏃 <?php echo $active_clans; ?> active</span>
                        <span class="clan-stat">📍 <?php echo $total_places; ?> places</span>
                        <span class="clan-stat">📛 <?php echo $total_names; ?> names</span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="stat-card success">
                <h3>🎯 Activity</h3>
                <div class="stat-number" style="font-size: 2em;">
                    <?php
                    if ($member_count === 0) {
                        echo '✨';
                    } elseif ($member_count < 5) {
                        echo '🌱';
                    } elseif ($member_count < 20) {
                        echo '🌳';
                    } else {
                        echo '🏛️';
                    }
                    ?>
                </div>
                <p>
                    <?php
                    if ($member_count === 0) {
                        echo 'Start building';
                    } elseif ($member_count < 5) {
                        echo 'Growing';
                    } elseif ($member_count < 20) {
                        echo 'Established';
                    } else {
                        echo 'Large tree';
                    }
                    ?>
                </p>
                <?php if ($member_count > 0): ?>
                    <div style="margin-top: 10px; font-size: 0.9em; opacity: 0.8;">
                        <?php
                        $recent_count = 0;
                        $one_week_ago = date('Y-m-d H:i:s', strtotime('-1 week'));
                        foreach ($members as $member) {
                            if ($member->created_at >= $one_week_ago) {
                                $recent_count++;
                            }
                        }
                        if ($recent_count > 0) {
                            echo "🆕 {$recent_count} new";
                        } else {
                            echo "📝 Add more";
                        }
                        ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($clan_count > 0): ?>
            <div class="recent-clans">
                <h3>🏛️ Your Family Clans</h3>
                <div class="clans-grid">
                    <?php 
                    // Display up to 6 clans
                    $display_clans = array_slice($clans, 0, 6);
                    foreach ($display_clans as $clan): 
                    ?>
                        <div class="clan-card">
                            <div class="clan-header">
                                <h4 class="clan-name"><?php echo esc_html($clan->clan_name); ?></h4>
                                <span style="font-size: 0.8em; color: #666;">#<?php echo $clan->id; ?></span>
                            </div>
                            
                            <?php if ($clan->clan_description): ?>
                                <div class="clan-description">
                                    <?php echo esc_html(wp_trim_words($clan->clan_description, 15)); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="clan-meta">
                                <span>👥 <?php echo $clan->members_count; ?> members</span>
                                <span>📍 <?php echo $clan->places_count; ?> places</span>
                                <span>📛 <?php echo $clan->names_count; ?> names</span>
                            </div>
                            
                            <div class="clan-actions">
                                <a href="<?php echo home_url("/edit-clan?id={$clan->id}"); ?>" class="btn-small btn-primary">Manage</a>
                                <a href="<?php echo home_url("/browse-members?clan={$clan->id}"); ?>" class="btn-small btn-secondary">View Members</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($clan_count > 6): ?>
                    <div style="text-align: center; margin-top: 20px;">
                        <a href="<?php echo home_url('/clans'); ?>" class="btn btn-outline">View All Clans (<?php echo $clan_count; ?>)</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($member_count > 0): ?>
            <div class="next-steps">
                <h3>🚀 Next Steps for Your Family Tree</h3>
                <div class="steps-grid">
                    <?php
                    // Calculate suggestions
                    $without_parents = 0;
                    $without_birthdates = 0;
                    $without_photos = 0;
                    $without_clans = 0;

                    foreach ($members as $member) {
                        if (!$member->parent1_id && !$member->parent2_id)
                            $without_parents++;
                        if (!$member->birth_date)
                            $without_birthdates++;
                        if (!$member->photo_url)
                            $without_photos++;
                        if (!$member->clan_id)
                            $without_clans++;
                    }
                    ?>

                    <?php if ($without_parents > 0): ?>
                        <div class="step-card">
                            <div class="step-icon">👥</div>
                            <div class="step-content">
                                <h4>Link Family Relationships</h4>
                                <p><?php echo $without_parents; ?> members need parent connections</p>
                                <a href="/family-tree" class="btn btn-small">View Tree</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($without_birthdates > 0): ?>
                        <div class="step-card">
                            <div class="step-icon">📅</div>
                            <div class="step-content">
                                <h4>Add Birth Dates</h4>
                                <p><?php echo $without_birthdates; ?> members missing birth dates</p>
                                <a href="/browse-members" class="btn btn-small">Add Dates</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($without_photos > 0): ?>
                        <div class="step-card">
                            <div class="step-icon">🖼️</div>
                            <div class="step-content">
                                <h4>Upload Photos</h4>
                                <p><?php echo $without_photos; ?> members need photos</p>
                                <a href="/browse-members" class="btn btn-small">Add Photos</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($without_clans > 0 && $clan_count > 0): ?>
                        <div class="step-card">
                            <div class="step-icon">🏛️</div>
                            <div class="step-content">
                                <h4>Assign to Clans</h4>
                                <p><?php echo $without_clans; ?> members not in clans</p>
                                <a href="/browse-members" class="btn btn-small">Assign Clans</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($clan_count === 0 && current_user_can('edit_family_members')): ?>
                        <div class="step-card">
                            <div class="step-icon">🏛️</div>
                            <div class="step-content">
                                <h4>Create Family Clans</h4>
                                <p>Organize your family into clans for better management</p>
                                <a href="/add-clan" class="btn btn-small">Create Clan</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if ($without_parents === 0 && $without_birthdates === 0 && $without_photos === 0 && $without_clans === 0): ?>
                        <div class="step-card">
                            <div class="step-icon">⭐</div>
                            <div class="step-content">
                                <h4>Tree Complete!</h4>
                                <p>Your family tree is well documented. Consider adding more historical records.</p>
                                <a href="/add-member" class="btn btn-small">Add More</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <style>
                .next-steps {
                    background: white;
                    padding: 30px;
                    border-radius: 10px;
                    margin: 30px 0;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                }

                .next-steps h3 {
                    margin-bottom: 20px;
                    color: #333;
                    text-align: center;
                }

                .steps-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                    gap: 20px;
                }

                .step-card {
                    display: flex;
                    align-items: flex-start;
                    gap: 15px;
                    padding: 20px;
                    background: #f8f9fa;
                    border-radius: 8px;
                    border-left: 4px solid #007cba;
                }

                .step-icon {
                    font-size: 2em;
                }

                .step-content {
                    flex: 1;
                }

                .step-content h4 {
                    margin: 0 0 5px 0;
                    color: #333;
                }

                .step-content p {
                    margin: 0 0 10px 0;
                    color: #666;
                    font-size: 0.9em;
                }

                .btn-small {
                    padding: 5px 10px;
                    font-size: 0.8em;
                    background: #007cba;
                    color: white;
                    text-decoration: none;
                    border-radius: 3px;
                }
            </style>
        <?php endif; ?>

        <div class="dashboard-actions">
            <h2 style="margin-bottom: 20px; color: #333;">Quick Actions</h2>
            <div class="action-buttons">
                <a href="/family-tree" class="btn btn-primary">
                    <strong>View Family Tree</strong><br>
                    <small>Visual family relationships</small>
                </a>

                <a href="/browse-members" class="btn btn-secondary">
                    <strong>Browse Members</strong><br>
                    <small>Search & filter members</small>
                </a>

                <a href="/clans" class="btn btn-warning">
                    <strong>Family Clans</strong><br>
                    <small>Manage family clans</small>
                </a>

                <?php if (current_user_can('edit_family_members')): ?>
                    <a href="/add-member" class="btn btn-info">
                        <strong>Add Member</strong><br>
                        <small>Add new person</small>
                    </a>
                <?php endif; ?>

                <?php if (current_user_can('edit_family_members') && $clan_count === 0): ?>
                    <a href="/add-clan" class="btn btn-info">
                        <strong>Create Clan</strong><br>
                        <small>Start first clan</small>
                    </a>
                <?php endif; ?>

                <?php if (current_user_can('manage_family')): ?>
                    <a href="/family-admin" class="btn btn-outline">
                        <strong>Admin</strong><br>
                        <small>User management</small>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($member_count === 0): ?>
            <div class="no-members-welcome">
                <div class="welcome-message">
                    <h3>Start Building Your Family Legacy</h3>
                    <p>Your family tree is empty. Begin by adding your first family member to create a lasting record of your
                        family history.</p>
                    <p>Once you add members, you'll be able to visualize relationships, track generations, and preserve your
                        family story.</p>

                    <?php if (current_user_can('edit_family_members')): ?>
                        <div style="display: flex; gap: 10px; justify-content: center; margin-top: 20px;">
                            <a href="/add-member" class="btn btn-primary">Add Your First Family Member</a>
                            <a href="/add-clan" class="btn btn-warning">Create Your First Clan</a>
                        </div>
                    <?php else: ?>
                        <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 5px; color: #856404;">
                            <strong>Note:</strong> You need editing permissions to add family members. Contact an administrator.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php wp_footer(); ?>
</body>

</html>