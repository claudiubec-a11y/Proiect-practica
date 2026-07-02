<?php

/**
 * ============================================================================
 * controllers/AdminController.php
 * ----------------------------------------------------------------------------
 * Rute (toate necesita rol = admin):
 *   GET    /admin/dashboard
 *   GET    /admin/listings
 *   PUT    /admin/listings/{id}/approve
 *   PUT    /admin/listings/{id}/reject
 *   DELETE /admin/listings/{id}
 *   GET    /admin/users
 *   PUT    /admin/users/{id}/block
 *   PUT    /admin/users/{id}/unblock
 * ============================================================================
 */

declare(strict_types=1);

final class AdminController extends BaseController
{
    private User $userModel;
    private Listing $listingModel;
    private Rental $rentalModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->listingModel = new Listing();
        $this->rentalModel = new Rental();
    }

    public function dashboard(): void
    {
        AdminMiddleware::handle();

        Response::success([
            'utilizatori' => $this->userModel->countByRole('user'),
            'anunturi_active' => $this->listingModel->countAll(),
            'utilaje_agricole' => $this->listingModel->countByCategorySlug('agricole'),
            'utilaje_constructii' => $this->listingModel->countByCategorySlug('constructii'),
            'inchirieri' => $this->rentalModel->countActive(),
            'anunturi_in_asteptare' => $this->listingModel->countByApprovalStatus('pending'),
        ]);
    }

    public function listings(): void
    {
        AdminMiddleware::handle();
        $status = $_GET['status'] ?? null;
        Response::success($this->listingModel->allForAdmin($status));
    }

    public function approveListing(int $id): void
    {
        $admin = AdminMiddleware::handle();

        if ($this->listingModel->findById($id) === null) {
            Response::notFound('Anuntul nu a fost gasit.');
        }

        $this->listingModel->updateApprovalStatus($id, 'approved');
        $this->logAction($admin['id'], 'approve_listing', 'listing', $id);

        Response::success(null, 'Anunt aprobat.');
    }

    public function rejectListing(int $id): void
    {
        $admin = AdminMiddleware::handle();

        if ($this->listingModel->findById($id) === null) {
            Response::notFound('Anuntul nu a fost gasit.');
        }

        $this->listingModel->updateApprovalStatus($id, 'rejected');
        $this->logAction($admin['id'], 'reject_listing', 'listing', $id);

        Response::success(null, 'Anunt respins.');
    }

    public function deleteListing(int $id): void
    {
        $admin = AdminMiddleware::handle();

        if ($this->listingModel->findById($id) === null) {
            Response::notFound('Anuntul nu a fost gasit.');
        }

        $this->listingModel->delete($id);
        $this->logAction($admin['id'], 'delete_listing', 'listing', $id);

        Response::success(null, 'Anunt sters.');
    }

    public function users(): void
    {
        AdminMiddleware::handle();
        $page = (int) ($_GET['page'] ?? 1);
        $perPage = (int) ($_GET['per_page'] ?? 20);
        $search = $_GET['search'] ?? null;

        Response::success($this->userModel->paginate($page, $perPage, $search));
    }

    public function blockUser(int $id): void
    {
        $admin = AdminMiddleware::handle();
        $this->guardTargetUser($id, $admin['id']);

        $this->userModel->setStatus($id, 'blocked');
        $this->logAction($admin['id'], 'block_user', 'user', $id);

        Response::success(null, 'Utilizator blocat.');
    }

    public function unblockUser(int $id): void
    {
        $admin = AdminMiddleware::handle();
        $this->guardTargetUser($id, $admin['id']);

        $this->userModel->setStatus($id, 'active');
        $this->logAction($admin['id'], 'unblock_user', 'user', $id);

        Response::success(null, 'Utilizator deblocat.');
    }

    private function guardTargetUser(int $targetId, int $adminId): void
    {
        if ($targetId === $adminId) {
            Response::error('Nu iti poti modifica propriul cont din acest panou.', 400);
        }
        if ($this->userModel->findById($targetId) === null) {
            Response::notFound('Utilizatorul nu a fost gasit.');
        }
    }

    private function logAction(int $adminId, string $action, string $targetType, int $targetId): void
    {
        $db = Database::getConnection();
        $stmt = $db->prepare('INSERT INTO admin_logs (admin_id, action, target_type, target_id)
                               VALUES (:admin_id, :action, :target_type, :target_id)');
        $stmt->execute([
            'admin_id' => $adminId,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
        ]);
    }
}
