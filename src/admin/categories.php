<?php

global $templates, $pdo, $components;

/**
 * Admin Categories Management Page
 *
 * This file handles all category management operations including:
 * - Listing all categories
 * - Adding new categories
 * - Editing existing categories
 * - Updating category information
 * - Deleting categories
 * - Managing category hierarchy (parent/child relationships)
 */

// Include required files
require_once 'config.php';
require_once 'includes/functions/auth.php';
requireAdmin(); // only admins can access this page

// Set page title for header
$pageTitle = t('admin.categories.manage_title');

// Include validation functions
require_once __DIR__ . '/includes/functions/validation.php';

// Include header template
include $templates . 'header.php';

// Get the operation (default to 'Manage')
$do = $_GET['do'] ?? 'Manage';

// Route to appropriate handler
try {
    switch ($do) {
        case 'Manage':
            handleManageCategories();
            break;
        case 'Add':
            handleAddCategory();
            break;
        case 'Insert':
            handleInsertCategory();
            break;
        case 'Edit':
            handleEditCategory();
            break;
        case 'Update':
            handleUpdateCategory();
            break;
        case 'Delete':
            handleDeleteCategory();
            break;
        case 'ToggleVisibility':
            handleToggleVisibility();
            break;
        default:
            handle_invalid_action(t('admin.categories.invalid_action'));
            break;
    }
} catch (Exception $e) {
    error_log("Categories management error: " . $e->getMessage());
    show_error_message(t('admin.categories.unexpected_error'), 'categories.php?do=Manage');
}

// Include footer template
include $templates . 'footer.php';

// =============================================================================
// HANDLER FUNCTIONS
// =============================================================================

/**
 * Handle the Manage Categories page - displays list of categories
 */
function handleManageCategories(): void
{
    global $components;

    $categories = getCategoriesList();
    renderCategoriesTable($categories);
    include $components . 'confirm_modal.php';
}

/**
 * Handle the Add Category page - displays add category form
 */
function handleAddCategory(): void
{
    renderCategoryForm('add');
}

/**
 * Handle Insert Category action - processes new category creation
 */
function handleInsertCategory(): void
{
    if (!is_post_request()) {
        show_error_message(t('admin.categories.invalid_request'), 'categories.php?do=Manage');
        return;
    }

    $categoryData = collectCategoryFormData();
    $errors = validateCategoryData($categoryData, 'insert');

    if (!empty($errors)) {
        show_validation_errors($errors, 'categories?do=Add');
        return;
    }

    if (insertCategory($categoryData)) {
        show_success_message(
            t('admin.categories.add_title'),
            t('admin.categories.insert_success'),
            [
                ['label' => t('admin.categories.add_another'), 'url' => 'categories.php?do=Add', 'style' => 'primary'],
                ['label' => t('admin.categories.back_to_categories'), 'url' => 'categories.php?do=Manage', 'style' => 'secondary']
            ],
            'categories?do=Manage'
        );
    } else {
        show_error_message(t('admin.categories.insert_failed'), '$redirect_url.php?do=Add');
    }
}

/**
 * Handle Edit Category page - displays edit category form
 */
function handleEditCategory(): void
{
    $categoryId = get_id_from_request();
    $category = get_record_by_id('categories', 'id', $categoryId);

    if (!$category) {
        show_error_message(t('admin.categories.category_not_found'), 'categories.php?do=Manage');
        return;
    }

    renderCategoryForm('edit', $category);
}

/**
 * Handle Update Category action - processes category updates
 */
function handleUpdateCategory(): void
{
    if (!is_post_request()) {
        show_error_message(t('admin.categories.invalid_request'), 'categories.php?do=Manage');
        return;
    }

    $categoryData = collectCategoryFormData();
    $errors = validateCategoryData($categoryData, 'update');

    if (!empty($errors)) {
        show_validation_errors($errors, 'categories.php?do=Edit&id=' . $categoryData['id']);
        return;
    }

    if (updateCategory($categoryData)) {
        show_success_message(
            t('admin.categories.update_success'),
            '',
            [],
            'categories.php?do=Manage',
            2
        );
    } else {
        show_error_message(t('admin.categories.update_failed'), 'categories.php?do=Edit&id=' . $categoryData['id']);
    }
}

/**
 * Handle Delete Category action
 */
function handleDeleteCategory(): void
{
    $categoryId = get_id_from_request();
    
    // Check if category has subcategories
    if (hasSubcategories($categoryId)) {
        show_error_message(t('admin.categories.has_subcategories'), 'categories.php?do=Manage');
        return;
    }

    delete_entity([
        'table' => 'categories',
        'id_column' => 'id',
        'id' => $categoryId,
        'redirect_url' => 'categories.php?do=Manage',
        'redirect_delay' => 3,
        'not_found_title' => 'admin.categories.category_not_found',
        'success_title' => 'admin.categories.delete_title',
        'success_message' => 'admin.categories.delete_success',
    ]);
}

/**
 * Handle Toggle Visibility action
 */
function handleToggleVisibility(): void
{
    $categoryId = get_id_from_request();
    $category = get_record_by_id('categories', 'id', $categoryId);

    if (!$category) {
        show_error_message(t('admin.categories.category_not_found'), 'categories.php?do=Manage');
        return;
    }

    $newVisibility = $category['visibility'] ? 0 : 1;
    
    if (update_status('categories', 'id', $categoryId, 'visibility', $newVisibility)) {
        show_success_message(
            t('admin.categories.visibility_updated'),
            '',
            [],
            'categories.php?do=Manage',
            1
        );
    } else {
        show_error_message(t('admin.categories.visibility_update_failed'), 'categories.php?do=Manage');
    }
}

// =============================================================================
// DATA ACCESS FUNCTIONS
// =============================================================================

/**
 * Get list of categories with hierarchy information
 */
function getCategoriesList(): array
{
    global $pdo;

    $stmt = $pdo->query("
        SELECT 
            c.id,
            c.name,
            c.description,
            c.parent_id,
            p.name as parent_name,
            c.order,
            c.visibility,
            c.created_at,
            c.updated_at
        FROM categories c
        LEFT JOIN categories p ON c.parent_id = p.id
        ORDER BY COALESCE(c.parent_id, c.id), c.order, c.name
    ");
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get categories for parent selection dropdown
 */
function getCategoriesForDropdown(): array
{
    global $pdo;

    $stmt = $pdo->query("
        SELECT id, name 
        FROM categories 
        WHERE parent_id IS NULL 
        ORDER BY name
    ");
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Insert new category into database
 */
function insertCategory(array $categoryData): bool
{
    // Set timestamps
    $categoryData['created_at'] = date('Y-m-d H:i:s');
    $categoryData['updated_at'] = date('Y-m-d H:i:s');
    
    return insert_record('categories', $categoryData, ['id']);
}

/**
 * Update existing category
 */
function updateCategory(array $categoryData): bool
{
    // Set updated timestamp
    $categoryData['updated_at'] = date('Y-m-d H:i:s');
    
    return update_record('categories', $categoryData, 'id', $categoryData['id'], ['created_at']);
}

/**
 * Check if category has subcategories
 */
function hasSubcategories(int $categoryId): bool
{
    return (bool) aggregate_query('categories', 'COUNT', 'id', ['parent_id' => $categoryId]);
}

// =============================================================================
// FORM HANDLING FUNCTIONS
// =============================================================================

/**
 * Collect and sanitize category form data
 */
function collectCategoryFormData(): array
{
    $data = collect_form_data(
        ['id', 'name', 'description', 'parent_id', 'order', 'visibility'],
        ['id' => 'int', 'parent_id' => 'int', 'order' => 'int', 'visibility' => 'int']
    );

    // Convert 0 parent_id to NULL
    if (isset($data['parent_id']) && $data['parent_id'] === 0) {
        $data['parent_id'] = null;
    }

    return $data;
}
/**
 * Validate category data based on operation type
 */
function validateCategoryData(array $data, string $operation): array
{
    global $pdo;

    $rules = [
        'name' => ['required', 'min:2', 'max:100'],
        'description' => ['max:1000'],
        'order' => ['numeric', 'min:0', 'max:255'],
    ];

    // Ensure category name is unique
    $unique = [
        'name' => ['table' => 'categories', 'id_column' => 'id', 'exclude_id' => $data['id']],
    ];

    $errors = validate($data, $rules, $unique, $pdo);

    // Additional validation for parent category
    if (!empty($data['parent_id'])) {
        if (!record_exists('categories', 'id', $data['parent_id'])) {
            $errors['parent_id'][] = 'Selected parent category does not exist.';
        }
        
        // Prevent circular reference in updates
        if ($operation === 'update' && $data['parent_id'] == $data['id']) {
            $errors['parent_id'][] = 'Category cannot be its own parent.';
        }
    }

    // Validate visibility
    if (!in_array($data['visibility'], [0, 1], true)) {
        $errors['visibility'][] = 'Invalid visibility status.';
    }

    return $errors;
}

// =============================================================================
// RENDERING FUNCTIONS
// =============================================================================

/**
 * Render the categories table
 */
function renderCategoriesTable(array $categories): void
{
    ?>
    <div class="container py-5 min-vh-100">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="mb-0"><?= t('admin.categories.manage_title') ?></h2>
            <a href="?do=Add" class="btn btn-success">
                <i class="fas fa-plus me-1"></i><?= t('admin.categories.add_new') ?>
            </a>
        </div>
        
        <div class="card content-card">
            <div class="card-header bg-transparent border-0 pb-0">
                <h5 class="section-header mb-0"><?= t('admin.categories.manage_title') ?></h5>
            </div>
            <div class="card-body pt-0">
                <div class="table-container">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <?php renderCategoriesTableHeader(); ?>
                            </thead>
                            <tbody>
                                <?php if (empty($categories)): ?>
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <?= t('admin.categories.no_categories') ?>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($categories as $category): ?>
                                        <?php renderCategoryTableRow($category); ?>
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
 * Render table header
 */
function renderCategoriesTableHeader(): void
{
    ?>
    <tr class="table-header">
        <th><?= t('admin.categories.fields.id') ?></th>
        <th><?= t('admin.categories.fields.name') ?></th>
        <th><?= t('admin.categories.fields.parent') ?></th>
        <th><?= t('admin.categories.fields.order') ?></th>
        <th><?= t('admin.categories.fields.visibility') ?></th>
        <th><?= t('admin.categories.fields.created_at') ?></th>
        <th class="text-end"><?= t('admin.categories.actions') ?></th>
    </tr>
    <?php
}

/**
 * Render a single category table row
 */
function renderCategoryTableRow(array $category): void
{
    $indentation = $category['parent_id'] ? '&nbsp;&nbsp;&nbsp;&nbsp;↳ ' : '';
    ?>
    <tr>
        <td><?= htmlspecialchars($category['id']) ?></td>
        <td>
            <?= $indentation ?><?= htmlspecialchars($category['name']) ?>
        </td>
        <td>
            <?= $category['parent_name'] ? htmlspecialchars($category['parent_name']) : '-' ?>
        </td>
        <td><?= htmlspecialchars($category['order']) ?></td>
        <td>
            <span class="badge bg-<?= $category['visibility'] ? 'success' : 'secondary' ?>">
                <?= $category['visibility'] ? t('admin.categories.visible') : t('admin.categories.hidden') ?>
            </span>
        </td>
        <td><?= date('Y-m-d H:i', strtotime($category['created_at'])) ?></td>
        <td class="text-end">
            <?php renderCategoryActionButtons($category); ?>
        </td>
    </tr>
    <?php
}

/**
 * Render action buttons for a category row
 */
function renderCategoryActionButtons(array $category): void
{
    ?>
    <a href="?do=Edit&id=<?= $category['id'] ?>" class="btn btn-outline-primary btn-sm me-1">
        <i class="fas fa-edit me-1"></i><?= t('admin.categories.edit') ?>
    </a>
    
    <a href="?do=ToggleVisibility&id=<?= $category['id'] ?>" class="btn btn-outline-<?= $category['visibility'] ? 'warning' : 'success' ?> btn-sm me-1">
        <i class="fas fa-eye<?= $category['visibility'] ? '-slash' : '' ?> me-1"></i>
        <?= $category['visibility'] ? t('admin.categories.hide') : t('admin.categories.show') ?>
    </a>
    
    <a href="categories.php?do=Delete&id=<?= $category['id'] ?>"
       data-confirm
       data-url="categories.php?do=Delete&id=<?= $category['id'] ?>"
       data-message="<?= t('admin.categories.delete_confirm') ?> '<?= htmlspecialchars($category['name']) ?>'?"
       data-btn-text="<?= t('admin.categories.delete') ?>"
       data-btn-class="btn-danger"
       data-title="<?= t('admin.categories.delete_title') ?>"
       class="btn btn-outline-danger btn-sm">
        <i class="fas fa-trash-alt me-1"></i><?= t('admin.categories.delete') ?>
    </a>
    <?php
}

/**
 * Render category form (add or edit)
 */
function renderCategoryForm(string $mode, array $category = []): void
{
    $isEdit = $mode === 'edit';
    $formAction = $isEdit ? '?do=Update' : '?do=Insert';
    $formTitle = $isEdit ? t('admin.categories.edit_title') : t('admin.categories.add_title');
    $parentCategories = getCategoriesForDropdown();
    ?>
    <div class="container py-5 min-vh-100 d-flex justify-content-center align-items-start">
        <div class="card shadow-sm w-100" style="max-width: 800px;">
            <div class="card-body">
                <h4 class="card-title text-center mb-4"><?= $formTitle ?></h4>
                
                <form action="<?= $formAction ?>" method="POST">
                    <?php if ($isEdit): ?>
                        <input type="hidden" name="id" value="<?= htmlspecialchars($category['id']) ?>">
                    <?php endif; ?>

                    <div class="row g-3">
                        <!-- Category Name -->
                        <div class="col-md-6">
                            <label for="name" class="form-label"><?= t('admin.categories.fields.name') ?> <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="name"
                                   id="name"
                                   class="form-control"
                                   value="<?= $isEdit ? htmlspecialchars($category['name']) : '' ?>"
                                   required>
                            <div class="form-text"><?= t('admin.categories.name_help') ?></div>
                        </div>

                        <!-- Parent Category -->
                        <div class="col-md-6">
                            <label for="parent_id" class="form-label"><?= t('admin.categories.fields.parent') ?></label>
                            <select name="parent_id" id="parent_id" class="form-select">
                                <option value="0"><?= t('admin.categories.no_parent') ?></option>
                                <?php foreach ($parentCategories as $parent): ?>
                                    <?php if (!$isEdit || $parent['id'] != $category['id']): // Prevent self-selection ?>
                                        <option value="<?= $parent['id'] ?>" 
                                                <?= ($isEdit && $category['parent_id'] == $parent['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($parent['name']) ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text"><?= t('admin.categories.parent_help') ?></div>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label for="description" class="form-label"><?= t('admin.categories.fields.description') ?></label>
                            <textarea name="description" 
                                      id="description" 
                                      class="form-control" 
                                      rows="3"><?= $isEdit ? htmlspecialchars($category['description']) : '' ?></textarea>
                            <div class="form-text"><?= t('admin.categories.description_help') ?></div>
                        </div>

                        <!-- Order -->
                        <div class="col-md-6">
                            <label for="order" class="form-label"><?= t('admin.categories.fields.order') ?></label>
                            <input type="number"
                                   name="order"
                                   id="order"
                                   class="form-control"
                                   min="0"
                                   max="255"
                                   value="<?= $isEdit ? htmlspecialchars($category['order']) : '0' ?>">
                            <div class="form-text"><?= t('admin.categories.order_help') ?></div>
                        </div>

                        <!-- Visibility -->
                        <div class="col-md-6">
                            <label for="visibility" class="form-label"><?= t('admin.categories.fields.visibility') ?></label>
                            <select name="visibility" id="visibility" class="form-select">
                                <option value="1" <?= (!$isEdit || $category['visibility']) ? 'selected' : '' ?>>
                                    <?= t('admin.categories.visible') ?>
                                </option>
                                <option value="0" <?= ($isEdit && !$category['visibility']) ? 'selected' : '' ?>>
                                    <?= t('admin.categories.hidden') ?>
                                </option>
                            </select>
                            <div class="form-text"><?= t('admin.categories.visibility_help') ?></div>
                        </div>
                    </div>

                    <div class="form-action-bar mt-4 d-flex justify-content-between">
                        <a href="categories.php?do=Manage" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-1"></i><?= t('admin.categories.back') ?>
                        </a>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i><?= t('admin.categories.submit') ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php
}
