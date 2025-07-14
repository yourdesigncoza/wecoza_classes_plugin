<!-- Class Details Display Section -->
<?php
// Validate and prepare data for update mode
if (isset($data['class_data']) && $data['class_data']):
    // Ensure we have proper data structure for clients and sites
    $clients = $data['clients'] ?? [];
    $sites = $data['sites'] ?? [];
    $classData = $data['class_data'];

    // Find client name if not already in class data
    if (empty($classData['client_name']) && !empty($classData['client_id'])) {
        foreach ($clients as $client) {
            if ((int)$client['id'] === (int)$classData['client_id']) {
                $classData['client_name'] = $client['name'];
                break;
            }
        }
    }

    // Find site name if not already available
    if (empty($classData['site_name']) && !empty($classData['site_id'])) {
        foreach ($sites as $clientSites) {
            foreach ($clientSites as $site) {
                if ((int)$site['id'] === (int)$classData['site_id']) {
                    $classData['site_name'] = $site['name'];
                    // Also get address if not already in class data
                    if (empty($classData['class_address_line']) && !empty($site['address'])) {
                        $classData['class_address_line'] = $site['address'];
                    }
                    break 2;
                }
            }
        }
    }

    // Find agent name if not already in class data
    if (empty($classData['agent_name']) && !empty($classData['class_agent'])) {
        $agents = $data['agents'] ?? [];
        foreach ($agents as $agent) {
            if ((int)$agent['id'] === (int)$classData['class_agent']) {
                $classData['agent_name'] = $agent['name'];
                break;
            }
        }
    }

    // Find supervisor name if not already in class data
    if (empty($classData['supervisor_name']) && !empty($classData['project_supervisor_id'])) {
        $supervisors = $data['supervisors'] ?? [];
        foreach ($supervisors as $supervisor) {
            if ((int)$supervisor['id'] === (int)$classData['project_supervisor_id']) {
                $classData['supervisor_name'] = $supervisor['name'];
                break;
            }
        }
    }

    // Process backup agents if needed
    if (!empty($classData['backup_agent_ids']) && is_string($classData['backup_agent_ids'])) {
        $classData['backup_agent_ids'] = json_decode($classData['backup_agent_ids'], true);
    }

    // Update the class data with enriched information
    $data['class_data'] = $classData;
?>
<div class="wecoza-class-details-display mb-4">
    <!-- Top Summary Cards -->
    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-4 justify-content-between">
                <!-- Client Card -->
                <div class="col-sm-auto">
                    <div class="d-flex align-items-center">
                        <div class="d-flex bg-primary-subtle rounded flex-center me-3" style="width:32px; height:32px">
                            <i class="bi bi-building text-primary"></i>
                        </div>
                        <div>
                            <p class="fw-bold mb-1">Client</p>
                            <h5 class="fw-bolder text-nowrap">
                                <?php if (!empty($data['class_data']['client_name'])): ?>
                                    <?php echo esc_html($data['class_data']['client_name']); ?>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </h5>
                        </div>
                    </div>
                </div>
                <!-- Class Type Card -->
                <div class="col-sm-auto">
                    <div class="d-flex align-items-center border-start-sm ps-sm-5">
                        <div class="d-flex bg-primary-subtle rounded flex-center me-3" style="width:32px; height:32px">
                            <i class="bi bi-layers text-primary"></i>
                        </div>
                        <div>
                            <p class="fw-bold mb-1">Class Type</p>
                            <h5 class="fw-bolder text-nowrap"><?php echo esc_html($data['class_data']['class_type'] ?? 'Unknown Type'); ?></h5>
                        </div>
                    </div>
                </div>
                <!-- Class Subject Card -->
                <div class="col-sm-auto">
                    <div class="d-flex align-items-center border-start-sm ps-sm-5">
                        <div class="d-flex bg-success-subtle rounded flex-center me-3" style="width:32px; height:32px">
                            <i class="bi bi-book text-success"></i>
                        </div>
                        <div>
                            <p class="fw-bold mb-1">Class Subject</p>
                            <h5 class="fw-bolder text-nowrap"><?php echo esc_html($data['class_data']['class_subject'] ?? 'N/A'); ?></h5>
                        </div>
                    </div>
                </div>
                <!-- Class Code Card -->
                <div class="col-sm-auto">
                    <div class="d-flex align-items-center border-start-sm ps-sm-5">
                        <div class="d-flex bg-info-subtle rounded flex-center me-3" style="width:32px; height:32px">
                            <i class="bi bi-tag text-info"></i>
                        </div>
                        <div>
                            <p class="fw-bold mb-1">Class Code</p>
                            <h5 class="fw-bolder text-nowrap"><?php echo esc_html($data['class_data']['class_code'] ?? 'N/A'); ?></h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Classes Capture Form -->
<form id="classes-form" class="needs-validation ydcoza-compact-form" novalidate method="POST" enctype="multipart/form-data">
   <!-- Hidden fields for update mode -->
   <input type="hidden" id="class_id" name="class_id" value="<?php echo esc_attr($data['class_id'] ?? $_GET['class_id'] ?? ''); ?>">
   <input type="hidden" id="redirect_url" name="redirect_url" value="<?php echo esc_attr($data['redirect_url'] ?? $_GET['redirect_url'] ?? ''); ?>">
   <input type="hidden" id="nonce" name="nonce" value="<?php echo wp_create_nonce('wecoza_class_nonce'); ?>">
   <!-- Debug mode indicator -->
   <?php if (isset($_GET['debug']) && $_GET['debug'] === '1'): ?>
   <input type="hidden" id="debug_mode" name="debug_mode" value="1">
   <?php endif; ?>

   <!-- ===== Section: Basic Details ===== -->
   <div class="container container-md classes-form ps-0">
      <!-- Basic Information Display Table -->
      <div class="px-xl-4 mb-4">
         <h5 class="mb-3">Basic Information</h5>
         <div class="row mx-0">
            <!-- Left Column -->
            <div class="col-sm-12 col-xxl-6 border-bottom border-end-xxl py-3">
               <table class="w-100 table-stats table table-hover table-sm fs-9 mb-0">
                  <tbody>
                     <!-- Class ID -->
                     <tr>
                        <td class="py-2 ydcoza-w-150">
                           <div class="d-inline-flex align-items-center">
                              <div class="d-flex bg-primary-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-hash text-primary" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">Class ID : </p>
                           </div>
                        </td>
                        <td class="py-2">
                           <p class="fw-semibold mb-0">#<?php echo esc_html($data['class_data']['class_id'] ?? $data['class_id'] ?? 'N/A'); ?></p>
                        </td>
                     </tr>
                     <!-- Client -->
                     <tr>
                        <td class="py-2">
                           <div class="d-flex align-items-center">
                              <div class="d-flex bg-primary-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-building text-primary" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">Client :</p>
                           </div>
                        </td>
                        <td class="py-2">
                           <div class="fw-semibold mb-0">
                              <?php echo esc_html($data['class_data']['client_name'] ?? 'N/A'); ?>
                              <?php if (!empty($data['class_data']['client_id'])): ?>
                                 <div class="fs-9 text-muted">ID: <?php echo esc_html($data['class_data']['client_id']); ?></div>
                              <?php endif; ?>
                           </div>
                        </td>
                     </tr>
                     <!-- Site/Location -->
                     <tr>
                        <td class="py-2">
                           <div class="d-flex align-items-center">
                              <div class="d-flex bg-info-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-pin-map text-info" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">Site/Location :</p>
                           </div>
                        </td>
                        <td class="py-2">
                           <div class="fw-semibold mb-0">
                              <?php echo esc_html($data['class_data']['site_name'] ?? 'N/A'); ?>
                              <?php if (!empty($data['class_data']['site_id'])): ?>
                                 <div class="fs-9 text-muted">ID: <?php echo esc_html($data['class_data']['site_id']); ?></div>
                              <?php endif; ?>
                           </div>
                        </td>
                     </tr>
                     <!-- Address -->
                     <tr>
                        <td class="py-2">
                           <div class="d-flex align-items-center">
                              <div class="d-flex bg-success-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-geo-alt text-success" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">Address : </p>
                           </div>
                        </td>                                    
                        <td class="py-2">
                           <p class="fw-semibold mb-0"><?php echo esc_html($data['class_data']['class_address_line'] ?? 'N/A'); ?></p>
                        </td>
                     </tr>
                     <!-- Class Type -->
                     <tr>
                        <td class="py-2">
                           <div class="d-flex align-items-center">
                              <div class="d-flex bg-primary-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-layers text-primary" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">Class Type :</p>
                           </div>
                        </td>
                        <td class="py-2">
                           <p class="fw-semibold mb-0"><?php echo esc_html($data['class_data']['class_type'] ?? 'N/A'); ?></p>
                        </td>
                     </tr>
                     <!-- Class Subject -->
                     <tr>
                        <td class="py-2">
                           <div class="d-flex align-items-center">
                              <div class="d-flex bg-success-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-book text-success" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">Class Subject :</p>
                           </div>
                        </td>
                        <td class="py-2">
                           <p class="fw-semibold mb-0"><?php echo esc_html($data['class_data']['class_subject'] ?? 'N/A'); ?></p>
                        </td>
                     </tr>
                  </tbody>
               </table>
            </div>
            
            <!-- Right Column -->
            <div class="col-sm-12 col-xxl-6 border-bottom py-3">
               <table class="w-100 table-stats table table-hover table-sm fs-9 mb-0">
                  <tbody>
                     <!-- Duration -->
                     <tr>
                        <td class="py-2 ydcoza-w-150">
                           <div class="d-flex align-items-center">
                              <div class="d-flex bg-warning-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-clock text-warning" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">Duration :</p>
                           </div>
                        </td>
                        <td class="py-2">
                           <p class="fw-semibold mb-0">
                              <?php if (!empty($data['class_data']['class_duration'])): ?>
                                 <?php echo esc_html($data['class_data']['class_duration']); ?> hours
                              <?php else: ?>
                                 <span class="text-muted">N/A</span>
                              <?php endif; ?>
                           </p>
                        </td>
                     </tr>
                     <!-- Class Code -->
                     <tr>
                        <td class="py-2">
                           <div class="d-flex align-items-center">
                              <div class="d-flex bg-info-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-tag text-info" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">Class Code :</p>
                           </div>
                        </td>
                        <td class="py-2">
                           <p class="fw-semibold mb-0"><?php echo esc_html($data['class_data']['class_code'] ?? 'N/A'); ?></p>
                        </td>
                     </tr>
                     <!-- Original Start Date -->
                     <tr>
                        <td class="py-2">
                           <div class="d-flex align-items-center">
                              <div class="d-flex bg-info-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-calendar-plus text-info" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">Original Start :</p>
                           </div>
                        </td>
                        <td class="py-2">
                           <p class="fw-semibold mb-0">
                              <?php if (!empty($data['class_data']['original_start_date'])): ?>
                                 <?php echo esc_html(date('M j, Y', strtotime($data['class_data']['original_start_date']))); ?>
                              <?php else: ?>
                                 <span class="text-muted">N/A</span>
                              <?php endif; ?>
                           </p>
                        </td>
                     </tr>
                     <!-- Current Agent -->
                     <?php if (!empty($data['class_data']['agent_name']) || !empty($data['class_data']['class_agent'])): ?>
                     <tr>
                        <td class="py-2">
                           <div class="d-flex align-items-center">
                              <div class="d-flex bg-success-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-person-badge text-success" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">Current Agent :</p>
                           </div>
                        </td>
                        <td class="py-2">
                           <div class="fw-semibold mb-0">
                              <?php echo esc_html($data['class_data']['agent_name'] ?? 'N/A'); ?>
                              <?php if (!empty($data['class_data']['class_agent'])): ?>
                                 <div class="fs-9 text-muted">ID: <?php echo esc_html($data['class_data']['class_agent']); ?></div>
                              <?php endif; ?>
                           </div>
                        </td>
                     </tr>
                     <?php endif; ?>
                     <!-- Backup Agents -->
                     <?php if (!empty($data['class_data']['backup_agent_names']) && is_array($data['class_data']['backup_agent_names'])): ?>
                     <tr>
                        <td class="py-2">
                           <div class="d-flex align-items-center">
                              <div class="d-flex bg-info-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-people text-info" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">Backup Agents :</p>
                           </div>
                        </td>
                        <td class="py-2">
                           <div class="fw-semibold mb-0">
                              <span class="badge badge-phoenix fs-10 badge-phoenix-info me-2"><?php echo count($data['class_data']['backup_agent_names']); ?> Backup<?php echo count($data['class_data']['backup_agent_names']) !== 1 ? 's' : ''; ?></span>
                              <div class="mt-1">
                                 <?php foreach ($data['class_data']['backup_agent_names'] as $backupAgent): ?>
                                    <div class="fs-9 mb-1">
                                       <i class="bi bi-person me-1"></i>
                                       <?php echo esc_html($backupAgent['name'] ?? $backupAgent); ?>
                                       <?php if (isset($backupAgent['id'])): ?>
                                          <span class="text-muted">(ID: <?php echo esc_html($backupAgent['id']); ?>)</span>
                                       <?php endif; ?>
                                    </div>
                                 <?php endforeach; ?>
                              </div>
                           </div>
                        </td>
                     </tr>
                     <?php endif; ?>
                     <!-- Supervisor -->
                     <?php if (!empty($data['class_data']['supervisor_name']) || !empty($data['class_data']['project_supervisor_id'])): ?>
                     <tr>
                        <td class="py-2">
                           <div class="d-flex align-items-center">
                              <div class="d-flex bg-warning-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-person-gear text-warning" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">Supervisor :</p>
                           </div>
                        </td>
                        <td class="py-2">
                           <div class="fw-semibold mb-0">
                              <?php echo esc_html($data['class_data']['supervisor_name'] ?? 'N/A'); ?>
                              <?php if (!empty($data['class_data']['project_supervisor_id'])): ?>
                                 <div class="fs-9 text-muted">ID: <?php echo esc_html($data['class_data']['project_supervisor_id']); ?></div>
                              <?php endif; ?>
                           </div>
                        </td>
                     </tr>
                     <?php endif; ?>
                  </tbody>
               </table>
            </div>
         </div>
      </div>

      <!-- Hidden fields for form submission -->
      <input type="hidden" name="client_id" value="<?php echo esc_attr($data['class_data']['client_id'] ?? ''); ?>">
      <input type="hidden" name="site_id" value="<?php echo esc_attr($data['class_data']['site_id'] ?? ''); ?>">
      <input type="hidden" id="class_type" name="class_type" value="<?php echo esc_attr($data['class_data']['class_type'] ?? ''); ?>">
      <input type="hidden" name="class_code_hidden" value="<?php echo esc_attr($data['class_data']['class_code'] ?? ''); ?>">
      <input type="hidden" id="class_duration" name="class_duration" value="<?php echo esc_attr($data['class_data']['class_duration'] ?? ''); ?>">
      <input type="hidden" name="class_start_date" value="<?php echo esc_attr($data['class_data']['original_start_date'] ?? ''); ?>">
      <input type="hidden" name="class_subject" value="<?php echo esc_attr($data['class_data']['class_subject'] ?? ''); ?>">

         <?php
         // Extract schedule data for pre-population
         $scheduleData = $data['class_data']['schedule_data'] ?? [];
         
         // Handle both string and array formats for schedule data
         if (is_string($scheduleData)) {
             $scheduleData = json_decode($scheduleData, true) ?? [];
         }
         
         $schedulePattern = $scheduleData['pattern'] ?? '';
         $scheduleDays = $scheduleData['selectedDays'] ?? $scheduleData['days'] ?? [];
         $scheduleStartTime = $scheduleData['start_time'] ?? '';
         $scheduleEndTime = $scheduleData['end_time'] ?? '';
         $scheduleStartDate = $scheduleData['start_date'] ?? $scheduleData['startDate'] ?? '';
         $scheduleEndDate = $scheduleData['end_date'] ?? $scheduleData['endDate'] ?? '';
         $holidayOverrides = $scheduleData['holiday_overrides'] ?? $scheduleData['holidayOverrides'] ?? [];
         $timeData = $scheduleData['timeData'] ?? [];
         $perDayTimes = isset($timeData['perDayTimes']) ? $timeData['perDayTimes'] : [];

         // Convert holiday overrides to JSON string for the hidden field
         $holidayOverridesJson = !empty($holidayOverrides) ? json_encode($holidayOverrides) : '';
         
         // Debug: Log holiday overrides extraction
         if (isset($_GET['debug']) && $_GET['debug'] === '1') {
             echo "<script>\n";
             echo "console.log('=== Holiday Overrides Debug ===');\n";
             echo "console.log('Raw schedule data holiday_overrides:', " . json_encode($scheduleData['holiday_overrides'] ?? null) . ");\n";
             echo "console.log('Raw schedule data holidayOverrides:', " . json_encode($scheduleData['holidayOverrides'] ?? null) . ");\n";
             echo "console.log('Extracted holidayOverrides:', " . json_encode($holidayOverrides) . ");\n";
             echo "console.log('Holiday overrides JSON string:', " . json_encode($holidayOverridesJson) . ");\n";
             echo "</script>\n";
         }
         
         // Normalize perDayTimes to JavaScript expected format (camelCase) and filter corrupt data
         if (!empty($perDayTimes)) {
             $validDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
             $normalizedPerDayTimes = [];
             
             foreach ($perDayTimes as $day => $times) {
                 // Only process valid day names, skip numeric keys
                 if (in_array($day, $validDays) && is_array($times)) {
                     $normalizedPerDayTimes[$day] = [
                         'startTime' => $times['start_time'] ?? $times['startTime'] ?? '',
                         'endTime' => $times['end_time'] ?? $times['endTime'] ?? '',
                         'duration' => $times['duration'] ?? ''
                     ];
                 }
             }
             
             $perDayTimes = $normalizedPerDayTimes;
             
             // Update the schedule data with normalized format
             if (!isset($scheduleData['timeData'])) {
                 $scheduleData['timeData'] = [];
             }
             $scheduleData['timeData']['perDayTimes'] = $perDayTimes;
             
             if (isset($_GET['debug']) && $_GET['debug'] === '1') {
                 echo "<script>\n";
                 echo "console.log('=== Cleaned Per Day Times ===');\n";
                 echo "console.log('Normalized perDayTimes:', " . json_encode($perDayTimes) . ");\n";
                 echo "</script>\n";
             }
         }
         
         // Handle legacy data or missing perDayTimes
         if (empty($perDayTimes) && !empty($scheduleDays)) {
             // Check if we have individual day times in the schedule data
             $foundDayTimes = false;
             foreach ($scheduleDays as $day) {
                 if (isset($scheduleData[$day])) {
                     $foundDayTimes = true;
                     $perDayTimes[$day] = [
                         'startTime' => $scheduleData[$day]['start_time'] ?? $scheduleData[$day]['start'] ?? '',
                         'endTime' => $scheduleData[$day]['end_time'] ?? $scheduleData[$day]['end'] ?? '',
                         'duration' => $scheduleData[$day]['duration'] ?? ''
                     ];
                 }
             }
             
             // If no individual day times found, use the general start/end times
             if (!$foundDayTimes && (!empty($scheduleStartTime) || !empty($scheduleEndTime))) {
                 foreach ($scheduleDays as $day) {
                     $perDayTimes[$day] = [
                         'startTime' => $scheduleStartTime ?: '',
                         'endTime' => $scheduleEndTime ?: ''
                     ];
                 }
             }
             
             // Update the schedule data structure
             if (!empty($perDayTimes)) {
                 if (!isset($scheduleData['timeData'])) {
                     $scheduleData['timeData'] = [];
                 }
                 $scheduleData['timeData']['mode'] = 'per-day';
                 $scheduleData['timeData']['perDayTimes'] = $perDayTimes;
                 
                 if (isset($_GET['debug']) && $_GET['debug'] === '1') {
                     echo "<script>\n";
                     echo "console.log('=== Migrated Per Day Times ===');\n";
                     echo "console.log('Migrated perDayTimes:', " . json_encode($perDayTimes) . ");\n";
                     echo "</script>\n";
                 }
             }
         }
         ?>

      <!-- Class Schedule Form Section -->
         <!-- Date Range -->
         <div class="row">
            <div class="col-md-4 ">
                  <label for="schedule_start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                  <input type="date" id="schedule_start_date" name="schedule_start_date" class="form-control" placeholder="YYYY-MM-DD" value="<?php echo esc_attr($scheduleStartDate); ?>" required>
                  <div class="invalid-feedback">Please select a start date.</div>
                  <div class="valid-feedback">Looks good!</div>
            </div>
         </div>
         <?php echo section_divider(); ?>
         <?php echo section_header('Class Schedule', 'Update the recurring schedule for this class.'); ?>
         <!-- Schedule Pattern Selection -->
         <div class="row mb-3">
            <div class="col-md-4 mb-3">
               <div class="mb-3">
                  <label for="schedule_pattern" class="form-label">Schedule Pattern <span class="text-danger">*</span></label>
                  <select id="schedule_pattern" name="schedule_pattern" class="form-select" required>
                     <option value="">Select</option>
                     <option value="weekly" <?php echo ($schedulePattern == 'weekly') ? 'selected' : ''; ?>>Weekly (Every Week)</option>
                     <option value="biweekly" <?php echo ($schedulePattern == 'biweekly') ? 'selected' : ''; ?>>Bi-Weekly (Every Two Weeks)</option>
                     <option value="monthly" <?php echo ($schedulePattern == 'monthly') ? 'selected' : ''; ?>>Monthly</option>
                     <option value="custom" <?php echo ($schedulePattern == 'custom') ? 'selected' : ''; ?>>Custom</option>
                  </select>
                  <div class="invalid-feedback">Please select a schedule pattern.</div>
                  <div class="valid-feedback">Looks good!</div>
               </div>
            </div>

            <!-- Day Selection (for weekly/biweekly) -->
            <div class="col-md-12 mb-3 <?php echo (!in_array($schedulePattern, ['weekly', 'biweekly'])) ? 'd-none' : ''; ?>" id="day-selection-container">
               <label class="form-label">Days of Week <span class="text-danger">*</span></label>
               <div class="days-checkbox-group">
                  <?php
                  $weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                  foreach ($weekDays as $index => $day):
                     $isChecked = is_array($scheduleDays) && in_array($day, $scheduleDays);
                     $isFirst = ($index === 0);
                  ?>
                  <div class="form-check form-check-inline">
                     <input class="form-check-input schedule-day-checkbox" type="checkbox" id="schedule_day_<?php echo strtolower($day); ?>" name="schedule_days[]" value="<?php echo $day; ?>" <?php echo $isChecked ? 'checked' : ''; ?> <?php echo $isFirst ? 'required' : ''; ?>>
                     <label class="form-check-label" for="schedule_day_<?php echo strtolower($day); ?>"><?php echo $day; ?></label>
                  </div>
                  <?php endforeach; ?>
               </div>
               <div class="mt-2">
                  <button type="button" class="btn btn-sm btn-outline-primary" id="select-all-days">Select All</button>
                  <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-all-days">Clear All</button>
               </div>
               <div class="invalid-feedback d-none">Please select at least one day.</div>
               <div class="valid-feedback d-none">Looks good!</div>
            </div>

            <!-- Day of Month (for monthly) -->
            <div class="col-md-4 mb-3 d-none" id="day-of-month-container">
               <div class="mb-3">
                  <label for="schedule_day_of_month" class="form-label">Day of Month <span class="text-danger">*</span></label>
                  <select id="schedule_day_of_month" name="schedule_day_of_month" class="form-select">
                     <option value="">Select</option>
                     <?php for ($i = 1; $i <= 31; $i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                     <?php endfor; ?>
                     <option value="last">Last Day</option>
                  </select>
                  <div class="invalid-feedback">Please select a day of the month.</div>
                  <div class="valid-feedback">Looks good!</div>
               </div>
            </div>
         </div>

         <!-- Time Selection -->
         <!-- Single Time Controls (shown when no days selected) -->
         <div id="single-time-controls" class="row mb-3 d-none">
            <!-- This section is now hidden and will be replaced by per-day controls when days are selected -->
         </div>
         
         <!-- Per-Day Time Controls (shown when multiple days selected) -->
         <div id="per-day-time-controls" class="d-none mt-3 mb-4">
            <div class="">
               <?php echo section_header('Set Times for Each Day', 'Configure individual start and end times for each selected day.'); ?>
            </div>
            <!-- Container for dynamically generated day sections -->
            <div id="per-day-sections-container" class="row g-4">
               <!-- Day sections will be generated here by JavaScript -->
            </div>
            <!-- Hidden template for day time section -->
            <div id="day-time-section-template" class="per-day-time-section col-sm-6 col-md-4 col-lg-3 d-none" data-day="">
               <div class="card h-100 border">
                  <div class="card-body">
                     <div class="d-flex justify-content-between align-items-center ">
                        <h6 class="card-title  day-name"></h6>
                        <button type="button" class="btn btn-sm btn-outline-secondary copy-to-all-btn" title="Copy to all days">
                        <i class="bi bi-files"></i>
                        </button>
                     </div>
                     <div class="">
                        <label class="form-label">Start Time <span class="text-danger">*</span></label>
                        <select class="form-select day-start-time" data-day="" required>
                           <option value="">Select</option>
                           <?php
                              // Generate time options from 6:00 AM to 8:00 PM in 30-minute increments
                              $start = strtotime('06:00:00');
                              $end = strtotime('20:00:00');
                              $interval = 30 * 60; // 30 minutes in seconds

                              for ($time = $start; $time <= $end; $time += $interval) {
                                 $timeStr = date('H:i', $time);
                                 echo '<option value="' . $timeStr . '">' . date('g:i A', $time) . '</option>';
                              }
                              ?>
                        </select>
                        <div class="invalid-feedback">Please select a start time.</div>
                     </div>
                     <div class="mb-2">
                        <label class="form-label">End Time <span class="text-danger">*</span></label>
                        <select class="form-select day-end-time" data-day="" required>
                           <option value="">Select</option>
                           <?php
                              // Generate time options from 6:30 AM to 8:30 PM in 30-minute increments
                              $start = strtotime('06:30:00');
                              $end = strtotime('20:30:00');
                              $interval = 30 * 60; // 30 minutes in seconds

                              for ($time = $start; $time <= $end; $time += $interval) {
                                 $timeStr = date('H:i', $time);
                                 echo '<option value="' . $timeStr . '">' . date('g:i A', $time) . '</option>';
                              }
                              ?>
                        </select>
                        <div class="invalid-feedback">Please select an end time.</div>
                     </div>
                     <small class="text-muted day-duration-display  d-none">Duration: <span class="duration-value badge badge-phoenix badge-phoenix-info">-</span> hours</small>
                  </div>
               </div>
            </div>
         </div>
      <?php echo section_divider(); ?>
         <div class="col-md-4" id="schedule-update-end-date-container">
            <?php echo section_header('Class End Date', 'If you make any changes to the schedule, you will need to recalculate the end date.'); ?>
            <label for="schedule_end_date" class="form-label">Estimated End Date <span class="text-danger">*</span></label>
            <input type="date" id="schedule_end_date" name="schedule_end_date" class="form-control readonly-field" placeholder="YYYY-MM-DD" required>
            <div class="invalid-feedback">Please generate the end date.</div>
            <div class="valid-feedback">Looks good!</div>
            <button type="button" class="btn btn-subtle-warning mb-2 mt-2" id="calculate_schedule_end_date-btn">
               Calculate Estimated End Date
            </button>
         </div>
      <?php echo section_divider(); ?>
         <!-- Exception Dates -->
         <div class="mb-4">
            <?php echo section_header('Exception Dates', 'Add dates when classes will not occur (e.g. client closed).'); ?>
            <!-- Container for all exception date rows -->
            <div id="exception-dates-container" data-populated="false"></div>

            <!-- Hidden Template Row (initially d-none) -->
            <div class="row exception-date-row align-items-center d-none" id="exception-date-row-template">
               <!-- Exception Date -->
               <div class="col-md-3 mb-2">
                  <div class="mb-1">
                     <label class="form-label">Date</label>
                     <input type="date" name="exception_dates[]" class="form-control" placeholder="YYYY-MM-DD">
                     <div class="invalid-feedback">Please select a valid date.</div>
                     <div class="valid-feedback">Looks good!</div>
                  </div>
               </div>

               <!-- Reason -->
               <div class="col-md-3 mb-2">
                  <div class="mb-1">
                     <label class="form-label">Reason</label>
                     <select name="exception_reasons[]" class="form-select">
                        <option value="">Select</option>
                        <option value="Client Cancelled">Client Cancelled</option>
                        <option value="Agent Absent">Agent Absent</option>
                        <option value="Public Holiday">Public Holiday</option>
                        <option value="Other">Other</option>
                     </select>
                     <div class="invalid-feedback">Please select a reason.</div>
                     <div class="valid-feedback">Looks good!</div>
                  </div>
               </div>

               <!-- Remove Button -->
               <div class="col-md-2 mt-2">
                  <div class="d-flex h-100 align-items-end">
                     <button type="button" class="btn btn-outline-danger btn-sm remove-exception-btn form-control date-remove-btn">Remove</button>
                  </div>
               </div>
            </div>

            <!-- Add Exception Button -->
            <button type="button" class="btn btn-outline-primary btn-sm" id="add-exception-date-btn">
            <i class="bi bi-plus-circle me-1"></i> Add Exception Date
            </button>
         </div>

         <!-- Public Holidays Section -->
         <div class="mb-4 col-md-8">
            <?php echo section_header('Public Holidays in Schedule', 'By default, classes are not scheduled on public holidays. The system will only show holidays that conflict with your class schedule (when a holiday falls on a scheduled class day). You can override specific holidays to include them in the schedule.'); ?>

            <!-- No holidays message -->
            <div id="no-holidays-message" class="bd-callout bd-callout-info">
               No public holidays that conflict with your class schedule were found. Holidays are only shown when they fall on a scheduled class day.
            </div>

            <!-- Holidays table container -->
            <div id="holidays-table-container" class="card-body card-body card px-5 d-none" >
               <div class="table-responsive">
                  <table class="table table-sm fs-9 mb-0 table-hover">
                     <thead>
                        <tr>
                           <th style="width: 50px;">
                              <div class="form-check">&nbsp;</div>
                           </th>
                           <th>Date</th>
                           <th>Holiday</th>
                           <th>Override</th>
                        </tr>
                     </thead>
                     <tbody id="holidays-list">
                        <!-- Holidays will be populated here dynamically -->
                     </tbody>
                  </table>
               </div>

               <div class="d-flex justify-content-between mt-2">
                  <div>
                     <button type="button" class="btn btn-outline-secondary btn-sm" id="skip-all-holidays-btn">Skip All Holidays</button>
                     <button type="button" class="btn btn-outline-primary btn-sm" id="override-all-holidays-btn">Override All Holidays</button>
                  </div>
               </div>
            </div>
         </div>

         <!-- Holiday Row Template (for JavaScript) -->
         <template id="holiday-row-template">
            <tr class="holiday-row">
               <td>
                  <div class="form-check">
                     <input class="form-check-input holiday-override-checkbox" type="checkbox" id="override-holiday-{id}" data-date="{date}">
                     <label class="form-check-label" for="override-holiday-{id}"></label>
                  </div>
               </td>
               <td class="holiday-date">{formatted_date}</td>
               <td class="holiday-name">{name}</td>
               <td class="holiday-status">
                  <span class="badge bg-danger holiday-skipped">Skipped</span>
                  <span class="badge bg-info holiday-overridden d-none">Included</span>
               </td>
            </tr>
         </template>

         <!-- Hidden input to store holiday override data -->
         <input type="hidden" id="holiday_overrides" name="schedule_data[holiday_overrides]" value="<?php echo esc_attr($holidayOverridesJson); ?>">

         <!-- Hidden inputs to store schedule data in the format expected by the backend -->
         <div id="schedule-data-container">
            <!-- These will be populated dynamically via JavaScript -->
         </div>

         <!-- Schedule Statistics Section -->
         <!-- Schedule Statistics Toggle Button -->
         <div class="mt-3 mb-3">
            <button type="button" class="btn btn-outline-primary btn-sm" id="toggle-statistics-btn">
               <i class="bi bi-bar-chart-line me-1"></i> View Schedule Statistics
            </button>
            <div class="clearfix"></div>
            <small class="text-muted">Click to view detailed statistics about the training schedule</small>
         </div>

         <!-- Schedule Statistics Section (hidden by default) -->
         <div class="card shadow-none border mb-3 d-none" id="schedule-statistics-section" data-component-card="data-component-card">
         <div class="card-header p-3 border-bottom bg-body">
            <h4 class="text-body mb-0" data-anchor="schedule-statistics" id="schedule-statistics">
               Schedule Statistics
               <a class="anchorjs-link" aria-label="Anchor" data-anchorjs-icon="#" href="#schedule-statistics" style="margin-left:0.1875em; padding:0 0.1875em;"></a>
            </h4>
         </div>
         <div class="card-body p-4">
            <div class="table-responsive scrollbar mb-3">
               <table class="table table-sm fs-9 mb-0 overflow-hidden">
               <thead class="text-body">
                  <tr>
                     <th class="sort pe-1 align-middle white-space-nowrap">Category</th>
                     <th class="sort pe-1 align-middle white-space-nowrap">Metric</th>
                     <th class="sort pe-1 align-middle white-space-nowrap">Value</th>
                  </tr>
               </thead>
               <tbody id="schedule-statistics-table">
                  <!-- Training Duration Statistics -->
                  <tr class="ydcoza-table-subheader">
                     <th colspan="3">Training Duration</th>
                  </tr>
                  <tr>
                     <td rowspan="3" class="align-middle">Time Period</td>
                     <td>Total Calendar Days</td>
                     <td id="stat-total-days">-</td>
                  </tr>
                  <tr>
                     <td>Total Weeks</td>
                     <td id="stat-total-weeks">-</td>
                  </tr>
                  <tr>
                     <td>Total Months</td>
                     <td id="stat-total-months">-</td>
                  </tr>

                  <!-- Class Session Statistics -->
                  <tr class="ydcoza-table-subheader">
                     <th colspan="3">Class Sessions</th>
                  </tr>
                  <tr>
                     <td rowspan="3" class="align-middle">Sessions</td>
                     <td>Total Scheduled Classes</td>
                     <td id="stat-total-classes">-</td>
                  </tr>
                  <tr>
                     <td>Total Training Hours</td>
                     <td id="stat-total-hours">-</td>
                  </tr>
                  <tr>
                     <td>Average Hours per Month</td>
                     <td id="stat-avg-hours-month">-</td>
                  </tr>

                  <!-- Attendance Impact Statistics -->
                  <tr class="ydcoza-table-subheader">
                     <th colspan="3">Attendance Impact</th>
                  </tr>
                  <tr>
                     <td rowspan="3" class="align-middle">Adjustments</td>
                     <td>Holidays Affecting Classes</td>
                     <td id="stat-holidays-affecting">-</td>
                  </tr>
                  <tr>
                     <td>Exception Dates</td>
                     <td id="stat-exception-dates">-</td>
                  </tr>
                  <tr>
                     <td>Actual Training Days</td>
                     <td id="stat-actual-days">-</td>
                  </tr>
               </tbody>
               </table>
            </div>
         </div>
         </div>

      </div>

      <!-- Pass existing schedule data to JavaScript for form population -->
      <script>
      <?php if (!empty($scheduleData)): ?>
      window.existingScheduleData = <?php echo json_encode($scheduleData); ?>;
      <?php endif; ?>
      
      <?php 
      // Load public holidays data for the schedule form
      try {
          $publicHolidaysController = \WeCozaClasses\Controllers\PublicHolidaysController::getInstance();
          $currentYear = date('Y');
          $nextYear = $currentYear + 1;
          
          // Get holidays for current and next year to cover class schedules
          $currentYearHolidays = $publicHolidaysController->getHolidaysForCalendar($currentYear);
          $nextYearHolidays = $publicHolidaysController->getHolidaysForCalendar($nextYear);
          $allHolidays = array_merge($currentYearHolidays, $nextYearHolidays);
          
          // Make holidays available to JavaScript
          echo "window.wecozaPublicHolidays = " . json_encode(['events' => $allHolidays]) . ";\n";
      } catch (\Exception $e) {
          error_log('WeCoza Classes Plugin: Error loading public holidays in update form: ' . $e->getMessage());
          echo "window.wecozaPublicHolidays = { events: [] }; // Error loading holidays\n";
      }
      ?>
      </script>

      <!-- Class Date History Section -->
      <div class="mb-4 mt-3">
         <h5>Class Date History</h5>
         <p class="text-muted small mb-3">Add stop and restart dates for this class. A class can have multiple stop and restart dates.</p>

         <!-- Container for all date history rows -->
         <div id="date-history-container"></div>

         <!-- Hidden Template Row (initially d-none) -->
         <div class="row date-history-row d-none" id="date-history-row-template">
            <!-- Stop Date -->
            <div class="col-md-3 mb-2">
               <label class="form-label">Stop Date</label>
               <input type="date" name="stop_dates[]" class="form-control form-control-sm">
               <div class="invalid-feedback">Please select a valid date.</div>
               <div class="valid-feedback">Looks good!</div>
            </div>

            <!-- Restart Date -->
            <div class="col-md-3 mb-2">
               <label class="form-label">Restart Date</label>
               <input type="date" name="restart_dates[]" class="form-control form-control-sm">
               <div class="invalid-feedback">Please select a valid date.</div>
               <div class="valid-feedback">Looks good!</div>
            </div>

            <!-- Remove Button -->
            <div class="col-md-2 mb-2">
               <label class="form-label invisible">&nbsp;</label>
               <button type="button" class="btn btn-outline-danger btn-sm remove-date-row-btn form-control date-remove-btn">Remove</button>
            </div>
         </div>

         <!-- Add Row Button -->
         <button type="button" class="btn btn-outline-primary btn-sm" id="add-date-history-btn">
         <i class="bi bi-plus-circle me-1"></i> Add Stop/Restart Dates
         </button>
      </div>

      <?php echo section_divider(); ?>

      <!-- ===== Section: Funding & Exam Details ===== -->
      <?php echo section_header('Funding & Exam Details'); ?>
      <div class="row">
         <!-- SETA Funded -->
         <div class="col-md-2 mb-3">
            <div class="mb-3">
               <label for="seta_funded" class="form-label">SETA Funded? <span class="text-danger">*</span></label>
               <select id="seta_funded" name="seta_funded" class="form-select" required>
                  <option value="">Select</option>
                  <?php foreach ($data['yes_no_options'] as $option): ?>
                     <option value="<?php echo $option['id']; ?>" <?php echo (isset($data['class_data']['seta_funded']) && $data['class_data']['seta_funded'] == $option['id']) ? 'selected' : ''; ?>><?php echo $option['name']; ?></option>
                  <?php endforeach; ?>
               </select>
               <div class="invalid-feedback">Please select if the class is SETA funded.</div>
               <div class="valid-feedback">Looks good!</div>
            </div>
         </div>

         <!-- SETA (conditionally displayed) -->
         <?php
         $setaFunded = $data['class_data']['seta_funded'] ?? '';
         $showSeta = ($setaFunded == 'Yes' || $setaFunded == '1');
         ?>
         <div class="col-md-2 mb-3" id="seta_container" style="display: <?php echo $showSeta ? 'block' : 'none'; ?>;">
            <div class="mb-3">
               <label for="seta_id" class="form-label">SETA <span class="text-danger">*</span></label>
               <select id="seta_id" name="seta_id" class="form-select">
                  <option value="">Select</option>
                  <?php foreach ($data['setas'] as $seta): ?>
                     <option value="<?php echo $seta['id']; ?>" <?php echo (isset($data['class_data']['seta']) && $data['class_data']['seta'] == $seta['id']) ? 'selected' : ''; ?>><?php echo $seta['name']; ?></option>
                  <?php endforeach; ?>
               </select>
               <div class="invalid-feedback">Please select a SETA.</div>
               <div class="valid-feedback">Looks good!</div>
            </div>
         </div>

         <!-- Exam Class -->
         <div class="col-md-2 mb-3">
            <div class="mb-3">
               <label for="exam_class" class="form-label">Exam Class <span class="text-danger">*</span></label>
               <select id="exam_class" name="exam_class" class="form-select" required>
                  <option value="">Select</option>
                  <?php foreach ($data['yes_no_options'] as $option): ?>
                     <option value="<?php echo $option['id']; ?>" <?php echo (isset($data['class_data']['exam_class']) && $data['class_data']['exam_class'] == $option['id']) ? 'selected' : ''; ?>><?php echo $option['name']; ?></option>
                  <?php endforeach; ?>
               </select>
               <div class="invalid-feedback">Please select if this is an exam class.</div>
               <div class="valid-feedback">Looks good!</div>
            </div>
         </div>

         <!-- Exam Type (conditionally displayed) -->
         <?php
         $examClass = $data['class_data']['exam_class'] ?? '';
         $showExamType = ($examClass == 'Yes' || $examClass == '1');
         ?>
         <div class="col-md-2 mb-3">
            <div id="exam_type_container" style="display: <?php echo $showExamType ? 'block' : 'none'; ?>;">
               <div class="mb-3">
                  <label for="exam_type" class="form-label">Exam Type</label>
                  <input type="text" id="exam_type" name="exam_type" class="form-control" placeholder="Enter exam type" value="<?php echo esc_attr($data['class_data']['exam_type'] ?? ''); ?>">
                  <div class="invalid-feedback">Please provide the exam type.</div>
                  <div class="valid-feedback">Looks good!</div>
               </div>
            </div>
         </div>
      </div>

      <!-- Class Learners Section -->
      <?php echo section_header('Class Learners <span class="text-danger">*</span>', 'Select learners for this class and manage their status.'); ?>
      <div class="row mb-4 col-md-9">
         <!-- Learner Selection -->
         <div class="col-md-4">
            <!-- For multi-select with floating labels, we need a custom approach -->
            <div class="mb-3">
               <label for="add_learner" class="form-label">Select Learners</label>
               <select id="add_learner" name="add_learner[]" class="form-select" aria-label="Learner selection" multiple>
                  <?php foreach ($data['learners'] as $learner): ?>
                     <option value="<?php echo $learner['id']; ?>"><?php echo $learner['name']; ?></option>
                  <?php endforeach; ?>
               </select>
               <div class="form-text">Select multiple learners to add to this class. Hold Ctrl/Cmd to select multiple.</div>
               <div class="invalid-feedback">Please select at least one learner.</div>
               <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-selected-learners-btn">
                  <i class="bi bi-person-plus me-1"></i> Add Selected Learners
               </button>
            </div>
         </div>

         <!-- Learners Table -->
         <div class="col-md-8">
            <div class="mb-3">
               <div class="form-label mb-2">Class Learners</div>
               <div id="class-learners-container" class="card-body card px-5 ">
                  <div class="bd-callout bd-callout-info" id="no-learners-message">
                     No learners added to this class yet. At least one learner is required. Select learners from the list and click "Add Selected Learners".
                  </div>
                  <table class="table table-sm fs-9 d-none" id="class-learners-table">
                     <thead>
                        <tr>
                           <th>Learner</th>
                           <th>Level/Module</th>
                           <th>Status</th>
                           <th>Actions</th>
                        </tr>
                     </thead>
                     <tbody id="class-learners-tbody">
                        <!-- Learner rows will be added here dynamically -->
                     </tbody>
                  </table>
               </div>
               <!-- Hidden field to store learner data -->
               <input type="hidden" id="class_learners_data" name="class_learners_data" value="">
            </div>
         </div>
      </div>

      <!-- Exam Learners (conditionally displayed) -->
      <div class="row mt-5" id="exam_learners_container" style="display: none;">
      <?php echo section_divider(); ?>
         <div class="col-12">
            <?php echo section_header('Select Learners Taking Exams', 'Not all learners in an exam class necessarily take exams. Select which learners will take exams.'); ?>

            <div class="row mb-4 col-md-9">
               <!-- Exam Learner Selection -->
               <div class="col-md-4">
                  <!-- For multi-select with floating labels, we need a custom approach -->
                  <div class="mb-3">
                     <label for="add_learner" class="form-label">Select Learners</label>
                     <select id="exam_learner_select" name="exam_learner_select[]" class="form-select" aria-label="Exam learner selection" multiple>
                        <!-- Will be populated dynamically with class learners -->
                     </select>
                     <div class="form-text">Select learners who will take exams in this class. Hold Ctrl/Cmd to select multiple.</div>
                     <div class="invalid-feedback">Please select at least one learner for exams.</div>
                     <div class="valid-feedback">Looks good!</div>
                     <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-selected-exam-learners-btn">
                        <i class="bi bi-person-plus me-1"></i> Add Selected Exam Learners
                     </button>
                  </div>
               </div>

               <!-- Exam Learners List -->
               <div class="col-md-8">
                  <div class="mb-3">
                     <div class="form-label mb-2">Learners Taking Exams</div>
                     <div id="exam-learners-list" class="card-body card px-5">
                        <div class="alert alert-info" id="no-exam-learners-message">
                           No exam learners added yet. Select learners from the list and click "Add Selected Exam Learners".
                        </div>
                        <table class="table table-sm fs-9 d-none" id="exam-learners-table">
                           <thead>
                              <tr>
                                 <th>Learner</th>
                                 <th>Level/Module</th>
                                 <th>Status</th>
                                 <th>Actions</th>
                              </tr>
                           </thead>
                           <tbody id="exam-learners-tbody">
                              <!-- Exam learner rows will be added here dynamically -->
                           </tbody>
                        </table>
                     </div>
                  </div>
               </div>
            </div>

            <!-- Hidden field to store exam learners data -->
            <input type="hidden" id="exam_learners" name="exam_learners" value="">
         </div>
      </div>

      <?php echo section_divider(); ?>
      <?php echo section_header('Class Notes & QA', 'Add operational notes and quality assurance information for this class.'); ?>
      <!-- Class Notes & QA Information -->
         <div class="card-body card px-5">
         
         <!-- Add Note Button -->
         <div class="col-md-12">
           
            <!-- Class Notes Container for dynamic display -->
            <div id="class-notes-container" class="mt-3">               
               <!-- Notes Search and Filter Controls -->
               <div class="notes-controls mb-3">
                  <div class="row g-2 mb-2">
                     <div class="col-md-2">
                        <button type="button" class="btn btn-primary w-100" id="add-class-note-btn" data-bs-toggle="modal" data-bs-target="#classNoteModal">
                           <i class="bi bi-plus-circle me-1"></i> Add New Class Note
                        </button>
                     </div>
                     <div class="col-md-2">
                        <select class="form-select form-select-sm" id="notes-priority-filter">
                           <option value="">All Priorities</option>
                           <option value="high">High Priority</option>
                           <option value="medium">Medium Priority</option>
                           <option value="low">Low Priority</option>
                        </select>
                     </div>
                     <div class="col-md-2">
                        <select class="form-select form-select-sm" id="notes-sort">
                           <option value="newest">Newest First</option>
                           <option value="oldest">Oldest First</option>
                           <option value="updated">Recently Updated</option>
                           <option value="priority">By Priority</option>
                           <option value="category">By Category</option>
                           <option value="title">By Title</option>
                        </select>
                     </div>
                     <div class="col-md-2">
                           <button type="button" class="btn btn-outline-secondary btn-sm" id="clear-notes-filters" title="Clear all filters">
                              <i class="bi bi-arrow-clockwise"></i> Reset Filters
                           </button>
                     </div>
                  </div>
                  <div class="row g-2">

                     <!-- Notes Display Header -->
                     <div class="d-flex justify-content-between align-items-center mb-3 col-md-2 mt-4">
                        <div class="d-flex align-items-center gap-2">
                           <span class="badge ms-2 badge badge-phoenix badge-phoenix-warning " id="notes-count">0 notes</span>
                        </div>
                     </div>
                     
                     <!-- Priority Legend -->
                     <div class="col-md-8 mt-4">
                        <div class="priority-legend d-flex align-items-center gap-3 flex-wrap">
                           <span class="legend-title small text-muted me-2">Priority:</span>
                           <div class="legend-item d-flex align-items-center">
                              <div class="legend-color-box priority-high"></div>
                              <span class="legend-label small">High</span>
                           </div>
                           <div class="legend-item d-flex align-items-center">
                              <div class="legend-color-box priority-medium"></div>
                              <span class="legend-label small">Medium</span>
                           </div>
                           <div class="legend-item d-flex align-items-center">
                              <div class="legend-color-box priority-low"></div>
                              <span class="legend-label small">Low</span>
                           </div>
                        </div>
                     </div>
                  </div>
               </div>
               
               <!-- Notes Display Area -->
               <div id="notes-display-area">
                  <!-- Loading state -->
                  <div id="notes-loading" class="text-center py-3 d-none">
                     <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Loading notes...</span>
                     </div>
                     <div class="mt-2 text-muted small">Loading notes...</div>
                  </div>
                  
                  <!-- Empty state -->
                  <div id="notes-empty" class="text-center py-4 text-muted">
                     <i class="bi bi-sticky-note display-4 mb-2"></i>
                     <p class="mb-0">No notes yet. Click "Add New Class Note" to get started.</p>
                  </div>
                  
                  <!-- Notes list -->
                  <div id="notes-list">
                     <!-- Notes will be dynamically loaded here -->
                  </div>
                  
                  <!-- No results state -->
                  <div id="notes-no-results" class="text-center py-3 text-muted d-none">
                     <i class="bi bi-search display-4 mb-2"></i>
                     <p class="mb-0">No notes found matching your search criteria.</p>
                     <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="clear-notes-filters">
                        Clear Filters
                     </button>
                  </div>
               </div>
               
               <!-- Notes Pagination -->
               <nav aria-label="Notes pagination" id="notes-pagination-nav" class="mt-3" style="display: none;">
                  <ul class="pagination pagination-sm justify-content-center" id="notes-pagination">
                     <!-- Pagination will be generated here -->
                  </ul>
               </nav>
            </div>
         </div>
         </div>

      <!-- QA Visit Dates and Reports Section -->
      <div class="mt-4">
         <p class="text-muted small mb-3">Add QA visit dates and upload corresponding reports for each visit.</p>
         <!-- Container for all QA visit date rows -->
         <div id="qa-visits-container"></div>

         <!-- Hidden Template Row (initially d-none) -->
         <div class="row qa-visit-row d-none" id="qa-visit-row-template">
            <!-- Visit Date -->
            <div class="col-md-2 mb-2">
               <div class="mb-3">
                  <label class="form-label">Visit Date</label>
                  <input type="date" name="qa_visit_dates[]" class="form-control form-control-sm">
                  <div class="invalid-feedback">Please select a valid date.</div>
               </div>
            </div>

            <!-- Visit Type -->
            <div class="col-md-2 mb-2">
               <div class="mb-3">
                  <label class="form-label">Type</label>
                  <select name="qa_visit_types[]" class="form-select form-select-sm">
                     <option value="Initial QA Visit">Initial QA</option>
                     <option value="Follow-up QA">Follow-up</option>
                     <option value="Compliance Check">Compliance</option>
                     <option value="Final Assessment">Final</option>
                  </select>
               </div>
            </div>

            <!-- QA Officer -->
            <div class="col-md-2 mb-2">
               <div class="mb-3">
                  <label class="form-label">QA Officer</label>
                  <input type="text" name="qa_officers[]" class="form-control form-control-sm" placeholder="Officer name">
               </div>
            </div>

            <!-- Report Upload -->
            <div class="col-md-3 mb-2">
               <div class="mb-3">
                  <label class="form-label">QA Report</label>
                  <input type="file" name="qa_reports[]" class="form-control form-control-sm" accept=".pdf">
                  <div class="invalid-feedback">Please upload a report.</div>
               </div>
            </div>

            <!-- Remove Button -->
            <div class="col-md-2 mb-2">
               <div class="mt-4">
                  <button type="button" class="btn btn-outline-danger btn-sm remove-qa-visit-btn">
                                <i data-feather="trash-2" style="height:12.8px;width:12.8px;"></i>
                                Remove
                            </button>
               </div>
            </div>
         </div>

         <!-- Add Row Button -->
         <button type="button" class="btn btn-outline-primary btn-sm" id="add-qa-visit-btn">
         <i class="bi bi-plus-circle me-1"></i> Add QA Visit Date
         </button>
         
         <!-- Hidden field to store QA reports metadata -->
         <input type="hidden" id="qa_reports_metadata" name="qa_reports_metadata" value="<?php echo esc_attr(json_encode($data['class_data']['qa_reports'] ?? [])); ?>">
      </div>

      <?php echo section_divider(); ?>

      <!-- ===== Section: Assignments & Dates ===== -->
      <?php echo section_header('Assignments & Dates', 'Assign staff to this class and track agent changes.'); ?>

      <!-- Class Agents Section -->
      <div class="mb-4">
         <?php echo section_header('Class Agents', 'Assign the primary class agent. If the agent changes during the class, the history will be tracked.'); ?>

         <!-- Initial Class Agent -->
         <div class="row mb-3">
            <div class="col-md-3 mb-3">
               <div class="mb-3">
                  <label for="initial_class_agent" class="form-label">Initial Class Agent <span class="text-danger">*</span></label>
                  <select id="initial_class_agent" name="initial_class_agent" class="form-select" required>
                     <option value="">Select</option>
                     <?php foreach ($data['agents'] as $agent): ?>
                        <option value="<?php echo $agent['id']; ?>" <?php echo (isset($data['class_data']['class_agent']) && $data['class_data']['class_agent'] == $agent['id']) ? 'selected' : ''; ?>><?php echo $agent['name']; ?></option>
                     <?php endforeach; ?>
                  </select>
                  <div class="invalid-feedback">Please select the initial class agent.</div>
                  <div class="valid-feedback">Looks good!</div>
               </div>
            </div>
            <div class="col-md-3 mb-3">
               <div class="mb-3">
                  <label for="initial_agent_start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
                  <input type="date" id="initial_agent_start_date" name="initial_agent_start_date" class="form-control" placeholder="YYYY-MM-DD" value="<?php echo esc_attr($data['class_data']['initial_agent_start_date'] ?? ''); ?>" required>
                  <div class="invalid-feedback">Please select the start date.</div>
                  <div class="valid-feedback">Looks good!</div>
               </div>
            </div>
         </div>

         <!-- Agent Replacements -->
         <?php echo section_header('Agent Replacements', 'If the class agent changes, add the replacement agent and takeover date here.'); ?>

         <!-- Container for all agent replacement rows -->
         <div id="agent-replacements-container"></div>

         <!-- Hidden Template Row (initially d-none) -->
         <div class="row agent-replacement-row d-none" id="agent-replacement-row-template">
            <!-- Replacement Agent -->
            <div class="col-md-3 mb-2">
               <div class="mb-3">
                  <label class="form-label">Replacement Agent</label>
                  <select name="replacement_agent_ids[]" class="form-select replacement-agent-select">
                     <option value="">Select</option>
                     <?php foreach ($data['agents'] as $agent): ?>
                        <option value="<?php echo $agent['id']; ?>"><?php echo $agent['name']; ?></option>
                     <?php endforeach; ?>
                  </select>
                  <div class="invalid-feedback">Please select a replacement agent.</div>
                  <div class="valid-feedback">Looks good!</div>
               </div>
            </div>

            <!-- Takeover Date -->
            <div class="col-md-3 mb-2">
               <div class="mb-3">
                  <label class="form-label">Takeover Date</label>
                  <input type="date" name="replacement_agent_dates[]" class="form-control" placeholder="YYYY-MM-DD">
                  <div class="invalid-feedback">Please select a valid takeover date.</div>
                  <div class="valid-feedback">Looks good!</div>
               </div>
            </div>

            <!-- Remove Button -->
            <div class="col-md-2 mb-2">
               <div class="d-flex h-100 align-items-end">
                  <button type="button" class="btn btn-outline-danger btn-sm remove-agent-replacement-btn form-control date-remove-btn">Remove</button>
               </div>
            </div>
         </div>

         <!-- Add Row Button -->
         <button type="button" class="btn btn-outline-primary btn-sm" id="add-agent-replacement-btn">
         <i class="bi bi-plus-circle me-1"></i> Add Agent Replacement
         </button>
      </div>

      <!-- Project Supervisor and Delivery Date -->
      <div class="row mb-4">
         <div class="col-md-3 mb-3">
            <div class="mb-3">
               <label for="project_supervisor" class="form-label">Project Supervisor <span class="text-danger">*</span></label>
               <select id="project_supervisor" name="project_supervisor" class="form-select" required>
                  <option value="">Select</option>
                  <?php foreach ($data['supervisors'] as $supervisor): ?>
                     <option value="<?php echo $supervisor['id']; ?>" <?php echo (isset($data['class_data']['project_supervisor_id']) && $data['class_data']['project_supervisor_id'] == $supervisor['id']) ? 'selected' : ''; ?>><?php echo $supervisor['name']; ?></option>
                  <?php endforeach; ?>
               </select>
               <div class="invalid-feedback">Please select a project supervisor.</div>
               <div class="valid-feedback">Looks good!</div>
            </div>
         </div>

         <div class="col-md-3 mb-3">
            <div class="mb-3">
               <label for="delivery_date" class="form-label">Delivery Date <span class="text-danger">*</span></label>
               <input type="date" id="delivery_date" name="delivery_date" class="form-control" placeholder="YYYY-MM-DD" value="<?php echo esc_attr($data['class_data']['delivery_date'] ?? ''); ?>" required>
               <div class="invalid-feedback">Please select the delivery date.</div>
               <div class="valid-feedback">Looks good!</div>
            </div>
         </div>
      </div>

      <!-- Backup Agents Section -->
      <div class="mt-4 mb-4">
         <?php echo section_header('Backup Agents', 'Add backup agents with specific dates when they will be available.'); ?>

         <!-- Container for all backup agent rows -->
         <div id="backup-agents-container"></div>

         <!-- Hidden Template Row (initially d-none) -->
         <div class="row backup-agent-row align-items-center d-none" id="backup-agent-row-template">
            <!-- Backup Agent -->
            <div class="col-md-3 mb-2">
               <div class="mb-3">
                  <label class="form-label">Backup Agent</label>
                  <select name="backup_agent_ids[]" class="form-select backup-agent-select">
                     <option value="">Select</option>
                     <?php foreach ($data['agents'] as $agent): ?>
                        <option value="<?php echo $agent['id']; ?>"><?php echo $agent['name']; ?></option>
                     <?php endforeach; ?>
                  </select>
                  <div class="invalid-feedback">Please select a backup agent.</div>
                  <div class="valid-feedback">Looks good!</div>
               </div>
            </div>

            <!-- Backup Date -->
            <div class="col-md-3 mb-2">
               <div class="mb-3">
                  <label class="form-label">Backup Date</label>
                  <input type="date" name="backup_agent_dates[]" class="form-control" placeholder="YYYY-MM-DD">
                  <div class="invalid-feedback">Please select a valid date.</div>
                  <div class="valid-feedback">Looks good!</div>
               </div>
            </div>

            <!-- Remove Button -->
            <div class="col-md-1 mb-2">
               <div class="mt-2">
                  <button type="button" class="btn btn-outline-danger btn-sm remove-backup-agent-btn form-control date-remove-btn">Remove</button>
               </div>
            </div>
         </div>

         <!-- Add Row Button -->
         <button type="button" class="btn btn-outline-primary btn-sm" id="add-backup-agent-btn">
         <i class="bi bi-plus-circle me-1"></i> Add Backup Agent
         </button>
      </div>

      <?php echo section_divider(); ?>
      <!-- Submit Button - Mode-aware text -->
      <div class="row mt-4">
         <div class="col-md-3">
               <?php echo button('<i class="bi bi-save me-1"></i> Update Class', 'submit', 'primary', ['class' => 'btn-lg']); ?>
         </div>
      </div>
   </div>
</form>

<!-- Alert container for form messages -->
<div id="form-messages" class="mt-3"></div>

<!-- Form Validation Script -->
<script>
// Enhanced form validation for update mode
function validateUpdateForm() {
    const form = document.getElementById('classes-form');
    const errors = [];
    
    // Validate learners (at least one required)
    const learnersData = document.getElementById('class_learners_data');
    if (!learnersData || !learnersData.value || learnersData.value === '[]') {
        errors.push('At least one learner must be added to the class.');
    }
    
    // Validate schedule data
    const schedulePattern = document.getElementById('schedule_pattern');
    if (schedulePattern && !schedulePattern.value) {
        errors.push('Schedule pattern is required.');
    }
    
    // Validate exam learners if exam class
    const examClass = document.getElementById('exam_class');
    const examLearners = document.getElementById('exam_learners');
    if (examClass && (examClass.value === 'Yes' || examClass.value === '1')) {
        if (!examLearners || !examLearners.value || examLearners.value === '[]') {
            errors.push('Exam classes must have at least one exam learner selected.');
        }
    }
    
    // Validate date consistency
    const startDate = document.getElementById('class_start_date');
    const scheduleStartDate = document.getElementById('schedule_start_date');
    if (startDate && scheduleStartDate && startDate.value && scheduleStartDate.value) {
        if (new Date(scheduleStartDate.value) < new Date(startDate.value)) {
            errors.push('Schedule start date cannot be before class original start date.');
        }
    }
    
    // Show errors if any
    if (errors.length > 0) {
        const messageContainer = document.getElementById('form-messages');
        messageContainer.innerHTML = '<div class="alert alert-danger" role="alert"><strong>Validation Errors:</strong><ul>' + 
            errors.map(e => '<li>' + e + '</li>').join('') + '</ul></div>';
        messageContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return false;
    }
    
    return true;
}

// Attach validation to form submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('classes-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            if (!validateUpdateForm()) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    }
});
</script>

<!-- Pre-populate form data for update mode -->
<script>
// Debug: Log the class data
<?php if (isset($_GET['debug']) && $_GET['debug'] === '1' && isset($data['class_data'])): ?>
console.log('Update Form - Class Data:', <?php echo json_encode($data['class_data']); ?>);
console.log('Update Form - Available Class Types:', <?php echo json_encode($data['class_types']); ?>);
console.log('Update Form - Available Yes/No Options:', <?php echo json_encode($data['yes_no_options']); ?>);
<?php endif; ?>

document.addEventListener('DOMContentLoaded', function() {
    // Debug logging for update form
    <?php if (isset($_GET['debug']) && $_GET['debug'] === '1'): ?>
    console.log('Update Form Debug Mode Active');
    <?php endif; ?>
    
    // Note: Read-only fields have been replaced with a visual display table
    // The form now only contains editable fields
    // Pre-populate learner data if available
    <?php if (isset($data['class_data']['learner_ids']) && !empty($data['class_data']['learner_ids'])): ?>
    const learnerData = <?php echo json_encode($data['class_data']['learner_ids']); ?>;

    // Pre-populate the class learners table
    if (learnerData && Array.isArray(learnerData)) {
        const classLearnersData = document.getElementById('class_learners_data');
        const classLearnersTable = document.getElementById('class-learners-table');
        const classLearnersTbody = document.getElementById('class-learners-tbody');
        const noLearnersMessage = document.getElementById('no-learners-message');

        if (classLearnersData && classLearnersTable && classLearnersTbody) {
            // Set the hidden field value
            classLearnersData.value = JSON.stringify(learnerData);

            // Clear existing rows
            classLearnersTbody.innerHTML = '';

            // Add each learner to the table
            learnerData.forEach(function(learner, index) {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${learner.name || 'Unknown Learner'}</td>
                    <td>
                        ${classes_generate_learner_level_select_html(learner.id, learner.level)}
                    </td>
                    <td>
                        <select class="form-select form-select-sm learner-status-select" data-learner-id="${learner.id}">
                            <option value="CIC - Currently in Class" ${learner.status === 'CIC - Currently in Class' ? 'selected' : ''}>CIC - Currently in Class</option>
                            <option value="RBE - Removed by Employer" ${learner.status === 'RBE - Removed by Employer' ? 'selected' : ''}>RBE - Removed by Employer</option>
                            <option value="DRO - Drop Out" ${learner.status === 'DRO - Drop Out' ? 'selected' : ''}>DRO - Drop Out</option>
                        </select>
                    </td>
                    <td>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-learner-btn" data-learner-id="${learner.id}">Remove</button>
                    </td>
                `;
                classLearnersTbody.appendChild(row);
            });

            // Show the table and hide the no learners message
            classLearnersTable.classList.remove('d-none');
            if (noLearnersMessage) {
                noLearnersMessage.classList.add('d-none');
            }
        }
    }
    <?php endif; ?>

    // Pre-populate exam learners data if available
    <?php if (isset($data['class_data']['exam_learners']) && !empty($data['class_data']['exam_learners'])): ?>
    const examLearnerData = <?php echo json_encode($data['class_data']['exam_learners']); ?>;

    // Pre-populate the exam learners table
    if (examLearnerData && Array.isArray(examLearnerData)) {
        const examLearnersDataField = document.getElementById('exam_learners');
        const examLearnersTable = document.getElementById('exam-learners-table');
        const examLearnersTbody = document.getElementById('exam-learners-tbody');
        const noExamLearnersMessage = document.getElementById('no-exam-learners-message');

        if (examLearnersDataField && examLearnersTbody) {
            // Set the hidden field value
            examLearnersDataField.value = JSON.stringify(examLearnerData);

            // Clear existing rows
            examLearnersTbody.innerHTML = '';

            // Add each exam learner to the table
            examLearnerData.forEach(function(learner) {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${learner.name || 'Unknown Learner'}</td>
                    <td>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-exam-learner-btn" data-learner-id="${learner.id}">
                            <i data-feather="trash-2" style="height:12.8px;width:12.8px;"></i>
                            Remove
                        </button>
                    </td>
                `;
                examLearnersTbody.appendChild(row);
            });

            // Show the table and hide the no exam learners message
            if (examLearnersTable) {
                examLearnersTable.classList.remove('d-none');
            }
            if (noExamLearnersMessage) {
                noExamLearnersMessage.classList.add('d-none');
            }

            // Re-initialize feather icons for new buttons
            if (typeof feather !== 'undefined') {
                feather.replace();
            }

            // Show the exam learners container if we have exam learners
            const examLearnersContainer = document.getElementById('exam_learners_container');
            if (examLearnersContainer && examLearnerData.length > 0) {
                examLearnersContainer.style.display = 'block';
            }

            console.log('Pre-populated exam learners:', examLearnerData);
        }
    }
    <?php endif; ?>

    // Pre-populate QA visit dates and reports if available
    <?php if (isset($data['class_data']['qa_visit_dates']) && !empty($data['class_data']['qa_visit_dates'])): ?>
    const qaVisitDates = <?php echo json_encode($data['class_data']['qa_visit_dates']); ?>;
    const qaReportsMetadata = <?php echo json_encode($data['class_data']['qa_reports'] ?? []); ?>;
    <?php if (isset($_GET['debug']) && $_GET['debug'] === '1'): ?>
    console.log('QA Visit Dates:', qaVisitDates);
    console.log('QA Reports Metadata:', qaReportsMetadata);
    <?php endif; ?>

    if (qaVisitDates && Array.isArray(qaVisitDates)) {
        const qaVisitsContainer = document.getElementById('qa-visits-container');
        const qaVisitTemplate = document.getElementById('qa-visit-row-template');

        if (qaVisitsContainer && qaVisitTemplate) {
            qaVisitDates.forEach(function(date, index) {
                const newRow = qaVisitTemplate.cloneNode(true);
                newRow.classList.remove('d-none');
                newRow.removeAttribute('id');

                const dateInput = newRow.querySelector('input[name="qa_visit_dates[]"]');
                const typeSelect = newRow.querySelector('select[name="qa_visit_types[]"]');
                const officerInput = newRow.querySelector('input[name="qa_officers[]"]');
                const fileInput = newRow.querySelector('input[name="qa_reports[]"]');
                
                if (dateInput) {
                    dateInput.value = date;
                }
                
                // If we have report metadata, populate type and officer fields
                if (qaReportsMetadata && qaReportsMetadata[index]) {
                    const reportInfo = qaReportsMetadata[index];
                    
                    // Populate visit type
                    if (typeSelect && reportInfo.type) {
                        typeSelect.value = reportInfo.type;
                    }
                    
                    // Populate QA officer
                    if (officerInput && reportInfo.officer) {
                        officerInput.value = reportInfo.officer;
                    }
                    
                    // Show existing filename
                    if (reportInfo.filename && fileInput) {
                        // Create a display element for existing file
                        const fileDisplay = document.createElement('div');
                        fileDisplay.className = 'text-muted small mt-1';
                        fileDisplay.innerHTML = '<i class="bi bi-file-pdf"></i> ' + reportInfo.filename;
                        fileInput.parentNode.appendChild(fileDisplay);
                    }
                }

                qaVisitsContainer.appendChild(newRow);
            });
        }
    }
    <?php endif; ?>

    // Pre-populate stop/restart dates if available
    <?php if (isset($data['class_data']['stop_restart_dates']) && !empty($data['class_data']['stop_restart_dates'])): ?>
    const stopRestartDates = <?php echo json_encode($data['class_data']['stop_restart_dates']); ?>;

    if (stopRestartDates && Array.isArray(stopRestartDates)) {
        const dateHistoryContainer = document.getElementById('date-history-container');
        const dateHistoryTemplate = document.getElementById('date-history-row-template');

        if (dateHistoryContainer && dateHistoryTemplate) {
            stopRestartDates.forEach(function(dateEntry) {
                const newRow = dateHistoryTemplate.cloneNode(true);
                newRow.classList.remove('d-none');
                newRow.removeAttribute('id');

                const stopDateInput = newRow.querySelector('input[name="stop_dates[]"]');
                const restartDateInput = newRow.querySelector('input[name="restart_dates[]"]');

                if (stopDateInput && dateEntry.stop_date) {
                    stopDateInput.value = dateEntry.stop_date;
                }
                if (restartDateInput && dateEntry.restart_date) {
                    restartDateInput.value = dateEntry.restart_date;
                }

                dateHistoryContainer.appendChild(newRow);
            });
        }
    }
    <?php endif; ?>

    // Pre-populate backup agent data if available
    <?php if (isset($data['class_data']['backup_agent_ids']) && !empty($data['class_data']['backup_agent_ids'])): ?>
    const backupAgentData = <?php echo json_encode($data['class_data']['backup_agent_ids']); ?>;
    <?php if (isset($_GET['debug']) && $_GET['debug'] === '1'): ?>
    console.log('Backup Agent Data:', backupAgentData);
    <?php endif; ?>

    if (backupAgentData) {
        const backupAgentsContainer = document.getElementById('backup-agents-container');
        const backupAgentTemplate = document.getElementById('backup-agent-row-template');

        if (backupAgentsContainer && backupAgentTemplate) {
            // Normalize data structure - ensure we have an array of objects
            let normalizedData = [];
            if (Array.isArray(backupAgentData)) {
                normalizedData = backupAgentData.map(function(item) {
                    if (typeof item === 'object' && item !== null) {
                        return {
                            agent_id: item.agent_id || item.id || '',
                            date: item.date || item.backup_date || ''
                        };
                    } else if (typeof item === 'string' || typeof item === 'number') {
                        // Legacy format - just agent ID
                        return {
                            agent_id: item,
                            date: ''
                        };
                    }
                    return null;
                }).filter(Boolean);
            }

            normalizedData.forEach(function(agentData) {
                const newRow = backupAgentTemplate.cloneNode(true);
                newRow.classList.remove('d-none');
                newRow.removeAttribute('id');

                const agentSelect = newRow.querySelector('select[name="backup_agent_ids[]"]');
                const dateInput = newRow.querySelector('input[name="backup_agent_dates[]"]');
                
                if (agentSelect && agentData.agent_id) {
                    agentSelect.value = agentData.agent_id;
                }
                if (dateInput && agentData.date) {
                    dateInput.value = agentData.date;
                }

                backupAgentsContainer.appendChild(newRow);
            });
        }
    }
    <?php endif; ?>

    // Pre-populate exception dates if available (with duplicate prevention)
    <?php if (isset($data['class_data']['schedule_data']['exceptionDates']) && !empty($data['class_data']['schedule_data']['exceptionDates'])): ?>
    const exceptionDates = <?php echo json_encode($data['class_data']['schedule_data']['exceptionDates']); ?>;
    
    if (exceptionDates && Array.isArray(exceptionDates)) {
        const exceptionDatesContainer = document.getElementById('exception-dates-container');
        const exceptionDateTemplate = document.getElementById('exception-date-row-template');
        
        // Check if container is already populated to prevent duplication
        if (exceptionDatesContainer && exceptionDateTemplate && exceptionDatesContainer.getAttribute('data-populated') === 'false') {
            <?php if (isset($_GET['debug']) && $_GET['debug'] === '1'): ?>
            console.log('PHP: Populating exception dates:', exceptionDates);
            <?php endif; ?>
            
            exceptionDates.forEach(function(exception) {
                const newRow = exceptionDateTemplate.cloneNode(true);
                newRow.classList.remove('d-none');
                newRow.removeAttribute('id');
                
                const dateInput = newRow.querySelector('input[name="exception_dates[]"]');
                const reasonSelect = newRow.querySelector('select[name="exception_reasons[]"]');
                
                if (dateInput && exception.date) {
                    dateInput.value = exception.date;
                }
                if (reasonSelect && exception.reason) {
                    reasonSelect.value = exception.reason;
                }
                
                exceptionDatesContainer.appendChild(newRow);
            });
            
            // Mark container as populated by PHP
            exceptionDatesContainer.setAttribute('data-populated', 'php');
            
            <?php if (isset($_GET['debug']) && $_GET['debug'] === '1'): ?>
            console.log('PHP: Exception dates container marked as populated');
            <?php endif; ?>
        } else {
            <?php if (isset($_GET['debug']) && $_GET['debug'] === '1'): ?>
            console.log('PHP: Skipping exception dates population - already populated or no container');
            <?php endif; ?>
        }
    }
    <?php endif; ?>

    // Pre-populate agent replacements if available
    <?php if (isset($data['class_data']['agent_replacements']) && !empty($data['class_data']['agent_replacements'])): ?>
    const agentReplacementData = <?php echo json_encode($data['class_data']['agent_replacements']); ?>;
    <?php if (isset($_GET['debug']) && $_GET['debug'] === '1'): ?>
    console.log('Agent Replacement Data:', agentReplacementData);
    <?php endif; ?>
    
    if (agentReplacementData) {
        const agentReplacementsContainer = document.getElementById('agent-replacements-container');
        const agentReplacementTemplate = document.getElementById('agent-replacement-row-template');
        
        if (agentReplacementsContainer && agentReplacementTemplate) {
            // Normalize data structure
            let normalizedData = [];
            if (Array.isArray(agentReplacementData)) {
                normalizedData = agentReplacementData.map(function(item) {
                    if (typeof item === 'object' && item !== null) {
                        return {
                            agent_id: item.agent_id || item.replacement_agent_id || '',
                            date: item.date || item.takeover_date || ''
                        };
                    }
                    return null;
                }).filter(Boolean);
            }

            normalizedData.forEach(function(replacement) {
                const newRow = agentReplacementTemplate.cloneNode(true);
                newRow.classList.remove('d-none');
                newRow.removeAttribute('id');
                
                const agentSelect = newRow.querySelector('select[name="replacement_agent_ids[]"]');
                const dateInput = newRow.querySelector('input[name="replacement_agent_dates[]"]');
                
                if (agentSelect && replacement.agent_id) {
                    agentSelect.value = replacement.agent_id;
                }
                if (dateInput && replacement.date) {
                    dateInput.value = replacement.date;
                }
                
                agentReplacementsContainer.appendChild(newRow);
            });
        }
    }
    <?php endif; ?>

    // Initialize schedule data for update mode
    <?php if (isset($data['class_data']['schedule_data']) && !empty($data['class_data']['schedule_data'])): ?>
    <?php
    // Use the normalized schedule data that was processed above
    // This ensures we pass the cleaned data with proper camelCase format
    $scheduleDataForJS = $scheduleData; // This now contains the normalized perDayTimes
    
    // Additional debug to verify the data structure
    if (isset($_GET['debug']) && $_GET['debug'] === '1') {
        echo "<script>\n";
        echo "console.log('=== Schedule Data for JS (PHP side) ===');\n";
        echo "console.log('scheduleDataForJS:', " . json_encode($scheduleDataForJS) . ");\n";
        echo "</script>\n";
    }
    ?>
    // Pass schedule data to the scheduling JavaScript
    window.existingScheduleData = <?php echo json_encode($scheduleDataForJS); ?>;
    
    // Enhanced debug logging
    <?php if (isset($_GET['debug']) && $_GET['debug'] === '1'): ?>
    console.log('=== Schedule Data Debug ===');
    console.log('Raw Schedule Data from PHP:', window.existingScheduleData);
    console.log('Schedule Pattern:', window.existingScheduleData?.pattern);
    console.log('Selected Days:', window.existingScheduleData?.selectedDays);
    console.log('Time Data:', window.existingScheduleData?.timeData);
    console.log('Per Day Times:', window.existingScheduleData?.timeData?.perDayTimes);
    
    // Check if perDayTimes exists and has data
    if (window.existingScheduleData?.timeData?.perDayTimes) {
        const perDayTimes = window.existingScheduleData.timeData.perDayTimes;
        console.log('Per Day Times Object:', perDayTimes);
        console.log('Per Day Times Keys:', Object.keys(perDayTimes));
        
        // Log each day's time data
        Object.entries(perDayTimes).forEach(([day, times]) => {
            console.log(`${day} times:`, times);
        });
    } else {
        console.warn('No perDayTimes data found in schedule!');
    }
    console.log('=== End Schedule Data Debug ===');
    <?php endif; ?>
    
    // Ensure the schedule form JavaScript can access this data
    if (window.existingScheduleData && typeof window.loadExistingScheduleData === 'function') {
        // The schedule form JS will handle loading this data
        console.log('Schedule data is ready for loading');
    }
    <?php endif; ?>
    
    // Log form submission data in debug mode
    <?php if (isset($_GET['debug']) && $_GET['debug'] === '1'): ?>
    const form = document.getElementById('classes-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('Form Submission Data:');
            const formData = new FormData(form);
            for (let [key, value] of formData.entries()) {
                console.log(key + ':', value);
            }
        });
    }
    <?php endif; ?>
    
    // Initialize class subject dropdown with proper type data
    <?php if (isset($data['class_data']['class_type']) && !empty($data['class_data']['class_type'])): ?>
    // Since class type is now fixed, we need to load subjects for that type
    setTimeout(function() {
        const classType = '<?php echo esc_js($data['class_data']['class_type']); ?>';
        const currentSubject = '<?php echo esc_js($data['class_data']['class_subject'] ?? ''); ?>';
        const classSubjectSelect = document.getElementById('class_subject');
        
        if (classSubjectSelect && classType) {
            // Trigger loading of subjects for the fixed class type
            // This should be handled by the existing class-capture.js script
            // We just need to ensure the current subject remains selected
            
            // If the subject dropdown needs to be populated based on class type
            // the existing JavaScript should handle it
        }
    }, 100);
    <?php endif; ?>
    
    // Load holiday overrides from hidden field for recalculation
    const holidayOverridesInput = document.getElementById('holiday_overrides');
    if (holidayOverridesInput && holidayOverridesInput.value) {
        try {
            const overrides = JSON.parse(holidayOverridesInput.value);
            window.holidayOverrides = overrides || {};
            
            <?php if (isset($_GET['debug']) && $_GET['debug'] === '1'): ?>
            console.log('Loaded holiday overrides for recalculation:', window.holidayOverrides);
            <?php endif; ?>
        } catch (e) {
            console.error('Error parsing holiday overrides:', e);
            window.holidayOverrides = {};
        }
    }
});
</script>

<!-- Class Note Modal -->
<div class="modal fade" id="classNoteModal" tabindex="-1" aria-labelledby="classNoteModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="classNoteModalLabel">
               <i class="bi bi-sticky-note me-2"></i>
               <span id="note-modal-title">Add Class Note</span>
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <form id="class-note-form" novalidate>
            <div class="modal-body">
               <input type="hidden" id="note_id" name="note_id" value="">
               <input type="hidden" id="note_class_id" name="class_id" value="<?php echo esc_attr($data['class_data']['class_id'] ?? ''); ?>">
               
               <!-- Class Notes -->
               <div class="mb-3">
                  <label for="class_notes" class="form-label">Class Notes <span class="text-danger">*</span></label>
                  <select
                     id="class_notes"
                     name="class_notes[]"
                     class="form-select"
                     size="5"
                     multiple
                     required
                     aria-label="Class notes selection"
                  >
                     <option value="" disabled>Select class notes that apply (hold Ctrl/Cmd for multiple)</option>
                     <?php foreach ($data['class_notes_options'] as $option): ?>
                        <option value="<?= $option['id'] ?>"><?= $option['name'] ?></option>
                     <?php endforeach; ?>
                  </select>
                  <div class="form-text">
                     Select multiple operational notes that apply to this class. Hold Ctrl/Cmd to select.
                  </div>
                  <div class="invalid-feedback">Please select at least one note.</div>
                  <div class="valid-feedback">Looks good!</div>
               </div>
               
               <!-- Note Content -->
               <div class="mb-3">
                  <label for="note_content" class="form-label">Note Content <span class="text-danger">*</span></label>
                  <textarea class="form-control" id="note_content" name="content" rows="5" required></textarea>
                  <div class="invalid-feedback">Please provide content for the note.</div>
                  <small class="form-text text-muted">
                     <span id="note-char-count">0</span> characters
                  </small>
               </div>
               
               <!-- Priority -->
               <div class="mb-3">
                  <label for="note_priority" class="form-label">Priority <span class="text-danger">*</span></label>
                  <select class="form-select" id="note_priority" name="priority" required>
                     <option value="">Select priority level</option>
                     <option value="low">Low</option>
                     <option value="medium">Medium</option>
                     <option value="high">High</option>
                  </select>
                  <div class="invalid-feedback">Please select a priority level.</div>
               </div>
               
               
               <!-- File Attachments -->
               <div class="mb-3">
                  <label class="form-label">Attachments</label>
                  <div id="note-dropzone" class="dropzone-area border border-2 border-dashed rounded p-3">
                     <div class="dropzone-content d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                           <i class="bi bi-cloud-upload fs-4 text-muted me-3"></i>
                           <div>
                              <p class="mb-1 fw-medium">Drag and drop files here or click to browse</p>
                              <p class="text-muted small mb-0">Supported formats: PDF, DOC, DOCX, XLS, XLSX, JPG, PNG (Max 10MB per file)</p>
                           </div>
                        </div>
                        <button type="button" class="btn btn-outline-primary btn-sm ms-2" id="browse-files-btn">
                           <i class="bi bi-folder2-open me-1"></i> Browse Files
                        </button>
                        <input type="file" id="note-file-input" class="d-none" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png">
                     </div>
                     <div class="dropzone-uploading d-none text-center py-2">
                        <div class="d-flex align-items-center justify-content-center">
                           <div class="spinner-border spinner-border-sm text-primary me-2" role="status">
                              <span class="visually-hidden">Uploading...</span>
                           </div>
                           <span class="text-muted small">Uploading files...</span>
                        </div>
                     </div>
                  </div>
                  
                  <!-- File list -->
                  <div id="note-file-list" class="mt-3 fs-9">
                     <!-- Files will be listed here -->
                  </div>
                  
                  <!-- Upload progress -->
                  <div id="upload-progress" class="mt-2 d-none">
                     <div class="progress">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 0%"></div>
                     </div>
                     <small class="text-muted">
                        <span id="upload-status">Preparing upload...</span>
                     </small>
                  </div>
               </div>
               
               <!-- Auto-save indicator -->
               <div id="auto-save-indicator" class="text-muted small d-none">
                  <i class="bi bi-cloud-check me-1"></i>
                  <span id="auto-save-message">Draft saved</span>
               </div>
               
               <!-- Error messages -->
               <div id="note-error-alert" class="alert alert-danger d-none" role="alert">
                  <i class="bi bi-exclamation-triangle-fill me-1"></i>
                  <span id="note-error-message"></span>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
               <button type="submit" class="btn btn-primary" id="save-note-btn">
                  <span class="btn-text">Save Note</span>
                  <span class="spinner-border spinner-border-sm d-none ms-2" role="status">
                     <span class="visually-hidden">Saving...</span>
                  </span>
               </button>
            </div>
         </form>
      </div>
   </div>
</div>

<!-- QA Form Modal -->
<div class="modal fade" id="qaFormModal" tabindex="-1" aria-labelledby="qaFormModalLabel" aria-hidden="true">
   <div class="modal-dialog">
      <div class="modal-content">
         <div class="modal-header">
            <h5 class="modal-title" id="qaFormModalLabel">
               <i class="bi bi-patch-question me-2"></i>
               Add Question
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <form id="qa-form" novalidate>
            <div class="modal-body">
               <input type="hidden" id="qa_class_id" name="class_id" value="<?php echo esc_attr($data['class_data']['class_id'] ?? ''); ?>">
               
               <!-- Question -->
               <div class="mb-3">
                  <label for="qa_question" class="form-label">Question <span class="text-danger">*</span></label>
                  <textarea class="form-control" id="qa_question" name="question" rows="3" required></textarea>
                  <div class="invalid-feedback">Please provide a question.</div>
               </div>
               
               <!-- Question Context -->
               <div class="mb-3">
                  <label for="qa_context" class="form-label">Context/Details</label>
                  <textarea class="form-control" id="qa_context" name="context" rows="2" placeholder="Additional context or details (optional)"></textarea>
               </div>
               
               <!-- Attachment -->
               <div class="mb-3">
                  <label for="qa_attachment" class="form-label">Attachment (optional)</label>
                  <input type="file" class="form-control" id="qa_attachment" name="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                  <small class="form-text text-muted">Max file size: 5MB</small>
               </div>
               
               <!-- Error messages -->
               <div id="qa-error-alert" class="alert alert-danger d-none" role="alert">
                  <i class="bi bi-exclamation-triangle-fill me-1"></i>
                  <span id="qa-error-message"></span>
               </div>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
               <button type="submit" class="btn btn-primary" id="submit-question-btn">
                  <span class="btn-text">Submit Question</span>
                  <span class="spinner-border spinner-border-sm d-none ms-2" role="status">
                     <span class="visually-hidden">Submitting...</span>
                  </span>
               </button>
            </div>
         </form>
      </div>
   </div>
</div>
