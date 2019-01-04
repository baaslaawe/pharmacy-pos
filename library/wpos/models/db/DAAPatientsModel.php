<?php
/**
 * Created by PhpStorm.
 * User: joe
 * Date: 1/4/19
 * Time: 5:56 PM
 */


class DAAPatientsModel extends DbConfig
{

    /**
     * @var array of available columns
     */
    protected $_columns = ['id', 'saleid', 'saleitemid', 'customerid'];

    /**
     * Init the PDO object
     */
    public function __construct()
    {
        parent::__construct();
    }


    /**
     * @param $saleid
     * @param $saleitemid
     * @param $customerid
     *
     * @return bool|string Returns false on an unexpected failure, returns -1 if a unique constraint in the database fails, or the new rows id if the insert is successful
     */
    public function create($saleid, $saleitemid, $customerid)
    {
        $sql = "INSERT INTO daa_patients (saleid, saleitemid, customerid) VALUES (:saleid, :saleitemid, :customerid)";
        $placeholders = [
            ':saleid'       => $saleid,
            ':saleitemid'   => $saleitemid,
            ':customerid'   => $customerid
        ];

        return $this->insert($sql, $placeholders);
    }

    /**
     * @param int $limit
     * @param int $offset
     * @param null $saleid
     * @param null $sitemid
     *
     * @return array|bool Returns false on an unexpected failure or the rows found by the statement. Returns an empty array when nothing is found
     */
    public function get($stime, $etime)
    {
        $sql = 'SELECT p.id, c.name as customer, d.name as drug, d.qty as qty FROM daa_patients as p LEFT JOIN sale_items as d on p.saleitemid=d.id LEFT JOIN customers as c on c.id=p.customerid LEFT JOIN sales as s on d.saleid=s.id WHERE (s.processdt>= :stime AND s.processdt<= :etime) ';
        $placeholders = [":stime"=>$stime, ":etime"=>$etime];
        return $this->select($sql, $placeholders);
    }

    /**
     * @param $stime
     * @param $etime
     * @param bool $group (1 to group by category, 2 to group by supplier)
     * @param bool $novoids
     * @param null $ttype
     * @return array|bool Returns an array of stored items and their totals for a corresponding period, items that are not stored are added into the Misc group (ie id=0). Returns false on failure
     */
    public function getStoredItemTotals($stime, $etime, $group = 0, $novoids = true, $ttype=null){

        if ($group==2){
            $groupcol = "supplierid";
            $grouptable = "stored_suppliers";
        } else {
            $groupcol = "categoryid";
            $grouptable = "stored_categories";
        }

        $sql = "SELECT ".($group>0?'si.'.$groupcol.' AS groupid, p.name AS name':'i.storeditemid AS groupid, i.name AS name').", COALESCE(SUM(i.qty), 0) AS itemnum, COALESCE(SUM(s.total-s.discount), 0) AS itemtotal, COALESCE(SUM(s.discount), 0) AS discounttotal, COALESCE(SUM(i.tax_total), 0) AS taxtotal, COALESCE(SUM(i.refundqty), 0) AS refnum, COALESCE(SUM(i.unit*i.refundqty), 0) AS reftotal, COALESCE(GROUP_CONCAT(DISTINCT s.ref SEPARATOR ','),'') as refs";
        $sql.= ' FROM sale_items AS i LEFT JOIN sales AS s ON i.saleid=s.id'.($group>0 ? ' LEFT JOIN stored_items AS si ON i.storeditemid=si.id LEFT JOIN '.$grouptable.' AS p ON si.'.$groupcol.'=p.id' : '').' WHERE (s.processdt>= :stime AND s.processdt<= :etime) '.($novoids?'AND s.status!=3':'');
        $placeholders = [":stime"=>$stime, ":etime"=>$etime];

        if ($ttype!=null){
            $sql .= ' AND s.type=:type';
            $placeholders[':type'] = $ttype;
        }

        $sql.= ' GROUP BY groupid, name';

        return $this->select($sql, $placeholders);
    }

}