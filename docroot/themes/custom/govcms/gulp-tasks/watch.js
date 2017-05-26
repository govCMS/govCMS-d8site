/**
 * @file
 * Task: Watch.
 */

module.exports = function (gulp, plugins, options) {
  'use strict';

  gulp.task('watch', ['watch:sass', 'watch:styleguide', 'watch:js']);

  gulp.task('watch:js', function () {
    return gulp.watch([
      options.js.files
    ], ['lint:js', 'lint:css']);
  });

  gulp.task('watch:sass', function () {
    return gulp.watch([
      options.sass.files
    ], ['compile:sass', 'minify:css']);
  });

  gulp.task('watch:styleguide', function () {
    return gulp.watch([
      options.sass.files
    ], ['compile:styleguide']);
  });
};
