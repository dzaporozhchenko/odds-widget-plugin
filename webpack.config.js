const defaultConfig = require("@wordpress/scripts/config/webpack.config");
const path = require('path');

module.exports = {
    ...defaultConfig,
    entry: {
        'admin-script': './src/admin-script.js',
        'widget-script': './src/widget-script.js',
        'admin-styles': './src/admin-styles.css',
        'widget-styles': './src/widget-styles.css',
    },
    output: {
        path: path.join(__dirname, './build'),
        filename: '[name].js'
    },
}