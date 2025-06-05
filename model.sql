#------------------------------------------------------------
#        Script MySQL.
#------------------------------------------------------------

#------------------------------------------------------------
# Table: datacommune
#------------------------------------------------------------

DROP TABLE IF EXISTS datacommunes;
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
# Table: datafile
#------------------------------------------------------------

DROP TABLE IF EXISTS datafile;
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


#------------------------------------------------------------
# Table: Installateur
#------------------------------------------------------------

DROP TABLE IF EXISTS Installateur;
CREATE TABLE Installateur(
        id          Int NOT NULL AUTO_INCREMENT, # unique because prim. k.
        install_nom Varchar (255) NOT NULL
	,CONSTRAINT Installateur_PK PRIMARY KEY (id)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: Marque Ondulateur
#------------------------------------------------------------

DROP TABLE IF EXISTS Marque_Ondulateur;
CREATE TABLE Marque_Ondulateur(
        id  Int NOT NULL AUTO_INCREMENT, # unique because prim. k.
        nom Varchar (255) NOT NULL
	,CONSTRAINT Marque_Ondulateur_PK PRIMARY KEY (id)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: Marque Panneau
#------------------------------------------------------------

DROP TABLE IF EXISTS Marque_Panneau;
CREATE TABLE Marque_Panneau(
        id  Int NOT NULL AUTO_INCREMENT, # unique because prim. k.
        nom Varchar (255) NOT NULL
	,CONSTRAINT Marque_Panneau_PK PRIMARY KEY (id)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: Modele Panneau
#------------------------------------------------------------

DROP TABLE IF EXISTS Modele_Panneau;
CREATE TABLE Modele_Panneau(
        id  Int NOT NULL AUTO_INCREMENT, # unique because prim. k.
        nom Varchar (255) NOT NULL
	,CONSTRAINT Modele_Panneau_PK PRIMARY KEY (id)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: Panneau
#------------------------------------------------------------

DROP TABLE IF EXISTS Panneau;
CREATE TABLE Panneau(
        id                Int NOT NULL AUTO_INCREMENT, # unique because prim. k.
        id_Marque_Panneau Int NOT NULL ,
        id_Modele_Panneau Int NOT NULL
	,CONSTRAINT Panneau_PK PRIMARY KEY (id)

	,CONSTRAINT Panneau_Marque_Panneau_FK FOREIGN KEY (id_Marque_Panneau) REFERENCES Marque_Panneau(id)
	,CONSTRAINT Panneau_Modele_Panneau0_FK FOREIGN KEY (id_Modele_Panneau) REFERENCES Modele_Panneau(id)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: Modele Ondulateur
#------------------------------------------------------------

DROP TABLE IF EXISTS Modele_Ondulateur;
CREATE TABLE Modele_Ondulateur(
        id  Int NOT NULL AUTO_INCREMENT, # unique because prim. k.
        nom Varchar (255) NOT NULL
	,CONSTRAINT Modele_Ondulateur_PK PRIMARY KEY (id)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: Ondulateur
#------------------------------------------------------------

DROP TABLE IF EXISTS Ondulateur;
CREATE TABLE Ondulateur(
        id                   Int NOT NULL AUTO_INCREMENT, # unique because prim. k.
        id_Marque_Ondulateur Int NOT NULL ,
        id_Modele_Ondulateur Int NOT NULL
	,CONSTRAINT Ondulateur_PK PRIMARY KEY (id)

	,CONSTRAINT Ondulateur_Marque_Ondulateur_FK FOREIGN KEY (id_Marque_Ondulateur) REFERENCES Marque_Ondulateur(id)
	,CONSTRAINT Ondulateur_Modele_Ondulateur0_FK FOREIGN KEY (id_Modele_Ondulateur) REFERENCES Modele_Ondulateur(id)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: Pays
#------------------------------------------------------------

DROP TABLE IF EXISTS Pays;
CREATE TABLE Pays(
        id       Int NOT NULL AUTO_INCREMENT, # unique because prim. k.
        pays_nom Varchar (255) NOT NULL UNIQUE # unique because else lots of duplicates
	,CONSTRAINT Pays_PK PRIMARY KEY (id)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: Region
#------------------------------------------------------------

DROP TABLE IF EXISTS Region;
CREATE TABLE Region(
        id      Int NOT NULL AUTO_INCREMENT, # unique because prim. k.
        dep_reg Varchar (255) NOT NULL ,
        id_Pays Int NOT NULL
	,CONSTRAINT Region_PK PRIMARY KEY (id)

	,CONSTRAINT Region_Pays_FK FOREIGN KEY (id_Pays) REFERENCES Pays(id)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: Departement
#------------------------------------------------------------

DROP TABLE IF EXISTS Departement;
CREATE TABLE Departement(
        id        Int NOT NULL AUTO_INCREMENT, # unique because prim. k.
        dep_nom   Varchar (255) NOT NULL ,
        id_Region Int NOT NULL
	,CONSTRAINT Departement_PK PRIMARY KEY (id)

	,CONSTRAINT Departement_Region_FK FOREIGN KEY (id_Region) REFERENCES Region(id)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: Commune
#------------------------------------------------------------

DROP TABLE IF EXISTS Commune;
CREATE TABLE Commune(
        code_insee Int NOT NULL , # unique because prim. k., but no autogen because already in .csv file
        com_nom    Varchar (255) NOT NULL ,
        id         Int NOT NULL
	,CONSTRAINT Commune_PK PRIMARY KEY (code_insee)

	,CONSTRAINT Commune_Departement_FK FOREIGN KEY (id) REFERENCES Departement(id)
)ENGINE=InnoDB;


#------------------------------------------------------------
# Table: Installation
#------------------------------------------------------------

DROP TABLE IF EXISTS Installation;
CREATE TABLE Installation(
        id                Int NOT NULL AUTO_INCREMENT, # unique because prim. k.
        an_installation   Int ,
        nb_pann           Int ,
        nb_ond            Int ,
        mois_installation Int ,
        surface           Int ,
        puissance_crete   Int ,
        lat               Float ,
        lon               Float ,
        ori               Varchar (10) ,
        ori_opti          Varchar (10) ,
        pente             Int ,
        pente_opti        Int ,
        prod_pvgis        Int ,
        code_postal       Int ,
        id_Panneau        Int ,
        id_Ondulateur     Int ,
        id_Installateur   Int ,
        code_insee        Int
	,CONSTRAINT Installation_PK PRIMARY KEY (id)

	,CONSTRAINT Installation_Panneau_FK FOREIGN KEY (id_Panneau) REFERENCES Panneau(id)
	,CONSTRAINT Installation_Ondulateur0_FK FOREIGN KEY (id_Ondulateur) REFERENCES Ondulateur(id)
	,CONSTRAINT Installation_Installateur1_FK FOREIGN KEY (id_Installateur) REFERENCES Installateur(id)
	,CONSTRAINT Installation_Commune2_FK FOREIGN KEY (code_insee) REFERENCES Commune(code_insee)
)ENGINE=InnoDB;

