# CP Sync Integration

CP Groups integrates with CP Sync to automatically import groups from your church management system (ChMS). This keeps your website's group listings up to date without manual data entry.

## Prerequisites

Before setting up group synchronization:

- CP Sync is installed, activated, and licensed
- CP Groups is installed, activated, and licensed
- Your ChMS account is connected in CP Sync settings

## Supported Church Management Systems

CP Sync imports group data from:

- **Planning Center Online (PCO)**: Imports groups from PCO Groups
- **Church Community Builder (CCB)**: Imports groups from CCB's group system

## How It Works

1. CP Sync connects to your ChMS via API
2. Group data is pulled based on your sync configuration and filters
3. CP Sync creates or updates `cp_group` posts in WordPress with the imported data
4. Fields like group name, description, leader, meeting time, and location are mapped to CP Groups fields
5. Taxonomy terms (Type, Category, Life Stage) can be mapped from ChMS categories

Syncs can run on a schedule (hourly, daily, weekly) or be triggered manually from the CP Sync settings.

## What Gets Imported

| ChMS Field | CP Groups Field |
|------------|----------------|
| Group name | Post title |
| Description | Post content |
| Group image | Featured image |
| Leader name | Group Leader |
| Leader email | Group Leader Email |
| Meeting time | Meeting Time Desc |
| Meeting location | Meeting Location |
| Group type/category | Taxonomy terms |

The exact fields available depend on your ChMS and what data is configured in CP Sync's field mapping.

## After Import

Once groups are imported:

- They appear in **Groups → All Groups** in the admin
- They display on the archive page and in shortcodes like any manually created group
- You can edit imported groups to add additional details (badges, action links)
- Subsequent syncs update existing groups rather than creating duplicates

## Troubleshooting

### Groups Not Importing

1. Verify your ChMS connection is active in **Settings → CP Sync**
2. Check CP Sync's logs for error messages
3. Confirm groups exist and are visible in your ChMS
4. Run a manual sync to test

### Imported Data Missing Fields

1. Review the field mapping in CP Sync settings
2. Verify the data exists in your ChMS for the affected groups
3. Some fields may not be available from all ChMS platforms

For more help, see the CP Sync documentation or the [Troubleshooting](../advanced/troubleshooting.md) guide.
