--liquibase formatted sql

--changeset LiRuZ:1 labels:create-table context:example-context
--comment: Table that stores companies information
CREATE TABLE companies
(
    id         INTEGER PRIMARY KEY AUTO_INCREMENT,                             -- Auto-increment primary key for companies
    name       VARCHAR(255)        NOT NULL,                                   -- Name of the companies
    size       INTEGER             NOT NULL CHECK (size > 0),                  -- companies size (number of employees), must be positive
    address    VARCHAR(255)        NOT NULL,                                   -- address of the companies
    siren      VARCHAR(255) UNIQUE NOT NULL,                                   -- Unique identifier for the companies (e.g., tax ID)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,                            -- Automatically set timestamp on creation
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP -- Auto-update timestamp on change
);
--rollback DROP TABLE companies;

--changeset LiRuZ:2 labels:create-table context:example-context
-- comment: Table storing job offers from companies
CREATE TABLE offers
(
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
    FOREIGN KEY (company_id) REFERENCES companies (id)                                       -- Foreign key to companies with cascading delete
);
--rollback DROP TABLE offers;


--changeset LiRuZ:3 labels:create-table context:example-context
-- comment: Media associated with job offers
CREATE TABLE offers_media
(
    id            INTEGER PRIMARY KEY AUTO_INCREMENT, -- Auto-increment primary key for media
    offer_id      INTEGER      NOT NULL,              -- Foreign key to the offer
    url           VARCHAR(255) NOT NULL,              -- URL of the media file
    type          VARCHAR(255) NOT NULL,              -- Type of media (e.g., "video", "image")
    description   VARCHAR(255),                       -- Description of the media
    display_order INTEGER DEFAULT 0,                  -- Optional display order
    FOREIGN KEY (offer_id) REFERENCES offers (id)     -- Foreign key to offers with cascading delete
);
--rollback DROP TABLE offers_media;

--changeset LiRuZ:4 labels:create-table context:example-context
-- comment: Table storing unique tags
CREATE TABLE tags
(
    id  INTEGER PRIMARY KEY AUTO_INCREMENT, -- Auto-increment primary key for tags
    tag VARCHAR(255) UNIQUE NOT NULL        -- Unique tag name
);
--rollback DROP TABLE tags;

--changeset LiRuZ:5 labels:create-table context:example-context
-- comment: Relationship table for tagging offers
CREATE TABLE tags_offers
(
    offer_id INTEGER NOT NULL,                     -- Foreign key to the offer
    tag_id   INTEGER NOT NULL,                     -- Foreign key to the tag
    PRIMARY KEY (offer_id, tag_id),                -- Composite primary key
    FOREIGN KEY (offer_id) REFERENCES offers (id), -- Foreign key to offers with cascading delete
    FOREIGN KEY (tag_id) REFERENCES tags (id)      -- Foreign key to tags with cascading delete
);
--rollback DROP TABLE tags_offers;

--changeset LiRuZ:6 labels:create-table context:example-context
-- comment: Table for storing user information
CREATE TABLE users
(
    id       INTEGER PRIMARY KEY AUTO_INCREMENT, -- Auto-increment primary key for users
    username VARCHAR(255) UNIQUE NOT NULL        -- Unique username
);
--rollback DROP TABLE users;

--changeset LiRuZ:7 labels:create-table context:example-context
-- comment: Table for saving favorite offers by users
CREATE TABLE favorite_offers
(
    offer_id INTEGER NOT NULL,                     -- Foreign key to the offer
    user_id  INTEGER NOT NULL,                     -- Foreign key to the user
    PRIMARY KEY (offer_id, user_id),               -- Composite primary key
    FOREIGN KEY (offer_id) REFERENCES offers (id), -- Foreign key to offers with cascading delete
    FOREIGN KEY (user_id) REFERENCES users (id)    -- Foreign key to users with cascading delete
);
--rollback DROP TABLE favorite_offers;

--changeset LiRuZ:8 labels:create-table context:example-context
-- comment: Table for handling pending offers (e.g., offers awaiting approval)
CREATE TABLE pending_offers
(
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
    FOREIGN KEY (company_id) REFERENCES companies (id), -- Foreign key to companies with cascading delete
    FOREIGN KEY (user_id) REFERENCES users (id)         -- Foreign key to users with cascading delete
);
--rollback DROP TABLE pending_offers;

--changeset LiRuZ:9 labels:create-table context:example-context
-- comment: Table that links users and companies
CREATE TABLE users_companies
(
    user_id    INTEGER NOT NULL,                       -- Foreign key to the user
    company_id INTEGER NOT NULL,                       -- Foreign key to the company
    PRIMARY KEY (user_id, company_id),                 -- Composite primary key
    FOREIGN KEY (user_id) REFERENCES users (id),       -- Foreign key to users with cascading delete
    FOREIGN KEY (company_id) REFERENCES companies (id) -- Foreign key to companies with cascading delete
);
--rollback DROP TABLE users_companies;

--changeset LiRuZ:10 labels:create-table context:example-context
-- comment: Table for alerts (e.g., new offers, updated offers)
CREATE TABLE alerts
(
    id          INTEGER PRIMARY KEY AUTO_INCREMENT, -- Auto-increment primary key
    user_id     INTEGER      NOT NULL,              -- Foreign key to the user
    title       VARCHAR(255) NOT NULL,              -- Title of the offer
    job         VARCHAR(255) NOT NULL,              -- Job role/title
    duration    INTEGER CHECK (duration > 0),       -- Duration in days, must be positive
    address     VARCHAR(255),                       -- address of the offer
    distance    INT,                                -- Distance around the address in km
    study_level VARCHAR(255),                       -- Study level of the offer
    FOREIGN KEY (user_id) REFERENCES users (id)     -- Foreign key to users with cascading delete
);
--rollback DROP TABLE alerts;

--changeset LiRuZ:11 labels:create-table context:example-context
-- comment: Table for tagging alerts
CREATE TABLE tags_alerts
(
    alert_id INTEGER NOT NULL,                     -- Auto-increment primary key
    tag_id   INTEGER NOT NULL,                     -- Foreign key to the tag
    PRIMARY KEY (alert_id, tag_id),                -- Composite primary key
    FOREIGN KEY (alert_id) REFERENCES alerts (id), -- Foreign key to alerts with cascading delete
    FOREIGN KEY (tag_id) REFERENCES tags (id)      -- Foreign key to tags with cascading delete
);
--rollback DROP TABLE tags_alerts;


--changeset LiRuZ:12 labels:insert-data-test context:test
--comment: Insert data for testing
INSERT INTO companies (name, size, address, siren)
VALUES ('TechGiant Inc.', 50000, '123 Silicon Valley Blvd, CA 94025', 'TG-123456789'),
       ('HealthCare Solutions', 10000, '456 Wellness Ave, NY 10001', 'HC-987654321'),
       ('GreenEnergy Corp', 5000, '789 Eco Street, TX 75001', 'GE-246813579'),
       ('FinTech Innovations', 2000, '101 Money Lane, IL 60601', 'FI-135792468'),
       ('EduTech Systems', 1500, '202 Learning Road, MA 02108', 'ES-975310864'),
       ('FoodDelivery Express', 3000, '303 Tasty Street, CA 90210', 'FD-864209753'),
       ('AIResearch Labs', 500, '404 Intelligence Ave, WA 98101', 'AI-753951852'),
       ('SpaceTech Enterprises', 7000, '505 Galaxy Road, FL 32899', 'SE-159263748'),
       ('CyberSecurity Experts', 1200, '606 Firewall Blvd, VA 22102', 'CE-357159852'),
       ('BioTech Innovations', 4000, '707 Gene Street, CA 94107', 'BI-951753852'),
       ('CloudComputing Solutions', 6000, '808 Server Lane, TX 78701', 'CC-753159456'),
       ('RoboTech Industries', 2500, '909 Automation Road, MI 48226', 'RI-456789123'),
       ('VirtualReality Systems', 1800, '1010 Immersion Ave, CA 95054', 'VR-789456123'),
       ('EcoFriendly Products', 900, '1111 Green Street, OR 97201', 'EP-321654987'),
       ('SmartHome Technologies', 3500, '1212 IoT Boulevard, WA 98004', 'SH-987321654'),
       ('QuantumComputing Corp', 800, '1313 Qubit Lane, NY 10044', 'QC-654987321'),
       ('DroneDelivery Services', 1300, '1414 Sky Road, NV 89109', 'DD-147258369'),
       ('BlockchainSolutions Inc.', 1100, '1515 Crypto Street, CA 94105', 'BS-369258147'),
       ('AugmentedReality Tech', 2200, '1616 Mixed Reality Ave, MA 02142', 'AR-258369147'),
       ('NanoTech Innovations', 1700, '1717 Microscopic Lane, CA 94304', 'NI-147258369'),
       ('SustainableEnergy Corp', 4500, '1818 Solar Road, AZ 85281', 'SE-963852741'),
       ('GenomicsResearch Labs', 2800, '1919 DNA Avenue, MD 20892', 'GR-741852963'),
       ('AI-Driven Marketing', 1600, '2020 Algorithm Street, NY 10013', 'AM-852963741'),
       ('SmartCity Solutions', 3200, '2121 Urban Tech Road, IL 60654', 'SC-369147258'),
       ('AutonomousVehicle Tech', 5500, '2222 Self-Drive Lane, MI 48103', 'AV-258741963'),
       ('3DPrinting Innovations', 1400, '2323 Additive Ave, CA 95131', '3D-741963852'),
       ('BionicProsthetics Inc.', 2100, '2424 Augmentation Blvd, UT 84112', 'BP-963852741'),
       ('NeuroTech Research', 900, '2525 Brain Interface St, MA 02115', 'NR-852741963'),
       ('SpaceExploration Systems', 6500, '2626 Martian Road, TX 77058', 'SE-741963852'),
       ('RenewableEnergy Solutions', 3800, '2727 Clean Power Ave, CO 80202', 'RE-369852147'),
       ('CyborgTechnologies', 1900, '2828 Human-Machine St, CA 94043', 'CT-258963147'),
       ('QuantumEncryption Corp', 1200, '2929 Secure Qubit Lane, MD 20755', 'QE-147963852'),
       ('BioInformatics Systems', 2600, '3030 Gene Data Road, NC 27709', 'BS-963147852'),
       ('SmartAgriTech', 3100, '3131 Precision Farming Ave, IA 50010', 'SA-852147963'),
       ('FusionEnergy Research', 4200, '3232 Plasma Street, NJ 08540', 'FE-741852369'),
       ('NeuralInterface Labs', 1800, '3333 Mind-Link Road, CA 94305', 'NI-369741852'),
       ('OceanTech Innovations', 2400, '3434 Deep Sea Ave, HI 96813', 'OT-258369741'),
       ('AerospaceSystems Inc.', 7500, '3535 Stratosphere Blvd, WA 98032', 'AS-147258963'),
       ('BionicVision Technologies', 1600, '3636 Retina Road, MA 02139', 'BV-963258147'),
       ('QuantumSensing Solutions', 2000, '3737 Nano-Detector St, CA 94720', 'QS-852369741'),
       ('HyperloopTransport Tech', 5000, '3838 Vacuum Tube Lane, NV 89109', 'HT-741963258'),
       ('ArtificialOrgan Systems', 3300, '3939 Synthetic Biology Ave, MN 55455', 'AO-369852741'),
       ('NanoRobotics Corp', 2700, '4040 Micro-Machine Road, NY 14853', 'NR-258741369'),
       ('BrainComputerInterface', 1500, '4141 Neural Link Street, CA 94720', 'BC-147369852'),
       ('VerticalFarming Solutions', 2900, '4242 Urban Agriculture Rd, IL 60601', 'VF-963741258'),
       ('HolographicDisplay Tech', 2200, '4343 3D Projection Ave, MA 02142', 'HD-852147369'),
       ('ExoskeletonDynamics', 1800, '4444 Power Assist Lane, MI 48109', 'ED-741258963'),
       ('SyntheticFood Systems', 3600, '4545 Lab-Grown Protein St, CA 94107', 'SF-369741258'),
       ('TeleportationResearch', 800, '4646 Quantum Tunnel Road, NY 14853', 'TR-258963741'),
       ('ClimateEngineering Corp', 4100, '4747 Geo-Engineering Ave, WA 98195', 'CE-147852369'),
       ('MolecularAssembly Tech', 2500, '4848 Atomic Construction St, CA 94720', 'MA-963258741'),
       ('CognitiveAI Systems', 3000, '4949 Machine Consciousness Rd, MA 02139', 'CA-852741369'),
       ('FusionPropulsion Labs', 3800, '5050 Interstellar Drive, TX 77058', 'FP-741369852'),
       ('BionicSports Equipment', 2100, '5151 Enhanced Athletics Ave, OR 97331', 'BS-369147852'),
       ('QuantumComputing Cloud', 4500, '5252 Superposition Street, WA 98052', 'QC-258741963'),
       ('NeuromorphicChip Design', 1700, '5353 Brain-Inspired CPU Rd, CA 95054', 'NC-147963258'),
       ('OrbitalManufacturing', 5200, '5454 Zero-G Factory Lane, FL 32899', 'OM-963741852'),
       ('BioRegeneration Systems', 2800, '5555 Tissue Engineering St, MA 02115', 'BR-852369147'),
       ('QuantumSensor Networks', 2300, '5656 Entanglement Ave, NY 14853', 'QS-741852963'),
       ('HapticInterface Tech', 1900, '5757 Touch Feedback Road, CA 94305', 'HI-369852147'),
       ('AstroBiology Research', 2600, '5858 Exoplanet Life Blvd, AZ 85287', 'AB-258147963'),
       ('MemoryAugmentation Labs', 1400, '5959 Cognitive Enhance St, MA 02142', 'MA-147369852'),
       ('PlasmaPhysics Systems', 3100, '6060 Fusion Reactor Ave, NJ 08540', 'PP-963258147'),
       ('BionicHearing Solutions', 2000, '6161 Cochlear Enhance Rd, MN 55455', 'BH-852741963'),
       ('QuantumMetrology Corp', 1600, '6262 Precision Measure St, CO 80309', 'QM-741963258'),
       ('NeuralDust Technologies', 1200, '6363 Nano-Sensor Lane, CA 94720', 'ND-369147852'),
       ('GravityManipulation Tech', 900, '6464 Anti-Grav Research Rd, WA 98195', 'GM-258369147'),
       ('SyntheticNeurobiology', 2400, '6565 Artificial Synapse Ave, MA 02139', 'SN-147852369'),
       ('QuantumCryptography Inc', 1800, '6666 Unhackable Code Street, MD 20742', 'QC-963741258'),
       ('BionicMuscle Systems', 2200, '6767 Strength Augment Road, MI 48109', 'BM-852369741'),
       ('NanoMedicine Research', 3400, '6868 Molecular Therapy Ave, CA 94143', 'NM-741258963'),
       ('ArtificialPhotosynthesis', 2700, '6969 Solar Fuel Street, AZ 85287', 'AP-369852741'),
       ('NeuroMorphic Computing', 2100, '7070 Brain-Like Chip Road, NY 14853', 'NC-258147963'),
       ('Exoplanet Terraforming', 3800, '7171 New Earth Avenue, TX 77058', 'ET-147963852'),
       ('BionicEye Technologies', 1700, '7272 Enhanced Vision St, MA 02139', 'BE-963258741'),
       ('QuantumRadar Systems', 2500, '7373 Stealth Detect Lane, VA 22202', 'QR-852741369'),
       ('NeuralDust Networks', 1300, '7474 Brain-Net Road, CA 94720', 'NN-741369852'),
       ('AntimatterPropulsion Lab', 4200, '7575 Interstellar Drive, NM 87185', 'AP-369741258'),
       ('SyntheticEcosystems Inc', 3100, '7676 Biodome Street, AZ 85287', 'SE-258963741'),
       ('QuantumComputing AI', 3600, '7777 Superintelligence Ave, NY 14853', 'QA-147852963'),
       ('BionicLimb Dynamics', 2300, '7878 Prosthetic Enhance Rd, UT 84112', 'BL-963147852'),
       ('NanoScale Fabrication', 2800, '7979 Atomic Assembly Lane, CA 94720', 'NF-852369147'),
       ('CognitiveVR Interfaces', 2000, '8080 Mind-Machine Street, WA 98195', 'CV-741963258'),
       ('FusionMicroreactors', 3500, '8181 Portable Power Road, NJ 08540', 'FM-369258147'),
       ('BionicSkin Systems', 1900, '8282 Sensory Enhance Ave, MA 02115', 'BS-258741963'),
       ('QuantumTeleportation Net', 1500, '8383 Instant Transfer St, IL 60637', 'QT-147369852'),
       ('NeuroFeedback Tech', 2200, '8484 Brain Training Lane, CA 94305', 'NT-963852741'),
       ('AerogelInsulation Corp', 2600, '8585 Ultralight Material Rd, CO 80309', 'AI-852147369'),
       ('BionicHeart Innovations', 3000, '8686 Cardio Enhance Street, MN 55455', 'BH-741258963'),
       ('QuantumNavigation Systems', 1800, '8787 Precision GPS Avenue, MD 20742', 'QN-369741852'),
       ('NeuralInterface Implants', 2400, '8888 Brain-Chip Road, MA 02139', 'NI-258369741'),
       ('MetamaterialCloaking Tech', 1600, '8989 Invisibility Research St, NY 14853', 'MC-147963258'),
       ('SyntheticBiology Systems', 3200, '9090 Gene Edit Avenue, CA 94143', 'SB-963258147'),
       ('QuantumComputing Security', 2700, '9191 Unhackable Network Rd, MD 20755', 'QS-852741963'),
       ('BionicSense Augmentation', 2100, '9292 Hyper-Perception St, MI 48109', 'BA-741369852'),
       ('NanoScaleEnergy Storage', 3400, '9393 Ultra-Capacity Lane, IL 60637', 'NE-369852147'),
       ('CognitiveAI Assistants', 2900, '9494 Digital Companion Ave, WA 98052', 'CA-258147963'),
       ('FusionSpacePropulsion', 3700, '9595 Deep Space Drive, TX 77058', 'FS-147258369');
-- rollback DELETE FROM companies WHERE true;

--changeset LiRuZ:13 labels:create-table context:test
--comment: Insert data for testing
INSERT INTO offers (company_id, title, address, job, description, duration, salary, study_level, begin_date,
                    email, phone)

VALUES (1, 'Senior Software Engineer', '123 Silicon Valley Blvd, CA 94025', 'Engineering',
        'Develop cutting-edge software solutions', 365, 150000, 'Masters', '2024-01-15', 'careers@techco.com',
        '123-456-7890'),

       (2, 'Medical Research Scientist', '456 Wellness Ave, NY 10001', 'Research',
        'Conduct groundbreaking medical research', 730, 120000, 'PhD', '2024-02-01', 'jobs@medlab.org',
        '234-567-8901'),

       (3, 'Renewable Energy Specialist', '789 Eco Street, TX 75001', 'Engineering',
        'Design innovative renewable energy systems', 365, 110000, 'Masters', '2024-03-01', 'hr@greenpower.com',
        '345-678-9012'),

       (4, 'Financial Analyst', '101 Money Lane, IL 60601', 'Finance', 'Analyze complex financial data', 365,
        95000, 'Bachelors', '2024-01-20', 'careers@finco.com', '456-789-0123'),

       (5, 'EdTech Product Manager', '202 Learning Road, MA 02108', 'Product Management',
        'Lead development of educational technology products', 365, 130000, 'MBA', '2024-02-15',
        'jobs@edtech.com', '567-890-1234'),

       (6, 'Food Delivery App Developer', '303 Tasty Street, CA 90210', 'Mobile Development',
        'Create user-friendly food delivery applications', 180, 105000, 'Bachelors', '2024-03-10',
        'hiring@foodapp.com', '678-901-2345'),

       (7, 'AI Research Scientist', '404 Intelligence Ave, WA 98101', 'Research',
        'Advance the field of artificial intelligence', 730, 160000, 'PhD', '2024-04-01', 'ai-jobs@techgiant.com',
        '789-012-3456'),

       (8, 'Aerospace Engineer', '505 Galaxy Road, FL 32899', 'Engineering', 'Design and develop spacecraft systems',
        365, 140000, 'Masters', '2024-05-01', 'careers@spacetech.com', '890-123-4567'),

       (9, 'Cybersecurity Analyst', '606 Firewall Blvd, VA 22102', 'IT Security',
        'Protect company assets from cyber threats', 365, 115000, 'Bachelors', '2024-02-10',
        'security-jobs@cyberdef.com', '901-234-5678'),

       (10, 'Genetic Researcher', '707 Gene Street, CA 94107', 'Biotechnology',
        'Conduct research in genetic engineering', 730, 125000, 'PhD', '2024-03-15', 'hr@genetech.com',
        '012-345-6789'),

       (11, 'Cloud Solutions Architect', '808 Server Lane, TX 78701', 'IT',
        'Design and implement cloud computing solutions', 365, 135000, 'Masters', '2024-04-01',
        'cloudcareers@techco.com', '123-456-7891'),

       (12, 'Robotics Engineer', '909 Automation Road, MI 48226', 'Engineering', 'Develop advanced robotic systems',
        365, 120000, 'Masters', '2024-05-15', 'jobs@robotics.com', '234-567-8902'),

       (13, 'VR Game Developer', '1010 Immersion Ave, CA 95054', 'Game Development',
        'Create immersive virtual reality games', 180, 110000, 'Bachelors', '2024-06-01', 'careers@vrgames.com',
        '345-678-9013'),

       (14, 'Sustainability Consultant', '1111 Green Street, OR 97201', 'Environmental Science',
        'Advise on eco-friendly business practices', 365, 95000, 'Masters', '2024-03-01', 'jobs@greenconsult.com',
        '456-789-0124'),

       (15, 'IoT Systems Engineer', '1212 IoT Boulevard, WA 98004', 'Engineering',
        'Design and implement Internet of Things solutions', 365, 125000, 'Masters', '2024-04-15',
        'iot-careers@techco.com', '567-890-1235'),

       (16, 'Quantum Computing Researcher', '1313 Qubit Lane, NY 10044', 'Research',
        'Advance quantum computing technologies', 730, 150000, 'PhD', '2024-07-01', 'quantum-jobs@qcomp.com',
        '678-901-2346'),

       (17, 'Drone Operations Manager', '1414 Sky Road, NV 89109', 'Operations', 'Oversee drone delivery operations',
        365, 100000, 'Bachelors', '2024-05-01', 'careers@dronedelivery.com', '789-012-3457'),

       (18, 'Blockchain Developer', '1515 Crypto Street, CA 94105', 'Software Development',
        'Develop blockchain-based applications', 365, 130000, 'Masters', '2024-06-15',
        'blockchain-jobs@cryptotech.com', '890-123-4568'),

       (19, 'AR UX Designer', '1616 Mixed Reality Ave, MA 02142', 'Design',
        'Create user experiences for augmented reality applications', 180, 115000, 'Bachelors', '2024-07-01',
        'design-careers@artech.com', '901-234-5679'),

       (20, 'Nanotechnology Engineer', '1717 Microscopic Lane, CA 94304', 'Engineering',
        'Develop nanotechnology-based solutions', 365, 140000, 'PhD', '2024-08-01', 'nano-jobs@tinytechco.com',
        '012-345-6780'),

       (21, 'Solar Energy Systems Designer', '1818 Solar Road, AZ 85281', 'Engineering',
        'Design efficient solar energy systems', 365, 110000, 'Masters', '2024-09-01', 'careers@solartech.com',
        '123-456-7892'),

       (22, 'Genomics Data Analyst', '1919 DNA Avenue, MD 20892', 'Data Science', 'Analyze complex genomic data', 365,
        120000, 'PhD', '2024-10-01', 'genomics-jobs@biodata.com', '234-567-8903'),

       (23, 'AI Marketing Strategist', '2020 Algorithm Street, NY 10013', 'Marketing',
        'Develop AI-driven marketing strategies', 180, 105000, 'Masters', '2024-11-01', 'ai-marketing@adtech.com',
        '345-678-9014'),

       (24, 'Smart City Planner', '2121 Urban Tech Road, IL 60654', 'Urban Planning',
        'Design and implement smart city technologies', 365, 115000, 'Masters', '2024-12-01',
        'smartcity@urbantech.com', '456-789-0125'),

       (25, 'Autonomous Vehicle Engineer', '2222 Self-Drive Lane, MI 48103', 'Automotive Engineering',
        'Develop self-driving car technologies', 365, 135000, 'PhD', '2025-01-15', 'av-careers@autotech.com',
        '567-890-1236'),

       (26, '3D Printing Specialist', '2323 Additive Ave, CA 95131', 'Manufacturing',
        'Innovate in 3D printing technologies', 180, 95000, 'Bachelors', '2025-02-01', 'jobs@3dprint.com',
        '678-901-2347'),

       (27, 'Bionic Limb Designer', '2424 Augmentation Blvd, UT 84112', 'Biomedical Engineering',
        'Design advanced prosthetic limbs', 365, 130000, 'Masters', '2025-03-01', 'bionics@medtech.com',
        '789-012-3458'),

       (28, 'Neurotechnology Researcher', '2525 Brain Interface St, MA 02115', 'Neuroscience',
        'Develop brain-computer interfaces', 730, 140000, 'PhD', '2025-04-01', 'neurotech@brainlab.com',
        '890-123-4569'),

       (29, 'Space Habitat Architect', '2626 Martian Road, TX 77058', 'Aerospace Architecture',
        'Design habitats for space colonization', 365, 125000, 'Masters', '2025-05-01',
        'space-jobs@marscolony.com', '901-234-5680'),

       (30, 'Wind Energy Technician', '2727 Clean Power Ave, CO 80202', 'Renewable Energy',
        'Maintain and optimize wind turbines', 180, 85000, 'Associates', '2025-06-01', 'windjobs@cleanenergy.com',
        '012-345-6781'),

       (31, 'Cyborg Systems Engineer', '2828 Human-Machine St, CA 94043', 'Bioengineering',
        'Develop human augmentation technologies', 365, 145000, 'PhD', '2025-07-01', 'cyborg@humantech.com',
        '123-456-7893'),

       (32, 'Quantum Cryptography Specialist', '2929 Secure Qubit Lane, MD 20755', 'Information Security',
        'Implement quantum-safe encryption systems', 365, 130000, 'PhD', '2025-08-01',
        'quantum-security@cryptotech.com', '234-567-8904'),

       (33, 'Bioinformatics Programmer', '3030 Gene Data Road, NC 27709', 'Computational Biology',
        'Develop software for genetic data analysis', 365, 115000, 'Masters', '2025-09-01',
        'bioinformatics@genometech.com', '345-678-9015'),

       (34, 'Precision Agriculture Technologist', '3131 Precision Farming Ave, IA 50010', 'Agricultural Technology',
        'Implement smart farming solutions', 180, 90000, 'Bachelors', '2025-10-01', 'agtech@smartfarm.com',
        '456-789-0126'),

       (35, 'Fusion Reactor Engineer', '3232 Plasma Street, NJ 08540', 'Nuclear Engineering',
        'Design and maintain fusion energy systems', 730, 160000, 'PhD', '2025-11-01', 'fusion@energytech.com',
        '567-890-1237'),

       (36, 'Neural Interface Developer', '3333 Mind-Link Road, CA 94305', 'Neurotechnology',
        'Create software for brain-computer interfaces', 365, 135000, 'Masters', '2025-12-01',
        'neurodev@braintech.com', '678-901-2348'),

       (37, 'Deep Sea Robotics Engineer', '3434 Deep Sea Ave, HI 96813', 'Marine Engineering',
        'Design robots for deep ocean exploration', 365, 120000, 'Masters', '2026-01-15',
        'deepseajobs@oceantech.com', '789-012-3459'),

       (38, 'Hypersonic Aircraft Designer', '3535 Stratosphere Blvd, WA 98032', 'Aerospace Engineering',
        'Develop hypersonic aircraft technologies', 365, 150000, 'PhD', '2026-02-01', 'hypersonic@aerotech.com',
        '890-123-4570'),

       (39, 'Bionic Eye Researcher', '3636 Retina Road, MA 02139', 'Biomedical Engineering',
        'Advance artificial vision technologies', 730, 140000, 'PhD', '2026-03-01', 'bioniceye@visiontech.com',
        '901-234-5681'),

       (40, 'Quantum Sensor Developer', '3737 Nano-Detector St, CA 94720', 'Quantum Physics',
        'Create ultra-sensitive quantum detection systems', 365, 130000, 'PhD', '2026-04-01',
        'quantumsensors@nanotech.com', '012-345-6782'),

       (41, 'Hyperloop Systems Engineer', '3838 Vacuum Tube Lane, NV 89109', 'Transportation Engineering',
        'Design and optimize hyperloop transportation systems', 365, 125000, 'Masters', '2026-05-01',
        'hyperloop@speedtech.com', '123-456-7894'),

       (42, 'Artificial Organ Designer', '3939 Synthetic Biology Ave, MN 55455', 'Tissue Engineering',
        'Develop lab-grown organs for transplantation', 730, 145000, 'PhD', '2026-06-01',
        'organsynthesis@biotech.com', '234-567-8905'),

       (43, 'Nanorobotics Programmer', '4040 Micro-Machine Road, NY 14853', 'Nanotechnology',
        'Create software for controlling nanorobots', 365, 130000, 'Masters', '2026-07-01',
        'nanorobotics@tinytech.com', '345-678-9016'),

       (44, 'Brain-Computer Interface Specialist', '4141 Neural Link Street, CA 94720', 'Neurotechnology',
        'Implement and maintain BCI systems', 365, 140000, 'PhD', '2026-08-01', 'bci@neuraltech.com',
        '456-789-0127'),

       (45, 'Vertical Farming Systems Designer', '4242 Urban Agriculture Rd, IL 60601', 'Agricultural Engineering',
        'Design efficient vertical farming solutions', 180, 100000, 'Masters', '2026-09-01',
        'verticalfarm@agritech.com', '567-890-1238'),

       (46, 'Holographic Display Engineer', '4343 3D Projection Ave, MA 02142', 'Optics Engineering',
        'Develop advanced holographic display technologies', 365, 120000, 'Masters', '2026-10-01',
        'holographics@displaytech.com', '678-901-2349'),

       (47, 'Exoskeleton Control Systems Engineer', '4444 Power Assist Lane, MI 48109', 'Robotics Engineering',
        'Design control systems for powered exoskeletons', 365, 130000, 'Masters', '2026-11-01',
        'exoskeletons@robotech.com', '789-012-3460'),

       (48, 'Lab-Grown Meat Scientist', '4545 Lab-Grown Protein St, CA 94107', 'Food Science',
        'Develop cultured meat products', 365, 115000, 'PhD', '2026-12-01', 'labmeat@foodtech.com',
        '890-123-4571');
--rollback DELETE FROM offers WHERE true;

-- changeset LiRuZ:14 labels:create-table context:test
--comment: Insert data for testing
INSERT INTO offers_media (offer_id, url, type, description, display_order)
VALUES (1, 'https://images.unsplash.com/photo-1587620962725-abab7fe55159', 'picture', 'Modern office space', 1),
       (2, 'https://images.unsplash.com/photo-1532187863486-abf9dbad1b69', 'picture', 'Medical research lab', 1),
       (3, 'https://images.unsplash.com/photo-1508514177221-188b1cf16e9d', 'picture', 'Solar panel installation', 1),
       (4, 'https://images.unsplash.com/photo-1554224155-6726b3ff858f', 'picture', 'Financial district', 1),
       (5, 'https://images.unsplash.com/photo-1509062522246-3755977927d7', 'picture', 'Educational technology', 1),
       (6, 'https://images.unsplash.com/photo-1526367790999-0150786686a2', 'picture', 'Food delivery app interface', 1),
       (7, 'https://images.unsplash.com/photo-1620712943543-bcc4688e7485', 'picture', 'AI research center', 1),
       (8, 'https://images.unsplash.com/photo-1517976487492-5750f3195933', 'picture', 'Aerospace engineering facility',
        1),
       (9, 'https://images.unsplash.com/photo-1563986768609-322da13575f3', 'picture', 'Cybersecurity operations center',
        1),
       (10, 'https://images.unsplash.com/photo-1532187863486-abf9dbad1b69', 'picture', 'Genetic research lab', 1),
       (11, 'https://images.unsplash.com/photo-1451187580459-43490279c0fa', 'picture', 'Cloud computing data center',
        1),
       (12, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158', 'picture', 'Advanced robotics lab', 1),
       (13, 'https://images.unsplash.com/photo-1593508512255-86ab42a8e620', 'picture', 'VR game development studio', 1),
       (14, 'https://images.unsplash.com/photo-1497435334941-8c899ee9e8e9', 'picture', 'Sustainable business practices',
        1),
       (15, 'https://images.unsplash.com/photo-1518770660439-4636190af475', 'picture', 'IoT devices showcase', 1),
       (16, 'https://images.unsplash.com/photo-1635070041078-e363dbe005cb', 'picture', 'Quantum computing lab', 1),
       (17, 'https://images.unsplash.com/photo-1473968512647-3e447244af8f', 'picture', 'Drone delivery operation', 1),
       (18, 'https://images.unsplash.com/photo-1639762681485-074b7f938ba0', 'picture', 'Blockchain development team',
        1),
       (19, 'https://images.unsplash.com/photo-1633356122544-f134324a6cee', 'picture', 'AR application demo', 1),
       (20, 'https://images.unsplash.com/photo-1532094349884-543bc11b234d', 'picture',
        'Nanotechnology research facility', 1),
       (21, 'https://images.unsplash.com/photo-1509391366360-2e959784a276', 'picture', 'Solar energy farm', 1),
       (22, 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158', 'picture', 'Genomics data analysis', 1),
       (23, 'https://images.unsplash.com/photo-1551288049-bebda4e38f71', 'picture', 'AI-driven marketing team', 1),
       (24, 'https://images.unsplash.com/photo-1477959858617-67f85cf4f1df', 'picture', 'Smart city control room', 1),
       (25, 'https://images.unsplash.com/photo-1549317661-bd32c8ce0db2', 'picture', 'Autonomous vehicle testing', 1),
       (26, 'https://images.unsplash.com/photo-1581092160607-ee22621dd758', 'picture', '3D printing lab', 1),
       (27, 'https://images.unsplash.com/photo-1530026405186-ed1f139313f8', 'picture', 'Bionic limb prototypes', 1),
       (28, 'https://images.unsplash.com/photo-1580191947416-62d35a55e71d', 'picture',
        'Neurotechnology research center', 1),
       (29, 'https://images.unsplash.com/photo-1446776811953-b23d57bd21aa', 'picture', 'Space habitat concept', 1),
       (30, 'https://images.unsplash.com/photo-1466611653911-95081537e5b7', 'picture', 'Wind turbine farm', 1),
       (31, 'https://images.unsplash.com/photo-1597733336794-12d05021d510', 'picture', 'Cyborg systems lab', 1),
       (32, 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5', 'picture', 'Quantum cryptography setup', 1),
       (33, 'https://images.unsplash.com/photo-1518152006812-edab29b069ac', 'picture', 'Bioinformatics workstation', 1),
       (34, 'https://images.unsplash.com/photo-1586771107445-d3ca888129ff', 'picture', 'Precision agriculture drone',
        1),
       (35, 'https://images.unsplash.com/photo-1563207153-f403bf289096', 'picture', 'Fusion reactor facility', 1),
       (36, 'https://images.unsplash.com/photo-1581092160607-ee22621dd758', 'picture', 'Neural interface prototype', 1),
       (37, 'https://images.unsplash.com/photo-1551244072-5d12893278ab', 'picture', 'Deep sea robotics testing', 1),
       (38, 'https://images.unsplash.com/photo-1559128010-7c1ad6e1b6a5', 'picture', 'Hypersonic aircraft design', 1),
       (39, 'https://images.unsplash.com/photo-1576086213369-97a306d36557', 'picture', 'Bionic eye research lab', 1),
       (40, 'https://images.unsplash.com/photo-1635070041078-e363dbe005cb', 'picture', 'Quantum sensor development', 1),
       (41, 'https://images.unsplash.com/photo-1530122037265-a5f1f91d3b99', 'picture', 'Hyperloop test track', 1),
       (42, 'https://images.unsplash.com/photo-1532187863486-abf9dbad1b69', 'picture', 'Artificial organ lab', 1),
       (43, 'https://images.unsplash.com/photo-1532187863486-abf9dbad1b69', 'picture', 'Nanorobotics research facility',
        1),
       (44, 'https://images.unsplash.com/photo-1580191947416-62d35a55e71d', 'picture', 'Brain-computer interface demo',
        1),
       (45, 'https://images.unsplash.com/photo-1530126483408-aa533e55bdb2', 'picture', 'Vertical farming system', 1),
       (46, 'https://images.unsplash.com/photo-1626908013351-800ddd734b8a', 'picture', 'Holographic display prototype',
        1),
       (47, 'https://images.unsplash.com/photo-1581092160607-ee22621dd758', 'picture', 'Exoskeleton testing facility',
        1),
       (48, 'https://images.unsplash.com/photo-1607623814075-e51df1bdc82f', 'picture', 'Lab-grown meat research', 1),
       (31, 'https://images.unsplash.com/photo-1635070041078-e363dbe005cb', 'picture',
        'Quantum teleportation experiment', 1),
       (32, 'https://images.unsplash.com/photo-1440342359743-84fcb8c21f21', 'picture', 'Climate engineering project',
        1),
       (33, 'https://images.unsplash.com/photo-1532187863486-abf9dbad1b69', 'picture', 'Molecular assembly lab', 1),
       (34, 'https://images.unsplash.com/photo-1620712943543-bcc4688e7485', 'picture', 'AGI research center', 1),
       (35, 'https://images.unsplash.com/photo-1446776811953-b23d57bd21aa', 'picture',
        'Fusion propulsion test facility', 1),
       (36, 'https://images.unsplash.com/photo-1461896836934-ffe607ba8211', 'picture',
        'Bionic sports equipment testing', 1),
       (36, 'https://images.unsplash.com/photo-1635070041078-e363dbe005cb', 'picture', 'Quantum cloud computing center',
        1),
       (37, 'https://images.unsplash.com/photo-1518770660439-4636190af475', 'picture', 'Neuromorphic chip design lab',
        1),
       (23, 'https://images.unsplash.com/photo-1446776877081-d282a0f896e2', 'picture', 'Orbital manufacturing concept',
        1),
       (43, 'https://images.unsplash.com/photo-1532187863486-abf9dbad1b69', 'picture', 'Tissue regeneration research',
        1),
       (23, 'https://images.unsplash.com/photo-1635070041078-e363dbe005cb', 'picture', 'Quantum sensor network demo',
        1),
       (12, 'https://images.unsplash.com/photo-1593508512255-86ab42a8e620', 'picture', 'Haptic interface testing', 1),
       (32, 'https://images.unsplash.com/photo-1446776811953-b23d57bd21aa', 'picture', 'Astrobiology research facility',
        1),
       (43, 'https://images.unsplash.com/photo-1580191947416-62d35a55e71d', 'picture', 'Cognitive enhancement lab', 1);
-- rollback DELETE FROM offers_media WHERE true;

-- changeset LiRuZ:15 labels:create-table context:test
--comment: Insert data for testing;
INSERT INTO tags (id, tag)
VALUES (27, 'ADA');
INSERT INTO tags (id, tag)
VALUES (19, 'B');
INSERT INTO tags (id, tag)
VALUES (17, 'Bash');
INSERT INTO tags (id, tag)
VALUES (2, 'C');
INSERT INTO tags (id, tag)
VALUES (13, 'C#');
INSERT INTO tags (id, tag)
VALUES (24, 'C3');
INSERT INTO tags (id, tag)
VALUES (25, 'Carbon');
INSERT INTO tags (id, tag)
VALUES (28, 'Cobol');
INSERT INTO tags (id, tag)
VALUES (3, 'CPP');
INSERT INTO tags (id, tag)
VALUES (22, 'CSS');
INSERT INTO tags (id, tag)
VALUES (11, 'Flutter');
INSERT INTO tags (id, tag)
VALUES (6, 'Golang');
INSERT INTO tags (id, tag)
VALUES (8, 'Haskell');
INSERT INTO tags (id, tag)
VALUES (21, 'HTML');
INSERT INTO tags (id, tag)
VALUES (4, 'Java');
INSERT INTO tags (id, tag)
VALUES (5, 'JavaScript');
INSERT INTO tags (id, tag)
VALUES (26, 'Kotlin');
INSERT INTO tags (id, tag)
VALUES (1, 'Lua');
INSERT INTO tags (id, tag)
VALUES (30, 'Objective-C');
INSERT INTO tags (id, tag)
VALUES (9, 'OCaml');
INSERT INTO tags (id, tag)
VALUES (29, 'Perl');
INSERT INTO tags (id, tag)
VALUES (18, 'PHP');
INSERT INTO tags (id, tag)
VALUES (12, 'Python');
INSERT INTO tags (id, tag)
VALUES (20, 'R');
INSERT INTO tags (id, tag)
VALUES (16, 'Ruby');
INSERT INTO tags (id, tag)
VALUES (7, 'Rust');
INSERT INTO tags (id, tag)
VALUES (10, 'Swift');
INSERT INTO tags (id, tag)
VALUES (23, 'TypeScript');
INSERT INTO tags (id, tag)
VALUES (15, 'VBscript');
-- rollback DELETE FROM tags WHERE true;

-- changeset LiRuZ:16 labels:create-table context:test
--comment: Insert data for testing
INSERT INTO users (id, username)
VALUES (1, 'LiRuZ'),
       (2, 'Thibaut'),
       (3, 'Leo'),
       (4, 'Alex');
-- rollback DELETE FROM users WHERE true;

-- changeset LiRuZ:17 labels:create-table context:test
--comment: Insert data for testing
INSERT INTO pending_offers (user_id, type, company_id, title, address, job, description, duration, salary, study_level,
                            email, phone, begin_date, offer_id)
VALUES (1, 'new offer', 1, 'Senior Software Engineer', '123 Silicon Valley Blvd, CA 94025', 'Engineering',
        'Develop cutting-edge software solutions', 365, 150000, 'Masters', 'careers@techco.com', '123-456-7890',
        '2024-01-15', 0),
       (2, 'new offer', 1, 'Medical Research Scientist', '456 Wellness Ave, NY 10001', 'Research',
        'Conduct groundbreaking medical research', 730, 120000, 'PhD', 'jobs@medlab.org', '234-567-8901', '2024-02-01', 0),
       (3, 'new offer', 1, 'Renewable Energy Specialist', '789 Eco Street, TX 75001', 'Engineering',
        'Design innovative renewable energy systems', 365, 110000, 'Masters', 'hr@greenpower.com', '345-678-9012',
        '2024-03-01', 0),
       (4, 'new offer', 1, 'Financial Analyst', '101 Money Lane, IL 60601', 'Finance', 'Analyze complex financial data',
        365, 95000, 'Bachelors', 'careers@finco.com', '456-789-0123', '2024-01-20', 0);

INSERT INTO pending_offers (user_id, type, offer_id, company_id, title, address, job, description, duration, salary,
                            study_level, email, phone, begin_date)
VALUES (1, 'updated offer', 1, 1, 'Senior Software Engineer', '123 Silicon Valley Blvd, CA 94025', 'Engineering',
        'Develop cutting-edge software solutions', 365, 150000, 'Masters', 'careers@techco.com', '123-456-7890',
        '2024-01-15'),
       (2, 'updated offer', 2, 2, 'Medical Research Scientist', '456 Wellness Ave, NY 10001', 'Research',
        'Conduct groundbreaking medical research', 730, 120000, 'PhD', 'jobs@medlab.org', '234-567-8901', '2024-02-01'),
       (3, 'updated offer', 3, 3, 'Renewable Energy Specialist', '789 Eco Street, TX 75001', 'Engineering',
        'Design innovative renewable energy systems', 365, 110000, 'Masters', 'hr@greenpower.com', '345-678-9012',
        '2024-03-01'),
       (4, 'updated offer', 4, 4, 'Financial Analyst', '101 Money Lane, IL 60601', 'Finance',
        'Analyze complex financial data', 365, 95000, 'Bachelors', 'careers@finco.com', '456-789-0123', '2024-01-20');
-- rollback DELETE FROM pending_offers WHERE true;

-- changeset LiRuZ:18 labels:create-table context:test
--comment: Create table for storing pending media
CREATE TABLE pending_media
(
    id               INTEGER PRIMARY KEY AUTO_INCREMENT,          -- Auto-increment primary key for media
    pending_offer_id INTEGER      NOT NULL,                       -- Foreign key to the pending offer
    url              VARCHAR(255) NOT NULL,                       -- URL of the media file
    type             VARCHAR(255) NOT NULL,                       -- Type of media (e.g., "video", "image")
    description      VARCHAR(255),                                -- Description of the media
    display_order    INTEGER DEFAULT 0,                           -- Optional display order
    FOREIGN KEY (pending_offer_id) REFERENCES pending_offers (id) -- Foreign key to pending offers with cascading delete
);
-- rollback DROP TABLE pending_media;

-- changeset LiRuZ:19 labels:create-table context:test
--comment: Create table for storing pending tags
CREATE TABLE pending_tags
(
    pending_id INTEGER,                                      -- Foreign key to the pending offer
    tag_id     INTEGER,                                      -- Foreign key to the tag
    PRIMARY KEY (pending_id, tag_id),                        -- Composite primary key
    FOREIGN KEY (pending_id) REFERENCES pending_offers (id), -- Foreign key to pending offers with cascading delete
    FOREIGN KEY (tag_id) REFERENCES tags (id)                -- Foreign key to tags with cascading delete
);
-- rollback DROP TABLE pending_tags;