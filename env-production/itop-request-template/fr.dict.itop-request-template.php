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
	'Class:Incident/Attribute:service_details' => 'Détails sur le service',
	'Class:Incident/Attribute:service_details+' => 'Informations additionnelles dépendantes du service choisi',
	'Class:RequestTemplate' => 'Modèle de requête',
	'Class:RequestTemplate+' => 'Un modèle définit un ensemble de champs additionels qui seront dynamiquement ajoutés au formulaire de demande en fonction de la sous-catégorie de service choisie',
	'Class:RequestTemplate/Attribute:service_id' => 'Service',
	'Class:RequestTemplate/Attribute:service_id+' => '',
	'Class:RequestTemplate/Attribute:service_name' => 'Nom service',
	'Class:RequestTemplate/Attribute:service_name+' => '',
	'Class:RequestTemplate/Attribute:servicesubcategory_id' => 'Sous catégorie de service',
	'Class:RequestTemplate/Attribute:servicesubcategory_id+' => 'Un modèle ne s\'applique qu\'à une et une seule sous-catégorie de service',
	'Class:RequestTemplate/Attribute:servicesubcategory_name' => 'Nom sous catégorie de service',
	'Class:RequestTemplate/Attribute:servicesubcategory_name+' => '',
	'Class:ServiceSubcategory/Attribute:requesttemplates_list' => 'Modèles de requête',
	'Class:ServiceSubcategory/Attribute:requesttemplates_list+' => 'S\'il y a plusieurs modèles, l\'utilisateur ne pourra en remplir qu\'un',
	'Class:UserRequest/Attribute:service_details' => 'Détails sur le service',
	'Class:UserRequest/Attribute:service_details+' => 'Informations additionnelles dépendantes du service choisi',
	'Menu:RequestTemplate' => 'Modèles de requête',
	'Menu:RequestTemplate+' => 'Tous les modèles de requête',
]);
