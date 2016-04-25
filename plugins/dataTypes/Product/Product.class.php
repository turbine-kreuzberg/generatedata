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
		$options = $generationContextData["generationOptions"];

        $randomProduct = $this->getRandomProduct($this->getProducts());

		if (isset($randomProduct[$options['fieldName']])) {
			$value = $randomProduct[$options['fieldName']];
		}
		else {
			$value = '<Invalid field name>';
		}

		return array(
			"display" => $value
		);
	}


	public function getRowGenerationOptionsUI($generator, $postdata, $column, $numCols) {
		if ((empty($postdata["dtProductFieldName_".$column]))) {
			return false;
		}

		$options = array(
			"fieldName"  => $postdata["dtProductFieldName_". $column],
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
	<select name="dtProductFieldName_%ROW%" id="dtProductFieldName_%ROW%">
		<option value="name">{$this->L["ProductName"]}</option>
		<option value="product_id">{$this->L["ProductId"]}</option>
		<option value="image">{$this->L["ProductImage"]}</option>
	</select>
END;
		return $html;
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
			FROM   {$prefix}products
		");

		if ($response["success"]) {
			$products = array();
			while ($row = mysqli_fetch_assoc($response["results"])) {
				$products[] = $row;
			}

			$this->products  = $products;
		}
	}

	private function getRandomProduct($nameArray) {
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
