<?php
require 'config.php';
require_login();

$user_id = $_SESSION['user_id'];

// Handle delete request
if(isset($_POST['delete_project_id'])){
    $delete_id = intval($_POST['delete_project_id']);
    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ? AND user_id = ?");
    $stmt->execute([$delete_id, $user_id]);
    header("Location: dashboard.php");
    exit;
}

// Fetch projects
$stmt = $pdo->prepare("SELECT * FROM projects WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard - Ram Editor</title>
<style>
:root {
    --color-bg: #0c0e21;
    --color-glass: rgba(255,255,255,0.05);
    --color-border: rgba(255,255,255,0.12);
    --color-text: #e6eaf2;
    --color-accent: #1fb6ff;
    --color-accent-left: linear-gradient(135deg,#1fb6ff,#009ffd);
    --color-radius: 16px;
    --shadow-light: 0 6px 20px rgba(31,182,255,0.2);
    --shadow-dark: 0 8px 30px rgba(0,0,0,0.6);
    --transition: all 0.35s cubic-bezier(0.25,0.8,0.25,1);
    --font-main: "SF Pro Display","Segoe UI",Roboto,sans-serif;
}

/* Base */
*{margin:0; padding:0; box-sizing:border-box;}
body {
    font-family: var(--font-main);
    background: var(--color-bg);
    color: var(--color-text);
    display:grid;
    grid-template-columns:280px 1fr;
    height:100vh;
    overflow:hidden;
}

/* Scrollbars */
::-webkit-scrollbar {width:6px;}
::-webkit-scrollbar-track {background: rgba(255,255,255,0.05); border-radius:10px;}
::-webkit-scrollbar-thumb {background: rgba(31,182,255,0.5); border-radius:10px; transition:0.3s;}
::-webkit-scrollbar-thumb:hover {background: rgba(31,182,255,0.8);}

/* Sidebar */
aside.sidebar {
    background: rgba(12,20,40,0.75);
    backdrop-filter: blur(20px);
    display:flex;
    flex-direction:column;
    border-right:1px solid var(--color-border);
    box-shadow: var(--shadow-dark);
}

/* Sidebar fixed top (logo + create project) */
.fixed-top {
    padding:20px;
    z-index:10;
}

/* Scrollable project list */
.project-list {
    flex:1;
    overflow-y:auto;
    padding:0 15px 20px 15px;
    margin:0;
}
/* Thin scrollbar */
.project-list::-webkit-scrollbar {width:5px;}
.project-list::-webkit-scrollbar-track {background:transparent;}
.project-list::-webkit-scrollbar-thumb {background:rgba(31,182,255,0.5); border-radius:3px;}

/* Sidebar elements */
.sidebar .logo {
    font-size:2rem; font-weight:700; color: var(--color-accent);
    text-shadow:0 0 12px var(--color-accent); margin-bottom:15px;
}
.sidebar h4 {margin:15px 0 10px; font-weight:600; text-transform:uppercase; color: var(--color-accent); font-size:0.85rem;}
.sidebar li {margin-bottom:10px;}
.sidebar .menu-item {
    display:block; padding:8px 12px; border-radius: var(--color-radius);
    color: var(--color-text); text-decoration:none; transition: var(--transition);
}
.sidebar .menu-item:hover {background: rgba(31,182,255,0.08);}
.sidebar .menu-item.active {background: rgba(31,182,255,0.15); font-weight:600;}
.sidebar .btn {
    background: var(--color-accent-left);
    color:#fff; padding:12px; border-radius: var(--color-radius);
    text-align:center; font-weight:600; text-decoration:none; display:block; margin-bottom:10px;
    transition: var(--transition); box-shadow: var(--shadow-dark);
}
.sidebar .btn:hover {transform: translateY(-2px) scale(1.02);}

/* Main Content */
main.main-content {
    display: flex;
    flex-direction: column;
    overflow: visible; /* allows content to expand naturally */
    padding: 20px 30px;
    height: auto; /* let it take the height it needs */
}



/* Topbar sticky */
.topbar {
    display:flex; justify-content:space-between; align-items:center;
    padding-bottom:15px; border-bottom:1px solid var(--color-border);
    position:sticky; top:0; z-index:10; background: var(--color-bg);
}
.topbar h1 {font-size:1.5rem; font-weight:600;}
.topbar .user-info {display:flex; align-items:center; gap:15px; position:relative;}
.topbar .avatar {
    width:40px; height:40px; border-radius:50%; background: var(--color-accent-left);
    display:flex; align-items:center; justify-content:center;
    font-weight:700; color:#fff; box-shadow: var(--shadow-light);
    cursor:pointer; transition: var(--transition);
}
.topbar .avatar:hover {transform: scale(1.1); box-shadow:0 0 15px var(--color-accent);}

/* User Panel */
.user-panel {
    position:absolute; top:50px; right:0; background: rgba(0,0,0,0.85);
    border-radius: var(--color-radius); padding:12px 20px;
    display:none; flex-direction: column; min-width:140px;
    box-shadow: var(--shadow-dark); backdrop-filter: blur(12px);
    z-index:20;
}
.user-panel.show {display:flex;}
.user-panel button {
    background:none; border:none; color: var(--color-text);
    text-align:left; padding:8px 0; cursor:pointer; border-radius: var(--color-radius);
    transition: var(--transition);
}
.user-panel button:hover {background: rgba(31,182,255,0.1);}

/* Project Grid */
.project-grid {
    display:grid; 
    grid-template-columns: repeat(auto-fit, minmax(280px,1fr)); 
    gap:20px;
    overflow-y:auto;
    min-height:auto;
    max-height: calc(100vh - 80px);
    padding-right:10px;
}
.project-card {
    background: var(--color-glass); border-radius: var(--color-radius);
    padding:15px; box-shadow: var(--shadow-dark); border:1px solid var(--color-border);
    backdrop-filter: blur(12px) saturate(180%); transition: var(--transition);
    display:flex; flex-direction:column;
}
.project-card:hover {transform: translateY(-3px) scale(1.02); box-shadow:0 10px 25px rgba(31,182,255,0.4);}
.project-preview {display:flex; align-items:center; gap:10px; padding:8px; border-radius: var(--color-radius);}
.project-preview .color-left {width:6px; height:40px; border-radius:4px; background: var(--color-accent-left);}
.project-preview .project-title {flex:1; font-weight:600; font-size:1rem; color: var(--color-text);}
.project-preview .delete-btn {background:none; border:none; color:#ff4d4d; cursor:pointer; font-size:1rem; transition: var(--transition);}
.project-preview .delete-btn:hover {color:#ff1a1a; transform: scale(1.2);}
.project-card iframe {width:100%; height:180px; border-radius: var(--color-radius); border:none; margin-top:10px; background: rgba(31,182,255,0.05); box-shadow: var(--shadow-light); transition: var(--transition);}
.project-card iframe:hover {filter: brightness(1.05) drop-shadow(0 0 10px var(--color-accent));}

/* Responsive */
@media(max-width:900px){
    body {grid-template-columns:1fr;}
    aside.sidebar {border-right:none; border-bottom:1px solid var(--color-border);}
    main.main-content {padding:15px;}
}
@media(max-width:600px){
    .topbar {flex-direction:column; gap:10px;}
    .sidebar .btn {padding:10px; font-size:0.95rem;}
}
</style>
</head>
<body>

<aside class="sidebar">
    <div class="fixed-top">
        <a href='index.php' style="text-decoration:none;"><div class="logo">Ram Editor</div></a>
        <a href="create_project.php" class="btn">+ Create Project</a>
        <h4>Dashboard</h4>
        <a href="dashboard.php" class="menu-item active">Dashboard</a>
        <h4>Recent Projects</h4>
    </div>
    <ul class="project-list" style="  list-style-type: none;">
    <style>
        .project-list {
            max-height: 400px; /* Adjust this value as needed */
            overflow-y: auto;
            padding-right: 15px; /* Adds space for the scrollbar so content doesn't touch the edge */
            -ms-overflow-style: none;  /* Hides scrollbar on IE and Edge */
            scrollbar-width: 0.1px;  /* Hides scrollbar on Firefox */
        }
    </style>
    <?php foreach($projects as $p): ?>
        <li>
            <form method="post" onsubmit="return confirm('Delete this project?');" style="display:flex; align-items:center; gap:10px;">
                <input type="hidden" name="delete_project_id" value="<?= $p['id'] ?>">
                <button class="delete-btn" type="submit">&#10005;</button>
                <a href="editor.php?project=<?=urlencode($p['id'])?>" class="menu-item"><?=htmlspecialchars($p['title'])?></a>
            </form>
        </li>
    <?php endforeach; ?>
</ul>
</aside>

<main class="main-content">
    <div class="topbar">
        <h1>Hi, Welcome <?=htmlspecialchars($_SESSION['user_name'])?></h1>
        <div class="user-info">
            <div class="avatar" onclick="toggleUserPanel()"><?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?></div>
            <div class="user-panel" id="userPanel">
                <span style="padding:8px 0; font-weight:bold;"><?=htmlspecialchars($_SESSION['user_name'])?></span>
                <form method="post" action="logout.php">
                    <button type="submit">Logout</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="project-grid" style="margin-top:20px;">
    <?php foreach($projects as $p): ?>
    <div class="project-card" id="project-<?= $p['id'] ?>">
        <a href="editor.php?project=<?=urlencode($p['id'])?>" class="menu-item">
            <div class="project-preview">
                <div class="color-left"></div>
                <div class="project-title"><?=htmlspecialchars($p['title'])?></div>
            </div>
            <iframe src="preview.php?project=<?=urlencode($p['id'])?>"></iframe>
        </a>
    </div>
    <?php endforeach; ?>
</div>
</main>

<script>
function toggleUserPanel(){
    document.getElementById('userPanel').classList.toggle('show');
}
</script>

</body>
</html>
