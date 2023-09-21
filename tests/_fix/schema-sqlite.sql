CREATE TABLE testarticles (
    id INTEGER PRIMARY KEY,
    title text NOT NULL,
    status varchar(16) NOT NULL DEFAULT 'draft',
    published_at datetime DEFAULT NULL,
    --    modified_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    created_at datetime DEFAULT CURRENT_TIMESTAMP
);

----
CREATE TABLE cars (
    id INTEGER PRIMARY KEY,
    name text NOT NULL,
    status varchar(16) NOT NULL DEFAULT 'in-stock'
);