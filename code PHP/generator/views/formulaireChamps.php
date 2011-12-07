<form name='formulairePourFormulaire' id="formulairePourFormulaire" method="post" target="blanc" action="<?php echo site_url('/generercode/generercode') ?>" >
    <table border="1">
        <thead>
            <tr>
                <th>Champ</th>
                <th>Type du champ</th>
                <th>Longueur du champ</th>
				<th><input type="checkbox" onclick="javascript:toggleDansFormulaire($(this).is(':checked'))">Dans le formulaire</th>
				<th>Libellé du formulaire</th>
                <th>Nom de l'input</th>
                <th>Type de l'input</th>
                <th>Longueur max de l'input</th>
                <th><input type="checkbox" onclick="javascript:toggleObligatoire($(this).is(':checked'))">Obligatoire</th>
            </tr>
        </thead>
        <tbody>
            <?php $ci = get_instance(); foreach ($listeChamps as $indice => $champ): ?>
                <tr>
                    <td><?php echo $champ->name; ?><input type="hidden" value="<?php echo $champ->name; ?>"  name="donnees[<?php echo $indice; ?>][nom_champ]" />
						<?php if($champ->primary_key==1){ echo ' (clé primaire)'; $sClePrimaire_l = $champ->name;} ?>
					</td>
                    <td><?php echo $champ->type; ?>
                    <input type="hidden" value="<?php echo $champ->type; ?>"  name="donnees[<?php echo $indice; ?>][type_donnee]" />
                    </td>
                    <td><?php echo $champ->max_length; ?></td>
					<td><input type="checkbox" name="donnees[<?php echo $indice; ?>][generer]" /></td>
					<td><input type="text" name="donnees[<?php echo $indice; ?>][label_input]" value="input_<?php echo $champ->name; ?>" /></td>
                    <td><input type="text" value="input_<?php echo $champ->name; ?>" name="donnees[<?php echo $indice; ?>][nom_input]" /></td>
                    <td><?php echo $ci->listeTypesChamps("donnees[".$indice."][type_input]"); ?></td>
					<td><input type="text" value="<?php echo $champ->max_length; ?>" name="donnees[<?php echo $indice; ?>][longueur_max_champ]" /></td>
                                        <td><input type="checkbox" name="donnees[<?php echo $indice; ?>][obligatoire]" /></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <input type="hidden" id="clePrimaire" name="clePrimaire" value="<?php echo $sClePrimaire_l ?>" />
	Nom du contrôleur : <input type="text" name="controller" id="controller">
	Nom du modèle : <input type="text" name="model" id="model">
	Nom de la vue : <input type="text" name="view" id="view">
    <input type="button" value="Générer!!!" onclick="envoyerDonneesFormulaire()" />
</form>