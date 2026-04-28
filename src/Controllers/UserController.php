<?php
namespace App\Controllers;

use App\Repositories\UserRepository;

class UserController {
    private $userRepo;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login");
            exit;
        }
        $this->userRepo = new UserRepository();
    }

    public function profile() {
        $user = $this->userRepo->findByEmail($_SESSION['user_email']);
        require_once __DIR__ . "/../../views/user/profile.php";
    }

    public function updatePassword() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['password_confirm'] ?? '';
            $userId = $_SESSION['user_id'];

            if ($password === $confirm && strlen($password) >= 6) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $userRepo = new UserRepository();
                $userRepo->updatePassword($userId, $hashedPassword);

                header("Location: /QR-code generator/public/profile?success=1");
                exit;
            } else {
                die("Паролі не збігаються або занадто короткі!");
            }
        }
    }
}