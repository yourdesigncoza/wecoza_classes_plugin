<?php
/**
 * ClassRepository.php
 *
 * Repository for fetching class-related reference data from database
 * Extracted from ClassController to follow DRY principles
 */

namespace WeCozaClasses\Repositories;

use WeCozaClasses\Services\Database\DatabaseService;
use WeCozaClasses\Controllers\ClassTypesController;

class ClassRepository {

    /**
     * Cache duration in seconds (12 hours)
     */
    private const CACHE_DURATION = 12 * HOUR_IN_SECONDS;

    /**
     * Get all clients ordered by name
     *
     * @return array Array of clients with id and name
     */
    public static function getClients(): array {
        try {
            $db = DatabaseService::getInstance();
            $sql = "SELECT client_id, client_name FROM public.clients ORDER BY client_name ASC";
            $stmt = $db->query($sql);

            $clients = [];
            while ($row = $stmt->fetch()) {
                $clients[] = [
                    'id' => (int)$row['client_id'],
                    'name' => \sanitize_text_field($row['client_name'])
                ];
            }

            return $clients;
        } catch (\Exception $e) {
            \error_log('WeCoza Classes Plugin: Error fetching clients: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all sites grouped by client ID
     *
     * @return array Sites grouped by client_id
     */
    public static function getSites(): array {
        try {
            $db = DatabaseService::getInstance();
            $sql = "SELECT s.site_id, s.client_id, s.site_name, l.street_address as address
                    FROM public.sites s
                    LEFT JOIN public.locations l ON s.place_id = l.location_id
                    ORDER BY s.client_id ASC, s.site_name ASC";
            $stmt = $db->query($sql);

            $sites = [];
            while ($row = $stmt->fetch()) {
                $client_id = (int)$row['client_id'];

                if (!isset($sites[$client_id])) {
                    $sites[$client_id] = [];
                }

                $sites[$client_id][] = [
                    'id' => (int)$row['site_id'],
                    'name' => \sanitize_text_field($row['site_name']),
                    'address' => \sanitize_textarea_field($row['address'])
                ];
            }

            return $sites;
        } catch (\Exception $e) {
            \error_log('WeCoza Classes Plugin: Error fetching sites: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all learners with location information (cached)
     *
     * @return array Array of learners with full details
     */
    public static function getLearners(): array {
        try {
            // Check cache first
            $cache_key = 'wecoza_class_learners_with_locations';
            $cached_learners = \get_transient($cache_key);
            if ($cached_learners !== false) {
                return $cached_learners;
            }

            $db = DatabaseService::getInstance();
            $sql = "SELECT
                        l.id,
                        l.first_name,
                        l.second_name,
                        l.initials,
                        l.surname,
                        l.sa_id_no,
                        l.passport_number,
                        l.city_town_id,
                        l.province_region_id,
                        l.postal_code,
                        loc.town AS city_town_name,
                        loc.province AS province_region_name
                    FROM public.learners l
                    LEFT JOIN public.locations loc ON l.city_town_id = loc.location_id
                    WHERE l.first_name IS NOT NULL AND l.surname IS NOT NULL
                    ORDER BY l.surname ASC, l.first_name ASC";
            $stmt = $db->query($sql);

            $learners = [];
            while ($row = $stmt->fetch()) {
                // Build formatted name
                $nameParts = array_filter([
                    trim($row['first_name'] ?? ''),
                    trim($row['second_name'] ?? ''),
                    trim($row['initials'] ?? ''),
                    trim($row['surname'] ?? '')
                ]);
                $formattedName = implode(' ', $nameParts);

                // Use ID number for identification, fallback to passport
                $idNumber = '';
                $idType = '';
                if (!empty($row['sa_id_no'])) {
                    $idNumber = $row['sa_id_no'];
                    $idType = 'sa_id';
                } elseif (!empty($row['passport_number'])) {
                    $idNumber = $row['passport_number'];
                    $idType = 'passport';
                }

                $learners[] = [
                    'id' => (int)$row['id'],
                    'name' => \sanitize_text_field($formattedName),
                    'id_number' => \sanitize_text_field($idNumber),
                    'id_type' => \sanitize_text_field($idType),
                    'first_name' => \sanitize_text_field($row['first_name']),
                    'second_name' => \sanitize_text_field($row['second_name'] ?? ''),
                    'initials' => \sanitize_text_field($row['initials'] ?? ''),
                    'surname' => \sanitize_text_field($row['surname']),
                    'city_town_id' => (int)($row['city_town_id'] ?? 0),
                    'province_region_id' => (int)($row['province_region_id'] ?? 0),
                    'postal_code' => \sanitize_text_field($row['postal_code'] ?? ''),
                    'city_town_name' => \sanitize_text_field($row['city_town_name'] ?? ''),
                    'province_region_name' => \sanitize_text_field($row['province_region_name'] ?? '')
                ];
            }

            // Cache results
            \set_transient($cache_key, $learners, self::CACHE_DURATION);

            return $learners;
        } catch (\Exception $e) {
            \error_log('WeCoza Classes Plugin: Error fetching learners: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get all agents
     * Note: Static data - in production should come from database
     *
     * @return array Array of agents with id and name
     */
    public static function getAgents(): array {
        return [
            ['id' => 1, 'name' => 'Michael M. van der Berg'],
            ['id' => 2, 'name' => 'Thandi T. Nkosi'],
            ['id' => 3, 'name' => 'Rajesh R. Patel'],
            ['id' => 4, 'name' => 'Lerato L. Moloi'],
            ['id' => 5, 'name' => 'Johannes J. Pretorius'],
            ['id' => 6, 'name' => 'Nomvula N. Dlamini'],
            ['id' => 7, 'name' => 'David D. O\'Connor'],
            ['id' => 8, 'name' => 'Zanele Z. Mthembu'],
            ['id' => 9, 'name' => 'Pieter P. van Zyl'],
            ['id' => 10, 'name' => 'Fatima F. Ismail'],
            ['id' => 11, 'name' => 'Sipho S. Ndlovu'],
            ['id' => 12, 'name' => 'Anita A. van Rensburg'],
            ['id' => 13, 'name' => 'Kobus K. Steyn'],
            ['id' => 14, 'name' => 'Nalini N. Reddy'],
            ['id' => 15, 'name' => 'Willem W. Botha']
        ];
    }

    /**
     * Get all supervisors
     * Note: Static data - in production should come from database
     *
     * @return array Array of supervisors with id and name
     */
    public static function getSupervisors(): array {
        return [
            ['id' => 1, 'name' => 'Dr. Sarah Johnson'],
            ['id' => 2, 'name' => 'Prof. Michael Smith'],
            ['id' => 3, 'name' => 'Ms. Jennifer Brown'],
            ['id' => 4, 'name' => 'Mr. David Wilson'],
            ['id' => 5, 'name' => 'Dr. Lisa Anderson']
        ];
    }

    /**
     * Get all SETA options
     * Note: Static data - in production should come from database
     *
     * @return array Array of SETAs with id and name
     */
    public static function getSeta(): array {
        return [
            ['id' => 'BANKSETA', 'name' => 'Banking Sector Education and Training Authority'],
            ['id' => 'CHIETA', 'name' => 'Chemical Industries Education and Training Authority'],
            ['id' => 'CETA', 'name' => 'Construction Education and Training Authority'],
            ['id' => 'ETDP', 'name' => 'Education, Training and Development Practices SETA'],
            ['id' => 'FASSET', 'name' => 'Finance and Accounting Services SETA'],
            ['id' => 'FOODBEV', 'name' => 'Food and Beverages Manufacturing Industry SETA'],
            ['id' => 'HWSETA', 'name' => 'Health and Welfare SETA'],
            ['id' => 'INSETA', 'name' => 'Insurance Sector Education and Training Authority'],
            ['id' => 'LGSETA', 'name' => 'Local Government Sector Education and Training Authority'],
            ['id' => 'MERSETA', 'name' => 'Manufacturing, Engineering and Related Services SETA']
        ];
    }

    /**
     * Get class types (delegates to ClassTypesController)
     *
     * @return array Array of class types
     */
    public static function getClassTypes(): array {
        return ClassTypesController::getClassTypes();
    }

    /**
     * Get Yes/No options for boolean fields
     *
     * @return array Array with Yes and No options
     */
    public static function getYesNoOptions(): array {
        return [
            ['id' => 'Yes', 'name' => 'Yes'],
            ['id' => 'No', 'name' => 'No']
        ];
    }

    /**
     * Get class notes options
     *
     * @return array Array of class note types
     */
    public static function getClassNotesOptions(): array {
        return [
            ['id' => 'Agent Absent', 'name' => 'Agent Absent'],
            ['id' => 'Client Cancelled', 'name' => 'Client Cancelled'],
            ['id' => 'Poor attendance', 'name' => 'Poor attendance'],
            ['id' => 'Learners behind schedule', 'name' => 'Learners behind schedule'],
            ['id' => 'Learners unhappy', 'name' => 'Learners unhappy'],
            ['id' => 'Client unhappy', 'name' => 'Client unhappy'],
            ['id' => 'Learners too fast', 'name' => 'Learners too fast'],
            ['id' => 'Class on track', 'name' => 'Class on track'],
            ['id' => 'Bad QA report', 'name' => 'Bad QA report'],
            ['id' => 'Good QA report', 'name' => 'Good QA report'],
            ['id' => 'Venue issues', 'name' => 'Venue issues'],
            ['id' => 'Equipment problems', 'name' => 'Equipment problems'],
            ['id' => 'Material shortage', 'name' => 'Material shortage'],
            ['id' => 'Weather delay', 'name' => 'Weather delay'],
            ['id' => 'Holiday adjustment', 'name' => 'Holiday adjustment']
        ];
    }

    /**
     * Clear learners cache (useful after data updates)
     */
    public static function clearLearnersCache(): void {
        \delete_transient('wecoza_class_learners_with_locations');
    }

    /**
     * Get all classes from database with optional filtering
     *
     * @param array $options Query options (limit, order_by, order)
     * @return array Array of class data
     */
    public static function getAllClasses(array $options = []): array {
        $db = DatabaseService::getInstance();

        // Set defaults
        $limit = isset($options['limit']) ? intval($options['limit']) : 50;
        $order_by = isset($options['order_by']) ? $options['order_by'] : 'created_at';
        $order = isset($options['order']) ? strtoupper($options['order']) : 'DESC';

        // Validate order_by to prevent SQL injection
        $allowed_columns = [
            'class_id', 'client_id', 'class_type', 'class_subject',
            'original_start_date', 'delivery_date', 'created_at', 'updated_at'
        ];

        if (!in_array($order_by, $allowed_columns)) {
            $order_by = 'created_at';
        }

        // Validate order direction
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }

        // Build the query with client name JOIN
        $sql = "
            SELECT
                c.class_id,
                c.client_id,
                cl.client_name,
                c.class_type,
                c.class_subject,
                c.class_code,
                c.class_duration,
                c.original_start_date,
                c.delivery_date,
                c.seta_funded,
                c.seta,
                c.exam_class,
                c.exam_type,
                c.class_agent,
                c.initial_class_agent,
                c.project_supervisor_id,
                c.stop_restart_dates,
                c.order_nr,
                c.created_at,
                c.updated_at
            FROM public.classes c
            LEFT JOIN public.clients cl ON c.client_id = cl.client_id
            ORDER BY c." . $order_by . " " . $order . "
            LIMIT " . $limit;

        try {
            $stmt = $db->getPdo()->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            \error_log('WeCoza Classes Plugin: Error in getAllClasses: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get single class from database by ID
     *
     * @param int $class_id Class ID
     * @return array|null Class data array or null if not found
     */
    public static function getSingleClass(int $class_id): ?array {
        try {
            // Use ClassModel to get the class data - this ensures agent replacements are loaded
            $classModel = \WeCozaClasses\Models\ClassModel::getById($class_id);

            if (!$classModel) {
                return null;
            }

            // Convert ClassModel object to array format expected by the view
            $result = [
                'class_id' => $classModel->getId(),
                'client_id' => $classModel->getClientId(),
                'site_id' => $classModel->getSiteId(),
                'class_address_line' => $classModel->getClassAddressLine(),
                'class_type' => $classModel->getClassType(),
                'class_subject' => $classModel->getClassSubject(),
                'class_code' => $classModel->getClassCode(),
                'skills_package' => $classModel->getSkillsPackage(),
                'class_duration' => $classModel->getClassDuration(),
                'original_start_date' => $classModel->getOriginalStartDate(),
                'seta_funded' => $classModel->getSetaFunded() ? 'Yes' : 'No',
                'seta' => $classModel->getSeta(),
                'exam_class' => $classModel->getExamClass() ? 'Yes' : 'No',
                'exam_type' => $classModel->getExamType(),
                'qa_visits' => self::getQAVisitsForClass($classModel->getId()),
                'class_agent' => $classModel->getClassAgent(),
                'initial_class_agent' => $classModel->getInitialClassAgent(),
                'initial_agent_start_date' => $classModel->getInitialAgentStartDate(),
                'project_supervisor_id' => $classModel->getProjectSupervisorId(),
                'delivery_date' => $classModel->getDeliveryDate(),
                'learner_ids' => $classModel->getLearnerIds(),
                'exam_learners' => $classModel->getExamLearners(),
                'backup_agent_ids' => $classModel->getBackupAgentIds(),
                'agent_replacements' => $classModel->getAgentReplacements(),
                'schedule_data' => $classModel->getScheduleData(),
                'stop_restart_dates' => $classModel->getStopRestartDates(),
                'event_dates' => $classModel->getEventDates(),
                'class_notes_data' => $classModel->getClassNotesData(),
                'created_at' => $classModel->getCreatedAt(),
                'updated_at' => $classModel->getUpdatedAt(),
            ];

            // Add client name
            if ($classModel->getClientId()) {
                $clients = self::getClients();
                foreach ($clients as $client) {
                    if ($client['id'] == $classModel->getClientId()) {
                        $result['client_name'] = $client['name'];
                        break;
                    }
                }
            }

            // Add agent names lookup
            $agents = self::getAgents();
            $agentLookup = [];
            foreach ($agents as $agent) {
                $agentLookup[$agent['id']] = $agent['name'];
            }

            // Add current agent name
            $currentAgentId = $result['class_agent'] ?? $result['initial_class_agent'] ?? null;
            if (!empty($currentAgentId)) {
                $result['agent_name'] = $agentLookup[$currentAgentId] ?? 'Unknown Agent';
                $result['class_agent'] = $currentAgentId;
            }

            // Add initial agent name
            if (!empty($result['initial_class_agent'])) {
                $result['initial_agent_name'] = $agentLookup[$result['initial_class_agent']] ?? 'Unknown Agent';
            }

            // Add supervisor name
            if (!empty($result['project_supervisor_id'])) {
                $supervisors = self::getSupervisors();
                foreach ($supervisors as $supervisor) {
                    if ($supervisor['id'] == $result['project_supervisor_id']) {
                        $result['supervisor_name'] = $supervisor['name'];
                        break;
                    }
                }
            }

            return $result;
        } catch (\Exception $e) {
            \error_log('WeCoza Classes Plugin: Error in getSingleClass: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get site addresses based on client and site selection
     *
     * @return array Associative array of site_id => address
     */
    public static function getSiteAddresses(): array {
        try {
            $db = DatabaseService::getInstance();

            $sql = "SELECT s.site_id, l.street_address as address
                    FROM public.sites s
                    LEFT JOIN public.locations l ON s.place_id = l.location_id
                    WHERE l.street_address IS NOT NULL AND l.street_address != ''";
            $stmt = $db->query($sql);

            $addresses = [];
            while ($row = $stmt->fetch()) {
                $site_id = (int)$row['site_id'];
                $address = \sanitize_textarea_field($row['address']);

                if (!empty($address)) {
                    $addresses[$site_id] = $address;
                }
            }

            return $addresses;

        } catch (\Exception $e) {
            \error_log('WeCoza Classes Plugin: Error fetching site addresses: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Enrich classes array with agent names
     *
     * @param array $classes Array of class data
     * @return array Array of class data with agent names added
     */
    public static function enrichClassesWithAgentNames(array $classes): array {
        $agents = self::getAgents();
        $agentLookup = [];

        // Create lookup array for faster access
        foreach ($agents as $agent) {
            $agentLookup[$agent['id']] = $agent['name'];
        }

        // Enrich each class with agent names
        foreach ($classes as &$class) {
            // Add current agent name
            if (!empty($class['class_agent'])) {
                $class['agent_name'] = $agentLookup[$class['class_agent']] ?? 'Unknown Agent';
            }

            // Add initial agent name
            if (!empty($class['initial_class_agent'])) {
                $class['initial_agent_name'] = $agentLookup[$class['initial_class_agent']] ?? 'Unknown Agent';
            }
        }

        return $classes;
    }

    /**
     * Get cached class notes with transient support and performance optimizations
     * Uses PostgreSQL database with JSONB column for class_notes_data
     *
     * @param int $class_id The class ID
     * @param array $options Optional parameters for optimization
     * @return array Array of notes
     */
    public static function getCachedClassNotes(int $class_id, array $options = []): array {
        $cache_key = "wecoza_class_notes_{$class_id}";
        $cached_notes = \get_transient($cache_key);

        if ($cached_notes !== false) {
            return $cached_notes;
        }

        // Use PostgreSQL connection for external database
        $db_config = include(dirname(__DIR__) . '/../config/app.php');
        $pg_config = $db_config['database']['postgresql'];

        try {
            $pdo = new \PDO(
                "pgsql:host={$pg_config['host']};port={$pg_config['port']};dbname={$pg_config['dbname']}",
                $pg_config['user'],
                $pg_config['password'],
                [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
            );

            // Query PostgreSQL classes table for class_notes_data JSONB column
            $stmt = $pdo->prepare("SELECT class_notes_data FROM public.classes WHERE class_id = :class_id LIMIT 1");
            $stmt->bindParam(':class_id', $class_id, \PDO::PARAM_INT);
            $stmt->execute();

            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            $notes = [];
            if ($result && !empty($result['class_notes_data'])) {
                $notes_data = json_decode($result['class_notes_data'], true);
                if (is_array($notes_data)) {
                    $notes = $notes_data;

                    // Sort notes by created_at for better performance
                    usort($notes, function($a, $b) {
                        return strtotime($b['created_at'] ?? '0') - strtotime($a['created_at'] ?? '0');
                    });

                    // Apply pagination limits early to reduce memory usage
                    $limit = isset($options['limit']) ? (int)$options['limit'] : 50;
                    $offset = isset($options['offset']) ? (int)$options['offset'] : 0;

                    if ($limit > 0) {
                        $notes = array_slice($notes, $offset, $limit);
                    }
                }
            }

            // Cache for 15 minutes with performance optimizations
            \set_transient($cache_key, $notes, 15 * MINUTE_IN_SECONDS);

            return $notes;

        } catch (\PDOException $e) {
            \error_log("PostgreSQL connection error in getCachedClassNotes: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Clear cached class notes
     *
     * @param int $class_id The class ID
     */
    public static function clearCachedClassNotes(int $class_id): void {
        $cache_key = "wecoza_class_notes_{$class_id}";
        \delete_transient($cache_key);
    }

    /**
     * Get QA visits for a class in a format suitable for the view
     *
     * @param int $classId Class ID
     * @return array QA visits data with separate arrays for backward compatibility
     */
    public static function getQAVisitsForClass(int $classId): array {
        try {
            $qaVisits = \WeCozaClasses\Models\QAVisitModel::findByClassId($classId);

            // Return complete visit objects instead of separate arrays
            $visits = [];
            foreach ($qaVisits as $visit) {
                $visits[] = [
                    'date' => $visit->getVisitDate(),
                    'type' => $visit->getVisitType(),
                    'officer' => $visit->getOfficerName(),
                    'document' => $visit->getLatestDocument(),
                    'hasNewFile' => false,
                    'existingDocument' => $visit->getLatestDocument()
                ];
            }

            return [
                'visits' => $visits
            ];
        } catch (\Exception $e) {
            \error_log('WeCoza Classes Plugin: Error loading QA visits: ' . $e->getMessage());
            return [
                'visits' => []
            ];
        }
    }

    /**
     * Get sample class data for testing when class is not found
     *
     * @param int $class_id Class ID to use in sample data
     * @return array Sample class data
     */
    public static function getSampleClassData(int $class_id): array {
        return [
            'class_id' => $class_id,
            'class_code' => 'SAMPLE-CLASS-' . $class_id,
            'class_subject' => 'Sample Class Subject',
            'class_type' => 1,
            'client_id' => 1,
            'site_id' => 1,
            'client_name' => 'Sample Client Ltd',
            'class_agent' => null,
            'supervisor_name' => 'Dr. Sarah Johnson',
            'project_supervisor_id' => 1,
            'seta_funded' => 'Yes',
            'seta' => 'CHIETA',
            'exam_class' => 'Yes',
            'exam_type' => 'Open Book Exam',
            'class_duration' => 240,
            'class_address_line' => '123 Sample Street, Sample City, 1234',
            'original_start_date' => date('Y-m-d'),
            'delivery_date' => date('Y-m-d', strtotime('+30 days')),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
            'schedule_data' => [
                'pattern' => 'weekly',
                'startDate' => date('Y-m-d'),
                'endDate' => date('Y-m-d', strtotime('+3 months')),
                'selectedDays' => ['Monday', 'Wednesday', 'Friday'],
                'timeData' => [
                    'mode' => 'per-day',
                    'perDayTimes' => [
                        'Monday' => ['startTime' => '09:00', 'endTime' => '11:00', 'duration' => 2],
                        'Wednesday' => ['startTime' => '14:00', 'endTime' => '16:30', 'duration' => 2.5],
                        'Friday' => ['startTime' => '10:00', 'endTime' => '12:00', 'duration' => 2]
                    ]
                ],
                'version' => '2.0',
                'holidayOverrides' => [],
                'exceptionDates' => []
            ],
            'exception_dates' => null,
            'stop_restart_dates' => [
                ['stop_date' => date('Y-m-d', strtotime('+10 days')), 'restart_date' => date('Y-m-d', strtotime('+15 days'))]
            ],
            'learner_ids' => [
                ['id' => 1, 'name' => 'Alice Johnson', 'status' => 'CIC - Currently in Class'],
                ['id' => 2, 'name' => 'Bob Smith', 'status' => 'CIC - Currently in Class']
            ],
            'exam_learners' => [
                ['id' => 1, 'name' => 'Alice Johnson', 'exam_status' => 'Registered'],
                ['id' => 2, 'name' => 'Bob Smith', 'exam_status' => 'Registered']
            ],
            'qa_reports' => [],
            'class_notes_data' => [],
            'backup_agent_ids' => [],
            'initial_class_agent' => 5,
            'initial_agent_start_date' => date('Y-m-d', strtotime('-30 days'))
        ];
    }
}
