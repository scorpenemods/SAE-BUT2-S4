--liquibase formatted sql

--changeset LiRuZ:14 labels:Modify-table context:example-context: Add website for get logo
-- comment: Table storing job offers from companies
ALTER TABLE offers ADD COLUMN website VARCHAR(255); -- Add website column to offers table
--rollback ALTER TABLE offers DROP COLUMN website;


--changeset LiRuZ:15 labels:Modify-table context:example-context: Add website for get logo
-- comment: Table for handling pending offers (e.g., offers awaiting approval)
ALTER TABLE pending_offers ADD COLUMN website VARCHAR(255); -- Add website column to pending_offers table
--rollback ALTER TABLE pending_offers DROP COLUMN website;

--changeset LiRuZ:16 labels:Drop-table context:example-context: Remove media table for offers
-- comment: Table storing media associated with job offers
DROP TABLE IF EXISTS offers_media;
--rollback CREATE TABLE offers_media (
-- rollback     id            INTEGER PRIMARY KEY AUTO_INCREMENT, -- Auto-increment primary key for media
-- rollback     offer_id      INTEGER      NOT NULL,              -- Foreign key to the offer
-- rollback     url           VARCHAR(255) NOT NULL,              -- URL of the media file
-- rollback     type          VARCHAR(255) NOT NULL,              -- Type of media (e.g., "video", "image")
-- rollback     description   VARCHAR(255),                       -- Description of the media
-- rollback     display_order INTEGER DEFAULT 0,                  -- Optional display order
-- rollback     FOREIGN KEY (offer_id) REFERENCES offers (id)     -- Foreign key to offers with cascading delete
-- rollback );


--changeset LiRuZ:17 labels:Drop-table context:example-context: Remove media table for pending offers
-- comment: Table storing media associated with pending offers
DROP TABLE IF EXISTS pending_media;
-- rollback CREATE TABLE pending_media (
-- rollback     id               INTEGER PRIMARY KEY AUTO_INCREMENT,          -- Auto-increment primary key for media
-- rollback     pending_offer_id INTEGER      NOT NULL,                       -- Foreign key to the pending offer
-- rollback     url              VARCHAR(255) NOT NULL,                       -- URL of the media file
-- rollback     type             VARCHAR(255) NOT NULL,                       -- Type of media (e.g., "video", "image")
-- rollback     description      VARCHAR(255),                                -- Description of the media
-- rollback     display_order    INTEGER DEFAULT 0,                           -- Optional display order
-- rollback     FOREIGN KEY (pending_offer_id) REFERENCES pending_offers (id) -- Foreign key to pending offers with cascading delete
-- rollback );