SELECT i.id, i.nb_pann, i.nb_ond, CONCAT(LPAD(i.mois_installation, 2, '0'), '/', i.an_installation) AS date_installation, i.surface, i.puissance_crete, i.lat, i.lon, i.ori, i.ori_opti, i.pente, i.pente_opti, i.prod_pvgis, i.code_postal, c.com_nom, d.dep_nom, r.dep_reg, p.pays_nom, mqo.nom, mdo.nom, mqpn.nom, mdpn.nom, inst.install_nom
    FROM Installation i
    LEFT JOIN Commune c ON i.id_Commune = c.id
    LEFT JOIN Departement d ON c.id_Departement = d.id
    LEFT JOIN Region r ON d.id_Region = r.id
    LEFT JOIN Pays p ON r.id_Pays = p.id
    LEFT JOIN Onduleur o ON i.id_Onduleur = o.id
    LEFT JOIN Marque_Onduleur mqo ON o.id_Marque_Onduleur = mqo.id
    LEFT JOIN Modele_Onduleur mdo ON o.id_Modele_Onduleur = mdo.id
    LEFT JOIN Panneau pn ON i.id_Panneau = pn.id
    LEFT JOIN Marque_Panneau mqpn ON pn.id_Marque_Panneau = mqpn.id
    LEFT JOIN Modele_Panneau mdpn ON pn.id_Modele_Panneau = mdpn.id
    LEFT JOIN Installateur inst ON i.id_Installateur = inst.id
    
    WHERE i.id = 706447;