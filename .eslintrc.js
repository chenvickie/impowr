module.exports = {
  root: true,
  parser: "babel-eslint",
  parserOptions: {
    ecmaVersion: 2018,
    sourceType: "module",
    ecmaFeatures: {
      legacyDecorators: true,
    },
  },
  plugins: ["ember"],
  extends: ["eslint:recommended", "plugin:ember/recommended"],
  env: {
    browser: true,
  },
  rules: {
    "ember/no-jquery": "off",
    "ember/no-observers": "off",
    "ember/no-new-mixins": "off",
    "ember/no-global-jquery": "off",
    "no-console": "off",
    indentation: "off",
    indent: "off",
    "block-indentation": "off",
    "no-unused-block-params": "off",
    "no-inline-styles": "off",
    "require-yield": "off",
    "no-invalid-interactive": "off",
    "no-inline-styles": "off",
    "self-closing-void-elements": "off",
  },
  overrides: [
    // node files
    {
      files: [
        ".eslintrc.js",
        ".template-lintrc.js",
        "ember-cli-build.js",
        "testem.js",
        "blueprints/*/index.js",
        "config/**/*.js",
        "lib/*/index.js",
        "server/**/*.js",
      ],
      parserOptions: {
        sourceType: "script",
      },
      env: {
        browser: false,
        node: true,
      },
      plugins: ["node"],
      rules: Object.assign(
        {},
        require("eslint-plugin-node").configs.recommended.rules,
        {
          // add your custom rules and overrides for node files here

          // this can be removed once the following is fixed
          // https://github.com/mysticatea/eslint-plugin-node/issues/77
          "node/no-unpublished-require": "off",
        }
      ),
    },
  ],
};
