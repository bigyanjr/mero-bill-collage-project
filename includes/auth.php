<?php
// includes/auth.php
// Authentication helper functions

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Log in a user securely
 * @param PDO $pdo Database connection
 * @param string $identifier User email or username
 * @param string $password Input password
 * @return bool|string True on success, error message string on failure
 */
function login_user($pdo, $identifier, $password) {
    if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    }
    
    $stmt->execute([$identifier]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        if (!$user['is_active']) {
            return "Account is inactive. Please contact admin.";
        }
        
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['business_name'] = $user['business_name'];
        $_SESSION['is_demo'] = ($user['role'] === 'demo');
        
        return true;
    }
    
    return "Invalid credentials.";
}

/**
 * Check if user is logged in
 * @return bool
 */
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

/**
 * Redirect to a specific URL
 * @param string $url
 */
function redirect($url) {
    header("Location: " . $url);
    exit;
}

/**
 * Require user to be logged in
 * Redirects to login page if not logged in
 */
function require_login() {
    if (!is_logged_in()) {
        redirect("/pos/public/login.php");
    }
}

/**
 * Require a specific role or redirect
 * @param string|array $roles Allowed role(s) (e.g., 'admin', or ['demo', 'merchant'])
 */
function require_role($roles) {
    require_login();

    $current_role = $_SESSION['role'] ?? '';
    
    if (is_array($roles)) {
        if (!in_array($current_role, $roles)) {
             // Redirect based on role if they are in the wrong place
            if ($current_role === 'admin') {
                redirect("/pos/admin/admin_dashboard.php");
            } else {
                redirect("/pos/public/dashboard.php");
            }
        }
    } else {
        if ($current_role !== $roles) {
             // Redirect based on role
            if ($current_role === 'admin') {
                redirect("/pos/admin/admin_dashboard.php");
            } else {
                redirect("/pos/public/dashboard.php");
            }
        }
    }
}

/**
 * Log out the user
 */
function logout_user() {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    redirect("/pos/public/login.php");
}

/**
 * Check if the user is a Demo user and if their plan is expired
 */
function is_demo_expired($pdo, $user_id) {
     $stmt = $pdo->prepare("SELECT plan_expires_at, role FROM users WHERE id = ?");
     $stmt->execute([$user_id]);
     $user = $stmt->fetch();
     
     if ($user && $user['role'] === 'demo') {
         if (strtotime($user['plan_expires_at']) < time()) {
             return true;
         }
     }
     return false;
}
?>
