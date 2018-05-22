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
class ExpensesModel extends DbConfig
{

    /**
     * @var array
     */
    protected $_columns = ['id', 'name', 'dt'];

    /**
     * Init the DB
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param $name
     * @return bool|string Returns false on an unexpected failure, returns -1 if a unique constraint in the database fails, or the new rows id if the insert is successful
     */
    public function create($name)
    {
        $sql          = "INSERT INTO expenses (`name`, `dt`) VALUES (:name, now());";
        $placeholders = [":name"=>$name];

        return $this->insert($sql, $placeholders);
    }

    /**
     * @param null $Id
     * @param null $stime
     * @param null $etime
     * @return array|bool Returns false on an unexpected failure or an array of selected rows
     */
    public function get($Id = null, $stime = null, $etime = null) {
        $sql = "SELECT e.*, COUNT(i.id) as enum, COALESCE(SUM(i.amount), 0) as total, COALESCE(GROUP_CONCAT(ref SEPARATOR ','),'') as refs FROM expenses as e LEFT OUTER JOIN expenses_items as i ON e.id=i.expenseid";
        $placeholders = [];
        if ($Id !== null) {
            if (empty($placeholders)) {
                $sql .= ' WHERE';
            }
            $sql .= ' e.id =:id';
            $placeholders[':id'] = $Id;
        }

        if ($stime !== null && $etime !== null) {
            if (empty($placeholders)) {
                $sql .= ' WHERE';
            }
            $sql .= ' (i.dt>= :stime AND i.dt<= :etime)';
            $placeholders[':stime'] = $stime;
            $placeholders[':etime'] = $etime;
        }

        $sql.=" GROUP BY e.id";

        return $this->select($sql, $placeholders);
    }

    /**
     * @param $id
     * @param $name
     * @return bool|int Returns false on an unexpected failure or number of affected rows
     */
    public function edit($id, $name)
    {

        $sql = "UPDATE expenses SET name=:name WHERE id=:id;";
        $placeholders = [":id"=>$id, ":name"=>$name];

        return $this->update($sql, $placeholders);
    }

    /**
     * @param null $id
     * @return bool|int Returns false on an unexpected failure or number of affected rows
     */
    public function remove($id = null)
    {
        $placeholders = [];
        $sql = "DELETE FROM expenses WHERE";
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