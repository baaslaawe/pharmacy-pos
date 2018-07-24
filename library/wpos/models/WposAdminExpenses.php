<?php
/**
 * JsonData is part of Wallace Point of Sale system (WPOS) API
 *
 * JsonData is used for retrieving database tables into JSON for use by the pos client.
 * The device,location and tax functions are no longer used much as WposSetup now provides these values alongside the config.
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
 * @since      File available since 17/11/13 2:16 PM
 */
class WposAdminExpenses
{
    /**
     * @var ExpensesModel
     */
     private $expMdl;
    /**
    /**
     * @var ExpenseItemsModel
     */
     private $expItemMdl;
    /**
     * @var int deviceId
     */
    var $devid;
    /**
     * @var int locationId
     */
    var $locid;

    /**
     * @var mixed JSON object
     */
    var $data;

    /**
     * Decodes any provided JSON string
     */

    public function __construct($jsondata = null)
    {
        if ($jsondata !== null) {
            if (is_string($jsondata)) {
                $this->data = json_decode($jsondata);
            } else {
                $this->data = $jsondata;
            }
        }
        $this->expMdl = new ExpensesModel();
        $this->expItemMdl = new ExpenseItemsModel();
    }

    /**
     * Add a new expense
     * @param $result
     * @return mixed
     */
    public function addExpense($result)
    {
        $jsonval = new JsonValidate($this->data, '{"name":"", "type": ""}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }
        $qresult = $this->expMdl->create($this->data->name, $this->data->type);
        if ($qresult === false) {
            $result['error'] = "Could not add the expense: ".$this->expMdl->errorInfo;
        } else {
            $result['data'] = $this->getExpenseRecord($qresult);
            // log data
            Logger::write("Expense added with id:" . $this->data->id, "EXPENSE", json_encode($this->data));
        }
        return $result;
    }

    /**
     * Add a new expense
     * @param $result
     * @return mixed
     */
    public function addExpenseItem($result)
    {
        $jsonval = new JsonValidate($this->data, '{"expenseid":1, "ref": ""}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }
        $qresult = $this->expItemMdl->create($this->data->expenseid, $this->data->ref, $this->data->amount, $this->data->notes, $this->data->status, $this->data->locationid, $this->data->userid, $this->data->dt);
        if ($qresult === false) {
            $result['error'] = "Could not add the expense item: ".$this->expItemMdl->errorInfo;
        } else {
            $result['data'] = $this->getExpenseRecord($this->data->expenseid);
            // log data
            Logger::write("Expense item added with id:" . $this->data->id, "EXPENSE", json_encode($this->data));
        }
        return $result;
    }

    /**
     * @param $result
     * @return mixed Returns an array of expenses
     */
    public function getExpenses($result)
    {
        if(isset($this->data->refs)){
            $expenses = $this->expItemMdl->getByRef($this->data->refs);
        } else if(isset($this->data->stime)) {
            $expenses = $this->expMdl->get(null, $this->data->stime, $this->data->etime, true);
        }else {
            $expenses = $this->expMdl->get();
        }
        if (is_array($expenses)) {
            $expdata = [];
            foreach ($expenses as $expense) {
                $expdata[$expense['id']] = $expense;
            }
            $result['data'] = $expdata;
        } else {
            $result['error'] = $this->expMdl->errorInfo;
        }

        return $result;
    }

    /**
     * @param $result
     * @return mixed Returns an array of expenses
     */
    public function getExpenseItems($result)
    {
        $expenses = $this->expItemMdl->get($this->data->expenseid, $this->data->locationid);
        if (is_array($expenses)) {
            $expdata = [];
            foreach ($expenses as $expense) {
                $expdata[$expense['id']] = $expense;
            }
            $result['data'] = $expdata;
        } else {
            $result['error'] = $this->expMdl->errorInfo;
        }

        return $result;
    }

    /**
     * Update a expenses
     * @param $result
     * @return mixed
     */
    public function updateExpense($result)
    {
        $jsonval = new JsonValidate($this->data, '{"id":1, "name":""}');
        if (($errors = $jsonval->validate()) !== true) {
            $result['error'] = $errors;
            return $result;
        }
        $qresult = $this->expMdl->edit($this->data->id, $this->data->name, $this->data->type);
        if ($qresult === false) {
            $result['error'] = "Could not edit the expense: ".$this->expMdl->errorInfo;
        } else {
            $result['data'] = $this->getExpenseRecord($this->data->id);
            // log data
            Logger::write("Expense updated with id:" . $this->data->id, "EXPENSE", json_encode($this->data));
        }
        return $result;
    }

    /**
     * Update a expenses
     * @param $result
     * @return mixed
     */
    public function updateExpenseItem($result)
    {
        $qresult = $this->expItemMdl->edit($this->data->id,$this->data->expenseid, $this->data->ref, $this->data->amount, $this->data->notes, $this->data->status, $this->data->locationid, $this->data->userid, $this->data->dt);
        if ($qresult === false) {
            $result['error'] = "Could not edit the expense: ".$this->expMdl->errorInfo;
        } else {
            $result['data'] = $this->getExpenseRecord($this->data->id);
            // log data
            Logger::write("Expense updated with id:" . $this->data->id, "EXPENSE", json_encode($this->data));
        }
        return $result;
    }
    /**
     * Returns expenses array by ID
     * @param $id
     * @return mixed
     */
    private function getExpenseRecord($id){
        $result = $this->expMdl->get($id)[0];
        return $result;
    }

    /**
     * Delete expenses
     * @param $result
     * @return mixed
     */
    public function deleteExpenses($result)
    {
        // validate input
        if (!is_numeric($this->data->id)) {
            if (isset($this->data->id)) {
                $ids = explode(",", $this->data->id);
                foreach ($ids as $id){
                    if (!is_numeric($id)){
                        $result['error'] = "A valid comma separated list of ids must be supplied";
                        return $result;
                    }
                }
            } else {
                $result['error'] = "A valid id, or comma separated list of ids must be supplied";
                return $result;
            }
        }

        $qresult = $this->expMdl->remove(isset($ids)?$ids:$this->data->id);
        if ($qresult === false) {
            $result['error'] = "Could not delete the expense: ".$this->expMdl->errorInfo;
        } else {
            $result['data'] = true;
            // log data
            Logger::write("Expenses(s) deleted with id:" . $this->data->id, "Expenses");
        }
        return $result;
    }

    /**
     * Delete expenses
     * @param $result
     * @return mixed
     */
    public function deleteExpenseItem($result)
    {
        // validate input
        if (!is_numeric($this->data->id)) {
            if (isset($this->data->id)) {
                $ids = explode(",", $this->data->id);
                foreach ($ids as $id){
                    if (!is_numeric($id)){
                        $result['error'] = "A valid comma separated list of ids must be supplied";
                        return $result;
                    }
                }
            } else {
                $result['error'] = "A valid id, or comma separated list of ids must be supplied";
                return $result;
            }
        }

        $qresult = $this->expItemMdl->remove(isset($ids)?$ids:$this->data->id);
        if ($qresult === false) {
            $result['error'] = "Could not delete the expense: ".$this->expMdl->errorInfo;
        } else {
            $result['data'] = true;
            // log data
            Logger::write("Expenses(s) deleted with id:" . $this->data->id, "Expenses");
        }
        return $result;
    }
}