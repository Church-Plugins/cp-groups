# Shortcodes

CP Groups provides shortcodes to display group lists and filters on any page or post. Use shortcodes when you want to embed groups outside the archive page or filter to specific groups.

## Available Shortcodes

### `[cp-groups]`

Displays a paginated list of groups. Place this on any page where you want groups to appear.

**Basic usage:**

```html
[cp-groups]
```

This displays all published groups with pagination.

### Filtering by Taxonomy

You can filter the shortcode output to show only groups from specific taxonomy terms. Pass the taxonomy slug as an attribute with the term slug(s) as the value.

**Show only groups of a specific type:**

```html
[cp-groups cp_group_type="small-group"]
```

**Show only groups for a specific life stage:**

```html
[cp-groups cp_group_life_stage="young-adults"]
```

**Show groups matching multiple terms** (comma-separated):

```html
[cp-groups cp_group_type="small-group,bible-study"]
```

### Finding Taxonomy Slugs

To find the slug for a taxonomy term:

1. Navigate to **Groups → [Taxonomy Name]** (e.g., Groups → Types)
2. Hover over the term you want to use
3. The slug appears in the **Slug** column of the table

### `[cp-groups-filter]`

Displays the filter panel (search, taxonomy dropdowns, and attribute checkboxes) without the group list. Use this when you want to place filters separately from the group list on the same page.

```html
[cp-groups-filter]
```

## Creating a Groups Page

A typical groups page setup using shortcodes:

1. Create a new page in WordPress (e.g., "Small Groups")
2. Add the `[cp-groups]` shortcode to display all groups, or filter to specific terms
3. Publish the page
4. Add the page to your site's navigation menu

If you only need to display certain groups (e.g., just young adult groups), the shortcode approach is simpler than the archive page since you can pre-filter the results.

## Shortcodes vs. Archive Page

| Feature | Archive Page | Shortcodes |
|---------|-------------|------------|
| Built-in filters | Yes | Only with `[cp-groups-filter]` |
| Taxonomy filtering | Via frontend filters | Via shortcode attributes |
| Custom page placement | Fixed at `/groups/` | Any page |
| Theme template | Uses archive template | Uses page template |

## Troubleshooting

If the shortcode displays no groups:

- Verify that published groups exist
- Check that the taxonomy slug and term slug are correct (use lowercase with hyphens)
- Make sure there are no typos in the shortcode attributes
- Clear any page cache on your site

For more help, see the [Troubleshooting](../advanced/troubleshooting.md) guide.
