--liquibase formatted sql


--changeset Noa:1:sprint4 labels:Documents context: Depot document
--comment: dépot et recupération de document dans l onglet document;


CREATE TABLE File (
                      id INT AUTO_INCREMENT PRIMARY KEY,
                      name VARCHAR(255) NOT NULL, -- Nom du fichier
                      path VARCHAR(500) NOT NULL, -- Chemin de stockage du fichier
                      user_id INT NOT NULL, -- Créateur du fichier
                      size BIGINT NOT NULL, -- Taille du fichier en octets
                      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                      CONSTRAINT fk_user_file FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE
);
--rollback DROP TABLE File;


--changeset Julien:2:sprint4 labels:status groupe context: status groupe
--comment: active groupe and inactive;

ALTER TABLE Groupe
    ADD onStage boolean not null default true;
--rollback ALTER TABLE Groupe DROP onStage;

--changeset Margot:3:sprint4 labels:Modify-table
--comment: Add title to Alert
ALTER TABLE Alert
ADD COLUMN title VARCHAR(255);
--rollback ALTER TABLE Alert DROP COLUMN title;



--changeset Marion:4:sprint4 labels:LivretDeSuivi
--comment: Livret de Suivi - Adding columns and creating new tables

-- Add new columns to FollowUpBook table
ALTER TABLE LivretSuivi
    ADD COLUMN start_date DATE NOT NULL,
    ADD COLUMN end_date DATE NOT NULL,
    ADD COLUMN group_id INT NOT NULL,
    ADD CONSTRAINT fk_group_id FOREIGN KEY (group_id) REFERENCES Groupe(conv_id);

-- Create MeetingBook table
CREATE TABLE MeetingBook (
                             id INT AUTO_INCREMENT PRIMARY KEY,
                             followup_id INT NOT NULL,
                             name TEXT NOT NULL,
                             start_date DATE NOT NULL,
                             end_date DATE NOT NULL,
                             meeting_date DATE,
                             validation BOOLEAN NOT NULL,
                             CONSTRAINT fk_followup_id FOREIGN KEY (followup_id) REFERENCES LivretSuivi(id)
);

-- Create MeetingTexts table
CREATE TABLE MeetingTexts (
                              id INT AUTO_INCREMENT PRIMARY KEY,
                              meeting_id INT NOT NULL,
                              title TEXT NOT NULL,
                              response TEXT,
                              CONSTRAINT fk_meeting_id FOREIGN KEY (meeting_id) REFERENCES MeetingBook(id)
);

-- Create MeetingQCM table
CREATE TABLE MeetingQCM (
                            id INT AUTO_INCREMENT PRIMARY KEY,
                            meeting_id INT NOT NULL,
                            title TEXT NOT NULL,
                            choices TEXT,
                            other_choice TEXT NOT NULL,
                            CONSTRAINT fk_meeting_id2 FOREIGN KEY (meeting_id) REFERENCES MeetingBook(id)
);

-- Rename LivretSuivi table to FollowUpBook
RENAME TABLE LivretSuivi TO FollowUpBook;

/* liquibase rollback
RENAME TABLE FollowUpBook TO LivretSuivi;

-- Supprimer les clés étrangères des tables dépendantes
ALTER TABLE MeetingTexts DROP FOREIGN KEY fk_meeting_id;
ALTER TABLE MeetingQCM DROP FOREIGN KEY fk_meeting_id2;

-- Supprimer les tables liées
DROP TABLE IF EXISTS MeetingTexts;
DROP TABLE IF EXISTS MeetingQCM;
DROP TABLE IF EXISTS MeetingBook;

-- Supprimer les colonnes et les clés étrangères dans FollowUpBook
ALTER TABLE LivretSuivi
    DROP FOREIGN KEY fk_group_id,
    DROP COLUMN start_date,
    DROP COLUMN end_date,
    DROP COLUMN group_id;
*/

--changeset Marion:5:sprint4 labels:LivretDeSuivi
--comment: Livret de Suivi - Removing columns and foreign key, with rollback to restore

-- Suppression de la clé étrangère
ALTER TABLE FollowUpBook
    DROP FOREIGN KEY fk_user_id_livret;

-- Suppression des colonnes
ALTER TABLE FollowUpBook
    DROP COLUMN user_id,
    DROP COLUMN date,
    DROP COLUMN description,
    DROP COLUMN feedback;

/* liquibase rollback
ALTER TABLE FollowUpBook
    ADD COLUMN user_id INT NOT NULL,
    ADD COLUMN date DATE,
    ADD COLUMN description TEXT,
    ADD COLUMN feedback TEXT;

ALTER TABLE FollowUpBook
    ADD CONSTRAINT fk_user_id_livret FOREIGN KEY (user_id) REFERENCES User(id);
*/

--changeset Lucien:6:sprint4 labels:detail note
--comment: detail des notes

CREATE TABLE Sous_Note (
                           sousNote_id INT PRIMARY KEY,
                           description text not null,
                               note_id int not null,
                           CONSTRAINT fk_note_id FOREIGN KEY (note_id) REFERENCES Note(id) ON DELETE CASCADE

)
--rollback DROP TABLE Sous_Note;

--changeset Lucien:7:sprint4 labels:detail note
--comment: detail des notes oubli de colonne

alter table Sous_Note add note int not null;
--rollback ALTER TABLE Sous_Note   DROP COLUMN note;

--changeset Marion:9:sprint4 labels:group id dnas file
--comment: group_id dans File

alter table File
    ADD COLUMN conv_id INT NULL,
    ADD CONSTRAINT fk_conv_id9 FOREIGN KEY (conv_id) REFERENCES Groupe(conv_id) ON DELETE CASCADE;
--rollback ALTER TABLE File Drop Foreign key fk_conv_id9; alter table File drop COLUMN conv_id;

--changeset Lucine:10:sprint4 labels:sous note id dnas file
--comment: sousnote id dans File
ALTER TABLE Sous_Note
    MODIFY COLUMN sousNote_id INT AUTO_INCREMENT;

--rollback ALTER TABLE Sous_Note modify COLUMN sousNote_id  int;