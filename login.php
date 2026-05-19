<?php
require_once 'connect.php';

$message = "";

if (isset($_POST['btnLogin'])) {
    $email = $_POST['txtemail'];
    $pwd   = $_POST['txtpassword'];

    $sql  = "SELECT * FROM users WHERE institutionalEmail = ?";
    $stmt = $connection->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $count  = $result->num_rows;

    if ($count == 0) {
        $message = "No account found with that email address.";
    } else {
        $row             = $result->fetch_assoc();
        $stored_password = $row['password'];

        if (!password_verify($pwd, $stored_password)) {
            $message = "Incorrect password. Please try again.";
        } else {
            $_SESSION['userID']   = $row['user_id'];
            $_SESSION['email']    = $row['institutionalEmail'];
            $_SESSION['is_admin'] = (int)$row['is_admin'];

            if ((int)$row['is_admin'] == 1) {
                header("Location: admin-dashboard.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        }
    }
}
?>

<?php require_once 'header_footer/pre-header.php'; ?>

<style>
  @import url('https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=DM+Sans:wght@300;400;500;600&display=swap');

  :root {
    --ink:       #1c1c1c;
    --ink-soft:  #5a5a5a;
    --accent:    #8b0000;
    --accent-h:  #a80000;
    --surface:   #f5f3f0;
    --card:      #ffffff;
    --border:    #e0deda;
    --error-bg:  #fff0f0;
    --error-bd:  #f5bcbc;
    --error-tx:  #8b0000;
    --radius:    14px;
    --shadow:    0 8px 40px rgba(0,0,0,.09), 0 2px 8px rgba(0,0,0,.05);
  }

  /* Centering wrapper — doesn't touch body so navbar is unaffected */
  .auth-page {
    font-family: 'DM Sans', sans-serif;
    min-height: calc(100vh - 230px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 48px 20px;
    background: var(--surface);
    position: relative;
    overflow: hidden;
  }
  .auth-page::before {
    content: '';
    position: absolute; top: -100px; right: -80px;
    width: 420px; height: 420px; border-radius: 50%;
    background: radial-gradient(circle, rgba(139,0,0,.07) 0%, transparent 70%);
    pointer-events: none;
  }
  .auth-page::after {
    content: '';
    position: absolute; bottom: -60px; left: -50px;
    width: 320px; height: 320px; border-radius: 50%;
    background: radial-gradient(circle, rgba(139,0,0,.05) 0%, transparent 70%);
    pointer-events: none;
  }

  .auth-wrapper {
    position: relative; z-index: 1;
    width: 100%; max-width: 420px;
    animation: fadeUp .45s ease both;
  }
  @keyframes fadeUp {
    from { opacity: 0; transform: translateY(16px); }
    to   { opacity: 1; transform: translateY(0); }
  }

  .auth-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    padding: 40px 38px 36px;
  }

  .auth-header { margin-bottom: 28px; }
  .auth-header .eyebrow {
    display: block;
    font-size: 10.5px; font-weight: 600; letter-spacing: .13em;
    text-transform: uppercase; color: var(--accent);
    margin-bottom: 6px;
  }
  .auth-header h2 {
    font-family: 'Instrument Serif', serif;
    font-size: 1.9rem; color: var(--ink); line-height: 1.15;
  }
  .auth-header h2 em { font-style: italic; color: var(--accent); }

  .auth-error {
    display: flex; align-items: flex-start; gap: 10px;
    background: var(--error-bg);
    border: 1px solid var(--error-bd);
    border-radius: 8px;
    padding: 11px 14px;
    margin-bottom: 20px;
    animation: shake .32s ease;
  }
  @keyframes shake {
    0%,100%{ transform: translateX(0); }
    25%    { transform: translateX(-5px); }
    75%    { transform: translateX(5px); }
  }
  .auth-error svg { flex-shrink: 0; margin-top: 1px; width: 17px; height: 17px; color: var(--error-tx); }
  .auth-error span { font-size: .84rem; color: var(--error-tx); line-height: 1.45; }

  .field { margin-bottom: 18px; }
  .field label {
    display: block; font-size: .75rem; font-weight: 600;
    color: var(--ink-soft); letter-spacing: .05em;
    text-transform: uppercase; margin-bottom: 7px;
  }
  .field input {
    width: 100%;
    padding: 11px 14px;
    font-family: 'DM Sans', sans-serif;
    font-size: .93rem; color: var(--ink);
    background: #fafaf9;
    border: 1.5px solid var(--border);
    border-radius: 8px;
    outline: none;
    transition: border-color .2s, box-shadow .2s, background .2s;
  }
  .field input::placeholder { color: #bbb; }
  .field input:focus {
    border-color: var(--accent);
    box-shadow: 0 0 0 3px rgba(139,0,0,.10);
    background: #fff;
  }

  .auth-alt {
    font-size: .83rem; color: var(--ink-soft);
    margin-bottom: 22px; display: block;
  }
  .auth-alt a {
    color: var(--accent); font-weight: 600; text-decoration: none;
    border-bottom: 1.5px solid transparent; transition: border-color .2s;
  }
  .auth-alt a:hover { border-color: var(--accent); }

  .btn-auth {
    width: 100%; padding: 13px;
    font-family: 'DM Sans', sans-serif;
    font-size: .95rem; font-weight: 600; letter-spacing: .02em;
    color: #fff;
    background: var(--accent);
    border: none; border-radius: 8px; cursor: pointer;
    position: relative; overflow: hidden;
    transition: background .2s, transform .15s, box-shadow .15s;
  }
  .btn-auth::after {
    content: '';
    position: absolute; inset: 0;
    background: linear-gradient(120deg, transparent 30%, rgba(255,255,255,.15) 50%, transparent 70%);
    transform: translateX(-100%);
    transition: transform .4s ease;
  }
  .btn-auth:hover {
    background: var(--accent-h);
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(139,0,0,.28);
  }
  .btn-auth:hover::after { transform: translateX(100%); }
  .btn-auth:active { transform: translateY(0); }
</style>

<div class="auth-page">
  <div class="auth-wrapper">
    <div class="auth-card">

      <div class="auth-header">
        <span class="eyebrow">Welcome back</span>
        <h2>Sign in to <em>continue</em></h2>
      </div>

      <?php if (!empty($message)): ?>
      <div class="auth-error" role="alert">
        <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
          <circle cx="10" cy="10" r="8.5"/>
          <line x1="10" y1="6.5" x2="10" y2="10.5"/>
          <circle cx="10" cy="13.5" r=".5" fill="currentColor" stroke="none"/>
        </svg>
        <span><?php echo htmlspecialchars($message); ?></span>
      </div>
      <?php endif; ?>

      <form method="POST">
        <div class="field">
          <label for="txtemail">Institutional Email</label>
          <input type="email" id="txtemail" name="txtemail"
                 placeholder="you@school.edu.ph" required
                 value="<?php echo isset($_POST['txtemail']) ? htmlspecialchars($_POST['txtemail']) : ''; ?>">
        </div>

        <div class="field">
          <label for="txtpassword">Password</label>
          <input type="password" id="txtpassword" name="txtpassword"
                 placeholder="••••••••" required>
        </div>

        <span class="auth-alt">Don't have an account? <a href="register.php">Register</a></span>

        <button type="submit" name="btnLogin" class="btn-auth">Sign In</button>
      </form>

    </div>
  </div>
</div>

<?php require_once 'header_footer/footer.php'; ?>