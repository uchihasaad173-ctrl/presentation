-- ============================================================
--  Club DanDana — Database Schema + Sample Data
-- ============================================================

CREATE DATABASE IF NOT EXISTS clubdandana CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE clubdandana;

-- ------------------------------------------------------------
-- membres
-- ------------------------------------------------------------
CREATE TABLE membres (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom_prenom  VARCHAR(120) NOT NULL,
    telephone   VARCHAR(20)  NOT NULL UNIQUE,
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- admins
-- ------------------------------------------------------------
CREATE TABLE admins (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(60)  NOT NULL UNIQUE,
    password   VARCHAR(255) NOT NULL   -- bcrypt hash
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- evenements
-- ------------------------------------------------------------
CREATE TABLE evenements (
    id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titre              VARCHAR(200) NOT NULL,
    description        TEXT,
    date_event         DATETIME     NOT NULL,
    lieu               VARCHAR(200) NOT NULL,
    prix               DECIMAL(8,2) NOT NULL DEFAULT 0.00,
    places_disponibles INT UNSIGNED NOT NULL DEFAULT 0,
    created_at         TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- inscriptions
-- ------------------------------------------------------------
CREATE TABLE inscriptions (
    id           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    membre_id    INT UNSIGNED NOT NULL,
    evenement_id INT UNSIGNED NOT NULL,
    ticket_code  VARCHAR(64)  NOT NULL UNIQUE,
    utilise      TINYINT(1)   NOT NULL DEFAULT 0,
    date_achat   TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (membre_id)    REFERENCES membres(id)    ON DELETE CASCADE,
    FOREIGN KEY (evenement_id) REFERENCES evenements(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- notifications
-- ------------------------------------------------------------
CREATE TABLE notifications (
    id                INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    message           TEXT         NOT NULL,
    date_notification TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
    lu                TINYINT(1)   NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- ============================================================
--  Sample data
-- ============================================================

-- Admin  (password = "admin123")
INSERT INTO admins (username, password) VALUES
('admin', '$2y$12$Gj5W1s7oL3Pf5z8QKXV1fOT7H.mP3b5JVf9.eQq4R3xSwRhLpSOe');

-- Events
INSERT INTO evenements (titre, description, date_event, lieu, prix, places_disponibles) VALUES
('Nuit Jazz à DanDana',  'Soirée jazz live avec artistes locaux.',          '2025-08-15 21:00:00', 'Club DanDana – Salle principale', 50.00, 100),
('Soirée Gnawa',         'Musique traditionnelle gnawa et ambiance chaleureuse.', '2025-09-01 20:30:00', 'Club DanDana – Terrasse',         30.00, 80),
('Festival Chaabi',      'Grand festival de musique chaabi marocain.',       '2025-09-20 19:00:00', 'Club DanDana – Scène extérieure',  40.00, 200);

-- Sample members
INSERT INTO membres (nom_prenom, telephone) VALUES
('Yassine El Amrani', '0612345678'),
('Sara Benali',       '0698765432');

-- Sample inscriptions
INSERT INTO inscriptions (membre_id, evenement_id, ticket_code, utilise) VALUES
(1, 1, 'TKT-A1B2C3D4E5F6', 0),
(2, 1, 'TKT-Z9Y8X7W6V5U4', 1);
