<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when an explicit scope context is required (e.g. non-web, queue, or AI tool calls)
 * but no valid user or scope boundaries were provided.
 */
class MissingScopeContextException extends RuntimeException
{
}
