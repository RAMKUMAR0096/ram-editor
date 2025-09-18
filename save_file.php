<?php
require 'config.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $project_id = isset($_POST['project_id']) ? (int)$_POST['project_id'] : 0;
    $filename   = isset($_POST['filename']) ? trim($_POST['filename']) : '';
    $content    = $_POST['content'] ?? '';

    if ($project_id <= 0 || $filename === '') {
        die("Invalid request");
    }

    // Check if the file already exists
    $stmt = $pdo->prepare("SELECT id FROM files WHERE project_id=? AND user_id=? AND filename=?");
    $stmt->execute([$project_id, $_SESSION['user_id'], $filename]);
    $file = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($file) {
        // Update file
        $stmt = $pdo->prepare("UPDATE files SET content=?, updated_at=NOW() WHERE id=?");
        $stmt->execute([$content, $file['id']]);
    } else {
        // Insert new file
        $stmt = $pdo->prepare("INSERT INTO files (project_id, user_id, filename, content, created_at, updated_at) VALUES (?,?,?,?,NOW(),NOW())");
        $stmt->execute([$project_id, $_SESSION['user_id'], $filename, $content]);
    }

    // Cache busting with ?v=timestamp to force reload
    $version = time();
    header("Location: editor.php?project={$project_id}&file=" . urlencode($filename) . "&v={$version}");
    exit;
}
