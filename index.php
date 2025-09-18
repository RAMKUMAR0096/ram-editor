<?php
require 'config.php';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Ram Editor - Build Space</title>
<style>
/* 🌌 Premium Variables */
:root {
    --primary: #2563eb;
    --secondary: #0ea5e9;
    --accent: #facc15;
    --bg: #0f172a;
    --surface: rgba(255,255,255,0.08);
    --text-light: #f8fafc;
    --text-muted: #94a3b8;
    --radius: 18px;
    --transition: all 0.4s ease-in-out;
}

/* Reset */
* { margin:0; padding:0; box-sizing:border-box; }
body {
    font-family: "Poppins", sans-serif;
    background: radial-gradient(circle at top left, #1e293b, #0f172a);
    color: var(--text-light);
    line-height:1.6;
    overflow-x:hidden;
    opacity:0;
    animation: fadeInPage 1s ease forwards;
}

/* Page fade-in */
@keyframes fadeInPage {
    from { opacity:0; transform: translateY(20px);}
    to { opacity:1; transform: translateY(0);}
}

/* 🔹 Topbar */
header.topbar {
    display:flex; justify-content:space-between; align-items:center;
    padding:18px 40px;
    background: rgba(255,255,255,0.05);
    backdrop-filter: blur(12px);
    border-radius: var(--radius);
    box-shadow: 0 8px 30px rgba(0,0,0,0.4);
    position: sticky;
    top:0; z-index:100;
    transition: var(--transition);
}
.topbar .brand {
    font-size: 2rem;
    font-weight: 700;
    background: linear-gradient(45deg, var(--primary), var(--secondary));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    text-shadow: 0 0 12px var(--secondary); /* use --secondary instead of --color-secondary */
}

.topbar .actions {
    display:flex; align-items:center; gap:12px;
}
.topbar .actions a {
    color: var(--text-light);
    padding:10px 20px;
    border-radius: var(--radius);
    background: linear-gradient(135deg,var(--primary),var(--secondary));
    text-decoration:none; font-weight:500;
    transition: var(--transition);
}
.topbar .actions a:hover {
    filter: brightness(1.2);
    transform: scale(1.05);
    box-shadow: 0 6px 20px rgba(14,165,233,0.5);
}

/* 🔹 Hero Section */
main.hero {
    display:flex; align-items:center; justify-content:space-between;
    padding:80px 100px; min-height:calc(100vh - 90px); gap:60px;
}
.hero-left { flex:1; animation: slideInLeft 1.2s ease; }
.hero-left h1 {
    font-size:56px; font-weight:700; line-height:1.2; margin-bottom:25px;
    background: linear-gradient(135deg,var(--secondary),var(--accent));
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
}
.hero-left h1 span { color: var(--accent); }
.hero-left p {
    font-size:20px; margin-bottom:35px; color: var(--text-muted);
}
.hero-left .btn {
    background: linear-gradient(135deg,var(--primary),var(--secondary));
    color:#fff;
    padding:16px 32px; font-size:17px; font-weight:600;
    border-radius: var(--radius);
    text-decoration:none;
    transition: var(--transition);
    display:inline-block;
    box-shadow: 0 10px 25px rgba(14,165,233,0.4);
}
.hero-left .btn:hover {
    transform: translateY(-3px) scale(1.07);
    filter: brightness(1.15);
    box-shadow: 0 15px 35px rgba(14,165,233,0.6);
}

/* 🔹 Preview Card */
.hero-right { flex:1; display:flex; justify-content:center; animation: slideInRight 1.2s ease; }
.editor-preview {
    background: var(--surface);
    backdrop-filter: blur(18px);
    border-radius: 24px;
    overflow:hidden;
    width:100%; max-width:540px;
    border:1px solid rgba(255,255,255,0.15);
    box-shadow: 0 12px 40px rgba(0,0,0,0.6);
    transition: var(--transition);
    transform-style: preserve-3d;
}
.editor-preview:hover {
    transform: translateY(-10px) rotateX(5deg) rotateY(-3deg) scale(1.02);
    box-shadow: 0 20px 60px rgba(0,0,0,0.7);
}
.editor-preview iframe {
    width:100%; height:360px; border:0; display:block;
    border-radius:20px;
}

/* 🔹 Animations */
@keyframes slideInLeft {
    from { opacity:0; transform: translateX(-50px);}
    to { opacity:1; transform: translateX(0);}
}
@keyframes slideInRight {
    from { opacity:0; transform: translateX(50px);}
    to { opacity:1; transform: translateX(0);}
}

/* 🔹 Responsive */
@media(max-width:1000px){
    main.hero { flex-direction:column; padding:60px 30px; text-align:center; gap:40px; }
    .hero-left h1 { font-size:42px; }
    .hero-left p { font-size:18px; }
}
@media(max-width:600px){
    .hero-left h1 { font-size:34px; }
    .hero-left .btn { padding:12px 20px; font-size:15px; }
}
</style>
</head>
<body>

<header class="topbar">
  <a href='index.php' style="text-decoration:none;"><div class="brand">Ram Editor</div></a>
  <div class="actions">
    <?php if(is_logged_in()): ?>
      <a href="dashboard.php">Dashboard</a>
      <a href="logout.php">Logout</a>
    <?php else: ?>
      <a href="login.php">Login</a>
      <a href="register.php">Register</a>
    <?php endif; ?>
  </div>
</header>

<main class="hero">
  <div class="hero-left">
    <h1>Build Space<br><span>Your Ideas.</span></h1>
    <p>Create projects effortlessly with our intuitive editor.</p>
    <?php if(!is_logged_in()): ?>
      <a class="btn" href="register.php">Create Account</a>
    <?php else: ?>
      <a class="btn" href="dashboard.php">Open Dashboard</a>
    <?php endif; ?>
  </div>

  <div class="hero-right">
    <div class="editor-preview">
      <iframe src="editor.php?demo=1"></iframe>
    </div>
  </div>
</main>

</body>
</html>
