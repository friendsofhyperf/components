import OpenAI from "openai";
import { promises as fs } from 'fs';
import path from 'path';

const token = process.env["GITHUB_TOKEN"];
const endpoint = "https://models.inference.ai.azure.com";
const modelName = "o1-mini";

async function translateFiles() {
  const client = new OpenAI({
    baseURL: endpoint,
    apiKey: token,
    dangerouslyAllowBrowser: true
  });

  const docsPath = path.join(process.cwd(), 'docs/zh-cn');

  async function translateFile(filePath) {
    const content = await fs.readFile(filePath, 'utf8');
    const response = await client.chat.completions.create({
      messages: [
        { role: "user", content: "You are a professional translator. Translate the following Markdown content from Chinese to English. Preserve all Markdown formatting." },
        { role: "user", content: content }
      ],
      model: modelName
    });

    const translatedContent = response.choices[0].message.content;
    const englishPath = filePath.replace('/zh-cn/', '/en/');
    await fs.mkdir(path.dirname(englishPath), { recursive: true });
    await fs.writeFile(englishPath, translatedContent);
    console.log(`Translated: ${filePath} -> ${englishPath}`);
  }

  async function processDirectory(dirPath) {
    const files = await fs.readdir(dirPath, { withFileTypes: true });

    for (const file of files) {
      const fullPath = path.join(dirPath, file.name);

      if (file.isDirectory()) {
        await processDirectory(fullPath);
      } else if (file.name.endsWith('.md')) {
        await translateFile(fullPath);
      }
    }
  }

  await processDirectory(docsPath);
}

translateFiles().catch(console.error);
