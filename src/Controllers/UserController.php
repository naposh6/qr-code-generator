<?php
namespace App\Controllers;

use App\Repositories\UserRepository;
use App\Repositories\QrRepository;
use App\Services\FileService;
use App\Services\QrDeletionService;

class UserController
{
    private $userRepo;
    private $qrRepo;
    private $qrDeletionService;

    public function __construct()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_DIR . '/login');
            exit;
        }
        $this->userRepo          = new UserRepository();
        $this->qrRepo            = new QrRepository();
        $this->qrDeletionService = new QrDeletionService();
    }

    public function profile()
    {
        $userId    = $_SESSION['user_id'];
        $userEmail = $_SESSION['user_email'];

        $user    = $this->userRepo->findByEmail($userEmail);
        $userQrs = $this->qrRepo->getByUserId($userId, 20);

        require_once __DIR__ . '/../../views/user/profile.php';
    }

    public function deleteMyQrs()
    {
        header('Content-Type: application/json');

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!isset($data['ids']) || !is_array($data['ids'])) {
            echo json_encode(['success' => false, 'message' => 'Невірні дані']);
            return;
        }

        $currentUserId = (int)$_SESSION['user_id'];
        $allowedIds    = [];

        foreach ($data['ids'] as $id) {
            $qr = $this->qrRepo->getById((int)$id);
            if ($qr && (int)$qr['user_id'] === $currentUserId) {
                $allowedIds[] = (int)$id;
                $this->qrDeletionService->deleteQrFiles($qr);
            }
        }

        if (empty($allowedIds)) {
            echo json_encode(['success' => false, 'message' => 'Немає доступу до цих записів']);
            return;
        }

        $result = $this->qrRepo->deleteMultiple($allowedIds);
        echo json_encode(['success' => $result]);
    }

    public function updateNickname()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nickname = trim($_POST['nickname'] ?? '');
            $userId   = $_SESSION['user_id'];
            $this->userRepo->updateNickname($userId, $nickname);
            $_SESSION['user_nickname'] = $nickname;
            header('Location: ' . BASE_DIR . '/profile?success=1');
            exit;
        }
    }

    public function updatePassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password']         ?? '';
            $confirm  = $_POST['password_confirm'] ?? '';
            $userId   = $_SESSION['user_id'];

            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                || str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json');

            if ($password === $confirm && strlen($password) >= 6) {
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $this->userRepo->updatePassword($userId, $hashed);

                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true]);
                    exit;
                }
                header('Location: ' . BASE_DIR . '/profile?success=1');
                exit;
            }

            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Паролі не співпадають або занадто короткі (мін. 6 символів)']);
                exit;
            }
            header('Location: ' . BASE_DIR . '/profile?error=invalid_password');
            exit;
        }
    }

    public function updateAvatar()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
            try {
                $fileService = new FileService();
                $userId      = $_SESSION['user_id'];
                $userEmail   = $_SESSION['user_email'];
                $user        = $this->userRepo->findByEmail($userEmail);

                $newPath = $fileService->upload($_FILES['avatar'], 'avatar');

                if (!empty($user['avatar_path'])) {
                    $fileService->deleteFile($user['avatar_path']);
                }

                $this->userRepo->updateAvatar($userId, $newPath);
                header('Location: ' . BASE_DIR . '/profile?success=1');
                exit;
            } catch (\Exception $e) {
                header('Location: ' . BASE_DIR . '/profile?error=' . urlencode($e->getMessage()));
                exit;
            }
        }
    }

    public function getHistoryAjax()
    {
        $page   = max(1, (int)($_GET['page']   ?? 1));
        $search = $_GET['search'] ?? null;
        $limit  = 5;
        $offset = ($page - 1) * $limit;

        $userId = $_SESSION['user_id'];
        $qrs    = $this->qrRepo->getByUserIdWithSearch($userId, $limit, $offset, $search ?: null);

        header('Content-Type: application/json');
        echo json_encode($qrs);
        exit;
    }
}
