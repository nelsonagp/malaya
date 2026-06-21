<?php

namespace App\Scrapers;

/**
 * megamillions.com bloquea la conexión (ERR_CONNECTION_RESET) desde el
 * entorno donde se construyó este módulo, tanto con curl nativo (Schannel)
 * como con Chromium real vía Playwright (reintentado 2026-06-20) — el
 * handshake TLS ni siquiera se completa. A diferencia de loteriadebogota.com
 * (bloqueo por fingerprint TLS, resuelto con BaseLotteryScraper::getHtmlViaBrowser()),
 * esto es un bloqueo de conexión/red (probablemente geo-bloqueo de IP), por lo
 * que un navegador real no lo soluciona desde aquí. Hay que probar desde el
 * servidor de producción (puede que no esté bloqueado con una IP distinta) o
 * conseguir un proxy con IP de EE.UU. Por ahora funciona como
 * ConfigurableScraper: configura date_selector/numbers_selector desde el
 * admin una vez que se pueda acceder al sitio y confirmar las clases CSS
 * reales.
 */
class MegaMillionsScraper extends ConfigurableScraper
{
}
