const path = require('path');
const {VueLoaderPlugin} = require('vue-loader');


module.exports = {

    entry: [
        './assets/scripts/main.js',
        './assets/styles/app.scss'
    ],

    output: {
        path: path.resolve('./public/build'),
        filename: 'app.js',
        publicPath: '/public/'
    },

    devtool: 'eval-cheap-module-source-map',

    plugins: [
        new VueLoaderPlugin()
    ],

    stats: {
        warnings: false
    },

    module: {
        rules: [
            {
                test: /\.js$/,
                exclude: /node_modules/,
                use: ['babel-loader']
            },
            {
                test: /\.vue$/,
                exclude: /node_modules/,
                use: ['vue-loader']
            },
            {
                test: /\.scss$/,
                exclude: /node_modules/,
                use: [
                    {
                        loader: 'file-loader',
                        options: {
                            outputPath: '/',
                            name: 'main.css'
                        }
                    },
                    {
                        loader: 'sass-loader',
                        options: {
                            implementation: require('sass')
                        }
                    }
                ]
            }
        ]
    }
}