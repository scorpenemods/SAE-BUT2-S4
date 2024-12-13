--liquibase formatted sql

--changeset LiRuZ:14 labels:Modify-table context:example-context: Add website for get logo
-- comment: Column website added to offers table
ALTER TABLE Offer
    ADD COLUMN website VARCHAR(255);
--rollback ALTER TABLE Offer DROP COLUMN website;


--changeset LiRuZ:15 labels:Modify-table context:example-context: Add website for get logo
-- comment: Column website added to pending_offers table
ALTER TABLE Pending_Offer
    ADD COLUMN website VARCHAR(255);
--rollback ALTER TABLE Pending_Offer DROP COLUMN website;


--changeset Margot:16 labels:create-table
--comment: create table applications
CREATE TABLE Application (
    idUser INTEGER NOT NULL,
    idOffer INTEGER NOT NULL,
    cv VARCHAR(255) NOT NULL,
    motivation_letter VARCHAR(255) NOT NULL,
    FOREIGN KEY (idUser) REFERENCES User (id),
    FOREIGN KEY (idOffer) REFERENCES Offer (id),
    PRIMARY KEY (idUser, idOffer)
);
--rollback DROP TABLE Application;

--changeset Thibaut:17 labels:alter-table
--comment: delete useless columns
ALTER TABLE Application DROP COLUMN IF EXISTS cv;
ALTER TABLE Application DROP COLUMN IF EXISTS motivation_letter;
/* liquibase rollback
ALTER TABLE Application ADD COLUMN cv VARCHAR(255) NOT NULL ;
ALTER TABLE Application ADD COLUMN motivation_letter VARCHAR(255) NOT NULL;
*/

--changeset Margot:18 labels:Alter-table
-- comment: modify the table to save user's filters
Alter table Alert
    add column salary integer NULL,
    add column begin_date varchar(255) NULL,
    modify column duration integer NULL,
    modify column address Varchar(255) NULL,
    modify column study_level Varchar(255) NULL,
drop column IF EXISTS title,
drop column IF EXISTS job,
drop column IF EXISTS distance;
--rollback ALTER TABLE alerts DROP COLUMN salary, DROP COLUMN begin_date, MODIFY COLUMN duration INTEGER NOT NULL, MODIFY COLUMN address VARCHAR(255) NOT NULL, MODIFY COLUMN study_level VARCHAR(255) NOT NULL, ADD COLUMN title VARCHAR(255), ADD COLUMN job VARCHAR(255), ADD COLUMN distance INTEGER;