/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
  ],
  theme: {
    extend: {
      fontFamily: {
        'luciole': ['Luciole', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
