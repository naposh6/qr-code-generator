<?php
namespace App\Controllers;

use App\Repositories\UserRepository;

class AuthController {
    private $userRepo;

    public function __construct() {
        $this->userRepo = new UserRepository();
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if ($this->userRepo->findByEmail($email)) {
                return "З цим email вже зареєстрованно користувача.";
            }

            if ($this->userRepo->register($email, $password)) {
                $baseDir = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
                header('Location: ' . $baseDir . '/login');
                exit;
            }
        }

        $baseDir = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);

        require_once __DIR__ . '/../../views/register.php';
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $user = $this->userRepo->findByEmail($email);

            if ($user && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_nickname'] = $user['nickname'] ?? '';

                $baseDir = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);
                header('Location: ' . $baseDir . '/');
                exit;
            }
            return "Невірний email або пароль!";
        }

        $baseDir = str_replace('/index.php', '', $_SERVER['SCRIPT_NAME']);

        require_once __DIR__ . '/../../views/login.php';
    }

    public function logout() {
        session_destroy();
        header('Location: login');
        exit;
    }
}