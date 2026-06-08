<?php
namespace App\Controllers;

use App\Repositories\UserRepository;

class AuthController
{
    private $userRepo;

    public function __construct()
    {
        $this->userRepo = new UserRepository();
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email']    ?? '');
            $password = $_POST['password'] ?? '';

            if ($this->userRepo->findByEmail($email)) {
                $error = "З цим email вже зареєстровано користувача.";
                $baseDir = BASE_DIR;
                require_once __DIR__ . '/../../views/register.php';
                return;
            }

            if ($this->userRepo->register($email, $password)) {
                header('Location: ' . BASE_DIR . '/login');
                exit;
            }
        }

        $baseDir = BASE_DIR;
        require_once __DIR__ . '/../../views/register.php';
    }

    public function login()
    {
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email']    ?? '');
            $password = $_POST['password'] ?? '';

            $user = $this->userRepo->findByEmail($email);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id']       = $user['id'];
                $_SESSION['role']          = $user['role'];
                $_SESSION['user_email']    = $user['email'];
                $_SESSION['user_nickname'] = $user['nickname'] ?? '';

                header('Location: ' . BASE_DIR . '/');
                exit;
            }

            $error = "Невірний email або пароль!";
        }

        $baseDir = BASE_DIR;
        require_once __DIR__ . '/../../views/login.php';
    }

    public function logout()
    {
        session_destroy();
        header('Location: ' . BASE_DIR . '/login');
        exit;
    }
}
