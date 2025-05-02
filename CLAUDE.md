# CP Groups - Claude Guidelines

This document contains essential information about the CP Groups WordPress plugin to help Claude assist with development and support.

## Plugin Overview

CP Groups is a WordPress plugin developed by Church Plugins that helps churches manage and display group information on their websites. It enables church members to discover opportunities to engage in community and discipleship through various types of groups.

## Core Functionality

- Custom Post Type: `cp_group` for managing church groups
- Group information management (leaders, meeting times, locations, etc.)
- Multiple taxonomies for categorization (types, categories, life stages)
- Integration with CP Locations for location-based filtering
- Custom registration form with spam protection
- Shortcodes and Gutenberg blocks for displaying groups
- Archive page for browsing groups (optional)
- Filter system for members to find appropriate groups

## Key Files

- `cp-groups.php` - Main plugin file
- `includes/Models/Group.php` - Group data model
- `includes/Controllers/Group.php` - Group controller with data methods
- `includes/Setup/PostTypes/Group.php` - Group post type setup
- `templates/parts/group-list.php` - Template for group listings
- `templates/parts/group-single.php` - Template for single group view
- `templates/parts/group-modal.php` - Template for group modal view

## Taxonomies

1. **Group Type** (`cp_group_type`): 
   - Types of study/group (small group, bible study, etc.)
   
2. **Group Category** (`cp_group_category`): 
   - Flexible taxonomy for additional categorization
   
3. **Life Stage** (`cp_group_life_stage`):
   - Typically for age ranges or life situations
   
All taxonomies can be customized with different labels as needed.

## Group Attributes

- **Basic Info**: Name, description, leader, meeting time
- **Status Flags**:
  - Kid Friendly
  - Wheelchair Accessible
  - Meets Online
  - Group is Full
- **Contact Options**: Email, registration links

## Template Rendering

The plugin follows an MVC-like pattern:
1. Controllers get data from the database
2. Data is passed to templates for rendering
3. Templates use this data to output HTML

## Common Tasks

When working with this plugin, these commands might be helpful:

```bash
# Check for modified files
git status

# Run tests (if available)
npm test

# Build assets
npm run build
```

## Development Workflow

1. Identify the relevant files for the task
2. Understand the data flow from model to template
3. Make changes following the existing patterns
4. Test changes with sample group data
5. Check compatibility with other Church Plugins

## Settings & Configuration

The plugin has settings accessible via:
- `CP_Groups\Admin\Settings` class
- Settings are grouped into categories (groups, advanced, labels)

## Integration Notes

- Integrates with CP Locations for location-based filtering
- Uses CMB2 for custom fields and metaboxes
- Compatible with other Church Plugins products