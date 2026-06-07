import { access, readFile, readdir } from 'node:fs/promises';
import path from 'node:path';
import process from 'node:process';
import { fileURLToPath } from 'node:url';

const docsRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '..');
const repositoryRoot = path.resolve(docsRoot, '..');
const locales = ['en', 'zh-cn', 'zh-hk', 'zh-tw'];

async function exists(filePath) {
    try {
        await access(filePath);
        return true;
    } catch {
        return false;
    }
}

async function markdownFiles(directory, prefix = '') {
    const entries = await readdir(directory, { withFileTypes: true });
    const files = [];

    for (const entry of entries) {
        const relativePath = path.join(prefix, entry.name);
        if (entry.isDirectory()) {
            files.push(...await markdownFiles(path.join(directory, entry.name), relativePath));
        } else if (entry.name.endsWith('.md')) {
            files.push(relativePath);
        }
    }

    return files.sort();
}

function difference(left, right) {
    const rightSet = new Set(right);
    return left.filter((item) => ! rightSet.has(item));
}

function headings(markdown) {
    let inFence = false;

    return markdown.split('\n').flatMap((line) => {
        if (/^(```|~~~)/.test(line)) {
            inFence = ! inFence;
            return [];
        }
        if (inFence || ! /^#{1,6} /.test(line)) {
            return [];
        }
        return [line.match(/^(#{1,6}) /)[1].length];
    });
}

function localMarkdownLinks(markdown) {
    return [...markdown.matchAll(/\[[^\]]*]\((?!https?:|mailto:|#)([^)\s]+)(?:\s+"[^"]*")?\)/g)]
        .map((match) => match[1]);
}

async function resolveDocumentationLink(sourceFile, link) {
    const linkWithoutHash = link.split('#')[0];
    if (! linkWithoutHash) {
        return true;
    }

    const target = linkWithoutHash.startsWith('/')
        ? path.join(docsRoot, linkWithoutHash)
        : path.resolve(path.dirname(sourceFile), linkWithoutHash);
    const candidates = path.extname(target) ? [target] : [`${target}.md`, path.join(target, 'index.md')];

    return (await Promise.all(candidates.map(exists))).some(Boolean);
}

const errors = [];
const localeFiles = Object.fromEntries(
    await Promise.all(locales.map(async (locale) => [locale, await markdownFiles(path.join(docsRoot, locale))])),
);
const referenceFiles = localeFiles['zh-cn'];

for (const locale of locales) {
    const missing = difference(referenceFiles, localeFiles[locale]);
    const extra = difference(localeFiles[locale], referenceFiles);

    if (missing.length > 0) {
        errors.push(`${locale} is missing: ${missing.join(', ')}`);
    }
    if (extra.length > 0) {
        errors.push(`${locale} has extra files: ${extra.join(', ')}`);
    }
}

const srcRoot = path.join(repositoryRoot, 'src');
if (await exists(srcRoot)) {
    const componentDirectories = (await readdir(srcRoot, { withFileTypes: true }))
        .filter((entry) => entry.isDirectory() && entry.name !== '.github')
        .map((entry) => `${entry.name}.md`)
        .sort();
    const componentPages = (await readdir(path.join(docsRoot, 'zh-cn', 'components')))
        .filter((entry) => entry.endsWith('.md') && entry !== 'index.md')
        .sort();

    const undocumentedComponents = difference(componentDirectories, componentPages);
    const unknownComponentPages = difference(componentPages, componentDirectories);
    if (undocumentedComponents.length > 0) {
        errors.push(`Components without documentation: ${undocumentedComponents.join(', ')}`);
    }
    if (unknownComponentPages.length > 0) {
        errors.push(`Documentation without a component: ${unknownComponentPages.join(', ')}`);
    }

    for (const componentPage of componentPages) {
        const component = path.basename(componentPage, '.md');
        const installationCommand = `composer require friendsofhyperf/${component}`;

        for (const locale of locales) {
            const filePath = path.join(docsRoot, locale, 'components', componentPage);
            if (! await exists(filePath)) {
                continue;
            }
            const markdown = await readFile(filePath, 'utf8');
            if (! markdown.includes(installationCommand)) {
                errors.push(`${locale}/components/${componentPage} is missing: ${installationCommand}`);
            }
        }
    }
}

for (const relativePath of referenceFiles) {
    const structures = {};

    for (const locale of locales) {
        const filePath = path.join(docsRoot, locale, relativePath);
        if (! await exists(filePath)) {
            structures[locale] = null;
            continue;
        }
        const markdown = await readFile(filePath, 'utf8');
        structures[locale] = headings(markdown);

        for (const link of localMarkdownLinks(markdown)) {
            if (! await resolveDocumentationLink(filePath, link)) {
                errors.push(`${path.relative(docsRoot, filePath)} has a broken link: ${link}`);
            }
        }
    }

    const referenceStructure = structures['zh-cn'].join(',');
    for (const locale of locales) {
        if (structures[locale] === null) {
            continue;
        }
        if (structures[locale].join(',') !== referenceStructure) {
            errors.push(`${locale}/${relativePath} heading structure differs from zh-cn`);
        }
    }
}

const navigationRoot = path.join(docsRoot, '.vitepress', 'src');
for (const locale of locales) {
    for (const fileName of ['nav.ts', 'sidebars.ts']) {
        const filePath = path.join(navigationRoot, locale, fileName);
        const content = await readFile(filePath, 'utf8');
        const links = [...content.matchAll(/link:\s*['"]([^'"]+)['"]/g)]
            .map((match) => match[1])
            .filter((link) => link.startsWith('/'));

        for (const link of links) {
            if (! await resolveDocumentationLink(filePath, link)) {
                errors.push(`${path.relative(docsRoot, filePath)} has a broken link: ${link}`);
            }
        }
    }
}

if (errors.length > 0) {
    console.error('Documentation checks failed:\n');
    for (const error of errors) {
        console.error(`- ${error}`);
    }
    process.exit(1);
}

console.log(`Documentation checks passed for ${referenceFiles.length} pages in ${locales.length} locales.`);
