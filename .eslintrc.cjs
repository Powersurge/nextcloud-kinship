module.exports = {
	extends: [
		'@nextcloud',
	],
	rules: {
		'jsdoc/require-jsdoc': 'off',
		'vue/first-attribute-linebreak': 'off',
		'n/no-missing-import': 'off',
		'@typescript-eslint/no-explicit-any': 'off',
		'jsdoc/require-param-description': 'off',
	},
	settings: {
		'import/resolver': {
			typescript: {
				project: './tsconfig.json',
			},
		},
	},
	overrides: [
		{
			// Parse `<script lang="ts">` blocks in Vue SFCs with the TypeScript
			// parser while keeping the Babel parser for plain JS blocks.
			files: ['*.vue'],
			// The @nextcloud config extends the Vue 2 preset; override with the
			// Vue 3 preset since this project runs Vue 3.
			extends: ['plugin:vue/vue3-recommended'],
			parserOptions: {
				parser: {
					ts: '@typescript-eslint/parser',
					js: '@babel/eslint-parser',
					'<template>': 'espree',
				},
			},
			rules: {
				// Preserve the @nextcloud config's intentional relaxations, which
				// the Vue 3 preset above would otherwise re-enable.
				'vue/multi-word-component-names': 'off',
				'vue/first-attribute-linebreak': 'off',
				// Vue 2-only rule from the base preset; v-model arguments are valid
				// in Vue 3.
				'vue/no-v-model-argument': 'off',
			},
		},
	],
}
