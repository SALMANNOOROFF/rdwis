<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Thrown when an AI tool attempts to look up a record (purchase case, transaction,
 * cheque, employee) that does not exist in the database.
 */
class AiToolRecordNotFoundException extends RuntimeException
{
}
