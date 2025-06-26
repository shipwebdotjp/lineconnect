<?php

namespace Shipweb\LineConnect\Action\Definitions;

use Shipweb\LineConnect\Action\AbstractActionDefinition;
use Shipweb\LineConnect\Core\LineConnect;

/**
 * Definition for the get_the_current_datetime action.
 */
class GetTheCurrentDatetime extends AbstractActionDefinition
{
    /**
     * Returns the action key.
     *
     * @return string
     */
    public static function name(): string
    {
        return 'get_the_current_datetime';
    }

    /**
     * Returns the action configuration.
     *
     * @return array
     */
    public static function config(): array
    {
        return [
            'title'       => __('Get the current date and time', lineconnect::PLUGIN_NAME),
            'description' => 'Get the current date and time.',
            'namespace'   => self::class,
            'role'        => 'any',
        ];
    }

    /**
     * Execute action: get current date and time.
     *
     * @return array<string,string>
     */
    public function get_the_current_datetime(): array
    {
        return ['datetime' => date(DATE_RFC2822)];
    }
}
