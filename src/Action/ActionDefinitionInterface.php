<?php

namespace Shipweb\LineConnect\Action;

/**
 * Interface for defining a LINE Connect action.
 */
interface ActionDefinitionInterface {
    /**
     * Returns the action key (name).
     *
     * @return string
     */
    public static function name(): string;

    /**
     * Returns the action configuration array:
     * title, description, namespace, parameters, role, etc.
     *
     * @return array
     */
    public static function config(): array;
}
