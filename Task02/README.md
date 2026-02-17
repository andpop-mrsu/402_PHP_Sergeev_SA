# Task02 — веб-приложение + SQLite (Page Controller)

Цель: оформить игру (вариант **GCD/НОД**) как простое веб-приложение на PHP, которое хранит данные в SQLite.

## Структура
- `public/` — корень сайта (в браузере доступны только файлы отсюда)
- `db/` — база данных (`app.sqlite`) **вне** `public/`
- `src/` — вспомогательные классы/функции (DB, репозиторий, логика игры)
- `views/` — шаблоны (используется альтернативный синтаксис PHP)

## Запуск
Из каталога `Task02`:

```bash
php -S localhost:3000 -t public
```

Открыть в браузере:

- http://localhost:3000/ (перенаправит на `index.php`)
- или http://localhost:3000/index.php

## Страницы (Page Controller)
- `public/index.php` — старт, ввод имени
- `public/play.php` — игра по раундам
- `public/result.php` — результат текущей/указанной игры
- `public/history.php` — история игр из SQLite

## База данных
База создаётся автоматически при первом запуске (миграции — в `src/Database.php`).

Таблицы:
- `players` — игроки
- `games` — сессии игры
- `rounds` — раунды (вопросы/ответы)


## Требования окружения
- В PHP должно быть включено расширение **PDO SQLite** (`pdo_sqlite`).
  - Проверка: `php -m | findstr /i sqlite` (Windows) или `php -m | grep -i sqlite` (Linux/macOS).

## Автор

**Sergeev S.A.**
GitHub: `Kulebyaka1337`

---