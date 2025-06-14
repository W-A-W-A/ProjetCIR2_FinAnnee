DROP DATABASE IF EXISTS solaire;
CREATE DATABASE solaire;
USE solaire;
#------------------------------------------------------------
#        Script MySQL.
#------------------------------------------------------------
CREATE TABLE datafile(
        id INT (32),
        iddoc INT,
        mois_installation INT (4),
        an_installation INT (12),
        nb_panneaux INT (32),
        panneaux_marque VARCHAR (255),
        panneaux_modele VARCHAR (255),
        nb_onduleur INT,
        onduleur_marque VARCHAR (255),
        onduleur_modele VARCHAR (255),
        puissance_crete INT,
        surface INT,
        pente INT,
        pente_optimum INT,
        orientation VARCHAR (10),
        orientation_optimum VARCHAR (10),
        installateur VARCHAR (255),
        production_pvgis INT,
        lat FLOAT,
        lon FLOAT,
        country VARCHAR (255),
        postal_code INT,
        postal_code_suffix INT,
        postal_town VARCHAR (255),
        locality VARCHAR (255),
        administrative_area_level_1 VARCHAR (255),
        administrative_area_level_2 VARCHAR (255),
        administrative_area_level_3 VARCHAR (255),
        administrative_area_level_4 VARCHAR (255),
        political VARCHAR (255)
);
CREATE TABLE datacommunes(
        code_insee INT,
        nom_standard VARCHAR (255),
        reg_code INT,
        reg_nom VARCHAR (255),
        dep_code int,
        dep_nom VARCHAR (255),
        code_postal INT,
        population INT
);
#------------------------------------------------------------
# Table: Installateur
#------------------------------------------------------------
CREATE TABLE Installateur(
        id Int NOT NULL AUTO_INCREMENT,
        install_nom Varchar (255) NOT NULL,
        CONSTRAINT Installateur_PK PRIMARY KEY (id)
) ENGINE = InnoDB;
#------------------------------------------------------------
# Table: Marque Onduleur
#------------------------------------------------------------
CREATE TABLE Marque_Onduleur(
        id Int NOT NULL AUTO_INCREMENT,
        nom Varchar (255) NOT NULL,
        CONSTRAINT Marque_Onduleur_PK PRIMARY KEY (id)
) ENGINE = InnoDB;
#------------------------------------------------------------
# Table: Marque Panneau
#------------------------------------------------------------
CREATE TABLE Marque_Panneau(
        id Int NOT NULL AUTO_INCREMENT,
        nom Varchar (255) NOT NULL,
        CONSTRAINT Marque_Panneau_PK PRIMARY KEY (id)
) ENGINE = InnoDB;
#------------------------------------------------------------
# Table: Modele Panneau
#------------------------------------------------------------
CREATE TABLE Modele_Panneau(
        id Int NOT NULL AUTO_INCREMENT,
        nom Varchar (255) NOT NULL,
        CONSTRAINT Modele_Panneau_PK PRIMARY KEY (id)
) ENGINE = InnoDB;
#------------------------------------------------------------
# Table: Panneau
#------------------------------------------------------------
CREATE TABLE Panneau(
        id Int NOT NULL AUTO_INCREMENT,
        id_Marque_Panneau Int NOT NULL,
        id_Modele_Panneau Int NOT NULL,
        CONSTRAINT Panneau_PK PRIMARY KEY (id),
        CONSTRAINT Panneau_Marque_Panneau_FK FOREIGN KEY (id_Marque_Panneau) REFERENCES Marque_Panneau(id),
        CONSTRAINT Panneau_Modele_Panneau0_FK FOREIGN KEY (id_Modele_Panneau) REFERENCES Modele_Panneau(id)
) ENGINE = InnoDB;
#------------------------------------------------------------
# Table: Modele Onduleur
#------------------------------------------------------------
CREATE TABLE Modele_Onduleur(
        id Int NOT NULL AUTO_INCREMENT,
        nom Varchar (255) NOT NULL,
        CONSTRAINT Modele_Onduleur_PK PRIMARY KEY (id)
) ENGINE = InnoDB;
#------------------------------------------------------------
# Table: Onduleur
#------------------------------------------------------------
CREATE TABLE Onduleur(
        id Int NOT NULL AUTO_INCREMENT,
        id_Marque_Onduleur Int NOT NULL,
        id_Modele_Onduleur Int NOT NULL,
        CONSTRAINT Onduleur_PK PRIMARY KEY (id),
        CONSTRAINT Onduleur_Marque_Onduleur_FK FOREIGN KEY (id_Marque_Onduleur) REFERENCES Marque_Onduleur(id),
        CONSTRAINT Onduleur_Modele_Onduleur0_FK FOREIGN KEY (id_Modele_Onduleur) REFERENCES Modele_Onduleur(id)
) ENGINE = InnoDB;
#------------------------------------------------------------
# Table: Pays
#------------------------------------------------------------
CREATE TABLE Pays(
        id Int NOT NULL AUTO_INCREMENT,
        pays_nom Varchar (255) NOT NULL,
        CONSTRAINT Pays_PK PRIMARY KEY (id)
) ENGINE = InnoDB;
#------------------------------------------------------------
# Table: Region
#------------------------------------------------------------
CREATE TABLE Region(
        id Int NOT NULL AUTO_INCREMENT,
        dep_reg Varchar (255) NOT NULL,
        id_Pays Int NOT NULL,
        CONSTRAINT Region_PK PRIMARY KEY (id),
        CONSTRAINT Region_Pays_FK FOREIGN KEY (id_Pays) REFERENCES Pays(id)
) ENGINE = InnoDB;
#------------------------------------------------------------
# Table: Departement
#------------------------------------------------------------
CREATE TABLE Departement(
        id Int NOT NULL AUTO_INCREMENT,
        dep_nom Varchar (255) NOT NULL,
        id_Region Int NOT NULL,
        CONSTRAINT Departement_PK PRIMARY KEY (id),
        CONSTRAINT Departement_Region_FK FOREIGN KEY (id_Region) REFERENCES Region(id)
) ENGINE = InnoDB;
#------------------------------------------------------------
# Table: Commune
#------------------------------------------------------------
CREATE TABLE Commune(
        id Int NOT NULL AUTO_INCREMENT,
        code_insee Int,
        com_nom Varchar (255) NOT NULL,
        id_Departement Int NOT NULL,
        CONSTRAINT Commune_PK PRIMARY KEY (id),
        CONSTRAINT Commune_Departement_FK FOREIGN KEY (id_Departement) REFERENCES Departement(id)
) ENGINE = InnoDB;
#------------------------------------------------------------
# Table: Installation
#------------------------------------------------------------
CREATE TABLE Installation(
        id Int NOT NULL AUTO_INCREMENT,
        an_installation Int,
        nb_pann Int,
        nb_ond Int,
        mois_installation Int,
        surface Int,
        puissance_crete Int,
        lat Float,
        lon Float,
        ori Varchar (10),
        ori_opti Varchar (10),
        pente Int,
        pente_opti Int,
        prod_pvgis Int,
        code_postal Int,
        id_Panneau Int,
        id_Onduleur Int,
        id_Installateur Int,
        id_Commune Int,
        CONSTRAINT Installation_PK PRIMARY KEY (id),
        CONSTRAINT Installation_Panneau_FK FOREIGN KEY (id_Panneau) REFERENCES Panneau(id),
        CONSTRAINT Installation_Onduleur0_FK FOREIGN KEY (id_Onduleur) REFERENCES Onduleur(id),
        CONSTRAINT Installation_Installateur1_FK FOREIGN KEY (id_Installateur) REFERENCES Installateur(id),
        CONSTRAINT Installation_Commune2_FK FOREIGN KEY (id_Commune) REFERENCES Commune(id)
) ENGINE = InnoDB;