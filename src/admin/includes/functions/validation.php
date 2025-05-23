<?php

/**
 * Main validation function that processes data against defined rules
 *
 * @param array $data Data to validate
 * @param array $rules Validation rules to apply
 * @param array $uniqueChecks Database uniqueness checks
 * @param PDO $pdo Database connection for uniqueness checks
 * @return array List of validation errors
 */
function validate(array $data, array $rules, array $uniqueChecks = [], PDO $pdo = null): array
{
    // Initialize error container
    $errors = [];

    // Process regular validation rules
    $errors = processValidationRules($data, $rules, $errors);

    // Process uniqueness checks if a database connection is provided
    if ($pdo !== null) {
        $errors = mergeUniqueValidationErrors($errors, $data, $uniqueChecks, $pdo);
    }

    return $errors;
}

/**
 * Process all validation rules against the data
 *
 * @param array $data Data to validate
 * @param array $rules Rules to validate against
 * @param array $errors Existing errors to append to
 * @return array Updated errors array
 */
function processValidationRules(array $data, array $rules, array $errors = []): array
{
    foreach ($rules as $field => $fieldRules) {
        // Get trimmed value or empty string if not exists
        $value = trim($data[$field] ?? '');

        foreach ($fieldRules as $rule) {
            // Parse the rule and its parameters
            [$ruleName, $ruleParam] = parseRule($rule);

            // Validate field against the rule
            $validationResult = validateField($ruleName, $value, $ruleParam, $field);

            // Add error message if validation failed
            if (!empty($validationResult)) {
                $errors[$field][] = $validationResult;
            }
        }
    }

    return $errors;
}

/**
 * Parse a validation rule into name and parameter
 *
 * @param string $rule The rule to parse (e.g. "min:5")
 * @return array [ruleName, ruleParam]
 */
function parseRule(string $rule): array
{
    // Check if rule has parameters
    if (strpos($rule, ':') !== false) {
        list($ruleName, $ruleParam) = explode(':', $rule, 2);
    } else {
        $ruleName = $rule;
        $ruleParam = null;
    }

    return [$ruleName, $ruleParam];
}

/**
 * Merge uniqueness validation errors with other validation errors
 *
 * @param array $errors Existing validation errors
 * @param array $data Data to validate
 * @param array $uniqueChecks Uniqueness checks configuration
 * @param PDO $pdo Database connection
 * @return array Updated errors array
 */
function mergeUniqueValidationErrors(array $errors, array $data, array $uniqueChecks, PDO $pdo): array
{
    $uniquenessErrors = validateUniqueness($data, $uniqueChecks, $pdo);

    foreach ($uniquenessErrors as $field => $error) {
        $errors[$field][] = $error;
    }

    return $errors;
}

/**
 * Routes validation to the appropriate validation function
 *
 * @param string $rule Name of the validation rule
 * @param mixed $value Value to validate
 * @param mixed $param Optional parameter for the rule
 * @param string $field Name of the field being validated
 * @return string|null Error message or null if validation passes
 */
function validateField(string $rule, $value, $param = null, string $field = ''): ?string
{
    // Map of validation rules to their handler functions
    $validationHandlers = [
        'required' => 'validateRequired',
        'min' => 'validateMin',
        'max' => 'validateMax',
        'email' => 'validateEmail',
        'password_strength' => 'validatePasswordStrength',

    ];

    // Check if the rule exists in our validation handlers
    if (isset($validationHandlers[$rule])) {
        $validationFunction = $validationHandlers[$rule];
        return $validationFunction($value, $param, $field);
    }

    // For custom rules, check if a function exists with the pattern validate{RuleName}
    $customValidationFunction = 'validate' . ucfirst($rule);
    if (function_exists($customValidationFunction)) {
        return $customValidationFunction($value, $param, $field);
    }

    // Rule not found
    return null;
}

/**
 * Validates that a field is not empty
 *
 * @param mixed $value The value to validate
 * @param mixed $param Unused for this validation rule
 * @param string $field The field name for error messaging
 * @return string|null Error message or null if validation passes
 */
function validateRequired($value, $param = null, string $field = ''): ?string
{
    if ($value === '') {
        return ucfirst($field) . ' is required.';
    }
    return null;
}

/**
 * Validates minimum length requirement
 *
 * @param mixed $value The value to validate
 * @param mixed $min Minimum length required
 * @param string $field The field name for error messaging
 * @return string|null Error message or null if validation passes
 */
function validateMin($value, $min, string $field = ''): ?string
{
    $min = (int)$min;
    if (strlen($value) < $min) {
        return ucfirst($field) . " must be at least $min characters.";
    }
    return null;
}

/**
 * Validates maximum length requirement
 *
 * @param mixed $value The value to validate
 * @param mixed $max Maximum length allowed
 * @param string $field The field name for error messaging
 * @return string|null Error message or null if validation passes
 */
function validateMax($value, $max, string $field = ''): ?string
{
    $max = (int)$max;
    if (strlen($value) > $max) {
        return ucfirst($field) . " must be at most $max characters.";
    }
    return null;
}

/**
 * Validates email format
 *
 * @param mixed $value The value to validate
 * @param mixed $param Unused for this validation rule
 * @param string $field The field name for error messaging
 * @return string|null Error message or null if validation passes
 */
function validateEmail($value, $param = null, string $field = ''): ?string
{
    if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
        return 'Invalid email format.';
    }
    return null;
}

/**
 * Validates password strength requirements
 *
 * @param mixed $value The value to validate
 * @param mixed $param Optional custom requirements
 * @param string $field The field name for error messaging
 * @return string|null Error message or null if validation passes
 */
function validatePasswordStrength($value, $param = null, string $field = ''): ?string
{
    if ($value === '') {
        return null; // Empty check should be handled by required rule
    }

    // Minimum length check
    if (strlen($value) < 6) {
        return 'Password must be at least 6 characters.';
    }

    // Character diversity check
    if (!preg_match('/[A-Z]/', $value) || !preg_match('/[a-z]/', $value) || !preg_match('/[0-9]/', $value)) {
        return 'Password must include upper, lower case letters and a number.';
    }

    return null;
}

/**
 * Validates uniqueness against database records
 *
 * @param array $data Data to check for uniqueness
 * @param array $uniqueChecks Uniqueness configuration
 * @param PDO $pdo Database connection
 * @return array Uniqueness validation errors
 */
function validateUniqueness(array $data, array $uniqueChecks, ?PDO $pdo): array
{
    $errors = [];

    if (!$pdo) {
        return $errors;
    }

    foreach ($uniqueChecks as $field => $opts) {
        if (empty($data[$field])) {
            continue;
        }

        $query = "SELECT COUNT(*) FROM {$opts['table']} WHERE $field = ?";
        $params = [$data[$field]];

        // Add exclusion for updates
        if (!empty($opts['exclude_id']) && !empty($opts['id_column'])) {
            $query .= " AND {$opts['id_column']} != ?";
            $params[] = $opts['exclude_id'];
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        if ($stmt->fetchColumn() > 0) {
            $errors[$field] = ucfirst($field) . ' already in use.';
        }
    }

    return $errors;
}
