--liquibase formatted sql

--changeset Rémy:1:sprint5 labels:Modify-table context:example-context: table company
-- comment: retirer des colonnes
ALTER TABLE Company
    DROP COLUMN postal_code,
    DROP COLUMN phone_number,
    DROP COLUMN city,
    DROP COLUMN country,
    DROP COLUMN APE_code,
    DROP COLUMN legal_status;


--rollback ALTER TABLE Company ADD COLUMN postal_code VARCHAR(10) NOT NULL  ,ADD COLUMN phone_number VARCHAR(20) NOT NULL,ADD COLUMN city VARCHAR(50) NOT NULL,ADD COLUMN country VARCHAR(100) NOT NULL,ADD COLUMN APE_code VARCHAR(5) NULL,ADD COLUMN legal_status VARCHAR(30) NOT NULL;


--changeset Rémy:2:sprint5 labels:Modify-table context:example-context: table company
-- comment: ajout de colonne
ALTER TABLE Pending_Offer
    ADD COLUMN latitude FLOAT,
    ADD COLUMN longitude FLOAT;


--rollback Alter Table Pending_Offer Drop Column latitude, DROP COLUMN longitude;

--changeset Valerii:3:sprint5 labels:modify groupe message context:example-context: table groupe message
-- comment: erreur de clé étrangere

ALTER TABLE MessageGroupe DROP FOREIGN KEY fk_sender_id;
ALTER TABLE MessageGroupe
    ADD CONSTRAINT fk_sender_id FOREIGN KEY (sender_id) REFERENCES Groupe(user_id) ON DELETE CASCADE;


--rollback ALTER TABLE MessageGroupe DROP FOREIGN KEY fk_sender_id; ALTER TABLE MessageGroupe ADD CONSTRAINT fk_sender_id FOREIGN KEY (sender_id) REFERENCES User(id) ON DELETE CASCADE;

--changeset Lucien:4:sprint5 labels:modify groupe message context:example-context: table Note
-- comment: colonne inutile pour le moment
ALTER TABLE Note
    DROP COLUMN appreciation;

--rollback ALTER TABLE Note ADD appreciation VARCHAR(55);

--changeset Valerii:4:sprint5 labels:modify groupe message context:example-context: table groupe message
-- comment: erreur de clé étrangere

ALTER TABLE MessageGroupe DROP FOREIGN KEY fk_groupe_id;
ALTER TABLE MessageGroupe ADD CONSTRAINT fk_groupe_id FOREIGN KEY (groupe_id) REFERENCES Groupe(conv_id) ON DELETE CASCADE;


--rollback ALTER TABLE MessageGroupe DROP FOREIGN KEY fk_groupe_id;ALTER TABLE MessageGroupe ADD CONSTRAINT fk_groupe_id FOREIGN KEY (groupe_id) REFERENCES Groupe(id) ON DELETE CASCADE;

--changeset Rémy:5:Sprint5 labels:update-foreign-keys
--comment: Ajout des delete Cascade écris en commentaire mais jamais appliqué

ALTER TABLE Offer DROP FOREIGN KEY Offer_ibfk_1;
ALTER TABLE Offer ADD CONSTRAINT fk_offer_company
    FOREIGN KEY (company_id) REFERENCES Company(id) ON DELETE CASCADE;


ALTER TABLE Offer_Media DROP FOREIGN KEY Offer_Media_ibfk_1;
ALTER TABLE Offer_Media ADD CONSTRAINT fk_offer_media_offer
    FOREIGN KEY (offer_id) REFERENCES Offer(id) ON DELETE CASCADE;

ALTER TABLE Tag_Offer DROP FOREIGN KEY Tag_Offer_ibfk_1;
ALTER TABLE Tag_Offer ADD CONSTRAINT fk_tag_offer_offer
    FOREIGN KEY (offer_id) REFERENCES Offer(id) ON DELETE CASCADE;

ALTER TABLE Tag_Offer DROP FOREIGN KEY Tag_Offer_ibfk_2;
ALTER TABLE Tag_Offer ADD CONSTRAINT fk_tag_offer_tag
    FOREIGN KEY (tag_id) REFERENCES Tag(id) ON DELETE CASCADE;

ALTER TABLE Favorite_Offer DROP FOREIGN KEY Favorite_Offer_ibfk_1;
ALTER TABLE Favorite_Offer ADD CONSTRAINT fk_favorite_offer_offer
    FOREIGN KEY (offer_id) REFERENCES Offer(id) ON DELETE CASCADE;

ALTER TABLE Favorite_Offer DROP FOREIGN KEY Favorite_Offer_ibfk_2;
ALTER TABLE Favorite_Offer ADD CONSTRAINT fk_favorite_offer_user
    FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE;

ALTER TABLE Pending_Offer DROP FOREIGN KEY Pending_Offer_ibfk_1;
ALTER TABLE Pending_Offer ADD CONSTRAINT fk_pending_offer_user
    FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE;

ALTER TABLE Pending_Offer DROP FOREIGN KEY Pending_Offer_ibfk_2;
ALTER TABLE Pending_Offer ADD CONSTRAINT fk_pending_offer_company
    FOREIGN KEY (company_id) REFERENCES Company(id) ON DELETE CASCADE;

ALTER TABLE User_Company DROP FOREIGN KEY User_Company_ibfk_1;
ALTER TABLE User_Company ADD CONSTRAINT fk_user_company_user
    FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE;

ALTER TABLE User_Company DROP FOREIGN KEY User_Company_ibfk_2;
ALTER TABLE User_Company ADD CONSTRAINT fk_user_company_company
    FOREIGN KEY (company_id) REFERENCES Company(id) ON DELETE CASCADE;

ALTER TABLE Alert DROP FOREIGN KEY Alert_ibfk_1;
ALTER TABLE Alert ADD CONSTRAINT fk_alert_user
    FOREIGN KEY (user_id) REFERENCES User(id) ON DELETE CASCADE;

-- Step 8: Modify FOREIGN KEY in the Tag_Alert table
ALTER TABLE Tag_Alert DROP FOREIGN KEY Tag_Alert_ibfk_1;
ALTER TABLE Tag_Alert ADD CONSTRAINT fk_tag_alert_alert
    FOREIGN KEY (alert_id) REFERENCES Alert(id) ON DELETE CASCADE;

ALTER TABLE Tag_Alert DROP FOREIGN KEY Tag_Alert_ibfk_2;
ALTER TABLE Tag_Alert ADD CONSTRAINT fk_tag_alert_tag
    FOREIGN KEY (tag_id) REFERENCES Tag(id) ON DELETE CASCADE;


ALTER TABLE Pending_Media DROP FOREIGN KEY Pending_Media_ibfk_1;
ALTER TABLE Pending_Media ADD CONSTRAINT fk_pending_media_pending_offer
    FOREIGN KEY (pending_offer_id) REFERENCES Pending_Offer(id) ON DELETE CASCADE;

ALTER TABLE Pending_Tag DROP FOREIGN KEY Pending_Tag_ibfk_1;
ALTER TABLE Pending_Tag ADD CONSTRAINT fk_pending_tag_pending_offer
    FOREIGN KEY (pending_id) REFERENCES Pending_Offer(id) ON DELETE CASCADE;

ALTER TABLE Pending_Tag DROP FOREIGN KEY Pending_Tag_ibfk_2;
ALTER TABLE Pending_Tag ADD CONSTRAINT fk_pending_tag_tag
    FOREIGN KEY (tag_id) REFERENCES Tag(id) ON DELETE CASCADE;

/* liquibase rollback

ALTER TABLE Offer DROP FOREIGN KEY fk_offer_company;
ALTER TABLE Offer ADD CONSTRAINT Offer_ibfk_1
    FOREIGN KEY (company_id) REFERENCES Company(id);

ALTER TABLE Offer_Media DROP FOREIGN KEY fk_offer_media_offer;
ALTER TABLE Offer_Media ADD CONSTRAINT Offer_Media_ibfk_1
    FOREIGN KEY (offer_id) REFERENCES Offer(id);

ALTER TABLE Tag_Offer DROP FOREIGN KEY fk_tag_offer_offer;
ALTER TABLE Tag_Offer ADD CONSTRAINT Tag_Offer_ibfk_1
    FOREIGN KEY (offer_id) REFERENCES Offer(id);

ALTER TABLE Tag_Offer DROP FOREIGN KEY fk_tag_offer_tag;
ALTER TABLE Tag_Offer ADD CONSTRAINT Tag_Offer_ibfk_2
    FOREIGN KEY (tag_id) REFERENCES Tag(id);

ALTER TABLE Favorite_Offer DROP FOREIGN KEY fk_favorite_offer_offer;
ALTER TABLE Favorite_Offer ADD CONSTRAINT Favorite_Offer_ibfk_1
    FOREIGN KEY (offer_id) REFERENCES Offer(id);

ALTER TABLE Favorite_Offer DROP FOREIGN KEY fk_favorite_offer_user;
ALTER TABLE Favorite_Offer ADD CONSTRAINT Favorite_Offer_ibfk_2
    FOREIGN KEY (user_id) REFERENCES User(id);

ALTER TABLE Pending_Offer DROP FOREIGN KEY fk_pending_offer_user;
ALTER TABLE Pending_Offer ADD CONSTRAINT Pending_Offer_ibfk_1
    FOREIGN KEY (user_id) REFERENCES User(id);

ALTER TABLE Pending_Offer DROP FOREIGN KEY fk_pending_offer_company;
ALTER TABLE Pending_Offer ADD CONSTRAINT Pending_Offer_ibfk_2
    FOREIGN KEY (company_id) REFERENCES Company(id);

ALTER TABLE User_Company DROP FOREIGN KEY fk_user_company_user;
ALTER TABLE User_Company ADD CONSTRAINT User_Company_ibfk_1
    FOREIGN KEY (user_id) REFERENCES User(id);

ALTER TABLE User_Company DROP FOREIGN KEY fk_user_company_company;
ALTER TABLE User_Company ADD CONSTRAINT User_Company_ibfk_2
    FOREIGN KEY (company_id) REFERENCES Company(id);

ALTER TABLE Alert DROP FOREIGN KEY fk_alert_user;
ALTER TABLE Alert ADD CONSTRAINT Alert_ibfk_1
    FOREIGN KEY (user_id) REFERENCES User(id);

ALTER TABLE Tag_Alert DROP FOREIGN KEY fk_tag_alert_alert;
ALTER TABLE Tag_Alert ADD CONSTRAINT Tag_Alert_ibfk_1
    FOREIGN KEY (alert_id) REFERENCES Alert(id);

ALTER TABLE Tag_Alert DROP FOREIGN KEY fk_tag_alert_tag;
ALTER TABLE Tag_Alert ADD CONSTRAINT Tag_Alert_ibfk_2
    FOREIGN KEY (tag_id) REFERENCES Tag(id);

ALTER TABLE Pending_Media DROP FOREIGN KEY fk_pending_media_pending_offer;
ALTER TABLE Pending_Media ADD CONSTRAINT Pending_Media_ibfk_1
    FOREIGN KEY (pending_offer_id) REFERENCES Pending_Offer(id);

ALTER TABLE Pending_Tag DROP FOREIGN KEY fk_pending_tag_pending_offer;
ALTER TABLE Pending_Tag ADD CONSTRAINT Pending_Tag_ibfk_1
    FOREIGN KEY (pending_id) REFERENCES Pending_Offer(id);

ALTER TABLE Pending_Tag DROP FOREIGN KEY fk_pending_tag_tag;
ALTER TABLE Pending_Tag ADD CONSTRAINT Pending_Tag_ibfk_2
    FOREIGN KEY (tag_id) REFERENCES Tag(id);
*/


--changeset Rémy:6:Sprint5 labels:update-foreign-keysV2
--comment: Ajout des delete Cascade écris en commentaire mais jamais appliqué

ALTER TABLE Application DROP FOREIGN KEY Application_ibfk_1;
ALTER TABLE Application DROP FOREIGN KEY Application_ibfk_2;

ALTER TABLE Application ADD CONSTRAINT fk_application_user
    FOREIGN KEY (idUser)
        REFERENCES User(id)
        ON DELETE CASCADE;

ALTER TABLE Application ADD CONSTRAINT fk_application_offer
    FOREIGN KEY (idOffer)
        REFERENCES Offer(id)
        ON DELETE CASCADE;

/* liquibase rollback
   ALTER TABLE Application DROP FOREIGN KEY fk_application_user;
ALTER TABLE Application DROP FOREIGN KEY fk_application_offer;

ALTER TABLE Application ADD CONSTRAINT Application_ibfk_1
    FOREIGN KEY (idUser)
    REFERENCES User(id);

ALTER TABLE Application ADD CONSTRAINT Application_ibfk_2
    FOREIGN KEY (idOffer)
    REFERENCES Offer(id);
 */



--changeset Rémy-Julien:7:Sprint5 labels:ajout de foreingn key
--comment: Ajout de foreign key
Alter TABLE Convention add column id_pre_agreement int;
ALTER TABLE Convention add constraint fk_pre_agreement
    foreign key (id_pre_agreement)
        references Pre_Agreement(id)
        on delete cascade;

-- rollback  ALTER TABLE Convention DROP FOREIGN KEY fk_pre_agreement; ALTER TABLE Convention drop column id_pre_agreement;
