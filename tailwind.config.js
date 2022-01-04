const defaultTheme = require('tailwindcss/defaultTheme');

module.exports = {
    purge: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            maxWidth: {
                100: '100px',
              },
            minWidth: {
                200: '200px',
            }
        },
    },

    plugins: [require('@tailwindcss/forms')],
};
