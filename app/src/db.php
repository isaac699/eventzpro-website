<?php
declare(strict_types=1);

/**
 * Database access (PDO). SQLite by default: a single file in app/storage/database,
 * no server needed. Set DB_CONNECTION=mysql in app/.env for MySQL/MariaDB.
 * Tables are created (and upgraded) automatically on first use.
 */

const DB_SCHEMA_VERSION = 1;

function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_TIMEOUT            => 5,
    ];

    if (db_driver() === 'mysql') {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            config('db.host'), (int) config('db.port'), config('db.database')
        );
        $pdo = new PDO($dsn, (string) config('db.username'), (string) config('db.password'), $options);
        $pdo->exec("SET time_zone = '+00:00'");
    } else {
        $path = (string) config('db.sqlite_path');
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0750, true);
        }
        $pdo = new PDO('sqlite:' . $path, null, null, $options);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $pdo->exec('PRAGMA busy_timeout = 5000');
    }

    db_migrate($pdo);
    return $pdo;
}

function db_driver(): string
{
    return config('db.driver') === 'mysql' ? 'mysql' : 'sqlite';
}

/** Current time in the format stored in every *_at column. */
function db_now(): string
{
    return date('Y-m-d H:i:s');
}

/** Run a query with bound parameters. */
function db_query(string $sql, array $params = []): PDOStatement
{
    $stmt = db()->prepare($sql);
    $stmt->execute($params);
    return $stmt;
}

function db_one(string $sql, array $params = []): ?array
{
    $row = db_query($sql, $params)->fetch();
    return $row === false ? null : $row;
}

function db_all(string $sql, array $params = []): array
{
    return db_query($sql, $params)->fetchAll();
}

function db_value(string $sql, array $params = []): mixed
{
    $value = db_query($sql, $params)->fetchColumn();
    return $value === false ? null : $value;
}

/** Insert a row and return its id. */
function db_insert(string $table, array $row): int
{
    $cols = array_keys($row);
    $sql = sprintf(
        'INSERT INTO %s (%s) VALUES (%s)',
        $table, implode(', ', $cols), implode(', ', array_fill(0, count($cols), '?'))
    );
    db_query($sql, array_values($row));
    return (int) db()->lastInsertId();
}

/** Create or upgrade the tables. Cheap after the first run: one indexed read. */
function db_migrate(PDO $pdo): void
{
    $pdo->exec('CREATE TABLE IF NOT EXISTS schema_version (version INTEGER NOT NULL)');
    $current = (int) $pdo->query('SELECT MAX(version) FROM schema_version')->fetchColumn();
    if ($current >= DB_SCHEMA_VERSION) {
        return;
    }

    $statements = db_driver() === 'mysql' ? db_schema_mysql() : db_schema_sqlite();
    foreach ($statements as $sql) {
        $pdo->exec($sql);
    }
    $pdo->exec('DELETE FROM schema_version');
    $pdo->prepare('INSERT INTO schema_version (version) VALUES (?)')->execute([DB_SCHEMA_VERSION]);
}

function db_schema_sqlite(): array
{
    return [
        'CREATE TABLE IF NOT EXISTS users (
            id            INTEGER PRIMARY KEY AUTOINCREMENT,
            name          TEXT    NOT NULL,
            email         TEXT    NOT NULL UNIQUE,
            password_hash TEXT    NOT NULL,
            role          TEXT    NOT NULL DEFAULT \'staff\',
            is_active     INTEGER NOT NULL DEFAULT 1,
            last_login_at TEXT    NULL,
            created_at    TEXT    NOT NULL,
            updated_at    TEXT    NOT NULL
        )',
        'CREATE TABLE IF NOT EXISTS messages (
            id          INTEGER PRIMARY KEY AUTOINCREMENT,
            reference   TEXT    NOT NULL UNIQUE,
            name        TEXT    NOT NULL,
            email       TEXT    NOT NULL,
            phone       TEXT    NOT NULL DEFAULT \'\',
            company     TEXT    NOT NULL DEFAULT \'\',
            topic       TEXT    NOT NULL,
            message     TEXT    NOT NULL,
            status      TEXT    NOT NULL DEFAULT \'new\',
            notes       TEXT    NULL,
            email_sent  INTEGER NOT NULL DEFAULT 0,
            ip_hash     TEXT    NOT NULL DEFAULT \'\',
            user_agent  TEXT    NOT NULL DEFAULT \'\',
            handled_by  INTEGER NULL REFERENCES users(id) ON DELETE SET NULL,
            created_at  TEXT    NOT NULL,
            read_at     TEXT    NULL,
            replied_at  TEXT    NULL,
            updated_at  TEXT    NOT NULL
        )',
        'CREATE INDEX IF NOT EXISTS messages_status_created ON messages (status, created_at)',
        'CREATE INDEX IF NOT EXISTS messages_created ON messages (created_at)',
    ];
}

function db_schema_mysql(): array
{
    return [
        'CREATE TABLE IF NOT EXISTS users (
            id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            name          VARCHAR(120) NOT NULL,
            email         VARCHAR(190) NOT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role          VARCHAR(20)  NOT NULL DEFAULT \'staff\',
            is_active     TINYINT(1)   NOT NULL DEFAULT 1,
            last_login_at DATETIME     NULL,
            created_at    DATETIME     NOT NULL,
            updated_at    DATETIME     NOT NULL,
            UNIQUE KEY users_email (email)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
        'CREATE TABLE IF NOT EXISTS messages (
            id          INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            reference   CHAR(16)     NOT NULL,
            name        VARCHAR(120) NOT NULL,
            email       VARCHAR(190) NOT NULL,
            phone       VARCHAR(25)  NOT NULL DEFAULT \'\',
            company     VARCHAR(160) NOT NULL DEFAULT \'\',
            topic       VARCHAR(160) NOT NULL,
            message     TEXT         NOT NULL,
            status      VARCHAR(20)  NOT NULL DEFAULT \'new\',
            notes       TEXT         NULL,
            email_sent  TINYINT(1)   NOT NULL DEFAULT 0,
            ip_hash     CHAR(64)     NOT NULL DEFAULT \'\',
            user_agent  VARCHAR(255) NOT NULL DEFAULT \'\',
            handled_by  INT UNSIGNED NULL,
            created_at  DATETIME     NOT NULL,
            read_at     DATETIME     NULL,
            replied_at  DATETIME     NULL,
            updated_at  DATETIME     NOT NULL,
            UNIQUE KEY messages_reference (reference),
            KEY messages_status_created (status, created_at),
            KEY messages_created (created_at),
            CONSTRAINT messages_handled_by FOREIGN KEY (handled_by) REFERENCES users (id) ON DELETE SET NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
    ];
}
