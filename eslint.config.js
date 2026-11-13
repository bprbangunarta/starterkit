import js from '@eslint/js';
import jsxA11y from 'eslint-plugin-jsx-a11y';
import reactHooks from 'eslint-plugin-react-hooks';
import globals from 'globals';
import tseslint from 'typescript-eslint';

// Rules that need a parser (hooks, accessibility, promises). Mechanical house rules live in scripts/check-standards.mjs.
export default tseslint.config(
    // Wayfinder output is generated: it is regenerated, never edited or linted.
    { ignores: ['vendor', 'node_modules', 'public', 'storage', 'resources/js/actions', 'resources/js/routes', 'resources/js/wayfinder'] },
    {
        files: ['resources/js/**/*.{ts,tsx}'],
        extends: [js.configs.recommended, ...tseslint.configs.recommendedTypeChecked, jsxA11y.flatConfigs.recommended, reactHooks.configs.flat.recommended],
        languageOptions: {
            globals: globals.browser,
            parserOptions: { projectService: true, tsconfigRootDir: import.meta.dirname },
        },
        rules: {
            '@typescript-eslint/no-explicit-any': 'error',
            '@typescript-eslint/no-floating-promises': ['error', { ignoreVoid: true }],
            '@typescript-eslint/no-misused-promises': ['error', { checksVoidReturn: { attributes: false } }],
            '@typescript-eslint/consistent-type-imports': 'error',
            'react-hooks/rules-of-hooks': 'error',
            'react-hooks/exhaustive-deps': 'error',
            // Loading on mount (network probe) is the sanctioned effect; this React-Compiler-oriented rule flags it.
            'react-hooks/set-state-in-effect': 'off',
            // Dialogs focus their first field on purpose; the dialog focus trap (Radix) manages the rest.
            'jsx-a11y/no-autofocus': 'off',
        },
    },
);
