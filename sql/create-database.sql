/* Création de la base de donnée */
USE master;
GO

/* Nettoyage : supprimer toute base ayant le même nom et coupe toute autre connexion en cours  */
IF DB_ID('laine_pdo') IS NOT NULL
    BEGIN
        ALTER DATABASE laine_pdo
            SET SINGLE_USER
            WITH ROLLBACK IMMEDIATE;
        DROP DATABASE laine_pdo;
    END
GO

CREATE DATABASE laine_pdo;
GO

/* Connexion (login et user) */
USE master;
GO

/* Supprimer herboristerie_user s'il existe */
IF SUSER_ID('laine_user') IS NOT NULL
    DROP LOGIN laine_user;
GO

/* Crée herboristerie_user */
CREATE LOGIN laine_user
    WITH PASSWORD = 'Test1234=', DEFAULT_DATABASE = laine_pdo, CHECK_POLICY = OFF;
GO

USE laine_pdo;
GO

DROP USER IF EXISTS laine_user;
GO

CREATE USER laine_user FOR LOGIN laine_user;
GO

/* On accorde les droits lecture et écriture au laine_user*/
ALTER ROLE db_datareader ADD MEMBER laine_user;
ALTER ROLE db_datawriter ADD MEMBER laine_user;
GO

USE laine_pdo;
GO

-- On supprime d'abord les tables enfants (FK)
DROP TABLE IF EXISTS dbo.favoris; -- deux FK
DROP TABLE IF EXISTS dbo.transformation; -- une FK
DROP TABLE IF EXISTS dbo.pelote; -- une FK
DROP TABLE IF EXISTS dbo.eleveur; -- pas de FK mais est parent de pelote
DROP TABLE IF EXISTS dbo.client; -- pas de FK
GO

SET QUOTED_IDENTIFIER ON;
GO

-- Création des tables dans l'ordre parent -> enfant

CREATE TABLE dbo.eleveur (
    id              INT   IDENTITY (1, 1) NOT NULL,
    nom_ferme       NVARCHAR (100)   NOT NULL,
    province        NVARCHAR (50)    NOT NULL,
    philosophie     NVARCHAR (500)   NULL,
    CONSTRAINT PK_eleveur PRIMARY KEY (id)
);
GO

CREATE TABLE dbo.client (
    id            INT    IDENTITY (1, 1) NOT NULL,
    nom           NVARCHAR (100) NOT NULL,
    prenom        NVARCHAR (100) NOT NULL,
    mot_de_passe  NVARCHAR (255) NOT NULL,
    email         NVARCHAR (255) NOT NULL,
    telephone     NVARCHAR (20)  NOT NULL,
    role          NVARCHAR (10)  NOT NULL  -- ajout de la colonne rôle ici
    CONSTRAINT DF_client_role DEFAULT 'user', -- tout le monde est client 'user' par défaut   
    date_creation DATETIME2(0) NOT NULL -- date d'inscription automatique
    CONSTRAINT DF_client_date DEFAULT SYSDATETIME(),
    CONSTRAINT PK_client PRIMARY KEY (id),
    CONSTRAINT UQ_client_email UNIQUE (email) -- clé unique pour empècher deux inscriptions avec le même e-mail
);
GO

CREATE TABLE dbo.pelote (
    id                  INT     IDENTITY (1, 1) NOT NULL,
    nom_commercial      NVARCHAR (100)  NOT NULL,
    race_mouton         NVARCHAR (100)  NOT NULL,
    description         NVARCHAR (250)  NULL,
    conseil_lavage      NVARCHAR (250)  NULL,
    quantite_stock      SMALLINT        NOT NULL,
    prix_unitaire       DECIMAL  (6, 2) NOT NULL,
    eleveur_id          INT             NOT NULL,
    CONSTRAINT PK_pelote PRIMARY KEY (id),
    CONSTRAINT CK_pelote_prix_unitaire  CHECK (prix_unitaire >= 0), -- une Check Key pour empècher des chiffres négatifs
    CONSTRAINT CK_pelote_quantite_stock CHECK (quantite_stock >= 0),
    CONSTRAINT FK_pelote_eleveur FOREIGN KEY (eleveur_id) REFERENCES dbo.eleveur (id) ON DELETE CASCADE
);
GO

CREATE TABLE dbo.transformation (
    id                  INT    IDENTITY (1, 1) NOT NULL,
    type_action         NVARCHAR(50)    NOT NULL, -- Tonte, lavage, cardage, filage, teinture
    nom_intervenant     NVARCHAR(250)   NOT NULL,
    ordre_etape         SMALLINT        NOT NULL, -- pour le tri php
    pelote_id           INT             NOT NULL,
    CONSTRAINT PK_transformation PRIMARY KEY (id),
    CONSTRAINT FK_transformation_pelote FOREIGN KEY (pelote_id) REFERENCES dbo.pelote (id) ON DELETE CASCADE
);
GO

CREATE TABLE dbo.favori (
    client_id    INT NOT NULL,
    pelote_id    INT NOT NULL,
    CONSTRAINT FK_favori_client FOREIGN KEY (client_id) REFERENCES dbo.client (id) ON DELETE CASCADE,
    CONSTRAINT FK_favori_pelote FOREIGN KEY (pelote_id) REFERENCES dbo.pelote (id) ON DELETE CASCADE,
    CONSTRAINT PK_favori PRIMARY KEY (client_id, pelote_id)
);

-- Index sur la clé étrangère transformation_pelote
CREATE INDEX IX_transformation_pelote_id 
    ON dbo.transformation(pelote_id);
GO

-- Insertion des données pour chaque table
INSERT INTO dbo.eleveur (nom_ferme, province, philosophie)
VALUES 
(N'Bergerie du Gros Cron', N'Luxembourg', N'Élevage extensif écoresponsable axé sur la sauvegarde des races menacées.'),
(N'Élevage du Grand Cortil', N'Namur', N'Ferme biologique certifiée veillant au bien-être animal et pâturage tournant.'),
(N'L’amour et la laine', N'Brabant Wallon', N'Bergerie et projet d’écopâturage vendant sa propre laine et proposant des ateliers artisanaux de feutrage.'),
(N'La lainerie urbaine', N'Bruxelles', N'Élevage citoyen et éco-pâturage de moutons rustiques de race Zwartbles valorisés pour leur toison bouclée.'),
(N'La Ferme du Haya', N'Liège', N'Ferme familiale intégrant des moutons de race pure pour l''entretien des terres agricoles et la récolte lainière.');


INSERT INTO dbo.pelote (nom_commercial, race_mouton, description, conseil_lavage, quantite_stock, prix_unitaire, eleveur_id)
VALUES 
(N'Brune d''Ardenne', N'Ardennais Roux', N'Teinte rousse naturelle, fil rustique d''une robustesse incomparable.', N'Lavage main, 30°C max, séchage à plat', 25, 6.80, 1),
(N'Douceur de Sambre', N'Laitier Belge', N'Laine blanche d''une extrême douceur, filage fin idéal pour layette.', N'Programme laine délicat, 20°C', 40, 5.90, 2),
(N'Éclat de Fagne', N'Mouton des Fagnes', N'Laine écrue naturelle légèrement crépue, offrant un gonflant exceptionnel idéal pour les pulls chauds.', N'Lavage à la main à l''eau tiède, essorage doux sans tordre', 15, 7.20, 3),
(N'Velours d''Entre-Sambre-et-Meuse', N'Mouton d''Entre-Sambre-et-Meuse', N'Toison fine et dense de couleur blanc cassé, fil élastique très agréable à tricoter.', N'Lavage main, 30°C max, ne pas utiliser d''adoucissant', 30, 8.50, 4),
(N'Ombre d''Ardenne', N'Zwartbles', N'Fil d''un brun chocolat profond et lumineux, très chaud et résistant, parfait pour les vestes.', N'Lavage à la main recommandé, séchage à plat à l''abri du soleil', 50, 6.90, 5);


INSERT INTO dbo.transformation (type_action, nom_intervenant, ordre_etape, pelote_id)
VALUES 

(N'Tonte', N'Arnaud Chausteur (Tondeur itinérant wallon)', 1, 1),
(N'Lavage', N'Entreprise Fettweis (Verviers — Eau de la Vesdre)', 2, 1),
(N'Cardage', N'Atelier d''Entre mes laines (Nappes cardées)', 3, 1),
(N'Filage', N'La Filature du Hibou (Micro-filature locale)', 4, 1),
(N'Teinture Végétale', N'Teinturerie du Cygne (Teintures nature aux plantes)', 5, 1),

(N'Tonte', N'Sébastien Ferailles (Tonte douce)', 1, 2),
(N'Lavage', N'Lavoir de la Vesdre (Verviers)', 2, 2),
(N'Cardage', N'BlackWool (Atelier-boutique urbain géré par une artisane lainière.)', 3, 2),
(N'Filage', N'Filature Terrade (Micro-filature locale)', 4, 2),
(N'Teinture Végétale', N'By night (Teintures nature aux plantes)', 5, 2),

(N'Tonte', N'Arnaud Chausteur (Tondeur itinérant wallon)', 1, 3),
(N'Lavage', N'Lavoir de la Vesdre (Verviers)', 2, 3),
(N'Cardage', N'Tumulus Fiber Mill (Micro-filature professionnelle spécialisée dans le traitement de petits lots de fibres)', 3, 3),
(N'Filage', N'Filature Terrade (Micro-filature locale)', 4, 3),
(N'Teinture Végétale', N'By night (Teintures nature aux plantes)', 5, 3),

(N'Tonte', N'L''Artisan Tondeur du Hainaut (Spécialiste de la tonte de préservation pour les petits élevages de races anciennes.)', 1, 4),
(N'Lavage', N'Lavage Artisanal de la Hoëgne (Atelier écologique utilisant l''eau de source locale sans produits chimiques agressifs)', 2, 4),
(N'Cardage', N'Le Texlab (Laboratoire textile partagé)', 3, 4),
(N'Filage', N'Filature de la Lesse (Atelier écoresponsable filant la laine locale selon des méthodes traditionnelles)', 4, 4),
(N'Teinture Végétale', N'L''Atelier des Couleurs Végétales (Artisane teinturière utilisant exclusivement des plantes tinctoriales locales.)', 5, 4),

(N'Tonte', N'Tonte Mobile Ardennaise (Service de tonte itinérant axé sur le bien-être animal et le tri de la toison.)', 1, 5),
(N'Lavage', N'Traitex SA (Centre industriel historique de Verviers spécialisé dans le lavage et le dégraissage de la laine.)', 2, 5),
(N'Cardage', N'Le Goupil File (Atelier itinérant)', 3, 5),
(N'Filage', N'La Filature du Hibou (Micro-filature locale)', 4, 5),
(N'Teinture Végétale', N'Teintures du Viroin (Atelier spécialisé dans les bains de teinture naturelle à base de racines, écorces et fleurs.)', 5, 5);


