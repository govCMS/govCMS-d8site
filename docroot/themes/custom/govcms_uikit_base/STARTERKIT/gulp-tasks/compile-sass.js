/**
 * @file
 * Task: Compile: Sass.
 */

module.exports = function (gulp, plugins, options) {
  'use strict';

  gulp.task('compile:sass', function () {
    return gulp.src(options.sass.files)
      .pipe(plugins.plumber({
        errorHandler: function (e) {

          /* eslint-disable no-console */
          console.log(e.messageFormatted);

          /* eslint-enable no-console */
          this.emit('end');
        }
      }))
      .pipe(plugins.sourcemaps.init())
      .pipe(plugins.sassGlob())
      .pipe(plugins.sass({
        errLogToConsole: true,
        outputStyle: 'expanded'
      }))
      .pipe(plugins.autoprefixer({
        browsers: ['last 2 versions'],
        cascade: false
      }))
      .pipe(plugins.sourcemaps.write())
      .pipe(plugins.plumber.stop())
      .pipe(gulp.dest(options.sass.destination));
  });
};
