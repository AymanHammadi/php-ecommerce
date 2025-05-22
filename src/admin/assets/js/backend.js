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
            e.preventDefault(); // Stop submission
        }
    });

    // Hide/Show Password
    $(document).on('click', '.toggle-password', function () {
        const $input = $(this).siblings('input');
        const $icon = $(this).find('i');

        const is_hidden = $input.attr('type') === 'password';
        $input.attr('type', is_hidden ? 'text' : 'password');
        $icon.toggleClass('fa-eye fa-eye-slash');
    });

    // Generic confirm modal handler
    $(document).on('click', '[data-confirm]', function (e) {
        e.preventDefault();

        const $btn = $(this);
        const url = $btn.data('url') || $btn.attr('href');
        const message = $btn.data('message') || 'Are you sure?';
        const title = $btn.data('title') || 'Confirm Action';
        const btnText = $btn.data('btn-text') || 'Confirm';
        const btnClass = $btn.data('btn-class') || 'btn-primary';

        // Optional pre-check
        const preCheck = $btn.data('precheck');
        if (preCheck) {
            const error = runPrecheck(preCheck, $btn);
            if (error) {
                $('#genericErrorModalMessage').text(error);
                $('#genericErrorModal').modal('show');
                return;
            }
        }

        $('#genericConfirmModalTitle').text(title);
        $('#genericConfirmModalMessage').text(message);
        $('#genericConfirmButton')
            .text(btnText)
            .attr('href', url)
            .attr('class', 'btn ' + btnClass);
        $('#genericConfirmModal').modal('show');
    });

    //  precheck function
    function runPrecheck(type, $el) {
        switch (type) {
            case 'preventSelfDelete':
                const userId = parseInt($el.data('user-id'), 10);
                const currentId = parseInt($el.data('current-id'), 10);
                if (userId === currentId) {
                    return $el.data('error') || 'You cannot delete your own account.';
                }
                break;

            // more checks will be added later
        }
        return null;
    }


});
