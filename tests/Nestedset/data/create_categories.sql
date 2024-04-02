drop table if exists categories;
CREATE TABLE categories
(
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(255) NOT NULL,
    parent_id  INT          NULL,
    deleted_at DATETIME     NULL,
    _lft INT NOT NULL,
    _rgt INT NOT NULL
);
