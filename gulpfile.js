var gulp = require("gulp");
var postcss = require("gulp-postcss");
var cssvars = require("postcss-simple-vars");
var nested = require("postcss-nested");
var cssImport = require("postcss-import");
var autoprefixer = require("autoprefixer");
var watch = require("gulp-watch");
var browserSync = requite("broswer-sync").create();

gulp.task("cssInject", ["styles"], function () {
  return gulp.src("styles/style.css").pipe(browserSync.stream());
});
