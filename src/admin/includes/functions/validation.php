<?php

function validate(array $data, array $rules, array $uniqueChecks = [], PDO $pdo = null): array
{
    $errors = [];

    foreach ($rules as $field => $fieldRules) {
        $value = trim($data[$field] ?? '');
        foreach ($fieldRules as $rule) {
            if ($rule === 'required' && $value === '') {
                $errors[$field][] = ucfirst($field) . ' is required.';
            } elseif (str_starts_with($rule, 'min:')) {
                $min = (int)substr($rule, 4);
                if (strlen($value) < $min) {
                    $errors[$field][] = ucfirst($field) . " must be at least $min characters.";
                }
            } elseif (str_starts_with($rule, 'max:')) {
                $max = (int)substr($rule, 4);
                if (strlen($value) > $max) {
                    $errors[$field][] = ucfirst($field) . " must be at most $max characters.";
                }
            } elseif ($rule === 'email') {
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field][] = 'Invalid email format.';
                }
            } elseif ($field === 'password') {
                if (in_array('required', $fieldRules) && $value === '') {
                    $errors[$field][] = 'Password is required.';
                } else {
                    if (strlen($value) < 6) {
                        $errors[$field][] = 'Password must be at least 6 characters.';
                    }
                    if (!preg_match('/[A-Z]/', $value) || !preg_match('/[a-z]/', $value) || !preg_match('/[0-9]/', $value)) {
                        $errors[$field][] = 'Password must include upper, lower case letters and a number.';
                    }
                }
            }

        }
    }

    // Uniqueness check
    foreach ($uniqueChecks as $field => $opts) {
        if (!$pdo || empty($data[$field])) continue;
        $query = "SELECT COUNT(*) FROM {$opts['table']} WHERE $field = ?";
        $params = [$data[$field]];

        if (!empty($opts['exclude_id']) && !empty($opts['id_column'])) {
            $query .= " AND {$opts['id_column']} != ?";
            $params[] = $opts['exclude_id'];
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);
        if ($stmt->fetchColumn() > 0) {
            $errors[$field][] = ucfirst($field) . ' already in use.';
        }
    }

    return $errors;
}
