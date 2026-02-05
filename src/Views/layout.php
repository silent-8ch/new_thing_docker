<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'OAuth Playground') ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        
        .header {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 1rem 2rem;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .header-content {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            text-decoration: none;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: white;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 2px solid white;
        }
        
        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .container {
            flex: 1;
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
            width: 100%;
        }
        
        .card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }
        
        h1 {
            color: #333;
            margin-bottom: 1rem;
        }
        
        .oauth-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .oauth-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1rem;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            background: white;
            color: #333;
            text-decoration: none;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s;
            cursor: pointer;
        }
        
        .oauth-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        .oauth-btn.google {
            border-color: #4285f4;
        }
        
        .oauth-btn.google:hover {
            background: #4285f4;
            color: white;
        }
        
        .oauth-btn.github {
            border-color: #333;
        }
        
        .oauth-btn.github:hover {
            background: #333;
            color: white;
        }
        
        .oauth-btn.microsoft {
            border-color: #00a4ef;
        }
        
        .oauth-btn.microsoft:hover {
            background: #00a4ef;
            color: white;
        }
        
        .oauth-btn svg {
            width: 24px;
            height: 24px;
        }
        
        .info-section {
            margin-top: 2rem;
            padding: 1rem;
            background: #f5f5f5;
            border-radius: 8px;
        }
        
        .info-section h3 {
            color: #555;
            margin-bottom: 0.5rem;
        }
        
        .info-section p {
            color: #666;
            line-height: 1.6;
        }
        
        .user-details {
            display: grid;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .detail-item {
            display: flex;
            padding: 0.75rem;
            background: #f9f9f9;
            border-radius: 5px;
        }
        
        .detail-label {
            font-weight: 600;
            color: #555;
            min-width: 150px;
        }
        
        .detail-value {
            color: #333;
            word-break: break-all;
        }
        
        .json-data {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 1rem;
            border-radius: 5px;
            overflow-x: auto;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-top: 1rem;
        }
        
        .error {
            background: #fee;
            border: 1px solid #fcc;
            color: #c33;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .success {
            background: #efe;
            border: 1px solid #cfc;
            color: #3c3;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <?php if (isset($user)): ?>
    <div class="header">
        <div class="header-content">
            <a href="/" class="logo">OAuth Playground</a>
            <div class="user-info">
                <?php if (!empty($user['avatar'])): ?>
                <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" class="user-avatar">
                <?php endif; ?>
                <span><?= htmlspecialchars($user['name']) ?></span>
                <a href="/logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="container">
        <?= $content ?? '' ?>
    </div>
</body>
</html>
