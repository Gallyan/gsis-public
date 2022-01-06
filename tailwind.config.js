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

    variants: {
        extend: {
            backgroundColor: ['active','disabled'],
            textColor: ['active','disabled'],
            outlineColor : ['focus'],
            outlineOffset : ['focus'],
            outlineWidth : ['focus'],
        }
    },

    plugins: [require('@tailwindcss/forms')],
};
