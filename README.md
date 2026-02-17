# bratko.notes

Модуль **1С-Битрикс** — мини-сервис заметок с веб-интерфейсом (React).

## Возможности

- **Публичная страница** `/notes/` — список заметок, добавление, редактирование, удаление.
- **Авторизация** — вход и регистрация; доступ к заметкам только для авторизованных пользователей.
- **Своя таблица БД** — данные хранятся в `bratko_notes`, без инфоблоков.

## Требования

- 1С-Битрикс: Управление сайтом (ядро D7).
- PHP 7.4+ (рекомендуется 8.x).

## Установка

1. Склонируйте или скачайте репозиторий.
2. Скопируйте папку модуля в каталог модулей сайта:
   - для разработки: `local/modules/bratko.notes`
   - либо в `bitrix/modules/bratko.notes`
3. В админке Битрикс: **Marketplace → Установленные решения** — найдите «Заметки» (bratko.notes) и нажмите **Установить**.
4. После установки:
   - создаётся таблица `bratko_notes`;
   - компоненты копируются в `local/components/bratko/`;
   - JS/CSS копируются в `bitrix/js/bratko.notes/`;
   - в корень сайта добавляется раздел `/notes/` (файлы из `install/public/notes/`).

Страница заметок будет доступна по адресу: **https://ваш-сайт.ru/notes/**.

## DEMO

- Демо-стенд: [https://notes.fuzehub.ru/](https://notes.fuzehub.ru/)
- Логин: `bratko_notes_demo_user`
- Пароль: `DemoNotes123!`

## Использование

- Откройте `/notes/` в браузере: гостям показывается форма входа и регистрация, после входа — список заметок и форма добавления.
- Компонент списка заметок: `bratko:notes.list` (шаблон `.default`). Можно встроить на любую страницу при необходимости.

## Структура модуля

```
bratko.notes/
├── install/
│   ├── components/bratko/   — компоненты notes.list, notes.auth, notes.register
│   ├── db/mysql/            — install.sql, uninstall.sql
│   ├── js/bratko.notes/     — notes-react.js, notes-app.css
│   ├── public/notes/        — index.php для раздела /notes/
│   └── index.php            — установщик
├── lib/
│   ├── controller/          — AJAX-контроллеры (Auth, Register, Notes)
│   └── notesTable.php       — ORM для таблицы заметок
├── description.php
├── version.php
├── LICENSE                  — MIT
└── README.md
```

## Лицензия

MIT. См. файл [LICENSE].

## Автор

**Артём Братко** — [arbratko.ru](https://arbratko.ru/)
