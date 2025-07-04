$(() => {
    function validateField($input) {
        const value = $input.val().trim();
        const type = $input.data('validate');
        let valid = true;
        let msg = '';

        switch (type) {
            case 'username':
                valid = value.length >= 4 && value.length <= 20;
                msg = valid ? '' : 'Username must be 4–20 characters.';
                break;
            case 'email':
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                valid = emailRegex.test(value);
                msg = valid ? '' : 'Invalid email address.';
                break;
            case 'full_name':
                valid = value.length >= 2;
                msg = valid ? '' : 'Full name must be at least 2 characters.';
                break;
            case 'password':
                valid = value.length >= 6 &&
                    /[A-Z]/.test(value) &&
                    /[a-z]/.test(value) &&
                    /\d/.test(value);
                msg = valid ? '' : 'Password must have upper/lowercase and a number.';
                break;
            case 'group_id':
            case 'trust_status':
            case 'reg_status':
                valid = !isNaN(value) && value !== '' && +value >= 0;
                msg = valid ? '' : 'Please select a valid option.';
                break;
        }

        $input.toggleClass('is-invalid', !valid);
        $input.toggleClass('is-valid', valid);
        $input.siblings('.invalid-feedback').text(msg);
        return valid;
    }

    // Validate on interaction
    $('input[data-validate], select[data-validate]').on('blur keyup change', function () {
        validateField($(this));
    });

    // Validate all on submit
    $('form').on('submit', function (e) {
        let allValid = true;
        $(this).find('input[data-validate], select[data-validate]').each(function () {
            if (!validateField($(this))) {
                allValid = false;
            }
        });

        if (!allValid) {
            e.preventDefault();
        }
    });

    // Category form 
    if ($('#name').length) {
        // Auto-generate order value based on existing categories
        if ($('#order').val() === '0' || $('#order').val() === '') {
            // You could make an AJAX call here to get the next order number
            // For now, we'll leave it as is
        }

        // Form validation for category-specific fields
        $('input[name="name"]').on('blur keyup', function() {
            const value = $(this).val().trim();
            const valid = value.length >= 2 && value.length <= 100;
            const msg = valid ? '' : 'Category name must be 2-100 characters.';
            
            $(this).toggleClass('is-invalid', !valid);
            $(this).toggleClass('is-valid', valid && value.length > 0);
            $(this).siblings('.invalid-feedback').text(msg);
        });

        $('textarea[name="description"]').on('blur keyup', function() {
            const value = $(this).val().trim();
            const valid = value.length <= 1000;
            const msg = valid ? '' : 'Description must not exceed 1000 characters.';
            
            $(this).toggleClass('is-invalid', !valid);
            $(this).toggleClass('is-valid', valid);
            $(this).siblings('.invalid-feedback').text(msg);
        });

        $('input[name="order"]').on('blur keyup', function() {
            const value = $(this).val();
            const valid = !isNaN(value) && value >= 0 && value <= 255;
            const msg = valid ? '' : 'Order must be a number between 0 and 255.';
            
            $(this).toggleClass('is-invalid', !valid);
            $(this).toggleClass('is-valid', valid);
            $(this).siblings('.invalid-feedback').text(msg);
        });
    }

    // Enhanced form submission feedback
    $('form').on('submit', function() {
        const $submitBtn = $(this).find('button[type="submit"]');
        const originalText = $submitBtn.html();
        
        $submitBtn.prop('disabled', true);
        $submitBtn.html('<i class="fas fa-spinner fa-spin me-1"></i>Saving...');
        
        // Re-enable after a delay (in case of server-side redirect)
        setTimeout(() => {
            $submitBtn.prop('disabled', false);
            $submitBtn.html(originalText);
        }, 3000);
    });

    // Confirm modal enhancements for categories
    $('[data-confirm]').on('click', function(e) {
        e.preventDefault();
        
        const url = $(this).data('url');
        const message = $(this).data('message');
        const title = $(this).data('title') || 'Confirm Action';
        const btnText = $(this).data('btn-text') || 'Confirm';
        const btnClass = $(this).data('btn-class') || 'btn-danger';
        
        // Create or update modal
        let modal = $('#confirmModal');
        if (modal.length === 0) {
            modal = $(`
                <div class="modal fade" id="confirmModal" tabindex="-1">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title"></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body"></div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <a href="#" class="btn confirm-btn">Confirm</a>
                            </div>
                        </div>
                    </div>
                </div>
            `);
            $('body').append(modal);
        }
        
        modal.find('.modal-title').text(title);
        modal.find('.modal-body').html(message);
        modal.find('.confirm-btn').attr('href', url).attr('class', `btn ${btnClass}`).text(btnText);
        
        modal.modal('show');
    });
});
