<?php
namespace orq\php\yelly;

interface DbInterface {

    /**
     * @param array $record The new record
     * @return void
     */
    public function insert(Array $record);

    /**
     * @param array $fields Fields to select
     * @param array $conditions Conditios for filtering the result, only support AND logical for multiple condition items
     * @param array $orderBy  Order by criterea
      */
    public function select(Array $fields, Array $conditions, $orderBy = []);
    
    /**
     * @param array $fields Fields to select
     * @param array $conditions Conditios for filtering the result
      */
    public function update(Array $fields, Array $conditions);
          
    /**
     * @param array $conditions Conditios for filtering the result
      */
    public function delete(Array $conditions);
}