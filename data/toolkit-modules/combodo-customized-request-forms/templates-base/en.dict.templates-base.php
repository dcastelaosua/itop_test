<?php
/**
 * Localized data
 *
 * @copyright Copyright (C) 2010-2018 Combodo SARL
 * @license	http://opensource.org/licenses/AGPL-3.0
 *
 * This file is part of iTop.
 *
 * iTop is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * iTop is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with iTop. If not, see <http://www.gnu.org/licenses/>
 */

Dict::Add('EN US', 'English', 'English', array(
	// Dictionary entries go here
	'Menu:Templates' => 'Templates',
	'Menu:Templates+' => 'Templates for object creation forms',
	'Templates:UserData' => 'Extra data',
	'Templates:Need' => 'Need',

	'Templates:PreviewTab:Title' => 'Preview',
	'Templates:PreviewTab:FormFields' => 'Faked form',
	'Templates:PreviewTab:HiddenFields' => 'Hidden fields',

	'Class:Template' => 'Template',
	'Class:Template+' => 'Template for creating objects from the portal',
	'Class:Template/Attribute:name' => 'Name',
	'Class:Template/Attribute:name+' => 'Internal name',
	'Class:Template/Attribute:label' => 'Label',
	'Class:Template/Attribute:label+' => 'Label used in the form',
	'Class:Template/Attribute:description' => 'Description',
	'Class:Template/Attribute:description+' => 'Description used in the form',
	'Class:Template/Attribute:field_list' => 'Fields',
	'Class:Template/Attribute:field_list+' => 'There must be a least one field defined, for the template to be useful',

	'Class:TemplateField' => 'Field',
	'Class:TemplateField+' => 'A field of a Template',
	'Class:TemplateField/Attribute:template_id' => 'Template',
	'Class:TemplateField/Attribute:template_id+' => '',
	'Class:TemplateField/Attribute:template_id_finalclass_recall' => 'Type',
	'Class:TemplateField/Attribute:template_id_finalclass_recall+' => '',
	'Class:TemplateField/Attribute:code' => 'Code',
	'Class:TemplateField/Attribute:code+' => 'Field code, must be unique within the template',
	'Class:TemplateField/Attribute:label' => 'Label',
	'Class:TemplateField/Attribute:label+' => 'Displayed to the end users',
	'Class:TemplateField/Attribute:order' => 'Order',
	'Class:TemplateField/Attribute:order+' => 'Position in the form',
	'Class:TemplateField/Attribute:mandatory' => 'Mandatory',
	'Class:TemplateField/Attribute:mandatory+' => 'This flag applies only if the field is displayed',
	'Class:TemplateField/Attribute:display_condition' => 'Display condition',
	'Class:TemplateField/Attribute:display_condition+' => 'Syntax is :template->code=\'value\'   For example :template->PC=\'Yes\'
The current field will be displayed to the user only if this condition is met.
Rather base your condition on \'Drop-down list\' or \'Read-only\' template field types',
	'Class:TemplateField/Attribute:input_type' => 'Input type',
	'Class:TemplateField/Attribute:input_type+' => 'Date: A pure date
Date and time: A date and time
Drop-down list:	A value to select within \'Values (OQL or CSV)\'
Duration: A time lapse
Hidden:	A read only string, visible in console modification only
List: A button to press amongst \'Values (OQL or CSV)\' buttons
Read-only: A read only string
Text: A single line of text
Text area: A text with several lines',
	'Class:TemplateField/Attribute:input_type/Value:text' => 'Text',
	'Class:TemplateField/Attribute:input_type/Value:text+' => 'A single line of text',
	'Class:TemplateField/Attribute:input_type/Value:text_area' => 'Text area',
	'Class:TemplateField/Attribute:input_type/Value:text_area+' => 'A text with several lines',
	'Class:TemplateField/Attribute:input_type/Value:drop_down_list' => 'Drop-down list',
	'Class:TemplateField/Attribute:input_type/Value:drop_down_list+' => 'A value to select within \'Values (OQL or CSV)\'',
	'Class:TemplateField/Attribute:input_type/Value:radio_buttons' => 'List',
	'Class:TemplateField/Attribute:input_type/Value:radio_buttons+' => 'A button to press amongst \'Values (OQL or CSV)\' buttons',
	'Class:TemplateField/Attribute:input_type/Value:date' => 'Date',
	'Class:TemplateField/Attribute:input_type/Value:date+' => 'A pure date',
	'Class:TemplateField/Attribute:input_type/Value:date_and_time' => 'Date and time',
	'Class:TemplateField/Attribute:input_type/Value:date_and_time+' => 'A date and time',
	'Class:TemplateField/Attribute:input_type/Value:duration' => 'Duration',
	'Class:TemplateField/Attribute:input_type/Value:duration+' => 'A time lapse',
	'Class:TemplateField/Attribute:input_type/Value:read_only' => 'Read-only',
	'Class:TemplateField/Attribute:input_type/Value:read_only+' => 'A read only string',
	'Class:TemplateField/Attribute:input_type/Value:hidden' => 'Hidden',
	'Class:TemplateField/Attribute:input_type/Value:hidden+' => 'A read only string visible in console modification only',
	'Class:TemplateField/Attribute:values' => 'Values (OQL or CSV)',
	'Class:TemplateField/Attribute:values+' => 'Allowed values for \'List\' and \'Drop-down list\'.
Format: "SELECT myClass WHERE name LIKE \'foo\'" or "val1,val2,..."
Cautious, no carriage return nor blank, just comma separated values, if not using an OQL,
If using OQL, objects proposed are limited to User rights. User Portal scopes are not applied.',
	'Class:TemplateField/Attribute:initial_value' => 'Initial value',
	'Class:TemplateField/Attribute:initial_value+' => 'Mandatory for \'Hidden\' and \'Read-only\' types.
The field is prefilled with this value when displayed in the form.',
	'Class:TemplateField/Attribute:format' => 'Format',
	'Class:TemplateField/Attribute:format+' => 'Regular expression (REGEXP) to control the format of the user provided value',
    'Class:TemplateField/Attribute:max_combo_length' => 'Auto-complete threshold',
    'Class:TemplateField/Attribute:max_combo_length+' => 'List and Drop-down list using OQL: Number of possible values above which the input uses auto-completion',

	'Class:TemplateField/Error:InvalidDisplayConditionOql' => 'The display condition has an invalid format, should match OQL conditions!',
	'Class:TemplateField/Error:InvalidDisplayConditionCode' => 'The display condition cannot use the field code as source!',
));
