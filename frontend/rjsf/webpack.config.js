module.exports = (env, args) => {
  const { mode } = args
  const sourceMap = mode === 'development'

  return {
    entry: "./src/index.jsx",
    output: {
      path: `${__dirname}/dist`,
      filename: "main.js",
    },
    devServer: {
      static: {
        directory: "./dist",
      },
    },
    resolve: {
      extensions: [".js", ".jsx"],
    },
    mode: "development",
    module: {
      rules: [
        {
          test: /\.jsx$/,
          exclude: /node_modules/,
          use: {
            loader: 'babel-loader',   //loader名
            options: {                //Babelの設定
              presets: [
                '@babel/preset-env',
                ['@babel/preset-react', {
                  development: mode === 'development'
                }]
              ],
              plugins: [
                '@babel/plugin-syntax-jsx',
                ['@wordpress/babel-plugin-makepot', {
                  output: './languages/rjsf.pot',
                  domain: 'lineconnect',
                  exclude: ['node_modules/**/*'],
                  headers: {
                    'Project-Id-Version': 'LINE Connect',
                    'Report-Msgid-Bugs-To': 'shipwebdotjp@gmail.com'
                  }
                }],
                '@babel/plugin-transform-runtime'
              ]
            }
          }
        },
      ],
    },
  }
};