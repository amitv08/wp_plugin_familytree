<?php
if (!is_user_logged_in() || !current_user_can('edit_family_members')) {
    wp_redirect('/family-login');
    exit;
}

$clan_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$clan = FamilyTreeDatabase::get_clan($clan_id);
$clan_places = FamilyTreeDatabase::get_clan_places($clan_id);
$clan_names = FamilyTreeDatabase::get_clan_names($clan_id);
$clan_members = FamilyTreeDatabase::get_members_by_clan($clan_id);

if (!$clan) {
    echo '<div class="error-message">Clan not found.</div>';
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Clan: <?php echo esc_html($clan->clan_name); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f0f0f1; padding: 20px; line-height: 1.6; }
        
        .edit-clan-page {
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
            padding: 8px 16px;
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
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
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
        
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
        }
        
        .clan-sections {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
            margin-top: 20px;
        }
        
        .section {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 25px;
            background: #f8f9fa;
        }
        
        .section h3 {
            margin-bottom: 20px;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        
        .form-group textarea {
            min-height: 80px;
            resize: vertical;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #007cba;
            box-shadow: 0 0 0 2px rgba(0, 124, 186, 0.2);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .items-list {
            margin-top: 15px;
        }
        
        .item-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .item-info h4 {
            margin: 0 0 5px 0;
            color: #333;
        }
        
        .item-info p {
            margin: 0;
            color: #666;
            font-size: 0.9em;
        }
        
        .item-actions {
            display: flex;
            gap: 5px;
        }
        
        .no-items {
            text-align: center;
            padding: 20px;
            color: #666;
            background: white;
            border: 2px dashed #e0e0e0;
            border-radius: 6px;
        }
        
        .form-actions {
            display: flex;
            gap: 10px;
            justify-content: flex-end;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
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
        
        .section-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .members-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }
        
        .member-card {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 10px;
            text-align: center;
        }
    </style>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<div class="edit-clan-page">
    <div class="page-header">
        <h1>Manage Clan: <?php echo esc_html($clan->clan_name); ?></h1>
        <a href="/clans" class="btn btn-secondary">← Back to Clans</a>
    </div>

    <div id="message-container"></div>

    <div class="clan-sections">
        <!-- Clan Details Section -->
        <div class="section">
            <h3>Clan Details</h3>
            <form id="edit-clan-form">
                <input type="hidden" name="clan_id" value="<?php echo $clan->id; ?>">
                
                <div class="form-group">
                    <label for="clan_name">Clan Name</label>
                    <input type="text" id="clan_name" name="clan_name" value="<?php echo esc_attr($clan->clan_name); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="clan_description">Clan Description</label>
                    <textarea id="clan_description" name="clan_description"><?php echo esc_textarea($clan->clan_description); ?></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Update Clan</button>
                    <button type="button" id="delete-clan" class="btn btn-danger">Delete Clan</button>
                </div>
            </form>
        </div>

        <!-- Clan Places Section -->
        <div class="section">
            <h3>
                Clan Places
                <span style="font-size: 14px; font-weight: normal; color: #666;">(<?php echo count($clan_places); ?> places)</span>
            </h3>
            
            <!-- Add New Place Form -->
            <div class="form-group">
                <label>Add New Place</label>
                <div class="form-row">
                    <input type="text" id="new_place_name" placeholder="Place name (e.g., Ancestral Village)">
                    <input type="text" id="new_place_location" placeholder="Location (e.g., City, Country)">
                </div>
                <textarea id="new_place_description" placeholder="Description of this place..." style="margin-top: 10px;"></textarea>
            </div>
            
            <div class="section-actions">
                <button type="button" id="add-place" class="btn btn-success">Add Place</button>
            </div>
            
            <!-- Existing Places -->
            <div class="items-list" id="places-list">
                <?php if ($clan_places): ?>
                    <?php foreach ($clan_places as $place): ?>
                        <div class="item-card" data-id="<?php echo $place->id; ?>">
                            <div class="item-info">
                                <h4><?php echo esc_html($place->place_name); ?></h4>
                                <?php if ($place->location): ?>
                                    <p>📍 <?php echo esc_html($place->location); ?></p>
                                <?php endif; ?>
                                <?php if ($place->place_description): ?>
                                    <p><?php echo esc_html($place->place_description); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="item-actions">
                                <button class="btn btn-danger btn-sm delete-place">Delete</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-items">No places added yet</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Clan Names Section -->
        <div class="section">
            <h3>
                Clan Last Names
                <span style="font-size: 14px; font-weight: normal; color: #666;">(<?php echo count($clan_names); ?> names)</span>
            </h3>
            
            <!-- Add New Name Form -->
            <div class="form-group">
                <label>Add New Last Name</label>
                <div class="form-row">
                    <input type="text" id="new_last_name" placeholder="Last name (e.g., Smith)">
                    <textarea id="new_name_description" placeholder="Description or history of this name..."></textarea>
                </div>
            </div>
            
            <div class="section-actions">
                <button type="button" id="add-name" class="btn btn-success">Add Last Name</button>
            </div>
            
            <!-- Existing Names -->
            <div class="items-list" id="names-list">
                <?php if ($clan_names): ?>
                    <?php foreach ($clan_names as $name): ?>
                        <div class="item-card" data-id="<?php echo $name->id; ?>">
                            <div class="item-info">
                                <h4><?php echo esc_html($name->last_name); ?></h4>
                                <?php if ($name->description): ?>
                                    <p><?php echo esc_html($name->description); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="item-actions">
                                <button class="btn btn-danger btn-sm delete-name">Delete</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-items">No last names added yet</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Clan Members Section -->
        <div class="section">
            <h3>Clan Members (<?php echo count($clan_members); ?>)</h3>
            <?php if ($clan_members): ?>
                <div class="members-grid">
                    <?php foreach ($clan_members as $member): ?>
                        <div class="member-card">
                            <strong><?php echo esc_html($member->first_name . ' ' . $member->last_name); ?></strong>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p>No members assigned to this clan yet.</p>
            <?php endif; ?>
            <div style="margin-top: 15px;">
                <a href="/browse-members?clan=<?php echo $clan->id; ?>" class="btn btn-outline">Manage Members</a>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Update clan details
    $('#edit-clan-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serializeArray();
        var data = {};
        
        formData.forEach(function(field) {
            data[field.name] = field.value;
        });
        
        data.action = 'update_clan';
        data.nonce = family_tree.nonce;
        
        $.post(family_tree.ajax_url, data, function(response) {
            if (response.success) {
                showMessage(response.data, 'success');
            } else {
                showMessage(response.data, 'error');
            }
        });
    });
    
    // Delete clan
    $('#delete-clan').on('click', function() {
        if (confirm('Are you sure you want to delete this clan? This action cannot be undone.')) {
            var data = {
                action: 'delete_clan',
                clan_id: <?php echo $clan->id; ?>,
                nonce: family_tree.nonce
            };
            
            $.post(family_tree.ajax_url, data, function(response) {
                if (response.success) {
                    showMessage(response.data, 'success');
                    setTimeout(function() {
                        window.location.href = '/clans';
                    }, 1500);
                } else {
                    showMessage(response.data, 'error');
                }
            });
        }
    });
    
    // Add new place
    $('#add-place').on('click', function() {
        const placeName = $('#new_place_name').val().trim();
        const location = $('#new_place_location').val().trim();
        const description = $('#new_place_description').val().trim();
        
        if (!placeName) {
            alert('Please enter a place name');
            return;
        }
        
        const data = {
            action: 'add_clan_place',
            clan_id: <?php echo $clan->id; ?>,
            place_name: placeName,
            location: location,
            place_description: description,
            nonce: family_tree.nonce
        };
        
        $.post(family_tree.ajax_url, data, function(response) {
            if (response.success) {
                showMessage(response.data, 'success');
                // Reload the page to show new place
                setTimeout(() => location.reload(), 1000);
            } else {
                showMessage(response.data, 'error');
            }
        });
    });
    
    // Add new name
    $('#add-name').on('click', function() {
        const lastName = $('#new_last_name').val().trim();
        const description = $('#new_name_description').val().trim();
        
        if (!lastName) {
            alert('Please enter a last name');
            return;
        }
        
        const data = {
            action: 'add_clan_name',
            clan_id: <?php echo $clan->id; ?>,
            last_name: lastName,
            description: description,
            nonce: family_tree.nonce
        };
        
        $.post(family_tree.ajax_url, data, function(response) {
            if (response.success) {
                showMessage(response.data, 'success');
                // Reload the page to show new name
                setTimeout(() => location.reload(), 1000);
            } else {
                showMessage(response.data, 'error');
            }
        });
    });
    
    // Delete place
    $('.delete-place').on('click', function() {
        const placeId = $(this).closest('.item-card').data('id');
        const $card = $(this).closest('.item-card');
        
        if (confirm('Are you sure you want to delete this place?')) {
            const data = {
                action: 'delete_clan_place',
                place_id: placeId,
                nonce: family_tree.nonce
            };
            
            $.post(family_tree.ajax_url, data, function(response) {
                if (response.success) {
                    $card.remove();
                    showMessage(response.data, 'success');
                    // Update the count in the header
                    const currentCount = parseInt($('.section h3 span').text().match(/\d+/)[0]) || 0;
                    $('.section h3 span').text('(' + (currentCount - 1) + ' places)');
                } else {
                    showMessage(response.data, 'error');
                }
            });
        }
    });
    
    // Delete name
    $('.delete-name').on('click', function() {
        const nameId = $(this).closest('.item-card').data('id');
        const $card = $(this).closest('.item-card');
        
        if (confirm('Are you sure you want to delete this last name?')) {
            const data = {
                action: 'delete_clan_name',
                name_id: nameId,
                nonce: family_tree.nonce
            };
            
            $.post(family_tree.ajax_url, data, function(response) {
                if (response.success) {
                    $card.remove();
                    showMessage(response.data, 'success');
                    // Update the count in the header
                    const currentCount = parseInt($('.section h3 span').text().match(/\d+/)[0]) || 0;
                    $('.section h3 span').text('(' + (currentCount - 1) + ' names)');
                } else {
                    showMessage(response.data, 'error');
                }
            });
        }
    });
    
    function showMessage(message, type) {
        $('#message-container').html('<div class="message ' + type + '">' + message + '</div>');
        setTimeout(function() {
            $('#message-container').empty();
        }, 5000);
    }
});
</script>

<?php wp_footer(); ?>
</body>
</html>