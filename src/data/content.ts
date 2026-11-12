/**
 * Curated, typed content model.
 * Source of truth for facts: ../../slavaads-site-content.json (scraped from slavaads.ru).
 * Copy here is polished for SEO and conversion; numbers, reviews, education and FAQ
 * are kept faithful to the source.
 */

export interface Specialist {
  fullName: string;
  shortName: string;
  role: string;
  tagline: string;
  experienceYears: number;
  photo: string;
}

export interface EconomicsPoint {
  title: string;
  text: string;
  icon: string;
}

export interface Service {
  id: string;
  slug: string;
  title: string;
  short: string;
  description: string;
  bullets: string[];
  tools: string[];
  icon: string;
}

export interface WorkflowStep {
  step: number;
  title: string;
  actions: string;
  outcome: string;
}

export interface AudienceSituation {
  text: string;
  icon: string;
}

export interface CaseStudy {
  id: string;
  title: string;
  project: string;
  goal: string;
  solution: string;
  takeaway: string;
  resultNote: string;
  geo: string;
  website: string;
  websiteNote?: string;
  advertising: string;
  metric: { value: string; label: string };
  secondary?: { value: string; label: string }[];
  accent: "signal" | "volt";
  logo?: string;
  chart?: CaseChart;
}

/** Chart numbers must come from the case facts — never invent a time series.
 *  `value` drives bar geometry; `display` keeps the exact source formatting. */
export type CaseChart =
  | {
      kind: "delta";
      caption: string;
      note?: string;
      bars: { label: string; value: number; display: string }[];
    }
  | {
      kind: "compare";
      caption: string;
      note?: string;
      beforeLabel: string;
      afterLabel: string;
      series: {
        label: string;
        before: number;
        after: number;
        beforeDisplay: string;
        afterDisplay: string;
      }[];
    }
  | {
      kind: "rate";
      caption: string;
      note?: string;
      label: string;
      value: number;
      display: string;
    };

export interface Review {
  text: string;
  author: string;
  company: string;
  channel: "WhatsApp" | "Telegram";
}

export interface QualificationDoc {
  title: string;
  provider?: string;
  type: string;
  src: string;
}

export interface FaqItem {
  question: string;
  answer: string;
}

export const specialist: Specialist = {
  fullName: "Вячеслав Острополец",
  shortName: "Вячеслав",
  role: "Интернет-маркетолог",
  tagline: "Привожу горячих клиентов из контекстной рекламы",
  experienceYears: 8,
  photo: "/images/portrait.png",
};

export const economics = {
  heading: "Дешёвая заявка ещё не означает выгодного клиента",
  lead: "Оцениваю рекламу не по кликам и даже не по цене заявки, а по тому, во что обращение обходится бизнесу и что оно приносит.",
  points: [
    {
      title: "Конверсия не равна обращению",
      text: "Конверсия в аналитике не всегда равна обращению: нужно понимать, какое именно действие учитывается.",
      icon: "target",
    },
    {
      title: "Низкий CPL — не гарантия",
      text: "Низкая цена заявки не гарантирует хороший результат: важны качество обращений и их переход в продажи.",
      icon: "shield",
    },
    {
      title: "Масштабирование требует проверки",
      text: "Увеличение бюджета само по себе не делает рекламу выгоднее — масштабирование требует проверки экономики.",
      icon: "chart",
    },
  ] as EconomicsPoint[],
  formula: {
    result: "Допустимый CPL",
    factors: [
      "Допустимая стоимость привлечения клиента",
      "Конверсия заявки в продажу",
    ],
    note: "Допустимая стоимость клиента определяется с учётом маржи, остальных затрат и требуемой прибыли. Без данных о продажах нельзя достоверно заявлять окупаемость.",
  },
};

export const services: Service[] = [
  {
    id: "context",
    slug: "kontekstnaya-reklama",
    title: "Контекстная реклама",
    short: "Яндекс Директ и Google Ads",
    description:
      "Работаю со спросом, запросами, объявлениями и бюджетом в Яндекс Директ и Google Ads. Результат оцениваю по обращениям и их качеству, а не по кликам и показам.",
    bullets: [
      "Семантика и запросы под ваш продукт",
      "Объявления и бюджет под цель проекта",
      "Оценка по стоимости и качеству заявок",
      "Понятные отчёты с ключевыми цифрами",
    ],
    tools: ["Яндекс Директ", "Google Ads", "Яндекс Метрика", "Google Analytics"],
    icon: "rocket",
  },
  {
    id: "sites",
    slug: "sozdanie-saytov",
    title: "Сайты, лендинги, квизы",
    short: "Продающие посадочные",
    description:
      "Привожу страницу в соответствие рекламному предложению: понятный путь до обращения и измерение конверсии. Лендинги, многостраничные сайты и квизы на конструкторах.",
    bullets: [
      "Соответствие страницы рекламному предложению",
      "Понятный путь до обращения",
      "Измерение конверсии и аналитика",
      "A/B-тесты элементов страницы",
    ],
    tools: ["Flexbe", "Tilda", "Craftum", "GA4", "Яндекс Метрика"],
    icon: "layout",
  },
  {
    id: "target",
    slug: "targetirovannaya-reklama",
    title: "Таргетированная реклама",
    short: "Соцсети и воронки",
    description:
      "Дополнительный канал, когда он оправдан аудиторией и задачей, а не обязательная часть каждого проекта. Медиаплан, сегменты и креативы под гипотезы.",
    bullets: [
      "Медиаплан и сегменты аудитории",
      "Креативы и тесты гипотез",
      "Подключение, когда канал оправдан задачей",
      "Аналитика до обращения",
    ],
    tools: ["Соцсети", "Яндекс Метрика", "Google Analytics"],
    icon: "users",
  },
  {
    id: "design",
    slug: "dizayn",
    title: "Дизайн и креативы",
    short: "Визуал, который продаёт",
    description:
      "Материалы под конкретные гипотезы, аудитории и объявления: баннеры, статичные креативы и видеомонтаж. CTR — диагностическая метрика, а не конечная бизнес-цель.",
    bullets: [
      "Баннеры и статичные креативы",
      "Видеомонтаж под соцсети и рекламу",
      "Материалы под гипотезы и аудитории",
      "CTR как диагностика, а не самоцель",
    ],
    tools: ["Figma", "Photoshop", "Canva", "Premier Pro", "After Effects"],
    icon: "palette",
  },
];

export const workflow: WorkflowStep[] = [
  {
    step: 1,
    title: "Разбираем задачу",
    actions: "Предложение, спрос, прошлые результаты, ограничения",
    outcome: "Приоритеты и план проверки гипотез",
  },
  {
    step: 2,
    title: "Согласуем измерение",
    actions: "Что считаем обращением, как различаем качество, какие данные доступны",
    outcome: "Понятные критерии оценки",
  },
  {
    step: 3,
    title: "Готовим и запускаем",
    actions: "Посадочная, объявления, кампании, проверка передачи заявок",
    outcome: "Рабочую связку и первые данные",
  },
  {
    step: 4,
    title: "Оптимизируем",
    actions: "Сопоставляем затраты, обращения и обратную связь по ним",
    outcome: "Обоснованные решения: менять, продолжать или масштабировать",
  },
];

export const audience = {
  situations: [
    { text: "Обращения нужны, но системного привлечения пока нет.", icon: "rocket" },
    { text: "Реклама работает, но непонятно, какие заявки действительно полезны.", icon: "target" },
    { text: "Трафик приходит, а посадочная страница плохо переводит его в обращения.", icon: "layout" },
    { text: "Требуется увеличивать объём, сохраняя приемлемую стоимость привлечения.", icon: "chart" },
  ] as AudienceSituation[],
  requirement:
    "Для работы нужны готовое предложение, возможность обрабатывать обращения и обратная связь по качеству заявок.",
};

export const cases: CaseStudy[] = [
  {
    id: "tmm",
    title: "TV Mount Master",
    project: "Установка ТВ на стену",
    goal: "Запустить бренд, сайт и поток заявок",
    solution: "Выделил установку ТВ в отдельный бренд и сайт из проекта услуг по дому.",
    takeaway:
      "Когда направление вырастает внутри общего проекта, отдельный бренд и посадочные помогают сфокусировать рекламу и спрос.",
    resultNote: "В среднем за неделю; $19 — средняя цена заявки.",
    geo: "США, Калифорния",
    website: "tvmountmaster.com",
    advertising: "Google Ads",
    metric: { value: "200", label: "заявок в неделю" },
    secondary: [{ value: "$19", label: "цена заявки" }],
    accent: "signal",
    logo: "/images/logos/tmm.png",
    chart: {
      kind: "rate",
      caption: "Доля оформленных заявок",
      note: "В среднем 200 заявок в неделю по $19.",
      label: "оформляется",
      value: 70,
      display: "70%",
    },
  },
  {
    id: "hhn",
    title: "Handy Help Now",
    project: "Услуги ремонта по дому",
    goal: "Запустить бренд, сайт и поток заявок",
    solution: "Собрал многостраничный сайт: отдельная страница под каждую услугу.",
    takeaway:
      "Страница под каждую услугу даёт релевантную посадочную под запрос — так проще вести рекламу и оценивать конверсию.",
    resultNote: "В среднем за неделю; $18 — средняя цена заявки.",
    geo: "США, Калифорния",
    website: "handyhelpnow.com",
    advertising: "Google Ads",
    metric: { value: "50", label: "заявок в неделю" },
    secondary: [{ value: "$18", label: "цена заявки" }],
    accent: "signal",
    logo: "/images/logos/hhn.png",
    chart: {
      kind: "rate",
      caption: "Доля оформленных заявок",
      note: "В среднем 50 заявок в неделю по $18.",
      label: "оформляется",
      value: 70,
      display: "70%",
    },
  },
  {
    id: "morskaya",
    title: "Станция Морская",
    project: "Семейный курорт на берегу моря",
    goal: "Поднять результаты прошлых кампаний",
    solution:
      "Пересобрал действующие кампании Директа под новое позиционирование: семейный курорт вместо СПА-комплекса.",
    takeaway:
      "Смена позиционирования требует пересборки семантики и объявлений, а результат корректно сравнивать на одинаковых периодах.",
    resultNote:
      "865 — конверсии (учтённые в аналитике действия), 90 ₽ — цена конверсии, не заявки. Динамика — сравнение одинаковых периодов 2023 и 2024 годов.",
    geo: "Юг России",
    website: "st-morskaya.ru",
    advertising: "Яндекс Директ",
    metric: { value: "865", label: "конверсий в неделю" },
    secondary: [{ value: "90 ₽", label: "цена конверсии" }],
    accent: "volt",
    logo: "/images/logos/morskaya.svg",
    chart: {
      kind: "delta",
      caption: "Динамика: одинаковые периоды 2023 и 2024",
      note: "Бюджет прежний. Снижение стоимости клика и конверсии, а также отказов — улучшение.",
      bars: [
        { label: "Показы", value: 25, display: "+25%" },
        { label: "Клики", value: 19, display: "+19%" },
        { label: "Стоимость клика", value: -9, display: "−9%" },
        { label: "Отказной трафик", value: -5, display: "−5%" },
        { label: "Глубина просмотра", value: 59, display: "+59%" },
        { label: "Стоимость конверсии", value: -16, display: "−16%" },
        { label: "Конверсии", value: 29, display: "+29%" },
      ],
    },
  },
  {
    id: "proxamam",
    title: "ProXamam",
    project: "Производство мебели для хамама из пенополистирола",
    goal: "Поднять результаты прошлых кампаний",
    solution:
      "Оптимизировал Директ без переделки сайта: сменил стратегии, переписал объявления и добавил квиз для сбора заявок.",
    takeaway:
      "Переделка сайта нужна не всегда: иногда рост даёт пересборка кампаний при том же бюджете и посадочной.",
    resultNote:
      "Сопоставимые периоды: 193 (10.07.2023–10.02.2024) → 286 (10.07.2024–10.02.2025); +93 конверсии за период.",
    geo: "Россия",
    website: "prohamam.ru",
    advertising: "Яндекс Директ",
    metric: { value: "+93", label: "конверсии за период" },
    secondary: [
      { value: "193 → 286", label: "рост конверсий" },
    ],
    accent: "volt",
    logo: "/images/logos/proxamam.png",
    chart: {
      kind: "compare",
      caption: "Конверсии за сопоставимые периоды",
      note: "До: 10.07.2023–10.02.2024. После: 10.07.2024–10.02.2025. Бюджет тот же.",
      beforeLabel: "до",
      afterLabel: "после",
      series: [
        { label: "Конверсии", before: 193, after: 286, beforeDisplay: "193", afterDisplay: "286" },
      ],
    },
  },
  {
    id: "kzmdgrp",
    title: "Kazmedia Group",
    project: "Реклама в лифтах",
    goal: "Больше заявок и ниже цена лида",
    solution:
      "Работал с существующим сайтом (создан не мной): перестроил кампании и снижал цену обращения.",
    takeaway:
      "Если сайт уже есть, фокус смещается на кампании и цену обращения, а качество лидов проверяется через аналитику и обратную связь.",
    resultNote:
      "5 → 20 заявок в неделю по $5,59. Отдельный отрезок: 76 конверсий за полтора месяца по средней $7,71.",
    geo: "Казахстан",
    website: "kazmedia-group.kz",
    websiteNote: "сайт создан не мной",
    advertising: "Google Ads",
    metric: { value: "×4", label: "рост заявок" },
    secondary: [
      { value: "5 → 20", label: "заявок в неделю" },
      { value: "$5,59", label: "цена заявки" },
    ],
    accent: "volt",
    logo: "/images/logos/kzmdgrp.jpeg",
    chart: {
      kind: "compare",
      caption: "Заявки в неделю и цена лида",
      note: "Заявки выросли в 4 раза, цена лида снизилась. Шкалы у метрик независимые.",
      beforeLabel: "до",
      afterLabel: "после",
      series: [
        { label: "Заявки в неделю", before: 5, after: 20, beforeDisplay: "5", afterDisplay: "20" },
        { label: "Цена лида", before: 23, after: 5.59, beforeDisplay: "$23", afterDisplay: "$5,59" },
      ],
    },
  },
  {
    id: "sarmat",
    title: "Сармат",
    project: "Речные прогулки по Дону на катерах",
    goal: "Сделать сайт и наладить заявки",
    solution: "Связал новый сайт и Директ для локальной услуги: речные прогулки по Дону.",
    takeaway:
      "Для локальной услуги сайт и реклама запускаются вместе, а конверсия растёт за счёт тестов блоков и точных запросов.",
    resultNote:
      "20 заявок в неделю по 407 ₽ — цена заявки. Динамика конверсий — при росте расходов на 10 000 ₽.",
    geo: "Ростов-на-Дону",
    website: "sarmat-rostov.ru",
    advertising: "Яндекс Директ",
    metric: { value: "20", label: "заявок в неделю" },
    secondary: [{ value: "407 ₽", label: "цена заявки" }],
    accent: "volt",
    logo: "/images/logos/sarmat.svg",
    chart: {
      kind: "delta",
      caption: "Динамика при росте расходов на 10 000 ₽",
      note: "Снижение стоимости конверсии — улучшение.",
      bars: [
        { label: "Показы", value: 148, display: "+148%" },
        { label: "Клики", value: 63, display: "+63%" },
        { label: "Конверсии", value: 121, display: "+121%" },
        { label: "Стоимость конверсии", value: -28, display: "−28%" },
      ],
    },
  },
];

export const caseHighlights: { value?: string; label: string }[] = [
  { value: "6", label: "проектов в портфолио" },
  { label: "Россия, СНГ и США" },
  { value: "8 лет", label: "опыта в трафике" },
  { value: "около 20%", label: "мой лучший результат по конверсии" },
];

export const reviews: Review[] = [
  {
    text: "Вячеслав, большое спасибо! Всё сделано быстро и по делу, а главное — заявки идут каждый день. Раньше такого потока не было, теперь мастера всегда при деле.",
    author: "Роман",
    company: 'Услуги "Handyman"',
    channel: "WhatsApp",
  },
  {
    text: "Сотрудничеством очень доволен. Сайт начал приносить клиентов буквально с первых дней, а реклама даёт стабильный поток заявок. Работаем дальше!",
    author: "Роман",
    company: "TV Mount Master",
    channel: "Telegram",
  },
  {
    text: "Работаем уже не первый месяц — результатом доволен. Когда в Метрике увидел +500% посещений при том же бюджете, долго не мог поверить)) Оказывается, из Директа можно выжать гораздо больше. Спасибо за подход и инициативу!",
    author: "Игорь",
    company: "ProXamam",
    channel: "WhatsApp",
  },
  {
    text: "Всё сделали профессионально и быстро, никакой головной боли с нашей стороны. Реклама реально работает. Спасибо за помощь и отличный результат!",
    author: "Марина",
    company: "Сармат",
    channel: "Telegram",
  },
  {
    text: "Рады, что нашли вас. Цифры стабильно растут! Всё настроено грамотно, без лишних обещаний. Спасибо, что всегда на связи и оперативно запускаете акции. Продолжаем!",
    author: "Наталья",
    company: "Станция Морская",
    channel: "WhatsApp",
  },
];

export const qualification = {
  note: "Дипломы и сертификаты Яндекса и профильных школ. Подлинность легко проверить — открывайте оригиналы по ссылкам.",
  docs: [
    { title: "Интернет-маркетолог", provider: "Яндекс", type: "Диплом", src: "/images/edu/1-yandex.jpg" },
    { title: "Яндекс Директ", provider: "Elama", type: "Сертификат", src: "/images/edu/2-elama.jpg" },
    { title: "Google Ads", provider: "BeOnMax", type: "Сертификат", src: "/images/edu/3-googleads.jpg" },
    { title: "Веб-аналитик", provider: "Yagla", type: "Диплом", src: "/images/edu/4-yagla-webanalyst.jpg" },
    { title: "Повышение конверсии сайта", provider: "Yagla", type: "Сертификат", src: "/images/edu/5-yagla-conversion.jpg" },
    { title: "Разработка на WordPress", provider: "BeOnMax", type: "Сертификат", src: "/images/edu/6-wordpress.png" },
    { title: "SMM-менеджер", provider: "Convert Monster", type: "Диплом", src: "/images/edu/7-convertmonster.png" },
    {
      title: "Яндекс Директ. Продвинутый",
      provider: "Яндекс",
      type: "Сертификат",
      src: "https://274418.selcdn.ru/cv08300-33250f0d-0664-43fc-9dbf-9d89738d114e/uploads/471312/302d9c36-a470-45c2-ac55-df06abccf14d.jpg",
    },
    {
      title: "Яндекс Метрика",
      provider: "Яндекс",
      type: "Сертификат",
      src: "https://274418.selcdn.ru/cv08300-33250f0d-0664-43fc-9dbf-9d89738d114e/uploads/471312/c9a06d5c-66b6-4f5c-8f01-db8c7288beea.jpg",
    },
    {
      title: "Медийная реклама",
      type: "Сертификат",
      src: "https://274418.selcdn.ru/cv08300-33250f0d-0664-43fc-9dbf-9d89738d114e/uploads/471312/c7933c27-9d2e-4fc3-b5cc-a352c450f871.jpg",
    },
    {
      title: "Яндекс Директ. Базовый",
      provider: "Яндекс",
      type: "Сертификат",
      src: "https://274418.selcdn.ru/cv08300-33250f0d-0664-43fc-9dbf-9d89738d114e/uploads/471312/73ed7f1e-8781-4441-9410-d55c8a6c5799.jpg",
    },
  ] as QualificationDoc[],
};

export const about = {
  greeting: "Привет, я Вячеслав",
  lead: "Маркетолог, который живёт трафиком и цифрами.",
  workspacePhoto: "/images/workspace.jpg",
  paragraphs: [
    "Восемь лет занимаюсь контекстной рекламой и сайтами: Яндекс Директ, Google Ads, посадочные страницы и аналитика. Веду ограниченное число проектов, чтобы в каждом доводить рекламу и страницу до измеримого результата и отвечать за цифры.",
    "Работаю прозрачно: согласуем, что считаем обращением, регулярно показываю динамику в понятных цифрах и объясняю решения. На связи в Telegram и WhatsApp — можно писать напрямую, без менеджеров и длинных цепочек.",
  ],
};

export const faq: FaqItem[] = [
  {
    question: "Из чего складывается бюджет: реклама, работа специалиста, сайт и дополнительные инструменты?",
    answer:
      "Из четырёх частей: рекламный бюджет (уходит площадке за показы и клики), работа специалиста по запуску и ведению, сайт или посадочная страница и инструменты — аналитика, квизы, виджеты. Рекламный бюджет — не мой гонорар: его размер зависит от ниши, региона и конкуренции. Конкретные суммы называю после разбора проекта, универсального прайса нет.",
  },
  {
    question: "Можно ли начать с существующим сайтом?",
    answer:
      "Да. Сначала провожу аудит сайта и текущей рекламы: какие обращения уже идут, что мешает конверсии и где точки роста. Часто достаточно доработать посадочную и пересобрать кампании — например, в кейсе ProXamam сайт не переделывали, а конверсии выросли со 193 до 286 за сопоставимый период. Новый сайт нужен не всегда: решение принимаю по данным аудита.",
  },
  {
    question: "Что считается заявкой и как оценивается её качество?",
    answer:
      "Заявка — это целевое обращение: отправленная форма, звонок или сообщение в мессенджер, которое мы заранее согласовали как обращение. В аналитике конверсией называют и другие действия, поэтому сначала определяем, что именно считаем заявкой. Качество оцениваю не по цене, а по обратной связи: насколько обращения целевые и доходят ли они до продажи. Дешёвая заявка ещё не значит выгодного клиента.",
  },
  {
    question: "Какие данные нужны для оценки стоимости привлечения?",
    answer:
      "Расходы на рекламу, число обращений и обратная связь по их качеству — сколько заявок дошло до продажи. Нужен доступ к аналитике (Яндекс Метрика, GA4) и рекламным кабинетам, чтобы видеть затраты и конверсии. Без данных о продажах нельзя достоверно посчитать допустимую стоимость привлечения и говорить об окупаемости: цена заявки сама по себе ещё не экономика.",
  },
  {
    question: "Когда можно принимать решение об увеличении бюджета?",
    answer:
      "Когда экономика подтверждена на данных: стабильная стоимость обращения, приемлемое качество заявок и их переход в продажи на сопоставимых периодах. Увеличение бюджета само по себе не делает рекламу выгоднее — сначала проверяем, что связка работает, и только потом масштабируем. Решение принимаю по цифрам и прозрачно показываю динамику.",
  },
  {
    question: "Можно ли гарантировать количество заявок и окупаемость?",
    answer:
      "Конкретное количество заявок и окупаемость гарантировать нельзя: результат зависит от ниши, региона, предложения, конкуренции и периода. Универсальных обещаний в маркетинге не бывает. Но я работаю на измеримый результат и отвечаю за цифры. Мой лучший результат по конверсии — около 20%: это личный рекорд конкретного проекта, а не универсальная норма для всех. Заранее согласуем, что считаем обращением, и прозрачно показываю динамику.",
  },
];

export const cta = {
  title: "Обсудим, как привлекать заявки в ваш проект",
  subtitle:
    "Расскажите, что продаёте, в каком регионе работаете и что уже пробовали. Начнём с разбора текущей ситуации и возможных следующих шагов.",
  button: "Получить разбор проекта",
};

export const leadForm = {
  title: cta.button,
  subtitle: cta.subtitle,
  button: "Отправить заявку",
  success: "Заявка принята — скоро свяжусь с вами!",
  consent: "Нажимая кнопку, вы соглашаетесь с политикой обработки данных",
};
