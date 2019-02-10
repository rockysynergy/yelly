<?php
namespace orq\php\yelly;
use Jajo\JSONDB;

class JsonDb implements DbInterface {
    /**
     * @var Jajo\JSONDB
     */
    private $db;

    /**
     * @var string
     */
    private $jsonFile;

    /**
     * @param string $dbPath
     * @param string $jsonFile
     * @return void
     */
    public function __construct($dbPath, $jsonFile) {
        $jsonFilePath = $dbPath . DIRECTORY_SEPARATOR . $jsonFile;
        if (!file_exists($jsonFilePath)) {
            throw new \Exception("Please create {$jsonFilePath}");
        }
        $this->db = new JSONDB($dbPath);
        $this->jsonFile = $jsonFile;
    }

    /**
     * @param array $record The new record
     * @return void
     */
    public function insert(Array $record) {
        $this->db->insert($this->jsonFile, $record);
    }

    /**
     * @param array $fields Fields to select
     * @param array $conditions Conditios for filtering the result, only support AND logical for multiple condition items
     * @param array $orderBy  Order by criterea
     * 
     * @return array
      */
    public function select(Array $fields, Array $conditions, $orderBy = []) {
        $result = $this->db->select(implode(',', $fields))
            -from($this->jsonFile)
            ->where($conditions, 'AND')
            ->get();
    }
    
    /**
     * @param array $fields Fields to select
     * @param array $conditions Conditios for filtering the result
     * @return void
      */
    public function update(Array $fields, Array $conditions) {
        $this->db->update($fields)
            ->from($this->jsonFile)
            ->where($conditions, 'AND')
            ->trigger();
    }
          
    /**
     *
     * @param array $conditions Conditios for filtering the result
      */
    public function delete(Array $conditions) {
        $this->db->delete()
            ->from($this->db)
            ->where($conditions, 'AND')
            ->trigger();
    }
}