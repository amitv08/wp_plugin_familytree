<?php
if (!is_user_logged_in() || !current_user_can('edit_family_members')) {
    wp_redirect('/family-login');
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Add New Clan</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f0f0f1; padding: 20px; line-height: 1.6; }
        
        .add-clan-page {
            max-width: 1000px;
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
        
        .btn-success {
            background: #28a745;
            color: white;
        }
        
        .btn-success:hover {
            background: #218838;
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
        
        .clan-form {
            margin-top: 20px;
        }
        
        .form-section {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            border-left: 4px solid #007cba;
            margin-bottom: 25px;
        }
        
        .form-section h3 {
            margin-bottom: 20px;
            color: #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .form-group {
            margin-bottom: 20px;
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
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        
        .form-group textarea {
            min-height: 100px;
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
            margin-top: 30px;
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
    </style>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<div class="add-clan-page">
    <div class="page-header">
        <h1>Add New Clan</h1>
        <a href="/clans" class="btn btn-secondary">← Back to Clans</a>
    </div>

    <form id="add-clan-form" class="clan-form">
        <!-- Basic Clan Information -->
        <div class="form-section">
            <h3>Basic Clan Information</h3>
            <div class="form-group">
                <label for="clan_name">Clan Name *</label>
                <input type="text" id="clan_name" name="clan_name" required placeholder="Enter clan name">
            </div>
            
            <div class="form-group">
                <label for="clan_description">Clan Description</label>
                <textarea id="clan_description" name="clan_description" placeholder="Describe the clan's history, traditions, or notable characteristics..."></textarea>
            </div>
        </div>

        <!-- Clan Places -->
        <div class="form-section">
            <h3>
                Clan Places
                <span style="font-size: 14px; font-weight: normal; color: #666;">(Optional)</span>
            </h3>
            
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
            
            <div class="items-list" id="places-list">
                <div class="no-items">No places added yet</div>
            </div>
        </div>

        <!-- Clan Last Names -->
        <div class="form-section">
            <h3>
                Clan Last Names
                <span style="font-size: 14px; font-weight: normal; color: #666;">(Optional)</span>
            </h3>
            
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
            
            <div class="items-list" id="names-list">
                <div class="no-items">No last names added yet</div>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Create Clan</button>
            <a href="/clans" class="btn btn-secondary">Cancel</a>
        </div>
    </form>

    <div id="form-message"></div>
</div>

<script>
jQuery(document).ready(function($) {
    let places = [];
    let names = [];
    
    // Add place functionality
    $('#add-place').on('click', function() {
        const placeName = $('#new_place_name').val().trim();
        const location = $('#new_place_location').val().trim();
        const description = $('#new_place_description').val().trim();
        
        if (!placeName) {
            alert('Please enter a place name');
            return;
        }
        
        const place = {
            id: Date.now(), // Temporary ID
            place_name: placeName,
            location: location,
            place_description: description
        };
        
        places.push(place);
        updatePlacesList();
        
        // Clear inputs
        $('#new_place_name').val('');
        $('#new_place_location').val('');
        $('#new_place_description').val('');
    });
    
    // Add name functionality
    $('#add-name').on('click', function() {
        const lastName = $('#new_last_name').val().trim();
        const description = $('#new_name_description').val().trim();
        
        if (!lastName) {
            alert('Please enter a last name');
            return;
        }
        
        const name = {
            id: Date.now(), // Temporary ID
            last_name: lastName,
            description: description
        };
        
        names.push(name);
        updateNamesList();
        
        // Clear inputs
        $('#new_last_name').val('');
        $('#new_name_description').val('');
    });
    
    // Remove place
    $(document).on('click', '.remove-place', function() {
        const placeId = $(this).closest('.item-card').data('id');
        places = places.filter(place => place.id != placeId);
        updatePlacesList();
    });
    
    // Remove name
    $(document).on('click', '.remove-name', function() {
        const nameId = $(this).closest('.item-card').data('id');
        names = names.filter(name => name.id != nameId);
        updateNamesList();
    });
    
    // Update places list display
    function updatePlacesList() {
        const $placesList = $('#places-list');
        
        if (places.length === 0) {
            $placesList.html('<div class="no-items">No places added yet</div>');
            return;
        }
        
        let html = '';
        places.forEach(place => {
            html += `
                <div class="item-card" data-id="${place.id}">
                    <div class="item-info">
                        <h4>${place.place_name}</h4>
                        ${place.location ? `<p>📍 ${place.location}</p>` : ''}
                        ${place.place_description ? `<p>${place.place_description}</p>` : ''}
                    </div>
                    <div class="item-actions">
                        <button type="button" class="btn btn-secondary btn-sm remove-place">Remove</button>
                    </div>
                </div>
            `;
        });
        $placesList.html(html);
    }
    
    // Update names list display
    function updateNamesList() {
        const $namesList = $('#names-list');
        
        if (names.length === 0) {
            $namesList.html('<div class="no-items">No last names added yet</div>');
            return;
        }
        
        let html = '';
        names.forEach(name => {
            html += `
                <div class="item-card" data-id="${name.id}">
                    <div class="item-info">
                        <h4>${name.last_name}</h4>
                        ${name.description ? `<p>${name.description}</p>` : ''}
                    </div>
                    <div class="item-actions">
                        <button type="button" class="btn btn-secondary btn-sm remove-name">Remove</button>
                    </div>
                </div>
            `;
        });
        $namesList.html(html);
    }
    
    // Form submission
    $('#add-clan-form').on('submit', function(e) {
        e.preventDefault();
        
        const clanName = $('#clan_name').val().trim();
        const clanDescription = $('#clan_description').val().trim();
        
        if (!clanName) {
            alert('Please enter a clan name');
            return;
        }
        
        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.text();
        submitBtn.text('Creating...').prop('disabled', true);
        
        // Prepare form data
        const formData = new FormData();
        formData.append('action', 'add_clan');
        formData.append('nonce', family_tree.nonce);
        formData.append('clan_name', clanName);
        formData.append('clan_description', clanDescription);
        formData.append('places', JSON.stringify(places));
        formData.append('names', JSON.stringify(names));
        
        // Submit via AJAX
        fetch(family_tree.ajax_url, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(response => {
            submitBtn.text(originalText).prop('disabled', false);
            
            if (response.success) {
                $('#form-message').html('<div class="message success">' + response.data + '</div>');
                $('#add-clan-form')[0].reset();
                places = [];
                names = [];
                updatePlacesList();
                updateNamesList();
                
                setTimeout(function() {
                    window.location.href = '/clans';
                }, 2000);
            } else {
                $('#form-message').html('<div class="message error">' + response.data + '</div>');
            }
        })
        .catch(error => {
            submitBtn.text(originalText).prop('disabled', false);
            $('#form-message').html('<div class="message error">Network error. Please try again.</div>');
            console.error('Error:', error);
        });
    });
});
</script>

<?php wp_footer(); ?>
</body>
</html>