import OpenAI from "openai";
import { readdir, readFile, writeFile, mkdir } from 'fs/promises';
import path from 'path';

const endpoint = "https://api.deepseek.com";
const token = process.env["DEEPSEEK_API_KEY"];

const openai = new OpenAI({
    baseURL: endpoint,
    apiKey: token,
});

async function translateContent(content) {
    const completion = await openai.chat.completions.create({
        messages: [
            { role: "system", content: "You are a professional translator. Translate the following Chinese markdown content to English. Keep all markdown formatting intact." },
            { role: "user", content: content }
        ],
        model: "deepseek-chat",
    });
    return completion.choices[0].message.content;
}

async function translateFiles(srcDir, destDir) {
    try {
        const files = await readdir(srcDir, { recursive: true });

        for (const file of files) {
            if (!file.endsWith('.md')) continue;

            const srcPath = path.join(srcDir, file);
            const destPath = path.join(destDir, file);
            const destFolder = path.dirname(destPath);

            await mkdir(destFolder, { recursive: true });

            const content = await readFile(srcPath, 'utf8');
            const translatedContent = await translateContent(content);
            const finalContent = translatedContent.replace(/\/zh-cn\//g, '/en/');
            await writeFile(destPath, finalContent);

            console.log(`Translated: ${file}`);
        }
    } catch (error) {
        console.error('Translation error:', error);
    }
}

translateFiles('docs/zh-cn', 'docs/en');