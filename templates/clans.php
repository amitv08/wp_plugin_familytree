<?php
if (!is_user_logged_in()) {
    wp_redirect('/family-login');
    exit;
}

$clans = FamilyTreeDatabase::get_clans();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Family Clans</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f0f0f1; padding: 20px; line-height: 1.6; }
        
        .clans-page {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #007cba;
            color: white;
        }
        
        .btn-primary:hover {
            background: #005a87;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #545b62;
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid #007cba;
            color: #007cba;
        }
        
        .btn-outline:hover {
            background: #007cba;
            color: white;
        }
        
        .clans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .clan-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            background: #f8f9fa;
            transition: transform 0.2s ease;
        }
        
        .clan-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .clan-header {
            display: flex;
            justify-content: between;
            align-items: flex-start;
            margin-bottom: 15px;
        }
        
        .clan-name {
            font-size: 1.4em;
            font-weight: bold;
            color: #333;
            margin: 0;
        }
        
        .clan-stats {
            display: flex;
            gap: 15px;
            margin: 15px 0;
            padding: 10px;
            background: white;
            border-radius: 5px;
        }
        
        .stat {
            text-align: center;
            flex: 1;
        }
        
        .stat-number {
            font-size: 1.2em;
            font-weight: bold;
            color: #007cba;
        }
        
        .stat-label {
            font-size: 0.8em;
            color: #666;
        }
        
        .clan-description {
            color: #555;
            margin-bottom: 15px;
        }
        
        .clan-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .no-clans {
            text-align: center;
            padding: 40px;
            color: #666;
        }
        
        .message {
            padding: 12px;
            border-radius: 4px;
            margin: 10px 0;
        }
        
        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<div class="clans-page">
    <div class="page-header">
        <h1>Family Clans</h1>
        <div>
            <a href="/family-dashboard" class="btn btn-secondary">← Back to Dashboard</a>
            <?php if (current_user_can('edit_family_members')): ?>
                <a href="/add-clan" class="btn btn-primary">Add New Clan</a>
            <?php endif; ?>
        </div>
    </div>

    <div id="message-container"></div>

    <?php if ($clans): ?>
        <div class="clans-grid">
            <?php foreach ($clans as $clan): ?>
                <div class="clan-card">
                    <div class="clan-header">
                        <h3 class="clan-name"><?php echo esc_html($clan->clan_name); ?></h3>
                    </div>
                    
                    <?php if ($clan->clan_description): ?>
                        <div class="clan-description">
                            <?php echo esc_html(wp_trim_words($clan->clan_description, 20)); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="clan-stats">
                        <div class="stat">
                            <div class="stat-number"><?php echo $clan->members_count; ?></div>
                            <div class="stat-label">Members</div>
                        </div>
                        <div class="stat">
                            <div class="stat-number"><?php echo $clan->places_count; ?></div>
                            <div class="stat-label">Places</div>
                        </div>
                        <div class="stat">
                            <div class="stat-number"><?php echo $clan->names_count; ?></div>
                            <div class="stat-label">Last Names</div>
                        </div>
                    </div>
                    
                    <div class="clan-actions">
                        <a href="/edit-clan?id=<?php echo $clan->id; ?>" class="btn btn-outline">Manage</a>
                        <a href="/browse-members?clan=<?php echo $clan->id; ?>" class="btn btn-outline">View Members</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="no-clans">
            <h3>No Clans Created Yet</h3>
            <p>Start by creating your first family clan to organize your family tree.</p>
            <?php if (current_user_can('edit_family_members')): ?>
                <a href="/add-clan" class="btn btn-primary">Create First Clan</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?php wp_footer(); ?>
</body>
</html>