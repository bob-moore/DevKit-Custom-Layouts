const path = require('path');
const glob = require("glob");
const DependencyExtractionWebpackPlugin = require( '@wordpress/dependency-extraction-webpack-plugin' );
/**
 * Helper function to glob all .js files in src directory
 */
const webpack_entries = glob.sync( 'src/scripts/*.js' ).reduce( ( files, file ) => {

    let filename =  path.basename( file );

    let name = path.parse( filename ).name;

    files[name] = './src/scripts/' + filename;

    return files;

}, {} );

module.exports = (env, argv) => {
    return {
        entry: webpack_entries,
        output: {
            filename: argv.mode === 'production' ? '[name].min.js' : '[name].js',
            path: path.resolve(__dirname, 'dist/scripts'),
        },
        devtool : 'eval-cheap-source-map',
        watchOptions: {
            ignored: '**/node_modules/',
        },
        externals: {
            'jquery': 'jQuery'
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
        plugins: [ new DependencyExtractionWebpackPlugin() ],
    }
};