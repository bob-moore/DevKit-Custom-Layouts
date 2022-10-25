const path = require('path');
const DependencyExtractionWebpackPlugin = require( '@wordpress/dependency-extraction-webpack-plugin' );

module.exports = (env, argv) => {
    return {
        entry: {
            frontend: { import: './src/scripts/frontend.js', filename: './dist/scripts/frontend.js' },
            admin: { import: './src/scripts/admin.js', filename: './dist/scripts/admin.js' },
            block_twig: { import: './src/scripts/blocks/twig.js', filename: './Blocks/twig/block.js' },
            block_template_parts: { import: './src/scripts/blocks/template-parts.js', filename: './Blocks/TemplateParts/block.js' },
        },
        output: {
            path: path.resolve(__dirname),
        },
        devtool : 'eval-cheap-source-map',
        watchOptions: {
            ignored: '**/node_modules/',
        },
        module: {
            rules: [
                {
                    test: /.js$/,
                    exclude: /node_modules/,
                    use: {
                        loader: 'babel-loader',
                        options: {
                            presets: ["@wordpress/babel-preset-default"],
                            plugins: ["@babel/plugin-proposal-object-rest-spread"]
                        }
                    }
                }
            ]
        },
        plugins: [ new DependencyExtractionWebpackPlugin({injectPolyfill : true, combineAssets : false}) ],
    }
};