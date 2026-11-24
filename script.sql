DROP DATABASE IF EXISTS azahara_tienda;

CREATE DATABASE azahara_tienda
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE azahara_tienda;

CREATE TABLE usuarios (
    dni       VARCHAR(9)   NOT NULL,
    nombre    VARCHAR(50)  NOT NULL,
    direccion VARCHAR(100) NULL,
    localidad VARCHAR(50)  NULL,
    provincia VARCHAR(50)  NULL,
    telefono  VARCHAR(9)   NULL,
    email     VARCHAR(100) NOT NULL,
    clave     VARCHAR(255) NOT NULL,
    rol       ENUM('admin', 'editor', 'cliente') NOT NULL DEFAULT 'cliente',

    PRIMARY KEY (dni),
    UNIQUE KEY uk_usuarios_email (email)
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE articulos (
    codigo      VARCHAR(8)   NOT NULL,
    nombre      VARCHAR(100) NOT NULL,
    descripcion TEXT         NULL,
    categoria   VARCHAR(50)  NOT NULL,
    precio      DECIMAL(8,2) NOT NULL,
    imagen      VARCHAR(255) NULL,

    PRIMARY KEY (codigo)
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;


-- Datos de prueba: usuarios
INSERT INTO usuarios (dni, nombre, direccion, localidad, provincia, telefono, email, clave, rol) VALUES
    ('12345678Z', 'Azahara Gracia Macia',   'C/ Mayor 10',              'Elche',    'Alicante', '600111222', 'admin@tienda.com',
     '$2y$10$KXboAYi124tMMY1EttFRre4fwlJ0H7MDiUm5192Otsa8A.jUhpgP.', 'admin'),
    ('23456789D', 'Pedro Sánchez Ruiz',     'Avda. de la Libertad 5',   'Alicante', 'Alicante', '611222333', 'editor@tienda.com',
     '$2y$10$Oz.VvXgm97m6aBmO2.XWM./bIvrHXlP7OG0lUhUDe1kSZM0G4uMeO', 'editor'),
    ('34567890V', 'María Gómez Giner',      'Avda. Reyes Católicos 23', 'Alicante', 'Alicante', '677765567', 'maria@gmail.com',
     '$2y$10$gPsXcZgq1V0fkTHjvKIxOuqefALFsguY9pg.R4Mm9yM5xHssmPgwW', 'cliente'),
    ('45678901G', 'Juan José Martínez López','C/ El Peral 24',          'Aspe',     'Alicante', NULL,        'juanjo@hotmail.com',
     '$2y$10$gPsXcZgq1V0fkTHjvKIxOuqefALFsguY9pg.R4Mm9yM5xHssmPgwW', 'cliente'),
    ('56789012B', 'Lucía Fernández Pérez',  'C/ San Juan 7',            'Novelda',  'Alicante', '622333444', 'lucia@gmail.com',
     '$2y$10$gPsXcZgq1V0fkTHjvKIxOuqefALFsguY9pg.R4Mm9yM5xHssmPgwW', 'cliente');

-- Datos de prueba: artículos
INSERT INTO articulos (codigo, nombre, descripcion, categoria, precio, imagen) VALUES
    ('INF10001', 'Portátil 15"',          'Portátil de 15,6 pulgadas, 16 GB RAM, SSD 512 GB', 'Informática',  749.99, NULL),
    ('INF10002', 'Ratón inalámbrico',     'Ratón óptico inalámbrico 2,4 GHz',                 'Informática',   19.95, NULL),
    ('INF10003', 'Teclado mecánico',      'Teclado mecánico retroiluminado, switches red',    'Informática',   59.90, NULL),
    ('INF10004', 'Monitor 27"',           'Monitor IPS 27 pulgadas QHD',                      'Informática',  279.00, NULL),
    ('ELE20001', 'Auriculares Bluetooth', 'Auriculares inalámbricos con cancelación de ruido','Electrónica',   89.99, NULL),
    ('ELE20002', 'Altavoz portátil',      'Altavoz Bluetooth resistente al agua',             'Electrónica',   39.50, NULL),
    ('ELE20003', 'Smartwatch',            'Reloj inteligente con GPS y pulsómetro',           'Electrónica',  129.00, NULL),
    ('HOG30001', 'Cafetera de cápsulas',  'Cafetera automática de 19 bares',                  'Hogar',         79.99, NULL),
    ('HOG30002', 'Lámpara de escritorio', 'Lámpara LED regulable con puerto USB',             'Hogar',         24.95, NULL),
    ('HOG30003', 'Aspiradora robot',      'Robot aspirador con mapeado y control por app',    'Hogar',        199.00, NULL),
    ('DEP40001', 'Esterilla de yoga',     'Esterilla antideslizante de 6 mm',                 'Deportes',      18.50, NULL),
    ('DEP40002', 'Mancuernas 5 kg',       'Pareja de mancuernas de 5 kg recubiertas',         'Deportes',      29.99, NULL),
    ('LIB50001', 'Aprende PHP 8',         'Libro de iniciación a PHP 8 y PDO',                'Libros',        32.00, NULL),
    ('LIB50002', 'MySQL y MariaDB',       'Guía práctica de bases de datos relacionales',     'Libros',        28.50, NULL);
