//--- Lancement du choix de la table en ajax.
function choixTable(base){
	var url = site_url+'/generercode/choisirTable';
	var parametres = {'base': base};
	$.post(url, parametres,
		function(data){
			$('#listerTables').html(data);
		}
	);
}

//--- Lancement de l'affichage de la table en ajax.
function infosTable(table){
	base = $("#choixBase").val();
	table = $("#tables option:selected").text();//.attr('text');
        console.log(table);
	var url = site_url+'/generercode/donneesTableAjax';
	var parametres = {'base': base, 'table': table};
	$.post(url, parametres,
		function(data){
			$('#infosTable').html(data);
		}
	);
}

function envoyerDonneesFormulaire(){
    var donneesFormulaire = $("#formulairePourFormulaire input:text, input:hidden, input:checkbox, select");
	var url = site_url+'/generercode/genererCode';
	var parametres = "({ 'table': '"+$("#tables option:selected").text()+"'";
	var separateur = ',';
	donneesFormulaire.each(
		function(){
			if($(this).attr('type')!='checkbox'){
				parametres+=separateur+'"'+$(this).attr('name')+'":"'+$(this).val()+'"';
				separateur = ',';
			}else{
				parametres+=separateur+'"'+$(this).attr('name')+'":"'+$(this).is(':checked')+'"';
			}
		}
	);
	
	parametres += '})';
	$.post(url, eval(parametres),
		function(data){
			$("#codeGenere").html(data);
		}
	);
}