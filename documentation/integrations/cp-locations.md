# CP Locations Integration

CP Groups integrates with CP Locations to enable location-based group organization and per-location email routing. When active, groups can be assigned to locations and each location can have its own CC and BCC email settings for contact form submissions.

## Prerequisites

Before setting up location-based groups:

- CP Locations plugin is installed and activated
- At least one location is created in CP Locations
- CP Groups is installed, activated, and licensed

## How the Integration Works

When CP Locations is active, CP Groups automatically:

1. Adds the location taxonomy (`cp_location`) to the group post type, so groups can be assigned to locations
2. Adds group-specific email settings to each location's edit screen
3. Includes location-based CC and BCC addresses in contact form emails

No manual activation is required — the integration loads automatically when CP Locations is detected.

## Assigning Groups to Locations

1. Open a group in the editor (**Groups → All Groups → [Group Name]**)
2. In the **Location** taxonomy panel (sidebar), check one or more locations
3. Click **Update**

Groups assigned to locations can be filtered by location on the archive page, just like any other taxonomy.

## Location Email Settings

Each location can have its own email routing for group contact form submissions:

1. Navigate to the location edit screen in **CP Locations**
2. Scroll to the **Group Settings** metabox
3. Configure the following fields:

| Field | Description |
|-------|-------------|
| **Group Email CC** | Email addresses to CC on contact form submissions for groups at this location (comma-separated) |
| **Group Email BCC** | Email addresses to BCC on contact form submissions for groups at this location (comma-separated) |

These addresses are combined with the global CC/BCC settings from [Advanced Settings](../configuration/advanced-settings.md) and any per-group CC addresses.

## Email Routing Summary

When a visitor submits the contact form for a group, the email recipients are compiled from multiple sources:

1. **To**: The group leader's email address
2. **CC**: Global CC setting + group-specific CC field + location CC field
3. **BCC**: Global BCC setting + location BCC field

## Troubleshooting

### Location Taxonomy Not Showing

1. Verify CP Locations is installed and activated
2. Deactivate and reactivate CP Groups to re-trigger the integration
3. Check that locations have been created in CP Locations

### Location Emails Not Sending

1. Verify the CC/BCC fields are filled in on the location edit screen
2. Confirm the contact form is working for non-location groups first
3. Check that the group is assigned to the correct location

For more help, see the [Troubleshooting](../advanced/troubleshooting.md) guide.
