<?php
/**
 * Copyright (C) 2013-2024 Combodo SAS
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
 */

use Combodo\iTop\Application\UI\Base\Component\Field\FieldUIBlockFactory;
use Combodo\iTop\Application\UI\Base\Component\FieldSet\FieldSetUIBlockFactory;
use Combodo\iTop\Application\UI\Base\Component\Html\Html;
use Combodo\iTop\Application\UI\Base\Layout\UIContentBlockUIBlockFactory;
use Combodo\iTop\Form\Validator\CustomRegexpValidator;
use Combodo\iTop\Form\Validator\Validator;
use Combodo\iTop\Renderer\BlockRenderer;


/**
 * Module templates-base
 *
 * @author      Erwan Taloc <erwan.taloc@combodo.com>
 * @author      Romain Quetiez <romain.quetiez@combodo.com>
 * @author      Denis Flaven <denis.flaven@combodo.com>
 * @license     http://www.opensource.org/licenses/gpl-3.0.html LGPL
 */

abstract class Template extends cmdbAbstractObject
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "bizmodel,searchable",
			"key_type" => "autoincrement",
			"name_attcode" => "name",
			"state_attcode" => "",
			"reconc_keys" => array("name","label"),
			"db_table" => "tpl_base",
			"db_key_field" => "id",
			"db_finalclass_field" => "",
			"display_template" => "",
		);
		MetaModel::Init_Params($aParams);
		MetaModel::Init_InheritAttributes();

		MetaModel::Init_AddAttribute(new AttributeString("name", array("allowed_values"=>null, "sql"=>"name", "default_value"=>"", "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("label", array("allowed_values"=>null, "sql"=>"label", "default_value"=>"", "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("description", array("allowed_values"=>null, "sql"=>"description", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeLinkedSet("field_list", array("linked_class"=>"TemplateField", "ext_key_to_me"=>"template_id", "allowed_values"=>null, "count_min"=>0, "count_max"=>0,"edit_mode"=>LINKSET_EDITMODE_INPLACE, "depends_on"=>array(), "tracking_level"=>LINKSET_TRACKING_ALL)));

		MetaModel::Init_SetZListItems('details', array('name', 'label', 'description', 'field_list'));
		MetaModel::Init_SetZListItems('advanced_search', array('name', 'label', 'description'));
		MetaModel::Init_SetZListItems('standard_search', array('name', 'label', 'description'));
		MetaModel::Init_SetZListItems('list', array('name', 'label'));
	}

	/**
	 * @return string The name of the class using the template
	 */
	public abstract function GetTargetClass();

	/**
	 * Make a serializable array out of the characteristics of the template
	 */
	public function ToArray()
	{
		$aRet = array(
			'class' => get_class($this),
			'id' => $this->GetKey(),
			'label' => $this->Get('label'),
			'name' => $this->Get('name'),
			'description' => $this->Get('description'),
			'fields' => array(),
			'hidden_fields' => array(),
		);
		$oFieldSet = $this->Get('field_list');
		while ($oField = $oFieldSet->Fetch())
		{
			$aFieldData = $oField->ToArray();
			$aRet['fields'][$aFieldData['code']] = $aFieldData;
			if ($aFieldData['input_type'] == 'hidden')
			{
				$aRet['hidden_fields'][$aFieldData['code']] = true;
			}
		}

		return $aRet;
	}

	/**
	 * @param DBObject|null $oHostObject
	 * @param \Combodo\iTop\Form\Form $oForm
	 * @param array{
	 *      class: string,
	 *      id: string,
	 *      label: string,
	 *      name: string,
	 *      description: string,
	 *      fields: array,
	 *      hidden_fields: array
	 *     } $aTemplateData
	 * @param array $aValues
	 * @param array $aPreviousValues
	 *
	 * @return array
	 *
	 * @throws FieldInvalidDependencyException if field depends on a non-existing field (N°2922)
	 * @throws \CoreUnexpectedValue if trying to set forbidden field (N°1150)
	 *
	 * @since 3.5.0 N°1150 check setting hidden or read_only fields
	 */
	public static function PopulateUserDataForm($oHostObject, \Combodo\iTop\Form\Form $oForm, $aTemplateData, $aValues, $aPreviousValues = []) {
		$aFieldSpecs = $aTemplateData['fields'];
		$aExtKeyFields = array();
		$aFields = array(); // code => field spec
		$aDisplaySortKeys = array();
		$aDisplayConditions = array();

		$aDisplayValues = array();

		$aReadOnlyFieldsWithValuesToSet = [];

		/** @var array{
		 *          class: string,
		 *          id: string,
		 *          code: string,
		 *          label: string,
		 *          order: int,
		 *          mandatory: string,
		 *          display_condition: string,
		 *          input_type: string,
		 *          values: mixed,
		 *          initial_value: mixed,
		 *          format: string,
		 *          max_combo_length: int
		 *     } $aFieldData
		 */
		foreach ($aFieldSpecs as $aFieldData) {
			$sFieldCode = $aFieldData['code'];
			$aFields[$sFieldCode] = $aFieldData;
			$aDisplaySortKeys[] = (int)$aFieldData['order'];

			$sDisplayCondition = (array_key_exists('display_condition', $aFieldData))
				? $aFieldData['display_condition']
				: null;
			$aDisplayConditions[$sFieldCode] = $sDisplayCondition;

			// If no value in $aValues this is the first display
			// silently discarding hidden fields updates (N°6333)
			//TODO when multiplevalues Field will be develop, add condition on field
			if (isset($aValues[$sFieldCode]) && ($aFieldData['input_type'] !== 'hidden')) {
				if ((ContextTag::Check(ContextTag::TAG_REST))
					&& ($aFieldData['input_type'] === 'read_only')
					&& ($aValues[$sFieldCode] !== $aFieldData['initial_value'])) {
					// N°1150 N°6333 some fields must not be updated
					// When building the form in console object edit screen, we are setting read_only fields initial value, so we don't want to crash then !
					// But we need to crash when setting a custom value (which can be done with the REST API)
					// N°6484 Warning : in console if the field is refreshed (display condition) it is set to a blank value, so we might crash :( To avoid this using ContextTag
					$aReadOnlyFieldsWithValuesToSet[] = $sFieldCode;
				}

				if (is_array($aValues[$sFieldCode])) {
					$aDisplayValues[$sFieldCode] = $aValues[$sFieldCode][0];
				}
				else {
					$aDisplayValues[$sFieldCode] = $aValues[$sFieldCode];
				}
			}
			else {
				$aDisplayValues[$sFieldCode] = $aFieldData['initial_value'];
			}

			if (strlen($aDisplayValues[$sFieldCode]) == 0 && isset($aPreviousValues[$sFieldCode])) {
				$aDisplayValues[$sFieldCode] = $aPreviousValues[$sFieldCode];
			}

			switch ($aFieldData['input_type']) {
				case 'drop_down_list':
				case 'radio_buttons':
					$sValues = $aFieldData['values'];
					$oSearch = null;
					$aTemplateArgs = array();
					if (strlen($sValues) > 0) {
						try {
							$oSearch = DBObjectSearch::FromOQL($sValues);
							foreach ($oSearch->GetQueryParams() as $sParam => $foo) {
								$iPos = strpos($sParam, '->');
								if ($iPos !== false) {
									$sRefName = substr($sParam, 0, $iPos);
									if ($sRefName == 'template') {
										$sCode = substr($sParam, $iPos + 2);
										$aTemplateArgs[$sCode] = null;
										$oForm->AddFieldDependency($sFieldCode, $sCode);
									}
								}
							}
						} catch (Exception $e) {
							// The values are defined as a CSV list
						}
					}
				// Keep that information for further reuse in ArrayToFormField
				$aFields[$sFieldCode]['_oSearch_'] = $oSearch;
				$aFields[$sFieldCode]['_aTemplateArgs_'] = $aTemplateArgs;
				if ($oSearch) {
					// List ext keys to record object class/name when storing the info into the DB
					$aExtKeyFields[$sFieldCode] = $oSearch->GetClass();
				}
				break;
			}
		}

		// Initially we were also crashing on setting hidden fields
		// But a bug displays those fields in the portal, and they are set after refreshing or submitting the request template :(
		// So in consequence hidden fields are just ignored (N°6333)
		$aForbiddenFields = $aReadOnlyFieldsWithValuesToSet;
		if (count($aForbiddenFields) > 0) {
			$sForbiddenFields = implode(', ', $aForbiddenFields);
			$sExceptionMessage = 'Cannot set read_only fields : '.$sForbiddenFields;
			throw new CoreUnexpectedValue($sExceptionMessage);
		}

		// Adding dependencies for the field display_condition
		foreach ($aDisplayConditions as $sFieldCode => $sDisplayCondition) {
			$aMasterFieldsList = static::GetMasterFieldsListForDisplayCondition($sFieldCode, $aDisplayConditions);
			foreach ($aMasterFieldsList as $sMasterFieldCode) {
				if (!array_key_exists($sMasterFieldCode, $aFields)) {
					throw new FieldInvalidDependencyException("Display '$sFieldCode' depends on '$sMasterFieldCode' witch doesn't exist");
				}
				$oForm->AddFieldDependency($sFieldCode, $sMasterFieldCode);
			}
		}

		// Build the fields with the relevant display order
		array_multisort($aDisplaySortKeys, $aFields);
		$aCachedDisplayConditionsResults = array();
		foreach ($aFields as $sFieldCode => $aFieldData) {
			$bIsFieldVisibleToCurrentUser = self::IsFieldVisibleToCurrentUser($aTemplateData, $sFieldCode);
			$bIsDisplayConditionOk = static::IsFieldDisplayConditionOk($sFieldCode, $aDisplayConditions, $aDisplayValues,	$aCachedDisplayConditionsResults);
			if ($bIsFieldVisibleToCurrentUser && $bIsDisplayConditionOk) {
				$oField = static::MakeFormField($oHostObject, $aFieldData, $oForm);
				$oField->SetCurrentValue($aDisplayValues[$sFieldCode]);
			} else {
				// Leave a placeholder so as to preserve the field order
				$oField = new Combodo\iTop\Form\Field\HiddenField($aFieldData['code']);
				$oField->SetCurrentValue('');
				// Add metadata in order to keep previous/initial value
				if (isset($aDisplayValues[$sFieldCode]) && strlen($aDisplayValues[$sFieldCode])>0) {
					$oField->AddMetadata('attribute-previous-value', $aDisplayValues[$sFieldCode]);
				} elseif (isset($aFieldData['initial_value']) && strlen($aFieldData['initial_value'])>0){
					$oField->AddMetadata('attribute-previous-value', $aFieldData['initial_value']);
				}
			}

			// Overload (AttributeDefinition) flags metadata as they have been changed while building the form
			// Note: Protection for iTop 2.6 and earlier
			if (is_callable(array($oField, 'AddMetadata'))) {
				$oField->AddMetadata('attribute-flag-hidden', $oField->GetHidden() ? 'true' : 'false');
				$oField->AddMetadata('attribute-flag-read-only', $oField->GetReadOnly() ? 'true' : 'false');
				$oField->AddMetadata('attribute-flag-mandatory', $oField->GetMandatory() ? 'true' : 'false');
				$oField->AddMetadata('attribute-flag-must-change', $oField->GetMustChange() ? 'true' : 'false');
			}

			$oForm->AddField($oField);
		}

		return $aExtKeyFields;
	}

	/**
	 * @param array $aTemplateData
	 *
	 * @return array field code as array key, display_condition as array value
	 */
	public static function GetDisplayConditions($aTemplateData)
	{
		$aDisplayConditions = array();
		foreach ($aTemplateData['fields'] as $sFieldCode => $aFieldProperties) {
			$sDisplayCondition = (array_key_exists('display_condition', $aFieldProperties))
				? $aFieldProperties['display_condition']
				: null;
			$aDisplayConditions[$sFieldCode] = $sDisplayCondition;
		}

		return $aDisplayConditions;
	}

	/**
	 * If we have multiple conditions levels, for example these $aDisplayConditions values :
	 *      'A' => "",
	 *      'B' => "A='Yes'",
	 *      'C' => "B='True'",
	 * Then field C needs to be dependant of both B and A fields change in the GUI
	 *
	 * @param string $sFieldCode
	 * @param array $aDisplayConditions field code as array key, TemplateField.display_condition as array value
	 *
	 * @return array|null list of field codes that $sFieldCode depends on
	 *        null can be returned so that we can handle circular display conditions and avoid infinite loops
	 */
	private static function GetMasterFieldsListForDisplayCondition($sFieldCode, $aDisplayConditions, &$aBrowsedFieldsCodes = null)
	{
		$bIsTopOfCallStack = false;
		if (is_null($aBrowsedFieldsCodes)) {
			$bIsTopOfCallStack = true;
			$aBrowsedFieldsCodes = array();
		}
		$aBrowsedFieldsCodes[] = $sFieldCode;

		$aMasterFields = array();
		if (array_key_exists($sFieldCode, $aDisplayConditions)) {
			$sDisplayCondition = $aDisplayConditions[$sFieldCode];
		} else {
			$sDisplayCondition = '';
		}
		if (empty($sDisplayCondition)) {
			return array();
		}

		$sDisplayConditionRegExp = '/'.TemplateField::DISPLAY_CONDITION_VALIDATION_PATTERN.'/';
		preg_match($sDisplayConditionRegExp, $sDisplayCondition, $aMatches, PREG_OFFSET_CAPTURE);
		if (count($aMatches) < 4) {
			return array();
		}
		$sMasterFieldCode = $aMatches[1][0];
		if (in_array($sMasterFieldCode, $aBrowsedFieldsCodes, true)) {
			// avoid infinite loops for circular display conditions
			return null;
		}

		$aMasterFieldDependencies = static::GetMasterFieldsListForDisplayCondition($sMasterFieldCode, $aDisplayConditions,
			$aBrowsedFieldsCodes);
		if (is_null($aMasterFieldDependencies)) {
			if ($bIsTopOfCallStack) {
				return array();
			}

			return null;
		}

		$aMasterFields[] = $sMasterFieldCode;

		return array_merge($aMasterFields, $aMasterFieldDependencies);
	}

	/**
	 * @param string $sFieldCode field code to evaluate
	 * @param array $aDisplayConditions TemplateField code as array key, display_condition as array value
	 * @param array $aValues TemplateField code as array key, TemplateField value as array value
	 * @param array $aCachedDisplayConditionsResults contains a cache of evaluation results :
	 *    TemplateField code as array key, display_condition evaluation as array value
	 *    evaluation is a ?boolean, null if condition is currently under evaluation in the stack to prevent loops : if so returns true
	 *
	 * @return bool|null true if display_condition is OK, false otherwise.
	 *         A display condition can depend on a field having itself a display condition, etc : so this method does recursive calls !
	 *         This method prevents circular conditions, for example when we have display conditions like :
	 *                 ['A' => "B='Yes'", 'B' => "A='No'"]
	 *         In such case, all fields are displayed, and the method returns null to exit to call stack top level
	 */
	public static function IsFieldDisplayConditionOk($sFieldCode, $aDisplayConditions, $aValues, &$aCachedDisplayConditionsResults = null)
	{
		if (!array_key_exists($sFieldCode, $aValues)) {
			// will occur in preview mode (call from \Template::DisplayBareRelations)
			$aValues[$sFieldCode] = '';
		}

		if (is_null($aCachedDisplayConditionsResults)) {
			$aCachedDisplayConditionsResults = array();
		}

		if (array_key_exists($sFieldCode, $aCachedDisplayConditionsResults)) {
			if (is_null($aCachedDisplayConditionsResults[$sFieldCode])) {
				$aCachedDisplayConditionsResults[$sFieldCode] = true;

				return true;
			}

			return $aCachedDisplayConditionsResults[$sFieldCode];
		}
		$aCachedDisplayConditionsResults[$sFieldCode] = null;

		$sDisplayCondition = (array_key_exists($sFieldCode, $aDisplayConditions))
			? $aDisplayConditions[$sFieldCode]
			: '';
		if (empty($sDisplayCondition)) {
			$aCachedDisplayConditionsResults[$sFieldCode] = true;

			return true;
		}

		$sDisplayConditionRegExp = '/'.TemplateField::DISPLAY_CONDITION_VALIDATION_PATTERN.'/';
		preg_match($sDisplayConditionRegExp, $sDisplayCondition, $aMatches, PREG_OFFSET_CAPTURE);
		if (count($aMatches) < 4) {
			$aCachedDisplayConditionsResults[$sFieldCode] = true;

			return true;
		}

		$sMasterTemplateFieldCode = $aMatches[1][0];
		if ($sMasterTemplateFieldCode === $sFieldCode) {
			$aCachedDisplayConditionsResults[$sFieldCode] = true;

			return true;
		}

		if ((array_key_exists($sMasterTemplateFieldCode, $aCachedDisplayConditionsResults))
			&& is_null($aCachedDisplayConditionsResults[$sMasterTemplateFieldCode])) {
			$aCachedDisplayConditionsResults[$sMasterTemplateFieldCode] = true;

			// we need to exit the call stack and return true on first level !
			return null;
		}

		$bMasterTemplateFieldOk = static::IsFieldDisplayConditionOk(
			$sMasterTemplateFieldCode,
			$aDisplayConditions,
			$aValues,
			$aCachedDisplayConditionsResults
		);
		if (is_null($bMasterTemplateFieldOk)) {
			$aCachedDisplayConditionsResults[$bMasterTemplateFieldOk] = true;

			return true;
		}
		if (!$bMasterTemplateFieldOk) {
			$aCachedDisplayConditionsResults[$sFieldCode] = false;

			return false;
		}

		$sDisplayConditionOperator = $aMatches[2][0];
		$sDisplayConditionValue = $aMatches[3][0];
		if (!array_key_exists($sMasterTemplateFieldCode, $aValues)) {
			// will occur in preview mode (call from \Template::DisplayBareRelations)
			$aValues[$sMasterTemplateFieldCode] = '';
		}
		$sCurrentValue = $aValues[$sMasterTemplateFieldCode];
		if (is_array($sCurrentValue)) {
			// When called from BuildForm (during form refresh) : fields of type List get theur values in an array
			// When called with values get with \Combodo\iTop\Form\Form::GetCurrentValues, we get a string for all fields
			$sCurrentValue = $sCurrentValue[0];
		}
		if ($sDisplayConditionOperator === '=') {
			$bConditionEvaluation = ($sCurrentValue == $sDisplayConditionValue);
			$aCachedDisplayConditionsResults[$sFieldCode] = $bConditionEvaluation;

			return $bConditionEvaluation;
		}

		if ($sDisplayConditionOperator === '!=') {
			$bConditionEvaluation = ($sCurrentValue != $sDisplayConditionValue);
			$aCachedDisplayConditionsResults[$sFieldCode] = $bConditionEvaluation;

			return $bConditionEvaluation;
		}

		$aCachedDisplayConditionsResults[$sFieldCode] = true;

		return $aCachedDisplayConditionsResults[$sFieldCode];
	}

	/**
	 * Overridable in derived classes
	 *
	 * @param $aFieldData Field spec as returned by ToArray()
	 *
	 * @return bool
	 */
	protected static function IsVisibleToCurrentUser($aFieldData)
	{
		$bRet = true;
		switch ($aFieldData['input_type']) {
			case 'hidden':
				$bRet = true;
				$sProfiles = utils::GetConfig()->GetModuleSetting('templates-base', 'hidden_fields_profiles', 'Portal user');
				foreach (explode(',', $sProfiles) as $sProfile) {
					if (UserRights::HasProfile(trim($sProfile))) {
						$bRet = false;
						break;
					}
				}
				break;
		}

		return $bRet;
	}

	/**
	 * API for checking the visibility of a field
	 *
	 * @param array $aTemplateData Template spec as returned by {@see Template::ToArray()}
	 * @param string $sFieldCode
	 *
	 * @return bool
	 */
	final public static function IsFieldVisibleToCurrentUser($aTemplateData, $sFieldCode)
	{
		/** @var \Template $sTemplateClass */
		$sTemplateClass = $aTemplateData['class'];
		$aFieldData = $aTemplateData['fields'][$sFieldCode];

		return $sTemplateClass::IsVisibleToCurrentUser($aFieldData);
	}

	/**
	 * @param DBObject|null $oHostObject
	 * @param $aFieldData Field spec as returned by TemplateField::ToArray()
	 * @param \Combodo\iTop\Form\Form $oForm
	 *
	 * @return \Combodo\iTop\Form\Field\Field
	 */
	protected static function MakeFormField($oHostObject, $aFieldData, \Combodo\iTop\Form\Form $oForm) {
		switch ($aFieldData['input_type']) {
			case 'date':
				$oField = new Combodo\iTop\Form\Field\DateTimeField($aFieldData['code']);
				$oField->SetPHPDateTimeFormat((string)AttributeDate::GetFormat());
				$oField->SetJSDateTimeFormat(AttributeDate::GetFormat()->ToMomentJS());
				$oField->AddValidator(self::GetFieldRegExpValidatorInstance(AttributeDate::GetFormat()->ToRegExpr()));
				$oField->SetLabel($aFieldData['label']);
				$oField->SetMandatory($aFieldData['mandatory'] == 'yes');
				$oField->SetCurrentValue(AttributeDate::GetFormat()->Format($aFieldData['initial_value']));
				$oField->SetDateOnly(true);
				break;

			case 'date_and_time':
				$oField = new Combodo\iTop\Form\Field\DateTimeField($aFieldData['code']);
				$oField->SetPHPDateTimeFormat((string)AttributeDateTime::GetFormat());
				$oField->SetJSDateTimeFormat(AttributeDateTime::GetFormat()->ToMomentJS());
				$oField->AddValidator(self::GetFieldRegExpValidatorInstance(AttributeDateTime::GetFormat()->ToRegExpr()));
				$oField->SetLabel($aFieldData['label']);
				$oField->SetMandatory($aFieldData['mandatory'] == 'yes');
				$oField->SetCurrentValue(AttributeDateTime::GetFormat()->Format($aFieldData['initial_value']));
				break;

			case 'duration':
				$oField = new Combodo\iTop\Form\Field\DurationField($aFieldData['code']);
				$oField->SetLabel($aFieldData['label']);
				$oField->SetMandatory($aFieldData['mandatory'] == 'yes');
				$oField->SetCurrentValue($aFieldData['initial_value']);
				break;

			case 'text_area':
				$oField = new Combodo\iTop\Form\Field\TextAreaField($aFieldData['code']);
				$oField->SetFormat(Combodo\iTop\Form\Field\TextAreaField::ENUM_FORMAT_TEXT);
				$oField->SetLabel($aFieldData['label']);
				$oField->SetMandatory($aFieldData['mandatory'] == 'yes');
				$oField->SetCurrentValue($aFieldData['initial_value']);
				$oField->AddValidator(self::GetFieldRegExpValidatorInstance($aFieldData['format']));
				break;

			case 'drop_down_list':
			case 'radio_buttons':
				$sInputType = $aFieldData['input_type']; // could be changed to autocomplete...

				switch($sInputType)
				{
					case 'radio_buttons':
					case 'drop_down_list':
					default:
						$oSearch = $aFieldData['_oSearch_'];
						if ($oSearch !== null)
						{
							$oField = new Combodo\iTop\Form\Field\SelectObjectField($aFieldData['code'], function($oThis) use($aFieldData, $oForm, $oHostObject) {
								// Prepare arguments out of the current values (already validated)
								$aTemplateArgs = $aFieldData['_aTemplateArgs_'];
								$aQueryArgs = array();
								foreach ($aTemplateArgs as $sCode => $foo)
								{
									$value = $oForm->GetField($sCode)->GetCurrentValue();
									if (is_null($value)) $value = 0; // Otherwise the parameter is evaluated to NULL, which is NOT valid in OQL
									$aQueryArgs['template->'.$sCode] = $value;
								}
								$aQueryArgs['this'] = $oHostObject;
								$oSearch = $aFieldData['_oSearch_'];
								$oSearch->SetInternalParams($aQueryArgs);
								$oThis->SetSearch($oSearch);
							});
							$oField->SetLabel($aFieldData['label']);
							$oField->SetMandatory($aFieldData['mandatory'] == 'yes');
							$oField->SetCurrentValue($aFieldData['initial_value']);
							if ($sInputType == 'radio_buttons')
							{
								$oField->SetControlType(Combodo\iTop\Form\Field\SelectObjectField::CONTROL_RADIO_VERTICAL);
							}
                            $iMaxComboLength = (array_key_exists('max_combo_length', $aFieldData) && !is_null($aFieldData['max_combo_length']))
                                ? $aFieldData['max_combo_length']
                                : MetaModel::GetConfig()->Get('max_combo_length');
							$oField->SetMaximumComboLength($iMaxComboLength);
                            $oField->SetMinAutoCompleteChars(MetaModel::GetConfig()->Get('min_autocomplete_chars'));
						}
						elseif ($sInputType == 'radio_buttons')
						{
							$oField = new Combodo\iTop\Form\Field\RadioField($aFieldData['code']);
							$oField->SetLabel($aFieldData['label']);
							$oField->SetMandatory($aFieldData['mandatory'] == 'yes');
							$oField->SetCurrentValue($aFieldData['initial_value']);

							$aChoices = array();
							foreach(explode(',',$aFieldData['values']) as $sVal)
							{
								$aChoices[$sVal] = $sVal;
							}
							$oField->SetChoices($aChoices);
						}
						else
						{
							$oField = new Combodo\iTop\Form\Field\SelectField($aFieldData['code']);
							$oField->SetLabel($aFieldData['label']);
							$oField->SetMandatory($aFieldData['mandatory'] == 'yes');
							$oField->SetCurrentValue($aFieldData['initial_value']);

							$aChoices = array();
							foreach(explode(',',$aFieldData['values']) as $sVal)
							{
								$aChoices[$sVal] = $sVal;
							}
							$oField->SetChoices($aChoices);
						}
				}
				break;

			case 'read_only':
			case 'hidden':
				$oField = new Combodo\iTop\Form\Field\TextAreaField($aFieldData['code']);
				$oField->SetFormat(Combodo\iTop\Form\Field\TextAreaField::ENUM_FORMAT_TEXT);
				$oField->SetLabel($aFieldData['label']);
				$oField->SetReadOnly(true);
				$oField->SetCurrentValue($aFieldData['initial_value']);
				break;

			case 'text':
			default:
				$oField = new Combodo\iTop\Form\Field\StringField($aFieldData['code']);
				$oField->SetLabel($aFieldData['label']);
				$oField->SetMandatory($aFieldData['mandatory'] == 'yes');
			$oField->SetCurrentValue($aFieldData['initial_value']);
			$oField->AddValidator(self::GetFieldRegExpValidatorInstance($aFieldData['format']));
				break;
		} // switch(input_type)

		// Metadata
		// Note: Protection for iTop 2.6 and earlier
		if (is_callable(array($oField, 'AddMetadata')))
		{
			$oField->AddMetadata('attribute-code', $aFieldData['code']);
			$oField->AddMetadata('attribute-type', null);
			$oField->AddMetadata('attribute-label', $aFieldData['label']);
			$oField->AddMetadata('input-type', $aFieldData['input_type']);
			if ($aFieldData['input_type'] !== 'text_area') {
				$oField->AddMetadata('value-raw', $aFieldData['initial_value']);
			}
		}

		return $oField;
	}

	/**
	 * @since N°6414 {@see \Combodo\iTop\Form\Field\Field} validator refactoring in iTop 3.1.0
	 */
	final protected static function GetFieldRegExpValidatorInstance($sRegExp) {
		if (version_compare(ITOP_DESIGN_LATEST_VERSION, 3.1) < 0) {
			return new Validator($sRegExp);
		}

		return new CustomRegexpValidator($sRegExp);
	}

	/**
	 * Returns an HTML representation of the normalized $value.
	 *
	 * Note: Normalized means that date for example must be in SQL format instead of the conf-param format
	 *
	 * @param array $aFieldData Field spec as returned by TemplateField::ToArray()
	 * @param mixed $value The normalized value
	 *
	 * @return string
	 */
	static public function MakeHTMLValue($aFieldData, $value)
	{
		$sRet = $value;
		$sInputType = $aFieldData['input_type'];

		switch ($sInputType)
		{
			case 'drop_down_list':
			case 'radio_buttons':
				break;

			case 'text_area':
			case 'hidden':
				$sRet = '<div>'.utils::TextToHtml($value).'</div>';
				break;

			case 'read_only':
				$value = Str::pure2html($value);
				$sRet = AttributeText::RenderWikiHtml($value);
				break;

			case 'duration':
				$sRet = htmlentities(AttributeDuration::FormatDuration($value), ENT_QUOTES, 'UTF-8');
				break;

			case 'date':
			case 'date_and_time':
				$sAttDefClass = ($sInputType === 'date') ? 'AttributeDate' : 'AttributeDateTime';
				$sRet = htmlentities($sAttDefClass::GetFormat()->format($value), ENT_QUOTES, 'UTF-8');
				break;

			case 'text':
			default:
				$sRet = htmlentities($value, ENT_QUOTES, 'UTF-8');
				break;
		} // switch(input_type)
		return $sRet;
	}

	/**
	 * Returns an HTML representation of the normalized $value.
	 *
	 * Note: Normalized means that date for example must be in SQL format instead of the conf-param format
	 *
	 * @param array $aFieldData Field spec as returned by TemplateField::ToArray()
	 * @param mixed $value The normalized value
	 *
	 * @return string
	 */
	static public function MakePlainTextValue($aFieldData, $value)
	{
		$sRet = $value;
		$sInputType = $aFieldData['input_type'];

		switch ($sInputType)
		{
			case 'duration':
				$sRet = AttributeDuration::FormatDuration($value);
				break;

			case 'date':
			case 'date_and_time':
				$sAttDefClass = ($sInputType === 'date') ? 'AttributeDate' : 'AttributeDateTime';
				$sRet = $sAttDefClass::GetFormat()->format($value);
				break;
		} // switch(input_type)
		return $sRet;
	}

	/**
	 *	Get the form data as an array
	 * Must be preserved for the legacy User Portal
	 */
	public function GetPostedValuesAsArray($oObject)
	{
		$aValues = array();

		$sFormPrefix = '';

		$oFieldSearch = DBObjectSearch::FromOQL('SELECT TemplateField WHERE template_id = :template_id');
		$oFieldSearch->AllowAllData();
		$oFieldSet = new DBObjectSet($oFieldSearch, array('order' => true), array('template_id' => $this->GetKey()));
		while($oField = $oFieldSet->Fetch())
		{
			$sAttCode = $oField->GetKey();
			$value = utils::ReadPostedParam("tpl_{$sFormPrefix}{$sAttCode}", null, 'raw_data');
			if (!is_null($value))
			{
				$aValues[$oField->GetKey()] = array(
					'code' => $oField->Get('code'),
					'label' => $oField->Get('label'),
					'input_type' => $oField->Get('input_type'),
					'value' => $value
				);

				if ($oField->Get('input_type') == 'duration')
				{
					$iDurationSec = $value['d']*86400 + $value['h']*3600 + $value['m']*60 + $value['s'];
					$aValues[$oField->GetKey()]['value'] = AttributeDuration::FormatDuration($iDurationSec);
				}

				$sValues = $oField->Get('values');
				if (strlen($sValues) > 0)
				{
					try
					{
						$aAllowedValues = array();
						$oSearch = DBObjectSearch::FromOQL($sValues);
						// An OQL has been given, the value is in fact an object id
						// let's store the object friendlyname and metadata about the object
						if ($value == '')
						{
							$aValues[$oField->GetKey()]['value'] = '';
							$aValues[$oField->GetKey()]['value_obj_key'] = 0;
							$aValues[$oField->GetKey()]['value_obj_class'] = $oSearch->GetClass();
						}
						else
						{
							$oSelectedObject = MetaModel::GetObject($oSearch->GetClass(), $value);
							$aValues[$oField->GetKey()]['value'] = $oSelectedObject->Get('friendlyname');
							$aValues[$oField->GetKey()]['value_obj_key'] = $value;
							$aValues[$oField->GetKey()]['value_obj_class'] = get_class($oSelectedObject);
						}
					}
					catch(Exception $e)
					{
						// A CSV list has been given, keep it as is
					}
				}
			}
		}

		return $aValues;
	}

	/**
	 * Helper to dump the template data as text
	 * Must be preserved for the legacy User Portal
	 */
	public function GetPostedValuesAsText($oObject)
	{
		$aValues = $this->GetPostedValuesAsArray($oObject);
		$aLines = array();
		foreach ($aValues as $sFieldId => $aFieldData)
		{
			$sLabel = htmlentities($aFieldData['label'], ENT_QUOTES, 'utf-8');
			$sValue = utils::TextToHtml($aFieldData['value']);
			$aLines[] = "<b>$sLabel</b>&nbsp;: $sValue";
		}

		$sRet = implode("<br>\n", $aLines);
		return $sRet;
	}

	/**
	 * Record the template data in a structured way
	 * Must be preserved for the legacy User Portal
	 */
	public function RecordExtraDataFromPostedForm($oObject)
	{
		$aValues = $this->GetPostedValuesAsArray($oObject);

		$oExtraData = new TemplateExtraData();
		$oExtraData->Set('template_id', $this->GetKey());
		$oExtraData->Set('data', serialize($aValues));
		$oExtraData->Set('obj_class', get_class($oObject));
		$oExtraData->Set('obj_key', $oObject->GetKey());
		$oExtraData->DBInsert();
	}

	/**
	 * Display the form preview tab
	 *
	 */
	function DisplayBareRelations(WebPage $oPage, $bEditMode = false)
	{
		parent::DisplayBareRelations($oPage, $bEditMode);
		if (!$bEditMode)
		{
			$oPage->SetCurrentTab(Dict::S('Templates:PreviewTab:Title'));
			try
			{
				if (version_compare(ITOP_DESIGN_LATEST_VERSION , '3.0') >= 0) {
					$oFieldSet = FieldSetUIBlockFactory::MakeStandard(Dict::S('Templates:PreviewTab:FormFields'));
					$oPage->AddSubBlock($oFieldSet);

					$iId = 'fake_tpl';
					$sReloadSpan = "<span class=\"field_status\" id=\"fstatus_{$iId}\"></span>";
					$oBlockConsoleForm = UIContentBlockUIBlockFactory::MakeStandard($iId.'_console_form', ['ibo-is-visible']);
					$oFieldSet->AddSubBlock($oBlockConsoleForm);
					$oBlockFieldSet = UIContentBlockUIBlockFactory::MakeStandard($iId.'_field_set', ['ibo-is-visible']);
					$oBlockConsoleForm->AddSubBlock($oBlockFieldSet);
					$oFieldSet->AddSubBlock(new Html('<div>'.$sReloadSpan.'</div>')); // No validation span for this one: it does handle its own validation!

				}
				else {
					$oPage->add('<fieldset>');
					$oPage->add('<legend>'.Dict::S('Templates:PreviewTab:FormFields').'</legend>');

					$iId = 'fake_tpl';
					$sReloadSpan = "<span class=\"field_status\" id=\"fstatus_{$iId}\"></span>";
					$oPage->add('<table>');
					$oPage->add('<tr>');
					$oPage->add('<td>');
					$oPage->add('<div id="'.$iId.'_console_form">');
					$oPage->add('<div id="'.$iId.'_field_set">');
					$oPage->add('</div>');
					$oPage->add('</div>');
					$oPage->add('</td>');
					$oPage->add('<td>'.$sReloadSpan.'</td>'); // No validation span for this one: it does handle its own validation!
					$oPage->add('</tr>');
					$oPage->add('</table>');

					$oPage->add('</fieldset>');
				}
				$aTemplateData = $this->ToArray();
				$oForm = new \Combodo\iTop\Form\Form('faker');
				$this->PopulateUserDataForm(MetaModel::NewObject($this->GetTargetClass()), $oForm, $aTemplateData, array());
				$oForm->Finalize();
				$oRenderer = new \Combodo\iTop\Renderer\Console\ConsoleFormRenderer($oForm);
				$aRenderRes = $oRenderer->Render();

				$aFormHandlerOptions = array(
					'template_id' => $this->GetKey(),
				);
				$sFormHandlerOptions = json_encode($aFormHandlerOptions);
				$aFieldSetOptions = array(
					'field_identifier_attr' => 'data-field-id', // convention: fields are rendered into a div and are identified by this attribute
					'fields_list' => $aRenderRes,
					'fields_impacts' => $oForm->GetFieldsImpacts(),
					'form_path' => $oForm->GetId()
				);
				$sFieldSetOptions = json_encode($aFieldSetOptions);

				// Add JS files
				$aCoreJsFilesRelPaths = [
					"js/form_handler.js",
					"js/field_set.js",
					"js/form_field.js",
					"js/subform_field.js",
				];
				if (version_compare(ITOP_DESIGN_LATEST_VERSION, "3.2", ">=")) {
					// Core files
					array_map(function($sJsFileRelPath) use ($oPage) {
						$oPage->LinkScriptFromAppRoot($sJsFileRelPath);
					}, $aCoreJsFilesRelPaths);

					// Module files
					$oPage->LinkScriptFromModule("templates-base/template_form_handler.js");
				} else {
					// Core files
					array_map(function($sJsFileRelPath) use ($oPage) {
						$oPage->add_linked_script(utils::GetAbsoluteUrlAppRoot().$sJsFileRelPath);
					}, $aCoreJsFilesRelPaths);

					// Module files
					$oPage->add_linked_script(utils::GetAbsoluteUrlModulesRoot()."templates-base/template_form_handler.js");
				}

				// Add JS inline scripts
				$oPage->add_ready_script("$('#{$iId}_console_form').template_form_handler($sFormHandlerOptions);");
				$oPage->add_ready_script("$('#{$iId}_field_set').field_set($sFieldSetOptions);");
				$oPage->add_ready_script("$('#{$iId}_console_form').template_form_handler('alignColumns');");
				$oPage->add_ready_script("$('#{$iId}_console_form').template_form_handler('option', 'field_set', $('#{$iId}_field_set'));");
				// field_change must be processed to refresh the hidden value at anytime
				$oPage->add_ready_script("$('#{$iId}_console_form').bind('value_change', function() { $('#{$iId}').val(JSON.stringify($('#{$iId}_field_set').triggerHandler('get_current_values'))); });");
				// update_value is triggered when preparing the wizard helper object for ajax calls
				$oPage->add_ready_script("$('#{$iId}').bind('update_value', function() { $(this).val(JSON.stringify($('#{$iId}_field_set').triggerHandler('get_current_values'))); });");
				// validate is triggered by CheckFields, on all the input fields, once at page init and once before submitting the form
				$oPage->add_ready_script("$('#{$iId}').bind('validate', function(evt, sFormId) { return ValidateCustomFields('$iId', sFormId) } );"); // Custom validation function


				$oPage->add_ready_script(
					<<<EOF
								// Starts the validation when the page is ready
			CheckFields('tpl_preview', false);
EOF
				);
			}
			catch (Exception $e)
			{
				$oPage->add('ERROR: '.$e->getMessage());
			}
		}
	}
}

class TemplateFieldValue extends DBObject
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "",
			"key_type" => "autoincrement",
			"name_attcode" => array('obj_class', 'obj_key', 'template_id', 'template_name', 'field_code'),
			"state_attcode" => "",
			"reconc_keys" => array('obj_class', 'obj_key', 'template_id', 'template_name', 'field_code'),
			"db_table" => "tpl_field_value",
			"db_key_field" => "id",
			"db_finalclass_field" => "",
			"display_template" => "",
			'indexes' => array(
				array('obj_class', 'obj_key', 'field_value'),
				array('template_id'),
			)
		);
		MetaModel::Init_Params($aParams);
		MetaModel::Init_InheritAttributes();

		MetaModel::Init_AddAttribute(new AttributeString("obj_class", array("allowed_values"=>null, "sql"=>"obj_class", "default_value"=>"", "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeObjectKey("obj_key", array("class_attcode"=>'obj_class', "allowed_values"=>null, "sql"=>"obj_key", "default_value"=>"", "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeInteger("template_id", array("allowed_values"=>null, "sql"=>"template_id", "default_value"=>null, "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("template_name", array("allowed_values"=>null, "sql"=>"template_name", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("field_code", array("allowed_values"=>null, "sql"=>"field_code", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("field_value", array("allowed_values"=>null, "sql"=>"field_value", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("field_type", array("allowed_values"=>null, "sql"=>"field_type", "default_value"=>"", "is_null_allowed"=>true, "depends_on"=>array())));

		MetaModel::Init_SetZListItems('details', array('obj_class', 'obj_key', 'template_id', 'template_name', 'field_code', 'field_value', 'field_type'));
		MetaModel::Init_SetZListItems('advanced_search', array('template_id', 'template_name', 'field_code', 'field_value', 'field_type'));
		MetaModel::Init_SetZListItems('standard_search', array('obj_class', 'obj_key', 'template_id', 'template_name', 'field_code', 'field_value', 'field_type'));
		MetaModel::Init_SetZListItems('list', array('obj_class', 'obj_key', 'template_id', 'template_name', 'field_code', 'field_value', 'field_type'));
	}

	public function Set($sAttCode, $value)
	{
		if ('field_value' == $sAttCode)
		{
			$oAttDef = MetaModel::GetAttributeDef(self::class, $sAttCode);
			$value = self::TruncateFieldValue($value, $oAttDef->GetMaxSize()-1);
		}

		return parent::Set($sAttCode, $value);
	}

    /**
     * this method is a workaround to iTop core limitation with multibytes strings
     * cf DBObject:CheckValue which uses PHP strlen instead of mb_strlen
     * To mitigate this limitation, we decrement our string using a multibyte compatible function (mb_subtr)
     * @see N°3248/ N°3242
     * @param $value
     * @param $iMaxSize
     * @return string
     */
	public static function TruncateFieldValue($value, $iMaxSize){
		// N°6540 - Protection against null value for PHP 8.1.
		// Note that we can't use coalesce `??` operator as this needs to be compatible with PHP 5.6
        $strlen = is_null($value) ? 0 : strlen($value);
        if ($strlen <= $iMaxSize){
            return $value;
        }

        //We decrement initial db value until the result of mb_substr produces a result that strlen sees as less than 255 chars.
        //This is not straight-forward since each multiby char is seen as at least two chars, so whe have to do this in a loop until the result is ok.

        $sTruncateDbValue = $value;
        $iMaxLen  = $iMaxSize;
        do{
            $sTruncateDbValue = mb_substr($sTruncateDbValue, 0, $iMaxLen);
            $iCurentStrlen = strlen($sTruncateDbValue);
            $iNextMaxSize = ($iCurentStrlen - $iMaxSize) / 2;
            $iMaxLen = ($iNextMaxSize<1) ? $iMaxLen-1 : $iNextMaxSize;
        }while ($iCurentStrlen >$iMaxSize);

        return $sTruncateDbValue;
    }

}

class TemplateFieldValueLnk extends TemplateFieldValue
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "",
			"key_type" => "autoincrement",
			"name_attcode" => array('obj_class', 'obj_key', 'template_id', 'template_name', 'field_code'),
			"state_attcode" => "",
			"reconc_keys" => array('obj_class', 'obj_key', 'template_id', 'template_name', 'field_code'),
			"db_table" => "tpl_field_value_lnk",
			"db_key_field" => "id",
			"db_finalclass_field" => "",
			"display_template" => "",
			'indexes' => array(
				array('field_target_class', 'field_target_key'),
			)
		);
		MetaModel::Init_Params($aParams);
		MetaModel::Init_InheritAttributes();

		MetaModel::Init_AddAttribute(new AttributeString("field_target_class", array("allowed_values"=>null, "sql"=>"field_target_class", "default_value"=>"", "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeObjectKey("field_target_key", array("class_attcode"=>'field_target_class', "allowed_values"=>null, "sql"=>"field_target_key", "default_value"=>"", "is_null_allowed"=>false, "depends_on"=>array())));

		MetaModel::Init_SetZListItems('details', array('obj_class', 'obj_key', 'template_id', 'template_name', 'field_code', 'field_value', 'field_target_class', 'field_target_key', 'field_type'));
		MetaModel::Init_SetZListItems('advanced_search', array('template_id', 'template_name', 'field_code', 'field_value', 'field_target_class'));
		MetaModel::Init_SetZListItems('standard_search', array('obj_class', 'obj_key', 'template_id', 'template_name', 'field_code', 'field_value', 'field_target_class', 'field_type'));
		MetaModel::Init_SetZListItems('list', array('obj_class', 'obj_key', 'template_id', 'template_name', 'field_code', 'field_value', 'field_target_class', 'field_target_key'));
	}
}

class TemplateField extends cmdbAbstractObject
{
	const DISPLAY_CONDITION_VALIDATION_PATTERN = '^:template->([A-Za-z0-9_]+)(!?=)\'(.*)\'$';

	public static function Init()
	{
		$aParams = array
		(
			"category" => "bizmodel,searchable,servicemgmt",
			"key_type" => "autoincrement",
			"name_attcode" => "code",
			"state_attcode" => "",
			"reconc_keys" => array("template_name", "code"),
			"db_table" => "tpl_field",
			"db_key_field" => "id",
			"db_finalclass_field" => "",
			"display_template" => "",
			'order_by_default' => array('order' => true),
		);
		MetaModel::Init_Params($aParams);
		MetaModel::Init_InheritAttributes();

		MetaModel::Init_AddAttribute(new AttributeExternalKey("template_id", array(
			"targetclass" => "Template",
			"jointype" => null,
			"allowed_values" => null,
			"sql" => "template_id",
			"is_null_allowed" => false,
			"on_target_delete" => DEL_SILENT,
			"depends_on" => array(),
		)));
		MetaModel::Init_AddAttribute(new AttributeExternalField("template_name", array(
			"allowed_values" => null,
			"extkey_attcode" => 'template_id',
			"target_attcode" => 'name',
			"always_load_in_tables" => false,
		)));
		MetaModel::Init_AddAttribute(new AttributeString("code", array(
			"allowed_values" => null,
			"sql" => "code",
			"default_value" => "",
			"is_null_allowed" => false,
			"depends_on" => array(),
			'validation_pattern' => '^[A-Za-z0-9_]*$',
		)));
		MetaModel::Init_AddAttribute(new AttributeString("label",
			array("allowed_values" => null, "sql" => "label", "default_value" => "", "is_null_allowed" => false, "depends_on" => array())));
		MetaModel::Init_AddAttribute(new AttributeInteger("order",
			array("allowed_values" => null, "sql" => "order", "default_value" => 0, "is_null_allowed" => false, "depends_on" => array())));
		MetaModel::Init_AddAttribute(new AttributeEnum("mandatory", array(
			"allowed_values" => new ValueSetEnum('yes,no'),
			"sql" => "mandatory",
			"default_value" => "no",
			"is_null_allowed" => false,
			"depends_on" => array(),
		)));
		MetaModel::Init_AddAttribute(new AttributeString("display_condition", array(
			"allowed_values" => null,
			"sql" => "display_condition",
			"default_value" => "",
			"is_null_allowed" => true,
			"depends_on" => array(),
			'validation_pattern' => self::DISPLAY_CONDITION_VALIDATION_PATTERN,
		)));
		MetaModel::Init_AddAttribute(new AttributeEnum("input_type", array(
			"allowed_values" => new ValueSetEnum('text,text_area,drop_down_list,radio_buttons,date,date_and_time,duration,read_only,hidden'),
			"sql" => "input_type",
			"default_value" => "text",
			"is_null_allowed" => false,
			"depends_on" => array(),
		)));
		MetaModel::Init_AddAttribute(new AttributeText("values",
			array("allowed_values" => null, "sql" => "values", "default_value" => "", "is_null_allowed" => true, "depends_on" => array())));
		MetaModel::Init_AddAttribute(new AttributeText("initial_value", array(
			"allowed_values" => null,
			"sql" => "initial_value",
			"default_value" => "",
			"is_null_allowed" => true,
			"depends_on" => array(),
		)));
		MetaModel::Init_AddAttribute(new AttributeString("format",
			array("allowed_values" => null, "sql" => "format", "default_value" => "", "is_null_allowed" => true, "depends_on" => array())));
        MetaModel::Init_AddAttribute(new AttributeInteger("max_combo_length",
            array("allowed_values" => null, "sql" => "max_combo_length", "default_value" => "", "is_null_allowed" => true, "depends_on" => array())));

		MetaModel::Init_SetZListItems('details', array(
			'template_id',
			'code',
			'order',
			'label',
			'mandatory',
			'display_condition',
			'input_type',
			'values',
			'initial_value',
			'format',
            'max_combo_length',
		));
		MetaModel::Init_SetZListItems('advanced_search', array('template_id', 'code', 'label', 'mandatory', 'input_type'));
		MetaModel::Init_SetZListItems('standard_search', array('template_id', 'code', 'label', 'mandatory', 'input_type'));
		MetaModel::Init_SetZListItems('list', array('template_id', 'code', 'order', 'mandatory', 'input_type', 'display_condition'));
	}

	/**
	 * Make a serializable array out of the characteristics of the field
	 */
	public function ToArray()
	{
		$aRet = array(
			'class' => get_class($this),
			'id' => $this->GetKey(),
			'code' => $this->Get('code'),
			'label' => $this->Get('label'),
			'order' => $this->Get('order'),
			'mandatory' => $this->Get('mandatory'),
			'display_condition' => $this->Get('display_condition'),
			'input_type' => $this->Get('input_type'),
			'values' => $this->Get('values'),
			'initial_value' => $this->Get('initial_value'),
			'format' => $this->Get('format'),
            'max_combo_length' => $this->Get('max_combo_length'),
		);
		return $aRet;
	}

	/**
	 * Must be preserved for the legacy User Portal
	 *
	 * @param $oPage
	 * @param null $sClass
	 * @param string $sFormPrefix
	 * @return string
	 * @throws CoreException
	 * @throws DictExceptionMissingString
	 * @throws Exception
	 */
	public function GetFormElement($oPage, $sClass = null, $sFormPrefix = '')
	{
		$sAttCode = $this->GetKey();

		$value = $this->Get('initial_value');

		$sFieldPrefix = '';
		$sNameSuffix = '';
		$iId = 'tpl_'.$sAttCode;
		if (!empty($iId))
		{
			$iInputId = $iId;
		}
		else
		{
			$iInputId = $oPage->GetUniqueId();
		}

		$bMandatory = 'false';
		if ($this->Get('mandatory') == 'yes')
		{
			$bMandatory = 'true';
		}

		$sValidationField = "<span class=\"form_validation\" id=\"v_{$iId}\"></span>";
		$sHelpText = '';

		$aEventsList = array();
		switch($this->Get('input_type'))
		{
			case 'date':
				$aEventsList[] ='validate';
				$aEventsList[] ='keyup';
				$aEventsList[] ='change';
				$sPlaceholderValue = 'placeholder="'.htmlentities(AttributeDate::GetFormat()->ToPlaceholder(), ENT_QUOTES, 'UTF-8').'"';
				$sDisplayValue = AttributeDate::GetFormat()->Format($value);
				$sHTMLValue = "<input title=\"$sHelpText\" class=\"date-pick\" type=\"text\" size=\"12\" $sPlaceholderValue name=\"tpl_{$sFieldPrefix}{$sAttCode}{$sNameSuffix}\" value=\"".htmlentities($value, ENT_QUOTES, 'UTF-8')."\" id=\"$iId\"/>&nbsp;{$sValidationField}";
				break;

			case 'date_and_time':
				$aEventsList[] ='validate';
				$aEventsList[] ='keyup';
				$aEventsList[] ='change';
				$sPlaceholderValue = 'placeholder="'.htmlentities(AttributeDateTime::GetFormat()->ToPlaceholder(), ENT_QUOTES, 'UTF-8').'"';
				$sDisplayValue = AttributeDateTime::GetFormat()->Format($value);
				$sHTMLValue = "<input title=\"$sHelpText\" class=\"datetime-pick\" type=\"text\" size=\"15\" $sPlaceholderValue name=\"tpl_{$sFieldPrefix}{$sAttCode}{$sNameSuffix}\" value=\"".htmlentities($sDisplayValue, ENT_QUOTES, 'UTF-8')."\" id=\"$iId\"/>&nbsp;{$sValidationField}";
				break;

			case 'duration':
				$aEventsList[] ='validate';
				$aEventsList[] ='change';
				$oPage->add_ready_script("$('#{$iId}_d').bind('keyup change', function(evt, sFormId) { return UpdateDuration('$iId'); });");
				$oPage->add_ready_script("$('#{$iId}_h').bind('keyup change', function(evt, sFormId) { return UpdateDuration('$iId'); });");
				$oPage->add_ready_script("$('#{$iId}_m').bind('keyup change', function(evt, sFormId) { return UpdateDuration('$iId'); });");
				$oPage->add_ready_script("$('#{$iId}_s').bind('keyup change', function(evt, sFormId) { return UpdateDuration('$iId'); });");
				$aVal = AttributeDuration::SplitDuration($value);
				$sDays = "<input title=\"$sHelpText\" type=\"text\" size=\"3\" name=\"tpl_{$sFieldPrefix}{$sAttCode}[d]{$sNameSuffix}\" value=\"{$aVal['days']}\" id=\"{$iId}_d\"/>";
				$sHours = "<input title=\"$sHelpText\" type=\"text\" size=\"2\" name=\"tpl_{$sFieldPrefix}{$sAttCode}[h]{$sNameSuffix}\" value=\"{$aVal['hours']}\" id=\"{$iId}_h\"/>";
				$sMinutes = "<input title=\"$sHelpText\" type=\"text\" size=\"2\" name=\"tpl_{$sFieldPrefix}{$sAttCode}[m]{$sNameSuffix}\" value=\"{$aVal['minutes']}\" id=\"{$iId}_m\"/>";
				$sSeconds = "<input title=\"$sHelpText\" type=\"text\" size=\"2\" name=\"tpl_{$sFieldPrefix}{$sAttCode}[s]{$sNameSuffix}\" value=\"{$aVal['seconds']}\" id=\"{$iId}_s\"/>";
				$sHidden = "<input type=\"hidden\" id=\"{$iId}\" value=\"".htmlentities($value, ENT_QUOTES, 'UTF-8')."\"/>";
				$sHTMLValue = Dict::Format('UI:DurationForm_Days_Hours_Minutes_Seconds', $sDays, $sHours, $sMinutes, $sSeconds).$sHidden."&nbsp;".$sValidationField;
				$oPage->add_ready_script("$('#{$iId}').bind('update', function(evt, sFormId) { return ToggleDurationField('$iId'); });");
				break;

			case 'text_area':
				$aEventsList[] ='validate';
				$aEventsList[] ='keyup';
				$aEventsList[] ='change';
				$sEditValue = $value;
				$aStyles = array();
				$sStyle = '';
				if (count($aStyles) > 0)
				{
					$sStyle = 'style="'.implode('; ', $aStyles).'"';
				}
				$sAdditionalStuff = "";
				// Ok, the text area is drawn here
				if (version_compare(ITOP_DESIGN_LATEST_VERSION , '3.0') >= 0) {
					$sHTMLValue = "<textarea class=\"resizable\" title=\"$sHelpText\" name=\"tpl_{$sFieldPrefix}{$sAttCode}{$sNameSuffix}\" rows=\"8\" cols=\"40\" id=\"$iId\" $sStyle>".htmlentities($sEditValue, ENT_QUOTES, 'UTF-8')."</textarea>$sAdditionalStuff{$sValidationField}";
				} else {
					$sHTMLValue = "<table><tr><td><textarea class=\"resizable\" title=\"$sHelpText\" name=\"tpl_{$sFieldPrefix}{$sAttCode}{$sNameSuffix}\" rows=\"8\" cols=\"40\" id=\"$iId\" $sStyle>".htmlentities($sEditValue, ENT_QUOTES, 'UTF-8')."</textarea>$sAdditionalStuff</td><td>{$sValidationField}</td></tr></table>";
				}
				break;

			case 'drop_down_list':
			case 'radio_buttons':
				$aEventsList[] ='validate';
				$aEventsList[] ='change';

				$aAllowedValues = array();
				$sInputType = $this->Get('input_type');
				$sValues = $this->Get('values');
				if (strlen($sValues) > 0)
				{
					try
					{
						$oSearch = DBObjectSearch::FromOQL($sValues);
						foreach($oSearch->ToDataArray(array('id', 'friendlyname')) as $aRow)
						{
							$aAllowedValues[$aRow['id']] = $aRow['friendlyname'];
						}
						if (count($aAllowedValues) > MetaModel::GetConfig()->Get('max_combo_length'))
						{
							$sInputType = 'autocomplete';
						}
					}
					catch(Exception $e)
					{
						foreach(explode(',',$sValues) as $sVal)
						{
							$aAllowedValues[$sVal] = $sVal;
						}
					}
				}
				switch($sInputType)
				{
					case 'autocomplete':
						$oSearch = DBObjectSearch::FromOQL($sValues);
						$sTargetClass = $oSearch->GetClass();
						$oSearch->SetModifierProperty('UserRightsGetSelectFilter', 'bSearchMode', true);
						$oAllowedValues = new DBObjectSet($oSearch);

						$iMaxComboLength = MetaModel::GetConfig()->Get('max_combo_length');
						$aExtKeyParams = array();
						$aExtKeyParams['iFieldSize'] = 10;
						$aExtKeyParams['iMinChars'] = MetaModel::GetConfig()->Get('min_autocomplete_chars');
						$sFilterCode = '';
						$sFieldName = 'tpl_'.$sFieldPrefix.$sAttCode.$sNameSuffix;
						$sFormPrefix = '';
						$oWidget = new UIExtKeyWidget($sTargetClass, $iId, '', true);
						$aArgs = array();
						$sDisplayStyle = 'select';
						$sTitle = $this->Get('label');
						$sHTMLValue = $oWidget->Display($oPage, $iMaxComboLength, false /* $bAllowTargetCreation */, $sTitle, $oAllowedValues, '' /*$value*/, $iId, $bMandatory, $sFieldName, $sFormPrefix, $aArgs, null, $sDisplayStyle);
						break;

					case 'radio_buttons':
						$bVertical = true;
						$sHTMLValue = $oPage->GetRadioButtons($aAllowedValues, $value, $iId, "tpl_{$sFieldPrefix}{$sAttCode}{$sNameSuffix}", $bMandatory, $bVertical, $sValidationField);
						break;

					case 'drop_down_list':
					default:
						$sHTMLValue = "<select title=\"$sHelpText\" name=\"tpl_{$sFieldPrefix}{$sAttCode}{$sNameSuffix}\" id=\"$iId\">\n";
						$sHTMLValue .= "<option value=\"\">".Dict::S('UI:SelectOne')."</option>\n";
						foreach($aAllowedValues as $key => $display_value)
						{
							if ((count($aAllowedValues) == 1) && ($bMandatory == 'true') )
							{
								// When there is only once choice, select it by default
								$sSelected = ' selected';
							}
							else
							{
								$sSelected = ($value == $key) ? ' selected' : '';
							}
							$sHTMLValue .= "<option value=\"$key\"$sSelected>$display_value</option>\n";
						}
						$sHTMLValue .= "</select>&nbsp;{$sValidationField}\n";
				}

				break;

			case 'read_only':
				$sHTMLLabel = htmlentities($value, ENT_QUOTES, 'UTF-8');
				$sHTMLValue = "<input type=\"hidden\"name=\"tpl_{$sFieldPrefix}{$sAttCode}{$sNameSuffix}\" value=\"".htmlentities($value, ENT_QUOTES, 'UTF-8')."\" id=\"$iId\"/>".$sHTMLLabel;
				break;

			case 'hidden':
				$sHTMLValue = "<input type=\"hidden\"name=\"tpl_{$sFieldPrefix}{$sAttCode}{$sNameSuffix}\" value=\"".htmlentities($value, ENT_QUOTES, 'UTF-8')."\" id=\"$iId\"/>";
				break;

			case 'text':
			default:
				$aEventsList[] = 'validate';
				$iFieldSize = 255;
				$sHTMLValue = "<input title=\"$sHelpText\" type=\"text\" size=\"30\" maxlength=\"$iFieldSize\" name=\"tpl_{$sFieldPrefix}{$sAttCode}{$sNameSuffix}\" value=\"".htmlentities($value,
						ENT_QUOTES, 'UTF-8')."\" id=\"$iId\"/>&nbsp;{$sValidationField}";
				$aEventsList[] = 'keyup';
				$aEventsList[] = 'change';
				break;
		} // switch(input_type)
		$sPattern = $this->Get('format'); //'^([0-9]+)$';
		if (!empty($aEventsList)) {
			$sNullValue = '';
			if (!is_numeric($sNullValue)) {
				$sNullValue = "'$sNullValue'"; // Add quotes to turn this into a JS string if it's not a number
			}
			$oPage->add_ready_script("$('#$iId').bind('".implode(' ',
					$aEventsList)."', function(evt, sFormId) { return ValidateField('$iId', '$sPattern', $bMandatory, sFormId, $sNullValue) } );\n"); // Bind to a custom event: validate
		}

		return "<div>{$sHTMLValue}</div>";
	}

	public function DoCheckToWrite()
	{
		parent::DoCheckToWrite();
		$this->CheckDisplayConditionValidity();
	}

	protected function CheckDisplayConditionValidity()
	{
		$sDisplayCondition = $this->Get('display_condition');
		if (empty($sDisplayCondition)) {
			return;
		}

		// Check that display_condition syntax comply with OQL syntax
		try {
			Expression::FromOQL($sDisplayCondition);
		}
		catch (OQLException $e) {
			$this->m_aCheckIssues[] = Dict::S('Class:TemplateField/Error:InvalidDisplayConditionOql');;
		}

		// Error if display condition code is the field code itself !
		$sDisplayConditionRegExp = '/'.TemplateField::DISPLAY_CONDITION_VALIDATION_PATTERN.'/';
		preg_match($sDisplayConditionRegExp, $sDisplayCondition, $aMatches, PREG_OFFSET_CAPTURE);
		// $aMatches should contain correct indexes as we have already checked format using the validation_pattern !
		$sCurrentFieldCode = $this->Get('code');
		$sMasterTemplateFieldCode = $aMatches[1][0];
		if ($sMasterTemplateFieldCode === $sCurrentFieldCode) {
			$this->m_aCheckIssues[] = Dict::S('Class:TemplateField/Error:InvalidDisplayConditionCode');
		}
	}
}


class TemplateExtraData extends DBObject
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "",
			"key_type" => "autoincrement",
			"name_attcode" => array("obj_class", "obj_key"),
			"state_attcode" => "",
			"reconc_keys" => array("obj_class", "obj_key"),
			"db_table" => "tpl_extradata",
			"db_key_field" => "id",
			"db_finalclass_field" => "",
			"display_template" => "",
			'indexes' => array(
				array('obj_class', 'obj_key'),
			)
		);
		MetaModel::Init_Params($aParams);
		MetaModel::Init_InheritAttributes();

		MetaModel::Init_AddAttribute(new AttributeExternalKey("template_id", array("targetclass"=>"Template", "jointype"=>null, "allowed_values"=>null, "sql"=>"template_id", "is_null_allowed"=>true, "on_target_delete"=>DEL_SILENT, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeString("obj_class", array("allowed_values"=>null, "sql"=>"obj_class", "default_value"=>"", "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeInteger("obj_key", array("allowed_values"=>null, "sql"=>"obj_key", "default_value"=>"", "is_null_allowed"=>false, "depends_on"=>array())));

		// User data
		MetaModel::Init_AddAttribute(new AttributeLongText("data", array("allowed_values"=>null, "sql"=>"data", "default_value"=>"", "is_null_allowed"=>false, "depends_on"=>array())));
	}

	static public function FindByObject($sClass, $iKey)
	{
		$oSearch = DBObjectSearch::FromOQL('SELECT '.__class__.' WHERE obj_class = :obj_class AND obj_key = :obj_key');
		$oSearch->AllowAllData();
		$oSet = new DBObjectSet($oSearch, array(), array('obj_class' => $sClass, 'obj_key' => $iKey));
		return $oSet->Fetch();
	}
}

/**
 * Class TemplateFieldsHandler
 * Provides templating commons
 * Must be derived to implement GetPrerequisiteAttributes() and BuildForm(),
 * depending on custom conditions
 *
 */
abstract class TemplateFieldsHandler extends CustomFieldsHandler {
	const FORMAT_HTML = 1;
	const FORMAT_PLAINTEXT = 2;

	/**
	 * @var array Template id as key, template object as value
	 * @since 3.5.0 N°6322 N°1150 keep list of templates in memory so that we can check template_id
	 */
	protected $aTemplatesForServicesubcategory;

	protected function GetAsArray($aValues, $iFormat = self::FORMAT_HTML, $bLocalize = true, $bSkipHiddenFields = false, $bHyperlinks = true) {
		$this->NormalizeValues($aValues);

		$bHyperlinks = $bHyperlinks && ($iFormat == self::FORMAT_HTML);
		$aRet = array();
		if (self::IsNull($aValues)) {
		}
		else {
			if (self::IsLegacyFormat($aValues)) {
				if (!is_null($aValues['template_label'])) {
					$aRet[Dict::Format('Templates:Need')] = $aValues['template_label'];
				}

				$aExtraData = json_decode($aValues['extradata_legacy'], true);
				foreach ($aExtraData as $iField => $aInfo) {
					$sLabel = $aInfo['label'];
					$sDisplayValue = $aInfo['value'];
					if (isset($aInfo['value_obj_class']) && isset($aInfo['value_obj_key'])) {
						$oSelectedObj = MetaModel::GetObject($aInfo['value_obj_class'], $aInfo['value_obj_key'], false);
						if ($oSelectedObj) {
							if ($bHyperlinks) {
								$sDisplayValue = $oSelectedObj->GetHyperLink();
							}
							elseif ($iFormat == self::FORMAT_HTML) {
								$sDisplayValue = htmlentities($oSelectedObj->Get('friendlyname'), ENT_QUOTES, 'UTF-8');
							}
							else {
								$sDisplayValue = $oSelectedObj->Get('friendlyname');
							}
						}
					}
					$aRet[$sLabel] = $sDisplayValue;
				}
			}
			else
			{
				// This is the full featured / modern format
				if (!array_key_exists('template_data', $aValues)) throw new Exception('Wrong format: missing template_data');
				$aTemplateData = json_decode($aValues['template_data'], true);
				$aRet[Dict::Format('Templates:Need')] = $aTemplateData['label'];
				$aFieldLabels = array();
				foreach ($aTemplateData['fields'] as $aFieldData)
				{
					$aFieldLabels[$aFieldData['code']] = $aFieldData['label'];
				}
				foreach ($aValues['user_data'] as $sCode => $value)
				{
					$aFieldData = $aTemplateData['fields'][$sCode];

					// Skip hidden fields
					if ($bSkipHiddenFields)
					{
						// Skip inconditionnaly
						if ($aFieldData['input_type'] == 'hidden') continue;
					}
					else
					{
						if (!Template::IsFieldVisibleToCurrentUser($aTemplateData, $sCode)) continue;
					}

					$sDisplayValue = ($iFormat == self::FORMAT_HTML) ? Template::MakeHTMLValue($aFieldData, $value) : Template::MakePlainTextValue($aFieldData, $value);
					if (isset($aValues['user_data_objclass'][$sCode]))
					{
						if ($oObject = MetaModel::GetObject($aValues['user_data_objclass'][$sCode], $value, false))
						{
							if ($bHyperlinks)
							{
								$sDisplayValue = $oObject->GetHyperLink();
							}
							elseif ($iFormat == self::FORMAT_HTML)
							{
								$sDisplayValue = htmlentities($oObject->Get('friendlyname'), ENT_QUOTES, 'UTF-8');
							}
							else
							{
								$sDisplayValue = $oObject->Get('friendlyname');
							}
						}
						else
						{
							$sDisplayValue = $aValues['user_data_objname'][$sCode];
						}
					}
					$aRet[$aFieldLabels[$sCode]] = $sDisplayValue;
				}
			}
		}
		return $aRet;
	}
	/**
	 * @param $aValues
	 * @param bool|true $bLocalize
	 * @return string
	 */
	public function GetAsHTML($aValues, $bLocalize = true)
	{
		$this->NormalizeValues($aValues);

		if (self::IsNull($aValues)) {
			$sRet = '';
		} else {
			$aDisplayValues = array();
			if (self::IsLegacyFormat($aValues)) {
				if (!is_null($aValues['template_label'])) {
					$aDisplayValues[Dict::Format('Templates:Need')] = $aValues['template_label'];
				}

				$aExtraData = json_decode($aValues['extradata_legacy'], true);
				foreach ($aExtraData as $iField => $aInfo) {
					$sLabel = $aInfo['label'];
					$sDisplayValue = $aInfo['value'];
					if (isset($aInfo['value_obj_class']) && isset($aInfo['value_obj_key'])) {
						$oSelectedObj = MetaModel::GetObject($aInfo['value_obj_class'], $aInfo['value_obj_key'], false);
						if ($oSelectedObj) {
							$sDisplayValue = $oSelectedObj->GetHyperLink();
						}
					}
					$aDisplayValues[$sLabel] = $sDisplayValue;
				}
			} else {
				// This is the full featured / modern format
				if (!array_key_exists('template_data', $aValues)) {
					throw new Exception('Wrong format: missing template_data');
				}
				$aTemplateData = json_decode($aValues['template_data'], true);
				$aDisplayValues[Dict::Format('Templates:Need')] = $aTemplateData['label'];
				$aFieldLabels = array();
				foreach ($aTemplateData['fields'] as $aFieldData) {
					$aFieldLabels[$aFieldData['code']] = $aFieldData['label'];
				}
				$aCachedDisplayConditionsResults = array();
				$aDisplayConditions = Template::GetDisplayConditions($aTemplateData);
				foreach ($aValues['user_data'] as $sCode => $value) {
					// Skip hidden fields
					if (!Template::IsFieldVisibleToCurrentUser($aTemplateData, $sCode)) {
						continue;
					}
					$bIsDisplayConditionOk = Template::IsFieldDisplayConditionOk($sCode, $aDisplayConditions, $aValues['user_data'],
						$aCachedDisplayConditionsResults);
					if (!$bIsDisplayConditionOk) {
						continue;
					}

					$aFieldData = $aTemplateData['fields'][$sCode];
					$sDisplayValue = Template::MakeHTMLValue($aFieldData, $value);
					if (isset($aValues['user_data_objclass'][$sCode])) {
						if ($oObject = MetaModel::GetObject($aValues['user_data_objclass'][$sCode], $value, false)) {
							$sDisplayValue = $oObject->GetHyperlink();
						} else {
							$sDisplayValue = $aValues['user_data_objname'][$sCode];
						}
					}
					$aDisplayValues[$aFieldLabels[$sCode]] = $sDisplayValue;
				}
			}
			if (version_compare(ITOP_DESIGN_LATEST_VERSION , '3.0') >= 0) {
				$oDisplay = UIContentBlockUIBlockFactory::MakeStandard();

				foreach ($aDisplayValues as $sLabel => $sDisplayValue) {
					$oBlock = FieldUIBlockFactory::MakeStandard($sLabel);
					$oBlock->AddSubBlock(new Html($sDisplayValue));
					$oDisplay->AddSubBlock($oBlock);
				}
				$sRet = BlockRenderer::RenderBlockTemplates( $oDisplay);
			} else{

				$sRet = '<table class="details">';
				$sRet .= '<tbody>';

				foreach ($aDisplayValues as $sLabel => $sDisplayValue)
				{
					$sRet .= '<tr>';
					$sRet .= '<td class="label"><span>'.$sLabel.'</span></td><td>'.$sDisplayValue.'</td>';
					$sRet .= '</tr>';
				}
				$sRet .= '</tbody>';
				$sRet .= '</table>';
			}
		}
		return $sRet;
	}

	/**
	 * @param $aValues
	 * @param bool|true $bLocalize
	 * @return string
	 */
	public function GetAsXML($aValues, $bLocalize = true)
	{
		$this->NormalizeValues($aValues);

		$sRet = '';
		if (self::IsNull($aValues))
		{
			// Leave the container tag empty
		}
		else
		{
			if (self::IsLegacyFormat($aValues))
			{
				$sRet .= '<legacy_format>yes</legacy_format>';
				$sRet .= '<template_label>'.$aValues['template_label'].'</template_label>';

				$aExtraData = json_decode($aValues['extradata_legacy'], true);
				$sRet .= '<fields>';
				foreach ($aExtraData as $iField => $aInfo)
				{
					$sCode = $aInfo['code'];
					$sDisplayValue = $aInfo['value'];
					if (isset($aInfo['value_obj_class']) && isset($aInfo['value_obj_key']))
					{
						$oSelectedObj = MetaModel::GetObject($aInfo['value_obj_class'], $aInfo['value_obj_key'], false);
						if ($oSelectedObj)
						{
							$sDisplayValue = $oSelectedObj->Get('friendlyname');
						}
					}
					$sRet .= '<field id="'.$sCode.'">';
					$sRet .= '<value>'.Str::pure2xml((string)$aInfo['value']).'</value>';
					$sRet .= '<label>'.Str::pure2xml($sDisplayValue).'</label>';
					$sRet .= '</field>';
				}
				$sRet .= '</fields>';
			}
			else
			{
				$aTemplateData = json_decode($aValues['template_data'], true);
				$sRet .= '<template_id>'.$aTemplateData['id'].'</template_id>';
				$sRet .= '<template_label>'.Str::pure2xml($aTemplateData['label']).'</template_label>';
				$sRet .= '<fields>';
				foreach ($aValues['user_data'] as $sCode => $sValue)
				{
					// Skip hidden fields
					if (!Template::IsFieldVisibleToCurrentUser($aTemplateData, $sCode)) continue;

					$sDisplayValue = $sValue;
					if (array_key_exists($sCode, $aValues['user_data_objclass']))
					{
						if ($oObject = MetaModel::GetObject($aValues['user_data_objclass'][$sCode], $sValue, false))
						{
							$sDisplayValue = $oObject->Get('friendlyname');
						} else
						{
							$sDisplayValue = $aValues['user_data_objname'][$sCode];
						}
					}
					$sRet .= '<field id="'.$sCode.'">';
					$sRet .= '<value>'.Str::pure2xml((string)$sValue).'</value>';
					$sRet .= '<label>'.Str::pure2xml($sDisplayValue).'</label>';
					$sRet .= '</field>';
				}
				$sRet .= '</fields>';
			}
		}
		return $sRet;
	}

	/**
	 * @param $aValues
	 * @param string $sSeparator
	 * @param string $sTextQualifier
	 * @param bool|true $bLocalize
	 * @return string
	 */
	public function GetAsCSV($aValues, $sSeparator = ',', $sTextQualifier = '"', $bLocalize = true)
	{
		$this->NormalizeValues($aValues);

		$aFieldsValues = $this->GetTemplateFieldsValues($aValues, true);

		$sQualifier = "'";
		$sSepItem = ',';

		$aItems = array();
		foreach ($aFieldsValues as $sCode => $sDisplayValue) {
			$sCSV = $sCode.'='.$sDisplayValue;
			$sCSV = str_replace($sQualifier, $sQualifier.$sQualifier, $sCSV);
			$aItems[] = $sQualifier.$sCSV.$sQualifier;
		}
		$sRawRes = implode($sSepItem, $aItems);
		$sRawRes = str_replace($sTextQualifier, $sTextQualifier.$sTextQualifier, $sRawRes);
		$sRes = $sTextQualifier.$sRawRes.$sTextQualifier;

		return $sRes;
	}

	public function GetAsJSON($aValues)
	{
		$this->NormalizeValues($aValues);

		$aFieldsValues = $this->GetTemplateFieldsValues($aValues);

		$aJsonData['template_id'] = (string)$aValues['template_id']; // implicit conversion to avoid having an int here (OK for json_encode, but could cause problems in PHPUnit comparison)
		$aJsonData['values'] = $aFieldsValues;

		return $aJsonData;
	}

	public function FromJSONToValue(?stdClass $json, string $sAttCode): ?ormCustomFieldsValue
	{
		if (is_null($json)) {
			return null;
		}

		// converting stdClass to associative array
		$aUserData = json_decode(json_encode($json->values), true);

		$sTemplateId = $json->template_id;

		try {
			/** @var \Template $oTemplate */
			/** @noinspection PhpRedundantOptionalArgumentInspection */
			$oTemplate = MetaModel::GetObject(Template::class, $sTemplateId, true);
		} catch (ArchivedObjectException|CoreException $e) {
			throw new CoreUnexpectedValue("Cannot find template with specified ID ({$sTemplateId})", [
				'attcode' => $sAttCode,
				'json'    => $aUserData,
			]);
		}

		$aTemplateValues = [
			'template_id'   => $sTemplateId,
			'template_data' => json_encode($oTemplate->ToArray()),
			'user_data'     => $aUserData,
		];

		return new ormCustomFieldsValue(null, $sAttCode, $aTemplateValues);
	}

	/**
	 * @param array $aValues
	 * @param bool $bUseLabelAsArrayKey if true will output label field for the key, else use code
	 *
	 * @return array field code (or label depending on the $bUseLabelAsArrayKey parameter) as key, field value as value
	 *
	 * @since 3.5.0 N°1150 Method creation
	 */
	protected function GetTemplateFieldsValues($aValues, $bUseLabelAsArrayKey = false): array {
		if (self::IsLegacyFormat($aValues)) {
			$aExtraData = json_decode($aValues['extradata_legacy'], true);
			foreach ($aExtraData as $iField => $aInfo) {
				$sCode = $aInfo['code'];
				$sDisplayValue = $aInfo['value'];
				if (isset($aInfo['value_obj_class']) && isset($aInfo['value_obj_key'])) {
					$oSelectedObj = MetaModel::GetObject($aInfo['value_obj_class'], $aInfo['value_obj_key'], false);
					if ($oSelectedObj) {
						$sDisplayValue = $oSelectedObj->Get('friendlyname');
					}
				}
				$aRetValues[$sCode] = $sDisplayValue;
			}
		} elseif ($aValues['template_id'] > 0) {
			// This is the full featured / modern format
			if (!array_key_exists('template_data', $aValues)) {
				throw new Exception('Wrong format: missing template_data');
			}
			$aTemplateData = json_decode($aValues['template_data'], true);
			$aRetValues[Dict::Format('Templates:Need')] = $aTemplateData['label'];
			$aFieldLabels = array();
			foreach ($aTemplateData['fields'] as $aFieldData) {
				$aFieldLabels[$aFieldData['code']] = $aFieldData['label'];
			}
			foreach ($aValues['user_data'] as $sFieldCode => $sFieldValue) {
				// Skip hidden fields
				if (!Template::IsFieldVisibleToCurrentUser($aTemplateData, $sFieldCode)) {
					continue;
				}

				$sDisplayValue = $sFieldValue;
				if (array_key_exists($sFieldCode, $aValues['user_data_objclass'])) {
					if ($oObject = MetaModel::GetObject($aValues['user_data_objclass'][$sFieldCode], $sFieldValue, false)) {
						$sDisplayValue = $oObject->Get('friendlyname');
					}
					else {
						$sDisplayValue = $aValues['user_data_objname'][$sFieldCode];
					}
				}

				if ($bUseLabelAsArrayKey) {
					$sFieldLabel = $aFieldLabels[$sFieldCode];
					$sReturnedArrayFieldKey = $sFieldLabel;
				}
				else {
					$sReturnedArrayFieldKey = $sFieldCode;
				}

				$aRetValues[$sReturnedArrayFieldKey] = $sDisplayValue;
			}
		} else {
			$aRetValues = array();
		}

		return $aRetValues;
	}

	/**
	 * List the available verbs for 'GetForTemplate'
	 */
	public static function EnumTemplateVerbs()
	{
		return array(
			''     => 'Plain text (unlocalized) representation',
			'html' => 'HTML representation (unordered list)',
		);
	}

	/**
	 * Get various representations of the value, for insertion into a template (e.g. in Notifications)
	 * @param $aValues array The current values
	 * @param $sVerb string The verb specifying the representation of the value
	 * @param $bLocalize bool Whether or not to localize the value
	 * @return string
	 */
	public function GetForTemplate($aValues, $sVerb, $bLocalize = true)
	{
		$sRet = '';
		switch ($sVerb)
		{
			case '':
				$aDisplayValues = $this->GetAsArray($aValues, self::FORMAT_PLAINTEXT, $bLocalize, true, false);
				foreach ($aDisplayValues as $sLabel => $sDisplayValue)
				{
					$sRet .= '- '.$sLabel.': '.$sDisplayValue.PHP_EOL;
				}
				break;
			case 'html':
				$aDisplayValues = $this->GetAsArray($aValues, self::FORMAT_HTML, $bLocalize, true, false);
				if (count($aDisplayValues) > 0)
				{
					$sRet = '<ul>';
					foreach ($aDisplayValues as $sLabel => $sDisplayValue)
					{
						$sRet .= '<li>';
						$sRet .= $sLabel.':&nbsp;'.$sDisplayValue;
						$sRet .= '</li>';
					}
					$sRet .= '</ul>';
				}
				break;
		}
		return $sRet;
	}

	/**
	 * @return DBObjectSet of Templates
	 */
	abstract protected function FindTemplates(DBObject $oHostObject);

	/**
	 * @throws CoreException
	 * @throws DictExceptionMissingString
	 */
	public function BuildForm(DBObject $oHostObject, $sFormId)
	{
		try {
			$this->DoBuildForm($oHostObject, $sFormId);
		} catch (FieldInvalidDependencyException $e) {
			$this->oForm = new \Combodo\iTop\Form\Form('');
			// In iTop core (\AttributeCustomFields::GetForm) we have a similar catch which returns a LabelField
			// LabelField support in iTop admin console isn't supported before 3.0.0
			// So to be compatible with all iTop versions we're using a StringField instead !
			$oField = new \Combodo\iTop\Form\Field\StringField('');
			$oField->SetReadOnly(true);
			$oField->SetLabel('Error ');
			$oField->SetCurrentValue($e->getMessage());
			$this->oForm->AddField($oField);
			$this->oForm->Finalize();
		}
	}

	/**
	 * @throws \CoreException
	 * @throws \CoreUnexpectedValue If template_id isn't available for the current object
	 * @throws \ArchivedObjectException
	 * @throws \MySQLException
	 *
	 * @since 2.3.0 N°1150 check template_id
	 */
	public function DoBuildForm(DBObject $oHostObject, $sFormId) {
		$this->oForm = new \Combodo\iTop\Form\Form($sFormId);

		$oField = new Combodo\iTop\Form\Field\HiddenField('legacy');
		$iLegacy = isset($this->aValues['legacy']) ? $this->aValues['legacy'] : 0;
		$oField->SetCurrentValue((int)$iLegacy);
		$this->oForm->AddField($oField);

		if ($this->IsLegacyFormat($this->aValues)) {
			$this->AddLegacyFormFields();
		}
		else {
			// Keep information that will be necessary when refreshing the form
			$oField = new Combodo\iTop\Form\Field\HiddenField('extradata_id');
			$iExtraDataId = isset($this->aValues['extradata_id']) ? $this->aValues['extradata_id'] : '';
			$oField->SetCurrentValue($iExtraDataId);
			$this->oForm->AddField($oField);
			$oField = new Combodo\iTop\Form\Field\HiddenField('current_template_id');
			$iCurrentTemplateId = isset($this->aValues['current_template_id']) ? $this->aValues['current_template_id'] : '';
			$oField->SetCurrentValue($iCurrentTemplateId);
			$this->oForm->AddField($oField);
			$oField = new Combodo\iTop\Form\Field\HiddenField('current_template_data');
			$sCurrentTemplateData = isset($this->aValues['current_template_data']) ? $this->aValues['current_template_data'] : '';
			$oField->SetCurrentValue($sCurrentTemplateData);
			$this->oForm->AddField($oField);

			$bForceEmptyTemplate = false;
			if (!$oHostObject->IsNew() && ($iCurrentTemplateId == 0))
			{
				// The object has been recorded without templates
				// Keep it as is (make sure that the object could pass the CheckToWrite test), except if a change on the object has an impact on the template
				$bForceEmptyTemplate = true;
				$aChanges = $oHostObject->ListChanges();
				foreach (static::GetPrerequisiteAttributes() as $sAttCode)
				{
					if (array_key_exists($sAttCode, $aChanges))
					{
						$bForceEmptyTemplate = false;
					}
				}
			}

			$aChoices = array();
			if (!$bForceEmptyTemplate) {
				$aTemplates = array();
				$oTemplateSet = static::FindTemplates($oHostObject);
				while ($oTemplate = $oTemplateSet->Fetch()) {
					$aChoices[$oTemplate->GetKey()] = $oTemplate->Get('label');
					$aTemplates[$oTemplate->GetKey()] = $oTemplate;
				}
				$this->aTemplatesForServicesubcategory = $aTemplates; // saving for checks in the Validate() method !
			}

			if (count($aChoices) == 0) {
				// Empty form
			}
			else
			{
				$iTemplateValue = isset($this->aValues['template_id']) ? $this->aValues['template_id'] : 0;

				if (count($aChoices) == 1) {
					// There is one single template: auto select this one
					$aTemplateIds = array_keys($aChoices);
					$iTemplate = $aTemplateIds[0];
				}
				else {
					$iTemplate = $iTemplateValue;
					if (!array_key_exists($iTemplate, $aChoices))
					{
						$iTemplate = 0;
					}
				}

				if ($iTemplate == 0)
				{
					// No template selected
					$aTemplateData = null;
				}
				elseif ($iTemplate == $iCurrentTemplateId)
				{
					// The user has selected the template that corresponds to the one stored in the DB
					$aTemplateData = json_decode($sCurrentTemplateData, true);
					if (array_key_exists($aTemplateData['id'], $aChoices))
					{
						// Make sure that the label is the legacy one
						$aChoices[$aTemplateData['id']] = $aTemplateData['label'];
					}
				}
				else
				{
					// The user has selected a template different from the one stored in the DB (if any)
					$oTemplate = $aTemplates[$iTemplate];
					$aTemplateData = $oTemplate->ToArray();
				}

				if (count($aChoices) > 1)
				{
					$oField = new Combodo\iTop\Form\Field\SelectField('template_id');
					$oField->SetLabel(Dict::S('Templates:Need'));
					$oField->SetMandatory(true);
					$oField->SetChoices($aChoices);
					$oField->SetCurrentValue($iTemplate);
					$this->oForm->AddField($oField);
				}
				else
				{
					// There is one single template: no need to select it

					// This field is for the label
					$oField = new Combodo\iTop\Form\Field\StringField('_template_name_');
					$oField->SetLabel(Dict::S('Templates:Need'));
					$oField->SetReadOnly(true);
					$oField->SetCurrentValue($aChoices[$iTemplate]);
					$this->oForm->AddField($oField);

					// This field to keep the value
					$oField = new Combodo\iTop\Form\Field\HiddenField('template_id');
					$oField->SetCurrentValue($iTemplate);
					$this->oForm->AddField($oField);
				}

				$oField = new Combodo\iTop\Form\Field\HiddenField('template_data');
				$oField->SetCurrentValue(json_encode($aTemplateData));
				$this->oForm->AddField($oField);
				$this->oForm->AddFieldDependency('template_data', 'template_id');

				$aUserData = isset($this->aValues['user_data']) ? $this->aValues['user_data'] : array();

				if (MetaModel::GetModuleSetting('templates-base', 'reset_fields_on_template_change', false)) {
					if (isset($this->aValues['template_data'])) {
						$aPreviousTemplateData = json_decode($this->aValues['template_data'], true);
						if (isset($aPreviousTemplateData['id']) && $aPreviousTemplateData['id'] != $iTemplate) {
							$aUserData = array();
						}
					}
				}
				$oUserDataField = new Combodo\iTop\Form\Field\SubFormField('user_data');
				if (!is_null($aTemplateData)) {
					/** @var \Template $sTemplateClass */
					$sTemplateClass = $aTemplateData['class'];
					$aPreviousValues = isset($this->aValues['previous_values']) ? json_decode($this->aValues['previous_values'], true) : [];
					$aExtKeyFields = $sTemplateClass::PopulateUserDataForm($oHostObject, $oUserDataField->GetForm(), $aTemplateData, $aUserData, $aPreviousValues);
				}
				else
				{
					$aExtKeyFields = array();
				}
				$this->oForm->AddField($oUserDataField);
				$this->oForm->AddFieldDependency('user_data', 'template_id');

				$oField = new Combodo\iTop\Form\Field\HiddenField('__extkeys__');
				$oField->SetCurrentValue(json_encode($aExtKeyFields));
				$this->oForm->AddField($oField);
				$this->oForm->AddFieldDependency('__extkeys__', 'template_id');

				$oField = new Combodo\iTop\Form\Field\HiddenField('previous_values');
				$oField->SetCurrentValue('');
				$this->oForm->AddField($oField);

			}
		}
		$this->oForm->Finalize();
	}

	/**
	 * @inheritDoc
	 * @throws \CoreUnexpectedValue
	 */
	public function Validate(DBObject $oHostObject) {
		if ((false === is_null($this->aValues))
			&& array_key_exists('template_id', $this->aValues)
			&& ($this->aValues['template_id'] !== 0)
			&& (utils::IsNotNullOrEmptyString($this->aValues['template_id']))
		) {
			$sTemplateId = $this->aValues['template_id'];

			if (false === array_key_exists($sTemplateId, $this->aTemplatesForServicesubcategory)) {
				$sHostObjectClass = get_class($oHostObject);
				$sHostObjectId = $oHostObject->GetKey();
				throw new CoreUnexpectedValue("The specified template_id ({$sTemplateId}) isn't available for the current object ($sHostObjectClass::$sHostObjectId)");
			}
		}

		return parent::Validate($oHostObject);
	}

	protected function AddLegacyFormFields() {
		$oField = new Combodo\iTop\Form\Field\HiddenField('extradata_legacy');
		$oField->SetCurrentValue($this->aValues['extradata_legacy']);
		$this->oForm->AddField($oField);

		if (isset($this->aValues['template_label']) && (strlen($this->aValues['template_label']) > 0)) {
			$oField = new Combodo\iTop\Form\Field\StringField('template_label');
			$oField->SetLabel(Dict::S('Templates:Need'));
			$oField->SetReadOnly(true);
			$oField->SetCurrentValue($this->aValues['template_label']);
			$this->oForm->AddField($oField);
		}

		$aExtraDataLegacy = json_decode($this->aValues['extradata_legacy'], true);
		foreach ($aExtraDataLegacy as $iField => $aInfo)
		{
			$sDisplayValue = $aInfo['value'];
			if (isset($aInfo['value_obj_class']) && isset($aInfo['value_obj_key']))
			{
				$oSelectedObj = MetaModel::GetObject($aInfo['value_obj_class'], $aInfo['value_obj_key'], false);
				if ($oSelectedObj)
				{
					$sDisplayValue = $oSelectedObj->GetHyperLink();
				}
			}
			// Note : those fields are here for the presentation and will never be read: only the extradata_legacy value will be interpreted
			$oField = new Combodo\iTop\Form\Field\StringField('extradata_legacy_'.$aInfo['code']);
			$oField->SetLabel($aInfo['label']);
			$oField->SetReadOnly(true);
			$oField->SetCurrentValue($sDisplayValue);
			$this->oForm->AddField($oField);
		}
	}

	/**
	 * @param DBObject $oHostObject
	 * @return array Associative array id => value
	 */
	public function ReadValues(DBObject $oHostObject)
	{
		if ($oHostObject->IsNew())
		{
			$oExtraData = null;
		}
		else
		{
			$oExtraData = TemplateExtraData::FindByObject(get_class($oHostObject), $oHostObject->GetKey());
		}

		$aRet = $this->CreateValuesFromTemplateExtraData($oExtraData);

		return $aRet;
	}


	/**
	 * @param \TemplateExtraData|null $oExtraData
	 *
	 * @return array|mixed
	 * @throws \ArchivedObjectException
	 * @throws \CoreException
	 */
	public function CreateValuesFromTemplateExtraData($oExtraData)
	{
		if (!$oExtraData)
		{
			return array(
				'legacy' => false,
				'template_id' => null,
				'template_data' => null,
				'current_template_id' => null,
				'current_template_data' => null,
				'user_data' => array(),
				'extradata_id' => 0
			);
		}

		$aRawData = unserialize($oExtraData->Get('data'));
		if (!array_key_exists('user_data', $aRawData))
		{
			// Legacy format (template-base version 2.1.4 or older):
			// array of iTemplateField => array(
			//     'code' => ...
			//     'label' => ...
			//     'input_type' => ...
			//     'value' => ...
			// )
			$oTemplate = MetaModel::GetObject('Template', $oExtraData->Get('template_id'), false);
			$sTemplateLabel = $oTemplate ? $oTemplate->Get('label') : null;
			$aRet = array(
				'legacy' => true,
				'extradata_legacy' => json_encode($aRawData),
				'template_label' => $sTemplateLabel
			);
		}
		else
		{
			// Current format
			// 'legacy' => false,
			// 'template_id' => null or selected template id,
			// 'template_data' => null or template as an array (json encoded),
			// 'user_data' => array of <code> => <value>,
			// 'extradata_id' => 0 or extra data record id (used to read the format)
			if (!array_key_exists('template_id', $aRawData))
			{
				throw new Exception('Wrong format: missing template_id');
			}
			if (!array_key_exists('template_data', $aRawData))
			{
				throw new Exception('Wrong format: missing template_data');
			}
			if (!array_key_exists('user_data', $aRawData))
			{
				throw new Exception('Wrong format: missing user_data');
			}
			if (!array_key_exists('extradata_id', $aRawData))
			{
				throw new Exception('Wrong format: missing extradata_id');
			}

			$aRet = $aRawData;

			// Hidden fields are part of the internal data
			$aTemplateData = json_decode($aRet['template_data'], true);
			if (is_array($aTemplateData['fields']))
			{
				foreach ($aTemplateData['fields'] as $sFieldCode => $aFieldData)
				{
					switch ($aFieldData['input_type'])
					{
						case 'date':
							$aRet['user_data'][$sFieldCode] = AttributeDate::GetFormat()->Format($aRet['user_data'][$sFieldCode]);
							break;

						case 'date_and_time':
							$aRet['user_data'][$sFieldCode] = AttributeDateTime::GetFormat()->Format($aRet['user_data'][$sFieldCode]);
							break;
					}
				}
			}
			if (is_array($aTemplateData['hidden_fields']))
			{
				foreach ($aTemplateData['hidden_fields'] as $sFieldCode => $foo)
				{
					$aFieldData = $aTemplateData['fields'][$sFieldCode];
					if (Template::IsFieldVisibleToCurrentUser($aTemplateData, $sFieldCode))
					{
						// A placeholder is already there (empty string) so that the value will be correctly placed
						$aRet['user_data'][$sFieldCode] = $aFieldData['initial_value'];
					}
					else
					{
						// Hide this value
						unset($aRet['user_data'][$sFieldCode]);
					}
				}
			}
			// Keep track of the current (persistent) values, that may differ from template_id/template_data if the user selects another template (or none)
			$aRet['current_template_id'] = $aRet['template_id'];
			$aRet['current_template_data'] = $aRet['template_data'];
		}
		// Make sure that the source id is correct (not set on the first round - See WriteValues/DBInsert)
		$aRet['extradata_id'] = $oExtraData->GetKey();


		return $aRet;
	}

	/**
	 * Record the data (currently in the processing of recording the host object)
	 * It is assumed that the data has been checked prior to calling Write()
	 *
	 * @param DBObject $oHostObject
	 * @param array $aValues Associative array id => value
	 */
	public function WriteValues(DBObject $oHostObject, $aValues)
	{
		if (isset($aValues['template_id']) && ($aValues['template_id'] > 0))
		{
			if ( (strlen($aValues['extradata_id']) === 0) || ((int) $aValues['extradata_id'] === 0) )
			{
				$oExtraData = MetaModel::NewObject('TemplateExtraData');
				$oExtraData->Set('obj_class', get_class($oHostObject));
				$oExtraData->Set('obj_key', $oHostObject->GetKey());
			}
			else
			{
				$oExtraData = MetaModel::GetObject('TemplateExtraData', $aValues['extradata_id']);
			}
			$oExtraData->Set('template_id', $aValues['template_id']); // Legacy field (redundant with the data 'template_id' !

			$this->NormalizeValues($aValues);

			unset($aValues['current_template_id']); // the right one is 'template_id'
			unset($aValues['current_template_data']); // the right one is 'template_data'
			$oExtraData->Set('data', serialize($aValues));

			$oExtraData->DBWrite();

			$sObjClass = get_class($oHostObject);
			$iObjKey   = $oHostObject->GetKey();

			$this->DeleteTemplateFieldValues($oHostObject);
			$this->WriteTemplateFieldValues($sObjClass, $iObjKey, $aValues);

			$this->BeyondWriteValues($oHostObject, $aValues, $oExtraData);
		}
		else
		{
			if ($aValues['extradata_id'] > 0)
			{
				$oExtraData = MetaModel::GetObject('TemplateExtraData', $aValues['extradata_id']);
				$oExtraData->DBDelete();

				$this->DeleteTemplateFieldValues($oHostObject);
			}
			$this->BeyondWriteValues($oHostObject, $aValues, null);
		}
	}

	/**
	 * Cleanup data upon object deletion (object id still available here)
	 * @param DBObject $oHostObject
	 */
	public function DeleteValues(DBObject $oHostObject)
	{
		$oSearch = DBObjectSearch::FromOQL("SELECT TemplateExtraData WHERE obj_class = :obj_class AND obj_key = :obj_key");
		$oSearch->AllowAllData();
		$oSet = new DBObjectSet($oSearch, array(), array('obj_class' => get_class($oHostObject), 'obj_key' => $oHostObject->GetKey()));
		while ($oExtraData = $oSet->Fetch())
		{
			$oExtraData->DBDelete();
		}

		$this->DeleteTemplateFieldValues($oHostObject);
	}

	/**
	 * Returns true if the values are equivalent
	 * The comparison is not straightforward as the data read from the DB can slightly differ from the data obtained by the form
	 * @param $aValuesA
	 * @param $aValuesB
	 * @return bool
	 */
	public function CompareValues($aValuesA, $aValuesB)
	{
		if ($this->IsLegacyFormat($aValuesA))
		{
			// No change is possible in the legacy format
			return true;
		}

		$iTemplateA =  (isset($aValuesA['template_id']) && ($aValuesA['template_id'] > 0)) ? $aValuesA['template_id'] : 0;
		$iTemplateB =  (isset($aValuesB['template_id']) && ($aValuesB['template_id'] > 0)) ? $aValuesB['template_id'] : 0;
		if ($iTemplateA != $iTemplateB)
		{
			return false;
		}
		if ($iTemplateA != 0)
		{
			foreach ($aValuesA['user_data'] as $sFieldCode => $value)
			{
				if (!isset($aValuesB['user_data'][$sFieldCode]))
				{
					return false;
				}
				if ($aValuesB['user_data'][$sFieldCode] != $value)
				{
					return false;
				}
			}
		}
		return true;
	}

	/**
	 * Normalize template values (eg. ExtKey's ID is changed to ExtKey's friendlyname)
	 *
	 * @param array $aValues
	 *
	 * @throws \ArchivedObjectException
	 * @throws \CoreException
	 */
	public function NormalizeValues(&$aValues = array())
	{
		if(!empty($aValues))
		{
			if (!isset($aValues['template_data']))
			{
				return;
			}
			$aTemplateData = json_decode($aValues['template_data'], true);

			// Normalize the structure of aValues
			$aHiddenFields = $aTemplateData['hidden_fields'];

			// Checking entry presence as this normalization can be called several times
			if(isset($aValues['__extkeys__']))
			{
				$aExtKeys = json_decode($aValues['__extkeys__'], true);
				unset($aValues['__extkeys__']);
			}
			if(!isset($aValues['user_data_objclass']) && !isset($aValues['user_data_objname']))
			{
				$aValues['user_data_objclass'] = array();
				$aValues['user_data_objkey'] = array();
				$aValues['user_data_objname'] = array();
			}

			if (!isset($aValues['user_data']))
			{
				return;
			}
			foreach ($aValues['user_data'] as $sFieldCode => $value)
			{
				if ($aTemplateData['fields'][$sFieldCode]['input_type'] == 'date')
				{
					$val = AttributeDate::GetFormat()->Parse($value);
					if (is_object($val))
					{
						$val = $val->Format(AttributeDate::GetSQLFormat());
					}
					$aValues['user_data'][$sFieldCode] = $val;
				}
				else if($aTemplateData['fields'][$sFieldCode]['input_type'] == 'date_and_time')
				{
					$val = AttributeDateTime::GetFormat()->Parse($value);
					if (is_object($val))
					{
						$val = $val->Format(AttributeDateTime::GetSQLFormat());
					}
					$aValues['user_data'][$sFieldCode] = $val;
				}
				if (isset($aExtKeys[$sFieldCode]))
				{
					$sClass = $aExtKeys[$sFieldCode];
					if ($oObject = MetaModel::GetObject($sClass, $value, false))
					{
						$aValues['user_data_objclass'][$sFieldCode] = get_class($oObject);
						$aValues['user_data_objkey'][$sFieldCode] = $oObject->GetKey();
						$aValues['user_data_objname'][$sFieldCode] = $oObject->Get('friendlyname');
					}
				}
				if (isset($aHiddenFields[$sFieldCode]))
				{
					// Note: depending on the current user, hidden values might be given here or not
					// Let's normalize stored data (while leaving a placeholder so as to preserve the order)
					// Hidden values will be automatically restored by ReadValues, based on the 'initial_value'
					$aValues['user_data'][$sFieldCode] = '';
				}
			}
		}
	}

	/**
	 * String representation of the value, must depend solely on the semantics
	 * @return string
	 */
	public function GetValueFingerprint()
	{
		// todo: check if a cleaning is required
		return json_encode($this->aValues);
	}

	/**
	 * Returns if the custom fields are to be considered as NULL.
	 * This is inspired from the method self::ReadValues(), in case where $oExtraData is null
	 *
	 * @param $aValues
	 * @return bool
	 */
	public function IsNull($aValues)
	{
		$bRet = false;
		if ($aValues === null)
		{
			$bRet = true;
		}
		// Note: Using "==" instead of "===" as the value of "legacy" can be either a boolean or an int.
		elseif(isset($aValues['legacy']) && ($aValues['legacy'] == false))
		{
			// Case: Creating an Object with no template (N°1079)
			if(!isset($aValues['user_data']))
			{
				$bRet = true;
			}
			// Case: Creating an Object with a template but its values have not been initialized yet (N°961)
			elseif(isset($aValues['user_data']) && empty($aValues['user_data']))
			{
				$bRet = true;
			}
		}

		return $bRet;
	}

	public function IsLegacyFormat($aValues)
	{
		if (isset($aValues['legacy']))
		{
			return ($aValues['legacy']);
		}
		return false;
	}

	/**
	 * Write template data to the case log (legacy behavior)
	 * @param $aValues
	 * @param $oExtraData|null TemplateExtraData
	 * @throws CoreUnexpectedValue
	 * @throws Exception
	 */
	public function BeyondWriteValues(DBObject $oHostObject, $aValues, TemplateExtraData $oExtraData = null)
	{
	}

	/**
	 * write a copy into a leaf class of TemplateFieldValue in order to leverage the possibility to perform OQL queries
	 *
	 * @param $sObjClass
	 * @param $iObjKey
	 * @param $aValues
	 *
	 * @throws \CoreCannotSaveObjectException
	 * @throws \CoreException
	 */
	public function WriteTemplateFieldValues($sObjClass, $iObjKey, $aValues)
	{
		$aTemplateData = json_decode($aValues['template_data'], true);

		foreach ($aValues['user_data'] as $sFieldCode => $value)
		{
			$sFieldType = $aTemplateData['fields'][$sFieldCode]['input_type'];
			if (array_key_exists('user_data_objkey', $aValues) && array_key_exists($sFieldCode, $aValues['user_data_objkey']))
			{
				//data written with version 3.0.17 and bellow cannot to be restored with the linkset since user_data_objkey was not written yet!
				$oTemplateFieldValue = MetaModel::NewObject('TemplateFieldValueLnk', array(
					'obj_class'   => $sObjClass,
					'obj_key'     => $iObjKey,
					'template_id' => $aValues['template_id'],
					'template_name' => $aTemplateData['name'],
					'field_type'  => $sFieldType,
					'field_code'  => $sFieldCode,
					'field_value' => $aValues['user_data_objname'][$sFieldCode],

					'field_target_class' => $aValues['user_data_objclass'][$sFieldCode],
					'field_target_key' => $aValues['user_data_objkey'][$sFieldCode],
				));
			}
			else
			{
				$oTemplateFieldValue = MetaModel::NewObject('TemplateFieldValue', array(
					'obj_class'   => $sObjClass,
					'obj_key'     => $iObjKey,
					'template_id' => $aValues['template_id'],
					'template_name' => $aTemplateData['name'],
					'field_type'  => $sFieldType,
					'field_code'  => $sFieldCode,
					'field_value' => $value,
				));
			}

			$oTemplateFieldValue->DBWrite();
		}
	}

	/**
	 * @param \DBObject $oHostObject
	 *
	 * @throws \ArchivedObjectException
	 * @throws \CoreCannotSaveObjectException
	 * @throws \CoreException
	 * @throws \CoreUnexpectedValue
	 * @throws \DeleteException
	 * @throws \MySQLException
	 * @throws \MySQLHasGoneAwayException
	 * @throws \OQLException
	 */
	public function DeleteTemplateFieldValues(DBObject $oHostObject)
	{
		// delete previous TemplateFieldValue values, the new one will be inserted later.
		$oSearch = DBObjectSearch::FromOQL("SELECT TemplateFieldValue WHERE obj_class = :obj_class AND obj_key = :obj_key");
		$oSearch->AllowAllData();
		$oSet = new DBObjectSet($oSearch, array(), array('obj_class' => get_class($oHostObject), 'obj_key' => $oHostObject->GetKey()));
		/** @var  TemplateFieldValue $oTemplateFieldValue */
		while ($oTemplateFieldValue = $oSet->Fetch()) {
			$oTemplateFieldValue->DBDelete();
		}
	}
}

/**
 * @since 2.3.0 a catch was added on Exception for N°2922, but using Exception class for it is far too generic :( We need another catch for N°1150, so adding this specific exception !
 */
class FieldInvalidDependencyException extends CoreException {
}