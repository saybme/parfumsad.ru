// tailwind.config.js

/** @type {import('tailwindcss').Config} */
const defaultTheme = require('tailwindcss/defaultTheme')

module.exports = {
  content: [
    "./www/themes/tmp/layouts/**/*.htm",
    "./www/themes/tmp/pages/**/*.htm",
    "./www/themes/tmp/partials/**/*.htm",
    "./www/themes/tmp/assets/**/*.js"
  ],
  theme: {
    screens: {
      'sm': '640px',
      'md': '768px',
      'lg': '1024px',
      'xl': '1280px',
      '2xl': '1536px',
    },
    container: {
      center: true,
      padding: {
        DEFAULT: '0',
        tb: '1rem'
      },
    },
    extend: {
      colors: {
        'purple': {
          DEFAULT: '#863EE6',
          1: '#C199FC',
          2: '#F3EBFE',
          3: '#8D47F6',
          10: '#7220B5'
        },
        gray: {
          DEFAULT: '#333333',
          1: '#767676',
          2: '#EEEEEE',
          10: '#374957'
        },
        'rose': {
          DEFAULT: '#f1548e'
        }
      },
      fontFamily: {
        'montserrat': ['"Roboto Condensed"', ...defaultTheme.fontFamily.sans]
      },
      fontSize: {
        '26px': '1.625rem',
        '40px': '2.5rem'
      },
      backgroundImage: {
        'sheet': "url('../images/icons/sheet.png')",
      }
    },
  },
  plugins: [
    require("daisyui"),
    require('@tailwindcss/aspect-ratio'),
    require('tailwind-scrollbar'),
    require('tailwind-fontawesome')({
      version: 6
  })
  ],
  corePlugins: {
    aspectRatio: false,
  },
  daisyui: {
    themes: [{
      "light": {
        "secondary": "#7220B5",
        "secondary-content": "#ffffff"
      }
    }],
  },
}