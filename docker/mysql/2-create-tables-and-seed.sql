USE record-shop;

CREATE TABLE record (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(100) NOT NULL, artist VARCHAR(100) NOT NULL, price NUMERIC(8, 2) NOT NULL, released_year SMALLINT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

INSERT INTO `record-shop`.record (id, title, artist, price, released_year) VALUES (1, 'Appetite for Destruction', 'Guns N'' Roses', 14.99, 1987);
INSERT INTO `record-shop`.record (id, title, artist, price, released_year) VALUES (2, 'The Dark Side of the Moon', 'Pink Floyd', 17.99, null);
INSERT INTO `record-shop`.record (id, title, artist, price, released_year) VALUES (3, 'Use Your Illusion I', 'Guns N'' Roses', 10.99, 1991);


USE record-shop-test;

CREATE TABLE record (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(100) NOT NULL, artist VARCHAR(100) NOT NULL, price NUMERIC(8, 2) NOT NULL, released_year SMALLINT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;