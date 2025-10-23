# WordPress Notice Management System - Complete Documentation

## Overview

This system consists of two WordPress plugins that work together to manage and display dashboard notices across multiple client sites from a central admin location.

### Plugins Included

1. **Admin Notice Manager** - Installed on the admin/central site
2. **Client Notice Receiver** - Installed on each client site

---

## Installation

### Part 1: Admin Site Setup

1. **Create Plugin Directory Structure**

Create the following folder structure in your admin site's `wp-content/plugins/` directory:

```
admin-notice-manager/
├── admin-notice-plugin.php
├── includes/
│   ├── class-anm-database.php
│   ├── class-anm-admin.php
│   ├── class-anm-api.php
│   └── class-anm-clients.php
├── templates/
│   ├── notices-list.php
│   ├── notice-form.php
│   ├── clients-list.php
│   └── settings.php
└── assets/
    ├── css/
    │   └── admin-style.css
    └── js/
        └── admin-script.js
```

2. **Upload Files**
   - Place the main plugin file as `admin-notice-plugin.php`
   - Upload all class files to the `includes/` folder
   - Upload all template files to the `templates/` folder
   - Upload CSS and JS files to their respective `assets/` folders

3. **Activate Plugin**
   - Go to WordPress Admin → Plugins
   - Find "Admin Notice Manager"
   - Click "Activate"
   - The plugin will automatically create required database tables

4. **Add Your First Client**
   - Navigate to Notices → Clients
   - Click "Add New"
   - Enter client site name and URL
   - Click "Add Client"
   - **Copy the generated API Key** - you'll need this for the client site

---

### Part 2: Client Site Setup

1. **Create Plugin Directory Structure**

Create the following folder structure in your client site's `wp-content/plugins/` directory:

```
client-notice-receiver/
├── client-notice-plugin.php
└── assets/
    ├── css/
    │   └── style.css
    └── js/
        └── script.js
```

2. **Upload Files**
   - Place the main plugin file as `client-notice-plugin.php`
   - Upload CSS and JS files to their respective `assets/` folders

3. **Activate Plugin**
   - Go to WordPress Admin → Plugins
   - Find "Client Notice Receiver"
   - Click "Activate"

4. **Configure Connection**
   - Navigate to Settings → Notice Receiver
   - Enter the **Admin Site API URL**: `https://your-admin-site.com/wp-json/anm/v1`
   - Enter the **API Key** (copied from admin site)
   - Click "Save Changes"
   - Click "Test API Connection" to verify setup

---

## Usage Guide

### Creating and Sending Notices

1. **Create a New Notice**
   - Go to Notices → Add New on the admin site
   - Fill in the required fields:
     - **Title**: Notice headline
     - **Content**: Full message (supports HTML, images, links)
     - **Notice Type**: Info, Success, Warning, or Error (affects styling)
     - **Status**: Active or Inactive
     - **Expiration Date**: (Optional) When notice should stop showing
     - **Dismissable**: Allow users to dismiss the notice

2. **Assign to Clients**
   - Check the boxes for client sites that should receive this notice
   - You can select multiple clients
   - Click "Create Notice"

3. **View Your Notices**
   - Go to Notices → All Notices
   - See all created notices with their status and client assignments
   - Edit or delete notices as needed

### Managing Clients

1. **View All Clients**
   - Go to Notices → Clients
   - See all registered client sites
   - View connection status and last connected time
   - Copy API keys for each client

2. **Edit Client**
   - Click "Edit" next to any client
   - Update site name, URL, or status
   - Save changes

3. **Delete Client**
   - Click "Delete" next to a client
   - Confirm deletion
   - This removes all notice assignments for that client

### How Clients Receive Notices

- Notices appear automatically on the client's WordPress dashboard
- Only shown to logged-in users
- Displayed prominently at the top of the dashboard
- Users can dismiss notices (if dismissable is enabled)
- Dismissed notices won't show again to that user
- Fetched fresh on each dashboard load (no caching)

---

## Features

### Admin Plugin Features

✅ **Create Rich Notices**
- Full HTML support
- Image uploads via media library
- Text formatting and links
- Multiple notice types (info, success, warning, error)

✅ **Multi-Client Management**
- Assign notices to one or multiple clients
- Individual API keys for each client
- Track client connection status
- View last connected time

✅ **Expiration Control**
- Set expiration dates for temporary notices
- Automatic removal after expiration
- Optional permanent notices

✅ **Dismissal Tracking**
- Allow users to dismiss notices
- Track dismissals per user
- Dismissed notices won't reappear

✅ **Security Features**
- Unique API keys per client
- API key authentication
- Capability checks
- Nonce verification
- Input sanitization

### Client Plugin Features

✅ **Automatic Display**
- Shows notices on dashboard login
- Supports multiple notices simultaneously
- Smooth animations

✅ **Smart Fetching**
- Connects via REST API
- Fetches only assigned notices
- Respects expiration dates
- Filters out dismissed notices

✅ **User-Friendly**
- Dismissable notices
- Clear visual styling
- Responsive design
- No page reloads needed

✅ **Easy Configuration**
- Simple settings page
- Connection testing tool
- Clear setup instructions

---

## API Endpoints

The admin plugin exposes these REST API endpoints:

### 1. Get Notices
```
GET /wp-json/anm/v1/notices
Headers: X-ANM-API-Key: {your-api-key}
Query: ?user_id={user_id}
```

**Response:**
```json
{
  "success": true,
  "notices": [
    {
      "id": 1,
      "title": "System Maintenance",
      "content": "<p>Scheduled maintenance tonight...</p>",
      "content_type": "html",
      "notice_type": "warning",
      "is_dismissable": true,
      "created_at": "2025-10-23 10:00:00"
    }
  ],
  "count": 1
}
```

### 2. Dismiss Notice
```
POST /wp-json/anm/v1/dismiss/{notice_id}
Headers: X-ANM-API-Key: {your-api-key}
Body: {"user_id": 1}
```

**Response:**
```json
{
  "success": true,
  "message": "Notice dismissed successfully."
}
```

### 3. Ping (Test Connection)
```
GET /wp-json/anm/v1/ping
Headers: X-ANM-API-Key: {your-api-key}
```

**Response:**
```json
{
  "success": true,
  "message": "Connection successful.",
  "client": {
    "site_name": "Client Site",
    "site_url": "https://client-site.com"
  }
}
```

---

## Database Schema

### Admin Plugin Tables

**wp_anm_notices**
- `id` - Unique notice ID
- `title` - Notice title
- `content` - Notice content (HTML/text)
- `content_type` - html or text
- `notice_type` - info, success, warning, error
- `status` - active or inactive
- `expiration_date` - Optional expiration
- `is_dismissable` - Boolean
- `created_at` - Creation timestamp
- `updated_at` - Last update timestamp

**wp_anm_clients**
- `id` - Unique client ID
- `site_name` - Client site name
- `site_url` - Client site URL
- `api_key` - Unique authentication key
- `status` - active or inactive
- `last_connected` - Last API connection
- `created_at` - Registration timestamp

**wp_anm_notice_clients**
- `id` - Relationship ID
- `notice_id` - Foreign key to notices
- `client_id` - Foreign key to clients

**wp_anm_dismissals**
- `id` - Dismissal ID
- `notice_id` - Foreign key to notices
- `client_id` - Foreign key to clients
- `user_id` - WordPress user ID
- `dismissed_at` - Dismissal timestamp

---

## Security Best Practices

### Admin Site Security

1. **API Key Management**
   - Each client has a unique API key
   - Keys are 64-character random strings
   - Store securely and never share publicly
   - Regenerate keys if compromised

2. **Access Control**
   - Only administrators can manage notices
   - `manage_options` capability required
   - All forms use nonce verification

3. **Input Sanitization**
   - All user inputs are sanitized
   - Content uses `wp_kses_post()` for HTML
   - URLs validated with `esc_url_raw()`

### Client Site Security

1. **API Authentication**
   - All API requests require valid API key
   - Invalid keys return 403 Forbidden
   - Inactive clients are denied access

2. **User Privacy**
   - Dismissals tracked per user
   - No sensitive data transmitted
   - User IDs sent for dismissal tracking only

---

## Troubleshooting

### Client Site Not Receiving Notices

**Check 1: Verify Configuration**
- Go to Settings → Notice Receiver
- Ensure API URL is correct (include `/wp-json/anm/v1`)
- Verify API key matches the one in admin site
- Use "Test API Connection" button

**Check 2: Client Status**
- In admin site, go to Notices → Clients
- Ensure client status is "Active"
- Check last connected time

**Check 3: Notice Assignment**
- Edit the notice in admin site
- Verify client is checked in assignment list
- Ensure notice status is "Active"

**Check 4: Expiration**
- Check if notice has expired
- Remove or extend expiration date

### API Connection Errors

**Error: "Invalid API key"**
- Verify API key is copied correctly
- No extra spaces or characters
- Check client is active in admin site

**Error: "Connection failed"**
- Verify admin site URL is accessible
- Check for SSL certificate issues
- Ensure REST API is enabled on admin site
- Test URL in browser: `https://admin-site.com/wp-json/anm/v1/ping`

**Error: "Timeout"**
- Admin site may be slow or down
- Check server resources
- Increase timeout in code if needed

### Notices Not Displaying

**Check 1: Dashboard Page**
- Notices only display on WordPress dashboard
- Won't show on other admin pages
- Must be logged in as administrator

**Check 2: Dismissal Status**
- User may have dismissed the notice
- Check dismissal tracking in database
- Create new notice to test

**Check 3: Browser Console**
- Open browser developer tools
- Check for JavaScript errors
- Verify AJAX requests are successful

---

## Customization

### Changing Notice Styles

Edit `client-notice-receiver/assets/css/style.css`:

```css
/* Custom notice colors */
.cnr-notice.notice-info {
    border-left-color: #your-color;
}

/* Custom font */
.cnr-notice {
    font-family: 'Your Font', sans-serif;
}

/* Larger notices */
.cnr-notice {
    padding: 20px;
    font-size: 16px;
}
```

### Adding Custom Notice Types

In admin plugin, edit `includes/class-anm-database.php`:

```php
// Add custom type in insert_notice defaults
'notice_type' => 'custom'
```

Add styling in client CSS:
```css
.cnr-notice.notice-custom {
    border-left-color: #your-color;
}
```

### Extending Functionality

**Add Notice Priority:**
1. Add `priority` column to `wp_anm_notices` table
2. Update forms to include priority field
3. Sort notices by priority in API response

**Add Notice Categories:**
1. Create `wp_anm_categories` table
2. Add category selection to notice form
3. Filter notices by category on client

**Email Notifications:**
1. Hook into notice creation
2. Send email to client administrators
3. Include notice details and link

---

## File Checklist

### Admin Plugin Files

- [x] `admin-notice-plugin.php` - Main plugin file
- [x] `includes/class-anm-database.php` - Database operations
- [x] `includes/class-anm-admin.php` - Admin interface
- [x] `includes/class-anm-api.php` - REST API endpoints
- [x] `includes/class-anm-clients.php` - Client management
- [x] `templates/notices-list.php` - Notices table view
- [x] `templates/notice-form.php` - Add/edit notice form
- [x] `templates/clients-list.php` - Clients table view
- [x] `templates/settings.php` - Settings page
- [x] `assets/css/admin-style.css` - Admin styles
- [x] `assets/js/admin-script.js` - Admin JavaScript

### Client Plugin Files

- [x] `client-notice-plugin.php` - Main plugin file
- [x] `assets/css/style.css` - Client styles
- [x] `assets/js/script.js` - Client JavaScript

---

## Support & Maintenance

### Regular Maintenance Tasks

1. **Monitor Client Connections**
   - Check "Last Connected" times regularly
   - Investigate clients not connecting for 7+ days
   - Verify API keys haven't been changed

2. **Clean Up Old Notices**
   - Delete expired notices periodically
   - Archive inactive notices
   - Review and update existing notices

3. **Update API Keys**
   - Rotate keys periodically for security
   - Update clients with new keys
   - Test connections after rotation

### Backup Recommendations

- Back up admin site database (includes all notices and clients)
- Export client list with API keys (store securely)
- Document API URL for disaster recovery

---

## Credits

- Built with WordPress best practices
- Uses WordPress REST API
- Follows WordPress Coding Standards
- Compatible with WordPress 5.0+

---

## License

GPL v2 or later

---

## Version History

**v1.0.0** - Initial Release
- Multi-client notice management
- REST API communication
- Dismissable notices
- Expiration dates
- Rich HTML content support
- Security features
