# General Settings

The General Settings page lets you configure your default group thumbnail, customize labels, and control the archive page. This is the first place to set up after activating CP Groups.

## Accessing General Settings

1. Log in to your WordPress admin dashboard
2. Navigate to **Groups → Settings**
3. The **Main** tab is selected by default

![CP Groups settings page](https://docs.churchplugins.com/wp-content/uploads/sites/2/2024/03/Screenshot-2024-03-04-at-9.17.56%20AM-1024x315.png)

## Main Tab

### Default Thumbnail

Upload a default thumbnail image that displays for groups without a featured image. This keeps your group listings looking consistent even when individual group images are not set.

1. Click **Upload/Add Image** next to the Default Thumbnail field
2. Select or upload an image from the media library
3. Click **Save Changes**

## Groups Tab

Click the **Groups** tab to access label and archive settings.

### Group Labels

Customize the singular and plural labels used for the group post type throughout the admin and frontend:

| Setting | Default | Description |
|---------|---------|-------------|
| **Singular Label** | Group | Used in headings, buttons, and admin menus (e.g., "Add New Group") |
| **Plural Label** | Groups | Used in archive titles, navigation, and list headings (e.g., "All Groups") |

### Archive Page

By default, CP Groups creates a public archive page at `/groups/` that displays all your groups with filters and search.

- **Disable Archive Page**: Check this box to hide the archive page. Use this if you prefer to display groups only through shortcodes on specific pages.

### Modal Display

By default, clicking a group in the list opens a popup modal with the group's full details.

- **Disable Modal**: Check this box to link directly to the group's single page instead of opening a modal. Use this if you want groups to have their own dedicated pages.

## Labels Tab

Click the **Labels** tab to customize taxonomy and badge labels. These labels appear in the admin sidebar, filter dropdowns, and badge displays.

### Taxonomy Labels

| Setting | Default | Description |
|---------|---------|-------------|
| **Type Singular / Plural** | Type / Types | Label for the group type taxonomy (e.g., "Ministry" / "Ministries") |
| **Category Singular / Plural** | Category / Categories | Label for the group category taxonomy |
| **Life Stage Singular / Plural** | Life Stage / Life Stages | Label for the life stage taxonomy (e.g., "Age Group" / "Age Groups") |

### Badge Labels

| Setting | Default | Description |
|---------|---------|-------------|
| **Kid Friendly** | Kid Friendly | Text displayed on the kid friendly badge |
| **Wheelchair Accessible** | Wheelchair Accessible | Text displayed on the accessible badge |
| **Meets Online** | Meets Online | Text displayed on the meets online badge |

## Applying Settings

1. Click **Save Changes** at the bottom of the page
2. The page refreshes with a confirmation message

After changing labels, visit **Groups** in the admin sidebar to see the updated terminology. Taxonomy labels also update in the frontend filter dropdowns.

## Troubleshooting

If your settings are not taking effect:

- Clear any page cache or object cache on your site
- If the archive page returns a 404 after enabling or disabling it, go to **Settings → Permalinks** and click **Save Changes** to flush rewrite rules

For more help, see the [Troubleshooting](../advanced/troubleshooting.md) guide.
