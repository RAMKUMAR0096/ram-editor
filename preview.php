<?php 
require 'config.php'; // DB connection only, no login required

// -----------------------------
// 0. Redirect to add pname if missing
// -----------------------------
if (isset($_GET['project']) && !isset($_GET['pname'])) {
    $project_id = $_GET['project'];

    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        die("Project not found.");
    }

    // Use project title from DB if available, fallback to name, then Project_ID
    $generated_pname = $project['title'] ?? $project['name'] ?? ("Project_" . $project_id);

    // Build new URL with pname
    $query = $_GET;
    $query['pname'] = $generated_pname;
    $new_url = $_SERVER['PHP_SELF'] . '?' . http_build_query($query);

    header("Location: $new_url");
    exit;
}

// -----------------------------
// 1. Handle direct disk access (?file=... with absolute path)
// -----------------------------
if (isset($_GET['file'])) {
    $file = $_GET['file'];
    $file = str_replace('\\', '/', $file);

    if (preg_match('#^[a-zA-Z]:/#', $file) || str_starts_with($file, '/')) {
        if (!file_exists($file)) {
            http_response_code(404);
            die("File not found on disk: " . htmlspecialchars($file));
        }

        $ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
        $mimeTypes = [
            'css'  => 'text/css',
            'js'   => 'application/javascript',
            'json' => 'application/json',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'svg'  => 'image/svg+xml',
            'mp4'  => 'video/mp4',
            'webm' => 'video/webm',
            'ogg'  => 'video/ogg',
            'mp3'  => 'audio/mpeg',
            'wav'  => 'audio/wav',
            'html' => 'text/html',
            'htm'  => 'text/html'
        ];

        $mime = $mimeTypes[$ext] ?? mime_content_type($file);
        header("Content-Type: $mime");
        readfile($file);
        exit;
    }
}

// -----------------------------
// 2. Normal preview flow (DB project system)
// -----------------------------
$project_param = $_GET['project'] ?? '';
$file          = $_GET['file'] ?? 'index.html';
$anchor_id     = '';

if (str_starts_with($file, '#')) {
    $anchor_id = substr($file, 1);
    $file = 'index.html';
} else {
    $anchor_id = $_GET['d'] ?? '';
}

// Fetch project
if (is_numeric($project_param)) {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ?");
} else {
    $stmt = $pdo->prepare("SELECT * FROM projects WHERE slug = ?");
}
$stmt->execute([$project_param]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$project) {
    die("Project not found: " . htmlspecialchars($project_param));
}

$project_id      = $project['id'];
$generated_pname = $project['title'] ?? $project['name'] ?? ("Project_" . $project_id);

// -----------------------------
// 2a. Ensure pname matches project name
// -----------------------------
if (isset($_GET['pname'])) {
    if ($_GET['pname'] !== $generated_pname) {
        die("Unauthorized access: Project name mismatch.");
    }
} else {
    $query = $_GET;
    $query['pname'] = $generated_pname;
    $new_url = $_SERVER['PHP_SELF'] . '?' . http_build_query($query);
    header("Location: $new_url");
    exit;
}

$project_name = $generated_pname;

// -----------------------------
// 3. Fetch file (DB or disk)
// -----------------------------
if (preg_match('#^[a-zA-Z]:/#', $file) || str_starts_with($file, '/')) {
    if (!file_exists($file)) {
        die("File not found on disk: " . htmlspecialchars($file));
    }
    $content = file_get_contents($file);
    $ext     = strtolower(pathinfo($file, PATHINFO_EXTENSION));
} else {
    $file_base = basename($file);
    $stmt = $pdo->prepare("SELECT * FROM files WHERE project_id = ? AND filename = ?");
    $stmt->execute([$project_id, $file_base]);
    $fileRow = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$fileRow) {
        die("File not found in DB: " . htmlspecialchars($file_base));
    }

    $content = $fileRow['content'];
    $ext     = strtolower(pathinfo($file_base, PATHINFO_EXTENSION));
}

// -----------------------------
// 4. MIME types
// -----------------------------
$mimeTypes = [
    'css'  => 'text/css',
    'js'   => 'application/javascript',
    'json' => 'application/json',
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'gif'  => 'image/gif',
    'svg'  => 'image/svg+xml',
    'mp4'  => 'video/mp4',
    'webm' => 'video/webm',
    'ogg'  => 'video/ogg',
    'mp3'  => 'audio/mpeg',
    'wav'  => 'audio/wav',
    'html' => 'text/html',
    'htm'  => 'text/html'
];

if (isset($mimeTypes[$ext]) && !in_array($ext, ['html','htm'])) {
    header("Content-Type: " . $mimeTypes[$ext]);
    echo $content;
    exit;
}

// -----------------------------
// 5. Rewrite URLs
// -----------------------------
function rewrite_urls($matches, $project_id, $project_slug, $project_name) {
    $attr = $matches[1];
    $url  = $matches[2];

    if (preg_match('#^(https?:)?//#', $url) || strpos($url, 'mailto:') === 0 || strpos($url, 'javascript:') === 0) {
        return $matches[0];
    }

    if (preg_match('#^[a-zA-Z]:\\\\#', $url)) {
        $new_url = "preview.php?file=" . urlencode(str_replace('\\', '/', $url));
        return $attr . '="' . $new_url . '"';
    }

    if (str_starts_with($url, '/')) {
        $new_url = "preview.php?file=" . urlencode($url);
        return $attr . '="' . $new_url . '"';
    }

    if (str_starts_with($url, '#')) {
        return $attr . '="?pname=' . urlencode($project_name) . '&project=' . ($project_slug ?: $project_id) . '&file=index.html&d=' . urlencode(substr($url, 1)) . '"';
    }

    $new_url = "?pname=" . urlencode($project_name) . "&project=" . ($project_slug ?: $project_id) . "&file=" . urlencode($url);
    return $attr . '="' . $new_url . '"';
}

$html = preg_replace_callback(
    '#\b(href|src|action)=["\']([^"\']+)["\']#i',
    fn($matches) => rewrite_urls($matches, $project_id, $project['slug'], $project_name),
    $content
);

// -----------------------------
// 6. Rewrite <form> tags
// -----------------------------
$html = preg_replace_callback(
    '#<form\b([^>]*)>#i',
    function ($matches) use ($project_id, $project, $file, $project_name) {
        $formTag = $matches[0];
        if (!preg_match('/\bmethod=/i', $formTag)) {
            $formTag = str_replace('<form', '<form method="post"', $formTag);
        }
        if (!preg_match('/\baction=/i', $formTag)) {
            $formTag = str_replace(
                '<form',
                '<form action="?pname=' . urlencode($project_name) . '&project=' . ($project['slug'] ?: $project_id) . '&file=' . urlencode($file) . '"',
                $formTag
            );
        }
        return $formTag;
    },
    $html
);

// -----------------------------
// 7. Scroll to anchor
// -----------------------------
if ($anchor_id) {
    $html .= "<script>
        document.addEventListener('DOMContentLoaded', function() {
            const el = document.getElementById('" . addslashes($anchor_id) . "');
            if(el) el.scrollIntoView({behavior:'smooth'});
        });
    </script>";
}

// -----------------------------
// 8. Output HTML
// -----------------------------
header("Content-Type: text/html; charset=UTF-8");
echo $html;
