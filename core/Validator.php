<?php

class Validator
{
    /**
     * Generic string validator used by helper methods.
     */
    public static function string($value, string $field, int $min, int $max, array $options = []): string
    {
        $value = (string)($value ?? '');
        $value = ($options['allowHtml'] ?? false) ? $value : strip_tags($value);
        $value = trim($value);

        if ($value === '') {
            throw new InvalidArgumentException("$field est requis.");
        }

        $length = self::length($value);
        if ($length < $min || $length > $max) {
            throw new InvalidArgumentException("$field doit contenir entre $min et $max caractères.");
        }

        if (!empty($options['pattern']) && !preg_match($options['pattern'], $value)) {
            $message = $options['patternMessage'] ?? "$field contient des caractères invalides.";
            throw new InvalidArgumentException($message);
        }

        return $value;
    }

    public static function text($value, string $field, int $min, int $max): string
    {
        return self::string($value, $field, $min, $max, ['allowHtml' => false]);
    }

    public static function email($value, array $allowedDomains = []): string
    {
        $value = strtolower(trim((string)($value ?? '')));

        if ($value === '' || !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Email invalide.");
        }

        if (!empty($allowedDomains)) {
            $domain = substr(strrchr($value, '@'), 1);
            if (!$domain || !in_array(strtolower($domain), array_map('strtolower', $allowedDomains), true)) {
                throw new InvalidArgumentException("Domaine d'email non autorisé.");
            }
        }

        if (self::length($value) > 190) {
            throw new InvalidArgumentException("Email trop long.");
        }

        return $value;
    }

    public static function password($value, string $field, int $min, int $max, bool $requireComplexity = false): string
    {
        $value = (string)($value ?? '');

        if ($value === '') {
            throw new InvalidArgumentException("$field est requis.");
        }

        $length = self::length($value);
        if ($length < $min || $length > $max) {
            throw new InvalidArgumentException("$field doit contenir entre $min et $max caractères.");
        }

        if ($requireComplexity && !preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])/', $value)) {
            throw new InvalidArgumentException("$field doit contenir une majuscule, une minuscule, un chiffre et un caractère spécial.");
        }

        return $value;
    }

    public static function numericId($value, string $field): int
    {
        if (!isset($value) || !ctype_digit((string)$value)) {
            throw new InvalidArgumentException("$field invalide.");
        }

        return (int)$value;
    }

    public static function numericCode($value, string $field, int $length): string
    {
        $value = trim((string)($value ?? ''));
        $pattern = '/^\d{' . $length . '}$/';

        if (!preg_match($pattern, $value)) {
            throw new InvalidArgumentException("$field doit contenir exactement $length chiffres.");
        }

        return $value;
    }

    public static function arrayOfIds($values, string $field, int $maxItems = 10): array
    {
        if (!is_array($values)) {
            return [];
        }

        $values = array_slice($values, 0, $maxItems);
        $result = [];

        foreach ($values as $value) {
            if (ctype_digit((string)$value)) {
                $result[] = (int)$value;
            } else {
                throw new InvalidArgumentException("$field contient des identifiants invalides.");
            }
        }

        return array_unique($result);
    }

    private static function length(string $value): int
    {
        return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    }
}
?>
