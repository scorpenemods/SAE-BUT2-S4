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