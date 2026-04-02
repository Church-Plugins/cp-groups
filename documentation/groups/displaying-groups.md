# Displaying Groups

CP Groups provides an archive page, filters, and a modal system for displaying groups to visitors. This guide covers how the frontend display works and how to configure it.

## Archive Page

By default, CP Groups creates a public archive page at `/groups/`. The page displays a filter panel on the left and the group list on the right.

To disable the archive page, check **Disable Archive Page** in [General Settings](../configuration/general-settings.md). You can then use shortcodes to display groups on specific pages instead.

![Group archive page with filters and group cards](https://docs.churchplugins.com/wp-content/uploads/sites/2/2023/07/Screen-Shot-2023-07-05-at-1.02.34-PM-1024x305.png)

### Archive Page Layout

The archive page includes:

- **Search bar**: Full-text search across group titles and descriptions
- **Taxonomy filters**: Dropdown checkboxes for each taxonomy with assigned terms (Type, Category, Life Stage)
- **Attribute filters**: Checkbox filters for Kid Friendly, Wheelchair Accessible, Meets Online, and Show/Hide Full Groups
- **Group cards**: Each group displays its title, thumbnail, leader, meeting time, location, badges, and action buttons
- **Pagination**: Controlled by the **Groups Per Page** setting in [Advanced Settings](../configuration/advanced-settings.md) (default: 40)

### Filter Behavior

Filters update the group list dynamically. When a visitor selects a filter:

1. The page reloads with the filter applied as a URL parameter
2. Active filters appear as removable badges above the group list
3. Multiple filters can be combined (e.g., "Young Adults" + "Kid Friendly")
4. Taxonomy filters with no assigned terms are automatically hidden

## Group Modal

By default, clicking a group card opens a popup modal with the full group details. The modal includes:

- Group title and description
- Leader name and meeting details
- All assigned badges
- Action buttons (Registration, Contact, Details)

To disable the modal and link directly to the group's single page instead, check **Disable Modal** in [General Settings](../configuration/general-settings.md).

## Group Single Page

Each group has a dedicated single page at `/groups/{group-slug}/`. This page shows the same information as the modal but as a full page with your theme's header and footer.

The single page is always accessible by URL, even when the modal is enabled.

## Template Overrides

Customize the display by copying template files to your theme. Place override files in `wp-content/themes/your-theme/cp-groups/`:

| Template File | Purpose |
|---------------|---------|
| `archive.php` | Main archive page layout |
| `single.php` | Single group page wrapper |
| `parts/group-list.php` | Individual group card in the list |
| `parts/group-single.php` | Full group detail view |
| `parts/group-modal.php` | Group modal popup |
| `parts/filter.php` | Filter/search form |
| `parts/filter-selected.php` | Active filter badges |
| `parts/pagination.php` | Pagination controls |
| `shortcodes/group-list.php` | `[cp-groups]` shortcode output |
| `shortcodes/filter.php` | `[cp-groups-filter]` shortcode output |

## CSS Body Classes

CP Groups does not add custom body classes, but you can target group pages using WordPress default classes:

```css
/* Archive page */
.post-type-archive-cp_group .group-card { }

/* Single group page */
.single-cp_group .group-details { }
```

## Troubleshooting

If the archive page returns a 404:

1. Go to **Settings → Permalinks** in the WordPress admin
2. Click **Save Changes** without making any changes — this flushes the rewrite rules
3. Try visiting `/groups/` again

For more help, see the [Troubleshooting](../advanced/troubleshooting.md) guide.
