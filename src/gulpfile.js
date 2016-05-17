/*
 * @Author: gaohuabin
 * @Date:   2016-04-28 14:05:25
 * @Last Modified by:   gaohuabin
 * @Last Modified time: 2016-05-16 19:42:12
 */

var gulp = require('gulp');
var plugins = require('gulp-load-plugins')();
var pngquant = require('imagemin-pngquant'); //png图片压缩插件

var paths = {
    script: ['./js/app.js', './js/controllers.js', './js/directives.js', './js/filters.js', './js/services.js']
};

gulp.task('jsHint', function () {
    return gulp.src(paths.script)
        .pipe(plugins.jshint())
        .pipe(plugins.jshint.reporter()); // 输出检查结果
});

gulp.task('imagemin', function () {
    return gulp.src('./images/*.{png,jpg,gif,ico}')
        .pipe(plugins.imagemin({
            progressive: true,
            use: [pngquant()] //使用pngquant来压缩png图片(无损压缩)
        }))
        .pipe(gulp.dest('./dist/images'));
});

gulp.task('minifyIndexHtml', function () {
    return gulp.src('./index.html')
        .pipe(plugins.minifyHtml())
        .pipe(gulp.dest('./dist/'))
        .pipe(plugins.livereload());
});

gulp.task('minifyHtml', function () {
    return gulp.src('./tpls/**/*.html')
        .pipe(plugins.minifyHtml())
        .pipe(gulp.dest('./dist/tpls'))
        .pipe(plugins.livereload());
});

gulp.task('minifyCss', function () {
    return gulp.src('./css/*.css')
        .pipe(plugins.minifyCss())
        .pipe(plugins.rename({suffix: '.min'}))
        .pipe(gulp.dest('./dist/css'))
        .pipe(plugins.livereload());
});

gulp.task('uglifyJs', function () {
    return gulp.src(paths.script)
        .pipe(plugins.uglify())
        .pipe(plugins.rename({suffix: '.min'}))
        /*.pipe(plugins.concat('all.min.js'))*/
        .pipe(gulp.dest('./dist/js'))
        .pipe(plugins.livereload());
});

gulp.task('watch', function () {
    plugins.livereload.listen();
    gulp.watch('./css/*.css', ['minifyCss']);
    gulp.watch('./tpls/**/*.html', ['minifyHtml']);
    gulp.watch('./index.html', ['minifyIndexHtml']);
    //gulp.watch(paths.script, ['uglifyJs']);
});
/*gulp.task('clean', function () {
    return gulp.src('./dist', {read: false})
        .pipe(plugins.clean());
});*/
gulp.task('build', ['watch', 'imagemin', 'minifyCss', 'minifyIndexHtml', 'minifyHtml', 'jsHint','uglifyJs']);

gulp.task('default'/*,['clean']*/, function () {
    return gulp.start('build');
});