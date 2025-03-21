const { defineConfig } = require('@vue/cli-service');
// const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = defineConfig({
    publicPath: './', // Ustawienia dla ścieżki publicznej
    transpileDependencies: true, // Opcjonalne, jeśli masz zależności do transpile

    configureWebpack: {
        output: {
            filename: 'js/[name].js',
            chunkFilename: 'js/[name].js',
        },
        module: {
            rules: [
                {
                    test: /\.wasm$/,
                    type: "asset/resource",
                },
            ],
        },
        resolve: {
            fallback: {
                fs: false, // Usuwa problem z `fs` w przeglądarce
            },
        },
    },

    chainWebpack: config => {
        // Konfiguracja dla CSS
        config.plugin('extract-css')
            .use(MiniCssExtractPlugin, [{
                filename: 'css/[name].css',
                chunkFilename: 'css/[name].css',
            }]);

        // Obsługa plików WASM
        config.module
            .rule('wasm')
            .test(/\.wasm$/)
            .type('asset/resource');
    }
});