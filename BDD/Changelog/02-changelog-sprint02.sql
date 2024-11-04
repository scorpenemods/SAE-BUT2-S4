--liquibase formatted sql


--changeset Rémy:1 add date context: user add date
--comment: Add Date to user

alter table User add last_connexion datetime;
alter table User add account_creation datetime;

--rollback ALTER TABLE User   DROP COLUMN last_connexion,   DROP COLUMN account_creation;

--changeset Rémy:2 context:add message not read
--comment: Add 'read' column

ALTER TABLE Message
    ADD COLUMN `read` BOOLEAN NOT NULL DEFAULT FALSE;

--rollback ALTER TABLE Message DROP COLUMN `read`;


--changeset Rémy/Valerii:1 context:create-groupe-new
--comment: Create a new Groupe table with id as auto-increment and user_id as part of the primary key
CREATE TABLE Groupe_New (
                            id INT AUTO_INCREMENT,
                            user_id INT NOT NULL,
                            conv_id INT NULL,
                            PRIMARY KEY (id, user_id),
                            CONSTRAINT fk_convention FOREIGN KEY (conv_id) REFERENCES Convention(id) ON DELETE SET NULL,
                            CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE
);
--rollback DROP TABLE Groupe_New;

--changeset Rémy/Valerii:2 context:transfer-data-to-groupe-new
--comment: Transfer data from old Groupe table to new Groupe_New table, grouping users by conv_id
INSERT INTO Groupe_New (user_id, conv_id)
SELECT user_id, conv_id
FROM Groupe
GROUP BY conv_id, user_id;
--rollback TRUNCATE TABLE Groupe_New;

--changeset Rémy/Valerii:3 context:drop-old-groupe
--comment: Drop the old Groupe table
DROP TABLE Groupe;
--rollback CREATE TABLE Groupe (    conv_id     INT NOT NULL,    user_id     INT NOT NULL,    PRIMARY KEY (conv_id, user_id),    CONSTRAINT fk_user_groupe FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE,    CONSTRAINT fk_convention_groupe FOREIGN KEY (conv_id) REFERENCES Convention(id) ON DELETE CASCADE);

--changeset Rémy/Valerii:4 context:rename-groupe-new
--comment: Rename Groupe_New to Groupe
ALTER TABLE Groupe_New RENAME TO Groupe;
--rollback ALTER TABLE Groupe RENAME TO Groupe_New;

--changeset Rémy:5 context:rename-groupe-new
--comment: unique email
ALTER TABLE User add constraint unique_email UNIQUE(email);
--rollback ALTER TABLE User DROP CONSTRAINT unique_email;
;
