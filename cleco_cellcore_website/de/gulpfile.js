// Define dependencies
var gulp = require('gulp');
var sass = require('gulp-sass');
var sassGlob = require('gulp-sass-glob');
var autoprefixer = require('gulp-autoprefixer');
var nano = require('gulp-cssnano');
var sourcemaps = require('gulp-sourcemaps');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var rename = require('gulp-rename');
var imagemin = require('gulp-imagemin');
var cache = require('gulp-cache');

// Compile sass to compressed css andd add vendor prefixes
gulp.task('styles', function() {
	gulp.src('src/css/style.scss')
		.pipe(sassGlob())
		.pipe(sourcemaps.init())
		.pipe(sass())
		.on('error', function(error) {
			console.log('\n ✖ ✖ ✖ ✖ ✖ ERROR ✖ ✖ ✖ ✖ ✖ \n \n' + error.message + '\n \n');
		})
		.pipe(autoprefixer({
			browsers: ['> 1%', 'last 2 versions', 'Firefox >= 20']
		}))
		.pipe(nano())
		.pipe(rename('theme.min.css'))
		.pipe(sourcemaps.write('.'))
		.pipe(gulp.dest('build/css'))
});

// Concatenate files and minify to output to scripts.min.js
gulp.task('scripts', function() {
	gulp.src( ['src/js/libs/*.js','src/js/components/*.js', 'src/js/*.js'])
		.pipe(concat('scripts.min.js'))
		.pipe(uglify())
		.on('error', function(error) {
			console.log('\n ✖ ✖ ✖ ✖ ✖ ERROR ✖ ✖ ✖ ✖ ✖ \n \n' + error.message + '\n \n');
		})
		.pipe(rename('theme.min.js'))
		.pipe(gulp.dest('build/js'))
});


//Minify Images
gulp.task('images', function(){
  return gulp.src('src/images/**/*.+(png|jpg|gif)')
  .pipe(cache(imagemin()))
  .pipe(gulp.dest('build/images'))
});



// Watching scss, js, image files
gulp.task('watch', ['styles', 'scripts', 'images'], function() {
	gulp.watch('src/css/**/*.scss', ['styles']);
	gulp.watch('src/js/**/*.js', ['scripts']);
	gulp.watch('src/images/**/*.+(png|jpg|gif)', ['images']);
});



// Default task
gulp.task('default', ['watch']);
