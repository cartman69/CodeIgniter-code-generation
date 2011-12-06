<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class GenererCode extends CI_Controller {

    function __construct() {
        parent::__construct();
        $this->load->helper('url');

        //--- On est chez free? (si oui, on ne peut pas choisir la base)
        $this->chezFree = !(strpos(base_url(), '.free.fr/') === false);
    }

    /**
     *
     *
     * Par défaut, on fait choisir la base
     *
     */
    function index() {
        $this->choisirBase();
    }

    /**
     * 
     * Affichage de la combo avec la liste des bases
     * 
     */
    function choisirBase() {
        //--- Chargement de la classe CI
        $this->load->dbutil();
        //--- Listage des bases
        try {
            //--- Si on n'est pas chez free, on peut lister les bases
            if (!$this->chezFree) {

                $tBases_l = $this->dbutil->list_databases();

                //--- Appel de la vue
                $this->load->view('listeBases', array('bases' => array_merge(array('---'), $tBases_l)));
            } else {
                $this->load->database();
                $this->load->view('listeBases', array('bases' => array('--', $this->db->database)));
            }
        } catch (exception $e) {
            $this->load->database();
            $this->load->view('listeBases', array('bases' => array($this->db->database)));
        }
    }

    /**
     *
     * @param string $sBase_p
     * @return array 
     * 
     * Liste des tables d'une base
     */
    function listerTables($sBase_p) {
        //--- Chargement de la classe CI
        $this->load->database();

        //--- Sélection de la base passée en paramètre
        $this->db->database = $sBase_p;
        $this->db->db_select();

        //--- Listage des tables
        $tTables_l = $this->db->list_tables();
        return array_merge(array('---'), $tTables_l);
    }

    /**
     * 
     * Appel ajax à la méthode listerTables pour afficher une combo avec la liste des tables
     */
    function choisirTable() {
        $sBase_l = $this->input->get_post('base');
        $tTables_l = $this->listerTables($sBase_l);
        $this->load->helper('form');
        echo 'Choisissez une table : ', form_dropdown('tables', $tTables_l, null, 'id="tables" onchange="javascript : infosTable(this.value)"');
    }

    /**
     *
     * @param string $sBase_p
     * @param string $sTable_p 
     * 
     * Informations sur les champs de la table passée en paramètre
     * Appel à la vue d'affichage du formulaire
     * 
     */
    function informationsTable($sBase_p, $sTable_p) {
        $this->load->database();
        $this->db->database = $sBase_p;
        $this->db->db_select();
        $tDonneesChamps = $this->db->field_data($sTable_p);

        $this->load->view('formulaireChamps', array('listeChamps' => $tDonneesChamps));
    }

    /**
     * 
     * Appel ajax à la méthode informationsTable
     * 
     */
    function donneesTableAjax() {
        $sBase_l = $this->input->get_post('base');
        $sTable_l = $this->input->get_post('table');
        $this->informationsTable($sBase_l, $sTable_l);
    }

    /**
     *
     * @param string $sNom_p : Nom de la combo à générer
     * @return string => la combo du choix du type de contrôle 
     * 
     * Génération d'une combo pour le choix du type d'input
     * 
     */
    function listeTypesChamps($sNom_p) {
        //--- Les différents champs gérés par codeigniter
        $tTypesChamps_l = array('form_hidden' => 'Champ caché',
            'form_input' => 'Champ texte',
            'form_password' => 'Mot de passe',
            'form_textarea' => 'Zone de texte (textarea)',
            'form_dropdown' => 'Liste déroulante',
            'form_multiselect' => 'Zone de sélection multiple',
            'form_checkbox' => 'Case à cocher (checkbox)',
            'form_radio' => 'Bouton radio',);
        $this->load->helper('form');
        return form_dropdown($sNom_p, $tTypesChamps_l, 'form_input');
    }

    /**
     * 
     * Méthode qui va générer le code et l'afficher dans la vue
     * 
     */
    function genererFormulaire() {
        //--- Correspondance entre type de donnée dans la base et la validation
        $tTypesDonnees_l = array(
            'bigint' => 'integer',
            'int' => 'integer',
        );

        //--- On récupère les données postées
        $sBase_l = $this->input->get_post('choixBase', true);
        $sTable_l = $this->input->get_post('table', true);
        $tDonneesChamps_l = $this->input->get_post('donnees', true);
		$sNomController_l = $this->input->get_post('controller', true);
		$sNomVue_l = $this->input->get_post('view', true);
		$sNomModele_l = $this->input->get_post('model', true);

		//--- Début du contrôleur
		$sCodeController_l  = "<?php if (!defined('BASEPATH')) exit('No direct script access allowed');\n\n";
		$sCodeController_l .= "class {$sNomController_l} extends CI_Controller{\n\n";
		$sCodeController_l .= "\tfunction __construct(){\n";
		$sCodeController_l .= "\t\tparent::__construct();\n";
		$sCodeController_l .= "\t}\n\n";
		$sCodeController_l .= "\tfunction index(){\n";
		$sCodeController_l .= "\t\t\$this->afficherFormulaire();\n";
		$sCodeController_l .= "\t}\n\n";
		$sCodeController_l .= "\tfunction afficherFormulaire(){\n";
		$sCodeController_l .= "\t\t//--- Initialisation de la validation\n";
		//$sCodeController_l .= "\t\t\$this->affichervueFormulaire();\n";
		$sCodeController_l .= "\t\t\$this->load->library('form_validation');\n";
		$sCodeController_l .= "\t\t\$this->initValidation();\n";
		$sCodeController_l .= "\t\tif (\$this->form_validation->run() === false){\n";
		$sCodeController_l .= "\t\t\t//--- Action en cas d'erreur de validation\n";
		$sCodeController_l .= "\n\t\t\t//--- Affichage du formulaire\n";
		$sCodeController_l .= "\t\t\t\$this->afficherVueFormulaire();\n";
		$sCodeController_l .= "\t\t}else{\n";
		$sCodeController_l .= "\t\t\t\$this->enregistrerDonnees();\n";
		$sCodeController_l .= "\t\t}\n";
		$sCodeController_l .= "\t}\n\n";
		
        //--- Génération du formulaire
		$sCodeFormulaire_l  = "\n\tfunction afficherVueFormulaire(){\n";
        $sCodeFormulaire_l .= "\t\t\$this->load->helper('form');\n";
        $sCodeFormulaire_l .= "\t\t\$this->load->helper('html');\n";
        $sCodeFormulaire_l .= "\t\t\$champsFormulaires = array(\n";
		$sCodeFormulaireVue_l  = "<?php if (!defined('BASEPATH')) exit('No direct script access allowed');\n";
        $sCodeFormulaireVue_l .= "echo form_open();\n";

        //--- Génération de la validation
        $sCodeValidation_l = "\t\t\$this->load->library('form_validation');\n";

        //--- Génération du tableau des données pour l'écriture en base
        $sCodeEnregistrement_l = "if (\$this->form_validation->run() === false)\n";
        $sCodeEnregistrement_l .= "{\n";
        $sCodeEnregistrement_l .= "\t//--- Action en cas d\'erreur de validation\n";
        $sCodeEnregistrement_l .= "\n\t//--- Affichage du formulaire\n";
        $sCodeEnregistrement_l .= "}\n";
        $sCodeEnregistrement_l .= "else\n";
        $sCodeEnregistrement_l .= "{\n";
        $sCodeEnregistrement_l .= "\t//--- Enregistrement des données\n";
        $sCodeEnregistrement_l .= "\t\$tDonnees_l = array(\n";
		$sCodeFonctionEnregistrement_l = "\tfunction enregistrerDonnees(){\n";
		$sCodeFonctionEnregistrement_l .= "\t\t\$tDonneesFormulaire_l = array(\n";

		
        //--- Code de validation jquery
        $sCodeJquery_l = "$('form').validate(\n";
        $sCodeJquery_l .= "\trules: {\n";

        //--- Parcours de la liste des champs postés
        foreach ($tDonneesChamps_l as $indice => $tChamp_l) {
            //--- On a coché la case "dans le formulaire"
            if ($tChamp_l['generer'] === 'true') {
                $sCodeFormulaireVue_l .="\necho form_error('{$tChamp_l['nom_input']}');\n";

                //--- Génération du formulaire
                //--- En fonction du type de champ
                if ($tChamp_l['type_input'] != 'form_hidden') {
                    $sCodeFormulaire_l .="\t\t\t'{$tChamp_l['nom_input']}' => array(\n";
                    $sCodeFormulaire_l .="\t\t\t\t'name' => '{$tChamp_l['nom_input']}',\n";
                    $sCodeFormulaire_l .="\t\t\t\t'id' => '{$tChamp_l['nom_input']}',\n";
                    $sCodeFormulaire_l .="\t\t\t\t'value' => set_value('{$tChamp_l['nom_input']}'),\n";
                    if ($tChamp_l['longueur_max_champ'] != '') {
                        $sCodeFormulaire_l .="\t\t\t\t'maxlength' => {$tChamp_l['longueur_max_champ']}\n";
                    }
                    $sCodeFormulaire_l .="\t\t\t),\n";
                }

                switch ($tChamp_l['type_input']) {
                    case 'form_hidden':
                        $sCodeFormulaireVue_l.="echo form_hidden('{$tChamp_l['nom_input']}', set_value('{$tChamp_l['nom_input']}'));\n";
                        break;
                    case 'form_password':
                        $sCodeFormulaireVue_l.="echo form_label('{$tChamp_l['label_input']}', '{$tChamp_l['nom_input']}');\n";
                        $sCodeFormulaireVue_l.="echo form_password(\$champs['{$tChamp_l['nom_input']}']);\n";
                        $sCodeFormulaireVue_l.="echo br();\n";
                        break;
                    case 'form_textarea':
                        $sCodeFormulaireVue_l.="echo form_label('{$tChamp_l['label_input']}', '{$tChamp_l['nom_input']}');\n";
                        $sCodeFormulaireVue_l.="echo form_textarea(\$champs['{$tChamp_l['nom_input']}']);\n";
                        $sCodeFormulaireVue_l.="echo br();\n";
                        break;
                    case 'form_dropdown':
                        $sCodeFormulaireVue_l.="echo form_label('{$tChamp_l['label_input']}', '{$tChamp_l['nom_input']}');\n";
                        $sCodeFormulaireVue_l.="//--- Je vous laisse le soin de passer un tableau de valeurs à votre dropdown\n";
                        $sCodeFormulaireVue_l.="echo form_dropdown('{$tChamp_l['nom_input']}', array());\n";
                        $sCodeFormulaireVue_l.="echo br();\n";
                        break;
                    case 'form_multiselect':
                        $sCodeFormulaireVue_l.="echo form_label('{$tChamp_l['label_input']}', '{$tChamp_l['nom_input']}');\n";
                        $sCodeFormulaireVue_l.="//--- Je vous laisse le soin de passer un tableau de valeurs à votre multiselect\n";
                        $sCodeFormulaireVue_l.="echo form_multiselect('{$tChamp_l['nom_input']}', array());\n";
                        $sCodeFormulaireVue_l.="echo br();\n";
                        break;
                    case 'form_checkbox':
                        $sCodeFormulaireVue_l.="\necho form_label('{$tChamp_l['label_input']}', '{$tChamp_l['nom_input']}');\n";
                        $sCodeFormulaireVue_l.="echo form_checkbox(\$champs['{$tChamp_l['nom_input']}'], \$champs['{$tChamp_l['nom_input']}'], set_checkbox(\$champs['{$tChamp_l['nom_input']}'], \$champs['{$tChamp_l['nom_input']}']));\n";
                        $sCodeFormulaireVue_l.="echo br();\n";
                        break;
                    case 'form_radio':
                        $sCodeFormulaireVue_l.="\necho form_label('{$tChamp_l['label_input']}', '{$tChamp_l['nom_input']}');\n";
                        $sCodeFormulaireVue_l.="echo form_radio(\$champs['{$tChamp_l['nom_input']}'], \$champs['{$tChamp_l['nom_input']}'], set_radio(\$champs['{$tChamp_l['nom_input']}'], \$champs['{$tChamp_l['nom_input']}']));\n";
                        $sCodeFormulaireVue_l.="echo br();\n";
                        break;
                    case 'form_input':
                    default:
                        $sCodeFormulaireVue_l.="\necho form_label('{$tChamp_l['label_input']}', '{$tChamp_l['nom_input']}');\n";
                        $sCodeFormulaireVue_l.="echo form_input(\$champs['{$tChamp_l['nom_input']}']);\n";
                        $sCodeFormulaireVue_l.="echo br();\n";
                        break;
                }

                //--- Génération de la validation codeIgniter
                $sCodeValidation_l.="\t\t\$this->form_validation->set_rules('{$tChamp_l['nom_input']}', '{$tChamp_l['label_input']}', 'trim";
                if ($tChamp_l['obligatoire'] === 'true') {
                    $sCodeValidation_l.="|required";
                }
                if ($tChamp_l['longueur_max_champ'] != '') {
                    $sCodeValidation_l.="|max_length[{$tChamp_l['longueur_max_champ']}]";
                }
                $sCodeValidation_l.="|xss_clean');\n";
                //--- Génération du tableau des données
                $sCodeEnregistrement_l.="\t\t'{$tChamp_l['nom_champ']}' => set_value('{$tChamp_l['nom_input']}'), \n";
				$sCodeFonctionEnregistrement_l .= "\t\t\t'{$tChamp_l['nom_champ']}' => set_value('{$tChamp_l['nom_input']}'), \n";


                //--- Validation jQuery
                $sCodeJquery_l.="\t\t".$tChamp_l['nom_input'] . ": {";

                $sSeparateur_l = '';
                switch ($tChamp_l['type_donnee']) {
                    case 'int' :
                    case 'tinyint':
                        $sCodeJquery_l.="number: true";
                        $sSeparateur_l = ', ';
                        break;
                    case 'datetime':
                        $sCodeJquery_l.="date: true";
                        $sSeparateur_l = ', ';
                        break;

                    default:
                        break;
                }
                if ($tChamp_l['obligatoire'] === 'true') {
                    $sCodeJquery_l.=$sSeparateur_l . "required: true";
                }
                if ($tChamp_l['longueur_max_champ'] != '') {
                    $sCodeJquery_l.=$sSeparateur_l . "maxlength: {$tChamp_l['longueur_max_champ']
                            }";
                }


                $sCodeJquery_l.="}, \n";
            }
        }
		
		//---- fin de la fonction d'affichage du formulaire
		$sCodeFormulaire_l .= "\t\t);\n"; 
		$sCodeFormulaire_l .= "\t\t\$this->load->view('{$sNomVue_l}', array('champs' => \$champsFormulaires));\n"; 
		$sCodeFormulaire_l .= "\t}\n\n"; 
		
        $sCodeFormulaireVue_l .= "\necho form_submit('Enregistrer');
        \n";
        $sCodeFormulaireVue_l .= "\necho form_close();
        \n";

        //--- Fin du tableau contenant les données à enregistrer
        $sCodeEnregistrement_l.="\t);\n";
		$sCodeFonctionEnregistrement_l .= "\t\t);\n";
		$sCodeFonctionEnregistrement_l .= "\t\t\$this->load->model('{$sNomModele_l}');\n";
		$sCodeFonctionEnregistrement_l .= "\t\t\$this->{$sNomModele_l}->enregistrer(\$tDonneesFormulaire_l);\n";
		$sCodeFonctionEnregistrement_l .= "\t}\n\n";

        //--- Active record : on vérifie la clé primaire pour choisir insert / update
        $sChampClePrimaire = $this->input->get_post('clePrimaire');
        $sCodeEnregistrement_l.="\tif(\$tDonnees_l['{$sChampClePrimaire}'] === '' ) {\n";
        $sCodeEnregistrement_l.="\t\t\$this->db->insert('{$sTable_l}', \$tDonnees_l);\n";
        $sCodeEnregistrement_l.="\t} else {\n";
        $sCodeEnregistrement_l.="\t\t\$this->db->where('{$sChampClePrimaire}', \$tDonnees_l['{$sChampClePrimaire}']);\n";
        $sCodeEnregistrement_l.="\t\t\$this->db->update('{$sTable_l}', \$tDonnees_l);\n";
        $sCodeEnregistrement_l.="\t}\n";
		$sCodeEnregistrement_l.="}\n";

		//--- Code du modele
		$sCodeModele_l  = "<?php if (!defined('BASEPATH')) exit('No direct script access allowed');\n\n";
		$sCodeModele_l.="class {$sNomModele_l} extends CI_Model{\n";
		$sCodeModele_l.="\tprotected \$base='{$sBase_l}';\n";
		$sCodeModele_l.="\tprotected \$table='{$sTable_l}';\n";
		$sCodeModele_l.="\tprotected \$clePrimaire='{$sChampClePrimaire}';\n";
		$sCodeModele_l.="\tfunction __construct(){\n";
		$sCodeModele_l.="\t\tparent::__construct();\n";
		$sCodeModele_l.="\t\t\$this->load->database();\n";
		$sCodeModele_l.="\t\t\$this->db->database = \$this->base;\n";
        $sCodeModele_l.="\t\t\$this->db->db_select();\n";
		$sCodeModele_l.="\t}\n\n";
		$sCodeModele_l.="\tfunction enregistrer(\$tDonnees_p){\n";
		$sCodeModele_l.="\t\tif(\$tDonnees_p['{$sChampClePrimaire}'] === '' ) {\n";
		$sCodeModele_l.="\t\t\treturn \$this->db->insert(\$this->base.'.'.\$this->table, \$tDonnees_p);\n";
		$sCodeModele_l.="\t\t}else{\n";
        $sCodeModele_l.="\t\t\t\$this->db->where('{$sChampClePrimaire}', \$tDonnees_p['{$sChampClePrimaire}']);\n";
        $sCodeModele_l.="\t\t\treturn \$this->db->update('{$sTable_l}', \$tDonnees_p);\n";
		$sCodeModele_l.="\t\t}\n";
		$sCodeModele_l.="\t}\n";
		$sCodeModele_l.="}\n";
		
      $sCodeJquery_l=substr($sCodeJquery_l, 0, -3);
        $sCodeJquery_l.="\n\t}\n)";
        /*
          echo $sCodeValidation_l;
          echo "//------------------------------------------------------------------------------\n";
          echo $sCodeFormulaire_l;
          echo "//------------------------------------------------------------------------------\n";
          echo $sCodeEnregistrement_l;
         *
         */
		 
		 //--- fonction initValidation
		 $sCodeController_l.="\tfunction initValidation(){\n";
		 $sCodeController_l.=$sCodeValidation_l;
		 $sCodeController_l.="\t}\n\n";
		 
		 //--- Fonction affichage
		 $sCodeController_l.=$sCodeFormulaire_l;
		 
		 //--- Fonction enregistrement
		 $sCodeController_l.=$sCodeFonctionEnregistrement_l;
		 
		 //--- Fin crontrôleur
		 $sCodeController_l.="}";
		 
        $this->load->view('codeGenere', array('sCodeFormulaire' => $sCodeFormulaire_l,
			'sCodeVue' => $sCodeFormulaireVue_l,
			'sCodeModele' => $sCodeModele_l,
            'sCodeValidation' => $sCodeValidation_l,
            'sCodeEnregistrement' => $sCodeEnregistrement_l,
            'sCodeJquery' => $sCodeJquery_l,
			'sCodeController' => $sCodeController_l));
    }

}