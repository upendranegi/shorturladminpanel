$(document).ready(function () {
    $('#shortUrlForm').validate({
        rules: {
            title: {
                maxlength: 100
            },
            original_url: {
                required: true,
                url: true,
                maxlength: 2048
            }
        },
        messages: {
            title: {
                maxlength: 'The Title must not be greater than 100 characters.'
            },
            original_url: {
                required: 'The Original URL field is required.',
                url: 'The Original URL must be a valid URL.',
                maxlength: 'The Original URL must not be greater than 2048 characters.'
            }
        },
        errorElement: 'div',
        errorClass: 'text-danger'
    });
});
