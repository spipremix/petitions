<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2011                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

/**
 * Installation/maj des tables petitions et signatures
 *
 * @param string $nom_meta_base_version
 * @param string $version_cible
 */
function petitions_upgrade($nom_meta_base_version,$version_cible){
	$current_version = 0.0;
	if (   (!isset($GLOBALS['meta'][$nom_meta_base_version]) )
			|| (($current_version = $GLOBALS['meta'][$nom_meta_base_version])!=$version_cible)){

		if ($current_version==0.0){
			include_spip('base/create');
			// creer les tables
			creer_base();
			// mettre les metas par defaut
			$config = charger_fonction('config','inc');
			$config();
			ecrire_meta($nom_meta_base_version,$current_version=$version_cible);
		}
		/*
		# ajout du champ statut aux petitions
		if (version_compare($current_version, '1.1','<')) {
			sql_alter("TABLE `spip_petitions` ADD `statut` VARCHAR (25) DEFAULT 'publie' NOT NULL AFTER `texte`");
			ecrire_meta($nom_meta_base_version,$current_version = $version_cible);
		}
		*/
	}
}

/**
 * Desinstallation/suppression des tables petitions et signatures
 *
 * @param string $nom_meta_base_version
 */
function petitions_vider_tables($nom_meta_base_version) {
	sql_drop_table("spip_petitions");
	sql_drop_table("spip_signatures");

	effacer_meta($nom_meta_base_version);
}

?>
