/**
 * @file
 * Task: Compile: Styleguide.
 */

module.exports = function (gulp, plugins, options) {
  'use strict';

  gulp.task('compile:styleguide', function (cb) {
    return plugins.kss(options.styleGuide, cb);
  });
};
