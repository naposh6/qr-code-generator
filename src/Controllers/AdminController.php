<?php
namespace App\Controllers;

use App\Repositories\QrRepository;
use App\Repositories\UserRepository;

class AdminController {
    private $qrRepo;
    private $userRepo;

    public function __construct() {
        $this->qrRepo = new QrRepository();
        $this->userRepo = new UserRepository();
    }

    public function dashboard() {
        $this->checkAdmin();
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
        $this->checkAdmin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_POST['user_id'];
            $newRole = $_POST['role'];
            $this->userRepo->updateRole($userId, $newRole);
        }
        header("Location: /QR-code generator/public/admin");
    }

    private function checkAdmin() {
        if (($_SESSION["role"] ?? '') !== 'admin') {
            header("Location: /QR-code generator/public/");
            exit;
        }
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

    public function deleteQrs() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!isset($data['ids']) || !is_array($data['ids'])) {
            echo json_encode(['success' => false, 'message' => 'Невірні дані']);
            return;
        }

        $currentUserId = $_SESSION['user_id'] ?? null;
        $currentUserRole = $_SESSION['role'] ?? '';

        $allowedIds = [];
        foreach ($data['ids'] as $id) {
            $qr = $this->qrRepo->getById((int)$id);

            if ($qr) {
                $allowedIds[] = (int)$id;

                if (!empty($qr['media_path'])) {
                    $fullPathQr = __DIR__ . '/../../public/' . $qr['media_path'];
                    if (file_exists($fullPathQr)) unlink($fullPathQr);
                }

                if (in_array($qr['qr_type'], ['image', 'video'])) {
                    $parts = explode('/public/', $qr['original_url']);
                    if (isset($parts[1])) {
                        $fullPathMedia = __DIR__ . '/../../public/' . $parts[1];
                        if (file_exists($fullPathMedia)) unlink($fullPathMedia);
                    }
                }
            }
        }

        if (empty($allowedIds)) {
            echo json_encode(['success' => false, 'message' => 'Немає прав на видалення цих об\'єктів']);
            return;
        }

        $result = $this->qrRepo->deleteMultiple($allowedIds);

        header('Content-Type: application/json');
        echo json_encode(['success' => $result]);
    }
}