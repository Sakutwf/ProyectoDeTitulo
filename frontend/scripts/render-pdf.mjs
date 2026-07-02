import { chromium } from 'playwright'
import fs from 'node:fs/promises'

const args = process.argv.slice(2)
const getArg = (name) => {
  const index = args.indexOf(name)
  return index >= 0 ? args[index + 1] : null
}

const input = getArg('--input')
const output = getArg('--output')
const format = getArg('--format') || 'A4'

if (!input || !output) {
  console.error('Missing --input or --output')
  process.exit(1)
}

const html = await fs.readFile(input, 'utf8')
const browser = await chromium.launch({ headless: true })

try {
  const page = await browser.newPage()
  await page.setContent(html, { waitUntil: 'networkidle' })
  await page.pdf({
    path: output,
    format,
    printBackground: true,
    preferCSSPageSize: true,
    margin: {
      top: '0',
      right: '0',
      bottom: '0',
      left: '0'
    }
  })
} finally {
  await browser.close()
}
