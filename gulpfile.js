const gulp         = require('gulp');
const sass         = require('gulp-sass')(require('sass'));
const postcss      = require('gulp-postcss');
const autoprefixer = require('autoprefixer');
const cssnano      = require('cssnano');
const pxtorem      = require('postcss-pxtorem');
const sourcemaps   = require('gulp-sourcemaps');
const rename       = require('gulp-rename');

gulp.task( 'build-css-production', function(){
	return gulp.src( 'src/styles/**/*.scss' )
		.pipe( sass().on( 'error', sass.logError ) )
		.pipe( postcss( [ autoprefixer( { grid : 'autoplace' } ), pxtorem() ] ) )
		.pipe( postcss( [ cssnano() ] ) )
		// .pipe( rename( { suffix: '.min' } ) )
		.pipe( gulp.dest( './dist/styles' ) );


} );
gulp.task( 'build-css-dev', () => {
	return gulp.src( 'src/styles/**/*.scss' )
		.pipe( sourcemaps.init() )
		.pipe( sass().on( 'error', sass.logError ) )
		.pipe( postcss( [ autoprefixer( { grid : 'autoplace' } ), pxtorem() ] ) )
		.pipe( sourcemaps.write() )
		.pipe( gulp.dest( './dist/styles' ) )
} );

gulp.task( 'watch', function(){
	gulp.watch( 'src/styles/**/*.scss', gulp.series( 'build-css-dev' ) );
});