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
 * Interfaces des tables petitions et signatures pour le compilateur
 *
 * @param array $interfaces
 * @return array
 */
function petitions_declarer_tables_interfaces($interfaces){

	$interfaces['table_des_tables']['petitions']='petitions';
	$interfaces['table_des_tables']['signatures']='signatures';
	
	$interfaces['exceptions_des_tables']['signatures']['date']='date_time';
	$interfaces['exceptions_des_tables']['signatures']['nom']='nom_email';
	$interfaces['exceptions_des_tables']['signatures']['email']='ad_email';
	
	$interfaces['table_date']['signatures']='date_time';

	$interfaces['table_statut']['spip_signatures'][] = array('champ'=>'statut','publie'=>'publie','previsu'=>'publie','exception'=>array('statut','tout'));

	$interfaces['tables_jointures']['spip_articles'][]= 'petitions';
	$interfaces['tables_jointures']['spip_articles'][]= 'signatures';

	$interfaces['exceptions_des_jointures']['petition'] = array('spip_petitions', 'texte');
	$interfaces['exceptions_des_jointures']['id_signature']= array('spip_signatures', 'id_signature');

	// Signatures : passage des donnees telles quelles, sans traitement typo
	// la securite et conformite XHTML de ces champs est assuree par safehtml()
	foreach(array('NOM_EMAIL','AD_EMAIL','NOM_SITE','URL_SITE','MESSAGE') as $balise)
		if (!isset($interfaces['table_des_traitements'][$balise]['signatures']))
			$interfaces['table_des_traitements'][$balise]['signatures'] = 'safehtml(%s)';
		else
			if (strpos($interfaces['table_des_traitements'][$balise]['signatures'],'safehtml')==false)
				$interfaces['table_des_traitements'][$balise]['signatures'] = 'safehtml('.$interfaces['table_des_traitements'][$balise]['signatures'].')';

	return $interfaces;
}

/**
 * Table principale spip_signatures
 *
 * @param array $tables_principales
 * @return array
 */
function petitions_declarer_tables_principales($tables_principales){

	$spip_signatures = array(
			"id_signature"	=> "bigint(21) NOT NULL",
			"id_article"	=> "bigint(21) DEFAULT '0' NOT NULL",
			"date_time"	=> "datetime DEFAULT '0000-00-00 00:00:00' NOT NULL",
			"nom_email"	=> "text DEFAULT '' NOT NULL",
			"ad_email"	=> "text DEFAULT '' NOT NULL",
			"nom_site"	=> "text DEFAULT '' NOT NULL",
			"url_site"	=> "text DEFAULT '' NOT NULL",
			"message"	=> "mediumtext DEFAULT '' NOT NULL",
			"statut"	=> "varchar(10) DEFAULT '0' NOT NULL",
			"maj"	=> "TIMESTAMP");

	$spip_signatures_key = array(
			"PRIMARY KEY"	=> "id_signature",
			"KEY id_article"	=> "id_article",
			"KEY statut" => "statut");
	$spip_signatures_join = array(
			"id_signature"=>"id_signature",
			"id_article"=>"id_article");

	$tables_principales['spip_signatures'] =
		array('field' => &$spip_signatures,	'key' => &$spip_signatures_key, 'join' => &$spip_signatures_join);

	return $tables_principales;
}

/**
 * Table auxilaire spip_petitions
 *
 * @param array $tables_principales
 * @return array
 */
function petitions_declarer_tables_auxiliaires($tables_auxiliaires){

	$spip_petitions = array(
			"id_article"	=> "bigint(21) DEFAULT '0' NOT NULL",
			"email_unique"	=> "CHAR (3) DEFAULT '' NOT NULL",
			"site_obli"	=> "CHAR (3) DEFAULT '' NOT NULL",
			"site_unique"	=> "CHAR (3) DEFAULT '' NOT NULL",
			"message"	=> "CHAR (3) DEFAULT '' NOT NULL",
			"texte"	=> "LONGTEXT DEFAULT '' NOT NULL",
			"maj"	=> "TIMESTAMP");

	$spip_petitions_key = array(
			"PRIMARY KEY"	=> "id_article");


	$tables_auxiliaires['spip_petitions'] = array(
		'field' => &$spip_petitions,
		'key' => &$spip_petitions_key);

	return $tables_auxiliaires;
}



/**
 * Declarer le surnom des petitions
 *
 * @param array $table
 * @return array
 */
function petitions_declarer_tables_objets_surnoms($table){
	$table['petition'] = 'petitions';
	$table['signature'] = 'signatures';
	return $table;
}



/**
 * Alias de type pour les groupes de mot
 * @param array $table
 * @return string
 */
function petitions_declarer_type_surnoms($table) {
	$table['petition'] = 'petition'; // n'a pas de cle primaire valable pour s'y retrouver
	return $table;
}

?>
