/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#FAB133',
          50: '#FEF7EC',
          100: '#FDEFD9',
          200: '#FBDFB3',
          300: '#F9CF8D',
          400: '#F7BF67',
          500: '#FAB133',
          600: '#F19F0D',
          700: '#C17F0A',
          800: '#916008',
          900: '#614005',
        },
        secondary: {
          DEFAULT: '#000000',
          50: '#F7F7F7',
          100: '#E3E3E3',
          200: '#C8C8C8',
          300: '#A4A4A4',
          400: '#717171',
          500: '#4A4A4A',
          600: '#2C2C2C',
          700: '#1A1A1A',
          800: '#0A0A0A',
          900: '#000000',
        },
      },
    },
  },
  plugins: [],
}



