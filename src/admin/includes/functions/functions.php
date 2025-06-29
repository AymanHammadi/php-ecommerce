<?php

/**
 * Retrieves the page title, sanitized for safe output.
 *
 * Returns the global $pageTitle if set, otherwise falls back to a default title.
 * The output is escaped to prevent XSS attacks.
 *
 * @return string The sanitized page title.
 */
function getTitle(): string
{
    global $pageTitle;
    return isset($pageTitle) ? htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8') : 'PHP E-commerce';
}

/**
 * Redirects to a specified URL and terminates execution.
 *
 * Uses the HTTP Location header to redirect the browser to the given URL.
 * Ensures the script stops execution after the redirect.
 *
 * @param string $url The URL to redirect to.
 * @return never
 */
function redirect(string $url): void
{
    header("Location: " . filter_var($url, FILTER_SANITIZE_URL));
    exit;
}

/**
 * Checks if a record exists in a specified table.
 *
 * Queries the database to verify if a record with the given ID exists in the specified table.
 * Uses prepared statements to prevent SQL injection.
 *
 * @param string $table The name of the table to query.
 * @param string $id_column The name of the ID column to check.
 * @param int $id The ID value to search for.
 * @return bool True if the record exists, false otherwise.
 * @throws PDOException If the database query fails.
 */
function record_exists(string $table, string $id_column, int $id): bool
{
    global $pdo;

    // Validate table and column names to prevent SQL injection
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table) || !preg_match('/^[a-zA-Z0-9_]+$/', $id_column)) {
        throw new InvalidArgumentException('Invalid table or column name.');
    }

    $stmt = $pdo->prepare("SELECT 1 FROM `$table` WHERE `$id_column` = ? LIMIT 1");
    $stmt->execute([$id]);

    return (bool) $stmt->fetchColumn();
}

/**
 * Deletes a record from a specified table and displays a success or error message.
 *
 * Checks if the record exists and prevents self-deletion if specified.
 * Uses prepared statements for secure deletion and includes a redirect option.
 *
 * @param array $options Configuration options for deletion:
 *                       - table (string): The table name.
 *                       - id_column (string): The ID column name.
 *                       - id (int): The ID of the record to delete.
 *                       - redirect_url (string, optional): URL to redirect to after deletion. Defaults to 'index.php'.
 *                       - redirect_delay (int, optional): Delay in seconds before redirect. Defaults to 3.
 *                       - not_found_title (string, optional): Title for not found error. Defaults to 'Item not found'.
 *                       - success_title (string, optional): Title for success message. Defaults to 'Deleted'.
 *                       - success_message (string, optional): Success message text. Defaults to 'Item was deleted successfully'.
 *                       - prevent_self_delete (int|null, optional): ID to prevent self-deletion. Defaults to null.
 * @return void
 * @throws PDOException If the database query fails.
 * @throws InvalidArgumentException If required options are missing or invalid.
 */
function delete_entity(array $options): void
{
    global $pdo, $components;

    // Validate required options
    $required = ['table', 'id_column', 'id'];
    foreach ($required as $key) {
        if (!isset($options[$key])) {
            throw new InvalidArgumentException("Missing required option: $key");
        }
    }

    $table = $options['table'];
    $id_column = $options['id_column'];
    $id = (int) $options['id'];
    $redirect_url = $options['redirect_url'] ?? 'index.php';
    $redirect_delay = (int) ($options['redirect_delay'] ?? 3);
    $not_found_title = $options['not_found_title'] ?? 'Item not found';
    $success_title = $options['success_title'] ?? 'Deleted';
    $success_message = $options['success_message'] ?? 'Item was deleted successfully';
    $self_protect_id = isset($options['prevent_self_delete']) ? (int) $options['prevent_self_delete'] : null;

    // Prevent self-deletion if applicable
    if ($self_protect_id !== null && $id === $self_protect_id) {
        echo '<div class="alert alert-warning text-center mt-5">' . t('admin.users.cannot_delete_self') . '</div>';
        return;
    }

    // Check if record exists
    if (!record_exists($table, $id_column, $id)) {
        $type = 'error';
        $title = t($not_found_title);
        include $components . 'message.php';
        return;
    }

    // Perform deletion
    $delete_stmt = $pdo->prepare("DELETE FROM `$table` WHERE `$id_column` = ?");
    $delete_stmt->execute([$id]);

    // Display success message
    $type = 'success';
    $title = t($success_title);
    $message = t($success_message);
    include $components . 'message.php';

    // Optional redirect
    if ($redirect_url) {
        header("Refresh: $redirect_delay; url=" . filter_var($redirect_url, FILTER_SANITIZE_URL));
    }
}

/**
 * Performs an aggregate query on a specified table.
 *
 * Executes an aggregate function (e.g., COUNT, SUM) on a column with optional conditions.
 * Uses prepared statements to prevent SQL injection. Allows '*' for COUNT to count all rows.
 *
 * @param string $table_name The name of the table to query.
 * @param string $aggregate_function The aggregate function (e.g., COUNT, SUM, AVG, MAX, MIN).
 * @param string $column_name The column to apply the aggregate function to, or '*' for COUNT.
 * @param array $conditions Optional WHERE conditions as key-value pairs.
 * @return mixed The result of the aggregate query or null if no result.
 * @throws Exception If the query fails or invalid parameters are provided.
 */
function aggregate_query(string $table_name, string $aggregate_function, string $column_name, array $conditions = [])
{
    global $pdo;

    // Validate aggregate function
    $allowed_aggregates = ['COUNT', 'SUM', 'AVG', 'MAX', 'MIN'];
    $aggregate_function = strtoupper($aggregate_function);
    if (!in_array($aggregate_function, $allowed_aggregates)) {
        throw new Exception("Invalid aggregate function: $aggregate_function");
    }

    // Validate table name and column name
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table_name)) {
        throw new Exception('Invalid table name.');
    }
    if ($aggregate_function === 'COUNT' && $column_name === '*') {
        // Allow '*' for COUNT(*)
        $column_clause = '*';
    } else {
        // Validate column name for other cases
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $column_name)) {
            throw new Exception('Invalid column name.');
        }
        $column_clause = "`$column_name`";
    }

    // Build the query
    $query = "SELECT $aggregate_function($column_clause) AS result FROM `$table_name`";
    $params = [];
    if (!empty($conditions)) {
        $where_clauses = [];
        foreach ($conditions as $column => $value) {
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
                throw new Exception("Invalid condition column: $column");
            }
            $where_clauses[] = "`$column` = :$column";
            $params[":$column"] = $value;
        }
        $query .= " WHERE " . implode(' AND ', $where_clauses);
    }

    // Execute the query
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    // Return the result
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['result'] ?? null;
}
/**
 * Retrieves records from a specified table with flexible query options.
 *
 * Supports selecting specific columns, custom WHERE conditions with operators,
 * sorting, and pagination. Uses prepared statements to prevent SQL injection.
 *
 * @param string $table_name The name of the table to query.
 * @param array $columns Columns to select (default: ['*'] for all columns).
 * @param array $conditions WHERE conditions as [column => [value, operator]] pairs (default: []).
 * @param array $order_by Sorting options as [column => direction] pairs (default: []).
 * @param int $limit Number of records to return (default: 10, 0 for no limit).
 * @param int $offset Pagination offset (default: 0).
 * @return array An array of records as associative arrays.
 * @throws InvalidArgumentException If invalid parameters are provided.
 * @throws PDOException If the database query fails.
 */
function get_records(
    string $table_name,
    array $columns = ['*'],
    array $conditions = [],
    array $order_by = [],
    int $limit = 10,
    int $offset = 0
): array {
    global $pdo;

    // Validate inputs
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table_name)) {
        throw new InvalidArgumentException("Invalid table name: $table_name");
    }
    if ($limit < 0 || $offset < 0) {
        throw new InvalidArgumentException('Limit and offset must be non-negative integers.');
    }

    // Validate and sanitize columns
    $select_columns = [];
    foreach ($columns as $column) {
        if ($column !== '*' && !preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            throw new InvalidArgumentException("Invalid column name: $column");
        }
        $select_columns[] = $column === '*' ? '*' : "`$column`";
    }
    $select_clause = implode(', ', $select_columns);

    // Build the query
    $query = "SELECT $select_clause FROM `$table_name`";
    $params = [];

    // Handle WHERE conditions
    if (!empty($conditions)) {
        $where_clauses = [];
        foreach ($conditions as $column => $condition) {
            if (!is_array($condition) || count($condition) !== 2) {
                throw new InvalidArgumentException("Condition for $column must be an array with value and operator.");
            }
            [$value, $operator] = $condition;

            if (!preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
                throw new InvalidArgumentException("Invalid condition column: $column");
            }
            $allowed_operators = ['=', '!=', '<', '>', '<=', '>=', 'LIKE', 'IN'];
            if (!in_array(strtoupper($operator), $allowed_operators)) {
                throw new InvalidArgumentException("Invalid operator for $column: $operator");
            }

            $param_name = ":$column";
            if ($operator === 'IN') {
                if (!is_array($value)) {
                    throw new InvalidArgumentException("IN operator requires an array of values for $column");
                }
                $placeholders = [];
                foreach ($value as $i => $val) {
                    $placeholders[] = "{$param_name}_$i";
                    $params["{$param_name}_$i"] = $val;
                }
                $where_clauses[] = "`$column` IN (" . implode(', ', $placeholders) . ")";
            } else {
                $where_clauses[] = "`$column` $operator $param_name";
                $params[$param_name] = $value;
            }
        }
        $query .= " WHERE " . implode(' AND ', $where_clauses);
    }

    // Handle ORDER BY
    if (!empty($order_by)) {
        $order_clauses = [];
        foreach ($order_by as $column => $direction) {
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
                throw new InvalidArgumentException("Invalid order column: $column");
            }
            $direction = strtoupper($direction);
            if (!in_array($direction, ['ASC', 'DESC'])) {
                throw new InvalidArgumentException("Invalid order direction for $column: $direction");
            }
            $order_clauses[] = "`$column` $direction";
        }
        $query .= " ORDER BY " . implode(', ', $order_clauses);
    }

    // Handle LIMIT and OFFSET
    if ($limit > 0) {
        $query .= " LIMIT :limit OFFSET :offset";
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
    }

    // Execute the query
    $stmt = $pdo->prepare($query);
    foreach ($params as $param => $value) {
        $param_type = is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR;
        $stmt->bindValue($param, $value, $param_type);
    }
    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
