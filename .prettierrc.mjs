/**
 * @see https://prettier.io/docs/configuration
 * @type {import("prettier").Config}
 */
export default {
    singleQuote: true,
    trailingComma: 'all',
    printWidth: 120,
    semi: true,
    tabWidth: 4,
    useTabs: false,
    overrides: [
        {
            files: '**/*.php',
            options: {
                plugins: ['@prettier/plugin-php'],
                singleQuote: true,
            },
        },
        {
            files: '**/*.scss',
            options: {
                singleQuote: false,
            },
        },
    ],
};
