--liquibase formatted sql

--changeset LiRuZ:27 labels:Modify-table
--comment: Add latitude and longitude to offers to make precise location
ALTER TABLE pending_offers
    ADD COLUMN latitude FLOAT,
    ADD COLUMN longitude FLOAT;
--rollback ALTER TABLE pending_offers DROP COLUMN latitude, DROP COLUMN longitude;

--changeset LiRuZ:28 labels:Modify-table
--comment: Alter column address to varchar(1024)
ALTER TABLE pending_offers
    MODIFY COLUMN address VARCHAR(1024);
--rollback ALTER TABLE pending_offers MODIFY COLUMN address VARCHAR(255);