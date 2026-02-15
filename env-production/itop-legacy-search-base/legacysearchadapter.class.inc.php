<?php
/**
 * Copyright (C) 2012-2021 Combodo SARL
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

/**
 * Class LegacySearchBlock
 *
 * Can be used to bring back the legacy search box in the console by using the following snippet:
 *   $oSearchBlock = new LegacySearchBlock($oFilter);
 *   $oSearchBlock->Display($oPage, 'some_html_id');
 *
 * Note: Those methods are inspired by the search box as in iTop 2.4.
 */
class LegacySearchBlock
{
    /** @var DBSearch $oFilter */
    protected $oFilter;
    /** @var CMDBObjectSet $oSet */
    protected $oSet;
    /** @var array $aExtraParams */
    protected $aExtraParams;

	CONST XML_LEGACY_VERSION = '1.7';

	/**
	 * Compare static::XML_LEGACY_VERSION with ITOP_DESIGN_LATEST_VERSION and returns true if the later is <= to the former.
	 * If static::XML_LEGACY_VERSION, return false
	 * 
	 * @return bool
	 *
	 * @since 1.1.0
	 */
	public static function UseLegacy(){
		return static::XML_LEGACY_VERSION !== '' ? version_compare(ITOP_DESIGN_LATEST_VERSION, static::XML_LEGACY_VERSION, '<=') : false;
	}
	
    /**
     * LegacySearchBlock constructor.
     *
     * @param DBSearch $oFilter
     * @param array $aExtraParams
     */
    public function __construct(DBSearch $oFilter, $aExtraParams = array())
    {
        $this->oFilter = $oFilter;
        $this->aExtraParams = $aExtraParams;
    }

    /**
     * @param WebPage $oPage
     * @param $sId
     *
     * @throws CoreException
     * @throws DictExceptionMissingString
     */
    public function Display(WebPage $oPage, $sId)
    {
        if($this->oSet === null)
        {
            $this->oSet = new CMDBObjectSet($this->oFilter);
        }

        $oPage->add_linked_script(utils::GetAbsoluteUrlModulesRoot().'/itop-legacy-search-base/js/legacy-search.js');

	    if(static::UseLegacy()){
		    $oPage->add_saas('env-'.utils::GetCurrentEnvironment().'/itop-legacy-search-base/legacy/css/legacy-search.scss');
	    }
	    else{
		    $oPage->add_saas('env-'.utils::GetCurrentEnvironment().'/itop-legacy-search-base/css/legacy-search.scss');
	    }
	    if (version_compare(ITOP_DESIGN_LATEST_VERSION , '3.0') < 0) {
	    $sStyle = (isset($this->aExtraParams['open']) && ($this->aExtraParams['open'] == 'true')) ? 'SearchDrawer' : 'SearchDrawer DrawerClosed';
        $sHtml = "<div id=\"ds_$sId\" class=\"$sStyle\">\n";
        $oPage->add_ready_script(
<<<EOF
		$("#dh_$sId").click( function() {
			$("#ds_$sId").slideToggle('normal', function() { 
			    $("#ds_$sId").parent().resize();
			    /* Not part of the module */
			    /*FixSearchFormsDisposition();*/ 
			    $("#dh_$sId").trigger('toggle_complete'); } );
			$("#dh_$sId").toggleClass('open');
	    		});
EOF
        );
        $this->aExtraParams['currentId'] = $sId;
        $sHtml .= static::GetSearchForm($oPage, $this->oSet, $this->aExtraParams);
        $sHtml .= "</div>\n";
        $sHtml .= "<div class=\"HRDrawer\"></div>\n";
        $sHtml .= "<div id=\"dh_$sId\" class=\"DrawerHandle\">".Dict::S('UI:SearchToggle')."</div>\n";
	    } else {
        $sStyle = (isset($this->aExtraParams['open']) && ($this->aExtraParams['open'] == 'true')) ? ' ibo-is-opened' : '';
        $sHtml = "<div id=\"ds_$sId\" class=\" ibo-panel ibo-content-block ibo-block ibo-search-form-panel display_block ibo-is-cyan ibo-is-opened $sStyle\">\n";

        $oPage->add_ready_script(
<<<EOF
		$("#ds_$sId .ibo-panel--header").click( function() {
			$("#ds_$sId").toggleClass('ibo-is-opened');
		});
EOF
        );
        $this->aExtraParams['currentId'] = $sId;
        $sHtml .= static::GetSearchForm($oPage, $this->oSet, $this->aExtraParams);
        $sHtml .= "</div>\n";
        $sHtml .= "<div id=\"dh_$sId\" class=\"ibo-is-hidden\">".Dict::S('UI:SearchToggle')."</div>\n";
}
        $oPage->add($sHtml);
    }

    /**
     * @param WebPage $oPage
     * @param CMDBObjectSet $oSet
     * @param array $aExtraParams
     *
     * @return string
     *
     * @throws CoreException
     * @throws DictExceptionMissingString
     * @throws Exception
     */
    public static function GetSearchForm(WebPage $oPage, CMDBObjectSet $oSet, $aExtraParams = array())
    {
        static $iSearchFormId = 0;
        $bMultiSelect = false;
        $oAppContext = new ApplicationContext();
        $sHtml = '';
        $sClassName = $oSet->GetFilter()->GetClass();

        // Simple search form
        if (isset($aExtraParams['currentId']))
        {
            $sSearchFormId = $aExtraParams['currentId'];
        }
        else
        {
            $iSearchFormId =  utils::GetUniqueId();
            $sSearchFormId = 'SimpleSearchForm'.$iSearchFormId;
            $sHtml .= "<div id=\"ds_$sSearchFormId\" class=\"mini_tab{$iSearchFormId}\">\n";
        }
        // Check if the current class has some sub-classes
        if (isset($aExtraParams['baseClass']))
        {
            $sRootClass = $aExtraParams['baseClass'];
        }
        else
        {
            $sRootClass = $sClassName;
        }
        $aSubClasses = MetaModel::GetSubclasses($sRootClass);
        if (count($aSubClasses) > 0)
        {
            $aOptions = array();
            $aOptions[MetaModel::GetName($sRootClass)] = "<option value=\"$sRootClass\">".MetaModel::GetName($sRootClass)."</options>\n";
            foreach($aSubClasses as $sSubclassName)
            {
                $aOptions[MetaModel::GetName($sSubclassName)] = "<option value=\"$sSubclassName\">".MetaModel::GetName($sSubclassName)."</options>\n";
            }
            $aOptions[MetaModel::GetName($sClassName)] = "<option selected value=\"$sClassName\">".MetaModel::GetName($sClassName)."</options>\n";
            ksort($aOptions);
            $sContext = $oAppContext->GetForLink();
            $sClassesCombo = "<select name=\"class\" onChange=\"ReloadSearchForm('$sSearchFormId', this.value, '$sRootClass', '$sContext')\">\n".implode('', $aOptions)."</select>\n";
        }
        else
        {
            $sClassesCombo = MetaModel::GetName($sClassName);
        }
        $oUnlimitedFilter = new DBObjectSearch($sClassName);
        $sAction = (isset($aExtraParams['action'])) ? $aExtraParams['action'] : utils::GetAbsoluteUrlAppRoot().'pages/UI.php';
        $index = 0;
        if (version_compare(ITOP_DESIGN_LATEST_VERSION , '3.0') < 0) {
			$sHtml .= "<form id=\"fs_{$sSearchFormId}\" action=\"{$sAction}\">\n"; // Don't use $_SERVER['SCRIPT_NAME'] since the form may be called asynchronously (from ajax.php)
			$sHtml .= "<h2>".Dict::Format('UI:SearchFor_Class_Objects', $sClassesCombo)."</h2>\n";
	        $sHtml .= "<div>\n";
        } else {
			$sHtml .= "<div class='ibo-panel--header'><div class=''ibo-panel--titles'><div class='ibo-panel--title'>".Dict::Format('UI:SearchFor_Class_Objects', $sClassesCombo)."</div></div></div>\n";
			$sHtml .= "<div class='ibo-panel--body SearchDrawer'>\n";
        	$sHtml .= "<form id=\"fs_{$sSearchFormId}\" action=\"{$sAction}\">\n"; // Don't use $_SERVER['SCRIPT_NAME'] since the form may be called asynchronously (from ajax.php)
	     }

	    $aMapCriteria = array();
        $aList = MetaModel::GetZListItems($sClassName, 'standard_search');
        $aConsts = $oSet->ListConstantFields(); // Some fields are constants based on the query/context
        $sClassAlias = $oSet->GetFilter()->GetClassAlias();
        foreach($aList as $sFilterCode)
        {
            //$oAppContext->Reset($sFilterCode); // Make sure the same parameter will not be passed twice
            $sHtml .= '<div class="SearchAttribute" style="white-space: nowrap;padding:5px;display:inline-block;">';
            $sFilterValue = isset($aConsts[$sClassAlias][$sFilterCode]) ? $aConsts[$sClassAlias][$sFilterCode] : '';
            $sFilterValue = utils::ReadParam($sFilterCode, $sFilterValue, false, 'raw_data');
            $sFilterOpCode = null; // Use the default 'loose' OpCode
            if (empty($sFilterValue))
            {
                if (isset($aMapCriteria[$sFilterCode]))
                {
                    if (count($aMapCriteria[$sFilterCode]) > 1)
                    {
                        $sFilterValue = Dict::S('UI:SearchValue:Mixed');
                    }
                    else
                    {
                        $sFilterValue = $aMapCriteria[$sFilterCode][0]['value'];
                        $sFilterOpCode = $aMapCriteria[$sFilterCode][0]['opcode'];
                    }
                    // Todo: Investigate...
                    if ($sFilterCode != 'company')
                    {
                        $oUnlimitedFilter->AddCondition($sFilterCode, $sFilterValue, $sFilterOpCode);
                    }
                }
            }

            $oAttDef = MetaModel::GetAttributeDef($sClassName, $sFilterCode);
            if ($oAttDef->IsExternalKey(EXTKEY_ABSOLUTE))
            {
                $oKeyAttDef = $oAttDef->GetFinalAttDef();
                $sKeyAttClass = $oKeyAttDef->GetHostClass();
                $sKeyAttCode = $oKeyAttDef->GetCode();

                $sTargetClass = $oKeyAttDef->GetTargetClass();
                $oSearch = new DBObjectSearch($sTargetClass);
                $oSearch->SetModifierProperty('UserRightsGetSelectFilter', 'bSearchMode', true);
                $oAllowedValues = new DBObjectSet($oSearch);

                $sHtml .= "<label>".MetaModel::GetLabel($sKeyAttClass, $sKeyAttCode).":</label>&nbsp;";
                $aExtKeyParams = $aExtraParams;
                $aExtKeyParams['iFieldSize'] = $oKeyAttDef->GetMaxSize();
                $aExtKeyParams['iMinChars'] = $oKeyAttDef->GetMinAutoCompleteChars();
                $sHtml .= UIExtKeyWidget::DisplayFromAttCode($oPage, $sKeyAttCode, $sKeyAttClass, $oAttDef->GetLabel(), $oAllowedValues, $sFilterValue, $sSearchFormId.'search_'.$sFilterCode, false, $sFilterCode, '', $aExtKeyParams, true);
            }
            else
            {
                $aAllowedValues = $oAttDef->GetAllowedValues();//MetaModel::GetAllowedValues_flt($sClassName, $sFilterCode, $aExtraParams);
                if (is_null($aAllowedValues))
                {
                    // Any value is possible, display an input box
                    $sHtml .= "<label>".MetaModel::GetLabel($sClassName, $sFilterCode).":</label>&nbsp;<input class=\"textSearch\" name=\"$sFilterCode\" value=\"".htmlentities($sFilterValue, ENT_QUOTES, 'utf-8')."\"/>\n";
                }
                else
                {
                    //Enum field, display a multi-select combo
                    $sValue = "<select class=\"multiselect\" size=\"1\" name=\"{$sFilterCode}[]\" multiple>\n";
                    $bMultiSelect = true;
                    //$sValue .= "<option value=\"\">".Dict::S('UI:SearchValue:Any')."</option>\n";
                    asort($aAllowedValues);
                    foreach($aAllowedValues as $key => $value)
                    {
                        if (is_array($sFilterValue) && in_array($key, $sFilterValue))
                        {
                            $sSelected = ' selected';
                        }
                        else if ($sFilterValue == $key)
                        {
                            $sSelected = ' selected';
                        }
                        else
                        {
                            $sSelected = '';
                        }
                        $sValue .= "<option value=\"$key\" $sSelected >$value</option>\n";
                    }
                    $sValue .= "</select>\n";
                    $sHtml .= "<label>".MetaModel::GetLabel($sClassName, $sFilterCode).":</label>&nbsp;$sValue\n";
                }
            }
            unset($aExtraParams[$sFilterCode]);

            // Finally, add a tooltip if one is defined for this attribute definition
            $sTip = $oAttDef->GetHelpOnSmartSearch();
            if (strlen($sTip) > 0)
            {
                $sTip = addslashes($sTip);
                $sTip = str_replace(array("\n", "\r"), " ", $sTip);
                // :input does represent in form visible input (INPUT, SELECT, TEXTAREA)
	            if(self::UseLegacy()){
                    $oPage->add_ready_script("$('form#fs_$sSearchFormId :input[name={$sFilterCode}]').qtip( { content: '$sTip', show: 'mouseover', hide: 'mouseout', style: { name: 'dark', tip: 'leftTop' }, position: { corner: { target: 'rightMiddle', tooltip: 'leftTop' }} } );");
	            }
				else{
					$oPage->add_ready_script(
						<<<JS
							$('form#fs_$sSearchFormId :input[name={$sFilterCode}]')
								.attr('data-tooltip-html-enabled', true)
								.attr('data-tooltip-content', '$sTip');
							CombodoTooltip.InitTooltipFromMarkup($('form#fs_$sSearchFormId :input[name={$sFilterCode}]'));

JS
					);
				}
            }
            $index++;
            $sHtml .= '</div> ';
        }
        if (version_compare(ITOP_DESIGN_LATEST_VERSION , '3.0') < 0) {
         	$sHtml .= "</div>\n";
         }
          $sHtml .= "<p align=\"right\"><input type=\"submit\" class=\"ibo-button ibo-is-regular ibo-is-primary\" value=\"".Dict::S('UI:Button:Search')."\"></p>\n";
        if (isset($aExtraParams['table_id']))
        {
            // Rename to avoid collisions...
            $aExtraParams['_table_id_'] = $aExtraParams['table_id'];
            unset($aExtraParams['table_id']);
        }
        foreach($aExtraParams as $sName => $sValue)
        {
            if (is_scalar($sValue))
            {
                $sHtml .= "<input type=\"hidden\" name=\"$sName\" value=\"".htmlentities($sValue, ENT_QUOTES, 'UTF-8')."\" />\n";
            }
        }
        $sHtml .= "<input type=\"hidden\" name=\"class\" value=\"$sClassName\" />\n";
        $sHtml .= "<input type=\"hidden\" name=\"dosearch\" value=\"1\" />\n";
        $sHtml .= "<input type=\"hidden\" name=\"operation\" value=\"search_form\" />\n";
        $sHtml .= $oAppContext->GetForForm();
        $sHtml .= "</form>\n";
        if (version_compare(ITOP_DESIGN_LATEST_VERSION , '3.0') >= 0) {
			$sHtml .= "</div>\n";
		}
        if (!isset($aExtraParams['currentId']))
        {
            $sHtml .= "</div><!-- Simple search form -->\n";
        }
        if ($bMultiSelect)
        {
            $aOptions = array(
                'header' => true,
                'checkAllText' => Dict::S('UI:SearchValue:CheckAll'),
                'uncheckAllText' => Dict::S('UI:SearchValue:UncheckAll'),
                'noneSelectedText' => Dict::S('UI:SearchValue:Any'),
                'selectedText' => Dict::S('UI:SearchValue:NbSelected'),
                'selectedList' => 1,
            );
            $sJSOptions = json_encode($aOptions);
            $oPage->add_ready_script("$('.multiselect').multiselect($sJSOptions);");
        }
        return $sHtml;
    }
}