module.exports = {
    mode: 'development',
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
            }
        ]
    },
    resolve: {
        extensions: ['.js', '.jsx', '.json']  // .jsxも省略可能対象にする
    }
};