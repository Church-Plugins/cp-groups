# Requirements

This document outlines the system requirements and dependencies for running CP Groups on your WordPress site.

## System Requirements

| Requirement | Minimum Version |
|-------------|----------------|
| WordPress   | 5.0+           |
| PHP         | 7.0+           |
| MySQL       | 5.6+           |

## Browser Compatibility

CP Groups works in all modern browsers:

- Chrome 60+
- Firefox 55+
- Safari 11+
- Edge 79+

The group archive, filters, and modal use jQuery and standard WordPress scripts that are compatible with these browsers.

## Optional Dependencies

- **CP Locations**: Required for assigning groups to locations and per-location email routing. Install and activate CP Locations before enabling location features. See [CP Locations Integration](../integrations/cp-locations.md) for details.
- **CP Sync**: Required for importing groups from a church management system. See [CP Sync Integration](../integrations/cp-sync.md) for setup instructions.

## reCAPTCHA Requirements

If you plan to use reCAPTCHA on the contact form:

- A Google reCAPTCHA v3 site key and secret key from the [Google reCAPTCHA admin console](https://www.google.com/recaptcha/admin)
- Your domain must be registered in the reCAPTCHA console

## Next Steps

Continue to [Installation](installation.md) to install and activate CP Groups.
