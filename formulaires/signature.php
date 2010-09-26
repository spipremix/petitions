<?php

/***************************************************************************\
 *  SPIP, Systeme de publication pour l'internet                           *
 *                                                                         *
 *  Copyright (c) 2001-2010                                                *
 *  Arnaud Martin, Antoine Pitrou, Philippe Riviere, Emmanuel Saint-James  *
 *                                                                         *
 *  Ce programme est un logiciel libre distribue sous licence GNU/GPL.     *
 *  Pour plus de details voir le fichier COPYING.txt ou l'aide en ligne.   *
\***************************************************************************/

if (!defined("_ECRIRE_INC_VERSION")) return;

function formulaires_signature_charger_dist($id_article, $petition, $texte, $site_obli, $message) {
	$valeurs = array(
		'id_article' => $id_article,
		'session_nom' => sinon($GLOBALS['visiteur_session']['session_nom'],
			$GLOBALS['visiteur_session']['nom']),
		'session_email'=> sinon($GLOBALS['visiteur_session']['session_email'],
			$GLOBALS['visiteur_session']['email']),
		'signature_nom_site'=>'',
		'signature_url_site'=>'http://',
		'_texte'=>$petition,
		'_message'=>$message,
		'message'=>'',
		'site_obli' => $site_obli,
		'debut_signatures'=>'' // pour le nettoyer de l'url d'action !
		);

	if ($c = _request('var_confirm')) {
		$valeurs['_confirm'] = $c;
		$valeurs['editable'] = false;
	}
	return $valeurs;
}
function affiche_reponse_confirmation($confirm) {
	$confirmer_signature = charger_fonction('confirmer_signature','action');
	return $confirmer_signature($confirm);  # calculee plus tot: cf petitions_options
}

function formulaires_signature_verifier_dist($id_article, $petition, $texte, $site_obli, $message) {
	$erreurs = array();
	$oblis = array('session_email','session_email');

	if ($site_obli){
		$oblis[] = 'signature_nom_site';
		$oblis[] = 'signature_url_site';
		set_request('signature_url_site', vider_url(_request('signature_url_site')));
	}
	foreach ($oblis as $obli)
		if (!_request($obli))
			$erreurs[$obli] = _T('info_obligatoire');
	
	if ($nom = _request('session_nom') AND strlen($nom) < 2)
		$erreurs['nom_email'] =  _T('form_indiquer_nom');

	include_spip('inc/filtres');
	if (($mail=_request('session_email')) == _T('info_mail_fournisseur'))
		$erreurs['adresse_email'] = _T('form_indiquer');
	elseif ($mail AND !email_valide($mail)) 
		$erreurs['adresse_email'] = _T('form_email_non_valide');
	elseif (strlen(_request('nobot'))
		OR (@preg_match_all(',\bhref=[\'"]?http,i', // bug PHP
				    $message 
				    # ,  PREG_PATTERN_ORDER
				   )
		    >2)) {
		#$envoyer_mail = charger_fonction('envoyer_mail','inc');
		#envoyer_mail('email_moderateur@example.tld', 'spam intercepte', var_export($_POST,1));
		$erreurs['message_erreur'] = _T('form_pet_probleme_liens');
	}
	if ($site_obli){
		if (!vider_url($url_site = _request('signature_url_site'))) {
			$erreurs['signature_url_site'] = _T('form_indiquer_nom_site');
		}
		elseif (!count($erreurs)) {
			include_spip('inc/distant');
			if (!recuperer_page($url_site, false, true, 0))
				$erreurs['signature_url_site'] = _T('form_pet_url_invalide');
		}
	}
	
	if (!count($erreurs)){
		// tout le monde est la.
		include_spip('base/abstract_sql');
		$row = sql_fetsel('*', 'spip_petitions', "id_article=".intval($id_article));

		if (!$row) 
			$erreurs['message_erreur'] = _T('form_pet_probleme_technique');
		else {
			$email_unique = $row['email_unique']  == "oui";
			$site_unique = $row['site_unique']  == "oui";
		
			// Refuser si deja signe par le mail ou le site quand demande
			// Il y a un acces concurrent potentiel,
			// mais ca n'est qu'un cas particulier de qq n'ayant jamais confirme'.
			// On traite donc le probleme a la confirmation.
		
			if ($email_unique) {
				$r = sql_countsel('spip_signatures', "id_article=$id_article AND ad_email=" . sql_quote($mail) . " AND statut='publie'");
				if ($r)	$erreurs['message_erreur'] =  _T('form_pet_deja_signe');
			}
		
			if ($site_unique) {
				$r = sql_countsel('spip_signatures', "id_article=$id_article AND url_site=" . sql_quote($url_site) . " AND (statut='publie' OR statut='poubelle')");
				if ($r)	$erreurs['message_erreur'] = _T('form_pet_site_deja_enregistre');
			}
		}
	}

	return $erreurs;
}

function formulaires_signature_traiter_dist($id_article, $petition, $texte, $site_obli, $message) {
	$reponse = _T('form_pet_probleme_technique');
	include_spip('base/abstract_sql');
	if (spip_connect()) {
		$controler_signature = charger_fonction('controler_signature', 'inc');
		$reponse = $controler_signature($id_article,
		_request('session_nom'), _request('session_email'),
		_request('message'), _request('signature_nom_site'),
		_request('signature_url_site'), _request('url_page'));
	}

	return array('message_ok'=>$reponse);
}

//
// Recevabilite de la signature d'une petition
// les controles devraient mantenant etre faits dans formulaires_signature_verifier()
// 

// http://doc.spip.org/@inc_controler_signature_dist
function inc_controler_signature_dist($id_article, $nom, $mail, $message, $site, $url_site, $url_page) {

	include_spip('inc/texte');
	include_spip('inc/filtres');

	// tout le monde est la.
	// cela a ete verifie en amont, dans formulaires_signature_verifier()
	if (!$row = sql_fetsel('*', 'spip_petitions', "id_article=$id_article"))
		return _T('form_pet_probleme_technique');

	$statut = "";
	if (!$ret = signature_a_confirmer($id_article, $url_page, $nom, $mail, $site, $url_site, $message, $lang, $statut))
		return _T('form_pet_probleme_technique');

	include_spip('action/editer_signature');

	$id_signature = insert_signature($id_article);
	if (!$id_signature) return _T('form_pet_probleme_technique');

	signature_set($id_signature,
		array(
		'statut' => $statut,
		'nom_email' => $nom,
		'ad_email' => $mail,
		'message' => $message,
		'nom_site' => $site,
		'url_site' => $url_site
		)
	);

	return $ret;
}

// http://doc.spip.org/@signature_a_confirmer
function signature_a_confirmer($id_article, $url_page, $nom, $mail, $site, $url, $msg, $lang, &$statut)
{

	// Si on est deja connecte et que notre mail a ete valide d'une maniere
	// ou d'une autre, on entre directement la signature dans la base, sans
	// envoyer d'email. Sinon email de verification
	if (
		// Cas 1: on est loge et on signe avec son vrai email
		(
		isset($GLOBALS['visiteur_session']['statut'])
		AND $GLOBALS['visiteur_session']['session_email'] == $GLOBALS['visiteur_session']['email']
		AND strlen($GLOBALS['visiteur_session']['email'])
		)

		// Cas 2: on a deja signe une petition, et on conserve le meme email
		OR (
		isset($GLOBALS['visiteur_session']['email_confirme'])
		AND $GLOBALS['visiteur_session']['session_email'] == $GLOBALS['visiteur_session']['email_confirme']
		AND strlen($GLOBALS['visiteur_session']['session_email'])
		)
	) {
		// Si on est en ajax on demande a reposter sans ajax, car il faut
		// recharger toute la page pour afficher la signature
		refuser_traiter_formulaire_ajax();

		$statut = 'publie';
		// invalider le cache !
		include_spip('inc/invalideur');
		suivre_invalideur("id='article/$id_article'");

		// message de reussite
		return
			_T('form_pet_signature_validee');
	}


	//
	// Cas normal : envoi d'une demande de confirmation
	//
	$row = sql_fetsel('titre,lang', 'spip_articles', "id_article=$id_article");
	$lang = lang_select($row['lang']);
	$titre = textebrut(typo($row['titre']));
	if ($lang) lang_select();

	if (!strlen($statut))
		$statut = signature_test_pass();

	if ($lang != $GLOBALS['meta']['langue_site'])
		  $url_page = parametre_url($url_page, "lang", $lang,'&');

	$url_page = parametre_url($url_page, 'var_confirm', $statut, '&')
	. "#sp$id_article";

	$r = _T('form_pet_mail_confirmation',
		 array('titre' => $titre,
		       'nom_email' => $nom,
		       'nom_site' => $site,
		       'url_site' => $url, 
		       'url' => $url_page,
		       'message' => $msg));

	$titre = _T('form_pet_confirmation')." ". $titre;
	$envoyer_mail = charger_fonction('envoyer_mail','inc');
	if ($envoyer_mail($mail,$titre, $r))
		return _T('form_pet_envoi_mail_confirmation',array('email'=>$mail));

	return false; # erreur d'envoi de l'email
}

// Creer un mot de passe aleatoire et verifier qu'il est unique
// dans la table des signatures
// http://doc.spip.org/@signature_test_pass
function signature_test_pass() {
	include_spip('inc/acces');
	do {
		$passw = creer_pass_aleatoire();
	} while (sql_countsel('spip_signatures', "statut='$passw'") > 0);

	return $passw;
}

?>
