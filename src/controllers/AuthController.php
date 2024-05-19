<?php
require_once '../config/database.php';

class AuthController {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function login($username, $password) {
        try{
            $stmt = $this->pdo->prepare("SELECT * FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            

            if ($user && password_verify($password, $user['password'])) {
                if ($user['status'] === 'locked') {
                    $_SESSION['error'] = 'Your account is locked. Please contact the administrator.';
                    return false;
                }

                if ($user['status'] !== 'active') {
                    $_SESSION['error'] = 'Your account is not yet verified. Please verify your account to log in.';
                    return false;
                }


                session_start();
                session_regenerate_id();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role']; 
                $_SESSION['status'] = $user['status'];
                $_SESSION['must_change_password'] = $user['must_change_password'];

                return true;
            } else {
                $_SESSION['error'] = 'Invalid username or password.';
                return false;
            }
        } catch (PDOException $e) {
            error_log("Login failed: " . $e->getMessage());
            $_SESSION['error'] = 'Login failed. Please try again later.';
            return false;
        }
    }
        

    public function logout() {
        session_start();
        session_unset();
        session_destroy();
    }
}
?>
