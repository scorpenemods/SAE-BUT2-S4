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