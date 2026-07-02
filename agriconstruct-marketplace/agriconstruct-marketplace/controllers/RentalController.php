<?php

/**
 * ============================================================================
 * controllers/RentalController.php
 * ----------------------------------------------------------------------------
 * Rute:
 *   POST /rentals
 *   GET  /rentals
 * ============================================================================
 */

declare(strict_types=1);

final class RentalController extends BaseController
{
    private Rental $rentalModel;
    private Listing $listingModel;

    public function __construct()
    {
        $this->rentalModel = new Rental();
        $this->listingModel = new Listing();
    }

    public function index(): void
    {
        $user = AuthMiddleware::handle();
        Response::success([
            'ca_chirias' => $this->rentalModel->findByRenter($user['id']),
            'pentru_anunturile_mele' => $this->rentalModel->findByOwner($user['id']),
        ]);
    }

    public function store(): void
    {
        $user = AuthMiddleware::handle();
        $data = $this->input();

        $validator = new Validator($data);
        $validator->required('listing_id', 'anunt')->numeric('listing_id')
            ->required('start_date', 'data inceput')->date('start_date')
            ->required('end_date', 'data sfarsit')->date('end_date');
        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }

        $listingId = (int) $data['listing_id'];
        $listing = $this->listingModel->findById($listingId);

        if (!$listing) {
            Response::notFound('Anuntul nu a fost gasit.');
        }
        if ($listing['offer_type'] !== 'rental' || empty($listing['rental_price_per_day'])) {
            Response::error('Acest anunt nu este disponibil pentru inchiriere.', 400);
        }
        if ((int) $listing['user_id'] === $user['id']) {
            Response::error('Nu poti inchiria propriul anunt.', 400);
        }
        if ($data['end_date'] < $data['start_date']) {
            Response::validationError(['end_date' => ['Data de sfarsit trebuie sa fie dupa data de inceput.']]);
        }

        if (!$this->rentalModel->isAvailable($listingId, $data['start_date'], $data['end_date'])) {
            Response::error('Utilajul nu este disponibil in perioada selectata.', 409);
        }

        $rentalId = $this->rentalModel->create(
            $listingId,
            $user['id'],
            $data['start_date'],
            $data['end_date'],
            (float) $listing['rental_price_per_day']
        );

        $this->listingModel->updateStatus($listingId, 'reserved');

        $rental = $this->rentalModel->findById($rentalId);
        Response::success($rental, 'Rezervare creata cu succes.', 201);
    }
}
