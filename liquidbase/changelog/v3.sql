--liquibase formatted sql

--changeset Margot:20 labels:Alter-table
-- comment: modify the table to save user's filters
Alter table alerts
    add column salary integer NULL,
add column begin_date varchar(255) NULL,
modify column duration integer NULL,
modify column address Varchar(255) NULL,
modify column study_level Varchar(255) NULL,
drop column IF EXISTS title,
drop column IF EXISTS job,
drop column IF EXISTS distance;
--rollback ALTER TABLE alerts DROP COLUMN salary, DROP COLUMN begin_date, MODIFY COLUMN duration INTEGER NOT NULL, MODIFY COLUMN address VARCHAR(255) NOT NULL, MODIFY COLUMN study_level VARCHAR(255) NOT NULL, ADD COLUMN title VARCHAR(255), ADD COLUMN job VARCHAR(255), ADD COLUMN distance INTEGER;

--changeset LiRuZ:21 labels:Modify-table
--comment: Add created_at column to applications table
ALTER TABLE applications
    ADD COLUMN created_at TIMESTAMP;
--rollback ALTER TABLE applications DROP COLUMN created_at;

--changeset LiRuZ:22 labels:Modify-table
--comment: Add default for the collum created_at
ALTER TABLE applications
    ALTER COLUMN created_at SET DEFAULT CURRENT_TIMESTAMP;
--rollback ALTER TABLE applications ALTER COLUMN created_at DROP DEFAULT;

--changeset LiRuZ:23 labels:Modify-table
--comment: Add status column to applications table
ALTER TABLE applications
    ADD COLUMN status VARCHAR(255);
--rollback ALTER TABLE applications DROP COLUMN status;

--changeset LiRuZ:24 labels:Modify-table
--comment: Add favorites column to applications table
ALTER TABLE applications
    ADD COLUMN favorite BOOLEAN;
--rollback ALTER TABLE applications DROP COLUMN favorite;