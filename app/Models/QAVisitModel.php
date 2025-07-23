<?php

namespace WeCozaClasses\Models;

use WeCozaClasses\Services\Database\DatabaseService;

class QAVisitModel
{
    private $id;
    private $classId;
    private $visitDate;
    private $visitType;
    private $officerName;
    private $latestDocument;
    private $createdAt;
    private $updatedAt;

    public function __construct($data = [])
    {
        if (!empty($data)) {
            $this->hydrate($data);
        }
    }

    /**
     * Hydrate the model with data
     */
    private function hydrate($data)
    {
        $this->setId($data['id'] ?? null);
        $this->setClassId($data['class_id'] ?? null);
        $this->setVisitDate($data['visit_date'] ?? null);
        $this->setVisitType($data['visit_type'] ?? null);
        $this->setOfficerName($data['officer_name'] ?? null);
        $this->setLatestDocument($data['latest_document'] ?? null);
        $this->setCreatedAt($data['created_at'] ?? null);
        $this->setUpdatedAt($data['updated_at'] ?? null);
    }

    /**
     * Save the QA visit to the database
     */
    public function save()
    {
        $database = DatabaseService::getInstance();
        
        if ($this->getId()) {
            return $this->update();
        } else {
            return $this->create();
        }
    }

    /**
     * Create a new QA visit
     */
    private function create()
    {
        $database = DatabaseService::getInstance();
        
        $sql = "INSERT INTO qa_visits (
            class_id, visit_date, visit_type, officer_name, latest_document, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $params = [
            $this->getClassId(),
            $this->getVisitDate(),
            $this->getVisitType(),
            $this->getOfficerName(),
            $this->getLatestDocument() ? json_encode($this->getLatestDocument()) : null,
            date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s')
        ];
        
        $database->query($sql, $params);
        $this->setId($database->lastInsertId());
        
        return true;
    }

    /**
     * Update an existing QA visit
     */
    private function update()
    {
        $database = DatabaseService::getInstance();
        
        $sql = "UPDATE qa_visits SET
            class_id = ?, visit_date = ?, visit_type = ?, officer_name = ?, 
            latest_document = ?, updated_at = ?
            WHERE id = ?";
        
        $params = [
            $this->getClassId(),
            $this->getVisitDate(),
            $this->getVisitType(),
            $this->getOfficerName(),
            $this->getLatestDocument() ? json_encode($this->getLatestDocument()) : null,
            date('Y-m-d H:i:s'),
            $this->getId()
        ];
        
        return $database->query($sql, $params) !== false;
    }

    /**
     * Delete a QA visit
     */
    public function delete()
    {
        if (!$this->getId()) {
            return false;
        }
        
        $database = DatabaseService::getInstance();
        $sql = "DELETE FROM qa_visits WHERE id = ?";
        
        return $database->query($sql, [$this->getId()]) !== false;
    }

    /**
     * Find a QA visit by ID
     */
    public static function findById($id)
    {
        $database = DatabaseService::getInstance();
        $sql = "SELECT * FROM qa_visits WHERE id = ?";
        
        $stmt = $database->query($sql, [$id]);
        
        if ($row = $stmt->fetch()) {
            return new self($row);
        }
        
        return null;
    }

    /**
     * Find all QA visits for a class
     */
    public static function findByClassId($classId)
    {
        $database = DatabaseService::getInstance();
        $sql = "SELECT * FROM qa_visits WHERE class_id = ? ORDER BY visit_date DESC";
        
        $stmt = $database->query($sql, [$classId]);
        
        $visits = [];
        while ($row = $stmt->fetch()) {
            $visits[] = new self($row);
        }
        
        return $visits;
    }

    /**
     * Find QA visits by officer name
     */
    public static function findByOfficer($officerName)
    {
        $database = DatabaseService::getInstance();
        $sql = "SELECT * FROM qa_visits WHERE officer_name = ? ORDER BY visit_date DESC";
        
        $stmt = $database->query($sql, [$officerName]);
        
        $visits = [];
        while ($row = $stmt->fetch()) {
            $visits[] = new self($row);
        }
        
        return $visits;
    }

    /**
     * Find QA visits by date range
     */
    public static function findByDateRange($startDate, $endDate)
    {
        $database = DatabaseService::getInstance();
        $sql = "SELECT * FROM qa_visits WHERE visit_date BETWEEN ? AND ? ORDER BY visit_date DESC";
        
        $stmt = $database->query($sql, [$startDate, $endDate]);
        
        $visits = [];
        while ($row = $stmt->fetch()) {
            $visits[] = new self($row);
        }
        
        return $visits;
    }

    /**
     * Delete all QA visits for a class
     */
    public static function deleteByClassId($classId)
    {
        $database = DatabaseService::getInstance();
        $sql = "DELETE FROM qa_visits WHERE class_id = ?";
        
        return $database->query($sql, [$classId]) !== false;
    }

    /**
     * Get count of QA visits for a class
     */
    public static function getCountByClassId($classId)
    {
        $database = DatabaseService::getInstance();
        $sql = "SELECT COUNT(*) as count FROM qa_visits WHERE class_id = ?";
        
        $stmt = $database->query($sql, [$classId]);
        $row = $stmt->fetch();
        
        return $row ? (int)$row['count'] : 0;
    }

    /**
     * Convert to array for API/JSON responses
     */
    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'class_id' => $this->getClassId(),
            'visit_date' => $this->getVisitDate(),
            'visit_type' => $this->getVisitType(),
            'officer_name' => $this->getOfficerName(),
            'latest_document' => $this->getLatestDocument(),
            'created_at' => $this->getCreatedAt(),
            'updated_at' => $this->getUpdatedAt()
        ];
    }

    // Getters and Setters
    public function getId() { return $this->id; }
    public function setId($id) { $this->id = $id; return $this; }

    public function getClassId() { return $this->classId; }
    public function setClassId($classId) { $this->classId = $classId; return $this; }

    public function getVisitDate() { return $this->visitDate; }
    public function setVisitDate($visitDate) { $this->visitDate = $visitDate; return $this; }

    public function getVisitType() { return $this->visitType; }
    public function setVisitType($visitType) { $this->visitType = $visitType; return $this; }

    public function getOfficerName() { return $this->officerName; }
    public function setOfficerName($officerName) { $this->officerName = $officerName; return $this; }

    public function getLatestDocument() { return $this->latestDocument; }
    public function setLatestDocument($latestDocument) { 
        if (is_string($latestDocument)) {
            $this->latestDocument = json_decode($latestDocument, true);
        } else {
            $this->latestDocument = $latestDocument;
        }
        return $this; 
    }

    public function getCreatedAt() { return $this->createdAt; }
    public function setCreatedAt($createdAt) { $this->createdAt = $createdAt; return $this; }

    public function getUpdatedAt() { return $this->updatedAt; }
    public function setUpdatedAt($updatedAt) { $this->updatedAt = $updatedAt; return $this; }
}