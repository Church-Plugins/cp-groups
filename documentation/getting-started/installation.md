# Installation

This guide walks you through installing and activating CP Groups on your WordPress site.

## Prerequisites

Before installing CP Groups:

- A WordPress site running version 5.0 or higher
- PHP 7.0 or higher
- A valid CP Groups license key from [ChurchPlugins](https://churchplugins.com)

## Installing the Plugin

### Upload via WordPress Admin

1. Log in to your WordPress admin dashboard
2. Navigate to **Plugins → Add New**
3. Click the **Upload Plugin** button at the top of the page
4. Select the CP Groups ZIP file you downloaded from ChurchPlugins — do not extract the ZIP first
5. Click **Install Now**
6. Click **Activate Plugin** after the installation completes

### Installing via FTP

If you prefer to install manually:

1. Download the CP Groups ZIP file from your ChurchPlugins account
2. Extract the ZIP file on your computer
3. Upload the `cp-groups` folder to `wp-content/plugins/` on your server using an FTP client
4. Log in to your WordPress admin dashboard
5. Navigate to **Plugins → Installed Plugins**
6. Find **CP Groups** in the list and click **Activate**

## Activating Your License

1. Navigate to **Groups → Settings**
2. Click the **License** tab
3. Enter your license key in the **License Key** field
4. Click **Activate License**
5. Verify that the license status shows as **Active**

### Beta Updates

If you want early access to new features, enable beta updates on the License tab. Beta versions may contain experimental features and should be tested before use on a production site.

## Verifying the Installation

After activation, confirm the plugin is working:

1. Navigate to **Groups → Settings**
2. Verify you see the settings tabs: **Main**, **Groups**, **Advanced**, **Labels**, and **License**
3. Visit the frontend of your site and navigate to `/groups/` to see the archive page

## Next Steps

Continue to [General Settings](../configuration/general-settings.md) to configure your group labels, default thumbnail, and archive page options.
