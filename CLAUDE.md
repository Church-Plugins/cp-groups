# CP Groups Plugin Guidelines

## Project Overview
CP Groups is a WordPress plugin for church group management. It provides:
- Custom post type for groups
- Multiple taxonomies (categories, types, life stages) for organization
- Group details (leaders, meeting times, location)
- Group attributes (kid-friendly, accessible, virtual)
- Location-based filtering with geolocation capabilities
- Contact form with security features (reCAPTCHA, rate limiting)
- Shortcodes for displaying groups on any page

## Key Files and Structure
- `/cp-groups.php` - Main plugin file with initialization
- `/includes/Init.php` - Main plugin class with core functionality
- `/includes/Setup/PostTypes/Group.php` - Group post type definition
- `/includes/Setup/Taxonomies/` - Taxonomy definitions for categories, types, and life stages
- `/templates/` - Template files for front-end display
  - `archive.php` - Group directory
  - `single.php` - Single group display
  - `parts/` - Reusable template parts
  - `shortcodes/` - Templates for shortcode output

## Post Types and Taxonomies
- Post Type: `cp_group`
  - Supports: title, editor, thumbnail, page-attributes
  - Custom meta: leaders, meeting time, location, group attributes
- Taxonomies:
  - `cp_group_category` - Categories for groups
  - `cp_group_type` - Types of groups
  - `cp_group_life_stage` - Life stages for groups

## Code Conventions
- PHP Namespaces: Uses `CP_Groups` namespace
- OOP Approach: Class-based implementation with singletons
- WordPress Coding Standards
- ChurchPlugins core library integration

## Advanced Features
- **Geolocation**
  - Distance-based filtering with Mapbox API
  - Proximity sorting
- **Email System**
  - Contact form for reaching group leaders
  - Security measures including reCAPTCHA, rate limiting, honeypot
- **Filtering System**
  - Multiple filter types (categories, types, attributes)
  - Distance-based filtering

## Common Tasks
### Queries
- Group queries should respect distance parameters if provided:
```php
$query_args = array(
  'post_type' => 'cp_group',
  'orderby'   => 'post_title',
  'order'     => 'ASC',
);
```

### Templates
- Templates can be overridden in theme:
  - Create `/cp-groups/` directory in theme
  - Copy templates from plugin to theme directory
  
### Shortcodes
- `[cp-groups]` - Display groups list
- `[cp-groups-filter]` - Display group filters

### Plugin Integrations
- Compatible with CP Locations plugin
- Uses CMB2 for custom fields
- Integrates with Google reCAPTCHA v3
- Integrates with GroupsEngine

## Commands
### Build/Development
- `npm run bootstrap` - Setup the project
- `npm run build` - Build production assets
- `npm run start` - Start development server
- `npm run archive` - Package the plugin for distribution