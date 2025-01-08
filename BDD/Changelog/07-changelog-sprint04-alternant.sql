--liquibase formatted sql

--changeset Margot:1:sprint4alternant labels:Modify-table context:example-context: table company
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

--changeset Margot:2:sprint4alternant labels:add-table context:example-context: table pré-convention
-- comment: add table
Create table Pre_Agreement(
    id INT PRIMARY KEY AUTO_INCREMENT,
    idGroup integer not null,
    status boolean not null,
    inputs varchar(255) not null,
    CONSTRAINT fk_id_group_users FOREIGN KEY (idGroup) REFERENCES Groupe(id) ON DELETE CASCADE
);

--rollback drop table Pre_Agreement;

--changeset Margot:3:sprint4alternant labels:modify-table context:example-context: table pré-convention
-- comment: modify Pre_Agreement table
ALTER TABLE Pre_Agreement
    DROP FOREIGN KEY fk_id_group_users,
    ADD COLUMN idStudent integer not null,
    ADD COLUMN idMentor integer null,
    ADD COLUMN idProfessor integer null,
    drop column idGroup,
    ADD CONSTRAINT fk_id_student FOREIGN KEY (idStudent) REFERENCES User(id) ON DELETE CASCADE,
    ADD CONSTRAINT fk_id_mentor FOREIGN KEY (idMentor) REFERENCES User(id) ON DELETE SET NULL,
    ADD CONSTRAINT fk_id_professor FOREIGN KEY (idProfessor) REFERENCES User(id) ON DELETE SET NULL;
--rollback ALTER TABLE DROP FOREIGN KEY fk_id_student, DROP FOREIGN KEY fk_id_mentor, DROP FOREIGN KEY fk_id_professor,Pre_Agreement DROP COLUMN idStudent, DROP COLUMN idMentor, DROP COLUMN idProfessor, ADD COLUMN idGroup, ADD CONSTRAINT fk_id_group_users FOREIGN KEY (idGroup) REFERENCES Groupe(id) ON DELETE CASCADE;



--changeset Margot:4:sprint4alternant labels:modify-table context:example-context: table pré-convention
-- comment: modify Pre_Agreement table
ALTER TABLE Pre_Agreement
    CHANGE COLUMN inputs inputs JSON not null;
--rollback ALTER TABLE CHANGE COLUMN inputs inputs VARCHAR(14) not null;

--changeset Margot:5:sprint4alternant labels:modify-table context:example-context: table pré-convention
-- comment: modify Pre_Agreement table
ALTER TABLE Pre_Agreement
    CHANGE COLUMN inputs inputs JSON not null;
--rollback ALTER TABLE Pre_Agreement CHANGE COLUMN inputs inputs VARCHAR(14) not null;


--changeset Margot:6:sprint4alternant labels:modify-table context:example-context: table convention
-- comment: modify Convention table
ALTER TABLE Convention
    ADD COLUMN path_convention varchar(500) null;
--rollback ALTER TABLE Convention drop column path_convention;





