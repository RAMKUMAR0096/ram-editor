<?php
require 'config.php';
require_login();

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    if (!$title) {
        $msg = "Title required.";
    } else {
        // create slug
        $slug = strtolower(preg_replace('/[^a-z0-9]+/','-', $title)) . '-' . time();

        // insert project
        $stmt = $pdo->prepare("INSERT INTO projects (user_id, title, slug) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $title, $slug]);
        $project_id = $pdo->lastInsertId();

        // insert default files in DB
        $defaultFiles = [
            'index.html' => "<!doctype html>\n<html>\n<head>\n<meta charset='utf-8'>\n<title>$title</title>\n<link rel='stylesheet' href='index.css'>\n</head>\n<body>\n<h1>Hello World!</h1>\n<script src='script.js'></script>\n</body>\n</html>",
            'index.css' => "body{font-family:sans-serif;margin:30px;background:#f4f4f4}h1{color:#2a5298}",
            'script.js' => "console.log('Project $title loaded');"
        ];

        foreach ($defaultFiles as $name => $content) {
            $stmt = $pdo->prepare("INSERT INTO files (project_id, user_id, filename, content) VALUES (?, ?, ?, ?)");
            $stmt->execute([$project_id, $_SESSION['user_id'], $name, $content]);
        }

        header("Location: editor.php?project=$project_id");
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Create Project</title>
  <style>
    body {
      margin: 0;
      font-family: "Segoe UI", Roboto, sans-serif;
      background: linear-gradient(120deg, #2a5298, #1e3c72);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
    }
    main.auth {
      background: #fff;
      padding: 40px;
      border-radius: 16px;
      width: 420px;
      box-shadow: 0 8px 30px rgba(0,0,0,0.3);
      animation: fadeIn 0.8s ease-in-out;
    }
    h2 {
      margin-bottom: 25px;
      font-size: 24px;
      text-align: center;
      color: #1e3c72;
    }
    label {
      display: block;
      font-weight: 600;
      margin-bottom: 10px;
      color: #333;
    }
    input {
      width: 100%;
      padding: 14px;
      border-radius: 10px;
      border: 1px solid #ccc;
      font-size: 15px;
      margin-bottom: 20px;
      transition: border 0.3s, box-shadow 0.3s;
    }
    input:focus {
      border-color: #2a5298;
      outline: none;
      box-shadow: 0 0 8px rgba(42,82,152,0.5);
    }
    .btn {
      width: 100%;
      background: #2a5298;
      border: none;
      color: #fff;
      padding: 14px;
      border-radius: 10px;
      font-size: 16px;
      font-weight: bold;
      cursor: pointer;
      transition: background 0.3s, transform 0.2s;
    }
    .btn:hover {
      background: #1e3c72;
      transform: translateY(-2px);
    }
    .error {
      background: #ffebee;
      color: #c62828;
      padding: 12px;
      border-radius: 8px;
      margin-bottom: 20px;
      text-align: center;
      font-weight: bold;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(-20px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <main class="auth">
    <h2>🚀 New Project</h2>
    <?php if($msg): ?><div class="error"><?=htmlspecialchars($msg)?></div><?php endif; ?>
    <form method="post">
      <label for="title">Project Title</label>
      <input id="title" name="title" placeholder="Enter project name..." required>
      <button class="btn" type="submit">Create Project</button>
    </form>
    <p style="text-align:center;margin-top:15px;"><a href="dashboard.php">← Back to Dashboard</a></p>
  </main>
</body>
</html>
