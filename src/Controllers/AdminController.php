<?php
namespace App\Controllers;

use App\Repositories\QrRepository;
use App\Repositories\UserRepository;
use App\Services\QrDeletionService;

class AdminController
{
    private $qrRepo;
    private $userRepo;
    private $qrDeletionService;

    public function __construct()
    {
        $this->qrRepo            = new QrRepository();
        $this->userRepo          = new UserRepository();
        $this->qrDeletionService = new QrDeletionService();
    }

    public function dashboard()
    {
        $this->checkAdmin();

        $allUsers = $this->userRepo->getAllUsers();
        $allQrs   = $this->qrRepo->getAll(100);

        $stats = [
            'total_users' => count($allUsers),
            'total_qrs'   => count($this->qrRepo->getAll(9999)),
            'latest_user' => end($allUsers)['email'] ?? 'none',
        ];

        require_once __DIR__ . '/../../views/admin/dashboard.php';
    }

    public function updateRole()
    {
        $this->checkAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId  = (int)($_POST['user_id'] ?? 0);
            $newRole = $_POST['role'] ?? 'user';
            $this->userRepo->updateRole($userId, $newRole);
        }

        header('Location: ' . BASE_DIR . '/admin');
        exit;
    }

    public function getUsersAjax()
    {
        $this->checkAdmin();
        $allUsers = $this->userRepo->getAllUsers();
        require_once __DIR__ . '/../../views/admin/_users_table.php';
    }

    public function getQrsAjax()
    {
        $this->checkAdmin();
        $allQrs = $this->qrRepo->getAll(100);
        require_once __DIR__ . '/../../views/admin/_qr_table.php';
    }

    public function deleteUser()
    {
        $this->checkAdmin();
        $id = $_GET['id'] ?? null;

        if ($id && is_numeric($id) && (int)$id !== (int)$_SESSION['user_id']) {
            $this->userRepo->delete((int)$id);
        }

        header('Location: ' . BASE_DIR . '/admin');
        exit;
    }

    public function deleteQrs()
    {
        $this->checkAdmin();

        header('Content-Type: application/json');

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!isset($data['ids']) || !is_array($data['ids'])) {
            echo json_encode(['success' => false, 'message' => 'Невірні дані']);
            return;
        }

        $allowedIds = [];
        foreach ($data['ids'] as $id) {
            $qr = $this->qrRepo->getById((int)$id);
            if ($qr) {
                $allowedIds[] = (int)$id;
                $this->qrDeletionService->deleteQrFiles($qr);
            }
        }

        if (empty($allowedIds)) {
            echo json_encode(['success' => false, 'message' => 'Немає прав на видалення']);
            return;
        }

        $result = $this->qrRepo->deleteMultiple($allowedIds);
        echo json_encode(['success' => $result]);
    }

    private function checkAdmin(): void
    {
        if (($_SESSION['role'] ?? '') !== 'admin') {
            header('Location: ' . BASE_DIR . '/');
            exit;
        }
    }
}
