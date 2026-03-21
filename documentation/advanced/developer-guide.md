# Developer Guide

This guide covers the hooks, filters, template system, and architecture of CP Groups for developers who want to extend or customize the plugin.

## Architecture Overview

CP Groups follows an MVC-like pattern:

- **Models** (`CP_Groups\Models\Group`): Data layer for group post meta
- **Controllers** (`CP_Groups\Controllers\Group`): Business logic and API data formatting
- **Templates** (`templates/`): Frontend rendering with theme override support
- **Settings** (`CP_Groups\Admin\Settings`): CMB2-based settings organized into option groups

The plugin uses the ChurchPlugins core library for shared base classes and utilities.

## Option Groups

Settings are stored in four WordPress option groups:

| Option Group | Contents |
|--------------|----------|
| `cp_groups_main_options` | Default thumbnail |
| `cp_groups_group_options` | Labels, archive page, modal toggle |
| `cp_groups_advanced_options` | Facets, buttons, contact form, spam protection |
| `cp_groups_labels_options` | Taxonomy and badge labels |

## Key Hooks

### Actions

```php
// Fired when CP Groups loads its integrations
do_action( 'cp_groups_load_integrations' );

// Before and after the archive page content
do_action( 'cp_groups_before_archive' );
do_action( 'cp_after_archive' );

// Before and after a single group page
do_action( 'cp_before_group_single' );
do_action( 'cp_after_group_single' );

// After the content section in single group view
// $item contains the group's API data array
do_action( 'cp_group_single_after_content', $item );

// Before and after the group modal
do_action( 'cp_before_group_modal' );
do_action( 'cp_after_group_modal' );
```

### Filters

#### Settings

```php
// Filter any settings value
// $value: the setting value, $key: setting key, $group: option group
add_filter( 'cp_groups_settings_get', function( $value, $key, $group ) {
    if ( $key === 'groups_per_page' ) {
        return 20; // Override groups per page
    }
    return $value;
}, 10, 3 );
```

#### Labels

```php
// Customize post type labels
add_filter( 'cp_single_cp_group_label', function( $label ) {
    return 'Community';
} );

add_filter( 'cp_plural_cp_group_label', function( $label ) {
    return 'Communities';
} );
```

#### Archive and Filters

```php
// Customize the archive page title
add_filter( 'cp-groups-archive-title', function( $title ) {
    return 'Find a Community';
} );

// Control archive page visibility programmatically
add_filter( 'cp_groups_disable_archive', '__return_true' );

// Modify which taxonomies appear in the filter panel
add_filter( 'cp_groups_filter_facets', function( $taxonomies ) {
    // Remove a taxonomy from filters
    unset( $taxonomies['cp_group_life_stage'] );
    return $taxonomies;
} );

// Modify terms shown in a taxonomy filter
add_filter( 'cp_groups_filter_facet_terms', function( $terms, $taxonomy, $tax_obj ) {
    // Filter terms for a specific taxonomy
    return $terms;
}, 10, 3 );

// Customize facet link URLs
add_filter( 'cp_groups_facet_link_uri', function( $uri, $slug, $facet ) {
    return $uri;
}, 10, 3 );
```

#### Shortcode

```php
// Modify the WP_Query parameters for the [cp-groups] shortcode
add_filter( 'cp_groups_shortcode_query', function( $params ) {
    $params['meta_key'] = 'is_group_full';
    $params['meta_value'] = '0';
    return $params;
} );
```

#### Contact Form Emails

```php
// Customize the email subject
add_filter( 'cp_groups_email_subject', function( $subject, $original_subject ) {
    return '[Groups] ' . $original_subject;
}, 10, 2 );

// Customize the email message body
add_filter( 'cp_groups_email_message', function( $message, $group_id ) {
    return $message;
}, 10, 2 );

// Add a suffix to every email (e.g., disclaimer text)
add_filter( 'cp_groups_email_message_suffix', function( $suffix, $group_id ) {
    return $suffix . '<p>This message was sent from our website.</p>';
}, 10, 2 );

// Modify CC and BCC recipients
add_filter( 'cp_groups_email_cc', function( $cc, $group_id ) {
    return $cc;
}, 10, 2 );

add_filter( 'cp_groups_email_bcc', function( $bcc, $group_id ) {
    return $bcc;
}, 10, 2 );

// Modify email headers (full control)
add_filter( 'cp_groups_email_headers', function( $headers, $group_id ) {
    return $headers;
}, 10, 2 );
```

#### Taxonomy Registration

```php
// Add the group type taxonomy to additional post types
add_filter( 'cp_group_type_taxonomy_types', function( $types ) {
    $types[] = 'custom_post_type';
    return $types;
} );

// Same pattern for category and life stage
add_filter( 'cp_group_category_taxonomy_types', function( $types ) { return $types; } );
add_filter( 'cp_group_life_stage_taxonomy_types', function( $types ) { return $types; } );
```

## Template Overrides

Copy any template file from the plugin's `templates/` directory to your theme at `wp-content/themes/your-theme/cp-groups/` to override it:

| Plugin Template | Theme Override Path |
|-----------------|-------------------|
| `templates/archive.php` | `cp-groups/archive.php` |
| `templates/single.php` | `cp-groups/single.php` |
| `templates/parts/group-list.php` | `cp-groups/parts/group-list.php` |
| `templates/parts/group-single.php` | `cp-groups/parts/group-single.php` |
| `templates/parts/group-modal.php` | `cp-groups/parts/group-modal.php` |
| `templates/parts/filter.php` | `cp-groups/parts/filter.php` |
| `templates/parts/filter-selected.php` | `cp-groups/parts/filter-selected.php` |
| `templates/parts/pagination.php` | `cp-groups/parts/pagination.php` |
| `templates/shortcodes/group-list.php` | `cp-groups/shortcodes/group-list.php` |
| `templates/shortcodes/filter.php` | `cp-groups/shortcodes/filter.php` |

## Group API Data

The `CP_Groups\Controllers\Group::get_api_data()` method returns structured data for each group:

```php
[
    'id'               => 123,
    'originID'         => '456',
    'permalink'        => 'https://example.com/groups/young-adults/',
    'slug'             => 'young-adults',
    'thumb'            => 'https://example.com/wp-content/uploads/group.jpg',
    'title'            => 'Young Adults Bible Study',
    'desc'             => '<p>Full description...</p>',
    'excerpt'          => 'Short excerpt...',
    'date'             => [ 'desc' => 'January 15, 2024', 'timestamp' => 1705344000 ],
    'contact_url'      => 'mailto:leader@example.com',
    'registration_url' => 'https://example.com/register',
    'categories'       => [ /* taxonomy terms */ ],
    'locations'        => [ /* location terms */ ],
    'types'            => [ /* type terms */ ],
    'lifeStages'       => [ /* life stage terms */ ],
    'startTime'        => 'Thursdays at 6:00 PM',
    'leader'           => 'John Smith',
    'location'         => 'North Campus, Room 201',
    'handicap'         => 'on',
    'kidFriendly'      => 'on',
    'isFull'           => false,
    'meetsOnline'      => false,
]
```

## Constants

| Constant | Description |
|----------|-------------|
| `CP_GROUPS_PLUGIN_DIR` | Absolute path to the plugin directory |
| `CP_GROUPS_PLUGIN_URL` | URL to the plugin directory |
| `CP_GROUPS_INCLUDES` | Path to the `includes/` directory |

For more help, see the [Troubleshooting](troubleshooting.md) guide.
