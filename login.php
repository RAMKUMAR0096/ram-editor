<?php
require 'config.php';
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id, password, name FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        header('Location: dashboard.php');
        exit;
    } else {
        $msg = "Invalid credentials.";
    }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Login - Ram Editor</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    /* =============================
       Futuristic UI — ONLY STYLE
       Variables, glassmorphism, micro-interactions,
       page & element animations, responsive
       ============================= */

    :root{
      --bg-1: #071029;            /* deep navy */
      --bg-2: #0f2a4d;            /* midnight blue */
      --glass: rgba(255,255,255,0.06);
      --glass-2: rgba(255,255,255,0.04);
      --muted: #9fb4d6;
      --text: #e6eef9;
      --accent-1: #3aa0ff;
      --accent-2: #6ad3ff;
      --accent-3: #ffd66b;
      --danger: #ff6b6b;
      --radius-lg: 20px;
      --radius-md: 12px;
      --shadow-xl: 0 20px 60px rgba(2,6,23,0.6);
      --shadow-sm: 0 8px 20px rgba(2,6,23,0.35);
      --transition: 320ms cubic-bezier(.2,.9,.2,1);
      --glass-border: rgba(255,255,255,0.08);
      --max-width: 440px;
      --font-sans: "Inter", "Poppins", system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
    }

    /* Reset */
    *{box-sizing:border-box;margin:0;padding:0}
    html,body{height:100%}

    body{
      font-family: var(--font-sans);
      background: radial-gradient(1200px 600px at 10% 10%, var(--bg-2) 0%, transparent 20%),
                  radial-gradient(1000px 500px at 95% 90%, rgba(58,160,255,0.06) 0%, transparent 18%),
                  linear-gradient(180deg,var(--bg-1), #071a36 120%);
      color:var(--text);
      -webkit-font-smoothing:antialiased;
      -moz-osx-font-smoothing:grayscale;
      display:flex;
      align-items:center;
      justify-content:center;
      min-height:100vh;
      padding:28px;
      overflow-y:auto;
      animation: pageFade 700ms var(--transition) both;
    }

    /* Page fade/slide */
    @keyframes pageFade{
      from{opacity:0; transform: translateY(18px) scale(.995)}
      to  {opacity:1; transform: translateY(0) scale(1)}
    }

    /* Auth container (glass + neumorphism mix) */
    main.auth{
      width:100%;
      max-width:var(--max-width);
      background:
        linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)),
        var(--glass);
      border-radius:var(--radius-lg);
      padding:40px;
      box-shadow: var(--shadow-xl);
      border: 1px solid var(--glass-border);
      backdrop-filter: blur(14px) saturate(120%);
      -webkit-backdrop-filter: blur(14px) saturate(120%);
      text-align:center;
      position:relative;
      overflow:hidden;
      transition: transform var(--transition), box-shadow var(--transition);
    }
    main.auth::before{
      content:'';
      position:absolute;
      inset: -40% -20%;
      background: radial-gradient(600px 200px at 10% 20%, rgba(58,163,255,0.06), transparent 15%),
                  radial-gradient(400px 140px at 90% 80%, rgba(250,198,88,0.04), transparent 12%);
      transform: rotate(6deg);
      pointer-events:none;
    }
    main.auth:hover{ transform: translateY(-6px); box-shadow: 0 28px 80px rgba(2,6,23,0.72); }

    /* Header / Title */
    main.auth h2{
      font-size:26px;
      line-height:2;
      font-weight:700;
      margin-bottom:18px;
      background: linear-gradient(90deg,var(--accent-2),var(--accent-1));
      -webkit-background-clip:text;
      -webkit-text-fill-color:transparent;
      letter-spacing: -0.2px;
    }

    /* Subtle subtitle (optional) */
    main.auth .sub{
      color:var(--muted);
      font-size:13px;
      margin-bottom:22px;
      opacity:0.95;
    }

    /* Form layout */
    form{
      width:100%;
      display:block;
    }

    /* Label wraps input — we can use :focus-within */
    label{
      display:block;
      margin-bottom:14px;
      text-align:left;
      color:var(--muted);
      font-weight:600;
      font-size:13px;
      position:relative;
    }

    /* Input styles */
    input{
      width:100%;
      display:block;
      padding:14px 16px;
      margin-top:8px;
      border-radius:var(--radius-md);
      border:1px solid rgba(255,255,255,0.06);
      background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
      color:var(--text);
      font-size:15px;
      transition: box-shadow var(--transition), border-color var(--transition), transform var(--transition);
      box-shadow: inset 0 1px 0 rgba(255,255,255,0.02), 0 6px 18px rgba(2,6,23,0.35);
      outline:none;
    }

    /* Floating label indicator using :focus-within on label */
    label::after{
      content: '';
      position:absolute;
      right:12px;
      top:12px;
      width:10px;
      height:10px;
      border-radius:50%;
      background: linear-gradient(180deg,var(--accent-1), var(--accent-2));
      box-shadow: 0 6px 18px rgba(58,160,255,0.18);
      opacity:0;
      transform: scale(.85);
      transition:opacity var(--transition), transform var(--transition);
      pointer-events:none;
    }
    label:focus-within::after{
      opacity:1;
      transform: scale(1);
    }

    /* Input focus micro interaction */
    input:focus{
      border-color: rgba(58,160,255,0.9);
      box-shadow: 0 8px 30px rgba(58,160,255,0.12), 0 2px 0 rgba(255,255,255,0.02) inset;
      transform: translateY(-2px);
    }

    /* Button — cinematic gradient + subtle 3D */
    .btn{
      margin-top:10px;
      width:100%;
      padding:14px 16px;
      border-radius:14px;
      border: none;
      cursor:pointer;
      font-weight:700;
      font-size:15px;
      color:#041227;
      background: linear-gradient(90deg, var(--accent-3) 0%, #ffd967 30%, var(--accent-2) 100%);
      box-shadow: 0 10px 30px rgba(250,199,87,0.16), 0 4px 8px rgba(2,6,23,0.25);
      transition: transform 250ms cubic-bezier(.2,.9,.2,1), box-shadow var(--transition), filter var(--transition);
      will-change: transform;
    }
    .btn:hover{
      transform: translateY(-4px) scale(1.01);
      filter:brightness(1.02);
      box-shadow: 0 18px 52px rgba(250,199,87,0.22);
    }
    .btn:active{
      transform: translateY(-1px) scale(.995);
      box-shadow: 0 8px 24px rgba(2,6,23,0.3);
    }

    /* Error card */
    .error{
      display:block;
      text-align:left;
      background: linear-gradient(180deg, rgba(255,107,107,0.06), rgba(255,107,107,0.03));
      border-left: 4px solid rgba(255,107,107,0.95);
      color: var(--danger);
      padding:12px 14px;
      border-radius:10px;
      margin-bottom:14px;
      font-weight:700;
      animation: errorPop 380ms cubic-bezier(.2,.9,.2,1);
    }
    @keyframes errorPop{
      0% { transform: translateY(-6px) scale(.98); opacity:0 }
      60% { transform: translateY(2px) scale(1.02); opacity:1 }
      100% { transform: translateY(0) scale(1); opacity:1 }
    }

    /* Footer link */
    p{
      margin-top:18px;
      color:var(--muted);
      font-size:14px;
    }
    p a{
      color:var(--accent-2);
      font-weight:700;
      text-decoration:none;
      transition: color var(--transition), text-shadow var(--transition);
    }
    p a:hover{
      color:var(--accent-1);
      text-shadow:0 6px 28px rgba(58,160,255,0.08);
    }

    /* Small helper to reduce input width issues when label wraps input text */
    label input{ width:100% }

    /* Responsive: mobile-first adjustments */
    @media (max-width:520px){
      main.auth{ padding:26px; border-radius:16px; width:100% }
      :root{ --max-width: 420px }
      main.auth h2{ font-size:20px }
      label{ font-size:12px }
      input{ padding:12px 12px; font-size:14px }
      .btn{ padding:12px 14px; font-size:15px; border-radius:12px }
    }

    /* Tablet / desktop tweaks */
    @media (min-width:900px){
      main.auth{ padding:48px; border-radius:24px }
      main.auth h2{ font-size:28px }
      .btn{ font-size:16px }
    }

    /* Accessibility: reduce motion preference */
    @media (prefers-reduced-motion: reduce){
      *{ transition: none !important; animation: none !important; transform: none !important; }
    }
  </style>
</head>
<body>
  <main class="auth">
    <h2>Login</h2>
    <?php if($msg): ?>
      <div class="error"><?=htmlspecialchars($msg)?></div>
    <?php endif; ?>
    <form method="post">
      <label>Email
        <input type="email" name="email" required>
      </label>
      <label>Password
        <input type="password" name="password" required>
      </label>
      <button class="btn" type="submit">Login</button>
    </form>
    <p>Don't have an account? <a href="register.php">Create here</a></p>
  </main>
</body>
</html>
