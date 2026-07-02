<?php

/**
 * ============================================================================
 * controllers/ListingController.php
 * ----------------------------------------------------------------------------
 * CRUD complet pentru anunturi + cautare/filtrare/sortare/paginare.
 *
 * Rute:
 *   GET    /listings         (public - doar anunturi aprobate)
 *   GET    /listings/{id}    (public)
 *   GET    /listings/mine    (AuthMiddleware)
 *   POST   /listings         (AuthMiddleware)
 *   PUT    /listings/{id}    (AuthMiddleware + verificare proprietar)
 *   DELETE /listings/{id}    (AuthMiddleware + verificare proprietar sau admin)
 *
 * Imaginile incarcate sunt salvate in public/uploads/images/listings/,
 * folderul PUBLIC al proiectului (accesibil direct din browser), pentru
 * a putea fi afisate ulterior de frontend fara a trece prin PHP.
 * ============================================================================
 */

declare(strict_types=1);

final class ListingController extends BaseController
{
    private Listing $listingModel;
    private Category $categoryModel;

    private const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];
    private const MAX_IMAGE_SIZE = 5 * 1024 * 1024; // 5 MB, aliniat cu frontend-ul (add-listing.js)

    public function __construct()
    {
        $this->listingModel = new Listing();
        $this->categoryModel = new Category();
    }

    public function index(): void
    {
        $filters = $this->query();

        if (isset($filters['stare']) && !is_array($filters['stare'])) {
            $filters['stare'] = [$filters['stare']];
        }
        if (isset($filters['tip_oferta']) && !is_array($filters['tip_oferta'])) {
            $filters['tip_oferta'] = [$filters['tip_oferta']];
        }

        $result = $this->listingModel->search($filters);
        Response::success($result);
    }

    public function show(int $id): void
    {
        $listing = $this->listingModel->findById($id);
        if (!$listing) {
            Response::notFound('Anuntul nu a fost gasit.');
        }
        Response::success($listing);
    }

    public function store(): void
    {
        $user = AuthMiddleware::handle();
        $data = $this->input();

        $this->validateListingData($data);

        $machineryType = $this->categoryModel->findMachineryTypeById((int) $data['machinery_type_id']);
        if (!$machineryType) {
            Response::validationError(['machinery_type_id' => ['Tipul de utilaj selectat nu exista.']]);
        }

        $listingId = $this->listingModel->create($user['id'], $data);

        if (!empty($_FILES['images'])) {
            $this->handleImageUploads($listingId, $_FILES['images']);
        }

        $listing = $this->listingModel->findById($listingId);
        Response::success($listing, 'Anunt creat cu succes. Va fi vizibil dupa aprobare.', 201);
    }

    public function update(int $id): void
    {
        $user = AuthMiddleware::handle();
        $ownerId = $this->listingModel->ownerId($id);

        if ($ownerId === null) {
            Response::notFound('Anuntul nu a fost gasit.');
        }
        if ($ownerId !== $user['id'] && $user['role'] !== 'admin') {
            Response::forbidden('Nu ai dreptul sa editezi acest anunt.');
        }

        $data = $this->input();
        $this->validateListingData($data);

        $this->listingModel->update($id, $data);

        if (!empty($_FILES['images'])) {
            $this->handleImageUploads($id, $_FILES['images']);
        }

        Response::success($this->listingModel->findById($id), 'Anunt actualizat cu succes.');
    }

    public function destroy(int $id): void
    {
        $user = AuthMiddleware::handle();
        $ownerId = $this->listingModel->ownerId($id);

        if ($ownerId === null) {
            Response::notFound('Anuntul nu a fost gasit.');
        }
        if ($ownerId !== $user['id'] && $user['role'] !== 'admin') {
            Response::forbidden('Nu ai dreptul sa stergi acest anunt.');
        }

        $this->listingModel->delete($id);
        Response::success(null, 'Anunt sters cu succes.');
    }

    public function mine(): void
    {
        $user = AuthMiddleware::handle();
        Response::success($this->listingModel->findByUser($user['id']));
    }

    private function validateListingData(array $data): void
    {
        $validator = new Validator($data);
        $validator->required('title', 'titlu')
            ->required('machinery_type_id', 'tip utilaj')->numeric('machinery_type_id')
            ->required('manufacturing_year', 'an fabricatie')->numeric('manufacturing_year')
            ->required('offer_type', 'tip oferta')->in('offer_type', ['sale', 'rental'])
            ->required('city', 'oras')
            ->required('county', 'judet')
            ->in('condition', ['new', 'used']);

        if (($data['offer_type'] ?? null) === 'sale') {
            $validator->required('sale_price', 'pret de vanzare')->numeric('sale_price');
        }
        if (($data['offer_type'] ?? null) === 'rental') {
            $validator->required('rental_price_per_day', 'tarif de inchiriere pe zi')->numeric('rental_price_per_day');
        }

        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }
    }

    /**
     * Proceseaza imaginile trimise prin multipart/form-data (campul "images[]"),
     * le valideaza (tip MIME, dimensiune) si le muta in public/uploads/images/listings/.
     */
    private function handleImageUploads(int $listingId, array $filesField): void
    {
        $uploadDir = ROOT_PATH . '/public/uploads/images/listings/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $count = is_array($filesField['name']) ? count($filesField['name']) : 0;
        for ($i = 0; $i < $count; $i++) {
            if ($filesField['error'][$i] !== UPLOAD_ERR_OK) {
                continue;
            }
            if (!in_array($filesField['type'][$i], self::ALLOWED_IMAGE_TYPES, true)) {
                continue;
            }
            if ($filesField['size'][$i] > self::MAX_IMAGE_SIZE) {
                continue;
            }

            $extension = pathinfo($filesField['name'][$i], PATHINFO_EXTENSION);
            $safeName = $listingId . '_' . bin2hex(random_bytes(8)) . '.' . preg_replace('/[^a-z0-9]/i', '', $extension);
            $destination = $uploadDir . $safeName;

            if (move_uploaded_file($filesField['tmp_name'][$i], $destination)) {
                // Cale relativa la radacina proiectului, utilizabila direct in <img src="...">
                $relativePath = 'public/uploads/images/listings/' . $safeName;
                $this->listingModel->addImage($listingId, $relativePath, $i === 0, $i);
            }
        }
    }
}
