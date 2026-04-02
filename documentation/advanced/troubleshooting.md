# Troubleshooting

This guide covers common issues with CP Groups and how to resolve them.

## Archive Page Issues

### Archive Page Returns 404

1. Go to **Settings → Permalinks** in the WordPress admin
2. Click **Save Changes** without making any modifications — this flushes the rewrite rules
3. Try visiting `/groups/` again
4. If the issue persists, verify **Disable Archive Page** is unchecked in **Groups → Settings → Main**

### Archive Page Shows Wrong Groups

1. Check for active filters in the URL — clear them by visiting `/groups/` directly
2. Verify groups are published (not in draft or pending status)
3. If using CP Sync, confirm the latest sync completed successfully

## Filter Issues

### Taxonomy Filters Not Showing

1. Taxonomies with no assigned terms are automatically hidden — assign at least one group to a term
2. Verify the taxonomy exists under **Groups** in the admin sidebar
3. Check that you have not removed the taxonomy via a custom filter on `cp_groups_filter_facets`

### Badge Filters Not Showing

1. Navigate to **Groups → Settings → Advanced**
2. Verify the corresponding badge filter is enabled (Kid Friendly, Wheelchair Accessible, Meets Online)
3. The Show/Hide Full Groups filter must be set to **Hide** or **Show** (not **Disable**)

### Filters Not Working

1. Check for JavaScript errors in your browser's developer console
2. Verify no caching plugin is serving stale pages — clear all caches
3. Test in an incognito window to rule out browser extensions

## Contact Form Issues

### Form Not Appearing

1. Navigate to **Groups → Settings → Advanced**
2. Verify **Contact Action** is set to **Use Contact Form**
3. Confirm the group has a **Group Leader Email** address entered
4. Check that the contact button is not hidden (Advanced Settings → **Hide Contact** should be unchecked)

### Emails Not Sending

1. Verify WordPress can send emails — install a test plugin like WP Mail SMTP
2. Check the **From Email** and **From Name** in Advanced Settings
3. Verify the group leader's email address is valid
4. Check your server's email logs for delivery failures

### Rate Limit Reached

If you or a tester hits the rate limit during testing:

1. The limit resets daily (tracked by date)
2. To reset immediately, delete the rate limit option from the database:
   - Look for rows with `option_name` starting with `cp_ratelimit_` in the `wp_options` table

### reCAPTCHA Not Working

1. Verify your site key and secret key are entered correctly in Advanced Settings
2. Confirm your domain is registered in the [Google reCAPTCHA console](https://www.google.com/recaptcha/admin)
3. Check the browser console for reCAPTCHA loading errors
4. Ensure no other plugin or security rule is blocking Google's reCAPTCHA scripts

## Display Issues

### Group Modal Not Opening

1. Check for JavaScript errors in your browser's developer console
2. Verify jQuery and jQuery UI Dialog are loading (these are required dependencies)
3. Test with a default WordPress theme to rule out theme conflicts
4. Confirm **Disable Modal** is unchecked in **Groups → Settings → Main**

### Missing Group Information

1. Open the group in the editor and verify the fields are filled in
2. Check that the corresponding badge or button is enabled in Advanced Settings
3. If using template overrides, verify your custom templates include the relevant fields
4. Clear any page or object cache

### Default Thumbnail Not Showing

1. Verify a default thumbnail is uploaded in **Groups → Settings → Main**
2. Check that the image file still exists in the media library
3. If the group has a featured image set, it takes priority over the default thumbnail

## Performance Issues

### Slow Archive Page

1. Reduce the **Groups Per Page** setting in Advanced Settings
2. Optimize group images — large images slow down page loads
3. Ensure your hosting environment has adequate PHP memory
4. Consider enabling a page cache plugin

For additional help, see the [Getting Help](../support/getting-help.md) guide.
