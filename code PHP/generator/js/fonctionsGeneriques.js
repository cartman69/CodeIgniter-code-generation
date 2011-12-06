/**
 *
 * Fonction qui permet de r�cup�rer les contr�les d'un conteneur
 * (form, div, ...) avec son s�lecteur.
 *
 *
 */
function listerControles(sConteneur){
    var oConteneur = $(sConteneur);
    var tControles = oConteneur.find("input[type!='submit'][type!='button'], textarea, select");
    return tControles;
}

/**
 *
 * Fonction qui renvoie un tableau de valeurs � partir d'un tableau 
 * d'�l�ments DOM
 *
 */
function valeursJson(selectionJquery){
    // alert(selectionJquery.serialize());
    var tTableauValeurs = new Array;
    selectionJquery.each(function(indice, objet){
		nomControle = $(objet).attr('name');
		if(nomControle == undefined){
			nomControle = $(objet).attr('id');
		}
        // alert(objet);
        // alert("Type inconnu : "+$(objet).attr('type'));
        switch($(objet).attr('type')){
            case 'text':
            case 'hidden':
                tTableauValeurs[nomControle] = $(objet).val().replace(/'/g, "\\'");
                break;
            case 'checkbox' :
                tTableauValeurs[nomControle] = $(objet).is(':checked');
                break;
            case 'radio' :
                if($(objet).is(':checked')){
                    tTableauValeurs[nomControle] = $(objet).val();    
                }
                break;
            case undefined : //--- Pas un input
                tTableauValeurs[nomControle] = $(objet).val().replace(/\n/g, "<br />").replace(/'/g, "\\'");
                break;
            default:
                alert("Type inconnu : "+$(objet).attr('type'));
        }
    });
    //alert(tTableauValeurs.serialize());
    return tTableauValeurs;

}

/**
 *
 *
 * onction qui renvoie une chaine json � partir d'un tableau
 *
 */
function tableauToJson(tTableau){
    console.log(tTableau, 'tableau entr�e');
    var sRetourJson = '{';
    var sSeparateur = "";
    
    for(var indice in tTableau ){
        console.log(indice, "@@"+tTableau[indice]+"##");
        sRetourJson +=sSeparateur+"'"+indice+"':'"+tTableau[indice]+"'";
        sSeparateur=", ";
    }
    sRetourJson += '}';
    console.log(sRetourJson);
    return sRetourJson;
}

/**
 *
 * Chainage des trois fonctions ci dessus :
 * � partir d'un formulaire renvoie une chaine json.
 *
 */
function jsonFromFormulaire(idFormulaire){
    return eval('('+tableauToJson(valeursJson(listerControles(idFormulaire)))+')');
}

function dateHeureCourante(){
    var tJoursSemaine = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
	
    var tMois = ['Janvier', 'F�vrier', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'D?cembre'];
	
    dateDuJour = new Date();
	
    dateHeure = tJoursSemaine[dateDuJour.getDay()]+" ";
	
    jourMois = dateDuJour.getDate();
    if(jourMois==1)
        dateHeure += " premier";
    else
        dateHeure += jourMois;

    dateHeure += " "+tMois[dateDuJour.getMonth()];
	
    dateHeure += " "+dateDuJour.getFullYear();
	
    dateHeure += " il est ";
	
    nHeure = dateDuJour.getHours();
    nMinutes = dateDuJour.getMinutes();
    nSecondes = dateDuJour.getSeconds();
	
    if(nHeure<10) dateHeure += "0";
    dateHeure += nHeure+":"
	
    if(nMinutes<10) dateHeure += "0";
    dateHeure += nMinutes+":"

    if(nSecondes<10) dateHeure += "0";
    dateHeure += nSecondes+"."

    return(dateHeure);
}

function popup(div, source, fermeture){
    $(div).load(source,
        function(){
            div.dialog();
        }
        );
}
