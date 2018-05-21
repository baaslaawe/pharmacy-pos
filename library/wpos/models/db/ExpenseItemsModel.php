<?php
/**
 * ExpensesModel is part of Wallace Point of Sale system (WPOS) API
 *
 * ExpensesModel extends the DbConfig PDO class to interact with the config DB table
 *
 * WallacePOS is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3.0 of the License, or (at your option) any later version.
 *
 * WallacePOS is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details:
 * <https://www.gnu.org/licenses/lgpl.html>
 *
 * @package    wpos
 * @copyright  Copyright (c) 2014 WallaceIT. (https://wallaceit.com.au)

 * @link       https://wallacepos.com
 * @author     Michael B Wallace <micwallace@gmx.com>
 * @since      File available since 18/04/16 4:24 PM
 */
class ExpenseItemsModel extends DbConfig
{

    /**
     * @var array
     */
    protected $_columns = ['id', 'expenseid', 'notes', 'status', 'locationid', 'userid', 'ref', 'dt'];

    /**
     * Init the DB
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $expenseid
     * @param $ref
     * @param $amount
     * @param $notes
     * @param $status
     * @param $locationid
     * @param $userid
     * @param $dt
     * @return bool|string Returns false on an unexpected failure, returns -1 if a unique constraint in the database fails, or the new rows id if the insert is successful
     */
    public function create($expenseid, $ref, $amount, $notes, $status, $locationid, $userid, $dt)
    {
        $sql          = "INSERT INTO expenses_items (`expenseid`, `ref`, `amount`, `notes`, `status`, `locationid`, `userid`, `dt`) VALUES (:expenseid, :ref, :amount, :notes, :status, :locationid, :userid, :dt);";
        $placeholders = [":expenseid"=>$expenseid, ":ref"=>$ref, ":amount"=>$amount, ":notes"=>$notes, ":status"=>$status, ":locationid"=>$locationid, ":userid"=>$userid, ":dt"=>$dt];

        return $this->insert($sql, $placeholders);
    }

    /**
     * @param null $Id
     * @param null $ref
     * @return array|bool Returns false on an unexpected failure or an array of selected rows
     */
    public function get($Id = null, $ref) {
        $sql = 'SELECT i.*, COUNT(i.amount) as total FROM expenses as e LEFT OUTER JOIN expenses_items as i ON e.id=i.expenseid';
        $placeholders = [];
        if ($Id !== null) {
            if (empty($placeholders)) {
                $sql .= ' WHERE';
            }
            $sql .= ' e.id =:id';
            $placeholders[':id'] = $Id;
        }
        if ($ref !== null){
            if (empty($placeholders)) {
                $sql .= ' WHERE';
            }
            $sql .= ' ref =:ref';
            $placeholders[':ref'] = $ref;
        }

        return $this->select($sql, $placeholders);
    }

    /**
     * @param $id
     * @param $expenseid
     * @param $ref
     * @param $amount
     * @param $notes
     * @param $status
     * @param $locationid
     * @param $userid
     * @param $dt
     * @return bool|int Returns false on an unexpected failure or number of affected rows
     */
    public function edit($id, $expenseid, $ref, $amount, $notes, $status, $locationid, $userid, $dt)
    {

        $sql = "UPDATE expenses_items SET `expenseid`=:expenseid, `ref`=:ref, `amount`=:amount, `notes`=:notes, `status`=:status, `locationid`=:locationid, `userid`=:userid, `dt`=:id WHERE id=:id;";
        $placeholders = [":id"=>$id, "expenseid"=>$expenseid, ":ref"=>$ref, ":amount"=>$amount, ":notes"=>$notes, ":status"=>$status, ":locationid"=>$locationid, ":userid"=>$userid, ":dt"=>$dt];

        return $this->update($sql, $placeholders);
    }

    /**
     * @param null $id
     * @return bool|int Returns false on an unexpected failure or number of affected rows
     */
    public function remove($id = null)
    {
        $placeholders = [];
        $sql = "DELETE FROM expenses_items WHERE";
        if (is_numeric($id)){
            $sql .= " `id`=:id;";
            $placeholders[":id"] = $id;
        } else if (is_array($id)) {
            $id = array_map([$this->_db, 'quote'], $id);
            $sql .= " `id` IN (" . implode(', ', $id) . ");";
        } else {
            return false;
        }

        return $this->delete($sql, $placeholders);

    }

}