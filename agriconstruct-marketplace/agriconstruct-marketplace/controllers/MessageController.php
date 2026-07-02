<?php

/**
 * ============================================================================
 * controllers/MessageController.php
 * ----------------------------------------------------------------------------
 * Rute:
 *   GET  /messages
 *   GET  /messages/{conversationId}
 *   POST /messages
 * ============================================================================
 */

declare(strict_types=1);

final class MessageController extends BaseController
{
    private Message $messageModel;
    private Listing $listingModel;

    public function __construct()
    {
        $this->messageModel = new Message();
        $this->listingModel = new Listing();
    }

    public function index(): void
    {
        $user = AuthMiddleware::handle();
        Response::success($this->messageModel->listForUser($user['id']));
    }

    public function show(int $conversationId): void
    {
        $user = AuthMiddleware::handle();

        if (!$this->messageModel->isParticipant($conversationId, $user['id'])) {
            Response::forbidden('Nu ai acces la aceasta conversatie.');
        }

        $this->messageModel->markAsRead($conversationId, $user['id']);
        Response::success($this->messageModel->getMessages($conversationId));
    }

    public function store(): void
    {
        $user = AuthMiddleware::handle();
        $data = $this->input();

        $validator = new Validator($data);
        $validator->required('message', 'mesaj');
        if ($validator->fails()) {
            Response::validationError($validator->errors());
        }

        $conversationId = isset($data['conversation_id']) ? (int) $data['conversation_id'] : null;

        if ($conversationId === null) {
            if (empty($data['listing_id'])) {
                Response::validationError(['listing_id' => ['Este necesar listing_id sau conversation_id.']]);
            }
            $listing = $this->listingModel->findById((int) $data['listing_id']);
            if (!$listing) {
                Response::notFound('Anuntul nu a fost gasit.');
            }
            $sellerId = (int) $listing['user_id'];
            if ($sellerId === $user['id']) {
                Response::error('Nu poti incepe o conversatie cu tine insuti.', 400);
            }
            $conversationId = $this->messageModel->findOrCreateConversation($user['id'], $sellerId, (int) $listing['id']);
        } elseif (!$this->messageModel->isParticipant($conversationId, $user['id'])) {
            Response::forbidden('Nu ai acces la aceasta conversatie.');
        }

        $this->messageModel->addMessage($conversationId, $user['id'], trim($data['message']));

        Response::success([
            'conversation_id' => $conversationId,
            'messages' => $this->messageModel->getMessages($conversationId),
        ], 'Mesaj trimis.', 201);
    }
}
