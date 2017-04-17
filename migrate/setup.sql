

DROP TABLE IF EXISTS node_versions;
DROP TABLE IF EXISTS nodes;
DROP TABLE IF EXISTS node_types;
DROP TABLE IF EXISTS categories;

CREATE TABLE categories
(
  id INT UNSIGNED PRIMARY KEY NOT NULL,
  localized_name VARCHAR(255),
  identifier VARCHAR(255)
)
  ENGINE=InnoDB
  DEFAULT CHARSET=utf8
;


CREATE TABLE node_types
(
  id INT UNSIGNED PRIMARY KEY NOT NULL,
  identifier VARCHAR(255),
  icon VARCHAR(255),
  localized_name VARCHAR(255),
  category_id INT UNSIGNED NOT NULL,
  FOREIGN KEY (category_id) REFERENCES categories(id)
    ON UPDATE CASCADE ON DELETE CASCADE
)
  ENGINE=InnoDB
  DEFAULT CHARSET=utf8
;

CREATE TABLE nodes
(
  id CHAR(24) PRIMARY KEY NOT NULL,
  name VARCHAR(255),
  lat DECIMAL(10,7),
  lon DECIMAL(10,7),
  wheelchair VARCHAR(32) DEFAULT 'unknown',
  wheelchair_toilet VARCHAR(32) DEFAULT 'unknown',
  wheelchair_description TEXT,
  street VARCHAR(255),
  housenumber VARCHAR(255),
  city VARCHAR(255),
  postcode VARCHAR(255),
  website VARCHAR(255),
  phone VARCHAR(255),
  node_type_id INT UNSIGNED NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  created_at DATETIME,
  updated_at DATETIME,
  FOREIGN KEY fk_nodes_node_types (node_type_id) REFERENCES node_types(id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY fk_nodes_categories (category_id) REFERENCES categories(id)
    ON UPDATE CASCADE ON DELETE CASCADE
)
  ENGINE=InnoDB
  DEFAULT CHARSET=utf8
;

CREATE TABLE node_versions
(
  id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT NOT NULL,
  node_id CHAR(24) NOT NULL,
  version SMALLINT UNSIGNED NOT NULL,
  timestamp DATETIME,
  wheelchair VARCHAR(32) DEFAULT 'unknown',
  wheelchair_toilet VARCHAR(32) DEFAULT 'unknown',
  wheelchair_description TEXT,
  user VARCHAR(255),
  user_id INT UNSIGNED,
  KEY idx_node_versions_node_id_version (node_id, version),
  FOREIGN KEY fk_node_versions_nodes (node_id) REFERENCES nodes(id)
    ON UPDATE CASCADE ON DELETE CASCADE
)
  ENGINE=InnoDB
  DEFAULT CHARSET=utf8
;

