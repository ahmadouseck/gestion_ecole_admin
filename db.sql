-- Création de la base de données
CREATE DATABASE IF NOT EXISTS eduplateform;
USE eduplateform;

-- CREATION DES TABLES DE BASE

-- Table des administrateurs
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Table des professeurs
CREATE TABLE professeurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    speciality VARCHAR(100) NOT NULL, 
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Table des filieres
CREATE TABLE filieres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200)  NOT NULL,
    level VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);

-- Table des etudiants
CREATE TABLE etudiants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(70)  NOT NULL,
    lastname VARCHAR(50)  NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    id_filiere INT,
    FOREIGN KEY (id_filiere) REFERENCES filieres(id) ON DELETE SET NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL
);


--  INSERTION DES DONNEES POUR LES TESTS 

-- Insertion d'un admin
INSERT INTO admins (username, email, password) VALUES 
('admin', 'admin@eduplateform.com', '123456');
-- Insertion de professeurs
INSERT INTO professeurs (username, email, speciality, password) VALUES 
('prof', 'prof@eduplateform.com','mathematiques', '123456'),
('prof1', 'prof1@eduplateform.com', 'anglais', '1234567'),
('prof2', 'prof2@eduplateform.com','svt',  '1234568');

-- Insertion de filieres
INSERT INTO filieres (name, level) VALUES 
('GENIE LOGICIEL', 'licence 1'),
('RESEAU', 'licence 2'),
('GENIE CIVIL', 'master 1');

-- Insertion de etudiants
INSERT INTO etudiants (firstname, lastname, email, password, id_filiere) VALUES 
('Alpha','Sy', 'etudiant@eduplateform.com','123456',1),
('Modou','Diop','etudiant1@eduplateform.com', '1234567',2),
('Abdou','Fall','etudiant2@eduplateform.com','1234568',1);