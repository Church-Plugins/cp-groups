# Creating Groups

This guide walks you through adding and configuring individual groups in CP Groups. Each group is a custom post type with fields for leader information, meeting details, status badges, and action links.

## Adding a New Group

1. Navigate to **Groups → Add New**
2. Enter the group **Title** (e.g., "Young Adults Bible Study")
3. Add a **Description** in the content editor — this appears in the group modal or single page
4. Set a **Featured Image** to display as the group thumbnail (falls back to the default thumbnail if not set)
5. Fill in the group-specific fields described below
6. Assign the group to one or more taxonomies (Type, Category, Life Stage)
7. Click **Publish**

## Group Fields

### Basic Information

| Field | Description |
|-------|-------------|
| **Group Leader** | The name of the group leader, displayed on the group card |
| **Group Leader Email** | The leader's email address, used as the recipient for contact form submissions |
| **CC** | Additional email addresses to CC when this group receives a contact form submission (comma-separated) |
| **Meeting Time Desc** | A text description of when the group meets (e.g., "Thursdays at 6:00 PM") |
| **Meeting Location** | Where the group meets (e.g., "North Campus, Room 201") |

### Status Badges

These checkboxes control visual badges that appear on the group card and in the filter system:

| Badge | Description |
|-------|-------------|
| **Kid Friendly** | Indicates the group is kid-friendly or provides childcare |
| **Wheelchair Accessible** | Indicates the meeting location is wheelchair accessible |
| **Meets Online** | Indicates the group meets virtually |
| **Group is Full** | Marks the group as full — hides the registration button and optionally hides the group from listings |

### Action Links

These fields control the buttons that appear on each group:

| Field | Description |
|-------|-------------|
| **Group Details** | A URL linking to an external page with more information about the group. Controls the "View Details" button. |
| **Registration Action** | A URL or email address for the registration button. Enter a URL to link to an external registration page, or an email address to open the visitor's email client. |
| **Contact Action** | A URL or email address for the contact button. This field is used when the Advanced Settings contact action is set to **Use Contact Action**. If set to **Use Contact Form**, this field is ignored and the built-in form is shown instead. |

## Taxonomies

Assign groups to taxonomies to enable filtering on the archive page. These taxonomy labels can be customized in [General Settings](../configuration/general-settings.md).

- **Type** (`cp_group_type`): The type of group — e.g., Small Group, Bible Study, Service Team
- **Category** (`cp_group_category`): A flexible category for additional organization — e.g., Men, Women, Couples
- **Life Stage** (`cp_group_life_stage`): The target audience — e.g., Young Adults, College, Seniors

Taxonomies with no terms assigned are hidden from the frontend filter panel.

## Managing Groups

### Editing a Group

1. Navigate to **Groups → All Groups**
2. Click the group title to open the editor
3. Make your changes
4. Click **Update**

### Reordering Groups

Groups support page attributes, including menu order. Set the **Order** field in the Page Attributes panel to control the display order. By default, groups are sorted alphabetically by title.

### Bulk Actions

Use the WordPress bulk actions menu on the **All Groups** screen to move multiple groups to trash, or use the Quick Edit option to change taxonomy assignments.

## Troubleshooting

If group fields are not displaying on the frontend:

- Verify the field is filled in and saved
- Check that the corresponding badge or button is not disabled in [Advanced Settings](../configuration/advanced-settings.md)
- Clear any page cache on your site

For more help, see the [Troubleshooting](../advanced/troubleshooting.md) guide.
