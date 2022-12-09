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
                32: '8rem',
                36: '9rem',
                40: '10rem',
                44: '11rem',
                100: '100px',
                136: '34rem'
              },
            minWidth: {
                24: '6rem',
                200: '200px',
            },
            spacing: {
                136: '34rem',
            },
            minHeight: {
                24: '6rem',
            },
            maxHeight: {
                '95vh': '95vh',
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
