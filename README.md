# slava-ads.ru

Лидген-сайт интернет-маркетолога Вячеслава Острополец. Astro (статика) + PHP-бэкенд приёма заявок на VPS (Apache + PHP 8.5 + MySQL).

## Стек

- **Astro 5** + TypeScript, статическая генерация (`output: "static"`)
- **Tailwind CSS 4** (через `@tailwindcss/vite`), дизайн-токены в `src/styles/global.css`
- Шрифты self-hosted: Playfair Display (заголовки) + Montserrat (текст) через Fontsource
- Дизайн-система — светлая «резюме»-раскладка (белый холст, полосы `#f5f8fe`, синий `#2e71f1`, розовый акцент); токены и компоненты в `src/styles/global.css`
- **PHP** эндпоинт `api/lead.php` + MySQL + Telegram/email уведомления
- Мини-админка `admin/` для просмотра и выгрузки заявок

## Локальная разработка

```bash
npm install        # использует зеркало npmmirror через .npmrc
npm run dev        # http://localhost:4321
npm run build      # сборка в dist/
npm run preview    # предпросмотр сборки
```

> Примечание: `.npmrc` указывает на зеркало `registry.npmmirror.com`, т.к. `registry.npmjs.org`
> недоступен из сети разработки. На сервере используется реестр по умолчанию.

## Контент

- `slavaads-site-content.json` — исходные данные, собранные с оригинала slavaads.ru
- `src/data/content.ts` — типизированный контент (услуги, кейсы, отзывы, FAQ)
- `src/data/serviceDetails.ts` — расширенный контент страниц услуг
- `src/data/caseDetails.ts` — нарративы кейсов
- `src/content/blog/*.md` — статьи блога

## Переставляемые блоки главной

Порядок секций задаётся массивом в `src/config/home.ts`:

```ts
export const homeBlocks = ["hero", "trust", "services", "cases", ...];
```

Поменять порядок = переставить ключи. Скрыть блок = удалить ключ. Реестр компонентов —
в `src/pages/index.astro`.

## Структура страниц

- `/` — главная (воронка из блоков + квиз + форма)
- `/kontekstnaya-reklama`, `/targetirovannaya-reklama`, `/sozdanie-saytov`, `/dizayn` — услуги
- `/cases`, `/cases/[id]` — кейсы
- `/blog`, `/blog/[slug]` — блог
- `/privacy`, `/consent` — юридические
- `/404`

## Приём заявок

Форма постит на `api/lead.php`:
1. Блок-лист IP (`BLOCKED_IPS`) + honeypot
2. Проверка same-origin (`Origin`/`Referer`) — отсекает прямые POST-скрипты
3. Подписанный form-token (мин. 1 сек. на заполнение, макс. 24 часа)
4. Rate-limit по IP: в минуту / час / сутки
5. Своя капча: сервер рисует код на SVG, ответ проверяется по подписанному токену
6. Запись в MySQL (таблица `leads`)
7. Уведомление в Telegram + email (Brevo SMTP)
8. Сохраняет UTM, источник, страницу, referrer для атрибуции

Капча работает без внешних сервисов и сессий. Секрет берётся из `CAPTCHA_SECRET`,
затем из `FORM_SECRET`, затем из `ADMIN_PASS_HASH` — на сервере уже задан
`FORM_SECRET`, поэтому она включена автоматически. Клиент получает токен и
SVG-картинку через `GET /api/lead.php?challenge=1`.

> `TRUST_PROXY_HEADERS=1` допустим только если Apache стоит за доверенным
> прокси (Cloudflare/nginx) и origin недоступен напрямую. Иначе клиент
> подделает `X-Forwarded-For` и обойдёт rate-limit.

Настройки — в `api/.env` (на сервере, не в git). Шаблон: `api/.env.example`.
Схема БД: `api/schema.sql`.

## Архитектура бэкенда (PHP)

Классы под `api/src/` (namespace `SlavaAds\`), PSR-4 автозагрузка без vendor
(`api/autoload.php`). Принципы SOLID — каждый класс с единственной ответственностью,
зависимости через интерфейсы:

- `Config\Env` — неизменяемая конфигурация из `.env`
- `Http\Request` / `Response` / `Responder` — ввод/вывод без глобального состояния
- `Http\HttpClient` (+ `CurlHttpClient`) — исходящие запросы (мокается в тестах)
- `Lead\Lead`, `Utm` — неизменяемые value-объекты
- `Lead\LeadFactory` — санитизация, `LeadValidator` — валидация
- `Lead\LeadStore` / `LeadCounter` — узкие интерфейсы (Interface Segregation)
- `Lead\LeadRepository` — персистентность (портируемый SQL: MySQL + SQLite)
- `Lead\RateLimiter` (+ `Support\Clock`) — антиспам с детерминируемым временем
- `Notification\Notifier` (+ `Composite`/`Telegram`/`Email`) — каналы (Open/Closed)
- `Notification\LeadMessageFormatter` — единый формат сообщения (DRY), без эмодзи
- `Mail\Mailer` (+ `SmtpMailer`/`NativeMailer`) — транспорт письма
- `Lead\LeadController` — тонкая оркестрация, возвращает `Response` (тестируема)
- `App` — композиционный корень (вся проводка зависимостей)

## Тесты (PHPUnit)

```bash
composer install      # ставит PHPUnit (dev), использует зеркало из глобального конфига
composer test         # или: vendor\bin\phpunit
```

Тесты в `tests/`, используют in-memory SQLite (`tests/Support/TestDatabase.php`)
и фейки (`FakeHttpClient`, `FakeMailer`, `FrozenClock`, `RecordingNotifier`).
Покрыты: парсинг env, санитизация ввода, валидация, rate-limit, репозиторий,
форматтер (отсутствие эмодзи), каналы уведомлений, полный поток контроллера.

Production не требует `vendor/` — собственный автозагрузчик в `api/autoload.php`.

## Деплой

```powershell
powershell -ExecutionPolicy Bypass -File deploy\deploy.ps1
```

Скрипт собирает сайт, пакует `dist + api + admin`, заливает на VPS и перезагружает Apache
(сохраняя `api/.env` на сервере).

Первичная провизия сервера: `deploy/server-setup.sh` (создаёт БД, vhost, импортирует схему).
Vhost: `deploy/slava-ads.ru.conf`.

### SSL

После того как DNS укажет на сервер:

```bash
sudo certbot --apache -d slava-ads.ru -d www.slava-ads.ru
```

## Аналитика

Слоты под Я.Метрику и GA4 — в `src/layouts/Layout.astro` (комментарии `METRIKA_SLOT`, `GA4_SLOT`).
Цель `lead` отправляется в `ym`/`gtag` при успешной отправке формы (`src/components/LeadModal.astro`).
