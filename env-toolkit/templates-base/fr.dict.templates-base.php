<?php
/**
 * Localized data
 *
 * @copyright Copyright (C) 2010-2024 Combodo SAS
 * @license    https://opensource.org/licenses/AGPL-3.0
 * 
 */
/**
 *
 */
Dict::Add('FR FR', 'French', 'Français', [
	'Class:Template' => 'Modèle',
	'Class:Template+' => 'Modèle pour un formulaire de création d\'objet à partir du portail',
	'Class:Template/Attribute:description' => 'Description',
	'Class:Template/Attribute:description+' => 'Description dans le formulaire',
	'Class:Template/Attribute:field_list' => 'Champs',
	'Class:Template/Attribute:field_list+' => 'Définir au moins un champ, sinon le modèle ne sert à rien',
	'Class:Template/Attribute:label' => 'Label',
	'Class:Template/Attribute:label+' => 'Entête utilisé dans le formulaire',
	'Class:Template/Attribute:name' => 'Nom',
	'Class:Template/Attribute:name+' => 'Nom interne',
	'Class:TemplateField' => 'Champ',
	'Class:TemplateField+' => 'Le champ d\'un Modèle',
	'Class:TemplateField/Attribute:code' => 'Code',
	'Class:TemplateField/Attribute:code+' => 'Code du champ. Il doit être unique au sein du modèle',
	'Class:TemplateField/Attribute:display_condition' => 'Condition d\'affichage',
	'Class:TemplateField/Attribute:display_condition+' => 'La syntaxe est :template->code=\'value\'   Exemple :template->PC=\'Yes\'
Ce champ ne sera proposé, que si un autre champ contient une valeur particulière,
Il est préférable de dépendre de champs \'Liste\' ou \'Liste déroulante\'',
	'Class:TemplateField/Attribute:format' => 'Expression régulière',
	'Class:TemplateField/Attribute:format+' => 'Permet de contrôler le format de saisie du champ à l\'aide d\'une REGEXP',
	'Class:TemplateField/Attribute:initial_value' => 'Valeur initiale',
	'Class:TemplateField/Attribute:initial_value+' => 'Valeur préremplie à l\'affichage du formulaire.
Obligatoire pour les champs de type \'Lecture seule\' et \'Caché\',
Peut être utile pour d\'autres types de champ',
	'Class:TemplateField/Attribute:input_type' => 'Type de donnée',
	'Class:TemplateField/Attribute:input_type+' => 'Caché :	Instruction pour l\'agent, visible en console, non modifiable, 
Date : Une simple date, 
Date et Heure : Une date-heure, 
Durée : Une durée, 
Lecture seule : Instruction pour l\'utilisateur, non modifiable, 
Liste : Un choix parmi des \'Valeurs (OQL ou CSV)\' sous forme de boutons, 
Liste déroulante :	Un choix parmi des \'Valeurs (OQL ou CSV)\' en liste, 
Texte : Texte d\'une seule ligne, 
Zone de texte : Texte de plusieurs lignes',
	'Class:TemplateField/Attribute:input_type/Value:date' => 'Date',
	'Class:TemplateField/Attribute:input_type/Value:date+' => 'Une simple date',
	'Class:TemplateField/Attribute:input_type/Value:date_and_time' => 'Date et Heure',
	'Class:TemplateField/Attribute:input_type/Value:date_and_time+' => 'Une date-heure',
	'Class:TemplateField/Attribute:input_type/Value:drop_down_list' => 'Liste déroulante',
	'Class:TemplateField/Attribute:input_type/Value:drop_down_list+' => 'Un choix parmi des \'Valeurs (OQL ou CSV)\' en liste',
	'Class:TemplateField/Attribute:input_type/Value:duration' => 'Durée',
	'Class:TemplateField/Attribute:input_type/Value:duration+' => 'Une durée',
	'Class:TemplateField/Attribute:input_type/Value:hidden' => 'Caché',
	'Class:TemplateField/Attribute:input_type/Value:hidden+' => 'Instruction pour l\'agent, visible en console, non modifiable',
	'Class:TemplateField/Attribute:input_type/Value:radio_buttons' => 'Liste',
	'Class:TemplateField/Attribute:input_type/Value:radio_buttons+' => 'Un choix parmi des \'Valeurs (OQL ou CSV)\' sous forme de boutons',
	'Class:TemplateField/Attribute:input_type/Value:read_only' => 'Lecture seule',
	'Class:TemplateField/Attribute:input_type/Value:read_only+' => 'Instruction pour l\'utilisateur, non modifiable',
	'Class:TemplateField/Attribute:input_type/Value:text' => 'Texte',
	'Class:TemplateField/Attribute:input_type/Value:text+' => 'Texte d\'une seule ligne',
	'Class:TemplateField/Attribute:input_type/Value:text_area' => 'Zone de texte',
	'Class:TemplateField/Attribute:input_type/Value:text_area+' => 'Texte de plusieurs lignes',
	'Class:TemplateField/Attribute:label' => 'Label',
	'Class:TemplateField/Attribute:label+' => 'Label affiché à l\'utilisateur final',
	'Class:TemplateField/Attribute:mandatory' => 'Obligatoire',
	'Class:TemplateField/Attribute:mandatory+' => 'Ce champ doit-il être obligatoirement rempli par l\'utilisateur, dès lors qu\'il est présent ?',
	'Class:TemplateField/Attribute:max_combo_length' => 'Seuil d\'auto-completion',
	'Class:TemplateField/Attribute:max_combo_length+' => 'Liste et Liste déroulante : Si les valeurs possibles sont définies par un OQL, la saisie passe en auto-completion au dessus de ce nombre',
	'Class:TemplateField/Attribute:order' => 'Ordre',
	'Class:TemplateField/Attribute:order+' => 'Position dans le formulaire',
	'Class:TemplateField/Attribute:template_id' => 'Modèle',
	'Class:TemplateField/Attribute:template_id+' => '',
	'Class:TemplateField/Attribute:template_id_finalclass_recall' => 'Type',
	'Class:TemplateField/Attribute:template_id_finalclass_recall+' => '',
	'Class:TemplateField/Attribute:values' => 'Valeurs (OQL ou CSV)',
	'Class:TemplateField/Attribute:values+' => 'Spécifier les valeurs possibles.
Pertinent et obligatoire seulement pour les \'Liste\' et \'Liste Déroulante\'.
Format : "SELECT myClass WHERE name LIKE \'foo\'" ou "val1,val2,..."
Attention, ne pas mettre de retour à la ligne, ni d\'espace entre deux valeurs autorisées.
En cas d\'OQL, les objets proposés sont limités aux droits de l\'utilisateur. Les scopes du Portail Utilisateur ne sont pas applicables.',
	'Class:TemplateField/Error:InvalidDisplayConditionCode' => 'Le code du champ courant ne peut être utilisé dans la condition d\'affichage !',
	'Class:TemplateField/Error:InvalidDisplayConditionOql' => 'Le format de la condition d\'affichage est invalide, il doit correspondre à celui des conditions OQL !',
	'Menu:Templates' => 'Modèle',
	'Menu:Templates+' => 'Modèle pour un formulaire de création d\'objet',
	'Templates:Need' => 'Besoin',
	'Templates:PreviewTab:FormFields' => 'Formulaire',
	'Templates:PreviewTab:HiddenFields' => 'Champs cachés',
	'Templates:PreviewTab:Title' => 'Prévisualisation',
	'Templates:UserData' => 'Données complémentaires',
]);
