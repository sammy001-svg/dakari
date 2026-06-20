<?php
/**
 * Dakari — Deployed Admin Recovery & Diagnostic Utility
 * 
 * SECURITY WARNING: Delete this file from your server after successful use.
 */

// Define the secret token required to access this script
define('ACCESS_TOKEN', 'dakari_secure_reset_2026');

// Validate token before doing anything
$token = $_GET['token'] ?? $_POST['token'] ?? '';
if ($token !== ACCESS_TOKEN) {
    header('HTTP/1.1 403 Forbidden');
    echo '<!DOCTYPE html><html><head><title>Access Denied</title>';
    echo '<style>body { background: #132d22; color: #fff; font-family: sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; } .card { background: #1b4332; padding: 40px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.3); text-align: center; max-width: 400px; } h1 { color: #C9A84C; margin-top: 0; } p { color: #ccc; line-height: 1.6; }</style></head><body>';
    echo '<div class="card"><h1>Access Denied</h1><p>You must provide a valid security token to run this utility script.</p><p style="font-size: 0.85em; color: #888;">Example: <code>/fix_admin.php?token=your_token</code></p></div>';
    echo '</body></html>';
    exit;
}

// Enable display errors just in case
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Load base configuration and database
require_once __DIR__ . '/includes/init.php';

$message = '';
$message_type = 'info';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action === 'reset_pass') {
            $email = trim($_POST['email'] ?? '');
            if ($email !== 'admin@dakari.com' && $email !== 'info@dakari.com') {
                throw new Exception('Invalid admin email selected.');
            }
            
            // Create a fresh hash for "Admin@1234"
            $hash = password_hash('Admin@1234', PASSWORD_BCRYPT, ['cost' => 12]);
            
            // Check if user exists
            $user = fetchOne('SELECT id FROM users WHERE email = ?', 's', $email);
            
            if ($user) {
                // Update existing user
                query(
                    'UPDATE users SET password = ?, role = "admin", is_active = 1 WHERE email = ?',
                    'ss', $hash, $email
                );
                $message = "Successfully reset password for <strong>$email</strong> to <strong>Admin@1234</strong>. Role is set to 'admin' and user is active.";
                $message_type = 'success';
            } else {
                // Create user
                $firstName = ($email === 'admin@dakari.com') ? 'Site' : 'Admin';
                $lastName = ($email === 'admin@dakari.com') ? 'Admin' : 'Dakari';
                
                query(
                    'INSERT INTO users (first_name, last_name, email, password, role, is_active) VALUES (?, ?, ?, ?, "admin", 1)',
                    'ssss', $firstName, $lastName, $email, $hash
                );
                $message = "Admin user <strong>$email</strong> was not found, so it has been created successfully with password <strong>Admin@1234</strong>.";
                $message_type = 'success';
            }
        } elseif ($action === 'create_new') {
            $new_email = trim($_POST['new_email'] ?? '');
            $new_pass = $_POST['new_pass'] ?? '';
            
            if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email address format.');
            }
            if (strlen($new_pass) < 6) {
                throw new Exception('Password must be at least 6 characters.');
            }
            
            $hash = password_hash($new_pass, PASSWORD_BCRYPT, ['cost' => 12]);
            
            // Check if email already exists
            $existing = fetchOne('SELECT id, role FROM users WHERE email = ?', 's', $new_email);
            if ($existing) {
                query('UPDATE users SET password = ?, role = "admin", is_active = 1 WHERE id = ?', 'si', $hash, $existing['id']);
                $message = "User <strong>$new_email</strong> already existed. Updated their password and upgraded role to 'admin'.";
                $message_type = 'success';
            } else {
                query(
                    'INSERT INTO users (first_name, last_name, email, password, role, is_active) VALUES ("Custom", "Admin", ?, ?, "admin", 1)',
                    'ss', $new_email, $hash
                );
                $message = "Created new admin user <strong>$new_email</strong> successfully.";
                $message_type = 'success';
            }
        }
    } catch (Exception $e) {
        $message = 'Error: ' . $e->getMessage();
        $message_type = 'error';
    }
}

// Diagnostic Checks
$diagnostics = [];
$db_connected = false;
$table_exists = false;
$admin_users = [];

// 1. Connection Check
try {
    $db = db();
    if ($db && !$db->connect_error) {
        $db_connected = true;
        $diagnostics[] = ['label' => 'Database Connection', 'status' => 'pass', 'text' => 'Connected successfully to ' . DB_NAME];
    } else {
        $diagnostics[] = ['label' => 'Database Connection', 'status' => 'fail', 'text' => 'Failed to connect.'];
    }
} catch (Exception $e) {
    $diagnostics[] = ['label' => 'Database Connection', 'status' => 'fail', 'text' => 'Connection exception: ' . $e->getMessage()];
}

// 2. Users Table Check
if ($db_connected) {
    $result = query("SHOW TABLES LIKE 'users'");
    if ($result && $result->num_rows > 0) {
        $table_exists = true;
        $diagnostics[] = ['label' => 'Users Table Exists', 'status' => 'pass', 'text' => 'Table `users` found in the database.'];
    } else {
        $diagnostics[] = ['label' => 'Users Table Exists', 'status' => 'fail', 'text' => 'Table `users` does NOT exist. You need to import the SQL schema files first.'];
    }
}

// 3. Admin Users Check
if ($table_exists) {
    $all_admins = fetchAll("SELECT id, first_name, last_name, email, role, is_active FROM users WHERE role = 'admin'");
    foreach ($all_admins as $admin) {
        $admin_users[] = $admin;
    }
    
    $total_users = fetchOne("SELECT COUNT(*) as count FROM users");
    $diagnostics[] = ['label' => 'Total Users in DB', 'status' => 'info', 'text' => $total_users['count'] . ' user account(s) found in total.'];
    $diagnostics[] = ['label' => 'Admin Users Count', 'status' => count($admin_users) > 0 ? 'pass' : 'warn', 'text' => count($admin_users) . ' admin account(s) found.'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dakari Admin Recovery Utility</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --dark-bg: #132d22;
            --primary: #1B4332;
            --accent: #C9A84C;
            --white: #ffffff;
            --text-main: #333333;
            --text-light: #777777;
            --border: #e2e8f0;
            
            --success: #10b981;
            --error: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
        }
        
        * { box-sizing: border-box; }
        body {
            background-color: var(--dark-bg);
            background-image: radial-gradient(circle at 10% 20%, rgba(27, 67, 50, 0.4) 0%, rgba(19, 45, 34, 0.9) 90%);
            color: var(--text-main);
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 40px 20px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start;
        }
        
        .container {
            width: 100%;
            max-width: 800px;
            background: var(--white);
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
            overflow: hidden;
        }
        
        .header {
            background: var(--primary);
            color: var(--white);
            padding: 40px;
            text-align: center;
            border-bottom: 4px solid var(--accent);
            position: relative;
        }
        
        .header h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 2.2rem;
            margin: 0 0 10px 0;
            font-weight: 700;
            letter-spacing: -0.5px;
        }
        
        .header h1 span {
            color: var(--accent);
        }
        
        .header p {
            margin: 0;
            color: #a3b899;
            font-size: 0.95rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 500;
        }
        
        .badge-danger {
            background: var(--error);
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
            margin-top: 15px;
        }
        
        .content {
            padding: 40px;
        }
        
        .section-title {
            font-family: 'Outfit', sans-serif;
            font-size: 1.3rem;
            color: var(--primary);
            margin-top: 0;
            margin-bottom: 20px;
            border-bottom: 2px solid var(--border);
            padding-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            line-height: 1.5;
            font-size: 0.95rem;
            border-left: 5px solid transparent;
        }
        
        .alert-success {
            background-color: #ecfdf5;
            color: #065f46;
            border-left-color: var(--success);
        }
        
        .alert-error {
            background-color: #fef2f2;
            color: #991b1b;
            border-left-color: var(--error);
        }
        
        .alert-info {
            background-color: #eff6ff;
            color: #1e40af;
            border-left-color: var(--info);
        }
        
        .grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 30px;
        }
        
        @media(min-width: 768px) {
            .grid {
                grid-template-columns: 1fr 1fr;
            }
            .grid-full {
                grid-column: span 2;
            }
        }
        
        .card {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 24px;
        }
        
        .diag-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px dashed var(--border);
        }
        
        .diag-item:last-child {
            border-bottom: none;
        }
        
        .diag-label {
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .status-badge {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 4px 8px;
            border-radius: 4px;
            text-transform: uppercase;
        }
        
        .status-pass { background: #d1fae5; color: #065f46; }
        .status-fail { background: #fee2e2; color: #991b1b; }
        .status-warn { background: #fef3c7; color: #92400e; }
        .status-info { background: #dbeafe; color: #1e40af; }
        
        .user-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 0.85rem;
        }
        
        .user-table th, .user-table td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid var(--border);
        }
        
        .user-table th {
            background: #f1f5f9;
            font-weight: 600;
            color: var(--primary);
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 6px;
            color: var(--primary);
        }
        
        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-family: inherit;
            font-size: 0.9rem;
            outline: none;
            transition: border-color 0.2s;
        }
        
        .form-control:focus {
            border-color: var(--accent);
        }
        
        .btn {
            display: inline-block;
            width: 100%;
            padding: 12px 20px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            font-family: inherit;
            font-size: 0.9rem;
            cursor: pointer;
            transition: background 0.2s, transform 0.1s;
            text-align: center;
            text-decoration: none;
        }
        
        .btn:hover {
            background: #143527;
        }
        
        .btn:active {
            transform: scale(0.98);
        }
        
        .btn-accent {
            background: var(--accent);
            color: var(--primary);
        }
        
        .btn-accent:hover {
            background: #b59540;
        }
        
        .footer {
            background: #f1f5f9;
            padding: 20px 40px;
            text-align: center;
            font-size: 0.8rem;
            color: var(--text-light);
            border-top: 1px solid var(--border);
        }
        
        .footer code {
            background: #e2e8f0;
            padding: 2px 6px;
            border-radius: 4px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <p>Dakari System Administration</p>
        <h1>Admin <span>Recovery</span> & Diagnostics</h1>
        <span class="badge-danger">SECURITY TOOL — DELETE AFTER USE</span>
    </div>
    
    <div class="content">
        <?php if ($message): ?>
            <div class="alert alert-<?= $message_type ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <div class="grid">
            <!-- Diagnostics Card -->
            <div class="card">
                <h2 class="section-title">System Diagnostics</h2>
                
                <?php foreach ($diagnostics as $diag): ?>
                    <div class="diag-item">
                        <span class="diag-label"><?= htmlspecialchars($diag['label']) ?></span>
                        <span class="status-badge status-<?= $diag['status'] ?>"><?= htmlspecialchars($diag['status']) ?></span>
                    </div>
                    <div style="font-size:0.8rem; color:var(--text-light); padding-bottom:8px; border-bottom: 1px dashed var(--border);">
                        <?= htmlspecialchars($diag['text']) ?>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Quick Reset Card -->
            <div class="card">
                <h2 class="section-title">Reset Default Admins</h2>
                <p style="font-size: 0.85rem; color: var(--text-light); margin-bottom: 20px; line-height: 1.5;">
                    This action will reset/create the default admin user accounts with the password: <strong>Admin@1234</strong>
                </p>
                
                <form method="post" style="margin-bottom: 15px;">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    <input type="hidden" name="action" value="reset_pass">
                    <input type="hidden" name="email" value="admin@dakari.com">
                    <button type="submit" class="btn" style="margin-bottom: 10px;">Reset/Create admin@dakari.com</button>
                </form>
                
                <form method="post">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    <input type="hidden" name="action" value="reset_pass">
                    <input type="hidden" name="email" value="info@dakari.com">
                    <button type="submit" class="btn btn-accent">Reset/Create info@dakari.com</button>
                </form>
            </div>
            
            <!-- Admin Accounts Table -->
            <div class="card grid-full">
                <h2 class="section-title">Registered Admin Accounts</h2>
                <?php if ($table_exists && count($admin_users) > 0): ?>
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admin_users as $admin): ?>
                                <tr>
                                    <td><?= htmlspecialchars($admin['id']) ?></td>
                                    <td><?= htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']) ?></td>
                                    <td><strong><?= htmlspecialchars($admin['email']) ?></strong></td>
                                    <td><span style="background:#e0f2fe; color:#0369a1; padding:2px 6px; border-radius:4px; font-weight:600; font-size:0.75rem;"><?= htmlspecialchars($admin['role']) ?></span></td>
                                    <td>
                                        <span class="status-badge status-<?= $admin['is_active'] ? 'pass' : 'fail' ?>">
                                            <?= $admin['is_active'] ? 'Active' : 'Inactive' ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p style="font-size:0.9rem; color:var(--text-light); text-align:center; padding:20px 0;">
                        No admin accounts found in the database.
                    </p>
                <?php endif; ?>
            </div>
            
            <!-- Create/Modify Custom Admin Card -->
            <div class="card grid-full">
                <h2 class="section-title">Create / Upgrade Custom Admin</h2>
                <form method="post">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    <input type="hidden" name="action" value="create_new">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                        <div class="form-group" style="margin-bottom:0">
                            <label class="form-label">Email Address</label>
                            <input type="email" name="new_email" class="form-control" placeholder="admin-custom@dakari.com" required>
                        </div>
                        <div class="form-group" style="margin-bottom:0">
                            <label class="form-label">Password</label>
                            <input type="password" name="new_pass" class="form-control" placeholder="Minimum 6 characters" required>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn">Create/Upgrade to Admin</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="footer">
        Remember to delete this file <code>fix_admin.php</code> from your server root once you are logged in.
    </div>
</div>

</body>
</html>
