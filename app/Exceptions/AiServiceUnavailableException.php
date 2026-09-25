<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when the local Ollama AI service is not running, unreachable,
 * times out, or returns a non-successful HTTP status.
 */
class AiServiceUnavailableException extends RuntimeException
{
}
