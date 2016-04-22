<?php

/**
 * @package DataTypes
 */

class DataType_Category extends DataTypePlugin {

	protected $isEnabled = true;
	protected $dataTypeName = "Category";
	protected $hasHelpDialog = true;
	protected $dataTypeFieldGroup = "human_data";
	protected $dataTypeFieldGroupOrder = 115;

	/**
	 * @var array
	 */
	private $names;

	/**
	 * @var array
	 */
	private $parentIds;

    /**
     * @var array
     */
    private $categories;

    /**
	 * @param string $runtimeContext "generation" or "ui"
	 */
	public function __construct($runtimeContext) {
		parent::__construct($runtimeContext);
		if ($runtimeContext == "generation") {
			self::initCategories();
		}
	}


	public function generate($generator, $generationContextData) {
		$options = $generationContextData["generationOptions"];

        $randomCategory = $this->getRandomCategory($this->getCategories());

		if (isset($randomCategory[$options['fieldName']])) {
			$value = $randomCategory[$options['fieldName']];
		}
		else {
			$value = '<Invalid field name>';
		}

		return array(
			"display" => $value
		);
	}


	public function getRowGenerationOptionsUI($generator, $postdata, $column, $numCols) {
		if ((empty($postdata["dtCategoryFieldName_".$column]))) {
			return false;
		}

		$options = array(
			"fieldName"  => $postdata["dtCategoryFieldName_". $column],
		);

		return $options;
	}

	public function getRowGenerationOptionsAPI($generator, $json, $numCols) {
		if (empty($json->settings->fieldName)) {
			return false;
		}

		$options = array(
			"fieldName" => $json->settings->fieldName,
		);

		return $options;
	}


	public function getDataTypeMetadata() {
		return array(
			"SQLField" => "varchar(255) default NULL",
			"SQLField_Oracle" => "varchar2(255) default NULL",
			"SQLField_MSSQL" => "VARCHAR(255) NULL"
		);
	}

	public function getOptionsColumnHTML() {
		$html =<<<END
&nbsp;{$this->L["DecimalPoint"]}
	<select name="dtCategoryFieldName_%ROW%" id="dtCategoryFieldName_%ROW%">
		<option value="name">{$this->L["CategoryName"]}</option>
		<option value="category_id">{$this->L["CategoryId"]}</option>
		<option value="parent_id">{$this->L["CategoryParentId"]}</option>
	</select>
END;
		return $html;
	}

	public function getNames() {
		return $this->names;
	}

	public function getCategories() {
		return $this->categories;
	}

	public function getParentIds() {
		return $this->parentIds;
	}


	// -------- private member functions ---------

	/**
	 * Called when instantiating the plugin during data generation. Set the firstNames, maleNames and
	 * femaleNames.
	 */
	private function initCategories() {
		$prefix = Core::getDbTablePrefix();
		$response = Core::$db->query("
			SELECT *
			FROM   {$prefix}categories
		");

		if ($response["success"]) {
			$names = array();
			$categories = array();
			$parentIds = array();
			while ($row = mysqli_fetch_assoc($response["results"])) {
				$categories[] = $row;
			}

			$this->categories  = $categories;
		}
	}

	private function getRandomCategory($nameArray) {
		return $nameArray[mt_rand(0, count($nameArray)-1)];
	}


	/**
	 * Called during installation. This creates and populates the first_names and last_names DB tables.
	 *
	 * @return array [0] success / error (boolean)
	 *               [1] the error message, if there was an error
	 */
	public static function install() {

        $directoryIterator = new \DirectoryIterator(realpath(__DIR__ . '/../../data/categories/'));

        $items = [];
        foreach ($directoryIterator as $info) {
            if ($info->isFile()) {
                $fileName = $info->getPath() . DIRECTORY_SEPARATOR  . $info->getBasename();
                $json = file_get_contents($fileName);
                $items = array_merge($items, json_decode($json, true));
            }
        }
        $categoryValues = [];
        foreach ($items as $category) {
            if ($category['active'] === false) {
                continue;
            };

            $categoryPath = $category['path'];
            if (count($categoryPath) === 1) {
                $parentCategory = 'ROOT';
            } else {
                $parentCategory = $categoryPath[count($categoryPath) - 2]['id'];
            }
            $categoryValues[] = sprintf('("%s","%s","%s")',$category['id'], str_replace('"',"",$category['name']), $parentCategory);
        }
        $categoryValues = implode(',', $categoryValues);

		$prefix = Core::getDbTablePrefix();

		// always clear out the previous tables, just in case
		$rollbackQueries = array();
		$rollbackQueries[] = "DROP TABLE {$prefix}categories";
		Core::$db->query($rollbackQueries);

        $queries = array();
		$queries[] = "
			CREATE TABLE {$prefix}categories (
				id mediumint(9) NOT NULL auto_increment,
				category_id varchar(50) NOT NULL default '',
				name varchar(255) NOT NULL default '',
				parent_id varchar(50) NOT NULL default '',
				PRIMARY KEY (id)
			)
		";
		$queries[] = "INSERT INTO {$prefix}categories (category_id, name, parent_id)
			VALUES $categoryValues";

		$response = Core::$db->query($queries, $rollbackQueries);

		if ($response["success"]) {
			return array(true, "");
		} else {
			return array(false, $response["errorMessage"]);
		}
	}


	public function getHelpHTML() {
		$content =<<<EOF
	<p>
	    {$this->L["DATA_TYPE"]["DESC"]}
		{$this->L["help_intro"]}
	</p>

	<table cellpadding="0" cellspacing="1">
	<tr>
		<td width="100"><h4>Name</h4></td>
		<td>Name</td>
	</tr>
	<tr>
		<td><h4>Category Id</h4></td>
		<td>Category Id</td>
	</tr>
	</table>
EOF;

		return $content;
	}
}
