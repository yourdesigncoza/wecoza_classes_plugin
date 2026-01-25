<?php
/**
 * ScheduleService.php
 *
 * Service for schedule generation and calendar event creation
 * Extracted from ClassController to follow single responsibility principle
 */

namespace WeCozaClasses\Services;

/**
 * Service class for handling schedule-related operations
 */
class ScheduleService {

    /**
     * Convert V2 schedule data to legacy format for backward compatibility
     *
     * @param array $v2Data V2 schedule data
     * @return array Legacy format schedule data
     */
    public static function convertV2ToLegacy(array $v2Data): array {
        $legacyData = [];

        // Map v2.0 fields to legacy format
        $legacyData['pattern'] = $v2Data['pattern'] ?? 'weekly';
        $legacyData['start_date'] = $v2Data['start_date'] ?? $v2Data['startDate'] ?? null;
        $legacyData['end_date'] = $v2Data['end_date'] ?? $v2Data['endDate'] ?? null;
        $legacyData['selected_days'] = $v2Data['selected_days'] ?? $v2Data['selectedDays'] ?? [];
        $legacyData['time_mode'] = $v2Data['timeData']['mode'] ?? 'single';

        // Convert per-day times
        if (isset($v2Data['timeData']['perDay'])) {
            $legacyData['per_day_times'] = $v2Data['timeData']['perDay'];
        } elseif (isset($v2Data['per_day_times'])) {
            $legacyData['per_day_times'] = $v2Data['per_day_times'];
        }

        return $legacyData;
    }

    /**
     * Generate schedule entries based on pattern and time data
     *
     * @param string $pattern Schedule pattern (weekly, biweekly, monthly)
     * @param \DateTime $startDate Start date
     * @param \DateTime $endDate End date
     * @param array $timeData Time data structure
     * @param array $selectedDays Selected days of week (for weekly/biweekly)
     * @param int|null $dayOfMonth Day of month (for monthly)
     * @return array Schedule entries
     */
    public static function generateScheduleEntries(
        string $pattern,
        \DateTime $startDate,
        \DateTime $endDate,
        array $timeData,
        array $selectedDays = [],
        ?int $dayOfMonth = null
    ): array {
        switch ($pattern) {
            case 'weekly':
                return self::generateWeeklyEntries($startDate, $endDate, $timeData, $selectedDays);
            case 'biweekly':
                return self::generateBiweeklyEntries($startDate, $endDate, $timeData, $selectedDays);
            case 'monthly':
                return self::generateMonthlyEntries($startDate, $endDate, $timeData, $dayOfMonth ?? 1);
            default:
                return [];
        }
    }

    /**
     * Generate weekly schedule entries
     *
     * @param \DateTime $startDate Start date
     * @param \DateTime $endDate End date
     * @param array $timeData Time data
     * @param array $selectedDays Selected days of week
     * @return array Schedule entries
     */
    public static function generateWeeklyEntries(
        \DateTime $startDate,
        \DateTime $endDate,
        array $timeData,
        array $selectedDays
    ): array {
        $entries = [];
        $current = clone $startDate;

        while ($current <= $endDate) {
            $dayName = $current->format('l');

            if (in_array($dayName, $selectedDays)) {
                $times = self::getTimesForDay($timeData, $dayName);
                if ($times) {
                    $entries[] = [
                        'date' => $current->format('Y-m-d'),
                        'start_time' => $times['startTime'],
                        'end_time' => $times['endTime']
                    ];
                }
            }

            $current->add(new \DateInterval('P1D'));
        }

        return $entries;
    }

    /**
     * Generate biweekly schedule entries
     *
     * @param \DateTime $startDate Start date
     * @param \DateTime $endDate End date
     * @param array $timeData Time data
     * @param array $selectedDays Selected days of week
     * @return array Schedule entries
     */
    public static function generateBiweeklyEntries(
        \DateTime $startDate,
        \DateTime $endDate,
        array $timeData,
        array $selectedDays
    ): array {
        $entries = [];
        $current = clone $startDate;
        $weekCount = 0;

        while ($current <= $endDate) {
            $dayName = $current->format('l');

            // Only add entries on even weeks (0, 2, 4, etc.)
            if ($weekCount % 2 === 0 && in_array($dayName, $selectedDays)) {
                $times = self::getTimesForDay($timeData, $dayName);
                if ($times) {
                    $entries[] = [
                        'date' => $current->format('Y-m-d'),
                        'start_time' => $times['startTime'],
                        'end_time' => $times['endTime']
                    ];
                }
            }

            // Increment week count on Sundays
            if ($current->format('N') == 7) {
                $weekCount++;
            }

            $current->add(new \DateInterval('P1D'));
        }

        return $entries;
    }

    /**
     * Generate monthly schedule entries
     *
     * @param \DateTime $startDate Start date
     * @param \DateTime $endDate End date
     * @param array $timeData Time data
     * @param int $dayOfMonth Day of month for schedule
     * @return array Schedule entries
     */
    public static function generateMonthlyEntries(
        \DateTime $startDate,
        \DateTime $endDate,
        array $timeData,
        int $dayOfMonth
    ): array {
        $entries = [];
        $current = clone $startDate;

        // Set to the specified day of month
        $current->setDate($current->format('Y'), $current->format('n'), $dayOfMonth);

        // If the day is before start date, move to next month
        if ($current < $startDate) {
            $current->add(new \DateInterval('P1M'));
            $current->setDate($current->format('Y'), $current->format('n'), $dayOfMonth);
        }

        while ($current <= $endDate) {
            $times = self::getTimesForDay($timeData, null); // Use single time for monthly
            if ($times) {
                $entries[] = [
                    'date' => $current->format('Y-m-d'),
                    'start_time' => $times['startTime'],
                    'end_time' => $times['endTime']
                ];
            }

            // Move to next month
            $current->add(new \DateInterval('P1M'));

            // Handle month-end edge cases
            $targetDay = min($dayOfMonth, $current->format('t')); // Last day of month if dayOfMonth > days in month
            $current->setDate($current->format('Y'), $current->format('n'), $targetDay);
        }

        return $entries;
    }

    /**
     * Get times for a specific day from time data
     *
     * @param array $timeData Time data structure
     * @param string|null $dayName Day name (for per-day mode) or null (for single mode)
     * @return array|null Times array with startTime and endTime, or null if not found
     */
    public static function getTimesForDay(array $timeData, ?string $dayName = null): ?array {
        $mode = $timeData['mode'] ?? 'single';

        if ($mode === 'per-day' && $dayName && isset($timeData['perDay'][$dayName])) {
            $dayData = $timeData['perDay'][$dayName];
            return [
                'startTime' => $dayData['startTime'] ?? '09:00',
                'endTime' => $dayData['endTime'] ?? '17:00'
            ];
        } elseif ($mode === 'single' && isset($timeData['single'])) {
            return [
                'startTime' => $timeData['single']['startTime'] ?? '09:00',
                'endTime' => $timeData['single']['endTime'] ?? '17:00'
            ];
        }

        return null;
    }

    /**
     * Generate calendar events from class schedule data
     * Enhanced to handle both legacy v1.0 and new v2.0 schedule formats
     *
     * @param array $class Class data
     * @return array Calendar events
     */
    public function generateCalendarEvents(array $class): array {
        $events = [];

        // Get basic class information
        $classCode = $class['class_code'] ?? 'Unknown';
        $classSubject = $class['class_subject'] ?? 'Unknown Subject';
        $startDate = $class['original_start_date'] ?? null;
        $deliveryDate = $class['delivery_date'] ?? null;

        // Parse schedule data if available
        $scheduleData = null;
        if (!empty($class['schedule_data'])) {
            $scheduleData = is_string($class['schedule_data'])
                ? json_decode($class['schedule_data'], true)
                : $class['schedule_data'];
        }

        // Generate events from schedule data
        if ($scheduleData && is_array($scheduleData)) {
            $events = $this->generateEventsFromScheduleData($scheduleData, $class);
        } else {
            // Generate sample events based on start and delivery dates
            if ($startDate && $deliveryDate) {
                $events = $this->generateSampleEvents($class, $startDate, $deliveryDate, $classCode, $classSubject);
            }
        }

        // Handle exception dates if available
        if (!empty($class['exception_dates'])) {
            $events = array_merge($events, $this->generateExceptionDateEvents($class));
        }

        // Handle stop/restart dates if available
        if (!empty($class['stop_restart_dates'])) {
            $events = array_merge($events, $this->generateStopRestartEvents($class, $classSubject));
        }

        return $events;
    }

    /**
     * Generate sample events when no schedule data is available
     *
     * @param array $class Class data
     * @param string $startDate Start date
     * @param string $deliveryDate Delivery date
     * @param string $classCode Class code
     * @param string $classSubject Class subject
     * @return array Calendar events
     */
    private function generateSampleEvents(
        array $class,
        string $startDate,
        string $deliveryDate,
        string $classCode,
        string $classSubject
    ): array {
        $events = [];
        $start = new \DateTime($startDate);
        $end = new \DateTime($deliveryDate);
        $interval = new \DateInterval('P1D');
        $period = new \DatePeriod($start, $interval, $end);

        $eventCount = 0;
        foreach ($period as $date) {
            // Skip weekends for sample events
            if ($date->format('N') >= 6) {
                continue;
            }

            // Limit to first 10 events for demo
            if ($eventCount >= 10) {
                break;
            }

            $events[] = [
                'id' => 'class_' . $class['class_id'] . '_' . $date->format('Y-m-d'),
                'title' => '09:00 - 17:00',
                'start' => $date->format('Y-m-d') . 'T09:00:00',
                'end' => $date->format('Y-m-d') . 'T17:00:00',
                'classNames' => ['wecoza-class-event', 'text-primary'],
                'extendedProps' => [
                    'type' => 'class_session',
                    'classCode' => $classCode,
                    'classSubject' => $classSubject,
                    'notes' => 'Sample class session'
                ]
            ];

            $eventCount++;
        }

        return $events;
    }

    /**
     * Generate events from V2.0 schedule data only
     *
     * @param array $scheduleData V2.0 schedule data
     * @param array $class Class information
     * @return array Calendar events
     */
    public function generateEventsFromScheduleData(array $scheduleData, array $class): array {
        // Only handle V2.0 format
        $events = $this->generateEventsFromV2Data($scheduleData, $class);

        // Add exception date events
        if (isset($scheduleData['exceptionDates'])) {
            $events = array_merge($events, $this->generateExceptionEvents($scheduleData['exceptionDates'], $class));
        }

        return $events;
    }

    /**
     * Generate events from v2.0 schedule data with proper per-day time support
     *
     * @param array $scheduleData v2.0 schedule data
     * @param array $class Class information
     * @return array Calendar events
     */
    public function generateEventsFromV2Data(array $scheduleData, array $class): array {
        // Generate events directly from v2.0 pattern data
        return $this->generateEventsFromV2Pattern($scheduleData, $class);
    }

    /**
     * Generate events from V2.0 schedule data (handles both pattern-based and direct entries)
     *
     * @param array $scheduleData V2.0 schedule data
     * @param array $class Class information
     * @return array Calendar events
     */
    public function generateEventsFromV2Pattern(array $scheduleData, array $class): array {
        $events = [];
        $classCode = $class['class_code'] ?? 'Unknown';
        $classSubject = $class['class_subject'] ?? 'Unknown Subject';

        // Check if we have direct schedule entries (numbered keys format)
        $hasDirectEntries = false;
        foreach ($scheduleData as $key => $value) {
            if (is_numeric($key) && is_array($value) && isset($value['date']) && isset($value['start_time']) && isset($value['end_time'])) {
                $hasDirectEntries = true;
                break;
            }
        }

        if ($hasDirectEntries) {
            // Handle direct schedule entries (numbered keys format)
            foreach ($scheduleData as $key => $schedule) {
                if (is_numeric($key) && is_array($schedule) && isset($schedule['date']) && isset($schedule['start_time']) && isset($schedule['end_time'])) {
                    // Calculate duration for display
                    $duration = $this->calculateEventDuration($schedule['start_time'], $schedule['end_time']);
                    $dayName = $schedule['day'] ?? date('l', strtotime($schedule['date']));

                    $events[] = [
                        'id' => 'class_' . $class['class_id'] . '_' . $schedule['date'],
                        'title' => $dayName . ': ' . $schedule['start_time'] . ' - ' . $schedule['end_time'] . ' (' . $duration . 'h)',
                        'start' => $schedule['date'] . 'T' . $schedule['start_time'],
                        'end' => $schedule['date'] . 'T' . $schedule['end_time'],
                        'classNames' => ['wecoza-class-event', 'text-primary'],
                        'extendedProps' => [
                            'type' => 'class_session',
                            'classCode' => $classCode,
                            'classSubject' => $classSubject,
                            'notes' => $schedule['notes'] ?? '',
                            'scheduleFormat' => 'v2.0',
                            'dayOfWeek' => $dayName,
                            'duration' => $duration
                        ]
                    ];
                }
            }
        } else {
            // Handle pattern-based generation
            $pattern = $scheduleData['pattern'] ?? 'weekly';
            $startDate = isset($scheduleData['startDate']) ? new \DateTime($scheduleData['startDate']) : null;
            $endDate = isset($scheduleData['endDate']) ? new \DateTime($scheduleData['endDate']) : null;
            $timeData = $scheduleData['timeData'] ?? [];
            $selectedDays = $scheduleData['selectedDays'] ?? [];

            if ($startDate && $endDate) {
                // Generate schedule entries using existing pattern generation methods
                $scheduleEntries = [];
                switch ($pattern) {
                    case 'weekly':
                        $scheduleEntries = self::generateWeeklyEntries($startDate, $endDate, $timeData, $selectedDays);
                        break;
                    case 'biweekly':
                        $scheduleEntries = self::generateBiweeklyEntries($startDate, $endDate, $timeData, $selectedDays);
                        break;
                    case 'monthly':
                        $scheduleEntries = self::generateMonthlyEntries($startDate, $endDate, $timeData, $scheduleData['dayOfMonth'] ?? 1);
                        break;
                    case 'custom':
                    default:
                        // For custom pattern, create a single entry if we have time data
                        if (isset($timeData['single'])) {
                            $scheduleEntries[] = [
                                'date' => $scheduleData['startDate'],
                                'start_time' => $timeData['single']['startTime'] ?? '09:00',
                                'end_time' => $timeData['single']['endTime'] ?? '17:00'
                            ];
                        }
                        break;
                }

                // Convert schedule entries to calendar events
                foreach ($scheduleEntries as $schedule) {
                    if (isset($schedule['date']) && isset($schedule['start_time']) && isset($schedule['end_time'])) {
                        // Get day of week for enhanced title
                        $date = new \DateTime($schedule['date']);
                        $dayName = $date->format('l');

                        $events[] = [
                            'id' => 'class_' . $class['class_id'] . '_' . $schedule['date'],
                            'title' => $this->formatV2EventTitle($schedule, $dayName, $timeData),
                            'start' => $schedule['date'] . 'T' . $schedule['start_time'],
                            'end' => $schedule['date'] . 'T' . $schedule['end_time'],
                            'classNames' => ['wecoza-class-event', 'text-primary'],
                            'extendedProps' => [
                                'type' => 'class_session',
                                'classCode' => $classCode,
                                'classSubject' => $classSubject,
                                'notes' => $schedule['notes'] ?? '',
                                'scheduleFormat' => 'v2.0',
                                'dayOfWeek' => $dayName,
                                'pattern' => $pattern,
                                'timeMode' => $timeData['mode'] ?? 'single',
                                'duration' => $this->calculateEventDuration($schedule['start_time'], $schedule['end_time'])
                            ]
                        ];
                    }
                }
            }
        }

        return $events;
    }

    /**
     * Generate exception date events
     *
     * @param array $exceptionDates Exception dates from v2.0 format
     * @param array $class Class information
     * @return array Exception events
     */
    public function generateExceptionEvents(array $exceptionDates, array $class): array {
        $events = [];

        foreach ($exceptionDates as $exception) {
            if (isset($exception['date']) && isset($exception['reason'])) {
                $events[] = [
                    'id' => 'exception_' . $class['class_id'] . '_' . $exception['date'],
                    'title' => 'Exception - ' . $exception['reason'],
                    'start' => $exception['date'],
                    'allDay' => true,
                    'display' => 'background',
                    'classNames' => ['wecoza-exception-event'],
                    'extendedProps' => [
                        'type' => 'exception',
                        'reason' => $exception['reason'],
                        'scheduleFormat' => 'v2.0'
                    ]
                ];
            }
        }

        return $events;
    }

    /**
     * Generate events from exception_dates field in class data
     *
     * @param array $class Class data
     * @return array Exception events
     */
    private function generateExceptionDateEvents(array $class): array {
        $events = [];
        $exceptionDates = is_string($class['exception_dates'])
            ? json_decode($class['exception_dates'], true)
            : $class['exception_dates'];

        if (is_array($exceptionDates)) {
            foreach ($exceptionDates as $exception) {
                if (isset($exception['date']) && isset($exception['reason'])) {
                    $events[] = [
                        'id' => 'exception_' . $class['class_id'] . '_' . $exception['date'],
                        'title' => 'Exception - ' . $exception['reason'],
                        'start' => $exception['date'],
                        'allDay' => true,
                        'display' => 'background',
                        'classNames' => ['wecoza-exception-event'],
                        'extendedProps' => [
                            'type' => 'exception',
                            'reason' => $exception['reason']
                        ]
                    ];
                }
            }
        }

        return $events;
    }

    /**
     * Generate stop/restart events from class data
     *
     * @param array $class Class data
     * @param string $classSubject Class subject
     * @return array Stop/restart events
     */
    private function generateStopRestartEvents(array $class, string $classSubject): array {
        $events = [];
        $stopRestartDates = is_string($class['stop_restart_dates'])
            ? json_decode($class['stop_restart_dates'], true)
            : $class['stop_restart_dates'];

        if (is_array($stopRestartDates)) {
            foreach ($stopRestartDates as $index => $stopRestart) {
                if (isset($stopRestart['stop_date']) && isset($stopRestart['restart_date'])) {
                    $stopDate = $stopRestart['stop_date'];
                    $restartDate = $stopRestart['restart_date'];

                    // Create stop date event
                    $events[] = [
                        'id' => 'class_stop_' . $class['class_id'] . '_' . $index,
                        'title' => 'Class Stopped',
                        'start' => $stopDate,
                        'allDay' => true,
                        'display' => 'block',
                        'classNames' => ['text-danger', 'wecoza-stop'],
                        'extendedProps' => [
                            'type' => 'stop_date',
                            'class_id' => $class['class_id'],
                            'description' => sprintf('Class Stopped: %s\nClass: %s', $stopDate, $classSubject),
                            'interactive' => false
                        ]
                    ];

                    // Create restart date event
                    $events[] = [
                        'id' => 'class_restart_' . $class['class_id'] . '_' . $index,
                        'title' => 'Restart',
                        'start' => $restartDate,
                        'allDay' => true,
                        'display' => 'block',
                        'classNames' => ['text-danger', 'wecoza-restart'],
                        'extendedProps' => [
                            'type' => 'restart_date',
                            'class_id' => $class['class_id'],
                            'description' => sprintf('Class Restart: %s\nClass: %s', $restartDate, $classSubject),
                            'interactive' => false
                        ]
                    ];

                    // Create events for days between stop and restart (red circles only)
                    try {
                        $currentDate = new \DateTime($stopDate);
                        $endDate = new \DateTime($restartDate);

                        // Move to the day after stop date
                        $currentDate->add(new \DateInterval('P1D'));

                        while ($currentDate < $endDate) {
                            $dateStr = $currentDate->format('Y-m-d');

                            $events[] = [
                                'id' => 'stop_period_' . $class['class_id'] . '_' . $index . '_' . $dateStr,
                                'title' => '', // No text, just red circle
                                'start' => $dateStr,
                                'allDay' => true,
                                'display' => 'block',
                                'classNames' => ['text-danger', 'wecoza-stop-period'],
                                'extendedProps' => [
                                    'type' => 'stop_period',
                                    'class_id' => $class['class_id'],
                                    'description' => sprintf(
                                        'Class Stopped Period: %s\nClass: %s\nStopped from %s to %s',
                                        $dateStr,
                                        $classSubject,
                                        $stopDate,
                                        $restartDate
                                    ),
                                    'interactive' => false
                                ]
                            ];

                            $currentDate->add(new \DateInterval('P1D'));
                        }
                    } catch (\Exception $e) {
                        // Log error silently
                    }
                }
            }
        }

        return $events;
    }

    /**
     * Format event title based on schedule format
     *
     * @param array $schedule Schedule entry
     * @param string $format Schedule format version
     * @return string Formatted title
     */
    public function formatEventTitle(array $schedule, string $format): string {
        $startTime = $schedule['start_time'];
        $endTime = $schedule['end_time'];

        if ($format === 'v2.0') {
            // Enhanced title for v2.0 format
            $duration = $this->calculateEventDuration($startTime, $endTime);
            return sprintf('%s - %s (%.1fh)', $startTime, $endTime, $duration);
        } else {
            // Simple title for legacy format
            return $startTime . ' - ' . $endTime;
        }
    }

    /**
     * Format event title for v2.0 events with enhanced per-day information
     *
     * @param array $schedule Schedule entry
     * @param string $dayName Day of week
     * @param array $timeData Time data from v2.0 format
     * @return string Formatted title
     */
    public function formatV2EventTitle(array $schedule, string $dayName, array $timeData): string {
        $startTime = $schedule['start_time'];
        $endTime = $schedule['end_time'];
        $duration = $this->calculateEventDuration($startTime, $endTime);

        // Check if this is per-day mode for enhanced title
        $mode = $timeData['mode'] ?? 'single';
        if ($mode === 'per-day') {
            // Show day name for per-day schedules to highlight different times
            return sprintf('%s: %s - %s (%.1fh)', $dayName, $startTime, $endTime, $duration);
        } else {
            // Standard title for single-time mode
            return sprintf('%s - %s (%.1fh)', $startTime, $endTime, $duration);
        }
    }

    /**
     * Calculate event duration in hours
     *
     * @param string $startTime Start time (HH:MM)
     * @param string $endTime End time (HH:MM)
     * @return float Duration in hours
     */
    public function calculateEventDuration(string $startTime, string $endTime): float {
        $start = strtotime($startTime);
        $end = strtotime($endTime);

        if ($start === false || $end === false || $end <= $start) {
            return 0;
        }

        return ($end - $start) / 3600;
    }
}
