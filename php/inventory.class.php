<?php/** *  *  * @author: Sekiphp * @created: 4.4.2015 * @last_update: 8.4.2015   */ class Inventory{  private $playerID;  	public $status = FALSE;  public function __construct($playerID){    $this -> playerID = $playerID;  }  public function sellItem($id, $sub, $qty, $price){    $res = $this->checkQtyOwner($id, $sub, $qty);		if($res == FALSE) return "bad_qty";				$res = $this->checkPrice($id, $sub, $price);		if($res == FALSE) return "bad_price";        		$this -> createOffer($id, $sub, $qty, $price);		$this -> updateInventory($id, $sub, $qty);				$this->status = TRUE;		return "sell_ok";  }    /**   * Check if player have too much items then he sould like to sell	 * Check qty and owner   */       private function checkQtyOwner($id, $sub, $qty){			if($qty <= 0) return FALSE;    $sql = "      SELECT qty       FROM " . TABLE_ITEMS . "       WHERE         playerID = :playerID AND         itemID = :itemID AND         itemDamage = :itemDamage    ";    $where = array(      ":playerID" => $this->playerID,      ":itemID" => $id,      ":itemDamage" => $sub,    );         $item = DB::assoc(DB::query($sql, $where));		return ($item['qty'] >= $qty);  }		private function checkPrice($id, $sub, $price){		if($price <= 0) return FALSE;		return TRUE;	}    private function updateInventory($id, $sub, $qty){		$sql = "			UPDATE " .  TABLE_ITEMS. "			SET 				qty = (qty - $qty) 			WHERE 				playerID = :playerID AND 				itemID = :itemID AND 				itemDamage = :itemDamage		";		$where = array(			":playerID" => $this->playerID,			":itemID" => $id,			":itemDamage" => $sub, 		);		DB::query($sql, $where);				// delete rows where is zero pieces		$sql = "			DELETE FROM " . TABLE_ITEMS . " WHERE qty = 0;		";		DB::query($sql);  }		private function createOffer($id, $sub, $qty, $price){  $valuesInfo = array(":playerID" => $this->playerID, ":itemID" => $id, ":itemDamage" => $sub, ":price" => $price);  $sqlInfo =  "SELECT qty        FROM " . TABLE_OFFERS . "         WHERE        playerID = :playerID AND           itemID = :itemID AND           itemDamage = :itemDamage AND          price = :price          ";     $info = DB::assoc(DB::query($sqlInfo, $valuesInfo));    if(!$info){      $sql = "      INSERT INTO " . TABLE_OFFERS . "        SELECT         '' AS id, playerID, itemID, itemDamage, :qty AS qty, itemMeta, enchantments, lore, :price AS price         FROM " . TABLE_ITEMS . "         WHERE           playerID = :playerID AND           itemID = :itemID AND           itemDamage = :itemDamage      ";		  $valuesAndWhere = array(			 ":playerID" => $this->playerID, 			 ":itemID" => $id, 			 ":itemDamage" => $sub,         ":qty" => $qty,			 ":price" => $price, 		  );	   DB::query($sql, $valuesAndWhere);    }else{      $vys =  $info['qty'] + $qty;      $update =  array(":itemID" => $id, ":itemDamage" => $sub, ":playerID" => $this->playerID, ":qty" => $vys,":price" => $price);      DB::query("UPDATE " .  TABLE_OFFERS . ' SET qty = :qty WHERE itemID = :itemID AND itemDamage = :itemDamage AND playerID = :playerID AND price = :price ', $update);    }	}}