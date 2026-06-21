# PROMPT PARA CLAUDE CODE — malayaseamisuerte.com
# Sitio web de resultados de loterías Colombia y exterior
# Stack: Laravel 11 + PHP 8.2 + PostgreSQL (local XAMPP) + Node.js

---

## ENTORNO LOCAL

- **SO:** Windows con XAMPP
- **PHP:** 8.2.12 (vía XAMPP)
- **Base de datos:** PostgreSQL local — host: localhost, puerto: 5432, usuario: postgres, clave: abc123456
- **Composer:** instalado globalmente
- **Node.js:** instalado globalmente
- **Carpeta del proyecto:** C:\xampp\htdocs\malaya
- **NO usar Docker**
- **NO usar MySQL** — solo PostgreSQL

---

## INSTRUCCIONES INICIALES

Al comenzar, ejecuta estos comandos en orden:

```bash
cd C:\xampp\htdocs
composer create-project laravel/laravel malaya
cd malaya
composer require laravel/socialite
composer require guzzlehttp/guzzle
composer require symfony/dom-crawler
composer require symfony/css-selector
npm install
npm install bootstrap @popperjs/core alpinejs
npm install -D sass
```

Configura el archivo `.env` con:
```
APP_NAME="Malaya sea mi suerte"
APP_URL=http://localhost/malaya/public
APP_DEBUG=true

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=malaya_db
DB_USERNAME=postgres
DB_PASSWORD=abc123456

QUEUE_CONNECTION=database
CACHE_DRIVER=file
SESSION_DRIVER=file
```

Luego crea la base de datos ejecutando en psql:
```sql
CREATE DATABASE malaya_db;
```

---

## STACK TECNOLÓGICO

- **Backend:** Laravel 11 con PHP 8.2
- **Base de datos:** PostgreSQL 16
- **ORM:** Eloquent
- **Frontend:** Blade templates + Alpine.js + **Bootstrap 5.3**
- **Scraping:** Guzzle HTTP + Symfony DomCrawler
- **Autenticación social:** Laravel Socialite (Google, Facebook, Apple, Instagram/Meta)
- **Jobs/Colas:** Laravel Queue con driver "database" (sin Redis)
- **Scheduler:** Laravel Task Scheduling
- **Cache:** File cache (sin Redis)
- **Accesibilidad:** cumplimiento WCAG 2.1 nivel AA en todo el sitio (ver sección dedicada)
- **SEO:** estrategia completa on-page, técnica y de datos estructurados (ver sección dedicada)

---

## ESTRUCTURA DE BASE DE DATOS (PostgreSQL)

Crea migraciones para estas tablas:

```sql
-- Usuarios
users (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name VARCHAR(255),
  email VARCHAR(255) UNIQUE,
  email_verified_at TIMESTAMP,
  password VARCHAR(255) NULLABLE,
  avatar_url TEXT,
  role VARCHAR(20) DEFAULT 'user',  -- 'admin' o 'user'
  is_active BOOLEAN DEFAULT true,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
)

-- Cuentas sociales vinculadas
social_accounts (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID REFERENCES users(id) ON DELETE CASCADE,
  provider VARCHAR(50),
  provider_id VARCHAR(255),
  access_token TEXT,
  refresh_token TEXT,
  token_expires_at TIMESTAMP,
  created_at TIMESTAMP,
  UNIQUE(provider, provider_id)
)

-- Loterías
lotteries (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  name VARCHAR(255) NOT NULL,
  slug VARCHAR(255) UNIQUE NOT NULL,
  country VARCHAR(100) DEFAULT 'Colombia',
  country_code CHAR(2) DEFAULT 'CO',
  logo_url TEXT,
  website_url TEXT,
  results_url TEXT,
  scraper_class VARCHAR(255),
  scraper_config JSONB,
  draw_schedule JSONB,
  draw_frequency VARCHAR(50),
  number_count INT DEFAULT 4,
  number_range_min INT DEFAULT 0,
  number_range_max INT DEFAULT 9999,
  has_series BOOLEAN DEFAULT false,
  has_fractions BOOLEAN DEFAULT false,
  prize_info TEXT,
  is_active BOOLEAN DEFAULT true,
  last_scraped_at TIMESTAMP,
  scrape_error TEXT,
  affiliate_url TEXT,
  display_order INT DEFAULT 0,
  created_at TIMESTAMP,
  updated_at TIMESTAMP
)

-- Resultados de sorteos
lottery_results (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  lottery_id UUID REFERENCES lotteries(id) ON DELETE CASCADE,
  draw_date DATE NOT NULL,
  draw_number INT,
  numbers JSONB NOT NULL,
  prize_breakdown JSONB,
  jackpot_amount DECIMAL(15,2),
  currency VARCHAR(10) DEFAULT 'COP',
  is_verified BOOLEAN DEFAULT false,
  source_url TEXT,
  raw_data JSONB,
  scraped_at TIMESTAMP,
  created_at TIMESTAMP,
  UNIQUE(lottery_id, draw_date)
)

-- Estadísticas precalculadas
number_statistics (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  lottery_id UUID REFERENCES lotteries(id) ON DELETE CASCADE,
  number VARCHAR(20) NOT NULL,
  total_appearances INT DEFAULT 0,
  last_appeared_date DATE,
  days_since_last_appearance INT,
  appearance_frequency DECIMAL(5,4),
  updated_at TIMESTAMP,
  UNIQUE(lottery_id, number)
)

-- Banners publicitarios
ad_banners (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  position VARCHAR(50),
  image_url TEXT,
  link_url TEXT,
  alt_text VARCHAR(255),
  advertiser_name VARCHAR(255),
  starts_at DATE,
  ends_at DATE,
  is_active BOOLEAN DEFAULT true,
  click_count INT DEFAULT 0,
  impression_count INT DEFAULT 0,
  price_per_month DECIMAL(10,2),
  created_at TIMESTAMP,
  updated_at TIMESTAMP
)

-- Alertas de usuario
user_alerts (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id UUID REFERENCES users(id) ON DELETE CASCADE,
  lottery_id UUID REFERENCES lotteries(id) ON DELETE CASCADE,
  numbers JSONB,
  notify_email BOOLEAN DEFAULT true,
  is_active BOOLEAN DEFAULT true,
  created_at TIMESTAMP
)

-- Logs de scraping
scrape_logs (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  lottery_id UUID REFERENCES lotteries(id),
  started_at TIMESTAMP,
  finished_at TIMESTAMP,
  status VARCHAR(50),
  results_found INT DEFAULT 0,
  error_message TEXT,
  created_at TIMESTAMP
)

-- Cola de trabajos (Laravel Queue con driver database)
jobs (tabla estándar de Laravel — generada con php artisan queue:table)
failed_jobs (tabla estándar de Laravel)
```

Crea índices en:
- `lottery_results(lottery_id, draw_date)`
- `number_statistics(lottery_id, total_appearances DESC)`
- `social_accounts(provider, provider_id)`

---

## MÓDULO DE SCRAPING

### Clase base abstracta
Crea `app/Scrapers/BaseLotteryScraper.php`:

```php
abstract class BaseLotteryScraper {
    protected string $lotterySlug;
    
    abstract public function fetchLatestResults(): array;
    abstract protected function parseResult(string $html): ?array;
    
    protected function getHtml(string $url): string {
        // Guzzle con 3 reintentos, backoff exponencial, timeout 30s
        // User-Agent de navegador real para evitar bloqueos
    }
    
    protected function storeResults(array $results): void {
        // Guarda en lottery_results, actualiza last_scraped_at
        // Registra en scrape_logs
    }
    
    protected function updateStatistics(string $lotteryId, array $numbers): void {
        // Actualiza number_statistics
    }
}
```

### Scrapers a implementar
Crea un scraper por cada lotería. Cada uno en `app/Scrapers/`:

1. **LoteriaBogotaScraper** → https://www.loteriadebogota.com/resultados/
2. **LoteriaMedellinScraper** → https://loteriademedellin.com.co/resultados/
3. **LoteriaManizalesScraper** → página oficial
4. **LoteriaCaucaScraper** → página oficial
5. **CruzRojaScraper** → https://loteriadelacruzroja.com.co
6. **LoteriaHuilaScraper** → página oficial
7. **LoteriaTolimaScraper** → página oficial
8. **BalootoScraper** → https://www.baloto.com/resultados (Baloto y Revancha)
9. **MegaMillionsScraper** → https://www.megamillions.com/winning-numbers/
10. **PowerballScraper** → https://www.powerball.com/winning-numbers

Para cada scraper: usar Symfony DomCrawler con selectores CSS, manejar el caso de que no haya resultados nuevos (comparar con último registro en BD), registrar cada intento en scrape_logs.

### Jobs
Crea `app/Jobs/ScrapeLotteryJob.php`:
```php
class ScrapeLotteryJob implements ShouldQueue {
    public int $tries = 3;
    public int $backoff = 300; // 5 minutos entre reintentos
    
    public function handle(): void {
        $scraper = ScraperFactory::make($this->lotterySlug);
        $scraper->fetchLatestResults();
    }
}
```

Crea `app/Jobs/RecalculateStatisticsJob.php` que recalcula frecuencias en `number_statistics`.

### Scheduler
En `app/Console/Kernel.php`:
```php
// Loterías según horario oficial
$schedule->job(new ScrapeLotteryJob('loteria-bogota'))->weeklyOn(6, '23:45');
$schedule->job(new ScrapeLotteryJob('loteria-medellin'))->weeklyOn(5, '22:30');
$schedule->job(new ScrapeLotteryJob('baloto'))->twiceWeekly(3, 6); // miércoles y sábado
$schedule->job(new ScrapeLotteryJob('mega-millions'))->twiceWeekly(2, 5);
$schedule->job(new ScrapeLotteryJob('powerball'))->twiceWeekly(1, 3);
// Estadísticas cada 2 horas
$schedule->job(new RecalculateStatisticsJob())->everyTwoHours();
```

Para correr el scheduler en Windows con XAMPP, incluir instrucción para configurar Tarea Programada de Windows que ejecute cada minuto:
```
php C:\xampp\htdocs\malaya\artisan schedule:run
```

---

## AUTENTICACIÓN SOCIAL

Configura `config/services.php` para Google, Facebook, Apple e Instagram.

### Lógica de vinculación en `SocialAuthController`:
1. Recibe callback del proveedor con datos del usuario
2. Busca `social_accounts` por `provider` + `provider_id`
3. Si existe → login con usuario vinculado
4. Si no existe pero email ya está en `users` → vincula la cuenta social (nueva fila en `social_accounts`)
5. Si email nuevo → crea `users` + `social_accounts`

Esto permite que una persona use Google, Facebook o Apple para entrar, y todas apuntan al mismo perfil.

Rutas:
```
GET  /auth/{provider}           → redirige al proveedor
GET  /auth/{provider}/callback  → procesa callback
GET  /perfil/cuentas           → ver cuentas vinculadas
POST /perfil/cuentas/{provider}/vincular
POST /perfil/cuentas/{provider}/desvincular
POST /logout
```

---

## DISEÑO VISUAL

**Identidad:** Energía colombiana. Colores principales:
- Amarillo fuerte `#FFD700`
- Rojo colombiano `#C8102E`  
- Verde esmeralda `#007847`
- Fondo oscuro elegante `#1A1A2E` para el header
- Fondo claro `#F8F9FA` para el cuerpo

**Importante — contraste de color:** verificar que cada combinación de texto/fondo cumpla mínimo 4.5:1 de contraste (WCAG AA). El amarillo `#FFD700` sobre fondo claro NO pasa este umbral para texto — usarlo solo como acento, fondo de badge con texto oscuro encima, o borde, nunca como color de texto sobre blanco.

**Tipografía:** Google Fonts — "Oswald" para titulares (bold, impactante), "Inter" para cuerpo. Tamaño base de texto mínimo 16px, nunca menor a 14px ni en notas al pie.

**Framework CSS:** Bootstrap 5.3 vía npm + Sass, personalizado con variables propias en `resources/sass/_variables.scss` (sobreescribir `$primary`, `$danger`, `$success`, `$font-family-base` antes de importar Bootstrap). Usar componentes nativos de Bootstrap (navbar, cards, badges, tablas, modales, formularios, paginación) en vez de reconstruirlos a mano — son accesibles por defecto y aceleran el desarrollo.

**Estilo:** Mobile-first (grid de Bootstrap: `col-12 col-md-6 col-lg-4`, etc). Cards con `shadow-sm`. Números de lotería en fuente monospace grande (`font-monospace fs-1`). Badges de colores por país (`badge bg-warning text-dark`, nunca `badge bg-warning` con texto blanco — falla contraste).

### Layout principal (app.blade.php)
- Header: `navbar navbar-expand-lg` con logo + nombre del sitio + navegación + botón "Iniciar sesión", colapsable en móvil con buen soporte de teclado (Bootstrap lo da por defecto, pero verificar foco visible)
- Banner publicitario horizontal (728x90) debajo del header, marcado como `role="complementary"` con `aria-label="Publicidad"`
- Contenido principal envuelto en `<main id="contenido-principal">` 
- Footer con links y créditos
- Sin sidebar en móvil, sidebar (`col-lg-3`) en desktop para las páginas de estadísticas
- Skip link al inicio del `<body>`: `<a class="visually-hidden-focusable" href="#contenido-principal">Saltar al contenido principal</a>`

---

## ACCESIBILIDAD (WCAG 2.1 nivel AA)

Esto no es opcional ni decorativo — es un requisito de cada pantalla que se construya. Verificar cada punto antes de considerar una vista terminada.

### Estructura semántica
- Un solo `<h1>` por página (el título principal de esa vista, no el nombre del sitio)
- Jerarquía de encabezados sin saltos (`h1` → `h2` → `h3`, nunca `h1` → `h3` directo)
- Usar etiquetas semánticas: `<nav>`, `<main>`, `<header>`, `<footer>`, `<article>` para cada card de resultado, `<section>` con `aria-labelledby` para bloques temáticos
- Listas de resultados como `<ul>`/`<ol>` reales, no `<div>` simulando lista

### Navegación por teclado
- Todo elemento interactivo (links, botones, inputs, dropdowns del admin) debe ser alcanzable con Tab en orden lógico
- Foco visible siempre — nunca usar `outline: none` sin reemplazarlo por un estilo de foco igual o más visible (Bootstrap ya da esto, no sobreescribir el `:focus-visible`)
- Modales (Bootstrap modal) deben atrapar el foco dentro mientras están abiertos y devolverlo al elemento que los abrió al cerrar
- Skip link funcional al contenido principal (ver layout arriba)

### Formularios (login, perfil, admin)
- Cada `<input>` con su `<label for="id">` asociado, nunca placeholder como único indicador
- Mensajes de error de validación vinculados con `aria-describedby` al campo correspondiente
- Campos requeridos marcados con `aria-required="true"` además de la marca visual
- Usar `<fieldset>` + `<legend>` para grupos relacionados (ej: checkboxes de días de sorteo en el admin)

### Imágenes y contenido visual
- Todo `<img>` con `alt` descriptivo (logos de loterías: `alt="Logo Lotería de Bogotá"`); imágenes puramente decorativas con `alt=""`
- Gráficos de Chart.js: incluir tabla de datos equivalente oculta visualmente (`visually-hidden`) pero disponible para lectores de pantalla, o un resumen en texto de la tendencia principal
- Iconos sin texto visible (ej: botón de cerrar) con `aria-label` descriptivo

### Contraste y tamaños
- Mínimo 4.5:1 para texto normal, 3:1 para texto grande (≥24px) o negrita ≥19px, según la tabla de colores ya definida arriba
- Área táctil mínima de 44x44px para botones e iconos clicables en móvil
- No depender solo del color para transmitir información (ej: número "caliente" vs "frío" en estadísticas: usar también ícono o texto, no solo rojo/azul)

### Anuncios dinámicos (Alpine.js / AJAX)
- El generador de números y los resultados que se actualizan sin recargar la página deben anunciarse a lectores de pantalla con una región `aria-live="polite"` 
- Estados de carga ("Cargando resultados...") también dentro de la región viva

### Pruebas
- Antes de dar por terminada cada vista pública, correr el sitio mentalmente con solo teclado (sin mouse) y verificar que todo es operable
- Sugerir al usuario instalar la extensión de navegador **axe DevTools** o **WAVE** para auditar cada página antes de publicarla

---

## SEO (posicionamiento en buscadores)

El tráfico orgánico es la fuente principal de visitas para un sitio de resultados de lotería — la gente busca "resultado lotería de Bogotá hoy" en Google constantemente. El SEO no es un extra, es la base del modelo de negocio.

### SEO técnico

- **URLs limpias y descriptivas:** `/loteria/loteria-de-bogota` no `/loteria?id=4`. Usar siempre el slug.
- **Velocidad de carga:** comprimir imágenes de logos al subirlas (Laravel `Intervention/Image`), lazy-loading nativo (`loading="lazy"`) en imágenes fuera del viewport inicial, minificar CSS/JS vía Vite en producción (`npm run build`)
- **Sitemap.xml dinámico** en `/sitemap.xml` — debe regenerarse incluyendo cada lotería y, opcionalmente, páginas de resultados por fecha si se vuelven indexables. Generar con un comando artisan programado (`malaya:generar-sitemap`) que corra diariamente
- **robots.txt** permitiendo crawling de páginas públicas, bloqueando `/admin`, `/perfil`, `/api`
- **Canonical tags** (`<link rel="canonical">`) en cada página, especialmente importante en `/resultados` con filtros (evitar contenido duplicado por combinaciones de query params)
- **HTTPS obligatorio** en producción con redirect 301 desde HTTP
- **Responsive y mobile-friendly** (ya cubierto por Bootstrap mobile-first) — Google prioriza indexación mobile-first
- **Tiempos de respuesta del servidor:** cachear en archivo (`Cache::remember`) los resultados y estadísticas que no cambian más de una vez al día, para no recalcular en cada visita

### SEO on-page

- **Título único por página** (`<title>`), patrón: `Resultado Lotería de Bogotá hoy [fecha] | Malaya sea mi suerte` — incluir la lotería y que suene a búsqueda natural
- **Meta description única** por página (150-160 caracteres), generada dinámicamente con el dato más reciente: `Resultado de la Lotería de Bogotá del [fecha]: número [XXXX], serie [XXX]. Consulta el historial completo y estadísticas.`
- **Un solo `<h1>`** que coincida en intención con el título (ver sección de accesibilidad)
- **Contenido único por lotería** — no copiar texto descriptivo idéntico entre páginas de loterías distintas; cada una con su propia introducción breve (horario de sorteo, historia corta, cómo jugar)
- **Texto ancla descriptivo** en enlaces internos: "Ver resultados de Lotería de Medellín" no "haz clic aquí"
- **Breadcrumbs** visibles y marcados con datos estructurados (ver abajo): Inicio > Resultados > Lotería de Bogotá

### Datos estructurados (Schema.org / JSON-LD)

Implementar en cada página de lotería un bloque `<script type="application/ld+json">` con:
- Tipo `Organization` para datos del sitio en el layout principal
- Tipo `BreadcrumbList` en cada página interna
- Tipo `FAQPage` en una sección de preguntas frecuentes por lotería (ej: "¿A qué hora juega la Lotería de Bogotá?", "¿Cuánto paga el premio mayor?") — esto es además contenido valioso para SEO y para el usuario
- Considerar `Dataset` o `Table` para los históricos de resultados si Google Search Console muestra oportunidad

### Open Graph y redes sociales

- `og:title`, `og:description`, `og:image` (usar el logo de la lotería o una imagen genérica del resultado más reciente generada dinámicamente), `og:url`, `og:type=website`
- `twitter:card=summary_large_image` con los mismos datos
- Esto mejora cómo se ve el link al compartirse en WhatsApp/Facebook, canal de distribución importante para este tipo de contenido en Colombia

### Contenido que atrae búsquedas (preparar la infraestructura, no necesariamente todo el copy)

- Página dedicada por lotería con sección de preguntas frecuentes (estructura ya lista para que el cliente la llene)
- Considerar un blog simple (`/blog`) más adelante para artículos tipo "Cómo se calculan las probabilidades en el Baloto" — deja la tabla `posts` preparada en el roadmap pero no es prioritario en esta primera fase
- Página "Acerca de" y "Cómo funciona" — Google valora E-E-A-T (experiencia, pericia, autoridad, confianza), especialmente importante en sitios relacionados con juegos de azar/dinero

### Monitoreo

- Dejar preparada la integración de **Google Search Console** (solo requiere meta tag de verificación, agregar campo en `/admin/configuracion`)
- Dejar preparada integración de **Google Analytics 4** o alternativa como Plausible (campo de tracking ID en configuración)

---

## PÁGINAS PÚBLICAS

### 1. Homepage `/` (`HomeController@index`)
- Hero: "Los resultados de todas las loterías de Colombia en un solo lugar"
- Sección "Próximos sorteos" — las 3 loterías con sorteo más cercano
- Grid de "Últimos resultados" — card por lotería con el resultado más reciente
- Sección "Números de la suerte hoy" — generador con Alpine.js, sin backend
- Banner de publicidad (slot configurado desde admin)

### 2. Página de lotería `/loteria/{slug}` (`LotteryController@show`)
- Header con logo, nombre, país, frecuencia del sorteo
- Último resultado destacado (número grande, fecha)
- Tabla de últimos 20 resultados
- Sección "Estadísticas rápidas": top 5 más frecuentes y top 5 menos frecuentes
- Botón "Ver estadísticas completas"
- Link de afiliado si está configurado: "Compra tu billete aquí →"

### 3. Resultados `/resultados` (`ResultsController@index`)
- Filtros por: país (Colombia / Internacional), lotería específica, rango de fechas
- Lista paginada (20 por página), ordenada por fecha descendente
- Exportar a CSV (solo para usuarios registrados)

### 4. Estadísticas `/estadisticas/{slug}` (`StatisticsController@show`)
- Tabla completa de todos los números con columnas: Número, Veces que ha salido, Última vez, Días sin salir, Frecuencia %
- Ordenable por cualquier columna
- Gráfico de barras (Chart.js CDN) de top 20 más frecuentes
- Filtro por rango de fechas

### 5. Búsqueda `/buscar?numero=1234`
- Muestra en qué loterías ha salido ese número
- Fechas y sorteos específicos
- Frecuencia comparada entre loterías

### 6. Generador de números `/generador`
- Seleccionar lotería
- Elegir modo: "Aleatorio puro" o "Basado en estadísticas" (pondera números más frecuentes)
- Generar 1 a 5 combinaciones
- Todo en Alpine.js con una llamada AJAX a `/api/generar/{slug}`

### 7. Perfil `/perfil` (requiere auth)
- Datos personales
- Cuentas sociales vinculadas con botones conectar/desconectar
- Alertas configuradas

---

## PANEL DE ADMINISTRACIÓN `/admin`

Middleware que verifica `user->role === 'admin'`. Redirige a `/login` si no autenticado o a `/` si no es admin.

### Dashboard `/admin`
- Total loterías activas / inactivas
- Último scraping exitoso por lotería
- Total resultados en BD
- Usuarios registrados
- Clics en publicidad hoy

### Gestión de loterías `/admin/loterias`
- Tabla con: nombre, país, activa, último scraping, próximo sorteo, acciones
- **Crear nueva lotería** — formulario completo:
  - Nombre, slug (auto-generado), país, código país
  - Logo (subida de imagen a `storage/public/logos/`)
  - URL del sitio, URL de resultados para scraping
  - Seleccionar clase de scraper (dropdown de scrapers registrados en `config/scrapers.php`)
  - Editor de configuración del scraper (textarea JSON con validación)
  - Horario: días de la semana (checkboxes) + hora + zona horaria
  - Frecuencia: daily / weekly / biweekly / monthly
  - Número de cifras del resultado, rango mín/máx
  - ¿Tiene series? ¿Tiene fracciones?
  - URL de afiliado (opcional)
  - Orden de visualización
- **Botón "Probar scraper"** — ejecuta el scraper ahora, muestra resultado en modal sin guardar
- **Botón "Forzar scraping ahora"** — despacha el job inmediatamente
- **Ver logs** — últimos 50 intentos de scraping para esa lotería

### Gestión de resultados `/admin/resultados`
- Listado con filtros por lotería y fecha
- **Cargar resultado manual** — formulario para cuando el scraper falla
- Editar/eliminar resultados
- Marcar como verificado

### Publicidad `/admin/publicidad`
- CRUD de banners (imagen, link, posición, fechas)
- Ver estadísticas: impresiones y clics por banner
- Configurar ID de AdSense desde aquí

### Configuración `/admin/configuracion`
- Google AdSense Client ID y Slot IDs por posición
- Redes sociales del sitio
- Metadatos SEO globales
- Crear usuario administrador (formulario simple)

---

## RUTAS ADICIONALES

```
GET  /api/generar/{slug}     → genera números (JSON, público)
POST /ad/impresion/{id}      → registra impresión (JSON, público)
GET  /ad/clic/{id}           → registra clic y redirige al link del banner
GET  /sitemap.xml            → sitemap dinámico
```

---

## MONETIZACIÓN IMPLEMENTADA

### Google AdSense
- Variable `ADSENSE_CLIENT_ID` en `.env`
- Componente Blade `@include('components.adsense', ['slot' => 'header'])` 
- Los slot IDs se configuran desde `/admin/configuracion`
- Si `ADSENSE_CLIENT_ID` está vacío, muestra placeholder gris con texto "Espacio publicitario disponible — contacto@malayaseamisuerte.com"

### Banners directos
- Se sirven desde la tabla `ad_banners`
- Tracking de clics e impresiones
- Se muestran según `position`: `header_banner`, `sidebar`, `homepage_hero`, `footer`
- Respetan fechas `starts_at` / `ends_at`

### Links de afiliado
- En la página de cada lotería, si `lotteries.affiliate_url` tiene valor, mostrar botón "🎟️ Compra tu billete en línea" que abre el link en nueva pestaña
- Registrar clic en `scrape_logs` con tipo 'affiliate_click' para medir conversiones

---

## SEEDER DE LOTERÍAS

Crea `database/seeders/LotterySeeder.php` con estas loterías colombianas pre-cargadas:

```php
$lotteries = [
    ['name' => 'Lotería de Bogotá', 'slug' => 'loteria-bogota', 'country' => 'Colombia', 'draw_frequency' => 'weekly', 'draw_schedule' => ['days' => ['saturday'], 'time' => '22:30', 'timezone' => 'America/Bogota']],
    ['name' => 'Lotería de Medellín', 'slug' => 'loteria-medellin', ...],
    ['name' => 'Lotería del Cauca', 'slug' => 'loteria-cauca', ...],
    ['name' => 'Lotería de Manizales', 'slug' => 'loteria-manizales', ...],
    ['name' => 'Lotería del Huila', 'slug' => 'loteria-huila', ...],
    ['name' => 'Lotería del Tolima', 'slug' => 'loteria-tolima', ...],
    ['name' => 'Lotería de Cundinamarca', 'slug' => 'loteria-cundinamarca', ...],
    ['name' => 'Lotería del Meta', 'slug' => 'loteria-meta', ...],
    ['name' => 'Cruz Roja Colombiana', 'slug' => 'cruz-roja', ...],
    ['name' => 'Baloto', 'slug' => 'baloto', 'draw_frequency' => 'biweekly', ...],
    ['name' => 'Revancha Baloto', 'slug' => 'revancha-baloto', ...],
    ['name' => 'Chance', 'slug' => 'chance', 'draw_frequency' => 'daily', ...],
    ['name' => 'Mega Millions', 'slug' => 'mega-millions', 'country' => 'Estados Unidos', 'country_code' => 'US', ...],
    ['name' => 'Powerball', 'slug' => 'powerball', 'country' => 'Estados Unidos', 'country_code' => 'US', ...],
    ['name' => 'EuroMillones', 'slug' => 'euromillones', 'country' => 'Europa', 'country_code' => 'EU', ...],
];
```

También crear `AdminSeeder` que crea un usuario admin:
```
email: admin@malayaseamisuerte.com
password: Malaya2025!
role: admin
```

---

## COMANDOS ARTISAN PERSONALIZADOS

```bash
php artisan malaya:scrape {slug?}   # scraping manual de una o todas las loterías
php artisan malaya:stats {slug?}    # recalcula estadísticas
php artisan malaya:test-scraper {slug}  # prueba un scraper sin guardar
```

---

## COMANDOS PARA LEVANTAR EL PROYECTO

Incluir en el README.md:

```bash
# 1. Instalar dependencias
composer install
npm install

# 2. Configurar entorno
copy .env.example .env
php artisan key:generate

# 3. Crear tablas y datos iniciales
php artisan migrate
php artisan db:seed

# 4. Crear tablas de colas
php artisan queue:table
php artisan migrate

# 5. Generar link de storage
php artisan storage:link

# 6. Compilar assets
npm run build

# 7. Correr el worker de colas (dejar en una terminal)
php artisan queue:work

# 8. Acceder en el navegador
# http://localhost/malaya/public
# Admin: http://localhost/malaya/public/admin
# Usuario: admin@malayaseamisuerte.com
# Clave: Malaya2025!
```

---

## NOTAS IMPORTANTES

- Usar **español** en toda la interfaz
- El sitio debe ser **mobile-first** con Bootstrap 5.3, completamente responsive
- Cada vista nueva que se construya debe revisarse contra la sección de Accesibilidad y la sección de SEO antes de darse por terminada — no son un paso final, son parte de la definición de "hecho" de cada pantalla
- Todos los números de lotería mostrarlos con **ceros a la izquierda** según el formato de cada lotería (ej: "0234" no "234")
- En las páginas públicas **no mostrar errores de scraping** — si no hay resultado reciente, mostrar "Resultado pendiente"
- Configurar `APP_URL` correctamente para que los assets funcionen en XAMPP
- El proyecto debe correr en `http://localhost/malaya/public` sin configuración adicional de virtual hosts
