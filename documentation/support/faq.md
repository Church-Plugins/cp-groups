# Frequently Asked Questions

## General

### What is CP Groups?

CP Groups is a WordPress plugin that helps churches manage and display small groups, bible studies, classes, and other community groups. It provides a filterable directory, contact form, and shortcodes for embedding groups on your site.

### Does CP Groups work with any WordPress theme?

Yes, CP Groups works with any WordPress theme. It uses standard WordPress post types, shortcodes, and templates. You can further customize the display by overriding templates in your theme. See the [Developer Guide](../advanced/developer-guide.md) for template override details.

### Can I rename the taxonomies?

Yes. Navigate to **Groups → Settings → Labels** to customize the singular and plural labels for Type, Category, and Life Stage. You can also rename the badge labels (Kid Friendly, Wheelchair Accessible, Meets Online).

## Setup and Configuration

### How do I create the groups page?

CP Groups automatically creates an archive page at `/groups/` when activated. If you prefer to use a custom page, disable the archive in [General Settings](../configuration/general-settings.md) and use the `[cp-groups]` shortcode on any page. See [Shortcodes](../groups/shortcodes.md) for details.

### How do I set up the contact form?

1. Navigate to **Groups → Settings → Advanced**
2. Set **Contact Action** to **Use Contact Form**
3. Ensure each group has a **Group Leader Email** entered
4. Optionally enable spam protection (honeypot, reCAPTCHA, rate limiting)

See [Advanced Settings](../configuration/advanced-settings.md) for full configuration details.

### Can I have multiple groups pages showing different groups?

Yes. Use the `[cp-groups]` shortcode with taxonomy filtering to show specific groups on different pages. For example, `[cp-groups cp_group_type="small-group"]` on one page and `[cp-groups cp_group_type="bible-study"]` on another.

## Groups

### How do I mark a group as full?

Open the group in the editor, check the **Group is Full** checkbox, and click **Update**. Full groups have their registration button hidden and can be hidden from the archive listing depending on the full groups filter setting.

### Can visitors contact group leaders directly?

Yes, when the contact form is enabled. Visitors fill out a form with their name, email, subject, and message. The email is sent to the group leader's email address, with optional CC/BCC recipients.

### How does the registration button work?

The registration button uses the **Registration Action** field on each group. Enter a URL to link to an external registration page, or enter an email address to open the visitor's email client. To hide registration buttons globally, check **Hide Registration** in [Advanced Settings](../configuration/advanced-settings.md).

## Filters and Display

### Why are some taxonomy filters hidden?

Taxonomy filters are automatically hidden when no groups are assigned to terms in that taxonomy. Assign at least one group to a term for the filter to appear.

### Can I change how many groups show per page?

Yes. Navigate to **Groups → Settings → Advanced** and change the **Groups Per Page** setting (range: 10–100, default: 40).

### Can I disable the modal and use single pages instead?

Yes. Check **Disable Modal** in [General Settings](../configuration/general-settings.md). Clicking a group card will then link to the group's dedicated single page instead of opening a popup.

## Integrations

### Can I import groups from my church management system?

Yes, using CP Sync. CP Sync connects to Planning Center Online or Church Community Builder and imports groups automatically. See [CP Sync Integration](../integrations/cp-sync.md).

### Does CP Groups work with CP Locations?

Yes. When CP Locations is active, you can assign groups to locations and configure per-location email routing. See [CP Locations Integration](../integrations/cp-locations.md).

## Troubleshooting

### The archive page returns a 404

Go to **Settings → Permalinks** and click **Save Changes** to flush rewrite rules. See the [Troubleshooting](../advanced/troubleshooting.md) guide for more solutions.

### Contact form emails are not being received

Verify WordPress can send emails using a plugin like WP Mail SMTP. Check the group leader's email address and the From Email in Advanced Settings. See [Troubleshooting](../advanced/troubleshooting.md) for detailed steps.
