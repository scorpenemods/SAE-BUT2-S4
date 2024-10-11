--liquibase formatted sql


--changeset your.name:1 labels:create-user context:create-user
--comment: Création de la table User
CREATE TABLE User (
                      id          INT PRIMARY KEY AUTO_INCREMENT,
                      nom         TEXT,
                      prenom      TEXT,
                      login       VARCHAR(50),
                      email       VARCHAR(100),
                      telephone   VARCHAR(10),
                      role        TINYINT,
                      activite    TEXT,
                      status      VARCHAR(20),
                      valid_email BOOLEAN
)
--rollback DROP TABLE User;

--changeset your.name:2 labels:create-preference context:create-preference
--comment: Création de la table Preference
CREATE TABLE Preference (
                            notification    BOOLEAN,
                            a2f             BOOLEAN,
                            darkmode        BOOLEAN,
                            user_id         INT,
                            PRIMARY KEY (user_id),
                            CONSTRAINT fk_user_preference FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE
)
--rollback DROP TABLE Preference;

--changeset your.name:3 labels:create-verification-code context:create-verification-code
--comment: Création de la table Verification_Code
CREATE TABLE Verification_Code (
                                   user_id     INT,
                                   code        VARCHAR(6),
                                   expires_at  DATETIME,
                                   PRIMARY KEY (user_id),
                                   CONSTRAINT fk_user_verification_code FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE
)
--rollback DROP TABLE Verification_Code;

--changeset your.name:4 labels:create-reset-password context:create-reset-password
--comment: Création de la table Reset_Password
CREATE TABLE Reset_Password (
                                verification_code   VARCHAR(10),
                                expires_at          DATETIME,
                                user_id             INT,
                                PRIMARY KEY (user_id),
                                CONSTRAINT fk_user_reset_password FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE
)
--rollback DROP TABLE Reset_Password;

--changeset your.name:5 labels:create-password context:create-password
--comment: Création de la table Password
CREATE TABLE Password (
                          id              INT PRIMARY KEY AUTO_INCREMENT,
                          user_id         INT,
                          password_hash   VARCHAR(255),
                          actif           BOOLEAN,
                          CONSTRAINT fk_user_password FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE
)
--rollback DROP TABLE Password;

--changeset your.name:6 labels:create-convention context:create-convention
--comment: Création de la table Convention
CREATE TABLE Convention (
                            id          INT PRIMARY KEY AUTO_INCREMENT,
                            convention  VARCHAR(256)
)
--rollback DROP TABLE Convention;

--changeset your.name:7 labels:create-groupe context:create-groupe
--comment: Création de la table Groupe
CREATE TABLE Groupe (
                        conv_id     INT,
                        user_id     INT,
                        PRIMARY KEY (conv_id, user_id),
                        CONSTRAINT fk_user_groupe FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE,
                        CONSTRAINT fk_convention_groupe FOREIGN KEY (conv_id) REFERENCES Convention(id) ON DELETE CASCADE
)
--rollback DROP TABLE Groupe;

--changeset your.name:8 labels:create-message context:create-message
--comment: Création de la table Message
CREATE TABLE Message (
                         id          INT PRIMARY KEY AUTO_INCREMENT,
                         sender_id   INT,
                         receiver_id INT,
                         contenu     TEXT,
                         timestamp   DATETIME,
                         CONSTRAINT fk_sender_message FOREIGN KEY (sender_id) REFERENCES User(id) ON DELETE CASCADE,
                         CONSTRAINT fk_receiver_message FOREIGN KEY (receiver_id) REFERENCES User(id) ON DELETE CASCADE
)
--rollback DROP TABLE Message;

--changeset your.name:9 labels:create-document context:create-document
--comment: Création de la table Document
CREATE TABLE Document (
                          id          INT PRIMARY KEY AUTO_INCREMENT,
                          filepath    VARCHAR(256)
)
--rollback DROP TABLE Document;

--changeset your.name:10 labels:create-document-message context:create-document-message
--comment: Création de la table Document_Message
CREATE TABLE Document_Message (
                                  document_id INT,
                                  message_id  INT,
                                  PRIMARY KEY (document_id, message_id),
                                  CONSTRAINT fk_document_document_message FOREIGN KEY (document_id) REFERENCES Document(id) ON DELETE CASCADE,
                                  CONSTRAINT fk_message_document_message FOREIGN KEY (message_id) REFERENCES Message(id) ON DELETE CASCADE
)
--rollback DROP TABLE Document_Message;
