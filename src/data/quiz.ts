/**
 * 2-step lead-magnet quiz. Replaces the external Matomba widget from the source site.
 * Answers are collected client-side and submitted together with the lead form.
 */
export interface QuizStep {
  id: string;
  question: string;
  hint?: string;
  options: { value: string; label: string; emoji?: string }[];
}

export const quizSteps: QuizStep[] = [
  {
    id: "goal",
    question: "Что для вас сейчас важнее?",
    hint: "Шаг 1 из 2",
    options: [
      { value: "leads", label: "Больше заявок и клиентов", emoji: "🎯" },
      { value: "cheaper", label: "Снизить цену заявки", emoji: "📉" },
      { value: "site", label: "Нужен продающий сайт", emoji: "🧩" },
      { value: "audit", label: "Аудит текущей рекламы", emoji: "🔍" },
    ],
  },
  {
    id: "channel",
    question: "Где хотите продвигаться?",
    hint: "Шаг 2 из 2",
    options: [
      { value: "yandex", label: "Яндекс Директ", emoji: "🟡" },
      { value: "google", label: "Google Ads", emoji: "🔵" },
      { value: "both", label: "Везде, где есть клиенты", emoji: "🌍" },
      { value: "unsure", label: "Не знаю, нужен совет", emoji: "💬" },
    ],
  },
];

export const quizIntro = {
  eyebrow: "Бесплатная консультация",
  title: "Ответьте на 2 вопроса — получите план продвижения",
  subtitle:
    "Подберу стратегию под вашу нишу и бюджет. Займёт меньше минуты.",
};
