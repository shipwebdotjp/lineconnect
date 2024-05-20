module.exports = {
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
                loader: "babel-loader", 
                exclude: /node_modules/
            },
        ],
    },
  };