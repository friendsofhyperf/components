import OpenAI from "openai";
import { readdir, readFile, writeFile, mkdir } from 'fs/promises';
import path from 'path';

const endpoint = "https://api.deepseek.com";
const token = process.env["DEEPSEEK_API_KEY"];
const MAX_CONCURRENT = 5; // 最大并发数
const MAX_RETRIES = 3; // 最大重试次数

const openai = new OpenAI({
    baseURL: endpoint,
    apiKey: token,
});

async function translateWithRetry(content, retries = 0) {
    try {
        const completion = await openai.chat.completions.create({
            messages: [
                { role: "system", content: "You are a professional translator. Translate the following Chinese markdown content to English. Keep all markdown formatting intact." },
                { role: "user", content: content }
            ],
            model: "deepseek-chat",
        });
        return completion.choices[0].message.content;
    } catch (error) {
        if (retries < MAX_RETRIES) {
            await new Promise(resolve => setTimeout(resolve, 1000 * (retries + 1)));
            return translateWithRetry(content, retries + 1);
        }
        throw error;
    }
}

async function processFile(srcPath, destPath) {
    const destFolder = path.dirname(destPath);
    await mkdir(destFolder, { recursive: true });
    
    const content = await readFile(srcPath, 'utf8');
    const translatedContent = await translateWithRetry(content);
    const finalContent = translatedContent.replace(/\/zh-cn\//g, '/en/');
    await writeFile(destPath, finalContent);
    console.log(`Translated: ${path.basename(srcPath)}`);
}

async function translateFiles(srcDir, destDir) {
    try {
        const files = await readdir(srcDir, { recursive: true });
        const mdFiles = files.filter(file => file.endsWith('.md'));
        
        // 将文件分批处理
        for (let i = 0; i < mdFiles.length; i += MAX_CONCURRENT) {
            const batch = mdFiles.slice(i, i + MAX_CONCURRENT);
            const promises = batch.map(file => {
                const srcPath = path.join(srcDir, file);
                const destPath = path.join(destDir, file);
                return processFile(srcPath, destPath).catch(error => {
                    console.error(`Error translating ${file}:`, error);
                });
            });
            
            await Promise.all(promises);
        }
        
        console.log('All translations completed!');
    } catch (error) {
        console.error('Translation error:', error);
    }
}

translateFiles('docs/zh-cn', 'docs/en');