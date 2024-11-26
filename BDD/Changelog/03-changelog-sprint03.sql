--liquibase formatted sql


--changeset Noa:1:sprint3 labels:create-logs context:create-logs
--comment: Création de la table Logs pour enregistrer les notifications des secrétaires
CREATE TABLE Logs (
                      id          INT PRIMARY KEY AUTO_INCREMENT,
                      user_id     INT,
                      type        ENUM('INFO', 'WARNING', 'ERROR', 'ACTION') NOT NULL,
                      description TEXT,
                      date        DATETIME,
                      CONSTRAINT fk_user_logs FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE
)
--rollback DROP TABLE Logs;

--changeset Valerii:1:sprint3 labels:retirer le not null du téléphone
--comment: permettre de créer un compte sans numéro de téléphone

Alter table User modify column telephone  VARCHAR(10) null

--rollback Alter table User modify column telephone VARCHAR(10) not null;

--changeset Valerii:2:sprint3 labels:creation de 3 tables
--comment: création de table message groupe, livret de suivi , notification

CREATE TABLE MessageGroupe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    groupe_id INT NOT NULL,
    sender_id INT NOT NULL,
    contenu TEXT NOT NULL,
    timestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_read TINYINT(1) DEFAULT 0 NOT NULL,
    CONSTRAINT fk_groupe_id FOREIGN KEY (groupe_id) REFERENCES Groupe(id) ON DELETE CASCADE,
    CONSTRAINT fk_sender_id FOREIGN KEY (sender_id) REFERENCES User(id) ON DELETE CASCADE
);


CREATE TABLE LivretSuivi (
                             id INT AUTO_INCREMENT PRIMARY KEY,
                             user_id INT NOT NULL,
                             date DATE NOT NULL,
                             description TEXT NOT NULL,
                             status VARCHAR(50) NOT NULL DEFAULT 'En cours',
                             feedback TEXT,
                             CONSTRAINT fk_user_id_livret FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE
);


CREATE TABLE Notification (
                               id INT AUTO_INCREMENT PRIMARY KEY,
                               user_id INT NOT NULL,
                               content TEXT NOT NULL,
                               type VARCHAR(50) NOT NULL,
                               seen TINYINT(1) DEFAULT 0 NOT NULL,
                               created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                               CONSTRAINT fk_user_id_notif FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE
);

--rollback drop table Notification,LivretSuivi,MessageGroupe;


--changeset Lucien:1:sprint3 labels:creation de table note
--comment: creation de table note

CREATE TABLE Note (
                      id INT AUTO_INCREMENT PRIMARY KEY,
                      sujet TEXT NOT NULL,
                      appreciation text not null,
                      note float ,
                      coeff float,
                      user_id int not null,
                      CONSTRAINT fk_user_id_note FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE

);

--rollback drop table Note;

