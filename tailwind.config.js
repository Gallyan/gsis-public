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
                24: '6rem',
                200: '200px',
            }
        },
    },

    variants: {
        extend: {
            backgroundColor: ['active','disabled'],
            textColor: ['active','disabled'],
            borderColor : ['focus'],
            outlineColor : ['focus'],
            outlineOffset : ['focus'],
            outlineWidth : ['focus'],
            opacity: ['disabled','hover'],
            cursor: ['disabled'],
        }
    },

    plugins: [require('@tailwindcss/forms')],
};
