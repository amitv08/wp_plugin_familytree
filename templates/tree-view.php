<?php
if (!is_user_logged_in()) {
    wp_redirect('/family-login');
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Family Tree View</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f0f0f1; padding: 20px; }
        
        .tree-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            min-height: 500px;
            border: 1px solid #ddd;
        }
        
        .loading-message {
            text-align: center;
            padding: 50px;
            color: #666;
        }
        
        .spinner {
            border: 3px solid #f3f3f3;
            border-top: 3px solid #007cba;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .debug-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 10px 0;
            font-family: monospace;
            font-size: 12px;
        }
        
        /* Tree visualization styles */
        .node circle {
            fill: #fff;
            stroke: steelblue;
            stroke-width: 3px;
        }
        
        .node text {
            font: 12px sans-serif;
        }
        
        .link {
            fill: none;
            stroke: #ccc;
            stroke-width: 2px;
        }
        
        .member-details-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        
        .member-details-modal .modal-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            max-width: 400px;
            width: 90%;
        }
    </style>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<div class="family-tree-view">
    <div class="tree-header">
        <h1>Family Tree</h1>
        <div class="tree-controls">
            <a href="/family-dashboard" class="btn btn-secondary">← Back to Dashboard</a>
            <button class="btn btn-outline" onclick="location.reload()">Refresh</button>
            <button class="btn btn-info" onclick="toggleDebug()">Debug Info</button>
        </div>
    </div>

    <div id="debug-info" class="debug-info" style="display: none;">
        <h4>Debug Information:</h4>
        <div id="debug-content"></div>
    </div>

    <div class="tree-container">
        <div id="tree-visualization">
            <div class="loading-message">
                <p>Loading family tree...</p>
                <div class="spinner"></div>
            </div>
        </div>
    </div>

    <div class="tree-info">
        <p><strong>Tip:</strong> Click on family members to view details. The tree shows parent-child relationships.</p>
    </div>
</div>

<!-- Load D3.js from CDN -->
<script src="https://d3js.org/d3.v7.min.js"></script>

<script>
// Debug function
function toggleDebug() {
    var debug = document.getElementById('debug-info');
    debug.style.display = debug.style.display === 'none' ? 'block' : 'none';
}

// Simple tree visualization as fallback
function renderSimpleTree(data) {
    console.log('Rendering simple tree with data:', data);
    
    var container = document.getElementById('tree-visualization');
    
    if (!data || data.length === 0) {
        container.innerHTML = '<div class="no-data"><p>No family members found. <a href="/add-member">Add the first member</a></p></div>';
        return;
    }
    
    var html = '<div class="simple-tree">';
    html += '<h3>Family Relationships</h3>';
    
    // Group by generations
    var generations = {};
    
    data.forEach(function(member) {
        var level = 0;
        if (member.parent1 || member.parent2) {
            level = 1;
        }
        if (!generations[level]) {
            generations[level] = [];
        }
        generations[level].push(member);
    });
    
    // Display by generations
    Object.keys(generations).sort().forEach(function(level) {
        html += '<div class="generation">';
        html += '<h4>Generation ' + (parseInt(level) + 1) + '</h4>';
        html += '<div class="generation-members">';
        
        generations[level].forEach(function(member) {
            var genderClass = member.gender === 'female' ? 'female' : 
                            member.gender === 'male' ? 'male' : 'other';
            var statusClass = member.deathDate ? 'deceased' : 'living';
            
            html += `
                <div class="tree-member ${genderClass} ${statusClass}" onclick="showMemberDetails(${JSON.stringify(member).replace(/"/g, '&quot;')})">
                    <div class="member-avatar">
                        ${member.photo ? 
                            '<img src="' + member.photo + '" alt="' + member.firstName + '">' : 
                            '<div class="avatar-placeholder">' + (member.firstName ? member.firstName.charAt(0) : '?') + '</div>'
                        }
                    </div>
                    <div class="member-info">
                        <strong>${member.firstName} ${member.lastName}</strong>
                        <div class="member-dates">
                            ${member.birthDate ? 'Born: ' + member.birthDate : ''}
                            ${member.deathDate ? '<br>Died: ' + member.deathDate : ''}
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div></div>';
    });
    
    html += '</div>';
    container.innerHTML = html;
    
    // Add debug info
    document.getElementById('debug-content').innerHTML = `
        <p><strong>Data loaded:</strong> ${data.length} members</p>
        <p><strong>Generations found:</strong> ${Object.keys(generations).length}</p>
        <p><strong>D3.js loaded:</strong> ${typeof d3 !== 'undefined' ? 'Yes' : 'No'}</p>
        <p><strong>Sample data:</strong> ${JSON.stringify(data[0])}</p>
    `;
}

// Member details modal - Fixed version
function showMemberDetails(member) {
    // Remove any existing modals first
    const existingModals = document.querySelectorAll('.member-details-modal');
    existingModals.forEach(modal => modal.remove());
    
    const detailsHtml = `
        <div class="member-details-modal">
            <div class="modal-content">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <h3 style="margin: 0;">${member.firstName} ${member.lastName}</h3>
                    <button onclick="closeMemberModal()" style="background: none; border: none; font-size: 20px; cursor: pointer; color: #666;">&times;</button>
                </div>
                <p><strong>Gender:</strong> ${member.gender || 'Not specified'}</p>
                <p><strong>Born:</strong> ${member.birthDate || 'Unknown'}</p>
                ${member.deathDate ? `<p><strong>Died:</strong> ${member.deathDate}</p>` : ''}
                ${member.biography ? `<p><strong>Biography:</strong> ${member.biography.substring(0, 100)}...</p>` : ''}
                <div style="margin-top: 20px; text-align: center; display: flex; gap: 10px; justify-content: center;">
                    <button onclick="closeMemberModal()" class="btn btn-secondary">Close</button>
                    ${member.id ? `<button onclick="editMember(${member.id})" class="btn btn-primary">Edit</button>` : ''}
                </div>
            </div>
        </div>
    `;
    
    document.body.insertAdjacentHTML('beforeend', detailsHtml);
}

// Proper close function
function closeMemberModal() {
    const modals = document.querySelectorAll('.member-details-modal');
    modals.forEach(modal => modal.remove());
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('member-details-modal')) {
        closeMemberModal();
    }
});

// Close with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeMemberModal();
    }
});

function editMember(memberId) {
    window.location.href = '/edit-member?id=' + memberId;
}

// Load and render tree data
jQuery(document).ready(function($) {
    console.log('Loading tree data...');
    
    $.ajax({
        url: family_tree.ajax_url,
        type: 'POST',
        data: {
            action: 'get_tree_data',
            nonce: family_tree.nonce
        },
        success: function(response) {
            console.log('AJAX response:', response);
            
            if (response.success) {
                // Try advanced D3.js visualization first
                if (typeof d3 !== 'undefined' && response.data && response.data.length > 0) {
                    try {
                        // We'll use the simple tree for now since D3 might be complex
                        renderSimpleTree(response.data);
                    } catch (error) {
                        console.error('D3 error:', error);
                        // Fallback to simple tree
                        renderSimpleTree(response.data);
                    }
                } else {
                    // Use simple tree visualization
                    renderSimpleTree(response.data);
                }
            } else {
                $('#tree-visualization').html(
                    '<div class="error-message">' +
                    '<h3>Error Loading Tree</h3>' +
                    '<p>' + (response.data || 'Unknown error occurred') + '</p>' +
                    '<button class="btn btn-secondary" onclick="location.reload()">Try Again</button>' +
                    '</div>'
                );
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', error);
            $('#tree-visualization').html(
                '<div class="error-message">' +
                '<h3>Network Error</h3>' +
                '<p>Failed to load family tree data. Please check your connection and try again.</p>' +
                '<button class="btn btn-secondary" onclick="location.reload()">Try Again</button>' +
                '</div>'
            );
        }
    });
});
</script>

<style>
.simple-tree {
    padding: 20px;
}

.generation {
    margin-bottom: 30px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.generation h4 {
    margin: 0 0 15px 0;
    color: #333;
    border-bottom: 2px solid #007cba;
    padding-bottom: 5px;
}

.generation-members {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 15px;
}

.tree-member {
    background: white;
    padding: 15px;
    border-radius: 8px;
    border-left: 4px solid #007cba;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.tree-member:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.tree-member.male {
    border-left-color: #4A90E2;
}

.tree-member.female {
    border-left-color: #E53E3E;
}

.tree-member.other {
    border-left-color: #38A169;
}

.tree-member.deceased {
    opacity: 0.7;
    background-color: #f8f9fa;
}

.member-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    overflow: hidden;
    margin: 0 auto 10px;
    background-color: #e9ecef;
}

.member-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.avatar-placeholder {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #007cba, #0056b3);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2em;
    font-weight: bold;
}

.member-info {
    text-align: center;
}

.member-info strong {
    display: block;
    margin-bottom: 5px;
    color: #333;
}

.member-dates {
    font-size: 0.8em;
    color: #666;
    line-height: 1.3;
}

.error-message {
    text-align: center;
    padding: 40px;
    color: #dc3545;
}

.btn {
    display: inline-block;
    padding: 8px 16px;
    margin: 5px;
    border: none;
    border-radius: 4px;
    text-decoration: none;
    cursor: pointer;
    font-size: 14px;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-primary {
    background: #007cba;
    color: white;
}

.btn-info {
    background: #17a2b8;
    color: white;
}

.btn-outline {
    background: transparent;
    border: 1px solid #6c757d;
    color: #6c757d;
}
</style>

<?php wp_footer(); ?>
</body>
</html>