<?php
class ModelReportExportXLS extends Model{

	public function getOrders($data = array()){
		$sql = "SELECT * FROM `" . DB_PREFIX . "order` AS o ";
		$sql.= "LEFT JOIN `" . DB_PREFIX . "order_status` AS os ON (os.order_status_id = o.order_status_id AND os.language_id = '" . (int)$this->config->get('config_language_id') . "') ";

		if (!empty($data['filter_order_status_id'])) {
			$sql .= " WHERE o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " WHERE o.order_status_id > '0'";
		}

		if(!empty($data['filter_date_start'])){
			$sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}
		if(!empty($data['filter_date_end'])){
			$sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}

		$sql .= " ORDER BY DATE(date_added)";

		$query = $this->db->query($sql);
		return $query->rows;
	}


	public function getOrderHistory($order_id){
		$sql = "SELECT * FROM `" . DB_PREFIX . "order_history` WHERE order_status_id=3 AND order_id=".$order_id;
		$query = $this->db->query($sql);
		return $query->rows;
	}


	public function getOrder($order_id){
		$sql = "SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id=" . (int)$order_id;
		$query = $this->db->query($sql);
		return $query->rows;
	}


	public function getTotalOrders(){
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order`");
		return $query->row['total'];
	}


	public function getTotalProductFromOrder($order_id){
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order_product` WHERE order_id=" . (int)$order_id);
		return $query->row['total'];
	}


	public function getProductListFromOrder($order_id){
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_product` WHERE order_id=" . (int)$order_id);
		return $query->rows;
	}
}
?>