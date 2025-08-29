#!/usr/bin/env node
/*
  Format only pure HTML fragments in a PHP file.
  - Splits by PHP open/close tags.
  - Formats non-PHP blocks with Prettier (parser: html).
  - Leaves PHP blocks intact.
*/

const fs = require('fs');
const path = require('path');
const prettier = require('prettier');

function usage() {
  console.error('Usage: node scripts/format-php-html.js <file.php>');
}

async function formatHtml(html, filePath) {
  const config = await prettier.resolveConfig(filePath).catch(() => null);
  return prettier.format(html, {
    ...config,
    parser: 'html',
  });
}

function splitPhpHtml(content) {
  const segments = [];
  let i = 0;
  const n = content.length;

  while (i < n) {
    const open = content.indexOf('<?', i);
    if (open === -1) {
      segments.push({ type: 'html', text: content.slice(i) });
      break;
    }
    if (open > i) {
      segments.push({ type: 'html', text: content.slice(i, open) });
    }
    const close = content.indexOf('?>', open + 2);
    if (close === -1) {
      // no closing tag, treat rest as PHP
      segments.push({ type: 'php', text: content.slice(open) });
      break;
    }
    segments.push({ type: 'php', text: content.slice(open, close + 2) });
    i = close + 2;
  }
  return segments;
}

async function run() {
  const file = process.argv[2];
  if (!file || !file.endsWith('.php')) {
    usage();
    process.exit(2);
  }
  const abs = path.resolve(file);
  if (!fs.existsSync(abs)) {
    console.error(`File not found: ${abs}`);
    process.exit(2);
  }

  const src = fs.readFileSync(abs, 'utf8');
  const segments = splitPhpHtml(src);

  let out = '';
  for (const seg of segments) {
    if (seg.type === 'html') {
      let trimmed = seg.text;
      // Pre-normalize common broken patterns to help the HTML parser:
      // - a lone '<' on its own line before a tag name
      // - leading spaces before a tag name preceded by a newline
      // These are non-semantic whitespace changes that make invalid HTML parsable.
      trimmed = trimmed.replace(/(^|\n)\s*<\s*\n\s*/g, '$1<');
      // Collapse excessive leading spaces before tag starts
      trimmed = trimmed.replace(/(^|\n)\s+(<\/?[a-zA-Z!])/g, '$1$2');
      // Skip tiny/blank HTML blocks to avoid noisy diffs
      if (trimmed.trim().length === 0) {
        out += seg.text;
        continue;
      }
      try {
        const formatted = await formatHtml(trimmed, abs);
        out += formatted;
      } catch (e) {
        // If HTML parser fails for some edge case, keep original block
        out += seg.text;
      }
    } else {
      out += seg.text;
    }
  }

  if (out !== src) {
    fs.writeFileSync(abs, out, 'utf8');
    console.log(`Formatted HTML blocks in: ${file}`);
  } else {
    console.log(`No HTML block changes: ${file}`);
  }
}

run().catch((err) => {
  console.error(err);
  process.exit(1);
});
