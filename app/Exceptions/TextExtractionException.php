<?php

namespace App\Exceptions;

use Exception;

/**
 * Exception thrown when text extraction from resume files fails
 *
 * This exception is used specifically for resume processing errors
 * such as corrupted files, unsupported formats, or parsing failures.
 */
class TextExtractionException extends Exception
{
    /**
     * Create a new text extraction exception
     *
     * @param  string  $message  The exception message
     * @param  int  $code  The exception code
     * @param  Exception|null  $previous  The previous exception
     */
    public function __construct(string $message = 'Text extraction failed', int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Get the exception's context for logging
     */
    public function context(): array
    {
        return [
            'exception_type' => 'text_extraction',
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
        ];
    }
}
