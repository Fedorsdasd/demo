-- ============================================================
-- Портал "Учусь.РФ" — База данных
-- Демонстрационный экзамен В5_КОД 09.02.07-3-2026-ПУ
-- ============================================================

CREATE DATABASE IF NOT EXISTS uchis_rf CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE uchis_rf;

-- Таблица пользователей
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    login       VARCHAR(50)  NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    full_name   VARCHAR(150) NOT NULL,
    phone       VARCHAR(20)  NOT NULL,
    email       VARCHAR(100) NOT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Таблица курсов
CREATE TABLE IF NOT EXISTS courses (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(200) NOT NULL,
    type        ENUM('qualification','retraining','labor_safety') NOT NULL,
    description TEXT,
    duration    VARCHAR(50)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Таблица заявок
CREATE TABLE IF NOT EXISTS applications (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT NOT NULL,
    course_id       INT NOT NULL,
    start_date      DATE NOT NULL,
    payment_method  ENUM('card','cash','invoice') NOT NULL,
    status          ENUM('new','in_progress','completed') DEFAULT 'new',
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id)   REFERENCES users(id)   ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Таблица отзывов
CREATE TABLE IF NOT EXISTS reviews (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    application_id  INT NOT NULL UNIQUE,
    user_id         INT NOT NULL,
    rating          TINYINT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment         TEXT,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)        REFERENCES users(id)        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Начальные данные — курсы
INSERT INTO courses (title, type, description, duration) VALUES
('Повышение квалификации: 1С:Бухгалтерия',      'qualification',  'Актуализация знаний в области бухгалтерского учёта в программе 1С', '72 часа'),
('Повышение квалификации: Веб-разработка',       'qualification',  'Современные технологии фронтенд и бэкенд разработки',              '108 часов'),
('Переподготовка: Менеджмент в образовании',     'retraining',     'Управление образовательными организациями и проектами',            '256 часов'),
('Переподготовка: Информационная безопасность',  'retraining',     'Защита информации, работа с ГИС и КИИ',                           '520 часов'),
('Охрана труда: Базовый курс',                   'labor_safety',   'Требования охраны труда для руководителей и специалистов',        '40 часов'),
('Охрана труда: Первая помощь',                  'labor_safety',   'Оказание первой помощи пострадавшим на производстве',             '16 часов');
