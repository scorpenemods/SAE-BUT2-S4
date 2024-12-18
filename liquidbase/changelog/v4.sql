--liquibase formatted sql

--changeset LiRuZ:23 labels:Modify-table
--comment: Add title to alerts
ALTER TABLE alerts
ADD COLUMN title VARCHAR(255);
--rollback ALTER TABLE alerts DROP COLUMN title;

--changeset LiRuZ:24 labels:Modify-table
--comment: Add supress to offers
ALTER TABLE offers
ADD COLUMN supress BOOLEAN DEFAULT FALSE;
--rollback ALTER TABLE offers DROP COLUMN supress

--changeset LiRuZ:25 labels:Modify-table
--comment: Add latitude and longitude to offers to make precise location
ALTER TABLE offers
    ADD COLUMN latitude FLOAT,
    ADD COLUMN longitude FLOAT;
--rollback ALTER TABLE offers DROP COLUMN latitude, DROP COLUMN longitude;


