<?php

namespace App\Enums;

/**
 * UserType Enum
 * 
 * Represents the different types of users in the system.
 */
enum UserType: string
{
    /**
     * Regular customer user type
     */
    case CUSTOMER = 'customer';

    /**
     * Pharmacist user type
     */
    case PHARMACIST = 'pharmacist';

    /**
     * Administrator user type
     */
    case ADMIN = 'admin';

    /**
     * Get all enum values as an array
     *
     * @return array<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}