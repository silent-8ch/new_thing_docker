<?php
session_start();
if (empty($_SESSION['user'])) {
    header('Location: /index.php');
    exit;
}
$user = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Landing</title>
    <style>
      body {
        font-family: Arial, sans-serif;
        margin: 2rem;
      }
      .card {
        padding: 1.5rem;
        border: 1px solid #ddd;
        border-radius: 8px;
        max-width: 480px;
      }
    </style>
  </head>
  <body>
    <div class="card">
      <h1>Welcome</h1>
      <p>You are signed in.</p>
      <ul>
        <li><strong>Google ID:</strong> <?php echo htmlspecialchars($user['google_id'] ?? ''); ?></li>
        <li><strong>Email:</strong> <?php echo htmlspecialchars($user['email'] ?? ''); ?></li>
        <li><strong>Name:</strong> <?php echo htmlspecialchars($user['name'] ?? ''); ?></li>
      </ul>
      <form method="post" action="/logout.php">
        <button type="submit">Sign out</button>
      </form>
    </div>
  </body>
</html>
