/**
 * @file
 * Task: Build.
 */

module.exports = function (gulp, plugins, options) {
  'use strict';

  gulp.task('build', [
    'compile:sass',
    'minify:css',
    'compile:styleguide'
  ], function (cb) {
  // Run linting last, otherwise its output gets lost.
    plugins.runSequence(['lint:js-gulp', 'lint:js-with-fail', 'lint:css-with-fail'], cb);
  });

  gulp.task('build:dev', [
    'compile:sass',
    'minify:css',
    'compile:styleguide'
  ], function (cb) {
    // Run linting last, otherwise its output gets lost.
    plugins.runSequence(['lint:js-gulp', 'lint:js', 'lint:css'], cb);
  });
};
