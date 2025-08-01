module.exports = {
  purge: {
    enabled: process.env.NODE_ENV === 'production', // 本番環境のみPurge有効化
    content: ['./src/**/*.js'],
    safelist: [
      {
        pattern: /^flex-preview/,
      },
    ],
  },
  darkMode: false, // or 'media' or 'class'
  theme: {
    extend: {},
  },
  variants: {
    extend: {
      backgroundColor: ['active'],
    },
  },
  plugins: [],
}
