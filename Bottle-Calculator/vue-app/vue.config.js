const { defineConfig } = require('@vue/cli-service');
const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = defineConfig({
  publicPath: './', // Ustawienia dla ścieżki publicznej
  transpileDependencies: true, // Opcjonalne, jeśli masz zależności do transpile

  configureWebpack: {
    output: {
      filename: 'js/[name].js',
      chunkFilename: 'js/[name].js',
    },
  },

  chainWebpack: config => {
    // Konfiguracja dla CSS
    config.plugin('extract-css')
        .use(require('mini-css-extract-plugin'), [{
          filename: 'css/[name].css',
          chunkFilename: 'css/[name].css',
        }]);
  }
});
