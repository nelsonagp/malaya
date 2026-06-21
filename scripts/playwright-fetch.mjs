// Descarga el HTML ya renderizado de una URL usando Chromium real vía
// Playwright. Existe porque algunos sitios (loteriadebogota.com,
// megamillions.com) bloquean las peticiones de Guzzle/curl a nivel de
// fingerprint TLS (JA3) — el handshake de OpenSSL que usa PHP-curl no pasa
// el challenge de Cloudflare, pero el de un Chromium real sí. Invocado desde
// BaseLotteryScraper::getHtmlViaBrowser().
//
// Uso: node playwright-fetch.mjs <url> [waitForSelector]
// Salida: HTML completo por stdout. Errores por stderr + exit code 1.

import { chromium } from 'playwright';

const [, , url, waitForSelector] = process.argv;

if (!url) {
    console.error('Falta el argumento <url>');
    process.exit(1);
}

const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36';

try {
    const browser = await chromium.launch();
    const page = await browser.newPage({ userAgent: USER_AGENT });

    await page.goto(url, { waitUntil: 'domcontentloaded', timeout: 30_000 });

    if (waitForSelector) {
        await page.waitForSelector(waitForSelector, { timeout: 15_000 });
    }

    const html = await page.content();
    await browser.close();

    process.stdout.write(html);
} catch (error) {
    console.error(error.message);
    process.exit(1);
}
