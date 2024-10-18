--liquibase formatted sql

--changeset LiRuZ:14 labels:Modify-table context:example-context: Add website for get logo
-- comment: Column website added to offers table
ALTER TABLE offers
    ADD COLUMN website VARCHAR(255);
-- Add website column to offers table
--rollback ALTER TABLE offers DROP COLUMN website;


--changeset LiRuZ:15 labels:Modify-table context:example-context: Add website for get logo
-- comment: Column website added to pending_offers table
ALTER TABLE pending_offers
    ADD COLUMN website VARCHAR(255);
-- Add website column to pending_offers table
--rollback ALTER TABLE pending_offers DROP COLUMN website;

--changeset LiRuZ:16 labels:Drop-table context:example-context: Remove media table for offers
-- comment: Usless now, offers_media table dropped
DROP TABLE IF EXISTS offers_media;
/* liquibase rollback
CREATE TABLE offers_media (
     id            INTEGER PRIMARY KEY AUTO_INCREMENT, -- Auto-increment primary key for media
     offer_id      INTEGER      NOT NULL,              -- Foreign key to the offer
     url           VARCHAR(255) NOT NULL,              -- URL of the media file
     type          VARCHAR(255) NOT NULL,              -- Type of media (e.g., "video", "image")
     description   VARCHAR(255),                       -- Description of the media
     display_order INTEGER DEFAULT 0,                  -- Optional display order
     FOREIGN KEY (offer_id) REFERENCES offers (id)     -- Foreign key to offers with cascading delete
); */


--changeset LiRuZ:17 labels:Drop-table context:example-context: Remove media table for pending offers
-- comment: Usless now, pending_media table dropped
DROP TABLE IF EXISTS pending_media;
/* liquibase rollback
CREATE TABLE pending_media (
    id               INTEGER PRIMARY KEY AUTO_INCREMENT,          -- Auto-increment primary key for media
    pending_offer_id INTEGER      NOT NULL,                       -- Foreign key to the pending offer
    url              VARCHAR(255) NOT NULL,                       -- URL of the media file
    type             VARCHAR(255) NOT NULL,                       -- Type of media (e.g., "video", "image")
    description      VARCHAR(255),                                -- Description of the media
    display_order    INTEGER DEFAULT 0,                           -- Optional display order
    FOREIGN KEY (pending_offer_id) REFERENCES pending_offers (id) -- Foreign key to pending offers with cascading delete
); */


--changeset Margot:18 labels:create-table
--comment: create table applications
CREATE TABLE applications (
    idUser INTEGER NOT NULL,
    idOffer INTEGER NOT NULL,
    cv VARCHAR(255) NOT NULL,
    motivation_letter VARCHAR(255) NOT NULL,
    FOREIGN KEY (idUser) REFERENCES users (id),
    FOREIGN KEY (idOffer) REFERENCES offers (id),
    PRIMARY KEY (idUser, idOffer)
);
--rollback DROP TABLE applications;