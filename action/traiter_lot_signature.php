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

function action_traiter_lot_signature_dist($arg=null) {

	if (is_null($arg)){
		$securiser_action = charger_fonction('securiser_action', 'inc');
		$arg = $securiser_action();
	}

	/**
	 * $arg contient l'action relancer/supprimer/valider
	 * les id sont dans un tableau non signe ids[]
	 */
	if (preg_match(",^(\w+)$,",$arg,$match)
	 AND in_array($statut=$match[1],array('relancer','supprimer','valider'))
	 AND autoriser('instituer','signature',0)
	 AND $id=_request('ids')
	 AND is_array($id)){

		$ids = array_map('intval',$id);
		$rows = sql_allfetsel("id_signature", "spip_signatures", sql_in('id_signature',$ids));
		if (!count($rows)) return;
		$rows = array_map('reset',$rows);
		
		if ($action = charger_fonction($arg."_signature",'action',true))
			foreach ($rows as $id_signature) {
				$action($id_signature);
			}
	}
}

?>
