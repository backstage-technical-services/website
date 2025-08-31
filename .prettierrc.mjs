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
    plugins: ['@prettier/plugin-php', '@shufo/prettier-plugin-blade'],
    overrides: [
        {
            files: '**/*.php',
            options: {
                parser: 'php',
                singleQuote: true,
            },
        },
        {
            files: '**/*.blade.php',
            options: {
                parser: 'blade',
                wrapAttributes: 'force-expand-multiline',
                wrapAttributesMinAttrs: 4,
                sortHtmlAttributes: 'idiomatic',
            },
        },
        {
            files: '**/*.scss',
            options: {
                singleQuote: false,
            },
        },
        {
            files: 'resources/views/contact/book/terms.blade.php',
            options: {
                parser: 'markdown',
            },
        },
    ],
};
