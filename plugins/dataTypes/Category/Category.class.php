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
		$placeholderStr = $generationContextData["generationOptions"];

        $randomCategories = $this->getRandomCategories($this->getCategories());
		while (preg_match("/category_id/", $placeholderStr)) {
            $placeholderStr = preg_replace("/category_id/", $randomCategories['category_id'], $placeholderStr, 1);
		}
        while (preg_match("/name/", $placeholderStr)) {
            $placeholderStr = preg_replace("/name/", $randomCategories['name'], $placeholderStr, 1);
        }

		// in case the user entered multiple | separated formats, pick one
		$formats = explode("|", $placeholderStr);
		$chosenFormat = $formats[0];
		if (count($formats) > 1) {
			$chosenFormat = $formats[mt_rand(0, count($formats)-1)];
		}

		return array(
			"display" => trim($chosenFormat)
		);
	}


	public function getRowGenerationOptionsUI($generator, $post, $colNum, $numCols) {
		if (!isset($post["dtOption_$colNum"]) || empty($post["dtOption_$colNum"])) {
			return false;
		}
		return $post["dtOption_$colNum"];
	}

	public function getRowGenerationOptionsAPI($generator, $json, $numCols) {
		if (empty($json->settings->placeholder)) {
			return false;
		}
		return $json->settings->placeholder;
	}


	public function getDataTypeMetadata() {
		return array(
			"SQLField" => "varchar(255) default NULL",
			"SQLField_Oracle" => "varchar2(255) default NULL",
			"SQLField_MSSQL" => "VARCHAR(255) NULL"
		);
	}

	public function getExampleColumnHTML() {
		$L = Core::$language->getCurrentLanguageStrings();

		$html =<<< END
	<select name="dtExample_%ROW%" id="dtExample_%ROW%">
		<option value="">{$L["please_select"]}</option>
		<option value="Name">Name</option>
	</select>
END;
		return $html;
	}

	public function getOptionsColumnHTML() {
		return '<input type="text" name="dtOption_%ROW%" id="dtOption_%ROW%" style="width: 267px" />';
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
				$categories[] = [
                    'category_id' => $row["category_id"],
				    'name' => $row["name"],
				    'parentId' => $row['parent_id'],
                ];
			}

			$this->categories  = $categories;
		}
	}

	private function getRandomCategories($nameArray) {
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
