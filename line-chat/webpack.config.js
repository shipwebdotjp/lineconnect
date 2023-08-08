const path = require('path')
const TailwindCss = require('tailwindcss')
const Autoprefixer = require('autoprefixer')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')

module.exports = (env, args) => {
    const { mode } = args
    const sourceMap = mode === 'development'

    return {
        //mode: 'development',
        devtool: 'inline-source-map',
        entry: './src/index.js',
        output: {
            path: __dirname + '/dist',
            filename: 'slc_chat.js'
        },
        module: {
            rules: [
                {
                    test: /\.js$|jsx/,
                    exclude: /node_modules/,
                    use: {
                        loader: 'babel-loader',   //loader名
                        options: {                //Babelの設定
                            presets: [
                                '@babel/preset-env',
                                '@babel/preset-react',

                            ],
                            env: {
                                "development": {
                                    "presets": [
                                        [
                                            '@babel/preset-react',
                                            { "development": true }
                                        ]
                                    ]
                                }
                            },
                            plugins: ['@babel/plugin-syntax-jsx'] //JSXパース用
                        }
                    }
                },
                {
                    test: /\.css$/,
                    use: [
                        MiniCssExtractPlugin.loader,
                        {
                            loader: 'css-loader',
                            options: {
                                sourceMap
                            }
                        },
                        'postcss-loader'

                    ]
                }
            ]
        },
        resolve: {
            extensions: ['.js', '.jsx', '.json']  // .jsxも省略可能対象にする
        },
        plugins: [
            new MiniCssExtractPlugin({
                filename: 'style.css'
            })
        ],
		optimization: {
			concatenateModules: false,
		}
    }
};