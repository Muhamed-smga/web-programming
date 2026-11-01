    

CREATE DATABASE IF NOT EXISTS sarajevo2b
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE sarajevo2b;

-- 1. USERS TABLE

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. CATEGORIES TABLE

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. PLACES TABLE

CREATE TABLE places (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    address VARCHAR(255) NOT NULL,
    latitude DECIMAL(10,7),
    longitude DECIMAL(10,7),
    phone VARCHAR(30),
    website VARCHAR(255),
    opening_hours TEXT,
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (category_id)
    REFERENCES categories(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE
);


-- 4. EVENTS TABLE

CREATE TABLE events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    place_id INT,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    start_time TIME,
    end_time TIME,
    image_url VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (place_id)
    REFERENCES places(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE
);

-- 5. REVIEWS TABLE

CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    place_id INT NOT NULL,
    rating TINYINT NOT NULL,
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,

    FOREIGN KEY (place_id)
    REFERENCES places(id)
    ON DELETE CASCADE
    ON UPDATE CASCADE,

    CHECK (rating BETWEEN 1 AND 5),

    UNIQUE (user_id, place_id)
);

-- USERS

INSERT INTO users (name, email, password_hash, role)
VALUES
('Admin', 'admin@sarajevo2b.com', 'REPLACE_WITH_PASSWORD_HASH', 'admin'),
('Muhamed', 'user@sarajevo2b.com', 'REPLACE_WITH_PASSWORD_HASH', 'user');

-- CATEGORIES

INSERT INTO categories (name, description)
VALUES
('Coffee Bars', 'Coffee shops and cafes in Sarajevo'),
('Restaurants', 'Restaurants offering local and international food'),
('Sweet Shops', 'Dessert shops, bakeries and pastry shops'),
('Clubs & Bars', 'Nightlife, clubs and bars'),
('Tourist Attractions', 'Historical and cultural attractions');

-- PLACES

INSERT INTO places
(category_id, name, description, address, latitude, longitude, opening_hours)
VALUES
(5, 'Baščaršija',
 'Historic Ottoman bazaar and cultural centre of Sarajevo',
 'Baščaršija, Sarajevo',
 43.8590, 18.4313, 'Public area'),

(5, 'Sarajevo City Hall',
 'One of the most recognizable historical buildings in Sarajevo',
 'Brodačka 1, Sarajevo',
 43.8593, 18.4315, 'Check official website'),

(1, 'Demo Coffee Bar',
 'A sample coffee bar in Sarajevo',
 'Ferhadija, Sarajevo',
 43.8582, 18.4231, '09:00 - 22:00'),

(2, 'Demo Restaurant',
 'A sample restaurant serving local food',
 'Centar, Sarajevo',
 43.8563, 18.4131, '12:00 - 23:00'),

(3, 'Demo Sweet Shop',
 'A sample dessert shop',
 'Old Town, Sarajevo',
 43.8600, 18.4280, '08:00 - 21:00');

-- EVENTS

INSERT INTO events
(place_id, title, description, event_date, start_time, end_time)
VALUES
(1, 'Baščaršija Walking Tour',
 'A guided tour through the historic centre',
 '2027-05-15', '10:00:00', '12:00:00'),

(2, 'Cultural Evening',
 'A sample cultural event at Sarajevo City Hall',
 '2027-05-20', '19:00:00', '21:00:00');

-- REVIEWS

INSERT INTO reviews
(user_id, place_id, rating, comment)
VALUES
(2, 1, 5, 'Beautiful place with lots of history!'),
(2, 2, 5, 'Amazing architecture and atmosphere.');
