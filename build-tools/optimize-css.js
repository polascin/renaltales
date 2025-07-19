// optimize-css.js
// Optimize CSS assets with webpack and minification
const fs = require('fs');
const path = require('path');
const webpack = require('webpack');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');
const TerserPlugin = require('terser-webpack-plugin');

// Webpack configuration
const webpackConfig = {
  mode: 'production',
  entry: './public/assets/css/dist/purged.css',
  module: {
    rules: [
      {
        test: /\.css$/,
        use: [
          MiniCssExtractPlugin.loader,
          {
            loader: 'css-loader',
            options: { importLoaders: 1, sourceMap: false }
          },
          {
            loader: 'postcss-loader',
            options: { sourceMap: false }
          }
        ]
      }
    ]
  },
  output: {
    path: path.resolve(__dirname, 'public/assets/css/dist'),
    filename: 'optimized.bundle.js',
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: 'optimized.css'
    })
  ],
  optimization: {
    minimizer: [
      new TerserPlugin({
        terserOptions: {
          parse: {},
          compress: {},
          mangle: true, // Note `mangle.properties` is `false` by default.
          output: null,
          toplevel: false,
          nameCache: null,
          ie8: false,
          keep_fnames: false,
        },
      }),
      new OptimizeCSSAssetsPlugin({})
    ],
  },
};

// Run webpack build
webpack(webpackConfig, (err, stats) => {
  if (err || stats.hasErrors()) {
    console.error('Error optimizing CSS:', err ? err.details : stats.toString());
    return;
  }

  const outputFile = path.join(webpackConfig.output.path, webpackConfig.plugins[0].options.filename);

  // Log output file size
  const fileSize = (fs.statSync(outputFile).size / 1024).toFixed(2);
  console.log(`Optimized CSS size: ${fileSize} KB`);

});
