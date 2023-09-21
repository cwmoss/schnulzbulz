CREATE TABLE testarticles (
    id int(11) NOT NULL AUTO_INCREMENT,
    title text NOT NULL,
    status varchar(16) NOT NULL DEFAULT 'draft',
    published_at datetime DEFAULT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    --  modified_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
);

----
CREATE TABLE cars (
    id int(11) NOT NULL AUTO_INCREMENT,
    name text NOT NULL,
    status varchar(16) NOT NULL DEFAULT 'in-stock',
    PRIMARY KEY (id)
);