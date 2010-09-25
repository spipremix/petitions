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

if (!defined("_ECRIRE_INC_VERSION")) return;

/**
 * Charger
 *
 * @param int $id_article
 * @return array
 */
function formulaires_configurer_petition_article_charger_dist($id_article){
	
	$valeurs = array();
	
	$valeurs['editable'] = true;
	
	if (!autoriser('modererpetition', 'article', $id_article))
		$valeurs['editable'] = false;

	include_spip('inc/presentation');
	include_spip('base/abstract_sql');
	$nb_signatures = sql_countsel("spip_signatures", "id_article=$id_article");
	$petition = sql_fetsel("*", "spip_petitions", "id_article=$id_article");
	
	$valeurs['id_article'] = $id_article;
	$valeurs['petition'] = $petition;
	$valeurs['_controle_petition'] = $nb_signatures?singulier_ou_pluriel($nb_signatures,'petitions:une_signature','petitions:nombre_signatures'):"";
	
	return $valeurs;
	
}

/**
 * Traiter
 *
 * @param int $id_article
 * @return array
 */
function formulaires_configurer_petition_article_traiter_dist($id_article){
	
	include_spip('inc/autoriser');
	
	if (autoriser('modererpetition', 'article', $id_article)){
		switch(_request('change_petition')) {
		case 'on':
			$email_unique = (_request('email_unique') == 'on') ? 'oui' : 'non';
			$site_obli = (_request('site_obli') == 'on') ? 'oui' : 'non';
			$site_unique = (_request('site_unique') == 'on') ? 'oui' : 'non';
			$message =  (_request('message') == 'on') ? 'oui' : 'non';

			include_spip('base/auxiliaires');
			sql_replace('spip_petitions',
						  array('id_article' => $id_article,
							'email_unique' => $email_unique,
							'site_obli' => $site_obli,
							'site_unique' => $site_unique,
							'message' => $message),
						  $GLOBALS['tables_auxiliaires']['spip_petitions']);
			include_spip('inc/modifier');
			revision_petition($id_article,
				array('texte' => _request('texte_petition'))
			);
			break;
		case 'off':
			sql_delete("spip_petitions", "id_article=$id_article");
			break;
		}
	}
		
	return array('message_ok'=>_T('config_info_enregistree'));
	
}


// http://doc.spip.org/@revision_petition
function revision_petition($id_article, $c=false) {

	modifier_contenu('petition', $id_article,
		array(),
		$c);
}

?>
