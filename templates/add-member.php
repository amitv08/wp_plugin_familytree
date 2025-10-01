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
    <title>Add Family Member</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f0f0f1; padding: 20px; }
    </style>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<div class="add-member-page">
    <div class="page-header">
        <h1>Add Family Member</h1>
        <a href="/family-dashboard" class="btn btn-back">← Back to Dashboard</a>
    </div>

    <form id="add-member-form" class="member-form">
        <div class="form-section">
            <h3>Basic Information</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" required>
                </div>
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="gender">Gender</label>
                <select id="gender" name="gender">
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                    <option value="other">Other</option>
                </select>
            </div>
        </div>

        <div class="form-section">
            <h3>Life Events</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="birth_date">Birth Date</label>
                    <input type="date" id="birth_date" name="birth_date">
                </div>
                <div class="form-group">
                    <label for="death_date">Death Date</label>
                    <input type="date" id="death_date" name="death_date">
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>Family Relationships</h3>
            <div class="form-row">
                <div class="form-group">
                    <label for="parent1_id">Parent 1</label>
                    <select id="parent1_id" name="parent1_id">
                        <option value="">Select Parent</option>
                        <?php
                        $members = FamilyTreeDatabase::get_members();
                        foreach ($members as $member) {
                            echo '<option value="' . $member->id . '">' . $member->first_name . ' ' . $member->last_name . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="parent2_id">Parent 2</label>
                    <select id="parent2_id" name="parent2_id">
                        <option value="">Select Parent</option>
                        <?php
                        foreach ($members as $member) {
                            echo '<option value="' . $member->id . '">' . $member->first_name . ' ' . $member->last_name . '</option>';
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-section">
            <h3>Additional Information</h3>
            <div class="form-group">
                <label for="photo_url">Photo URL</label>
                <input type="url" id="photo_url" name="photo_url" placeholder="https://example.com/photo.jpg">
            </div>
            <div class="form-group">
                <label for="biography">Biography</label>
                <textarea id="biography" name="biography" rows="4" placeholder="Tell us about this family member..."></textarea>
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Add Family Member</button>
            <a href="/family-dashboard" class="btn btn-secondary">Cancel</a>
        </div>
    </form>

    <div id="form-message"></div>
</div>

<script>
jQuery(document).ready(function($) {
    $('#add-member-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serializeArray();
        var data = {};
        
        formData.forEach(function(field) {
            data[field.name] = field.value;
        });
        
        data.action = 'add_family_member';
        data.nonce = family_tree.nonce;
        
        $.post(family_tree.ajax_url, data, function(response) {
            if (response.success) {
                $('#form-message').html('<div class="message success">' + response.data + '</div>');
                $('#add-member-form')[0].reset();
                setTimeout(function() {
                    window.location.href = '/family-dashboard';
                }, 2000);
            } else {
                $('#form-message').html('<div class="message error">' + response.data + '</div>');
            }
        });
    });
});
</script>

<?php wp_footer(); ?>
</body>
</html>