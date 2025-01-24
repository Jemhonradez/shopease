module.exports = {
  proxy: "localhost:8000",
  files: ["**/*.php", "build/*.css", "assets/js/*.js"],
  injectChanges: true,
  open: true,
  notify: false,
  port: 3000,
  watchOptions: {
    debounceDelay: 1000,
  },
};
