<?php

/**
 * ============================================================================
 * controllers/FavoriteController.php
 * ----------------------------------------------------------------------------
 * Rute:
 *   GET    /favorites
 *   POST   /favorites          body: { "listing_id": 2011 }
 *   DELETE /favorites/{id}     {id} = id-ul anuntului
 * ============================================================================
 */

declare(strict_types=1);

final class FavoriteController extends BaseController
{
    private Favorite $favoriteModel;
    private Listing $listingModel;

    public function __construct()
    {
        $this->favoriteModel = new Favorite();
        $this->listingModel = new Listing();
    }

    public function index(): void
    {
        $user = AuthMiddleware::handle();
        Response::success($this->favoriteModel->listForUser($user['id']));
    }

    public function store(): void
    {
        $user = AuthMiddleware::handle();
        $data = $this->input();

        $validator = new Validator($data);
        $validator->required('listing_id', 'anunt')->numeric('listing_id');
        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }

        $listingId = (int) $data['listing_id'];
        if ($this->listingModel->findById($listingId) === null) {
            Response::notFound('Anuntul nu a fost gasit.');
        }

        $this->favoriteModel->add($user['id'], $listingId);
        Response::success(null, 'Adaugat la favorite.', 201);
    }

    public function destroy(int $listingId): void
    {
        $user = AuthMiddleware::handle();
        $this->favoriteModel->remove($user['id'], $listingId);
        Response::success(null, 'Eliminat din favorite.');
    }
}
