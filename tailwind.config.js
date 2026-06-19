/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './**/*.php',
    './js/**/*.js',
  ],
  theme: {
    extend: {
      colors: {
        ink:    '#0a0a0a',
        paper:  '#ECEAE4',
        muted:  '#9c988f',
        dim:    '#7d7970',
        faint:  '#6c685f',
        ash:    '#5e5b53',
        silver: '#b8b4ab',
        warm:   '#8a8780',
        text:   '#d6d2c9',
        alt:    '#8f8b82',
        quote:  '#bdb9b0',
        low:    '#928e85',
      },
      fontFamily: {
        display: ['"Cormorant Garamond"', 'Georgia', 'serif'],
        body:    ['"EB Garamond"', 'Georgia', 'serif'],
        sans:    ['"Helvetica Neue"', 'Arial', 'sans-serif'],
      },
      keyframes: {
        fade: {
          from: { opacity: '0', transform: 'translateY(14px)' },
          to:   { opacity: '1', transform: 'translateY(0)' },
        },
        reveal: {
          from: { opacity: '0', transform: 'translateY(34px)', filter: 'blur(6px)' },
          to:   { opacity: '1', transform: 'translateY(0)',    filter: 'blur(0)' },
        },
      },
      animation: {
        fade:   'fade .6s ease both',
        reveal: 'reveal 1s cubic-bezier(.22,.61,.36,1) both',
      },
    },
  },
  plugins: [],
};
