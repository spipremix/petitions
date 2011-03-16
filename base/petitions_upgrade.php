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

			// forcer le passage par upgrade !
			ecrire_meta($nom_meta_base_version,$current_version=0.0);
		}

		// maj des petitions
		if (spip_version_compare($current_version, '1.1.0','<')) {
			sql_alter("TABLE spip_petitions DROP PRIMARY KEY");
			ecrire_meta($nom_meta_base_version,$current_version = "1.1.0");
		}
		if (spip_version_compare($current_version, '1.1.1','<')) {
			sql_alter("TABLE spip_petitions ADD UNIQUE id_article (id_article)");
			ecrire_meta($nom_meta_base_version,$current_version = "1.1.1");
		}
		if (spip_version_compare($current_version, '1.1.2','<')) {
			sql_alter("TABLE spip_petitions ADD id_petition BIGINT(21) NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST");
			sql_alter("TABLE spip_petitions ADD PRIMARY KEY (id_petition)"); // securite
			ecrire_meta($nom_meta_base_version,$current_version = "1.1.2");
		}
		if (spip_version_compare($current_version, '1.1.3','<')) {
			sql_alter("TABLE spip_petitions ADD statut VARCHAR (10) DEFAULT 'publie' NOT NULL");
			ecrire_meta($nom_meta_base_version,$current_version = "1.1.3");
		}
		if (spip_version_compare($current_version, '1.1.4','<')) {
			sql_alter("TABLE spip_signatures ADD id_petition bigint(21) DEFAULT '0' NOT NULL");
			sql_alter("TABLE spip_signatures ADD INDEX id_petition (id_petition)");
			// marquer toutes les signatures a upgrader
			sql_updateq('spip_signatures',array('id_petition'=>-1));
			ecrire_meta($nom_meta_base_version,$current_version = "1.1.4");
		}
		if (spip_version_compare($current_version, '1.1.5','<')) {

			while ($rows = sql_allfetsel('DISTINCT id_article','spip_signatures','id_petition=-1','','','0,100')) {
				$rows = array_map('reset',$rows);
				foreach($rows as $id_article){
					$id_petition = sql_getfetsel('id_petition','spip_petitions','id_article='.intval($id_article));
					if (!$id_petition){
						include_spip('action/editer_petition');
						$id_petition = insert_petition($id_article);
						sql_updateq('spip_petitions',array('statut'=>'poubelle'),'id_petition='.$id_petition);
					}
					sql_updateq('spip_signatures',array('id_petition'=>$id_petition),'id_article='.$id_article);
				}
			}
			ecrire_meta($nom_meta_base_version,$current_version = "1.1.5");
		}
		if (spip_version_compare($current_version, '1.1.6','<')) {
			sql_alter("TABLE spip_signatures DROP INDEX id_article");
			sql_alter("TABLE spip_signatures DROP id_article");
			ecrire_meta($nom_meta_base_version,$current_version = "1.1.6");
		}
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
