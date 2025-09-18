<?php
require 'config.php';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (!$name || !$email || !$password) {
        $msg = "All fields are required.";
    } elseif ($password !== $confirm) {
        $msg = "Passwords do not match.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $msg = "Email already registered.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name,email,password) VALUES (?,?,?)");
            $stmt->execute([$name, $email, $hash]);
            header('Location: login.php?registered=1');
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Register - Ram Editor</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
/* =============================
   Same Futuristic UI as login
   ============================= */
:root{
  --bg-1: #071029;
  --bg-2: #0f2a4d;
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
  --transition: 320ms cubic-bezier(.2,.9,.2,1);
  --glass-border: rgba(255,255,255,0.08);
  --max-width: 460px;
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
  display:flex;
  align-items:center;
  justify-content:center;
  min-height:100vh;
  padding:28px;
  animation: pageFade 700ms var(--transition) both;
}

@keyframes pageFade{
  from{opacity:0; transform: translateY(18px) scale(.995)}
  to  {opacity:1; transform: translateY(0) scale(1)}
}

main.auth{
  width:100%;
  max-width:var(--max-width);
  background: linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01)), var(--glass);
  border-radius:var(--radius-lg);
  padding:40px;
  box-shadow: var(--shadow-xl);
  border: 1px solid var(--glass-border);
  backdrop-filter: blur(14px) saturate(120%);
  text-align:center;
  position:relative;
  transition: transform var(--transition), box-shadow var(--transition);
}
main.auth:hover{ transform: translateY(-6px); box-shadow: 0 28px 80px rgba(2,6,23,0.72); }

main.auth h2{
  font-size:26px;
  font-weight:700;
  margin-bottom:20px;
  background: linear-gradient(90deg,var(--accent-2),var(--accent-1));
  -webkit-background-clip:text;
  -webkit-text-fill-color:transparent;
  line-height:1.4;
}

form{ width:100%; }

label{
  display:block;
  margin-bottom:14px;
  text-align:left;
  color:var(--muted);
  font-weight:600;
  font-size:13px;
  position:relative;
}

input{
  width:100%;
  padding:14px 16px;
  margin-top:6px;
  border-radius:var(--radius-md);
  border:1px solid rgba(255,255,255,0.06);
  background: var(--glass-2);
  color:var(--text);
  font-size:15px;
  transition: var(--transition);
  outline:none;
}
input:focus{
  border-color: var(--accent-1);
  box-shadow: 0 0 12px rgba(58,160,255,0.4);
  transform: translateY(-2px);
}

.btn{
  margin-top:12px;
  width:100%;
  padding:14px 16px;
  border-radius:14px;
  border:none;
  cursor:pointer;
  font-weight:700;
  font-size:15px;
  color:#041227;
  background: linear-gradient(90deg, var(--accent-3), var(--accent-2));
  box-shadow: 0 10px 30px rgba(250,199,87,0.16);
  transition: var(--transition);
}
.btn:hover{
  transform: translateY(-3px) scale(1.02);
  box-shadow: 0 18px 52px rgba(250,199,87,0.22);
}

.error{
  background: rgba(255,107,107,0.1);
  border-left: 4px solid var(--danger);
  color: var(--danger);
  padding:12px;
  border-radius:10px;
  margin-bottom:16px;
  font-weight:600;
  text-align:left;
  animation: errorPop 0.4s ease;
}
@keyframes errorPop{
  0%{ transform: translateY(-6px) scale(.98); opacity:0 }
  60%{ transform: translateY(2px) scale(1.02); opacity:1 }
  100%{ transform: translateY(0) scale(1); opacity:1 }
}

p{
  margin-top:18px;
  color:var(--muted);
  font-size:14px;
}
p a{
  color:var(--accent-2);
  font-weight:700;
  text-decoration:none;
  transition: var(--transition);
}
p a:hover{
  color:var(--accent-1);
  text-shadow: 0 0 6px var(--accent-1);
}

@media (max-width:520px){
  main.auth{ padding:26px; border-radius:16px }
  main.auth h2{ font-size:22px }
  label{ font-size:12px }
  input{ padding:12px; font-size:14px }
  .btn{ padding:12px; font-size:15px }
}
</style>
</head>
<body>
<main class="auth">
    <h2>Create Account</h2>
    <?php if($msg): ?><div class="error"><?=htmlspecialchars($msg)?></div><?php endif; ?>
    <form method="post">
        <label>Name
            <input type="text" name="name" required>
        </label>
        <label>Email
            <input type="email" name="email" required>
        </label>
        <label>Password
            <input type="password" name="password" required>
        </label>
        <label>Confirm Password
            <input type="password" name="confirm" required>
        </label>
        <button class="btn" type="submit">Create Account</button>
    </form>
    <p>Already have an account? <a href="login.php">Login</a></p>
</main>
</body>
</html>
