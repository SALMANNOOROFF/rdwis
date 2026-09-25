<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when an acting user attempts to access records outside their authorized
 * division, unit, or organizational horizon scope.
 */
class UnauthorizedScopeException extends RuntimeException
{
}
