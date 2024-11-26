--liquibase formatted sql

--changeset LiRuZ:1 labels:create-table context:creercompanies
--comment: Create table companies
CREATE TABLE Company (
    id         INTEGER PRIMARY KEY AUTO_INCREMENT,                             -- Auto-increment primary key for companies
    name       VARCHAR(255)        NOT NULL,                                   -- Name of the companies
    size       INTEGER             NOT NULL CHECK (size > 0),                  -- companies size (number of employees), must be positive
    address    VARCHAR(255)        NOT NULL,                                   -- address of the companies
    siren      VARCHAR(255) UNIQUE NOT NULL,                                   -- Unique identifier for the companies (e.g., tax ID)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,                            -- Automatically set timestamp on creation
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP -- Auto-update timestamp on change
);
--rollback DROP TABLE Companie;

--changeset LiRuZ:2 labels:create-table
-- comment: Create table offers
CREATE TABLE Offer (
    id          INTEGER PRIMARY KEY AUTO_INCREMENT,                                          -- Auto-increment primary key for unique offers
    company_id  INTEGER      NOT NULL,                                                       -- Foreign key referencing companies
    title       VARCHAR(255) NOT NULL,                                                       -- Title of the offer
    address     VARCHAR(255),                                                                -- address of the offer
    job         VARCHAR(255) NOT NULL,                                                       -- Job role/title
    description TEXT,                                                                        -- Detailed job description
    duration    INTEGER CHECK (duration > 0),                                                -- Duration in days, must be positive
    is_active   BOOLEAN      NOT NULL DEFAULT 1,                                             -- Offer is active (true/false)
    salary      INTEGER CHECK (salary > 0),                                                  -- Salary in dollars, must be positive
    study_level VARCHAR(255),                                                                -- Study level of the offer
    email       VARCHAR(255),                                                                -- Email of the offer
    phone       VARCHAR(255),                                                                -- Phone number of the offer
    begin_date  DATE,                                                                        -- Date when the offer begins
    created_at  TIMESTAMP             DEFAULT CURRENT_TIMESTAMP,                             -- Automatically set timestamp on creation
    updated_at  TIMESTAMP             DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, -- Auto-update timestamp on change
    FOREIGN KEY (company_id) REFERENCES Company (id)                                       -- Foreign key to companies with cascading delete
);
--rollback DROP TABLE Offer;


--changeset LiRuZ:3 labels:create-table
-- comment: Create table Offer_Media
CREATE TABLE Offer_Media (
    id            INTEGER PRIMARY KEY AUTO_INCREMENT, -- Auto-increment primary key for media
    offer_id      INTEGER      NOT NULL,              -- Foreign key to the offer
    url           VARCHAR(255) NOT NULL,              -- URL of the media file
    type          VARCHAR(255) NOT NULL,              -- Type of media (e.g., "video", "image")
    description   VARCHAR(255),                       -- Description of the media
    display_order INTEGER DEFAULT 0,                  -- Optional display order
    FOREIGN KEY (offer_id) REFERENCES Offer (id)     -- Foreign key to offers with cascading delete
);
--rollback DROP TABLE Offer_Media;

--changeset LiRuZ:4 labels:create-table
-- comment: Create table tag
CREATE TABLE Tag (
    id  INTEGER PRIMARY KEY AUTO_INCREMENT, -- Auto-increment primary key for tags
    tag VARCHAR(255) UNIQUE NOT NULL        -- Unique tag name
);
--rollback DROP TABLE Tag;

--changeset LiRuZ:5 labels:create-table
-- comment: Create table Tag_Offer (relationship table for tagging offers)
CREATE TABLE Tag_Offer (
    offer_id INTEGER NOT NULL,                     -- Foreign key to the offer
    tag_id   INTEGER NOT NULL,                     -- Foreign key to the tag
    PRIMARY KEY (offer_id, tag_id),                -- Composite primary key
    FOREIGN KEY (offer_id) REFERENCES Offer (id), -- Foreign key to offers with cascading delete
    FOREIGN KEY (tag_id) REFERENCES Tag (id)      -- Foreign key to tags with cascading delete
);
--rollback DROP TABLE Tag_Offer;

--changeset LiRuZ:7 labels:create-table
-- comment: Create table Favorite_Offer
CREATE TABLE Favorite_Offer (
    offer_id INTEGER NOT NULL,                     -- Foreign key to the offer
    user_id  INTEGER NOT NULL,                     -- Foreign key to the user
    PRIMARY KEY (offer_id, user_id),               -- Composite primary key
    FOREIGN KEY (offer_id) REFERENCES Offer (id), -- Foreign key to offers with cascading delete
    FOREIGN KEY (user_id) REFERENCES User (id)    -- Foreign key to users with cascading delete
);
--rollback DROP TABLE Favorite_Offer;

--changeset LiRuZ:8 labels:create-table
-- comment: Create table pending_offers
CREATE TABLE Pending_Offer (
    id          INTEGER PRIMARY KEY AUTO_INCREMENT,     -- Auto-increment primary key
    user_id     INTEGER      NOT NULL,                  -- Foreign key to the user
    type        VARCHAR(255) NOT NULL,                  -- Type of offer (e.g., "new offer", "updated offer")
    offer_id    INTEGER,                                -- Foreign key to the offer
    company_id  INTEGER      NOT NULL,                  -- Foreign key referencing companies
    title       VARCHAR(255) NOT NULL,                  -- Title of the offer
    address     VARCHAR(255),                           -- address of the offer
    job         VARCHAR(255) NOT NULL,                  -- Job role/title
    description TEXT,                                   -- Detailed job description
    duration    INTEGER CHECK (duration > 0),           -- Duration in days, must be positive
    salary      INTEGER CHECK (salary > 0),             -- Salary in dollars, must be positive
    study_level VARCHAR(255),                           -- Study level of the offer
    email       VARCHAR(255),                           -- Email of the offer
    phone       VARCHAR(255),                           -- Phone number of the offer
    begin_date  DATE,                                   -- Date when the offer begins
    created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP, -- Automatically set timestamp on creation
    status      VARCHAR(255) DEFAULT 'Pending',         -- Status of the offer
    FOREIGN KEY (company_id) REFERENCES Company (id), -- Foreign key to companies with cascading delete
    FOREIGN KEY (user_id) REFERENCES User (id)         -- Foreign key to users with cascading delete
);
--rollback DROP TABLE Pending_Offer;

--changeset LiRuZ:9 labels:create-table
-- comment: Create table users_companies (relationship table for linking users and companies)
CREATE TABLE User_Company (
    user_id    INTEGER NOT NULL,                       -- Foreign key to the user
    company_id INTEGER NOT NULL,                       -- Foreign key to the company
    PRIMARY KEY (user_id, company_id),                 -- Composite primary key
    FOREIGN KEY (user_id) REFERENCES User (id),       -- Foreign key to users with cascading delete
    FOREIGN KEY (company_id) REFERENCES Company (id) -- Foreign key to companies with cascading delete
);
--rollback DROP TABLE User_Companie;

--changeset LiRuZ:10 labels:create-table
-- comment: Create table alerts (e.g., new offers, updated offers)
CREATE TABLE Alert (
    id          INTEGER PRIMARY KEY AUTO_INCREMENT, -- Auto-increment primary key
    user_id     INTEGER      NOT NULL,              -- Foreign key to the user
    title       VARCHAR(255) NOT NULL,              -- Title of the offer
    job         VARCHAR(255) NOT NULL,              -- Job role/title
    duration    INTEGER CHECK (duration > 0),       -- Duration in days, must be positive
    address     VARCHAR(255),                       -- address of the offer
    distance    INT,                                -- Distance around the address in km
    study_level VARCHAR(255),                       -- Study level of the offer
    FOREIGN KEY (user_id) REFERENCES User (id)     -- Foreign key to users with cascading delete
);
--rollback DROP TABLE Alert;

--changeset LiRuZ:11 labels:create-table
-- comment: Create table tags_alerts (relationship table for tagging alerts)
CREATE TABLE Tag_Alert (
    alert_id INTEGER NOT NULL,                     -- Auto-increment primary key
    tag_id   INTEGER NOT NULL,                     -- Foreign key to the tag
    PRIMARY KEY (alert_id, tag_id),                -- Composite primary key
    FOREIGN KEY (alert_id) REFERENCES Alert (id), -- Foreign key to alerts with cascading delete
    FOREIGN KEY (tag_id) REFERENCES Tag (id)      -- Foreign key to tags with cascading delete
);
--rollback DROP TABLE Tag_Alert;

-- changeset LiRuZ:12 labels:create-table
--comment: Create table pending_media
CREATE TABLE Pending_Media (
    id               INTEGER PRIMARY KEY AUTO_INCREMENT,          -- Auto-increment primary key for media
    pending_offer_id INTEGER      NOT NULL,                       -- Foreign key to the pending offer
    url              VARCHAR(255) NOT NULL,                       -- URL of the media file
    type             VARCHAR(255) NOT NULL,                       -- Type of media (e.g., "video", "image")
    description      VARCHAR(255),                                -- Description of the media
    display_order    INTEGER DEFAULT 0,                           -- Optional display order
    FOREIGN KEY (pending_offer_id) REFERENCES Pending_Offer (id) -- Foreign key to pending offers with cascading delete
);
-- rollback DROP TABLE Pending_Media;

-- changeset LiRuZ:13 labels:create-table
--comment: Create table pending_tags
CREATE TABLE Pending_Tag (
    pending_id INTEGER,                                      -- Foreign key to the pending offer
    tag_id     INTEGER,                                      -- Foreign key to the tag
    PRIMARY KEY (pending_id, tag_id),                        -- Composite primary key
    FOREIGN KEY (pending_id) REFERENCES Pending_Offer (id), -- Foreign key to pending offers with cascading delete
    FOREIGN KEY (tag_id) REFERENCES Tag (id)                -- Foreign key to tags with cascading delete
);
-- rollback DROP TABLE Pending_Tag;