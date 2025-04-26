<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Str;
use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Trait for sanitizing input data
 */
trait SanitizesInput
{
    /**
     * Sanitize input data
     *
     * @param array $data
     * @return array
     */
    protected function sanitizeInput(array $data): array
    {
        $sanitized = [];
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                // Remove HTML tags and trim whitespace
                $sanitized[$key] = trim(strip_tags($value));
            } elseif (is_array($value)) {
                $sanitized[$key] = $this->sanitizeInput($value);
            } else {
                $sanitized[$key] = $value;
            }
        }
        return $sanitized;
    }
} 