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
            $newPassword = $_POST['password'] ?? '';
            echo "Пароль успішно оновлено";
        }
    }
}