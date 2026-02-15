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
Dict::Add('PT BR', 'Brazilian', 'Brazilian', [
	'Class:Template' => 'Modelo',
	'Class:Template+' => 'Modelo para criar objetos no Portal',
	'Class:Template/Attribute:description' => 'Descrição',
	'Class:Template/Attribute:description+' => 'Descrição usada no formulário',
	'Class:Template/Attribute:field_list' => 'Campos',
	'Class:Template/Attribute:field_list+' => '',
	'Class:Template/Attribute:label' => 'Rótulo',
	'Class:Template/Attribute:label+' => 'Rótulo usado no formulário',
	'Class:Template/Attribute:name' => 'Nome',
	'Class:Template/Attribute:name+' => 'Nome interno',
	'Class:TemplateField' => 'Campo',
	'Class:TemplateField+' => '',
	'Class:TemplateField/Attribute:code' => 'Código',
	'Class:TemplateField/Attribute:code+' => 'Código do atributo ou qualquer valor',
	'Class:TemplateField/Attribute:display_condition' => 'Display condition~~',
	'Class:TemplateField/Attribute:display_condition+' => 'Syntax is :template->code=\'value\'   For example :template->PC=\'Yes\'
The current field will be displayed to the user only if this condition is met.
Rather base your condition on \'Drop-down list\' or \'Read-only\' template field types~~',
	'Class:TemplateField/Attribute:format' => 'Formato',
	'Class:TemplateField/Attribute:format+' => 'Expressão regular',
	'Class:TemplateField/Attribute:initial_value' => 'Valor inicial',
	'Class:TemplateField/Attribute:initial_value+' => '',
	'Class:TemplateField/Attribute:input_type' => 'Tipo de entrada',
	'Class:TemplateField/Attribute:input_type+' => '',
	'Class:TemplateField/Attribute:input_type/Value:date' => 'Data',
	'Class:TemplateField/Attribute:input_type/Value:date+' => '',
	'Class:TemplateField/Attribute:input_type/Value:date_and_time' => 'Data e hora',
	'Class:TemplateField/Attribute:input_type/Value:date_and_time+' => '',
	'Class:TemplateField/Attribute:input_type/Value:drop_down_list' => 'Lista suspensa',
	'Class:TemplateField/Attribute:input_type/Value:drop_down_list+' => '',
	'Class:TemplateField/Attribute:input_type/Value:duration' => 'Duração',
	'Class:TemplateField/Attribute:input_type/Value:duration+' => '',
	'Class:TemplateField/Attribute:input_type/Value:hidden' => 'Oculto',
	'Class:TemplateField/Attribute:input_type/Value:hidden+' => '',
	'Class:TemplateField/Attribute:input_type/Value:radio_buttons' => 'Lista',
	'Class:TemplateField/Attribute:input_type/Value:radio_buttons+' => '',
	'Class:TemplateField/Attribute:input_type/Value:read_only' => 'Somente leitura',
	'Class:TemplateField/Attribute:input_type/Value:read_only+' => '',
	'Class:TemplateField/Attribute:input_type/Value:text' => 'Texto',
	'Class:TemplateField/Attribute:input_type/Value:text+' => '',
	'Class:TemplateField/Attribute:input_type/Value:text_area' => 'Área de texto',
	'Class:TemplateField/Attribute:input_type/Value:text_area+' => '',
	'Class:TemplateField/Attribute:label' => 'Rótulo',
	'Class:TemplateField/Attribute:label+' => 'Exibido para os usuários finais',
	'Class:TemplateField/Attribute:mandatory' => 'Obrigatório',
	'Class:TemplateField/Attribute:mandatory+' => '',
	'Class:TemplateField/Attribute:max_combo_length' => 'Auto-complete threshold~~',
	'Class:TemplateField/Attribute:max_combo_length+' => 'List and Drop-down list using OQL: Number of possible values above which the input uses auto-completion~~',
	'Class:TemplateField/Attribute:order' => 'Ordem',
	'Class:TemplateField/Attribute:order+' => 'Posição no formulário',
	'Class:TemplateField/Attribute:template_id' => 'Modelo',
	'Class:TemplateField/Attribute:template_id+' => '',
	'Class:TemplateField/Attribute:template_id_finalclass_recall' => 'Tipo',
	'Class:TemplateField/Attribute:template_id_finalclass_recall+' => '',
	'Class:TemplateField/Attribute:values' => 'Valores (OQL ou CSV)',
	'Class:TemplateField/Attribute:values+' => '"SELECT myClass WHERE name LIKE \'foo\'" or "val1,val2,..."',
	'Class:TemplateField/Error:InvalidDisplayConditionCode' => 'The display condition cannot use the field code as source!~~',
	'Class:TemplateField/Error:InvalidDisplayConditionOql' => 'The display condition has an invalid format, should match OQL conditions!~~',
	'Menu:Templates' => 'Modelos',
	'Menu:Templates+' => 'Modelos para formulários de criação de objetos',
	'Templates:Need' => 'Necessidade',
	'Templates:PreviewTab:FormFields' => 'Formulário falso',
	'Templates:PreviewTab:HiddenFields' => 'Campos ocultos',
	'Templates:PreviewTab:Title' => 'Anterior',
	'Templates:UserData' => 'Dados extras',
]);
