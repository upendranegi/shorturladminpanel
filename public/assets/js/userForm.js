$(document).ready(function () {
    $('#userForm').validate({
        rules: {
            name: {
                required: true,
                maxlength: 100
            },
            email: {
                required: true,
                email: true,
                maxlength: 150
            },
            password: {
                required: function () {
                    return $('#password').length > 0;
                },
                minlength: 6,
                maxlength: 50
            },
            password_confirmation: {
                required: function () {
                    return $('#password_confirmation').length > 0;
                },
                equalTo: '#password'
            },
            company_name: {
                required: function () {
                    return $('#company_name').length > 0;
                },
                maxlength: 150
            },
            company_code: {
                required: function () {
                    return $('#company_code').length > 0;
                },
                maxlength: 50
            },
            role: {
                required: true
            }
        },
        messages: {
            name: {
                required: 'The Name field is required.',
                maxlength: 'The Name must not be greater than 100 characters.'
            },
            email: {
                required: 'The Email field is required.',
                email: 'The Email must be a valid email address.',
                maxlength: 'The Email must not be greater than 150 characters.'
            },
            password: {
                required: 'The Password field is required.',
                minlength: 'The Password must be at least 6 characters.',
                maxlength: 'The Password must not be greater than 50 characters.'
            },
            password_confirmation: {
                required: 'The Password confirmation field is required.',
                equalTo: 'The Password confirmation does not match.'
            },
            company_name: {
                required: 'The Company Name field is required.',
                maxlength: 'The Company Name must not be greater than 150 characters.'
            },
            company_code: {
                required: 'The Company Code field is required.',
                maxlength: 'The Company Code must not be greater than 50 characters.'
            },
            role: {
                required: 'The Role field is required.'
            }
        },
        errorElement: 'div',
        errorClass: 'text-danger'
    });
});
