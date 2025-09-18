<?php
require 'config.php';
require_login();

$project_id = isset($_GET['project']) ? (int)$_GET['project'] : 0;

// Validate ownership
$stmt = $pdo->prepare("SELECT * FROM projects WHERE id = ? AND user_id = ?");
$stmt->execute([$project_id, $_SESSION['user_id']]);
$project = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$project) { die("Project not found or access denied."); }

// Fetch file list
$stmt = $pdo->prepare("SELECT filename FROM files WHERE project_id = ? AND user_id = ?");
$stmt->execute([$project_id, $_SESSION['user_id']]);
$files = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Current file
$currentFile = $_GET['file'] ?? 'index.html';
$stmt = $pdo->prepare("SELECT * FROM files WHERE project_id = ? AND user_id = ? AND filename = ?");
$stmt->execute([$project_id, $_SESSION['user_id'], $currentFile]);
$fileRow = $stmt->fetch(PDO::FETCH_ASSOC);
$content = $fileRow ? $fileRow['content'] : '';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Editor - <?= htmlspecialchars($project['title']) ?></title>
<style>
:root{
    --bg: #0c0e21;
    --accent: #1fb6ff;
    --accent-light: #6dd5fa;
    --text: #e6eaf2;
    --radius: 16px;
    --shadow: 0 8px 20px rgba(0,0,0,0.25),0 4px 10px rgba(255,255,255,0.05);
    --transition: 0.35s cubic-bezier(0.25,0.8,0.25,1);
}

/* Reset */
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:"Segoe UI",sans-serif;background:var(--bg);color:var(--text);overflow-x:hidden;}

/* Scrollbar */
::-webkit-scrollbar{width:6px;}
::-webkit-scrollbar-track{background: rgba(255,255,255,0.05);}
::-webkit-scrollbar-thumb{background: var(--accent); border-radius:10px; transition:0.3s;}
::-webkit-scrollbar-thumb:hover{background: var(--accent-light);}

/* Header */
.topbar {
    display:flex; justify-content:space-between; align-items:center;
    padding:20px 30px;
    background: rgba(255,255,255,0.05);
    backdrop-filter: blur(15px) saturate(180%);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    position: sticky; top:10px;
    margin:10px 20px;
    z-index: 100;
    transition: var(--transition);
}
.topbar .brand {
    font-size:1.8rem; font-weight:bold;
    background: linear-gradient(45deg,var(--accent),var(--accent-light));
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
}
.topbar .actions {
    display: flex;
    align-items: center;
    justify-content: center; /* centers content horizontally inside .actions */
    position: relative;
    gap: 12px;
}
.topbar .btn-link {
    color: var(--text); text-decoration:none; padding:8px 16px; border-radius: var(--radius);
    transition: var(--transition);
}
.topbar .btn-link:hover{background: var(--accent); color:#fff; transform: scale(1.05);}
.topbar .btn{
    background: linear-gradient(135deg,var(--accent),var(--accent-light));
    color:#fff; border:none; padding:10px 18px; border-radius: var(--radius);
    font-weight:bold; cursor:pointer; transition: var(--transition);
    box-shadow: 0 6px 15px rgba(31,182,255,0.3);
}
.topbar .btn:hover{transform: scale(1.05); box-shadow: 0 10px 25px rgba(31,182,255,0.5);}

/* Avatar */
.avatar {
    width:40px; height:40px; background: var(--accent); border-radius:50%;
    display:flex; align-items:center; justify-content:center; font-weight:bold;
    color:#fff; font-size:1.1rem; cursor:pointer; box-shadow:0 4px 12px rgba(31,182,255,0.4);
    transition: var(--transition);
}
.avatar:hover{transform: scale(1.1); box-shadow:0 6px 20px rgba(31,182,255,0.6);}

/* User Panel */
/* User Panel */
.user-panel {
    position: absolute;
    top: 60px;
    left: 50%; /* center horizontally */
    transform: translate(-50%, -15px) scale(0.95); /* center + initial scale */
    background: rgba(255, 255, 255, 0.15); /* more visible glass background */
    backdrop-filter: blur(20px) saturate(180%);
    border-radius: var(--radius);
    padding: 15px 20px;
    min-width: 220px;
    box-shadow: 0 8px 24px rgba(0,0,0,0.35), 0 4px 12px rgba(255,255,255,0.05);
    opacity: 0;
    visibility: hidden;
    transition: transform 0.4s cubic-bezier(0.25, 0.8, 0.25, 1),
                opacity 0.4s ease,
                visibility 0.4s ease;
    display: flex;
    flex-direction: column;
    align-items: center; /* center content inside panel */
    gap: 12px;
    z-index: 200;
}

/* Active state */
.user-panel.active {
    opacity: 1;
    visibility: visible;
    transform: translate(-50%, 0) scale(1); /* slide down + scale in */
}

/* User Name */
.user-panel .user-name {
    font-weight: 600;
    color: var(--accent-light);
    font-size: 1rem;
    text-align: center;
}

/* Logout Button */
.user-panel .btn-logout {
    width: 100%;
    padding: 10px;
    border-radius: var(--radius);
    border: none;
    background: linear-gradient(135deg, var(--accent), var(--accent-light));
    color: #fff;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease, background 0.3s ease;
    box-shadow: 0 4px 12px rgba(31, 182, 255, 0.4);
}

/* Logout hover effect */
.user-panel .btn-logout:hover {
    transform: scale(1.07) translateY(-2px);
    box-shadow: 0 8px 20px rgba(31,182,255,0.6);
    background: linear-gradient(135deg, var(--accent-light), var(--accent));
}

/* Main container */
main.container {padding:25px; display:flex; gap:20px; flex-wrap:wrap;}

/* File list */
.file-list{
    width:220px; background: rgba(255,255,255,0.05); backdrop-filter: blur(15px);
    border-radius: var(--radius); padding:15px; box-shadow: var(--shadow); flex-shrink:0;
}
.file-list h4{color: var(--accent-light); margin-bottom:10px;}
.file-list ul{list-style:none;}
.file-list li{margin-bottom:8px; padding:8px; border-radius: var(--radius);
    transition: var(--transition); background: rgba(255,255,255,0.02); cursor:pointer;}
.file-list li:hover{background: rgba(31,182,255,0.1);}
.file-list a{text-decoration:none;color: var(--text);font-weight:bold;font-size:14px;}

/* Input and buttons */
input[type=text]{
    width:100%; padding:8px; border-radius: var(--radius); border:2px solid var(--accent);
}
input[type=text]:focus{outline:none; box-shadow:0 0 6px var(--accent-light); border-color: var(--accent-light);}
.btn{margin-top:8px; display:inline-block; cursor:pointer; text-align:center;}
.btn, .btn-link {
    display: inline-block;
    background: linear-gradient(135deg,var(--accent),var(--accent-light));
    color: #fff;
    border: none;
    padding: 10px 18px;
    border-radius: var(--radius);
    font-weight: bold;
    font-size: 14px;
    text-decoration: none;
    transition: var(--transition);
    box-shadow: 0 6px 15px rgba(31,182,255,0.3);
}
.btn:hover, .btn-link:hover {transform: scale(1.05); box-shadow:0 10px 25px rgba(31,182,255,0.5);}

/* Code Editor */
.code-editor{
    flex:1; background: rgba(255,255,255,0.05); backdrop-filter: blur(15px);
    border-radius: var(--radius); padding:20px; box-shadow: var(--shadow);
}
.code-editor h3{margin-top:0; color:var(--accent-light);}
textarea{
    width:100%; height:400px; font-family:monospace; font-size:14px; padding:10px;
    border-radius: var(--radius); border:2px solid rgba(255,255,255,0.1);
    background: rgba(0,0,0,0.1); color:#fff; resize:vertical;
}
textarea:focus{outline:none; border-color: var(--accent); box-shadow:0 0 8px var(--accent);}
textarea::-webkit-scrollbar-thumb{background: var(--accent);}
textarea::-webkit-scrollbar-track{background: rgba(255,255,255,0.05);}

/* Responsive */
@media(max-width:900px){main.container{flex-direction:column;} .file-list{width:100%;}}
</style>
</head>
<body>

<header class="topbar">
    <a href='index.php' style="text-decoration:none;"><div class="brand">Ram Editor</div></a>
    <div class="actions">
        <a href="dashboard.php" class="btn">Back</a>
        <a class="btn" href="preview.php?project=<?=$project_id?>" target="_blank">Preview</a>
        <div class="avatar" onclick="toggleUserPanel()"><?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?></div>
        <div class="user-panel" id="userPanel">
            <span class="user-name"><?=htmlspecialchars($_SESSION['user_name'])?></span>
            <form method="post" action="logout.php">
                <button type="submit" class="btn-logout">Logout</button>
            </form>
        </div>
    </div>
</header>

<main class="container">
    <aside class="file-list">
        <h4>Files</h4>
        <ul>
            <?php foreach($files as $f): ?>
                <li><a href="?project=<?=$project_id?>&file=<?=urlencode($f)?>"><?=htmlspecialchars($f)?></a></li>
            <?php endforeach; ?>
        </ul>
        <form method="post" action="save_file.php">
            <input type="hidden" name="project_id" value="<?=$project_id?>" />
            <input type="text" name="filename" placeholder="newfile.html" required />
            <button class="btn" type="submit">Create</button>
        </form>
    </aside>

    <section class="code-editor">
        <form id="editForm" method="post" action="save_file.php">
            <input type="hidden" name="project_id" value="<?=$project_id?>" />
            <input type="hidden" name="filename" value="<?=htmlspecialchars($currentFile)?>" />
            <h3>Editing: <?=htmlspecialchars($currentFile)?></h3>
            <textarea id="editor" name="content" spellcheck="false"><?=htmlspecialchars($content)?></textarea>
            <div>
                <button class="btn" type="submit">Save</button>
                <a class="btn" href="preview.php?project=<?=$project_id?>" target="_blank">Open Preview</a>
            </div>
        </form>
    </section>
</main>

<script>
/* === User Panel Toggle === */
function toggleUserPanel() {
    const panel = document.getElementById('userPanel');
    panel.classList.toggle('active');
}

// Close panel when clicking outside
document.addEventListener('click', function(e) {
    const panel = document.getElementById('userPanel');
    const avatar = document.querySelector('.avatar');
    if (!panel.contains(e.target) && !avatar.contains(e.target)) {
        panel.classList.remove('active');
    }
});

/* === Code Editor Auto-Close and Indent === */
const editor = document.getElementById('editor');

editor.addEventListener('keydown', function(e) {
    const start = editor.selectionStart;
    const end = editor.selectionEnd;
    const val = editor.value;

    // Auto-close brackets and quotes
    const pairs = { '{': '}', '[': ']', '(': ')', '"': '"', "'": "'" };
    if (pairs[e.key] && !e.ctrlKey && !e.metaKey && !e.altKey) {
        e.preventDefault();
        editor.value = val.slice(0, start) + e.key + pairs[e.key] + val.slice(end);
        editor.selectionStart = editor.selectionEnd = start + 1;
        return;
    }

    // Auto-close HTML tags on '>'
    if (e.key === '>' && !e.ctrlKey && !e.metaKey && !e.altKey) {
        const openTagMatch = val.slice(0, start).match(/<([a-zA-Z][a-zA-Z0-9\-]*)[^>]*$/);
        if (openTagMatch) {
            e.preventDefault();
            const tagName = openTagMatch[1];
            const selfClosing = ['br','hr','img','input','meta','link'];

            if (selfClosing.includes(tagName.toLowerCase())) {
                // Self-closing: just add '>' if missing
                if (val[start] !== '>') {
                    editor.value = val.slice(0, start) + '>' + val.slice(end);
                    editor.selectionStart = editor.selectionEnd = start + 1;
                } else {
                    editor.selectionStart = editor.selectionEnd = start + 1;
                }
            } else {
                // Normal tag: add closing tag automatically
                editor.value = val.slice(0, start) + '>' + `</${tagName}>` + val.slice(end);
                editor.selectionStart = editor.selectionEnd = start + 1;
            }
            return;
        }
    }

    // Auto-indent on Enter
    if (e.key === 'Enter') {
        e.preventDefault();
        const lines = val.slice(0, start).split('\n');
        const currentLine = lines[lines.length - 1];
        const indentMatch = currentLine.match(/^\s*/);
        const indent = indentMatch ? indentMatch[0] : '';
        editor.value = val.slice(0, start) + '\n' + indent + val.slice(end);
        editor.selectionStart = editor.selectionEnd = start + 1 + indent.length;
    }
});
</script>


</body>
</html>
