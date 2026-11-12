<?php

declare(strict_types=1);

use SlavaAds\Lead\LeadStatus;

/**
 * Presentation for the admin panel. Pure rendering — no data access.
 */

function admin_render_login(string $error): void
{
    ?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Вход — заявки</title>
<?php admin_styles(); ?>
</head>
<body class="login-body">
  <form method="post" class="login-card">
    <h1>Заявки slava-ads.ru</h1>
    <p class="muted">Панель управления</p>
    <?php if ($error !== ''): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <input type="text" name="username" placeholder="Логин" autocomplete="username" required>
    <input type="password" name="password" placeholder="Пароль" autocomplete="current-password" required>
    <button type="submit" name="login" value="1">Войти</button>
  </form>
</body>
</html>
    <?php
}

/**
 * @param list<array<string, mixed>> $leads
 * @param array{total:int,new:int,since:int} $stats
 */
function admin_render_dashboard(array $leads, array $stats, string $statusFilter): void
{
    ?>
<!doctype html>
<html lang="ru">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Заявки — панель</title>
<?php admin_styles(); ?>
</head>
<body>
  <header class="topbar">
    <div class="brand">Заявки <span class="muted">slava-ads.ru</span></div>
    <div class="actions">
      <a class="btn ghost" href="?export=csv">Экспорт CSV</a>
      <a class="btn ghost" href="?logout=1">Выйти</a>
    </div>
  </header>

  <main class="wrap">
    <div class="cards">
      <div class="stat"><span class="num"><?= $stats['total'] ?></span><span class="lbl">всего заявок</span></div>
      <div class="stat"><span class="num accent"><?= $stats['new'] ?></span><span class="lbl">новых</span></div>
      <div class="stat"><span class="num"><?= $stats['since'] ?></span><span class="lbl">за 7 дней</span></div>
    </div>

    <div class="filters">
      <a class="chip <?= $statusFilter === '' ? 'on' : '' ?>" href="./">Все</a>
      <?php foreach (LeadStatus::cases() as $status): ?>
        <a class="chip <?= $statusFilter === $status->value ? 'on' : '' ?>" href="?status=<?= $status->value ?>"><?= $status->label() ?></a>
      <?php endforeach; ?>
    </div>

    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>#</th><th>Дата</th><th>Имя</th><th>Контакт</th><th>Задача / Квиз</th>
            <th>Источник</th><th>UTM</th><th>Статус</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($leads === []): ?>
            <tr><td colspan="8" class="empty">Пока нет заявок</td></tr>
          <?php endif; ?>
          <?php foreach ($leads as $lead): ?>
            <tr>
              <td class="muted">#<?= (int) $lead['id'] ?></td>
              <td class="nowrap"><?= htmlspecialchars(date('d.m.y H:i', strtotime((string) $lead['created_at']))) ?></td>
              <td><strong><?= htmlspecialchars((string) $lead['name']) ?></strong></td>
              <td class="nowrap"><?= htmlspecialchars((string) $lead['phone']) ?></td>
              <td class="task">
                <?= htmlspecialchars((string) ($lead['message'] ?? '')) ?>
                <?php if (!empty($lead['quiz'])): ?><span class="quiz"><?= htmlspecialchars((string) $lead['quiz']) ?></span><?php endif; ?>
              </td>
              <td class="muted"><?= htmlspecialchars((string) ($lead['source'] ?? '')) ?></td>
              <td class="muted small">
                <?php
                $utm = trim(implode(' / ', array_filter([
                    (string) ($lead['utm_source'] ?? ''),
                    (string) ($lead['utm_medium'] ?? ''),
                    (string) ($lead['utm_campaign'] ?? ''),
                ])));
                echo htmlspecialchars($utm !== '' ? $utm : '—');
                ?>
              </td>
              <td>
                <form method="post" class="status-form">
                  <input type="hidden" name="lead_id" value="<?= (int) $lead['id'] ?>">
                  <select name="set_status" onchange="this.form.submit()" class="st-<?= htmlspecialchars((string) $lead['status']) ?>">
                    <?php foreach (LeadStatus::cases() as $status): ?>
                      <option value="<?= $status->value ?>" <?= $lead['status'] === $status->value ? 'selected' : '' ?>><?= $status->label() ?></option>
                    <?php endforeach; ?>
                  </select>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </main>
</body>
</html>
    <?php
}

function admin_styles(): void
{
    ?>
<style>
  :root{--bg:#0b0f14;--card:#141a22;--line:rgba(244,246,251,.1);--paper:#f4f6fb;--mute:#9aa7b8;--accent:#ff6b2c}
  *{box-sizing:border-box}
  body{margin:0;background:var(--bg);color:var(--paper);font-family:system-ui,Segoe UI,Roboto,sans-serif}
  .muted{color:var(--mute)}
  .small{font-size:12px}
  .nowrap{white-space:nowrap}
  .login-body{display:grid;place-items:center;min-height:100vh}
  .login-card{background:var(--card);border:1px solid var(--line);border-radius:18px;padding:32px;width:min(360px,92vw);display:flex;flex-direction:column;gap:12px}
  .login-card h1{margin:0;font-size:20px}
  .login-card p{margin:0 0 8px}
  .login-card input{background:var(--bg);border:1px solid var(--line);border-radius:10px;padding:12px;color:var(--paper);font-size:15px}
  .login-card button,.btn{background:linear-gradient(180deg,#ff8a4c,#f2570f);color:#fff;border:0;border-radius:10px;padding:12px;font-size:15px;font-weight:600;cursor:pointer;text-decoration:none;text-align:center}
  .error{background:rgba(255,80,80,.12);color:#ff8a8a;padding:10px;border-radius:8px;font-size:14px}
  .topbar{display:flex;justify-content:space-between;align-items:center;padding:16px 24px;border-bottom:1px solid var(--line)}
  .brand{font-weight:700}
  .actions{display:flex;gap:8px}
  .btn.ghost{background:transparent;border:1px solid var(--line);color:var(--paper);padding:9px 14px;font-weight:500;font-size:14px}
  .wrap{padding:24px;max-width:1300px;margin:0 auto}
  .cards{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:20px}
  .stat{background:var(--card);border:1px solid var(--line);border-radius:14px;padding:18px;display:flex;flex-direction:column}
  .stat .num{font-size:30px;font-weight:800}
  .stat .num.accent{color:var(--accent)}
  .stat .lbl{color:var(--mute);font-size:13px;margin-top:4px}
  .filters{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px}
  .chip{padding:7px 14px;border:1px solid var(--line);border-radius:999px;color:var(--mute);text-decoration:none;font-size:14px}
  .chip.on{background:var(--paper);color:#0b0f14;font-weight:600;border-color:var(--paper)}
  .table-wrap{overflow-x:auto;border:1px solid var(--line);border-radius:14px}
  table{width:100%;border-collapse:collapse;min-width:900px}
  th,td{text-align:left;padding:12px 14px;border-bottom:1px solid var(--line);font-size:14px;vertical-align:top}
  th{color:var(--mute);font-weight:600;font-size:12px;text-transform:uppercase;letter-spacing:.05em}
  tr:last-child td{border-bottom:0}
  .task{max-width:280px}
  .task .quiz{display:block;margin-top:4px;color:var(--accent);font-size:12px}
  .empty{text-align:center;color:var(--mute);padding:40px}
  select{background:var(--bg);border:1px solid var(--line);border-radius:8px;padding:7px;color:var(--paper);font-size:13px}
  select.st-new{border-color:var(--accent);color:var(--accent)}
  @media(max-width:640px){.cards{grid-template-columns:1fr}}
</style>
    <?php
}
