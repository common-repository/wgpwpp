const purgecss = require('@fullhuman/postcss-purgecss')
const cssnano = require('cssnano')

module.exports = {
    plugins: [
        require('postcss-import'),
        require('tailwindcss/nesting'),
        require('tailwindcss'),
        require('autoprefixer'),
      cssnano({
            preset: 'default'
          }),
        purgecss({
            content: ['./src/html/*.html', './src/html/*.php', './src/html/*.latte'],
            defaultExtractor: content => content.match(/[\w-/:]+(?<!:)/g) || []
          })
          
    ]
}
