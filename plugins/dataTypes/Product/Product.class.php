<?php

/**
 * @package DataTypes
 */

class DataType_Product extends DataTypePlugin {

	protected $isEnabled = true;
	protected $dataTypeName = "Product";
	protected $hasHelpDialog = true;
	protected $dataTypeFieldGroup = "human_data";
	protected $dataTypeFieldGroupOrder = 116;

	/**
	 * @var array
	 */
	private $names;

    /**
     * @var array
     */
    private $products;

    /**
	 * @param string $runtimeContext "generation" or "ui"
	 */
	public function __construct($runtimeContext) {
		parent::__construct($runtimeContext);
		if ($runtimeContext == "generation") {
			self::initProducts();
		}
	}


	public function generate($generator, $generationContextData) {
		$placeholderStr = $generationContextData["generationOptions"];

        $randomProducts = $this->getRandomProducts($this->getCategories());
		while (preg_match("/product_id/", $placeholderStr)) {
            $placeholderStr = preg_replace("/product_id/", $randomProducts['product_id'], $placeholderStr, 1);
		}
        while (preg_match("/name/", $placeholderStr)) {
            $placeholderStr = preg_replace("/name/", $randomProducts['name'], $placeholderStr, 1);
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

	public function getProducts() {
		return $this->products;
	}

	// -------- private member functions ---------

	/**
	 * Called when instantiating the plugin during data generation. Set the firstNames, maleNames and
	 * femaleNames.
	 */
	private function initProducts() {
		$prefix = Core::getDbTablePrefix();
		$response = Core::$db->query("
			SELECT *
			FROM   {$prefix}categories
		");

		if ($response["success"]) {
			$products = array();
			while ($row = mysqli_fetch_assoc($response["results"])) {
				$products[] = [
                    'product_id' => $row["product_id"],
				    'name' => $row["name"],
                ];
			}

			$this->products  = $products;
		}
	}

	private function getRandomProducts($nameArray) {
		return $nameArray[mt_rand(0, count($nameArray)-1)];
	}


	/**
	 * Called during installation. This creates and populates the first_names and last_names DB tables.
	 *
	 * @return array [0] success / error (boolean)
	 *               [1] the error message, if there was an error
	 */
	public static function install() {

        $directoryIterator = new \DirectoryIterator(realpath(__DIR__ . '/../../data/products/'));

        $items = [];
        foreach ($directoryIterator as $info) {
            if ($info->isFile()) {
				gc_collect_cycles();
                $fileName = $info->getPath() . DIRECTORY_SEPARATOR  . $info->getBasename();
                $json = file_get_contents($fileName);
				$newItems = json_decode($json, true);
				$items = array_merge($items, $newItems);
            }
        }
        $products = [];
        foreach ($items as $product) {
            if ($product['active'] === false) {
                continue;
            };

            $products[] = sprintf('("%s","%s","%s","%s")',
				$product['productId'],
				str_replace('"',"",$product['name']),
				$product['long_description'],
				$product['image']
			)
			;
        }
        $products = implode(',', $products);

		$prefix = Core::getDbTablePrefix();

		// always clear out the previous tables, just in case
		$rollbackQueries = array();
		$rollbackQueries[] = "DROP TABLE {$prefix}products";
		Core::$db->query($rollbackQueries);

        $queries = array();
		$queries[] = "
			CREATE TABLE {$prefix}products (
				id mediumint(9) NOT NULL auto_increment,
				product_id varchar(50) NOT NULL default '',
				name varchar(255) NOT NULL default '',
				long_description TEXT NOT NULL default '',
				image TEXT NOT NULL default '',
				PRIMARY KEY (id)
			)
		";
		$queries[] = "INSERT INTO {$prefix}products (product_id, name, long_description, image)
			VALUES $products";

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
		<td><h4>Product Id</h4></td>
		<td>Product Id</td>
	</tr>
	</table>
EOF;

		return $content;
	}
}
