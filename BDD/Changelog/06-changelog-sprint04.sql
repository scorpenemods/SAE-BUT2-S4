--liquibase formatted sql


--changeset Noa:1:sprint4 labels:Documents context: Depot document
--comment: dépot et recupération de document dans l'onglet document;


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

--rollback RENAME TABLE FollowUpBook TO LivretSuivi;ALTER TABLE LivretSuivi DROP FOREIGN KEY fk_group_id,DROP COLUMN start_date,DROP COLUMN end_date,DROP COLUMN group_id;DROP TABLE IF EXISTS MeetingBook CASCADE;DROP TABLE IF EXISTS MeetingTexts CASCADE;DROP TABLE IF EXISTS MeetingQCM CASCADE;Drop MeetingBook, MeetingTexts, and MeetingQCM tables


