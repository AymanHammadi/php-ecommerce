$(() => {
    // Bind validation to inputs with data-validate
    $('input[data-validate], select[data-validate]').on('blur keyup change', function () {
        const $input = $(this);
        const value = $input.val().trim();
        const type = $input.data('validate');
        let valid = true;
        let msg = '';

        // Run validation rules based on field type
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

        // Apply feedback styles
        $input.toggleClass('is-invalid', !valid);
        $input.toggleClass('is-valid', valid);
        $input.siblings('.invalid-feedback').text(msg);
    });
});
