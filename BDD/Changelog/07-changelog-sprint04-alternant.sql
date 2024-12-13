--liquibase formatted sql

--changeset Margot:18 labels:Modify-table context:example-context: table company
-- comment: add more details about companies
ALTER TABLE Company
    ADD COLUMN postal_code VARCHAR(10) NOT NULL,
    ADD COLUMN phone_number VARCHAR(20) NOT NULL,
    CHANGE COLUMN Siren Siret VARCHAR(14) NULL,
    ADD COLUMN city VARCHAR(50) NOT NULL,
    ADD COLUMN country VARCHAR(100) NOT NULL,
    ADD COLUMN APE_code VARCHAR(5) NULL,
    ADD COLUMN legal_status VARCHAR(30) NOT NULL;

--rollback ALTER TABLE Company DROP COLUMN postal_code, DROP COLUMN phone_number, CHANGE COLUMN Siret Siren VARCHAR(14) NULL, DROP COLUMN city, DROP COLUMN country, DROP COLUMN APE_code, DROP COLUMN legal_status;
