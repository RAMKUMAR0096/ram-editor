<?php
require 'config.php';
require_login();

$project_id = isset($_GET['project']) ? (int)$_GET['project'] : 0;
$file       = $_GET['file'] ?? 'index.html';
$anchor_id  = '';

// Handle anchor in file parameter (like file=#contact)
if (str_starts_with($file, '#')) {
    $anchor_id = substr($file, 1); // remove #
    $file = 'index.html'; // default file
} else {
    $anchor_id = $_GET['d'] ?? '';
}

// Only keep the base file name for DB query
$file = basename($file);

// Fetch file from DB
$stmt = $pdo->prepare("SELECT * FROM files WHERE project_id = ? AND filename = ? AND user_id = ?");
$stmt->execute([$project_id, $file, $_SESSION['user_id']]);
$fileRow = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$fileRow) {
    die("File not found: " . htmlspecialchars($file));
}

$content = $fileRow['content'];
$ext     = strtolower(pathinfo($file, PATHINFO_EXTENSION));

// MIME types
$mimeTypes = [
    'css'  => 'text/css',
    'js'   => 'application/javascript',
    'json' => 'application/json',
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif'  => 'image/gif',
    'svg'  => 'image/svg+xml',
    'html' => 'text/html',
    'htm'  => 'text/html'
];

// Serve non-HTML files directly
if (isset($mimeTypes[$ext]) && !in_array($ext, ['html','htm'])) {
    header("Content-Type: " . $mimeTypes[$ext]);
    echo $content;
    exit;
}

// Function to rewrite URLs for preview.php
function rewrite_urls($matches, $project_id) {
    $attr = $matches[1];
    $url  = $matches[2];

    // Skip absolute URLs, mailto:, javascript:
    if (preg_match('#^(https?:)?//#', $url) || strpos($url, 'mailto:') === 0 || strpos($url, 'javascript:') === 0) {
        return $matches[0];
    }

    // Handle internal anchor links
    if (str_starts_with($url, '#')) {
        return $attr . '="?project=' . $project_id . '&file=index.html&d=' . urlencode(substr($url, 1)) . '"';
    }

    // Rewrite relative URLs → go through preview.php
    $new_url = "preview.php?project=$project_id&file=" . urlencode($url);
    return $attr . '="' . $new_url . '"';
}

// Rewrite href, src, action
$html = preg_replace_callback(
    '#\b(href|src|action)=["\']([^"\']+)["\']#i',
    fn($matches) => rewrite_urls($matches, $project_id),
    $content
);

// Rewrite form tags to ensure method and action are correct
$html = preg_replace_callback(
    '#<form\b([^>]*)>#i',
    function ($matches) use ($project_id, $file) {
        $formTag = $matches[0];

        // Ensure method is set, default POST
        if (!preg_match('/\bmethod=/i', $formTag)) {
            $formTag = str_replace('<form', '<form method="post"', $formTag);
        }

        // Ensure action points to this preview.php
        if (!preg_match('/\baction=/i', $formTag)) {
            $formTag = str_replace(
                '<form',
                '<form action="preview.php?project=' . $project_id . '&file=' . urlencode($file) . '"',
                $formTag
            );
        }

        return $formTag;
    },
    $html
);

// If anchor_id is set, scroll to that element using JS
if ($anchor_id) {
    $html .= "<script>
        document.addEventListener('DOMContentLoaded', function() {
            const el = document.getElementById('" . addslashes($anchor_id) . "');
            if(el) el.scrollIntoView({behavior:'smooth'});
        });
    </script>";
}

// Output final HTML
header("Content-Type: text/html; charset=UTF-8");
echo $html;
