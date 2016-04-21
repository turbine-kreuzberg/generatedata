<?php

/**
 * @author Bernd Alter <bernd.alter@votum.de>
 * @package DataTypes
 */
class DataType_Float extends DataTypePlugin {

	/**#@+
     * @access protected
     */
	protected $isEnabled = true;
	protected $dataTypeName = "Float";
	protected $dataTypeFieldGroup = "numeric";
	protected $dataTypeFieldGroupOrder = 31;
	protected $jsModules = array("Float.js");

	public function generate($generator, $generationContextData) {
		$options = $generationContextData["generationOptions"];

		if (strlen($options['decimalPoint']) === 0) {
			$options['decimalPoint'] = '.';
		}
		else {
			$options['decimalPoint'] = substr($options['decimalPoint'], 0, 1);

			if (!in_array($options['decimalPoint'],['.',','])) {
				$options['decimalPoint'] = '.';
			}
		}

		$val = mt_rand($options["min"], $options["max"]);

        if ($options['decimalPlaces'] > 0) {
            $val .= $options['decimalPoint'];
            $val .= Utils::generateRandomNumStr(str_repeat('x', $options['decimalPlaces']));;
        }

		return array(
			"display" => $val
		);
	}

	public function getRowGenerationOptionsUI($generator, $postdata, $column, $numCols) {
        if ((empty($postdata["dtFloatMin_$column"]) && $postdata["dtFloatMin_$column"] !== "0") ||
            (empty($postdata["dtFloatMax_$column"]) && $postdata["dtFloatMax_$column"] !== "0")) {
            return false;
        }
        if (!is_numeric($postdata["dtFloatMin_$column"]) || !is_numeric($postdata["dtFloatMax_$column"])) {
            return false;
        }
        $options = array(
            "min" => $postdata["dtFloatMin_$column"],
            "max" => $postdata["dtFloatMax_$column"],
            "decimalPoint"  => isset($postdata["dtFloatDecimalPoint_$column"]) ? $postdata["dtFloatDecimalPoint_$column"] : '.',
            "decimalPlaces"  => isset($postdata["dtFloatDecimalPlaces_$column"]) ? $postdata["dtFloatDecimalPlaces_$column"] : 2,
        );
        return $options;
	}

	public function getRowGenerationOptionsAPI($generator, $json, $numCols) {
		if (empty($json->settings->max)) {
			return false;
		}

        $options = array(
            "min"           => property_exists($json->settings->min) ? $json->settings->min : 0,
            "max"           => $json->settings->max,
            "decimalPoint"  => property_exists($json->settings->decimalPoint) ? $json->settings->decimalPoint : '.',
            "decimalPlaces" => property_exists($json->settings->decimalPlaces) ? $json->settings->decimalPlaces : 2,
        );

		return $options;
	}

    public function getOptionsColumnHTML() {
        $html =<<<END
&nbsp;{$this->L["between"]} <input type="text" name="dtFloatMin_%ROW%" id="dtFloatMin_%ROW%" style="width: 30px" value="1" />
{$this->L["and"]} <input type="text" name="dtFloatMax_%ROW%" id="dtFloatMax_%ROW%" style="width: 30px" value="100" />
<br/>&nbsp;{$this->L["DecimalPlaces"]} <input type="text" name="dtFloatDecimalPlaces_%ROW%" id="dtFloatDecimalPlaces_%ROW%" style="width: 30px" value="2" />
<br/>&nbsp;{$this->L["DecimalPoint"]} <select name="dtFloatDecimalPoint_%ROW%" id="dtFloatDecimalPoint_%ROW%"> <option value=".">{$this->L["DecimalPointDot"]}</option> <option value=",">{$this->L["DecimalPointComma"]}</option> </select>
END;
        return $html;
    }

	public function getDataTypeMetadata() {
        return array(
            "type" => "numeric",
            "SQLField" => "mediumint default NULL",
            "SQLField_Oracle" => "varchar2(50) default NULL",
            "SQLField_MSSQL" => "INTEGER NULL",
            "SQLField_Postgres" => "integer NULL"
        );
	}

	public function getHelpHTML() {
		$content =<<<EOF
			<p>
				{$this->L["help_intro"]}
			</p>
EOF;

		return $content;
	}
}
