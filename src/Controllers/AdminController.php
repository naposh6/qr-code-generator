<?php
namespace App\Controllers;

use App\Repositories\QrRepository;
use App\Repositories\UserRepository;

class AdminController {
    private $qrRepo;
    private $userRepo;

    public function __construct() {
        if (($_SESSION["role"] ?? '') !== 'admin') {
            header("Location: /QR-code generator/public/");
            exit;
        }
        $this->qrRepo = new QrRepository();
        $this->userRepo = new UserRepository();
    }

    public function dashboard() {
        $allUsers = $this->userRepo->getAllUsers();
        $allQrs = $this->qrRepo->getAll(100);

        $stats = [
            'total_users' => count($allUsers),
            'total_qrs' => count($this->qrRepo->getAll(9999)),
            'latest_user' => end($allUsers)['email'] ?? 'none'
        ];

        require_once __DIR__ . '/../../views/admin/dashboard.php';
    }

    public function updateRole() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'];
            $newRole = $_POST['role'];
            // Тут виклик методу в UserRepository
            $this->userRepo->updateRole($userId, $newRole);
        }
        header("Location: /QR-code generator/public/admin");
    }

    public function getUsersAjax() {
        $allUsers = $this->userRepo->getAllUsers();
        require_once __DIR__ . '/../../views/admin/_users_table.php';
    }

    public function getQrsAjax() {
        $allQrs = $this->qrRepo->getAll(100);
        require_once __DIR__ . '/../../views/admin/_qr_table.php';
    }

    public function deleteUser() {
        $id = $_GET['id'] ?? null;
        if ($id && is_numeric($id)) {
            if ((int)$id !== (int)$_SESSION['user_id']) {
                $this->userRepo->delete((int)$id);
            }
        }
        header("Location: /QR-code generator/public/admin");
        exit;
    }
}