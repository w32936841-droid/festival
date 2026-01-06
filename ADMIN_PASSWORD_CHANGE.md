# 🔐 Change Admin Panel Password

## How to change admin password

To change the username and password for the admin panel, edit the `admin/index.php` file:

### Step 1: Open the file
Open the `admin/index.php` file in a text editor.

### Step 2: Find authentication lines
At the beginning of the file (lines 7-8), find these variables:

```php
$admin_username = 'admin';
$admin_password = 'festival2024'; // Change this in production!
```

### Step 3: Change values
Change the username and password to your desired values:

```php
$admin_username = 'your_new_username';
$admin_password = 'your_secure_password';
```

### Step 4: Save and upload
Save the file and upload it to your host.

## ⚠️ Important Security Notes

1. **Choose a strong password**: At least 8 characters including uppercase, lowercase, numbers and symbols
2. **Unique username**: Don't use common names like admin or administrator
3. **Backup the file**: Backup the original file before making changes
4. **Limited access**: Only allow trusted people to edit this file

## 🔑 Current Information

```
Username: admin
Password: festival2024
```

**⚠️ Warning**: These are default credentials. Please change them after setting up the system.
