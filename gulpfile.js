var gulp = require('gulp');

gulp.task('default', function() {	 
	console.log('Use the following commands');
	console.log('--------------------------');
	console.log('gulp sass				to compile the style.scss to style.css');
	console.log('gulp compile-sass		to compile both of the above.');
	console.log('gulp js				to compile the lsx-banners.js to lsx-banners.min.js');
	console.log('gulp compile-js		to compile both of the above.');
	console.log('gulp watch				to continue watching all files for changes, and build when changed');
	console.log('gulp wordpress-lang	to compile the lsx-banners.pot, en_EN.po and en_EN.mo');
});

var sass = require('gulp-sass');
var jshint = require('gulp-jshint');
var concat = require('gulp-concat');
var uglify = require('gulp-uglify');
var sort = require('gulp-sort');
var wppot = require('gulp-wp-pot');
var gettext = require('gulp-gettext');

gulp.task('sass', function () { 
    gulp.src('assets/css/style.scss')
        .pipe(sass())
        .pipe(gulp.dest('assets/css/'));   
});

gulp.task('js', function () {
	gulp.src('assets/js/lsx-banners.js')	 
	//.pipe(jshint())	 
	//.pipe(jshint.reporter('fail'))	 
	.pipe(concat('lsx-banners.min.js'))
	.pipe(uglify())
	.pipe(gulp.dest('assets/js'));
});
 
gulp.task('compile-sass', (['sass']));
gulp.task('compile-js', (['js']));

gulp.task('watch', function() {
	gulp.watch('assets/css/style.scss', ['sass']);
	gulp.watch('assets/js/lsx-banners.js', ['js']);
});

gulp.task('wordpress-pot', function () {
	return gulp.src('**/*.php')
		.pipe(sort())
		.pipe(wppot({
			domain: 'lsx-banners',
			destFile: 'lsx-banners.pot',
			package: 'lsx-banners',
			bugReport: 'https://bitbucket.org/feedmycode/lsx-banners/issues',
			team: 'LightSpeed <webmaster@lsdev.biz>'
		}))
		.pipe(gulp.dest('languages'));
});

gulp.task('wordpress-po', function () {
	return gulp.src('**/*.php')
		.pipe(sort())
		.pipe(wppot({
			domain: 'lsx-banners',
			destFile: 'en_EN.po',
			package: 'lsx-banners',
			bugReport: 'https://bitbucket.org/feedmycode/lsx-banners/issues',
			team: 'LightSpeed <webmaster@lsdev.biz>'
		}))
		.pipe(gulp.dest('languages'));
});

gulp.task('wordpress-po-mo', ['wordpress-po'], function() {
	return gulp.src('languages/en_EN.po')
		.pipe(gettext())
		.pipe(gulp.dest('languages'));
});

gulp.task('wordpress-lang', (['wordpress-pot', 'wordpress-po-mo']));
