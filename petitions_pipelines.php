<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2009                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

 
/**
 * Boite de configuration des objets articles
 *
 * @param array $flux
 * @return array
 */
function petitions_afficher_config_objet($flux){
	if (($type = $flux['args']['type'])=='article'){
		$id = $flux['args']['id'];
		$table = table_objet($type);
		$id_table_objet = id_table_objet($type);
		$flux['data'] .= recuperer_fond("prive/configurer/petitionner",array($id_table_objet=>$id));
	}
	return $flux;
}

/**
 * Liste et ponderation des champs pour la recherche
 * 
 * @param array $tables
 * @return int
 */
function petitions_rechercher_liste_des_champs($tables){
	$tables['signature'] = array(
				'nom_email' => 2, 'ad_email' => 4,
				'nom_site' => 2, 'url_site' => 4,
				'message' => 1
			);
	
	return $tables;
}

# cette requete devrait figurer dans l'optimisation
#sql_delete("spip_signatures", "NOT (statut='publie' OR statut='poubelle') AND NOT(" . sql_date_proche('date_time', -10, ' DAY') . ')');

?>
