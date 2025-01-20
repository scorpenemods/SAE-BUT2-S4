--liquibase formatted sql

--changeset LiRuZ:23 labels:Modify-table
--comment: Add title to alerts
ALTER TABLE Alert
ADD COLUMN title VARCHAR(255);
--rollback ALTER TABLE Alert DROP COLUMN title;

--changeset LiRuZ:24 labels:Modify-table
--comment: Add supress to Offer
ALTER TABLE Offer
ADD COLUMN supress BOOLEAN DEFAULT FALSE;
--rollback ALTER TABLE Offer DROP COLUMN supress

--changeset LiRuZ:25 labels:Modify-table
--comment: Add latitude and longitude to Offer to make precise location
ALTER TABLE Offer
    ADD COLUMN latitude FLOAT,
    ADD COLUMN longitude FLOAT;
--rollback ALTER TABLE Offer DROP COLUMN latitude, DROP COLUMN longitude;

--changeset LiRuZ:26 labels:Modify-table
--comment: Alter column address to varchar(1024)
ALTER TABLE Offer
MODIFY COLUMN address VARCHAR(1024);
--rollback ALTER TABLE Offer MODIFY COLUMN address VARCHAR(255);

--changeset LiRuZ:27 labels:Modify-table
--comment: Alter column address to varchar(1024)
ALTER TABLE Company
    RENAME COLUMN Siret TO siren;
--rollback ALTER TABLE Company RENAME COLUMN siren TO Siret;

--changeset Margot:7:sprint4alternant labels:modify-table context:example-context: table preagreement
-- comment: modify Pre_Agreement table
ALTER TABLE Pre_Agreement
    ADD COLUMN missions_status boolean not null;
--rollback ALTER TABLE Pre_Agreement DROP COLUMN missions_status

--changeset Margot:8:sprint4alternant labels:modify-table context:example-context: table preagreement
-- comment: add column created_at
ALTER TABLE Pre_Agreement
    ADD COLUMN created_at datetime not null;
--rollback ALTER TABLE Pre_Agreement drop column created_at;