<?php
/**
 * Created by JetBrains PhpStorm.
 * User: andre
 * Date: 19/06/13
 * Time: 18:23
 * To change this template use File | Settings | File Templates.
 */
namespace Walker;


/**
 * A class to manage data storage in array
 *
 * @author André Cianfarani <a.cianfarani@c2is.fr>
 *
 * @api
 */
class Storage extends Walker
{
    protected $varStored;
    public function __construct()
    {

    }
    public function addVarToStore($varName)
    {
        $this->varStored[$varName] = array();
    }
    public function addColumn($varName,$colName)
    {
        $this->varStored[$varName."Desc"][] = $colName;
    }
    public function getColumns($varName)
    {
        return $this->varStored[$varName."Desc"];
    }
    public function update($varName, $keyColName, $keyValue, $updateColName, $updateColValue="")
    {

        $keyIndex = array_search($keyColName, $this->varStored[$varName."Desc"]);
        $updateColIndex = array_search($updateColName, $this->varStored[$varName."Desc"]);

        $this->updateSubArray($this->varStored[$varName], $keyIndex, $keyValue, $updateColIndex, $updateColValue);

    }

    public function find($varName, $url)
    {
        foreach ($this->varStored[$varName] as $index => $line) {
            if ($line[0] == $url) {
                return $line;
            }
        }
    }
    public function add($varName, $array)
    {
        foreach ($array as $k=>$val) {
            $indexed[array_search($k, $this->varStored[$varName."Desc"])] = $val;
        }
        return $this->varStored[$varName][] = $indexed;
    }
    public function get($varName)
    {
        $colsNames = $this -> getColumns($varName);
        foreach ($this->varStored[$varName] as $infos) {
            $tmp = "";
            foreach ($infos as $k=>$val){
                $tmp[$colsNames[$k]] = $val;
            }
            $assoc[] = $tmp;
        }
        return $assoc;
    }
    public function updateSubArray(&$array, $indexSearched, $valueSearched, $indexUpdated, $valueUpdated)
    {
        $this->valueSearched = $valueSearched;
        $this->indexSearched = $indexSearched;

        $arrayField = array_filter($array, function($value) {
                if ($value[$this->indexSearched] == $this->valueSearched) {
                    return true;
                } else {
                    return false;
                }
            }
        );
        list($key, $val) = each($arrayField);

        if (strpos($array[$key][$indexUpdated], $valueUpdated) === false && $valueUpdated !="" && $key) {
            $tmpContent = ($array[$key][$indexUpdated] != "")?  explode(",", $array[$key][$indexUpdated]):array();
            $tmpContent[] = $valueUpdated;
            $array[$key][$indexUpdated] = implode(",", $tmpContent);
        }
    }
    public function subArraySearch($array,$indexSearched, $valueSearched)
    {
        foreach ($array as $index => $line) {
            if ($line[$indexSearched] == $valueSearched) {
                return $index;
            }
        }

        return false;
    }

}
