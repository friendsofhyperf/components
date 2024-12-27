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
  let combinedContent = '';

  async function combineFiles(dirPath) {
    const files = await fs.readdir(dirPath, { withFileTypes: true });

    for (const file of files) {
      const fullPath = path.join(dirPath, file.name);

      if (file.isDirectory()) {
        await combineFiles(fullPath);
      } else if (file.name.endsWith('.md')) {
        const content = await fs.readFile(fullPath, 'utf8');
        combinedContent += `<!-- {{${fullPath}}} -->\n${content}\n\n`;
      }
    }
  }

  await combineFiles(docsPath);

  const response = await client.chat.completions.create({
    messages: [
      { role: "system", content: "You are a professional translator. Translate the following Markdown content from Chinese to English. Preserve all Markdown formatting." },
      { role: "user", content: combinedContent }
    ],
    model: modelName
  });

  const translatedContent = response.choices[0].message.content;
  const sections = translatedContent.split(/<!--\s*\{\{(.*?)\}\}\s*-->/);

  for (let i = 1; i < sections.length; i += 2) {
    const filePath = sections[i];
    const content = sections[i + 1].trim();
    const englishPath = filePath.replace('/zh-cn/', '/en/');
    await fs.mkdir(path.dirname(englishPath), { recursive: true });
    await fs.writeFile(englishPath, content);
  }
}

translateFiles().catch(console.error);