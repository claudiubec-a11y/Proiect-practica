<?php

/**
 * ============================================================================
 * models/Message.php
 * ----------------------------------------------------------------------------
 * Acces la date pentru `conversations` si `messages` (mesagerie interna).
 * ============================================================================
 */

declare(strict_types=1);

final class Message
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findConversation(int $buyerId, int $sellerId, ?int $listingId): ?array
    {
        $sql = 'SELECT * FROM conversations
                WHERE buyer_id = :buyer_id AND seller_id = :seller_id
                  AND (listing_id = :listing_id OR (:listing_id IS NULL AND listing_id IS NULL))
                LIMIT 1';
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['buyer_id' => $buyerId, 'seller_id' => $sellerId, 'listing_id' => $listingId]);
        $conversation = $stmt->fetch();
        return $conversation ?: null;
    }

    public function findConversationById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM conversations WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $conversation = $stmt->fetch();
        return $conversation ?: null;
    }

    public function createConversation(int $buyerId, int $sellerId, ?int $listingId): int
    {
        $stmt = $this->db->prepare('INSERT INTO conversations (buyer_id, seller_id, listing_id)
                                     VALUES (:buyer_id, :seller_id, :listing_id)');
        $stmt->execute(['buyer_id' => $buyerId, 'seller_id' => $sellerId, 'listing_id' => $listingId]);
        return (int) $this->db->lastInsertId();
    }

    public function findOrCreateConversation(int $buyerId, int $sellerId, ?int $listingId): int
    {
        $existing = $this->findConversation($buyerId, $sellerId, $listingId);
        if ($existing) {
            return (int) $existing['id'];
        }
        return $this->createConversation($buyerId, $sellerId, $listingId);
    }

    public function listForUser(int $userId): array
    {
        $sql = "SELECT c.*,
                       CASE WHEN c.buyer_id = :user_id THEN c.seller_id ELSE c.buyer_id END AS other_user_id,
                       u.first_name AS other_first_name, u.last_name AS other_last_name,
                       l.title AS listing_title,
                       (SELECT message FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) AS last_message,
                       (SELECT created_at FROM messages WHERE conversation_id = c.id ORDER BY created_at DESC LIMIT 1) AS last_message_at,
                       (SELECT COUNT(*) FROM messages WHERE conversation_id = c.id AND is_read = 0 AND sender_id != :user_id) AS unread_count
                FROM conversations c
                JOIN users u ON u.id = CASE WHEN c.buyer_id = :user_id THEN c.seller_id ELSE c.buyer_id END
                LEFT JOIN listings l ON l.id = c.listing_id
                WHERE c.buyer_id = :user_id OR c.seller_id = :user_id
                ORDER BY last_message_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public function isParticipant(int $conversationId, int $userId): bool
    {
        $stmt = $this->db->prepare('SELECT 1 FROM conversations WHERE id = :id AND (buyer_id = :user_id OR seller_id = :user_id)');
        $stmt->execute(['id' => $conversationId, 'user_id' => $userId]);
        return (bool) $stmt->fetchColumn();
    }

    public function getMessages(int $conversationId): array
    {
        $stmt = $this->db->prepare('SELECT * FROM messages WHERE conversation_id = :id ORDER BY created_at ASC');
        $stmt->execute(['id' => $conversationId]);
        return $stmt->fetchAll();
    }

    public function addMessage(int $conversationId, int $senderId, string $text): int
    {
        $stmt = $this->db->prepare('INSERT INTO messages (conversation_id, sender_id, message)
                                     VALUES (:conversation_id, :sender_id, :message)');
        $stmt->execute(['conversation_id' => $conversationId, 'sender_id' => $senderId, 'message' => $text]);
        return (int) $this->db->lastInsertId();
    }

    public function markAsRead(int $conversationId, int $userId): bool
    {
        $stmt = $this->db->prepare('UPDATE messages SET is_read = 1
                                     WHERE conversation_id = :id AND sender_id != :user_id');
        return $stmt->execute(['id' => $conversationId, 'user_id' => $userId]);
    }
}
