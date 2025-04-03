const path = require('path')
const TailwindCss = require('tailwindcss')
const Autoprefixer = require('autoprefixer')
const MiniCssExtractPlugin = require('mini-css-extract-plugin')

module.exports = (env, args) => {
    const { mode } = args
    const sourceMap = mode === 'development'

    return {
        devtool: 'source-map',
        entry: './src/index.jsx',
        output: {
            path: __dirname + '/dist',
            filename: 'slc_dashboard.js'
        },
        module: {
            rules: [
                {
                    test: /\.jsx$|\.js$|\.ts$|\.tsx$/,
                    exclude: /node_modules/,
                    use: {
                        loader: 'babel-loader',
                        options: {
                            presets: [
                                '@babel/preset-env',
                                '@babel/preset-typescript',
                                ['@babel/preset-react', {
                                    development: mode === 'development'
                                }]
                            ],
                            plugins: [
                                '@babel/plugin-syntax-jsx',
                                ['@wordpress/babel-plugin-makepot', {
                                    output: './languages/line-dashboard.pot',
                                    domain: 'lineconnect',
                                    exclude: ['node_modules/**/*'],
                                    headers: {
                                        'Project-Id-Version': 'LINE Connect',
                                        'Report-Msgid-Bugs-To': 'shipwebdotjp@gmail.com'
                                    }
                                }]
                            ]
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
            alias: {
                '@': path.resolve(__dirname, 'src'), // rootPath にはルートのパスが入る。
            },
            extensions: ['.ts', '.tsx', '.js', '.jsx', '.json']
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
}