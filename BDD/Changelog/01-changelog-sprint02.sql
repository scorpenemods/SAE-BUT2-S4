--liquibase formatted sql

--changeset your.name:1 labels:create-user context:create-user
--comment: Creation of the User table
CREATE TABLE User (
                      id          INT PRIMARY KEY AUTO_INCREMENT,
                      nom         TEXT not null,
                      prenom      TEXT not null,
                      login       VARCHAR(50) not null,
                      email       VARCHAR(100) not null,
                      telephone   VARCHAR(10) not null,
                      role        TINYINT not null,
                      activite    TEXT not null,
                      status      VARCHAR(20) not null,
                      valid_email BOOLEAN not null default 0
)
--rollback DROP TABLE User;

--changeset your.name:2 labels:create-preference context:create-preference
--comment: Creation of the Preference table
CREATE TABLE Preference (
                            notification    BOOLEAN not null,
                            a2f             BOOLEAN not null,
                            darkmode        BOOLEAN not null,
                            user_id         INT not null,
                            PRIMARY KEY (user_id),
                            CONSTRAINT fk_user_preference FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE
)
--rollback DROP TABLE Preference;

--changeset your.name:3 labels:create-verification-code context:create-verification-code
--comment: Creation of the Verification_Code table
CREATE TABLE Verification_Code (
                                   user_id     INT not null,
                                   code        VARCHAR(6) not null,
                                   expires_at  DATETIME not null,
                                   PRIMARY KEY (user_id),
                                   CONSTRAINT fk_user_verification_code FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE
)
--rollback DROP TABLE Verification_Code;

--changeset your.name:4 labels:create-reset-password context:create-reset-password
--comment: Creation of the Reset_Password table
CREATE TABLE Reset_Password (
                                verification_code   VARCHAR(10) not null ,
                                expires_at          DATETIME not null,
                                user_id             INT not null,
                                PRIMARY KEY (user_id),
                                CONSTRAINT fk_user_reset_password FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE
)
--rollback DROP TABLE Reset_Password;

--changeset your.name:5 labels:create-password context:create-password
--comment: Creation of the Password table
CREATE TABLE Password (
                          id              INT PRIMARY KEY AUTO_INCREMENT,
                          user_id         INT not null,
                          password_hash   VARCHAR(255) not null,
                          actif           BOOLEAN not null,
                          CONSTRAINT fk_user_password FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE
)
--rollback DROP TABLE Password;

--changeset your.name:6 labels:create-convention context:create-convention
--comment: Creation of the Convention table
CREATE TABLE Convention (
                            id          INT PRIMARY KEY AUTO_INCREMENT,
                            convention  VARCHAR(256) not null
)
--rollback DROP TABLE Convention;

--changeset your.name:7 labels:create-groupe context:create-groupe
--comment: Creation of the Groupe table
CREATE TABLE Groupe (
                        conv_id     INT not null,
                        user_id     INT not null,
                        PRIMARY KEY (conv_id, user_id),
                        CONSTRAINT fk_user_groupe FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE,
                        CONSTRAINT fk_convention_groupe FOREIGN KEY (conv_id) REFERENCES Convention(id) ON DELETE CASCADE
)
--rollback DROP TABLE Groupe;

--changeset your.name:8 labels:create-message context:create-message
--comment: Creation of the Message table
CREATE TABLE Message (
                         id          INT PRIMARY KEY AUTO_INCREMENT,
                         sender_id   INT not null,
                         receiver_id INT not null,
                         contenu     TEXT not null,
                         timestamp   DATETIME not null,
                         CONSTRAINT fk_sender_message FOREIGN KEY (sender_id) REFERENCES User(id) ON DELETE CASCADE,
                         CONSTRAINT fk_receiver_message FOREIGN KEY (receiver_id) REFERENCES User(id) ON DELETE CASCADE
)
--rollback DROP TABLE Message;

--changeset your.name:9 labels:create-document context:create-document
--comment: Creation of the Document table
CREATE TABLE Document (
                          id          INT PRIMARY KEY AUTO_INCREMENT,
                          filepath    VARCHAR(256) not null
)
--rollback DROP TABLE Document;

--changeset your.name:10 labels:create-document-message context:create-document-message
--comment: Creation of the Document_Message table
CREATE TABLE Document_Message (
                                  document_id INT not null,
                                  message_id  INT not null,
                                  PRIMARY KEY (document_id, message_id),
                                  CONSTRAINT fk_document_document_message FOREIGN KEY (document_id) REFERENCES Document(id) ON DELETE CASCADE,
                                  CONSTRAINT fk_message_document_message FOREIGN KEY (message_id) REFERENCES Message(id) ON DELETE CASCADE
)
--rollback DROP TABLE Document_Message;

--changeset your.name:12 labels:update-valid-email-default context:update-valid-email-default
--comment: Set default value of valid_email to false in User table
ALTER TABLE User
    ALTER COLUMN valid_email SET DEFAULT false;

--rollback ALTER TABLE User ALTER COLUMN valid_email DROP DEFAULT;

--changeset your.name:13 labels:update-deletelogin context:update-deletelogin
--comment: Remove the login column
ALTER TABLE User
    DROP COLUMN login;

--rollback ALTER TABLE User ADD COLUMN login VARCHAR(50);

--changeset your.name:14 labels:update-statusUSER context:update-statusUSER
--comment: Change the type of the status column to BOOLEAN
ALTER TABLE User
    MODIFY status BOOLEAN;
--rollback ALTER TABLE User MODIFY status VARCHAR(20);

--changeset your.name:15 labels:update-statusUSER2 context:update-statusUSER2
--comment: Add a status2 column
ALTER TABLE User
    ADD status2 BOOLEAN;
--rollback ALTER TABLE User DROP COLUMN status2;

--changeset your.name:16 labels:delete-statusUSER2 context:delete-statusUSER2
--comment: Remove the status column
ALTER TABLE User
    DROP COLUMN status;

--rollback ALTER TABLE User ADD status BOOLEAN;

--changeset your.name:17 labels:delete-statusUSER2 context:delete-statusUSER2
--comment: Rename status2 column to status_user
ALTER TABLE User
    CHANGE status2 status_user BOOLEAN;

--rollback ALTER TABLE User CHANGE status_user status2 BOOLEAN;

--changeset your.name:18 labels:status context:status
--comment: Change the type of the status_user column to BOOLEAN and set the default value to 0
ALTER TABLE User
    MODIFY status_user BOOLEAN DEFAULT 0 NOT NULL;

--rollback ALTER TABLE User MODIFY status_user INT;
