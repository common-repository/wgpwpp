module.exports = {
  content: ["./src/**/*.{html,js,php,latte}"],
  darkMode: 'class',
  theme: {
    extend: {
      boxShadow: {
        '3xl': '0 35px 60px -15px rgba(0, 0, 0, 1)',
        'cart': '0 12px 20px rgba(0,0,0,0.06)'
      },
      fontFamily: {
        'Roboto': ['Roboto'],
        'Poppins': ['Poppins'],
      }
    },
    screens: {
      'sm': '640px',
      // => @media (min-width: 640px) { ... }

      'md': '768px',
      // => @media (min-width: 768px) { ... }

      'lg': '1024px',
      // => @media (min-width: 1024px) { ... }

      'xl': '1280px',
      // => @media (min-width: 1280px) { ... }

      '2xl': '1536px',
      // => @media (min-width: 1536px) { ... }

      '3xl': '1850px',
      // => @media (min-width: 1850px) { ... }
    }
  },
}
