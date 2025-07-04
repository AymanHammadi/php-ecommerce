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

/**
 * Checks if the current request is a POST request.
 *
 * @return bool True if the request method is POST, false otherwise.
 */
function is_post_request(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

/**
 * Gets and validates an ID from request parameters.
 *
 * @param string $param The parameter name to get the ID from (default: 'id').
 * @return int The validated ID, or 0 if not found or invalid.
 */
function get_id_from_request(string $param = 'id'): int
{
    return isset($_GET[$param]) ? (int)$_GET[$param] : 0;
}

/**
 * Shows validation errors in a formatted message.
 *
 * @param array $errors Array of validation errors.
 * @param string $redirect_url URL to redirect to after showing the message.
 * @param int $delay Delay in seconds before redirect (default: 3).
 * @return void
 */
function show_validation_errors(array $errors, string $redirect_url, int $delay = 3): void
{
    global $components;
    
    $title = 'Validation Failed';
    $type = 'error';
    $message = '<ol>';
    foreach ($errors as $fieldErrors) {
        foreach ((array)$fieldErrors as $err) {
            $message .= '<li class="text-start">' . htmlspecialchars($err) . '</li>';
        }
    }
    $message .= '</ol>';

    $redirect_delay = $delay;
    include $components . 'message.php';
}

/**
 * Shows a success message with optional actions and redirect.
 *
 * @param string $title The title of the success message.
 * @param string $message The message content (optional).
 * @param array $actions Array of action buttons (optional).
 * @param string $redirect_url URL to redirect to (optional).
 * @param int $delay Delay in seconds before redirect (default: 3).
 * @return void
 */
function show_success_message(string $title, string $message = '', array $actions = [], string $redirect_url = '', int $delay = 3): void
{
    global $components;
    
    $type = 'success';
    if ($redirect_url) {
        $redirect_delay = $delay;
    }
    include $components . 'message.php';
}

/**
 * Shows an error message with optional redirect.
 *
 * @param string $message The error message to display.
 * @param string $redirect_url URL to redirect to (optional).
 * @param int $delay Delay in seconds before redirect (default: 3).
 * @return void
 */
function show_error_message(string $message, string $redirect_url = '', int $delay = 3): void
{
    global $components;
    
    $title = 'Error';
    $type = 'error';
    if ($redirect_url) {
        $redirect_delay = $delay;
    }
    include $components . 'message.php';
}

/**
 * Handles invalid actions by displaying an error message.
 *
 * @param string $message Custom error message (optional).
 * @return void
 */
function handle_invalid_action(string $message = 'Invalid action requested.'): void
{
    echo "<div class='d-flex flex-column justify-content-center align-content-center container min-vh-100'>
            <div class='alert alert-danger text-center'>" . htmlspecialchars($message) . "</div>
          </div>";
}

/**
 * Generates action buttons for table rows with edit and delete options.
 *
 * @param array $config Configuration array with the following keys:
 *                     - id: The record ID
 *                     - edit_url: URL for edit action
 *                     - delete_url: URL for delete action
 *                     - delete_confirm_message: Confirmation message for delete
 *                     - record_name: Name of the record for display in confirmation
 *                     - edit_text: Text for edit button (default: 'Edit')
 *                     - delete_text: Text for delete button (default: 'Delete')
 *                     - additional_buttons: Array of additional button configurations
 * @return string HTML for the action buttons.
 */
function generate_action_buttons(array $config): string
{
    $id = $config['id'] ?? 0;
    $edit_url = $config['edit_url'] ?? '';
    $delete_url = $config['delete_url'] ?? '';
    $delete_confirm_message = $config['delete_confirm_message'] ?? 'Are you sure you want to delete this item?';
    $record_name = $config['record_name'] ?? '';
    $edit_text = $config['edit_text'] ?? 'Edit';
    $delete_text = $config['delete_text'] ?? 'Delete';
    $additional_buttons = $config['additional_buttons'] ?? [];

    $buttons = '';

    // Edit button
    if ($edit_url) {
        $buttons .= "<a href=\"{$edit_url}\" class=\"btn btn-outline-primary btn-sm me-1\">
                        <i class=\"fas fa-edit me-1\"></i>{$edit_text}
                     </a>";
    }

    // Delete button
    if ($delete_url) {
        $confirm_message = $delete_confirm_message . ($record_name ? " '{$record_name}'?" : '');
        $buttons .= "<a href=\"$delete_url\"
                       data-confirm
                       data-url=\"$delete_url\"
                       data-message=\"" . htmlspecialchars($confirm_message) . "\"
                       data-btn-text=\"{$delete_text}\"
                       data-btn-class=\"btn-danger\"
                       data-title=\"Delete\"
                       class=\"btn btn-outline-danger btn-sm\">
                        <i class=\"fas fa-trash-alt me-1\"></i>{$delete_text}
                     </a>";
    }

    // Additional buttons
    foreach ($additional_buttons as $button) {
        $buttons .= "<a href=\"{$button['url']}\" class=\"btn {$button['class']} btn-sm me-1\">";
        if (isset($button['icon'])) {
            $buttons .= "<i class=\"{$button['icon']} me-1\"></i>";
        }
        $buttons .= "{$button['text']}</a>";
    }

    return $buttons;
}

/**
 * Sanitizes and collects form data from POST request.
 *
 * @param array $fields Array of field names to collect.
 * @param array $types Array specifying data types for fields (int, string, bool).
 * @return array Sanitized form data.
 */
function collect_form_data(array $fields, array $types = []): array
{
    $data = [];
    
    foreach ($fields as $field) {
        $type = $types[$field] ?? 'string';
        $value = $_POST[$field] ?? '';
        
        switch ($type) {
            case 'int':
                $data[$field] = (int) $value;
                break;
            case 'bool':
                $data[$field] = (bool) $value;
                break;
            case 'string':
            default:
                $data[$field] = trim((string) $value);
                break;
        }
    }
    
    return $data;
}

/**
 * Renders a generic management table with pagination and actions.
 *
 * @param array $config Configuration array with the following keys:
 *                     - title: Page title
 *                     - add_url: URL for add new button
 *                     - add_text: Text for add button (default: 'Add New')
 *                     - data: Array of records to display
 *                     - columns: Array of column configurations
 *                     - no_data_message: Message when no data (default: 'No records found')
 * @return void
 */
function render_management_table(array $config): void
{
    $title = $config['title'] ?? 'Manage Records';
    $add_url = $config['add_url'] ?? '';
    $add_text = $config['add_text'] ?? 'Add New';
    $data = $config['data'] ?? [];
    $columns = $config['columns'] ?? [];
    $no_data_message = $config['no_data_message'] ?? 'No records found';
    ?>
    <div class="container py-5 min-vh-100">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0"><?= htmlspecialchars($title) ?></h2>
            <?php if ($add_url): ?>
                <a href="<?= htmlspecialchars($add_url) ?>" class="btn btn-success">+ <?= htmlspecialchars($add_text) ?></a>
            <?php endif; ?>
        </div>
        <div class="card content-card">
            <div class="card-header bg-transparent border-0 pb-0">
                <h5 class="section-header mb-0"><?= htmlspecialchars($title) ?></h5>
            </div>
            <div class="card-body pt-0">
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr class="table-header">
                                    <?php foreach ($columns as $column): ?>
                                        <th><?= htmlspecialchars($column['label']) ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($data)): ?>
                                    <tr>
                                        <td colspan="<?= count($columns) ?>" class="text-center text-muted py-4">
                                            <?= htmlspecialchars($no_data_message) ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($data as $row): ?>
                                        <tr>
                                            <?php foreach ($columns as $column): ?>
                                                <td>
                                                    <?php
                                                    if (isset($column['callback']) && is_callable($column['callback'])) {
                                                        echo $column['callback']($row, $column['field']);
                                                    } else {
                                                        echo htmlspecialchars($row[$column['field']] ?? '');
                                                    }
                                                    ?>
                                                </td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Generic function to get a single record by ID from any table.
 *
 * @param string $table_name The name of the table to query.
 * @param string $id_column The name of the ID column.
 * @param int $id The ID value to search for.
 * @param array $columns Columns to select (default: ['*'] for all columns).
 * @return array|null The record as an associative array, or null if not found.
 * @throws InvalidArgumentException If invalid parameters are provided.
 * @throws PDOException If the database query fails.
 */
function get_record_by_id(string $table_name, string $id_column, int $id, array $columns = ['*']): ?array
{
    global $pdo;

    // Validate inputs
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table_name)) {
        throw new InvalidArgumentException("Invalid table name: $table_name");
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $id_column)) {
        throw new InvalidArgumentException("Invalid column name: $id_column");
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

    $stmt = $pdo->prepare("SELECT $select_clause FROM `$table_name` WHERE `$id_column` = ? LIMIT 1");
    $stmt->execute([$id]);
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ?: null;
}

/**
 * Generic function to insert a record into any table.
 *
 * @param string $table_name The name of the table to insert into.
 * @param array $data Associative array of column => value pairs.
 * @param array $exclude_columns Columns to exclude from insertion (e.g., auto-increment IDs).
 * @return bool True if the insertion was successful, false otherwise.
 * @throws InvalidArgumentException If invalid parameters are provided.
 * @throws PDOException If the database query fails.
 */
function insert_record(string $table_name, array $data, array $exclude_columns = []): bool
{
    global $pdo;

    // Validate table name
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table_name)) {
        throw new InvalidArgumentException("Invalid table name: $table_name");
    }

    // Filter out excluded columns
    $filtered_data = array_diff_key($data, array_flip($exclude_columns));

    if (empty($filtered_data)) {
        throw new InvalidArgumentException('No valid data provided for insertion.');
    }

    // Validate column names
    foreach (array_keys($filtered_data) as $column) {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            throw new InvalidArgumentException("Invalid column name: $column");
        }
    }

    $columns = array_keys($filtered_data);
    $placeholders = array_map(fn($col) => ":$col", $columns);
    
    $columns_clause = '`' . implode('`, `', $columns) . '`';
    $placeholders_clause = implode(', ', $placeholders);

    $query = "INSERT INTO `$table_name` ($columns_clause) VALUES ($placeholders_clause)";
    
    $stmt = $pdo->prepare($query);
    
    // Bind parameters
    foreach ($filtered_data as $column => $value) {
        $stmt->bindValue(":$column", $value);
    }

    return $stmt->execute();
}

/**
 * Generic function to update a record in any table.
 *
 * @param string $table_name The name of the table to update.
 * @param array $data Associative array of column => value pairs to update.
 * @param string $id_column The name of the ID column for the WHERE clause.
 * @param int $id The ID value for the WHERE clause.
 * @param array $exclude_columns Columns to exclude from update.
 * @return bool True if the update was successful, false otherwise.
 * @throws InvalidArgumentException If invalid parameters are provided.
 * @throws PDOException If the database query fails.
 */
function update_record(string $table_name, array $data, string $id_column, int $id, array $exclude_columns = []): bool
{
    global $pdo;

    // Validate table and column names
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table_name)) {
        throw new InvalidArgumentException("Invalid table name: $table_name");
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $id_column)) {
        throw new InvalidArgumentException("Invalid ID column name: $id_column");
    }

    // Filter out excluded columns and ID column
    $exclude_columns[] = $id_column; // Always exclude the ID column from updates
    $filtered_data = array_diff_key($data, array_flip($exclude_columns));

    if (empty($filtered_data)) {
        throw new InvalidArgumentException('No valid data provided for update.');
    }

    // Validate column names
    foreach (array_keys($filtered_data) as $column) {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $column)) {
            throw new InvalidArgumentException("Invalid column name: $column");
        }
    }

    $set_clauses = [];
    foreach (array_keys($filtered_data) as $column) {
        $set_clauses[] = "`$column` = :$column";
    }
    
    $set_clause = implode(', ', $set_clauses);
    $query = "UPDATE `$table_name` SET $set_clause WHERE `$id_column` = :id";
    
    $stmt = $pdo->prepare($query);
    
    // Bind parameters
    foreach ($filtered_data as $column => $value) {
        $stmt->bindValue(":$column", $value);
    }
    $stmt->bindValue(':id', $id, PDO::PARAM_INT);

    return $stmt->execute();
}

/**
 * Generic function to handle approve/disapprove operations.
 *
 * @param string $table_name The name of the table to update.
 * @param string $id_column The name of the ID column.
 * @param int $id The ID of the record to update.
 * @param string $status_column The name of the status column to update.
 * @param int $status_value The new status value (typically 1 for approved, 0 for disapproved).
 * @return bool True if the operation was successful, false otherwise.
 * @throws InvalidArgumentException If invalid parameters are provided.
 * @throws PDOException If the database query fails.
 */
function update_status(string $table_name, string $id_column, int $id, string $status_column, int $status_value): bool
{
    global $pdo;

    // Validate table and column names
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table_name)) {
        throw new InvalidArgumentException("Invalid table name: $table_name");
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $id_column)) {
        throw new InvalidArgumentException("Invalid ID column name: $id_column");
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $status_column)) {
        throw new InvalidArgumentException("Invalid status column name: $status_column");
    }

    $stmt = $pdo->prepare("UPDATE `$table_name` SET `$status_column` = ? WHERE `$id_column` = ?");
    return $stmt->execute([$status_value, $id]);
}

/**
 * Validates and sanitizes a slug for URL-friendly use.
 *
 * @param string $slug The slug to validate and sanitize.
 * @param int $max_length Maximum length for the slug (default: 255).
 * @return string The sanitized slug.
 */
function sanitize_slug(string $slug, int $max_length = 255): string
{
    // Convert to lowercase
    $slug = strtolower($slug);
    
    // Remove special characters and replace with hyphens
    $slug = preg_replace('/[^a-z0-9\-_]/', '-', $slug);
    
    // Remove multiple consecutive hyphens
    $slug = preg_replace('/-+/', '-', $slug);
    
    // Remove leading and trailing hyphens
    $slug = trim($slug, '-');
    
    // Truncate to max length
    if (strlen($slug) > $max_length) {
        $slug = substr($slug, 0, $max_length);
        $slug = rtrim($slug, '-');
    }
    
    return $slug;
}

/**
 * Checks if a slug is unique in a given table.
 *
 * @param string $table_name The name of the table to check.
 * @param string $slug_column The name of the slug column.
 * @param string $slug The slug to check for uniqueness.
 * @param string $id_column The name of the ID column (for excluding current record during updates).
 * @param int|null $exclude_id The ID to exclude from the check (for updates).
 * @return bool True if the slug is unique, false otherwise.
 * @throws InvalidArgumentException If invalid parameters are provided.
 * @throws PDOException If the database query fails.
 */
function is_slug_unique(string $table_name, string $slug_column, string $slug, string $id_column = '', ?int $exclude_id = null): bool
{
    global $pdo;

    // Validate table and column names
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table_name)) {
        throw new InvalidArgumentException("Invalid table name: $table_name");
    }
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $slug_column)) {
        throw new InvalidArgumentException("Invalid slug column name: $slug_column");
    }
    if ($exclude_id !== null && !preg_match('/^[a-zA-Z0-9_]+$/', $id_column)) {
        throw new InvalidArgumentException("Invalid ID column name: $id_column");
    }

    $query = "SELECT COUNT(*) FROM `$table_name` WHERE `$slug_column` = ?";
    $params = [$slug];

    if ($exclude_id !== null && !empty($id_column)) {
        $query .= " AND `$id_column` != ?";
        $params[] = $exclude_id;
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    return (int) $stmt->fetchColumn() === 0;
}

/**
 * Generates a unique slug by appending numbers if necessary.
 *
 * @param string $table_name The name of the table to check.
 * @param string $slug_column The name of the slug column.
 * @param string $base_slug The base slug to make unique.
 * @param string $id_column The name of the ID column (for excluding current record during updates).
 * @param int|null $exclude_id The ID to exclude from the check (for updates).
 * @return string A unique slug.
 */
function generate_unique_slug(string $table_name, string $slug_column, string $base_slug, string $id_column = '', ?int $exclude_id = null): string
{
    $slug = sanitize_slug($base_slug);
    $original_slug = $slug;
    $counter = 1;

    while (!is_slug_unique($table_name, $slug_column, $slug, $id_column, $exclude_id)) {
        $slug = $original_slug . '-' . $counter;
        $counter++;
    }

    return $slug;
}
