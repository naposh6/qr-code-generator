<?php
namespace App\Controllers;

use App\Repositories\UserRepository;
use App\Repositories\QrRepository;

class UserController {
    private $userRepo;
    private $qrRepo;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: login");
            exit;
        }
        $this->userRepo = new UserRepository();
        $this->qrRepo = new QrRepository();
    }

    public function profile() {
        $userId = $_SESSION['user_id'];
        $userEmail = $_SESSION['user_email'];

        $user = $this->userRepo->findByEmail($userEmail);
        $userQrs = $this->qrRepo->getByUserId($userId, 20);

        require_once __DIR__ . "/../../views/user/profile.php";
    }

    public function deleteMyQrs() {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!isset($data['ids']) || !is_array($data['ids'])) {
            echo json_encode(['success' => false, 'message' => 'Невірні дані']);
            return;
        }

        $currentUserId = $_SESSION['user_id'];
        $allowedIds = [];

        foreach ($data['ids'] as $id) {
            $qr = $this->qrRepo->getById((int)$id);
            if ($qr && (int)$qr['user_id'] === (int)$currentUserId) {
                $allowedIds[] = (int)$id;

                if (!empty($qr['media_path'])) {
                    $fullPath = __DIR__ . '/../../public/' . $qr['media_path'];
                    if (file_exists($fullPath)) {
                        unlink($fullPath);
                    }
                }
            }
        }

        if (empty($allowedIds)) {
            echo json_encode(['success' => false, 'message' => 'Немає доступу до цих записів']);
            return;
        }

        $result = $this->qrRepo->deleteMultiple($allowedIds);
        header('Content-Type: application/json');
        echo json_encode(['success' => $result]);
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
                header("Location: /QR-code generator/public/profile?error=invalid_password");
                exit;
            }
        }
    }
}