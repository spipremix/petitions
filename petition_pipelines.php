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
function petition_afficher_config_objet($flux){
	if (($type = $flux['args']['type'])=='article'){
		$id = $flux['args']['id'];
		$table = table_objet($type);
		$id_table_objet = id_table_objet($type);
		$flux['data'] .= recuperer_fond("prive/configurer/petitionner",array($id_table_objet=>$id));
	}
	return $flux;
}

?>
