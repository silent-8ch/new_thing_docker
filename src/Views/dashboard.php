<div class="card">
    <h1>Welcome, <?= htmlspecialchars($user['name']) ?>!</h1>
    
    <div class="user-details">
        <div class="detail-item">
            <span class="detail-label">Provider:</span>
            <span class="detail-value"><?= htmlspecialchars($user['provider']) ?></span>
        </div>
        
        <div class="detail-item">
            <span class="detail-label">Provider User ID:</span>
            <span class="detail-value"><?= htmlspecialchars($user['provider_user_id']) ?></span>
        </div>
        
        <div class="detail-item">
            <span class="detail-label">Email:</span>
            <span class="detail-value"><?= htmlspecialchars($user['email']) ?></span>
        </div>
        
        <div class="detail-item">
            <span class="detail-label">Name:</span>
            <span class="detail-value"><?= htmlspecialchars($user['name']) ?></span>
        </div>
        
        <?php if (!empty($user['avatar'])): ?>
        <div class="detail-item">
            <span class="detail-label">Avatar:</span>
            <span class="detail-value">
                <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar" style="max-width: 100px; border-radius: 5px;">
            </span>
        </div>
        <?php endif; ?>
        
        <div class="detail-item">
            <span class="detail-label">Account Created:</span>
            <span class="detail-value"><?= htmlspecialchars($user['created_at']) ?></span>
        </div>
        
        <div class="detail-item">
            <span class="detail-label">Last Updated:</span>
            <span class="detail-value"><?= htmlspecialchars($user['updated_at']) ?></span>
        </div>
    </div>
    
    <div class="info-section">
        <h3>Raw Token Claims (JSON)</h3>
        <div class="json-data">
            <pre><?= htmlspecialchars(json_encode(json_decode($user['token_claims'], true), JSON_PRETTY_PRINT)) ?></pre>
        </div>
    </div>
    
    <div style="margin-top: 2rem; text-align: center;">
        <a href="/logout.php" class="logout-btn" style="display: inline-block;">Logout</a>
    </div>
</div>
