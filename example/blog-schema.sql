CREATE TABLE IF NOT EXISTS posts (
    id INTEGER PRIMARY KEY,
    title TEXT NOT NULL,
    slug TEXT,
    status TEXT DEFAULT 'draft',
    published_at datetime DEFAULT NULL,
    modified_at datetime,
    created_at datetime DEFAULT CURRENT_TIMESTAMP
);