CREATE DATABASE IF NOT EXISTS hw1;
USE hw1;

CREATE TABLE utenti (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(16) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    data_registrazione DATETIME DEFAULT CURRENT_TIMESTAMP,
    ultimo_accesso DATETIME,
    avatar VARCHAR(255) DEFAULT '/assets/images/avatar.png',
    karma_post INTEGER DEFAULT 0,
    karma_commenti INTEGER DEFAULT 0,
    bio TEXT,
    is_admin BOOLEAN DEFAULT FALSE
);

CREATE TABLE post (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    user_id INTEGER NULL,
    reddit_id VARCHAR(20) NOT NULL UNIQUE,
    subreddit VARCHAR(50) NOT NULL,
    titolo VARCHAR(500) NOT NULL,
    autore VARCHAR(100) NOT NULL,
    contenuto TEXT,
    tipo_contenuto VARCHAR(10) NULL,
    url VARCHAR(500),
    thumbnail VARCHAR(500),
    immagine_base64 LONGTEXT,
    voto INTEGER DEFAULT 0,
    data_salvataggio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE CASCADE
);

CREATE TABLE commenti (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    post_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    contenuto TEXT NOT NULL,
    data_commento TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES post(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE CASCADE
);

CREATE TABLE voti_utenti (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    user_id INTEGER NOT NULL,
    post_id INTEGER NOT NULL,
    tipo_voto TINYINT NOT NULL,
    data_voto TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES utenti(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES post(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_post_vote (user_id, post_id)
);

INSERT INTO utenti (username, password, email, avatar, bio, is_admin) 
VALUES (
    'will',
    '$2y$10$.e5qFQsmVSfvzL4PUa.RO.B/Inh/e8UnGwLO7USaSXI5epFCOO6Je',
    '1337',
    '/assets/images/img.png',
    '1337',
    TRUE
);