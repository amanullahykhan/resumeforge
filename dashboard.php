<?php
require __DIR__ . '/app/bootstrap.php';
use App\Core\{Auth, Database, LicenseManager};

// Configure your external checkout link here!
define('PRO_CHECKOUT_URL', 'https://yourwebsite.com/checkout');

Auth::requireLogin();
$user = Auth::user();
$pdo = Database::pdo();
$error = '';
$success = '';

// Handle License Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'verify_license') {
        $key = trim($_POST['license_key'] ?? '');
        if (empty($key)) {
            $error = "Please enter a license key.";
        } else {
            $res = LicenseManager::verify($key);
            if ($res['ok']) {
                $success = $res['message'];
                $user = Auth::user(); // Refresh user data
            } else {
                $error = $res['error'];
            }
        }
    }
    
    // Handle Resume Creation
    if ($_POST['action'] === 'new_resume') {
        $id = bin2hex(random_bytes(8));
        $title = trim($_POST['title'] ?? 'Untitled Resume');
        
        $defaults = Database::defaults();
        $defaults['profile']['name'] = $user['name'] ?? '';
        $defaults['profile']['email'] = $user['email'] ?? '';
        $defaults['profile']['phone'] = $user['phone'] ?? '';
        
        $locParts = [];
        if (!empty($user['address'])) $locParts[] = trim($user['address']);
        if (!empty($user['zipcode'])) $locParts[] = trim($user['zipcode']);
        if (!empty($user['country'])) $locParts[] = trim($user['country']);
        $defaults['profile']['location'] = implode(', ', $locParts);
        
        $data = json_encode($defaults);
        
        $st = $pdo->prepare('INSERT INTO resumes (id, user_id, title, data, updated_at) VALUES (?, ?, ?, ?, ?)');
        $st->execute([$id, $user['id'], $title, $data, time()]);
        
        header("Location: index.php?id=$id");
        exit;
    }
    
    // Handle Resume Duplication
    if ($_POST['action'] === 'duplicate_resume') {
        $oldId = $_POST['resume_id'] ?? '';
        $st = $pdo->prepare('SELECT title, data FROM resumes WHERE id = ? AND user_id = ?');
        $st->execute([$oldId, $user['id']]);
        $orig = $st->fetch(\PDO::FETCH_ASSOC);
        
        if ($orig) {
            $newId = bin2hex(random_bytes(8));
            $newTitle = 'Copy of ' . $orig['title'];
            $st = $pdo->prepare('INSERT INTO resumes (id, user_id, title, data, updated_at) VALUES (?, ?, ?, ?, ?)');
            $st->execute([$newId, $user['id'], $newTitle, $orig['data'], time()]);
            $success = "Resume duplicated.";
        } else {
            $error = "Resume not found.";
        }
    }

    // Handle Resume Deletion
    if ($_POST['action'] === 'delete_resume') {
        $id = $_POST['resume_id'] ?? '';
        $st = $pdo->prepare('DELETE FROM resumes WHERE id = ? AND user_id = ?');
        $st->execute([$id, $user['id']]);
        $success = "Resume deleted.";
    }
}

// Fetch user's resumes
$st = $pdo->prepare('SELECT id, title, updated_at FROM resumes WHERE user_id = ? ORDER BY updated_at DESC');
$st->execute([$user['id']]);
$resumes = $st->fetchAll(\PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>ResumeForge — Dashboard</title>
    <link rel="stylesheet" href="assets/css/app.css">
    <style>
        .dashboard-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; max-width: 1000px; margin: 40px auto; padding: 0 20px; }
        .card { background: white; padding: 24px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); }
        .resume-list { margin-top: 20px; }
        .resume-item { display: flex; justify-content: space-between; align-items: center; padding: 16px; border: 1px solid #e2e8f0; border-radius: 8px; margin-bottom: 12px; }
        .resume-item h3 { margin: 0 0 4px 0; font-size: 16px; }
        .resume-item p { margin: 0; font-size: 13px; color: #64748b; }
        .btn-sm { padding: 6px 12px; font-size: 13px; }
        .alert { padding: 12px; border-radius: 6px; margin-bottom: 16px; font-size: 14px; }
        .alert.error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .alert.success { background: #dcfce3; color: #166534; border: 1px solid #bbf7d0; }
        .pro-badge { display: inline-block; background: #4f46e5; color: white; padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: bold; margin-left: 8px; }
    </style>
</head>
<body style="background: #f8fafc;">
    
<div class="top">
    <div class="brand">Resume<b>Forge</b></div>
    <div class="spacer"></div>
    <span style="font-size:14px; margin-right:15px; color:#475569;"><?= htmlspecialchars($user['email']) ?></span>
    <a href="logout.php" style="color: #64748b; text-decoration: none; font-size:14px;">Logout</a>
</div>

<div class="dashboard-grid">
    <!-- Left Column: Resumes -->
    <div>
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center;">
                <h2 style="margin:0;">My Resumes</h2>
                <form method="POST" style="margin:0;">
                    <input type="hidden" name="action" value="new_resume">
                    <button class="btn primary btn-sm">＋ Create New</button>
                </form>
            </div>
            
            <?php if ($success): ?><div class="alert success"><?= htmlspecialchars($success) ?></div><?php endif; ?>
            
            <div class="resume-list">
                <?php if (empty($resumes)): ?>
                    <p style="color:#64748b; text-align:center; padding:40px 0;">You haven't created any resumes yet.</p>
                <?php else: ?>
                    <?php foreach ($resumes as $r): ?>
                        <div class="resume-item">
                            <div>
                                <h3><?= htmlspecialchars($r['title']) ?></h3>
                                <p>Last updated: <?= date('M j, Y g:i A', $r['updated_at']) ?></p>
                            </div>
                            <div style="display:flex; gap:8px;">
                                <a href="index.php?id=<?= $r['id'] ?>" class="btn btn-sm">Edit</a>
                                <form method="POST" style="margin:0;">
                                    <input type="hidden" name="action" value="duplicate_resume">
                                    <input type="hidden" name="resume_id" value="<?= $r['id'] ?>">
                                    <button class="btn btn-sm" style="color:#0f172a; border-color:#cbd5e1;">Duplicate</button>
                                </form>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this resume?');" style="margin:0;">
                                    <input type="hidden" name="action" value="delete_resume">
                                    <input type="hidden" name="resume_id" value="<?= $r['id'] ?>">
                                    <button class="btn btn-sm" style="color:#dc2626; border-color:#fecaca;">Delete</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Right Column: License & Account -->
    <div>
        <div class="card">
            <h2 style="margin-top:0;">Account Status</h2>
            <div style="padding: 16px; background: <?= $user['is_pro'] ? '#eef2ff' : '#f1f5f9' ?>; border-radius: 8px; margin-bottom:20px;">
                <div style="font-weight:600; margin-bottom:4px;">
                    Plan: <?= $user['is_pro'] ? 'Business Pro <span class="pro-badge">ACTIVE</span>' : 'Free Tier' ?>
                </div>
                <div style="font-size:13px; color:#475569;">
                    <?= $user['is_pro'] ? 'All premium addons are unlocked.' : 'Upgrade to unlock AI, ATS Scoring, and DOCX exports.' ?>
                </div>
            </div>
            
            <?php if (!$user['is_pro']): ?>
            <h3 style="font-size:15px; margin-bottom:12px;">Activate License</h3>
            <?php if ($error): ?><div class="alert error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
            <form method="POST">
                <input type="hidden" name="action" value="verify_license">
                <input type="text" name="license_key" placeholder="Enter License Key" style="width:100%; padding:10px; border-radius:6px; border:1px solid #cbd5e1; margin-bottom:10px;">
                <button class="btn primary" style="width:100%;">Verify Key</button>
            </form>
            <p style="font-size:12px; color:#64748b; margin-top:12px; text-align:center;">
                Don't have a key? <a href="<?= PRO_CHECKOUT_URL ?>" target="_blank" style="color:#4f46e5;">Purchase one here</a>.
            </p>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
