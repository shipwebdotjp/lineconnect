<?php

namespace Shipweb\LineConnect\Interaction;

/**
 * Holds the definition of a single interaction step.
 */
class StepDefinition {
    private string $id;
    private array $data;

    public function __construct(array $step_data) {
        $this->id = $step_data['id'];
        $this->data = $step_data;
    }

    public function get_id(): string {
        return $this->id;
    }

    // title
    public function get_title(): string {
        return $this->data['title'] ?? '';
    }

    public function get_description(): string {
        return $this->data['description'] ?? '';
    }

    public function get_messages(): array {
        return $this->data['messages'] ?? [];
    }

    public function get_branches(): array {
        return $this->data['branches'] ?? [];
    }

    public function get_next_step_id(): ?string {
        return $this->data['nextStepId'] ?? null;
    }

    public function is_stop_step(): bool {
        return $this->data['stop'] ?? false;
    }

    public function get_special_type(): ?string {
        return $this->data['special'] ?? null;
    }

    public function get_normalize_rules(): array {
        return (array)($this->data['normalize'] ?? []);
    }

    public function get_validation_rules(): array {
        return (array)($this->data['validate'] ?? []);
    }

    public function get_before_actions(): object {
        return (object)($this->data['beforeActions'] ?? []);
    }

    public function get_after_actions(): object {
        return (object)($this->data['afterActions'] ?? []);
    }
}
