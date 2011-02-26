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

if (!defined("_ECRIRE_INC_VERSION")) return;

function action_editer_petition_dist($arg=null) {

	if (is_null($arg)){
		$securiser_action = charger_fonction('securiser_action', 'inc');
		$arg = $securiser_action();
	}

	// si id_petition n'est pas un nombre, c'est une creation
	// mais on verifie qu'on a toutes les donnees qu'il faut.
	if (!$id_petition = intval($arg)) {
		$id_article = _request('id_article');
		if (!($id_article)) {
			include_spip('inc/headers');
			redirige_url_ecrire();
		}
		$id_petition = insert_petition($id_article);
	}

	// Enregistre l'envoi dans la BD
	if ($id_petition > 0)
		$err = petitions_set($id_petition);

	if (_request('redirect')) {
		$redirect = parametre_url(urldecode(_request('redirect')),
			'id_petition', $id_petition, '&') . $err;
	
		include_spip('inc/headers');
		redirige_par_entete($redirect);
	}
	else 
		return array($id_petition,$err);
}

/**
 * Mettre a jour une petition existante
 * 
 * @param int $id_petition
 * @param array $set
 * @return string
 */
function petition_set($id_petition, $set=null) {
	$err = '';

	$c = array();
	if($set){
		$c = $set;
		unset($c['id_article']);
	}
	else {
		foreach (array(
			"email_unique","site_obli",
			"site_unique","message","texte"
		) as $champ)
			$c[$champ] = _request($champ,$set);
	}

	include_spip('inc/modifier');
	revision_petition($id_petition, $c);

	// changement d'article ou de statut ?
	$c = array();
	foreach (array(
		'statut',
		'id_article'
	) as $champ)
		$c[$champ] = _request($champ,$set);
	$err .= instituer_petition($id_petition, $c);

	return $err;
}

/**
 * Inserer une petition en base
 * @param <type> $id_article
 * @return <type> 
 */
function insert_petition($id_article) {

	// Si id_article vaut 0 ou n'est pas definie, echouer
	if (!$id_article = intval($id_article))
		return 0;

	$champs = array(
		'id_article' => $id_article,
	);

	// Envoyer aux plugins
	$champs = pipeline('pre_insertion',
		array(
			'args' => array(
				'table' => 'spip_petitions',
			),
			'data' => $champs
		)
	);

	$id_petition = sql_insertq("spip_petitions", $champs);

	pipeline('post_insertion',
		array(
			'args' => array(
				'table' => 'spip_petitions',
				'id_objet' => $id_petition
			),
			'data' => $champs
		)
	);

	return $id_petition;
}


/**
 * $c est un array ('id_article' = changement d'article)
 * il n'est pas autoriser de deplacer une petition
 *
 * @param  $id_petition
 * @param  $c
 * @param bool $calcul_rub
 * @return string
 */
function instituer_petition($id_petition, $c) {

	include_spip('inc/autoriser');
	include_spip('inc/modifier');

	$row = sql_fetsel("id_article", "spip_petitions", "id_petition=".intval($id_petition));
	$statut_ancien = $statut = $row['statut'];
	#$date_ancienne = $date = $row['date_time'];
	$champs = array();

	$s = isset($c['statut'])?$c['statut']:$statut;

	// cf autorisations dans inc/instituer_petition
	if ($s != $statut /*OR ($d AND $d != $date)*/) {
		$statut = $champs['statut'] = $s;

		// En cas de publication, fixer la date a "maintenant"
		// sauf si $c commande autre chose
		// ou si l'petition est deja date dans le futur
		// En cas de proposition d'un petition (mais pas depublication), idem
		/*
		if ($champs['statut'] == 'publie') {
			if ($d)
				$champs['date_time'] = $date = $d;
			else
				$champs['date_time'] = $date = date('Y-m-d H:i:s');
		}*/
	}

	// Envoyer aux plugins
	$champs = pipeline('pre_edition',
		array(
			'args' => array(
				'table' => 'spip_petitions',
				'id_objet' => $id_petition,
				'action'=>'instituer',
				'statut_ancien' => $statut_ancien,
			),
			'data' => $champs
		)
	);

	if (!count($champs)) return;

	// Envoyer les modifs.
	sql_updateq('spip_petitions',$champs,'id_petition='.intval($id_petition));

	// Invalider les caches
	include_spip('inc/invalideur');
	suivre_invalideur("id='petition/$id_petition'");
	suivre_invalideur("id='article/".$row['id_article']."'");

	// Pipeline
	pipeline('post_edition',
		array(
			'args' => array(
				'table' => 'spip_petitions',
				'id_objet' => $id_petition,
				'action'=>'instituer',
				'statut_ancien' => $statut_ancien,
			),
			'data' => $champs
		)
	);

	// Notifications
	if ($notifications = charger_fonction('notifications', 'inc')) {
		$notifications('instituerpetition', $id_petition,
			array('statut' => $statut, 'statut_ancien' => $statut_ancien)
		);
	}

	return ''; // pas d'erreur
}

// http://doc.spip.org/@revision_petition
function revision_petition($id_petition, $c=false) {

	include_spip('inc/modifier');
	return modifier_contenu('petition', $id_petition,array(),$c);
}


?>