// Require path.
const path = require('path');

const isProduction = process.env.ENV === 'production';

// Configuration object.
const config = {
  // Create the entry points.
  // One for frontend and one for the admin area.
  entry: {
    // frontend and admin will replace the [name] portion of the output config below.
    'shortcodes-generator': path.resolve(__dirname, 'src/js/shortcodes-generator.jsx'),
  },

  // Create the output files.
  // One for each of our entry points.
  output: {
    // [name] allows for the entry object keys to be used as file names.
    filename: '[name].js',
    // Specify the path to the JS files.
    path: path.resolve(__dirname, 'assets/js'),
    publicPath: path.resolve(__dirname, 'assets/js'),
  },

  // Setup a loader to transpile down the latest and great JavaScript so older browsers
  // can understand it.
  module: {
    rules: [
      {
        // Look for any .js files.
        test: /\.jsx?$/,
        // Exclude the node_modules folder.
        exclude: /node_modules/,
        // Use babel loader to transpile the JS files.
        loader: 'babel-loader',
      },
    ],
  },

  resolve: {
    alias: {
      'react': 'preact/compat',
      'react-dom/test-utils': 'preact/test-utils',
      'react-dom': 'preact/compat',
      // Must be below test-utils
    },
  },

  mode: isProduction ? 'production' : 'development',
  target: 'browserslist',
  devtool: isProduction ? null : 'inline-source-map',
};

// Export the config object.
module.exports = config;
