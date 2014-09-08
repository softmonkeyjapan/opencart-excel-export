<?php
/**
* This is Export XLSX model
*
* Use it to export your orders into an Excel file compatible with Excel 2003-2007-2010
*
* PHP 5
*
* OpenCart(tm) : Webshop (http://www.opencart.com/)
*
* @author        Loic KARTONO. http://www.kartono-loic.com
* @copyright     Licence MIT
* @link          https://github.com/softmonkeyjapan/opencart-excel-export
* @package       admin/report
* @since         OpenCart(tm) 1.5.4.1
*/
class ModelReportExportXLS extends Model
{

	/**
	 * Get all orders
	 *
	 * @param  array  $data
	 * @return rows
	 */
	public function getOrders($data = array())
	{
		$sql  = "SELECT * FROM `" . DB_PREFIX . "order` AS o ";
		$sql .= "LEFT JOIN `" . DB_PREFIX . "order_status` AS os ON (os.order_status_id = o.order_status_id AND os.language_id = '" . (int)$this->config->get('config_language_id') . "') ";

		if (!empty($data['filter_order_status_id']))
		{
			$sql .= " WHERE o.order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		}
		else
		{
			$sql .= " WHERE o.order_status_id > '0'";
		}

		if (!empty($data['filter_date_start']))
		{
			$sql .= " AND DATE(o.date_added) >= '" . $this->db->escape($data['filter_date_start']) . "'";
		}

		if (!empty($data['filter_date_end']))
		{
			$sql .= " AND DATE(o.date_added) <= '" . $this->db->escape($data['filter_date_end']) . "'";
		}

		$sql .= " ORDER BY DATE(date_added)";

		$query = $this->db->query($sql);
		return $query->rows;
	}

	/**
	 * Get history for a given order
	 *
	 * @param  integer  $order_id
	 * @return rows
	 */
	public function getOrderHistory($order_id)
	{
		$sql = "SELECT * FROM `" . DB_PREFIX . "order_history` WHERE order_status_id=3 AND order_id=".$order_id;
		$query = $this->db->query($sql);
		return $query->rows;
	}

	/**
	 * Get a given order
	 *
	 * @param  integer  $order_id
	 * @return rows
	 */
	public function getOrder($order_id)
	{
		$sql = "SELECT * FROM `" . DB_PREFIX . "order` WHERE order_id=" . (int)$order_id;
		$query = $this->db->query($sql);
		return $query->rows;
	}

	/**
	 * Get total
	 *
	 * @return result
	 */
	public function getTotalOrders()
	{
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order`");
		return $query->row['total'];
	}

	/**
	 * Get total for a specific order
	 *
	 * @param  integer  $order_id
	 * @return result
	 */
	public function getTotalProductFromOrder($order_id)
	{
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order_product` WHERE order_id=" . (int)$order_id);
		return $query->row['total'];
	}

	/**
	* Get product list
	*
	* @param  integer  $order_id
	* @return rows
	*/
	public function getProductListFromOrder($order_id)
	{
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_product` WHERE order_id=" . (int)$order_id);
		return $query->rows;
	}
}
