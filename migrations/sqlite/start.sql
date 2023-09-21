CREATE TABLE IF NOT EXISTS apps (
    id INTEGER PRIMARY KEY,
    title TEXT NOT NULL,
    prefix TEXT NOT NULL,
    posts_count INTEGER DEFAULT 0,
    modified_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS posts (
    id INTEGER PRIMARY KEY,
    path TEXT NOT NULL,
    app_id INTEGER NOT NULL,
    title TEXT,
    comments_count INTEGER DEFAULT 0,
    modified_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

----
CREATE TRIGGER IF NOT EXISTS posts_ins_after
AFTER
INSERT
    ON posts BEGIN
UPDATE
    apps
SET
    posts_count = posts_count + 1
WHERE
    id = NEW.app_id;

END;

----
CREATE TABLE IF NOT EXISTS comments (
    id INTEGER PRIMARY KEY,
    post_id INTEGER NOT_NULL,
    parent_id INTEGER,
    reply_count INTEGER DEFAULT 0,
    user_name TEXT NOT NULL,
    content TEXT NOT NULL,
    bumped_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

----
CREATE TRIGGER IF NOT EXISTS comments_ins_after
AFTER
INSERT
    ON comments BEGIN
UPDATE
    posts
SET
    comments_count = comments_count + 1
WHERE
    id = NEW.post_id;

-- maybe we have a reply
UPDATE
    comments
SET
    reply_count = reply_count + 1,
    bumped_at = CURRENT_TIMESTAMP
WHERE
    NEW.parent_id NOT NULL
    AND id = NEW.parent_id;

END;