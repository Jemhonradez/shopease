module.exports = {
  proxy: "localhost:4000",
  files: ["**/*.php", "css/*.css", "js/*.js"],
  injectChanges: true,
  open: true,
  notify: false,
  port: 3000,
  watchOptions: {
    debounceDelay: 1000,
  },
};
