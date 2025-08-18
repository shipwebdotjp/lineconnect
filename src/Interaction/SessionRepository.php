<?php

namespace Shipweb\LineConnect\Interaction;

use Shipweb\LineConnect\Core\LineConnect;
use Shipweb\LineConnect\Interaction\InteractionSession;

/**
 * Repository for handling the persistence of InteractionSession objects.
 */
class SessionRepository {
    private \wpdb $wpdb;
    private string $table_name;

    public function __construct() {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_name = $wpdb->prefix . LineConnect::TABLE_INTERACTION_SESSIONS;
    }

    /**
     * Find a session by its primary key.
     */
    public function find(int $id): ?InteractionSession {
        $row = $this->wpdb->get_row(
            $this->wpdb->prepare("SELECT * FROM {$this->table_name} WHERE id = %d", $id)
        );
        return $row ? InteractionSession::from_db_row($row) : null;
    }

    /**
     * Find an active session for a given user and channel.
     */
    public function find_active(string $channel_prefix, string $line_user_id): ?InteractionSession {
        $row = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE channel_prefix = %s AND line_user_id = %s AND status IN ('active', 'editing') ORDER BY updated_at DESC LIMIT 1",
                $channel_prefix,
                $line_user_id
            )
        );
        return $row ? InteractionSession::from_db_row($row) : null;
    }

    /**
     * Save a session to the database (insert or update).
     */
    public function save(InteractionSession &$session): bool {
        $data = $session->to_db_array();
        unset($data['id']); // ID is not part of the data to be inserted/updated.

        if ($session->get_id()) {
            // Update existing session
            $result = $this->wpdb->update(
                $this->table_name,
                $data,
                ['id' => $session->get_id()]
            );
        } else {
            // Insert new session
            $result = $this->wpdb->insert($this->table_name, $data);
            if ($result) {
                // Set the new ID back to the session object
                $session_id_property = new \ReflectionProperty(InteractionSession::class, 'id');
                $session_id_property->setAccessible(true);
                $session_id_property->setValue($session, $this->wpdb->insert_id);
            }
        }

        return $result !== false;
    }

    /**
     * Find paused sessions for a given user and channel.
     *
     * @return InteractionSession[] An array of paused sessions (may be empty).
     */
    public function find_paused(string $channel_prefix, string $line_user_id): array {
        $rows = $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE channel_prefix = %s AND line_user_id = %s AND status = 'paused' ORDER BY updated_at DESC",
                $channel_prefix,
                $line_user_id
            )
        );

        $sessions = [];
        if ($rows) {
            foreach ($rows as $row) {
                $sessions[] = InteractionSession::from_db_row($row);
            }
        }
        return $sessions;
    }

    /**
     * Find a paused session for the given user/channel that matches a specific interaction id.
     *
     * @return InteractionSession|null
     */
    public function find_paused_by_interaction(string $channel_prefix, string $line_user_id, int $interaction_id): ?InteractionSession {
        $row = $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->table_name} WHERE channel_prefix = %s AND line_user_id = %s AND status = 'paused' AND interaction_id = %d ORDER BY updated_at DESC LIMIT 1",
                $channel_prefix,
                $line_user_id,
                $interaction_id
            )
        );
        return $row ? InteractionSession::from_db_row($row) : null;
    }

    /**
     * Delete a session from the database.
     */
    public function delete(InteractionSession $session): bool {
        if (!$session->get_id()) {
            return false; // Cannot delete a session that doesn't exist in DB.
        }

        $result = $this->wpdb->delete(
            $this->table_name,
            ['id' => $session->get_id()],
            ['%d']
        );

        return $result !== false;
    }
}
