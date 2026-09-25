<?php

namespace App\Services\Ai;

class AiSystemPrompt
{
    /**
     * Get the default system prompt from configuration.
     */
    public static function get(): string
    {
        return (string) config('ai.system_prompt', '');
    }

    /**
     * Get fallback message for unregistered/disallowed tools.
     */
    public static function getUnknownToolFallback(): string
    {
        return (string) config('ai.fallback_unknown_tool', 'Action not permitted.');
    }

    /**
     * Get fallback message for invalid or missing tool parameters.
     */
    public static function getInvalidParamsFallback(): string
    {
        return (string) config('ai.fallback_invalid_params', 'Invalid or missing parameters.');
    }
}
