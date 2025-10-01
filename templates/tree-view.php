<?php
if (!is_user_logged_in()) {
    wp_redirect('/family-login');
    exit;
}

$members = FamilyTreeDatabase::get_members();
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Family Tree Visualization</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; 
            background: #f0f0f1; 
            padding: 20px;
        }
        
        .family-tree-view {
            max-width: 1400px;
            margin: 0 auto;
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .tree-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .tree-controls {
            display: flex;
            gap: 10px;
        }
        
        #tree-container {
            width: 100%;
            height: 700px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            background: #fafafa;
            overflow: auto;
            position: relative;
        }
        
        .tree-node {
            position: absolute;
            background: white;
            border: 2px solid #007cba;
            border-radius: 8px;
            padding: 10px;
            min-width: 150px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .tree-node:hover {
            transform: scale(1.05);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
            z-index: 10;
        }
        
        .tree-node.male { border-color: #4A90E2; }
        .tree-node.female { border-color: #E53E3E; }
        .tree-node.other { border-color: #38A169; }
        
        .tree-node.deceased { 
            opacity: 0.7;
            background-color: #f8f9fa;
        }
        
        .node-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }
        
        .node-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            overflow: hidden;
            background: #e9ecef;
        }
        
        .node-avatar img {
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
            font-weight: bold;
            font-size: 1.2em;
        }
        
        .node-name {
            font-weight: bold;
            font-size: 14px;
            color: #333;
        }
        
        .node-dates {
            font-size: 11px;
            color: #666;
            line-height: 1.2;
        }
        
        .tree-connector {
            position: absolute;
            background: #ccc;
            z-index: 1;
        }
        
        .tree-legend {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .legend-items {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
        }
        
        .legend-color {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            border: 2px solid;
        }
        
        .legend-male { border-color: #4A90E2; }
        .legend-female { border-color: #E53E3E; }
        .legend-other { border-color: #38A169; }
        
        .no-tree-data {
            text-align: center;
            padding: 50px;
            color: #666;
        }
        
        .btn {
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn-primary {
            background: #007cba;
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-outline {
            background: transparent;
            border: 1px solid #6c757d;
            color: #6c757d;
        }
        
        /* Modal styles */
        .member-modal {
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
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 400px;
            width: 90%;
            max-height: 80vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .close-modal {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #666;
        }
    </style>
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<div class="family-tree-view">
    <div class="tree-header">
        <h1>Family Tree Visualization</h1>
        <div class="tree-controls">
            <a href="/family-dashboard" class="btn btn-secondary">← Dashboard</a>
            <a href="/browse-members" class="btn btn-outline">Browse Members</a>
            <button class="btn btn-primary" onclick="resetTreeView()">Reset View</button>
        </div>
    </div>

    <div id="tree-container">
        <?php if (empty($members)): ?>
            <div class="no-tree-data">
                <h3>No Family Members Yet</h3>
                <p>Start building your family tree by adding the first member.</p>
                <a href="/add-member" class="btn btn-primary">Add First Member</a>
            </div>
        <?php else: ?>
            <div id="tree-visualization">
                <!-- Tree will be rendered here by JavaScript -->
                <div style="text-align: center; padding: 50px; color: #666;">
                    <div class="spinner"></div>
                    <p>Loading family tree visualization...</p>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="tree-legend">
        <h4>Legend</h4>
        <div class="legend-items">
            <div class="legend-item">
                <div class="legend-color legend-male"></div>
                <span>Male</span>
            </div>
            <div class="legend-item">
                <div class="legend-color legend-female"></div>
                <span>Female</span>
            </div>
            <div class="legend-item">
                <div class="legend-color legend-other"></div>
                <span>Other</span>
            </div>
            <div class="legend-item">
                <div style="opacity: 0.7;">↗️</div>
                <span>Deceased (lighter color)</span>
            </div>
        </div>
    </div>
</div>

<script>
// Family Tree Visualization
class SimpleFamilyTree {
    constructor(containerId, members) {
        this.container = document.getElementById(containerId);
        this.members = members;
        this.nodeWidth = 180;
        this.nodeHeight = 100;
        this.levelHeight = 150;
        this.margin = 50;
        
        this.render();
    }
    
    render() {
        if (!this.members || this.members.length === 0) {
            this.container.innerHTML = '<div class="no-tree-data"><p>No family data available.</p></div>';
            return;
        }
        
        this.container.innerHTML = '';
        
        // Build hierarchy
        const hierarchy = this.buildHierarchy();
        
        // Calculate positions
        const positionedNodes = this.calculatePositions(hierarchy);
        
        // Draw connectors first (so they appear behind nodes)
        this.drawConnectors(positionedNodes);
        
        // Draw nodes
        this.drawNodes(positionedNodes);
    }
    
    buildHierarchy() {
        const memberMap = new Map();
        
        // Create all nodes
        this.members.forEach(member => {
            memberMap.set(member.id, {
                ...member,
                children: [],
                x: 0,
                y: 0
            });
        });
        
        // Build parent-child relationships
        const rootNodes = [];
        memberMap.forEach((node, id) => {
            if (node.parent1 || node.parent2) {
                if (node.parent1 && memberMap.has(node.parent1)) {
                    memberMap.get(node.parent1).children.push(node);
                }
                if (node.parent2 && memberMap.has(node.parent2)) {
                    memberMap.get(node.parent2).children.push(node);
                }
            } else {
                rootNodes.push(node);
            }
        });
        
        return rootNodes.length > 0 ? rootNodes : [this.members[0]];
    }
    
    calculatePositions(nodes, level = 0, startX = 0) {
        let currentX = startX;
        const positionedNodes = [];
        
        nodes.forEach(node => {
            // Calculate position
            node.x = currentX + this.margin;
            node.y = level * this.levelHeight + this.margin;
            
            positionedNodes.push(node);
            
            // Position children recursively
            if (node.children.length > 0) {
                const childNodes = this.calculatePositions(node.children, level + 1, currentX);
                positionedNodes.push(...childNodes);
                currentX += this.nodeWidth * node.children.length;
            } else {
                currentX += this.nodeWidth;
            }
        });
        
        return positionedNodes;
    }
    
    drawNodes(nodes) {
        nodes.forEach(node => {
            const isDeceased = node.deathDate && new Date(node.deathDate) < new Date();
            const genderClass = node.gender === 'female' ? 'female' : 
                              node.gender === 'male' ? 'male' : 'other';
            const statusClass = isDeceased ? 'deceased' : '';
            
            const nodeElement = document.createElement('div');
            nodeElement.className = `tree-node ${genderClass} ${statusClass}`;
            nodeElement.style.left = node.x + 'px';
            nodeElement.style.top = node.y + 'px';
            nodeElement.onclick = () => this.showMemberDetails(node);
            
            nodeElement.innerHTML = `
                <div class="node-content">
                    <div class="node-avatar">
                        ${node.photo ? 
                            `<img src="${node.photo}" alt="${node.firstName}">` : 
                            `<div class="avatar-placeholder">${node.firstName ? node.firstName.charAt(0) : '?'}</div>`
                        }
                    </div>
                    <div class="node-name">${node.firstName} ${node.lastName}</div>
                    <div class="node-dates">
                        ${node.birthDate ? 'Born: ' + this.formatDate(node.birthDate) : ''}
                        ${node.deathDate ? '<br>Died: ' + this.formatDate(node.deathDate) : ''}
                    </div>
                </div>
            `;
            
            this.container.appendChild(nodeElement);
        });
    }
    
    drawConnectors(nodes) {
        nodes.forEach(node => {
            node.children.forEach(child => {
                const parentX = node.x + this.nodeWidth / 2;
                const parentY = node.y + this.nodeHeight;
                const childX = child.x + this.nodeWidth / 2;
                const childY = child.y;
                
                const connector = document.createElement('div');
                connector.className = 'tree-connector';
                
                // Calculate line properties
                const length = Math.sqrt(Math.pow(childX - parentX, 2) + Math.pow(childY - parentY, 2));
                const angle = Math.atan2(childY - parentY, childX - parentX) * 180 / Math.PI;
                
                connector.style.width = length + 'px';
                connector.style.height = '2px';
                connector.style.background = '#ccc';
                connector.style.left = parentX + 'px';
                connector.style.top = parentY + 'px';
                connector.style.transform = `rotate(${angle}deg)`;
                connector.style.transformOrigin = '0 0';
                
                this.container.appendChild(connector);
            });
        });
    }
    
    formatDate(dateString) {
        if (!dateString) return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
    }
    
    showMemberDetails(member) {
        const modalHtml = `
            <div class="member-modal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3>${member.firstName} ${member.lastName}</h3>
                        <button class="close-modal" onclick="this.closest('.member-modal').remove()">&times;</button>
                    </div>
                    <p><strong>Gender:</strong> ${member.gender || 'Not specified'}</p>
                    <p><strong>Born:</strong> ${member.birthDate ? this.formatDate(member.birthDate) : 'Unknown'}</p>
                    ${member.deathDate ? `<p><strong>Died:</strong> ${this.formatDate(member.deathDate)}</p>` : ''}
                    ${member.biography ? `<p><strong>Biography:</strong> ${member.biography}</p>` : ''}
                    <div style="margin-top: 20px; display: flex; gap: 10px;">
                        <button class="btn btn-primary" onclick="editMember(${member.id})">Edit</button>
                        <button class="btn btn-secondary" onclick="this.closest('.member-modal').remove()">Close</button>
                    </div>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', modalHtml);
    }
}

// Global functions
function editMember(memberId) {
    window.location.href = '/edit-member?id=' + memberId;
}

function resetTreeView() {
    if (window.familyTree) {
        window.familyTree.render();
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('member-modal')) {
        event.target.remove();
    }
});

// Initialize tree when page loads
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($members)): ?>
        // Pass member data to JavaScript
        const memberData = <?php echo json_encode($members); ?>;
        window.familyTree = new SimpleFamilyTree('tree-visualization', memberData);
    <?php endif; ?>
});
</script>

<?php wp_footer(); ?>
</body>
</html>