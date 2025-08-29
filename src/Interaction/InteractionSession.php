<?php

namespace Shipweb\LineConnect\Interaction;

use DateTime;
use DateTimeZone;
use Shipweb\LineConnect\Core\LineConnect;

/**
 * Represents a single interaction session, corresponding to a row in the database.
 */
class InteractionSession {
    private ?int $id = null;
    private string $channel_prefix;
    private string $line_user_id;
    private int $interaction_id;
    private int $interaction_version;
    private string $status;
    private ?string $current_step_id = null;
    private ?string $previous_step_id = null;
    private array $answers = [];
    private ?DateTime $remind_at = null;
    private ?DateTime $reminder_sent_at = null;
    private ?DateTime $expires_at = null;
    private DateTime $created_at;
    private DateTime $updated_at;
    private int $timeout_minutes = 0;
    private int $timeout_remind_minutes = 0;

    private function __construct() {
    }

    /**
     * Start a new interaction session.
     */
    public static function start(InteractionDefinition $interaction, string $line_user_id, string $channel_prefix): self {
        $session = new self();
        $session->channel_prefix = $channel_prefix;
        $session->line_user_id = $line_user_id;
        $session->interaction_id = $interaction->get_id();
        $session->interaction_version = $interaction->get_version();
        $session->status = 'active';
        $session->created_at = new DateTime('now', new DateTimeZone('UTC'));
        $session->updated_at = new DateTime('now', new DateTimeZone('UTC'));
        $session->timeout_minutes = $interaction->get_timeout_minutes();
        $session->timeout_remind_minutes = $interaction->get_timeout_remind_minutes();

        if ($session->timeout_minutes > 0) {
            $session->expires_at = (new DateTime('now', new DateTimeZone('UTC')))->modify("+{$session->timeout_minutes} minutes");
            if ($session->timeout_remind_minutes > 0) {
                $session->remind_at = $session->expires_at->modify("-{$session->timeout_remind_minutes} minutes");
            }
        }

        return $session;
    }

    /**
     * Create an instance from a database row.
     */
    public static function from_db_row(object $row): self {
        $session = new self();
        $session->id = (int)$row->id;
        $session->channel_prefix = $row->channel_prefix;
        $session->line_user_id = $row->line_user_id;
        $session->interaction_id = (int)$row->interaction_id;
        $session->interaction_version = (int)$row->interaction_version;
        $session->status = $row->status;
        $session->current_step_id = $row->current_step_id;
        $session->previous_step_id = $row->previous_step_id;
        $session->answers = json_decode($row->answers, true) ?? [];
        $session->remind_at = $row->remind_at ? new DateTime($row->remind_at, new DateTimeZone('UTC')) : null;
        $session->reminder_sent_at = $row->reminder_sent_at ? new DateTime($row->reminder_sent_at, new DateTimeZone('UTC')) : null;
        $session->expires_at = $row->expires_at ? new DateTime($row->expires_at, new DateTimeZone('UTC')) : null;
        $session->created_at = new DateTime($row->created_at, new DateTimeZone('UTC'));
        $session->updated_at = new DateTime($row->updated_at, new DateTimeZone('UTC'));

        return apply_filters(LineConnect::FILTER_PREFIX . 'interaction_session_load', $session);
    }

    /**
     * Converts the session object to an associative array for database insertion/update.
     */
    public function to_db_array(): array {
        return [
            'id' => $this->id,
            'channel_prefix' => $this->channel_prefix,
            'line_user_id' => $this->line_user_id,
            'interaction_id' => $this->interaction_id,
            'interaction_version' => $this->interaction_version,
            'status' => $this->status,
            'current_step_id' => $this->current_step_id,
            'previous_step_id' => $this->previous_step_id,
            'answers' => json_encode($this->answers),
            'remind_at' => $this->remind_at ? $this->remind_at->format('Y-m-d H:i:s') : null,
            'reminder_sent_at' => $this->reminder_sent_at ? $this->reminder_sent_at->format('Y-m-d H:i:s') : null,
            'expires_at' => $this->expires_at ? $this->expires_at->format('Y-m-d H:i:s') : null,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }

    public function get_id(): ?int {
        return $this->id;
    }

    public function get_channel_prefix(): string {
        return $this->channel_prefix;
    }

    public function get_line_user_id(): string {
        return $this->line_user_id;
    }

    public function get_interaction_id(): int {
        return $this->interaction_id;
    }

    public function get_interaction_version(): int {
        return $this->interaction_version;
    }

    public function get_status(): string {
        return $this->status;
    }

    public function set_status(string $status): void {
        $this->status = $status;
        $this->touch();
    }

    public function get_current_step_id(): ?string {
        return $this->current_step_id;
    }

    public function set_current_step_id(?string $step_id): void {
        $this->previous_step_id = $this->current_step_id;
        $this->current_step_id = $step_id;
        $this->touch();
    }

    public function get_previous_step_id(): ?string {
        return $this->previous_step_id;
    }

    public function get_answer(string $key): mixed {
        return $this->answers[$key] ?? null;
    }

    public function set_answer(string $key, mixed $value): void {
        $this->answers[$key] = $value;
        $this->touch();
    }

    /**
     * Clear all collected answers on the session.
     */
    public function clear_answers(): void {
        $this->answers = [];
        $this->touch();
    }

    /**
     * Reset the session to a given step ID and clear answers.
     *
     * @param string|null $step_id The step id to set as current (or null to unset)
     */
    public function reset_to_step(?string $step_id): void {
        $this->previous_step_id = $this->current_step_id;
        $this->current_step_id = $step_id;
        $this->answers = [];
        $this->touch();
    }

    /**
     * Mark the session as completed.
     */
    public function complete(): void {
        $this->set_status('completed');
        // TODO: finalize answers
    }

    /**
     * Mark the session as paused.
     */
    public function pause(): void {
        $this->set_status('paused');
    }

    public function get_answers(): array {
        return $this->answers;
    }

    public function unset_answers(array $keys): void {
        foreach ($keys as $key) {
            unset($this->answers[$key]);
        }
        $this->touch();
    }

    public function get_excluded_answers($exclude_steps): array {
        $answers = $this->get_answers();
        $excluded_answers = [];
        foreach ($answers as $key => $value) {
            if (!in_array($key, $exclude_steps, true)) {
                $excluded_answers[$key] = $value;
            }
        }
        return $excluded_answers;
    }

    public function get_expires_at(): ?DateTime {
        return $this->expires_at;
    }

    public function set_reminder_sent_at(?DateTime $reminder_sent_at): void {
        $this->reminder_sent_at = $reminder_sent_at;
    }

    public function get_remind_at(): ?DateTime {
        return $this->remind_at;
    }

    /**
     * Update the 'updated_at' timestamp.
     */
    private function touch(): void {
        $this->updated_at = new DateTime('now', new DateTimeZone('UTC'));
        // extend expiration if timeout is configured
        if ($this->expires_at) {
            $this->expires_at = (new DateTime('now', new DateTimeZone('UTC')))->modify("+{$this->timeout_minutes} minutes");
            if ($this->timeout_remind_minutes > 0) {
                $this->remind_at = (clone $this->expires_at)->modify("-{$this->timeout_remind_minutes} minutes");
            }
        }
    }
}
