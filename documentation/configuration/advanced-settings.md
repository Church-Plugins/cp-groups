# Advanced Settings

The Advanced Settings page gives you control over the filter system, button visibility, and the built-in contact form including spam protection options.

## Accessing Advanced Settings

1. Log in to your WordPress admin dashboard
2. Navigate to **Groups → Settings**
3. Click the **Advanced** tab

## Facet Settings

Facets are the filter controls that appear on the group archive page and in the `[cp-groups-filter]` shortcode. These settings control which attribute filters are available to visitors.

| Setting | Default | Description |
|---------|---------|-------------|
| **Kid Friendly** | Enabled | Show or hide the Kid Friendly checkbox filter |
| **Wheelchair Accessible** | Enabled | Show or hide the Wheelchair Accessible checkbox filter |
| **Meets Online** | Enabled | Show or hide the Meets Online checkbox filter |
| **Show/Hide Full Groups** | Hide | Controls the Group is Full filter. **Hide**: enabled, full groups hidden by default. **Show**: enabled, full groups shown by default. **Disable**: no filter shown. |
| **Groups Per Page** | 40 | Number of groups displayed per page (range: 10–100) |

### How Full Groups Work

When the full groups filter is set to **Hide** (the default), groups marked as full are hidden from the listing. Visitors can click a "Show Full Groups" toggle to reveal them. When set to **Show**, full groups appear by default and visitors can hide them.

## Button Settings

Control which action buttons appear on group cards and detail views:

| Setting | Default | Description |
|---------|---------|-------------|
| **Hide Registration** | Unchecked | Hide the registration button on all groups |
| **Hide Details** | Unchecked | Hide the details/view details button on all groups |
| **Contact Action** | Use Contact Action | Controls the contact button behavior |

### Contact Action Options

- **Use Contact Action**: Uses the Contact Action field from each group (a URL or email address)
- **Use Contact Form**: Displays the built-in contact form (see Contact Form Settings below)
- **Hide**: Hides the contact button entirely

## Contact Form Settings

These settings configure the built-in contact form. The contact form appears when **Contact Action** is set to **Use Contact Form**, or when a group's **Registration Action** is an email address. The form lets visitors send messages directly to group leaders from the group modal or single page.

### Email Configuration

| Setting | Default | Description |
|---------|---------|-------------|
| **Show Leader Email** | Unchecked | Display the group leader's email address inside the contact form |
| **From Email** | Site admin email | The "From" address on outgoing emails |
| **From Name** | Site title | The "From" name on outgoing emails |
| **CC** | Empty | Email addresses to CC on all contact form submissions (comma-separated) |
| **BCC** | Empty | Email addresses to BCC on all contact form submissions (comma-separated) |

Individual groups can also have their own CC addresses set in the group editor, which are combined with the global CC setting.

### Spam Protection

CP Groups includes multiple layers of spam protection for the contact form:

**Rate Limiting (Throttling)**

- **Throttle Emails**: Enable rate limiting to prevent abuse
- **Throttle Amount**: Maximum number of submissions per day from the same person (range: 2–10, default: 3)
- Tracks submissions by both IP address and email address

**Staff Email Blocking**

- **Block Staff Emails**: Prevent submissions from email addresses that contain your site's domain name (enabled by default)
- For example, if your site is `example.com`, submissions from `staff@example.com` are blocked

**Honeypot Field**

- **Enable Honeypot**: Adds a hidden field that bots fill in but humans do not
- Submissions with the honeypot field filled are rejected with an error

**reCAPTCHA v3**

- **Enable reCAPTCHA**: Adds Google reCAPTCHA v3 verification to the form
- **Site Key**: Your reCAPTCHA v3 site key from the [Google reCAPTCHA console](https://www.google.com/recaptcha/admin)
- **Secret Key**: Your reCAPTCHA v3 secret key
- Submissions are verified server-side with a minimum score threshold of 0.5

## Troubleshooting

If your advanced settings are not behaving as expected:

- Verify that WordPress cron is running properly if rate limiting does not reset daily
- Check that your reCAPTCHA keys match the domain registered in the Google console
- Test the contact form in an incognito window to rule out caching issues

For more help, see the [Troubleshooting](../advanced/troubleshooting.md) guide.
