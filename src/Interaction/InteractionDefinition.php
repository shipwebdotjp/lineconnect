<?php

namespace Shipweb\LineConnect\Interaction;

use Shipweb\LineConnect\PostType\Interaction\Interaction as InteractionCPT;
use Shipweb\LineConnect\Core\LineConnect;

/**
 * Holds the definition of an entire interaction flow.
 */
class InteractionDefinition {
    private int $id;
    private string $title;
    private array $steps = [];
    private int $timeout_minutes;
    private int $timeout_remind_minutes;
    private string $on_timeout;
    private string $run_policy;
    private string $override_policy;
    private int $version;
    private string $storage;
    private array $exclude_steps = [];
    const SPECIAL_STEPS = [
        'confirm',
        'editPicker',
        'complete',
        'resumeConfirm',
        'canceled',
        'resumeConfirm',
        'timeoutRemind',
        'timeoutNotice',
    ];


    /**
     * @param int $post_id The post ID of the interaction.
     * @param array $data The decoded JSON data for the interaction.
     * @param string $title The post title.
     */
    public function __construct(int $post_id, int $version, array $data, string $title) {
        $this->id = $post_id;
        $this->title = $title;
        $this->version = $version;

        foreach ($data['steps'] ?? [] as $step_data) {
            $this->steps[] = new StepDefinition($step_data);
        }

        $this->timeout_minutes = $data['timeoutMinutes'] ?? 0;
        $this->timeout_remind_minutes = $data['timeoutRemind'] ?? false;
        $this->on_timeout = $data['onTimeout'] ?? 'mark_timeout';
        $this->run_policy = $data['runPolicy'] ?? 'single_latest_only';
        $this->override_policy = $data['overridePolicy'] ?? 'stack';
        $this->storage = $data['storage'] ?? 'none';
        $this->exclude_steps = $data['excludeSteps'] ?? [];
    }

    /**
     * Factory method to create an instance from a post ID.
     *
     * @param int $post_id
     * @return self|null
     */
    public static function from_post(int $post_id, ?int $version): ?self {
        $post = get_post($post_id);
        if (!$post || $post->post_type !== InteractionCPT::POST_TYPE) {
            return null;
        }

        $data = get_post_meta($post_id, InteractionCPT::META_KEY_DATA, true);
        // NOTE: This assumes the latest version of data structure.
        // Version migration logic might be needed here in the future.
        if ($version !== null) {
            $form_version = $version;
        } else {
            $form_version = get_post_meta($post_id, InteractionCPT::META_KEY_VERSION, true);
        }
        if (isset($data[$form_version])) {
            $data = $data[$form_version][0];
        }

        if (empty($data)) {
            return null;
        }

        $data = apply_filters(
            lineconnect::FILTER_PREFIX . 'interaction_definition',
            $data,
            $post_id,
            $form_version
        );

        return new self($post_id, $form_version, $data, $post->post_title);
    }

    public function get_id(): int {
        return $this->id;
    }

    public function get_title(): string {
        return $this->title;
    }

    /**
     * @return StepDefinition[]
     */
    public function get_steps(): array {
        return $this->steps;
    }

    public function get_first_step(): ?StepDefinition {
        return $this->steps[0] ?? null;
    }

    public function get_step(string $step_id): ?StepDefinition {
        foreach ($this->steps as $step) {
            if ($step->get_id() === $step_id) {
                return $step;
            }
        }
        return null;
    }

    public function get_special_step(string $special_type): ?StepDefinition {
        foreach ($this->steps as $step) {
            if ($step->get_special_type() === $special_type) {
                return $step;
            }
        }
        return null;
    }

    public function get_version(): int {
        return $this->version;
    }

    public function get_timeout_minutes(): int {
        return $this->timeout_minutes;
    }

    public function get_timeout_remind_minutes(): int {
        return $this->timeout_remind_minutes;
    }

    public function get_on_timeout(): string {
        return $this->on_timeout;
    }

    public function get_run_policy(): string {
        return $this->run_policy;
    }

    public function get_override_policy(): string {
        return $this->override_policy;
    }

    public function get_storage(): string {
        return $this->storage;
    }

    public function get_exclude_steps(): array {
        return array_merge($this->exclude_steps, self::SPECIAL_STEPS);
    }
}
