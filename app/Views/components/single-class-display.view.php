<?php
   /**
    * Single Class Display View - Modern Layout
    *
    * This view displays detailed information for a single class from the database in a modern Bootstrap 5 layout.
    * Used by the [wecoza_display_single_class] shortcode.
    *
    * Available Variables:
    *   - $class: Array of class data from the database
    *   - $show_loading: Boolean indicating whether to show loading indicator
    *   - $error_message: String containing error message if class not found or invalid
    *
    * Database Fields Displayed:
    *   - class_id, class_code, class_subject, class_type
    *   - original_start_date, delivery_date, class_duration
    *   - client information (name, ID)
    *   - agent information (name, ID)
    *   - supervisor information (name, ID)
    *   - SETA funding status and details
    *   - exam class status and type
    *   - class address information
    *
    * @package WeCoza
    * @see \WeCoza\Controllers\ClassController::displaySingleClassShortcode() For the controller method that renders this view
    */
   
   // Exit if accessed directly
   defined('ABSPATH') || exit;
   
   // Ensure we have the class data
   $class = $class ?? null;
   $show_loading = $show_loading ?? true;
   $error_message = $error_message ?? '';
   
   // Process schedule data early for display purposes
   $schedule_data = null;
   $end_date = null;
   $start_date = null;
   
   if (!empty($class['schedule_data'])) {
       $schedule_data = is_string($class['schedule_data']) 
           ? json_decode($class['schedule_data'], true) 
           : $class['schedule_data'];
       
       if (!empty($schedule_data)) {
           $start_date = $schedule_data['startDate'] ?? $class['original_start_date'] ?? null;
           $end_date = $schedule_data['endDate'] ?? null;
       }
   }
   ?>
<div class="wecoza-single-class-display">
   <!-- Loading Indicator -->
   <?php if ($show_loading): ?>
   <div id="single-class-loading" class="d-flex justify-content-center align-items-center py-4">
      <div class="spinner-border text-primary me-3" role="status">
         <span class="visually-hidden">Loading...</span>
      </div>
      <span class="text-muted">Loading class details...</span>
   </div>
   <?php endif; ?>
   <!-- Class Content -->
   <div id="single-class-content" class="<?php echo $show_loading ? 'd-none' : ''; ?>">
      <?php if (!empty($error_message)): ?>
      <!-- Error Message -->
      <div class="alert alert-danger d-flex align-items-center">
         <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
         <div>
            <h6 class="alert-heading mb-1">Error Loading Class</h6>
            <p class="mb-0"><?php echo esc_html($error_message); ?></p>
         </div>
      </div>
      <?php elseif (empty($class)): ?>
      <!-- No Class Found -->
      <div class="alert alert-warning d-flex align-items-center">
         <i class="bi bi-info-circle-fill me-3 fs-4"></i>
         <div>
            <h6 class="alert-heading mb-1">Class Not Found</h6>
            <p class="mb-0">The requested class could not be found in the database.</p>
         </div>
      </div>
      <?php else: ?>
      <!-- Class Details -->
      <!-- Action Buttons -->
      <div class="d-flex justify-content-end mb-4">
          <div class="btn-group mt-2 me-2" role="group" aria-label="...">
            <button class="btn btn-subtle-primary" type="button" onclick="backToClasses()">Back To Classes</button>
            <?php if (current_user_can('edit_posts') || current_user_can('manage_options')): ?>
            <button class="btn btn-subtle-success" type="button" onclick="editClass(<?php echo esc_js($class['class_id']); ?>)">Edit</button>
            <?php endif; ?>
            <?php if (current_user_can('manage_options')): ?>
            <button class="btn btn-subtle-danger" type="button" onclick="deleteClass(<?php echo esc_js($class['class_id']); ?>)">Delete</button>
            <?php endif; ?>
          </div>
      </div>
      <!-- Top Summary Cards -->
      <div class="card mb-3">
         <div class="card-body ydcoza-mini-card-header">
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
                           <?php if (!empty($class['client_name'])): ?>
                           <?php echo esc_html($class['client_name']); ?>
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
                        <h5 class="fw-bolder text-nowrap"><?php echo esc_html($class['class_type'] ?? 'Unknown Type'); ?></h5>
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
                        <h5 class="fw-bolder text-nowrap"><?php echo esc_html($class['class_subject'] ?? 'N/A'); ?></h5>
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
                        <h5 class="fw-bolder text-nowrap"><?php echo esc_html($class['class_code'] ?? 'N/A'); ?></h5>
                     </div>
                  </div>
               </div>
               <!-- Total Hours Card -->
               <div class="col-sm-auto">
                  <div class="d-flex align-items-center border-start-sm ps-sm-5">
                     <div class="d-flex bg-warning-subtle rounded flex-center me-3" style="width:32px; height:32px">
                        <i class="bi bi-clock-history text-warning"></i>
                     </div>
                     <div>
                        <p class="fw-bold mb-1">Total Hours</p>
                        <h5 class="fw-bolder text-nowrap"><?php echo isset($class['class_duration']) ? number_format($class['class_duration'], 0) : 'N/A'; ?></h5>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <!-- Details Tables -->
      <div class="px-xl-4 mb-7">
         <div class="row mx-0">
            <!-- Left Column - Basic Information -->
            <div class="col-sm-12 col-xxl-6 border-bottom border-end-xxl py-3">
               <table class="w-100 table-stats table table-hover table-sm fs-9 mb-0">
                  <tbody>
                     <tr>
                        
                        <td class="py-2 ydcoza-w-150 ">
                           <div class="d-inline-flex align-items-center">
                              <div class="d-flex bg-primary-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-hash text-primary" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">Class ID : </p>
                           </div>
                        </td>
                        <td class="py-2">
                           <p class="fw-semibold mb-0">#<?php echo esc_html($class['class_id']); ?></p>
                        </td>
                     </tr>
                     <tr>
                        <td class="py-2">
                           <div class="d-flex align-items-center">
                              <div class="d-flex bg-warning-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-clock text-warning" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">Duration :</p>
                           </div>
                        </td>
                        <td class="py-2">
                           <p class="fw-semibold mb-0">
                              <?php if (!empty($class['class_duration'])): ?>
                              <?php echo esc_html($class['class_duration']); ?> hours
                              <?php else: ?>
                              <span class="text-muted">N/A</span>
                              <?php endif; ?>
                           </p>
                        </td>
                     </tr>
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
                           <p class="fw-semibold mb-0"><?php echo esc_html($class['class_address_line'] ?? 'N/A'); ?></p>
                        </td>
                     </tr>
                     <tr>
                        <td class="py-2">
                           <div class="d-flex align-items-center">
                              <div class="d-flex bg-primary-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-layers text-primary" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">Class Type : </p>
                           </div>
                        </td>
                        <td class="py-2">
                           <p class="fw-semibold mb-0"><?php echo esc_html($class['class_type'] ?? 'N/A'); ?></p>
                        </td>
                     </tr>
                     <tr>
                        <td class="py-2">
                           <div class="d-flex align-items-center">
                              <div class="d-flex bg-success-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-book text-success" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">Class Subject : </p>
                           </div>
                        </td>
                        <td class="py-2">
                           <p class="fw-semibold mb-0"><?php echo esc_html($class['class_subject'] ?? 'N/A'); ?></p>
                        </td>
                     </tr>
                     <tr>
                        <td class="py-2 ydcoza-w-150 ">
                           <div class="d-inline-flex align-items-center">
                              <div class="d-flex bg-success-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-check-circle text-success" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">SETA Funded : </p>
                           </div>
                        </td>
                        <td class="py-2">
                           <div class="fw-semibold mb-0">
                              <?php if ($class['seta_funded']): ?>
                              <span>Yes</span>
                              <?php else: ?>
                              <span>No</span>
                              <?php endif; ?>
                           </div>
                        </td>
                     </tr>
                     <tr>
                        <td class="py-2">
                           <div class="d-flex align-items-center">
                              <div class="d-flex bg-info-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-building-gear text-info" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">SETA Name : </p>
                           </div>
                        </td>
                        <td class="py-2">
                           <div class="fw-semibold mb-0">
                              <?php echo esc_html($class['seta'] ?? 'N/A'); ?>
                           </div>
                        </td>
                     </tr>
                     <tr>
                        <td class="py-2">
                           <div class="d-flex align-items-center">
                              <div class="d-flex bg-warning-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-mortarboard text-warning" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">Exam Class : </p>
                           </div>
                        </td>
                        <td class="py-2">
                           <div class="fw-semibold mb-0">
                              <?php if ($class['exam_class']): ?>
                              <span>Yes</span>
                              <?php else: ?>
                              <span>No</span>
                              <?php endif; ?>
                           </div>
                        </td>
                     </tr>
                     <tr>
                        <td class="py-2">
                           <div class="d-flex align-items-center">
                              <div class="d-flex bg-primary-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-clipboard-check text-primary" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">Exam Type : </p>
                           </div>
                        </td>
                        <td class="py-2">
                           <div class="fw-semibold mb-0">
                              <?php echo esc_html($class['exam_type'] ?? 'N/A'); ?>
                           </div>
                        </td>
                     </tr>
                     <!-- Class Schedule Row -->
                     <tr>
                        <td class="py-2">
                           <div class="d-flex align-items-center">
                              <div class="d-flex bg-info-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-calendar-week text-info" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">Class Schedule : </p>
                           </div>
                        </td>
                        <td class="py-2">
                           <div class="fw-semibold mb-0">
                              <?php 
                                 // Display class schedule (days and times)
                                 if (!empty($schedule_data) && isset($schedule_data['selectedDays'])) {
                                     $schedule_output = [];
                                     $selected_days = $schedule_data['selectedDays'];
                                     $time_data = $schedule_data['timeData'] ?? [];
                                     
                                     foreach ($selected_days as $day) {
                                         $day_display = $day;
                                         
                                         // Check if we have per-day times
                                         if (isset($time_data['mode']) && $time_data['mode'] === 'per-day' && 
                                             isset($time_data['perDayTimes'][$day])) {
                                             $day_times = $time_data['perDayTimes'][$day];
                                             
                                             // Handle both camelCase and snake_case field names
                                             $start_field = isset($day_times['startTime']) ? 'startTime' : 'start_time';
                                             $end_field = isset($day_times['endTime']) ? 'endTime' : 'end_time';
                                             
                                             if (isset($day_times[$start_field], $day_times[$end_field])) {
                                                 $start_time = date('g:i A', strtotime($day_times[$start_field]));
                                                 $end_time = date('g:i A', strtotime($day_times[$end_field]));
                                                 $day_display .= ": {$start_time} - {$end_time}";
                                             }
                                         } 
                                         // Check if we have single time mode
                                         elseif (isset($time_data['mode']) && $time_data['mode'] === 'single' && 
                                                 isset($time_data['startTime'], $time_data['endTime'])) {
                                             $start_time = date('g:i A', strtotime($time_data['startTime']));
                                             $end_time = date('g:i A', strtotime($time_data['endTime']));
                                             $day_display .= ": {$start_time} - {$end_time}";
                                         }
                                         
                                         $schedule_output[] = $day_display;
                                     }
                                     
                                     // Display each day in a clean list format for better readability
                                     echo '<div class="schedule-days-list">';
                                     foreach ($schedule_output as $day_info) {
                                         echo '<div class="schedule-day-item mb-1">' . esc_html($day_info) . '</div>';
                                     }
                                     echo '</div>';
                                 } else {
                                     echo 'Schedule not available';
                                 }
                                 ?>
                           </div>
                        </td>
                     </tr>
                  </tbody>
               </table>
            </div>
            <!-- Right Column - Dates & Schedule -->
            <div class="col-sm-12 col-xxl-6 border-bottom py-3">
               <table class="w-100 table-stats table table-hover table-sm fs-9 mb-0">
                  <tbody>
                     <tr>
                        <td class="py-2 ydcoza-w-150">
                           <div class="d-inline-flex align-items-center">
                              <div class="d-flex bg-success-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-calendar-check text-success" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">End Date : </p>
                           </div>
                        </td>
                        <td class="py-2">
                           <p class="fw-semibold mb-0">
                              <?php if (!empty($end_date)): ?>
                              <?php echo esc_html(date('M j, Y', strtotime($end_date))); ?>
                              <?php else: ?>
                              <span class="text-muted">N/A</span>
                              <?php endif; ?>
                           </p>
                        </td>
                     </tr>
                     <tr>
                        <td class="py-2 ydcoza-w-150">
                           <div class="d-inline-flex align-items-center">
                              <div class="d-flex bg-info-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-calendar-plus text-info" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">Start Date : </p>
                           </div>
                        </td>
                        <td class="py-2">
                           <p class="fw-semibold mb-0">
                              <?php if (!empty($class['original_start_date'])): ?>
                              <?php echo esc_html(date('M j, Y', strtotime($class['original_start_date']))); ?>
                              <?php else: ?>
                              <span class="text-muted">N/A</span>
                              <?php endif; ?>
                           </p>
                        </td>
                     </tr>
                     <tr>
                        <td class="py-2">
                           <div class="d-flex align-items-center">
                              <div class="d-flex bg-warning-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-truck text-warning" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">Delivery Date : </p>
                           </div>
                        </td>
                        <td class="py-2">
                           <p class="fw-semibold mb-0">
                              <?php if (!empty($class['delivery_date'])): ?>
                              <?php echo esc_html(date('M j, Y', strtotime($class['delivery_date']))); ?>
                              <?php else: ?>
                              <span class="text-muted">N/A</span>
                              <?php endif; ?>
                           </p>
                        </td>
                     </tr>
                     <tr>
                        <td class="py-2">
                           <div class="d-flex align-items-center">
                              <div class="d-flex bg-secondary-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-calendar-check text-secondary" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">Created : </p>
                           </div>
                        </td>
                        <td class="py-2">
                           <p class="fw-semibold mb-0">
                              <?php if (!empty($class['created_at'])): ?>
                              <?php echo esc_html(date('M j, Y g:i A', strtotime($class['created_at']))); ?>
                              <?php else: ?>
                              <span class="text-muted">N/A</span>
                              <?php endif; ?>
                           </p>
                        </td>
                     </tr>
                     <tr>
                        <td class="py-2">
                           <div class="d-flex align-items-center">
                              <div class="d-flex bg-primary-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-calendar-event text-primary" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">Last Updated : </p>
                           </div>
                        </td>
                        <td class="py-2">
                           <p class="fw-semibold mb-0">
                              <?php if (!empty($class['updated_at'])): ?>
                              <?php echo esc_html(date('M j, Y g:i A', strtotime($class['updated_at']))); ?>
                              <?php else: ?>
                              <span class="text-muted">N/A</span>
                              <?php endif; ?>
                           </p>
                        </td>
                     </tr>
                     <tr>
                        <td class="py-2">
                           <div class="d-flex align-items-center">
                              <div class="d-flex bg-info-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-calendar3 text-info" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">QA Visit Dates : </p>
                           </div>
                        </td>
                        <td class="py-2">
                           <div class="fw-semibold mb-0">
                              <?php if (!empty($class['qa_visits']['visits']) && is_array($class['qa_visits']['visits'])): ?>
                              <?php foreach ($class['qa_visits']['visits'] as $visit): ?>
                              <div class="mb-2">
                                 <div class="d-flex align-items-center mb-1">
                                    <span class="badge badge-phoenix fs-10 badge-phoenix-info me-2">
                                       <?php echo esc_html($visit['type'] ?? 'QA Visit'); ?>
                                    </span>
                                    <span class="fw-semibold">
                                       <?php echo esc_html(date('M j, Y', strtotime($visit['date']))); ?>
                                    </span>
                                 </div>
                                 <?php if (!empty($visit['officer'])): ?>
                                 <div class="fs-9 text-muted">
                                    <i class="bi bi-person me-1"></i>
                                    <?php echo esc_html($visit['officer']); ?>
                                    <?php if (!empty($visit['document']) && !empty($visit['document']['file_url'])): ?>
                                    <a href="<?php echo esc_url($visit['document']['file_url']); ?>" 
                                       class="text-success ms-2 text-decoration-none" 
                                       title="Download QA Report: <?php echo esc_attr($visit['document']['original_name'] ?? $visit['document']['filename']); ?>"
                                       download>
                                       <i class="bi bi-file-earmark-pdf"></i>
                                       <small class="ms-1">Download</small>
                                    </a>
                                    <?php endif; ?>
                                 </div>
                                 <?php endif; ?>
                              </div>
                              <?php endforeach; ?>
                              <?php else: ?>
                              <span class="text-muted">N/A</span>
                              <?php endif; ?>
                           </div>
                        </td>
                     </tr>
                     <!-- Stop/Restart Periods -->
                     <?php if (!empty($class['stop_restart_dates']) && is_array($class['stop_restart_dates'])): ?>
                     <tr>
                        <td class="py-2">
                           <div class="d-flex align-items-center">
                              <div class="d-flex bg-warning-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-pause-circle text-warning" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">Stop/Restart Periods : </p>
                           </div>
                        </td>
                        <td class="py-2">
                           <div class="fw-semibold mb-0">
                              <?php 
                                 $stopPeriods = $class['stop_restart_dates'];
                                 $periodCount = count($stopPeriods);
                                 ?>
                              <span class="badge badge-phoenix fs-10 badge-phoenix-warning me-2">
                              <?php echo $periodCount; ?> Period<?php echo $periodCount !== 1 ? 's' : ''; ?>
                              </span>
                              <div class="mt-2">
                                 <?php foreach ($stopPeriods as $period): ?>
                                 <?php 
                                    if (isset($period['stop_date']) && isset($period['restart_date'])):
                                        $stopDate = new DateTime($period['stop_date']);
                                        $restartDate = new DateTime($period['restart_date']);
                                        $interval = $stopDate->diff($restartDate);
                                        $days = $interval->days;
                                    ?>
                                 <div class="fs-9 mb-1">
                                    <i class="bi bi-calendar-range me-1"></i>
                                    <?php echo date('M j', strtotime($period['stop_date'])); ?> - 
                                    <?php echo date('M j, Y', strtotime($period['restart_date'])); ?>
                                    <span class="text-muted">(<?php echo $days; ?> day<?php echo $days !== 1 ? 's' : ''; ?>)</span>
                                 </div>
                                 <?php endif; ?>
                                 <?php endforeach; ?>
                              </div>
                           </div>
                        </td>
                     </tr>
                     <?php endif; ?>
                     <tr>
                        <td class="py-2 ydcoza-w-150 ">
                           <div class="d-flex align-items-center">
                              <div class="d-flex bg-success-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-person-badge text-success" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">Agent : </p>
                           </div>
                        </td>
                        <td class="py-2">
                           <div class="fw-semibold mb-0">
                              <?php if (!empty($class['agent_name'])): ?>
                              <?php echo esc_html($class['agent_name']); ?>
                              <div class="fs-9 text-muted">ID: <?php echo esc_html($class['class_agent']); ?></div>
                              <?php else: ?>
                              <span class="text-muted">N/A</span>
                              <?php endif; ?>
                           </div>
                        </td>
                     </tr>
                     <!-- Backup Agents -->
                     <?php if (!empty($class['backup_agent_names']) && is_array($class['backup_agent_names'])): ?>
                     <tr>
                        <td class="py-2">
                           <div class="d-flex align-items-center">
                              <div class="d-flex bg-info-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-people text-info" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">Backup Agents : </p>
                           </div>
                        </td>
                        <td class="py-2">
                           <div class="fw-semibold mb-0">
                              <span class="badge badge-phoenix fs-10 badge-phoenix-info me-2"><?php echo count($class['backup_agent_names']); ?> Backup<?php echo count($class['backup_agent_names']) !== 1 ? 's' : ''; ?></span>
                              <div class="mt-1">
                                 <?php foreach ($class['backup_agent_names'] as $backupAgent): ?>
                                 <div class="fs-9 mb-1">
                                    <i class="bi bi-person me-1"></i>
                                    <?php echo esc_html($backupAgent['name']); ?>
                                    <span class="text-muted">(ID: <?php echo esc_html($backupAgent['id']); ?>)</span>
                                 </div>
                                 <?php endforeach; ?>
                              </div>
                           </div>
                        </td>
                     </tr>
                     <?php endif; ?>
                     <!-- Initial Agent History - Only show if different from current agent -->
                     <?php if (!empty($class['initial_class_agent']) && 
                        $class['initial_class_agent'] != $class['class_agent'] &&
                        !empty($class['initial_agent_name'])): ?>
                     <tr>
                        <td class="py-2">
                           <div class="d-flex align-items-center">
                              <div class="d-flex bg-secondary-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-clock-history text-secondary" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">Original Agent : </p>
                           </div>
                        </td>
                        <td class="py-2">
                           <div class="fw-semibold mb-0">
                              <?php echo esc_html($class['initial_agent_name']); ?>
                              <div class="fs-9 text-muted">
                                 Started: <?php echo !empty($class['initial_agent_start_date']) ? date('M j, Y', strtotime($class['initial_agent_start_date'])) : 'N/A'; ?>
                              </div>
                           </div>
                        </td>
                     </tr>
                     <?php endif; ?>
                     <tr>
                        <td class="py-2">
                           <div class="d-flex align-items-center">
                              <div class="d-flex bg-warning-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-person-gear text-warning" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">Supervisor : </p>
                           </div>
                        </td>
                        <td class="py-2">
                           <div class="fw-semibold mb-0">
                              <?php if (!empty($class['supervisor_name'])): ?>
                              <?php echo esc_html($class['supervisor_name']); ?>
                              <div class="fs-9 text-muted">ID: <?php echo esc_html($class['project_supervisor_id']); ?></div>
                              <?php else: ?>
                              <span class="text-muted">N/A</span>
                              <?php endif; ?>
                           </div>
                        </td>
                     </tr>
                  </tbody>
               </table>
            </div>
            <!-- Bottom Left - People & Staff -->
            <div class="col-sm-12 col-xxl-6 border-end-xxl py-3">
               <table class="w-100 table-stats table table-hover table-sm fs-9 mb-0">
                  <tbody>
                     <tr>
                        <td class="py-2">
                           <div class="d-flex align-items-center">
                              <div class="d-flex bg-info-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-people text-info" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">Learners : </p>
                           </div>
                        </td>
                        <td class="py-2">
                           <div class="fw-semibold mb-0">
                              <?php
                                 // Get learner_ids data (should already be decoded by controller)
                                 $learners = $class['learner_ids'] ?? [];
                                 
                                 if (!empty($learners) && is_array($learners)):
                                     $learnerCount = count($learners);
                                 ?>
                              <span class="badge badge-phoenix fs-10 badge-phoenix-primary me-2"><?php echo $learnerCount; ?> Learner<?php echo $learnerCount !== 1 ? 's' : ''; ?></span>
                              <div class="mt-2">
                                 <?php foreach ($learners as $index => $learner): ?>
                                 <?php if ($index < 3): // Show first 3 learners ?>
                                 <div class="fs-9 mb-1">
                                    <i class="bi bi-person-fill me-1"></i>
                                    <?php echo esc_html($learner['name'] ?? 'Unknown'); ?>
                                    <?php if (!empty($learner['status'])): ?>
                                    <span class="text-secondary text-muted ">(<?php echo esc_html($learner['status']); ?>)</span>
                                    <?php endif; ?>
                                 </div>
                                 <?php endif; ?>
                                 <?php endforeach; ?>
                                 <?php if ($learnerCount > 3): ?>
                                 <div class="fs-9 text-muted mb-2">
                                    <i class="bi bi-three-dots me-1"></i>
                                    and <?php echo ($learnerCount - 3); ?> more learner<?php echo ($learnerCount - 3) !== 1 ? 's' : ''; ?>
                                 </div>
                                 <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#learnersModal">
                                 <i class="bi bi-eye me-1"></i>View All Learners
                                 </button>
                                 <?php endif; ?>
                              </div>
                              <?php else: ?>
                              <span class="text-muted">No learners assigned</span>
                              <?php endif; ?>
                           </div>
                        </td>
                     </tr>
                     <!-- Exam Learners Section - Only show for exam classes -->
                     <?php if ($class['exam_class'] && !empty($class['exam_learners']) && is_array($class['exam_learners'])): ?>
                     <tr>
                        <td class="py-2">
                           <div class="d-flex align-items-center">
                              <div class="d-flex bg-warning-subtle rounded-circle flex-center me-3" style="width:24px; height:24px">
                                 <i class="bi bi-mortarboard-fill text-warning" style="font-size: 12px;"></i>
                              </div>
                              <p class="fw-bold mb-0">Exam Candidates : </p>
                           </div>
                        </td>
                        <td class="py-2">
                           <div class="fw-semibold mb-0">
                              <?php
                                 $examLearners = $class['exam_learners'];
                                 $examLearnerCount = count($examLearners);
                                 ?>
                              <span class="badge badge-phoenix fs-10 badge-phoenix-warning me-2">
                              <?php echo $examLearnerCount; ?> Exam Candidate<?php echo $examLearnerCount !== 1 ? 's' : ''; ?>
                              </span>
                              <div class="mt-2">
                                 <?php foreach ($examLearners as $index => $examLearner): ?>
                                 <?php if ($index < 3): // Show first 3 exam learners ?>
                                 <div class="fs-9 mb-1">
                                    <i class="bi bi-mortarboard me-1"></i>
                                    <?php echo esc_html($examLearner['name'] ?? 'Unknown'); ?>
                                    <?php if (!empty($examLearner['exam_status'])): ?>
                                    <span class="badge bg-light text-dark ms-1" style="font-size: 0.7rem;">
                                    <?php echo esc_html($examLearner['exam_status']); ?>
                                    </span>
                                    <?php endif; ?>
                                 </div>
                                 <?php endif; ?>
                                 <?php endforeach; ?>
                                 <?php if ($examLearnerCount > 3): ?>
                                 <div class="fs-9 text-muted">
                                    <i class="bi bi-three-dots me-1"></i>
                                    and <?php echo ($examLearnerCount - 3); ?> more exam candidate<?php echo ($examLearnerCount - 3) !== 1 ? 's' : ''; ?>
                                 </div>
                                 <?php endif; ?>
                              </div>
                           </div>
                        </td>
                     </tr>
                     <?php endif; ?>
                  </tbody>
               </table>
            </div>
            <!-- Bottom Right - SETA & Exam Information -->
            <div class="col-sm-12 col-xxl-6 py-3">
               <table class="w-100 table-stats table table-hover table-sm fs-9 mb-0">
                  &nbsp;
               </table>
            </div>
         </div>
      </div>
      <!-- Class Notes -->
      <?php
      // Process class notes data (reuse existing JSONB processing pattern)
      $class_notes_data = [];
      if (!empty($class['class_notes_data'])) {
          $class_notes_data = is_string($class['class_notes_data']) 
              ? json_decode($class['class_notes_data'], true) 
              : $class['class_notes_data'];
          if (!is_array($class_notes_data)) {
              $class_notes_data = [];
          }
      }

      // Helper function for author name resolution (reuse existing user lookup pattern)
      function getNoteAuthorName($author_id) {
          $user = get_user_by('ID', $author_id);
          return $user ? $user->display_name : 'Unknown User';
      }

      // Helper function for processing expandable content
      function processExpandableContent($content, $threshold = 150) {
          if (strlen($content) > $threshold) {
              $truncated = substr($content, 0, $threshold) . '...';
              return [
                  'is_expandable' => true,
                  'truncated' => $truncated,
                  'full' => $content
              ];
          }
          return [
              'is_expandable' => false,
              'content' => $content
          ];
      }

      // Helper function to generate category badges (server-side version)
      function generateCategoryBadgesServer($categories) {
          if (empty($categories)) {
              return '<span class="note-category-badge note-category-general">general</span>';
          }
          
          $categories_array = is_string($categories) ? explode(',', $categories) : (array)$categories;
          $badges = '';
          
          foreach ($categories_array as $category) {
              $category = trim($category);
              $class_name = 'note-category-' . strtolower(str_replace([' ', '&'], ['-', 'and'], $category));
              $badges .= '<span class="note-category-badge ' . esc_attr($class_name) . '">' . esc_html($category) . '</span> ';
          }
          
          return $badges;
      }

      // Sort notes by created_at (newest first by default)
      if (!empty($class_notes_data)) {
          usort($class_notes_data, function($a, $b) {
              return strtotime($b['created_at'] ?? 0) - strtotime($a['created_at'] ?? 0);
          });
      }
      ?>
      <div class="card-body card px-5 mb-3">
            <h5 class="mb-0">
               <i class="bi bi-notebook me-2"></i>Class Notes
               <span class="badge ms-2 badge badge-phoenix badge-phoenix-warning " id="notes-count">
                  <?php 
                  $notes_count = count($class_notes_data);
                  echo $notes_count . ' NOTE' . ($notes_count !== 1 ? 'S' : '');
                  ?>
               </span>
            </h5>
      
         <!-- Class Notes Container for dynamic display -->
         <div id="class-notes-container" class="mt-3">
            <!-- Notes Search and Filter Controls -->
            <div class="notes-controls mb-4">
               <div class="row g-2 mb-2">
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
                     </select>
                  </div>
                  <div class="col-md-2">
                     <button type="button" class="btn btn-outline-secondary btn-sm" id="clear-notes-filters" title="Clear all filters">
                     <i class="bi bi-arrow-clockwise"></i> Reset Filters
                     </button>
                  </div>
                  <!-- Priority Legend -->
                  <div class="col-md-6 mt-3">
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
               <div id="notes-empty" class="text-center py-4 text-muted d-none">
                  <i class="bi bi-sticky-note display-4 mb-2"></i>
                  <p class="mb-0">No notes yet.</p>
               </div>
               <!-- Notes list -->
               <div id="notes-list">
                  <?php if (empty($class_notes_data)): ?>
                     <!-- Show empty state -->
                     <div id="notes-empty" class="text-center py-4 text-muted">
                        <i class="bi bi-sticky-note display-4 mb-2"></i>
                        <p class="mb-0">No notes yet.</p>
                     </div>
                  <?php else: ?>
                     <div class="notes-grid">
                        <?php foreach ($class_notes_data as $note): ?>
                           <div class="note-card priority-<?php echo esc_attr($note['priority'] ?? 'medium'); ?>" data-note-id="<?php echo esc_attr($note['id'] ?? ''); ?>">
                              <!-- Note header with categories -->
                              <div class="note-card-header">
                                 <div class="note-card-categories">
                                    <?php echo generateCategoryBadgesServer($note['category'] ?? []); ?>
                                 </div>
                                 <div class="note-card-metadata">
                                    <?php if (!empty($note['attachments']) && is_array($note['attachments'])): ?>
                                       <!-- Attachments dropdown -->
                                       <div class="dropdown note-attachments-dropdown">
                                          <button class="btn btn-sm btn-outline-secondary dropdown-toggle note-attachments-indicator" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="<?php echo count($note['attachments']); ?> attachment(s)">
                                             <i class="bi bi-paperclip"></i>
                                             <span><?php echo count($note['attachments']); ?></span>
                                          </button>
                                          <ul class="dropdown-menu">
                                             <?php foreach ($note['attachments'] as $attachment): ?>
                                                <li>
                                                   <a class="dropdown-item" href="<?php echo esc_url($attachment['url'] ?? '#'); ?>" target="_blank" download="<?php echo esc_attr($attachment['filename'] ?? ''); ?>">
                                                      <i class="bi bi-download me-2"></i><?php echo esc_html($attachment['filename'] ?? 'Unknown file'); ?>
                                                   </a>
                                                </li>
                                             <?php endforeach; ?>
                                          </ul>
                                       </div>
                                    <?php endif; ?>
                                 </div>
                              </div>
                              
                              <!-- Note content -->
                              <div class="note-card-body">
                                 <?php 
                                 $content_data = processExpandableContent($note['content'] ?? '');
                                 if ($content_data['is_expandable']): ?>
                                    <div class="note-content-expandable">
                                       <div class="note-content-full note-content-collapsed" data-full-content="<?php echo esc_attr($content_data['full']); ?>">
                                          <?php echo esc_html($content_data['truncated']); ?>
                                       </div>
                                       <button type="button" class="note-expand-btn fs-10" onclick="toggleNoteContent(this)">
                                          <i class="bi bi-chevron-down me-1"></i>Show More
                                       </button>
                                    </div>
                                 <?php else: ?>
                                    <div class="note-content-full">
                                       <?php echo esc_html($content_data['content']); ?>
                                    </div>
                                 <?php endif; ?>
                              </div>
                              
                              <!-- Note footer with author and timestamp -->
                              <div class="note-card-footer">
                                 <div class="note-card-meta">
                                    <span><i class="bi bi-person"></i> <?php echo esc_html(getNoteAuthorName($note['author_id'] ?? 0)); ?></span>
                                    <span title="<?php echo esc_attr($note['created_at'] ?? ''); ?>">
                                       <i class="bi bi-calendar"></i> 
                                       <?php 
                                       if (!empty($note['created_at'])) {
                                           echo date('M j, Y', strtotime($note['created_at']));
                                       } else {
                                           echo 'Unknown date';
                                       }
                                       ?>
                                    </span>
                                 </div>
                              </div>
                           </div>
                        <?php endforeach; ?>
                     </div>
                  <?php endif; ?>
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
      <!-- Monthly Schedule Summary Section -->
      <?php
         // Process monthly schedule data for single class
         $monthly_data = [];
         
         if (!empty($class['schedule_data'])) {
             $schedule_data = is_string($class['schedule_data']) 
                 ? json_decode($class['schedule_data'], true) 
                 : $class['schedule_data'];
             
             $stop_restart_dates = is_string($class['stop_restart_dates']) 
                 ? json_decode($class['stop_restart_dates'], true) 
                 : ($class['stop_restart_dates'] ?? []);
             
             if (!empty($schedule_data)) {
                 // Extract date range
                 $start_date = $schedule_data['startDate'] ?? $class['original_start_date'] ?? null;
                 $end_date = $schedule_data['endDate'] ?? null;
                 
                 if ($start_date) {
                     // Calculate monthly statistics
                     $current_date = new DateTime($start_date);
                     $end_datetime = $end_date ? new DateTime($end_date) : (clone $current_date)->add(new DateInterval('P1Y'));
                     
                     // Calculate daily hours based on schedule format
                     $daily_hours = 0;
                     if (isset($schedule_data['timeData'])) {
                         $time_data = $schedule_data['timeData'];
                         
                         if ($time_data['mode'] === 'single' && isset($time_data['startTime'], $time_data['endTime'])) {
                             $start_time = new DateTime($time_data['startTime']);
                             $end_time = new DateTime($time_data['endTime']);
                             $daily_hours = ($end_time->getTimestamp() - $start_time->getTimestamp()) / 3600;
                         } elseif ($time_data['mode'] === 'per-day' && isset($time_data['perDayTimes'])) {
                             // Average hours across all configured days
                             $total_hours = 0;
                             $day_count = 0;
                             foreach ($time_data['perDayTimes'] as $day_times) {
                                 // Handle both camelCase (startTime) and snake_case (start_time) field names
                                 $start_field = isset($day_times['startTime']) ? 'startTime' : 'start_time';
                                 $end_field = isset($day_times['endTime']) ? 'endTime' : 'end_time';
                                 
                                 if (isset($day_times[$start_field], $day_times[$end_field])) {
                                     $start_time = new DateTime($day_times[$start_field]);
                                     $end_time = new DateTime($day_times[$end_field]);
                                     $total_hours += ($end_time->getTimestamp() - $start_time->getTimestamp()) / 3600;
                                     $day_count++;
                                 }
                             }
                             $daily_hours = $day_count > 0 ? $total_hours / $day_count : 0;
                         }
                     }
                     
                     // Get selected days for the pattern and normalize to lowercase for comparison
                     $selected_days_raw = $schedule_data['selectedDays'] ?? [];
                     $selected_days = array_map('strtolower', $selected_days_raw);
                     $days_per_week = count($selected_days);
                     
                     // Get holidays for the entire date range using PublicHolidaysController
                     $holidaysController = \WeCozaClasses\Controllers\PublicHolidaysController::getInstance();
                     $all_holidays = $holidaysController->getHolidaysInRange($start_date, $end_date);
                     
                     // Calculate total sessions needed based on class duration
                     $total_sessions_needed = $daily_hours > 0 ? ceil($class['class_duration'] / $daily_hours) : 0;
                     $total_sessions_scheduled = 0;
                     
                     // Process each month in the date range
                     $process_date = clone $current_date;
                     while ($process_date <= $end_datetime && $total_sessions_scheduled < $total_sessions_needed) {
                         $year_month = $process_date->format('Y-m');
                         $year = (int)$process_date->format('Y');
                         $month = (int)$process_date->format('m');
                         
                         if (!isset($monthly_data[$year])) {
                             $monthly_data[$year] = [];
                         }
                         
                         if (!isset($monthly_data[$year][$month])) {
                             $monthly_data[$year][$month] = [
                                 'name' => $process_date->format('F'),
                                 'hours' => 0,
                                 'exceptions' => 0,
                                 'stop_starts' => 0,
                                 'calculation_breakdown' => []
                             ];
                         }
                         
                         // Calculate actual sessions for this month with proper accounting
                         $first_day = new DateTime($process_date->format('Y-m-01'));
                         $last_day = new DateTime($process_date->format('Y-m-t'));
                         
                         // Step 1: Get all potential session dates in this month
                         $potential_sessions = [];
                         $temp_date = clone $first_day;
                         
                         while ($temp_date <= $last_day) {
                             $day_name = strtolower($temp_date->format('l'));
                             $date_string = $temp_date->format('Y-m-d');
                             
                             // Check if this day matches our pattern
                             $matches_pattern = false;
                             
                             if ($schedule_data['pattern'] === 'weekly') {
                                 $matches_pattern = in_array($day_name, $selected_days);
                             } elseif ($schedule_data['pattern'] === 'biweekly') {
                                 $start_datetime = new DateTime($schedule_data['startDate']);
                                 $days_since_start = $start_datetime->diff($temp_date)->days;
                                 $week_in_cycle = floor($days_since_start / 7) % 2;
                                 $matches_pattern = ($week_in_cycle === 0) && in_array($day_name, $selected_days);
                             } elseif ($schedule_data['pattern'] === 'monthly') {
                                 $matches_pattern = in_array($day_name, $selected_days);
                             }
                             
                             if ($matches_pattern) {
                                 $potential_sessions[] = $date_string;
                             }
                             
                             $temp_date->add(new DateInterval('P1D'));
                         }
                         
                         // Step 2: Remove sessions that fall on public holidays
                         $final_sessions = $potential_sessions;
                         foreach ($all_holidays as $holiday) {
                             $holiday_date = $holiday['date'];
                             if (in_array($holiday_date, $potential_sessions)) {
                                 $final_sessions = array_diff($final_sessions, [$holiday_date]);
                             }
                         }
                         
                         // Step 3: Remove sessions that fall on exception dates
                         if (isset($schedule_data['exceptionDates'])) {
                             foreach ($schedule_data['exceptionDates'] as $exception) {
                                 $exception_date = $exception['date'];
                                 if (in_array($exception_date, $final_sessions)) {
                                     $final_sessions = array_diff($final_sessions, [$exception_date]);
                                 }
                             }
                         }
                         
                         // Step 4: Remove sessions that fall within stop periods
                         if (!empty($stop_restart_dates)) {
                             foreach ($stop_restart_dates as $stop_restart) {
                                 if (isset($stop_restart['stop_date']) && isset($stop_restart['restart_date'])) {
                                     $stop_date = new DateTime($stop_restart['stop_date']);
                                     $restart_date = new DateTime($stop_restart['restart_date']);
                                     
                                     // Remove any session dates that fall within the stop period
                                     // Note: restart date is exclusive (the day classes resume)
                                     foreach ($final_sessions as $index => $session_date) {
                                         $session_datetime = new DateTime($session_date);
                                         if ($session_datetime >= $stop_date && $session_datetime < $restart_date) {
                                             unset($final_sessions[$index]);
                                         }
                                     }
                                     $final_sessions = array_values($final_sessions); // Re-index array
                                 }
                             }
                         }
                         
                         // Step 5: Add back sessions for holiday overrides
                         if (isset($schedule_data['holidayOverrides'])) {
                             foreach ($schedule_data['holidayOverrides'] as $holiday_date => $include) {
                                 if ($include && !in_array($holiday_date, $final_sessions)) {
                                     // Check if this override date is in current month and on a selected day
                                     $override_datetime = new DateTime($holiday_date);
                                     if ($override_datetime->format('Y-m') === $year_month) {
                                         $override_day_name = strtolower($override_datetime->format('l'));
                                         if (in_array($override_day_name, $selected_days)) {
                                             $final_sessions[] = $holiday_date;
                                         }
                                     }
                                 }
                             }
                         }
                         
                         $estimated_sessions = count($final_sessions);
                         
                         // Limit sessions to not exceed total needed
                         $remaining_sessions_needed = $total_sessions_needed - $total_sessions_scheduled;
                         if ($estimated_sessions > $remaining_sessions_needed) {
                             $estimated_sessions = $remaining_sessions_needed;
                             // Trim the final_sessions array to match
                             $final_sessions = array_slice($final_sessions, 0, $remaining_sessions_needed);
                         }
                         
                         // Update total sessions scheduled
                         $total_sessions_scheduled += $estimated_sessions;
                         
                         // Initialize month data with hours
                         $monthly_data[$year][$month]['hours'] = $estimated_sessions * $daily_hours;
                         
                         // Check if we've reached our target
                         if ($total_sessions_scheduled >= $total_sessions_needed) {
                             // We've scheduled enough sessions, exit the loop
                             break; // Break out of the while loop
                         }
                         
                         // Calculate breakdown data for this month (store for use in UI)
                         $month_name = $process_date->format('F Y');
                         $potential_count = count($potential_sessions);
                         $holiday_removals = 0;
                         $exception_removals = 0;
                         $stop_removals = 0;
                         $override_additions = 0;
                         
                         // Count holiday removals
                         foreach ($all_holidays as $holiday) {
                             if (in_array($holiday['date'], $potential_sessions)) {
                                 $holiday_removals++;
                             }
                         }
                         
                         // Count exception removals
                         if (isset($schedule_data['exceptionDates'])) {
                             foreach ($schedule_data['exceptionDates'] as $exception) {
                                 if (in_array($exception['date'], $potential_sessions)) {
                                     $exception_removals++;
                                 }
                             }
                         }
                         
                         // Count stop period removals
                         if (!empty($stop_restart_dates)) {
                             foreach ($stop_restart_dates as $stop_restart) {
                                 if (isset($stop_restart['stop_date']) && isset($stop_restart['restart_date'])) {
                                     $stop_date = new DateTime($stop_restart['stop_date']);
                                     $restart_date = new DateTime($stop_restart['restart_date']);
                                     foreach ($potential_sessions as $session_date) {
                                         $session_datetime = new DateTime($session_date);
                                         if ($session_datetime >= $stop_date && $session_datetime < $restart_date) {
                                             $stop_removals++;
                                         }
                                     }
                                 }
                             }
                         }
                         
                         // Count override additions
                         if (isset($schedule_data['holidayOverrides'])) {
                             foreach ($schedule_data['holidayOverrides'] as $holiday_date => $include) {
                                 if ($include) {
                                     $override_datetime = new DateTime($holiday_date);
                                     if ($override_datetime->format('Y-m') === $year_month) {
                                         $override_day_name = strtolower($override_datetime->format('l'));
                                         if (in_array($override_day_name, $selected_days)) {
                                             $override_additions++;
                                         }
                                     }
                                 }
                             }
                         }
                         
                         // Store calculation breakdown for this month
                         $monthly_data[$year][$month]['calculation_breakdown'] = [
                             'potential_sessions' => $potential_count,
                             'holiday_removals' => $holiday_removals,
                             'exception_removals' => $exception_removals,
                             'stop_removals' => $stop_removals,
                             'override_additions' => $override_additions,
                             'final_sessions' => $estimated_sessions,
                             'daily_hours' => $daily_hours,
                             'calculated_hours' => $estimated_sessions * $daily_hours
                         ];
                         
                         // DEBUG: Show step-by-step accounting (only when debugging)
                         // if (defined('WP_DEBUG') && WP_DEBUG) {
                         //     echo "<div style='background: #f9f9f9; padding: 8px; margin: 2px; border-left: 4px solid #007cba; font-size: 12px;'>";
                         //     echo "<strong>$month_name Calculation:</strong><br>";
                         //     echo "• Potential sessions: $potential_count<br>";
                         //     if ($holiday_removals > 0) echo "• Minus holidays: -$holiday_removals<br>";
                         //     if ($exception_removals > 0) echo "• Minus exceptions: -$exception_removals<br>";
                         //     if ($stop_removals > 0) echo "• Minus stop periods: -$stop_removals<br>";
                         //     if ($override_additions > 0) echo "• Plus holiday overrides: +$override_additions<br>";
                         //     echo "• <strong>Final sessions: $estimated_sessions</strong><br>";
                         //     echo "• Hours: $estimated_sessions × " . number_format($daily_hours, 2) . " = " . number_format($estimated_sessions * $daily_hours, 2) . "h<br>";
                         //     echo "</div>";
                         // }
                         
                         // Hours already calculated above, no need to add again
                         
                         // Count exception dates in this month (for display purposes)
                         if (isset($schedule_data['exceptionDates'])) {
                             foreach ($schedule_data['exceptionDates'] as $exception) {
                                 $exception_date = new DateTime($exception['date']);
                                 if ($exception_date->format('Y-m') === $year_month) {
                                     $monthly_data[$year][$month]['exceptions']++;
                                 }
                             }
                         }
                         
                         // Count stop/start pairs in this month
                         if (!empty($stop_restart_dates)) {
                             foreach ($stop_restart_dates as $stop_restart) {
                                 $stop_date = new DateTime($stop_restart['stop_date']);
                                 $restart_date = isset($stop_restart['restart_date']) ? new DateTime($stop_restart['restart_date']) : null;
                                 
                                 if ($stop_date->format('Y-m') === $year_month || 
                                     ($restart_date && $restart_date->format('Y-m') === $year_month)) {
                                     $monthly_data[$year][$month]['stop_starts']++;
                                 }
                             }
                         }
                         
                         $process_date->add(new DateInterval('P1M'));
                     }
                     
                     // Sort years and months
                     ksort($monthly_data);
                     foreach ($monthly_data as &$year_data) {
                         ksort($year_data);
                     }
                 }
             }
         }
         ?>
      <?php if (!empty($monthly_data)): ?>
      <div class="card mb-4">
         <div class="card-header">
            <h5 class="mb-0">
               <i class="bi bi-calendar-month me-2"></i>Monthly Schedule Summary
               <span class="badge bg-secondary ms-2"><?php echo array_sum(array_map('count', $monthly_data)); ?> Months</span>
            </h5>
         </div>
         <div class="card-body">
            <?php foreach ($monthly_data as $year => $months): ?>
            <div class="mb-4">
               <div class="d-flex align-items-center mb-3">
                  <h6 class="text-body-secondary mb-0 me-3">Year: <?php echo $year; ?></h6>
                  <!-- <div class="flex-grow-1 border-bottom border-300"></div> -->
               </div>
               <div class="row g-3">
                  <?php foreach ($months as $month_num => $month_data): ?>
                  <div class="col-12 col-sm-6 col-lg-4 col-xl-4">
                     <div class="card border border-300 h-100">
                        <div class="card-body p-2 pt-1 pb-1">
                           <!-- Clickable Month Header -->
                           <div class="d-flex justify-content-between align-items-center mb-2" 
                              style="cursor: pointer;" 
                              data-bs-toggle="collapse" 
                              data-bs-target="#breakdown-<?php echo $year; ?>-<?php echo $month_num; ?>" 
                              aria-expanded="false">
                              <span class="fw-bold mb-0 fs-10"><?php echo $month_data['name']; ?></span>
                              <i class="bi bi-chevron-down fs-10 text-muted"></i>
                           </div>
                           <!-- Month Summary Badges -->
                           <div class="d-flex flex-wrap gap-1 mb-2">
                              <!-- Total Hours Badge -->
                              <div class="badge badge-phoenix fs-10 badge-phoenix-primary">
                                 <i class="bi bi-clock me-1"></i>
                                 <?php echo number_format($month_data['hours'], 1); ?>h
                              </div>
                              <!-- Exceptions Badge -->
                              <?php if ($month_data['exceptions'] > 0): ?>
                              <div class="badge badge-phoenix fs-10 badge-phoenix-warning">
                                 <i class="bi bi-exclamation-triangle me-1"></i>
                                 <?php echo $month_data['exceptions']; ?> EXC
                              </div>
                              <?php endif; ?>
                              <!-- Stop/Start Badge -->
                              <?php if ($month_data['stop_starts'] > 0): ?>
                              <div class="badge badge-phoenix fs-10 badge-phoenix-danger">
                                 <i class="bi bi-pause-circle me-1"></i>
                                 <?php echo $month_data['stop_starts']; ?> STOP
                              </div>
                              <?php endif; ?>
                           </div>
                           <!-- Expandable Calculation Breakdown -->
                           <div class="collapse" id="breakdown-<?php echo $year; ?>-<?php echo $month_num; ?>">
                              <div class="border-top pt-2">
                                 <div class="fs-9 text-muted">
                                    <strong><?php echo $month_data['name']; ?> <?php echo $year; ?> Calculation:</strong>
                                 </div>
                                 <?php if (isset($month_data['calculation_breakdown'])): 
                                    $breakdown = $month_data['calculation_breakdown']; ?>
                                 <div class="fs-9 mt-1">
                                    <div>• Potential sessions: <?php echo $breakdown['potential_sessions'] ?? 0; ?></div>
                                    <?php if (($breakdown['holiday_removals'] ?? 0) > 0): ?>
                                    <div>• Minus holidays: -<?php echo $breakdown['holiday_removals'] ?? 0; ?></div>
                                    <?php endif; ?>
                                    <?php if (($breakdown['exception_removals'] ?? 0) > 0): ?>
                                    <div>• Minus exceptions: -<?php echo $breakdown['exception_removals'] ?? 0; ?></div>
                                    <?php endif; ?>
                                    <?php if (($breakdown['stop_removals'] ?? 0) > 0): ?>
                                    <div>• Minus stop periods: -<?php echo $breakdown['stop_removals'] ?? 0; ?></div>
                                    <?php endif; ?>
                                    <?php if (($breakdown['override_additions'] ?? 0) > 0): ?>
                                    <div>• Plus holiday overrides: +<?php echo $breakdown['override_additions'] ?? 0; ?></div>
                                    <?php endif; ?>
                                    <div class="fw-medium">• Final sessions: <?php echo $breakdown['final_sessions'] ?? 0; ?></div>
                                    <div class="fw-medium">• Hours: <?php echo $breakdown['final_sessions'] ?? 0; ?> × <?php echo number_format($breakdown['daily_hours'] ?? 0, 2); ?> = <?php echo number_format(($breakdown['calculated_hours'] ?? 0), 2); ?>h</div>
                                 </div>
                                 <?php else: ?>
                                 <div class="fs-9 text-muted mt-1">
                                    <em>Calculation breakdown not available</em>
                                 </div>
                                 <?php endif; ?>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <?php endforeach; ?>
               </div>
            </div>
            <?php endforeach; ?>
         </div>
      </div>
      <?php endif; ?>
      <!-- QA Reports Section -->
      <?php if (!empty($class['qa_reports']) && is_array($class['qa_reports'])): ?>
      <div class="card mb-4">
         <div class="card-header">
            <h5 class="mb-0">
               <i class="bi bi-file-earmark-check me-2"></i>Quality Assurance Reports
               <span class="badge bg-secondary ms-2"><?php echo count($class['qa_reports']); ?></span>
            </h5>
         </div>
         <div class="card-body">
            <div class="table-responsive">
               <table class="table table-sm">
                  <thead>
                     <tr>
                        <th>Report Date</th>
                        <th>Report Type</th>
                        <th>QA Officer</th>
                        <th>File Name</th>
                        <th>Uploaded By</th>
                        <th>Actions</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php foreach ($class['qa_reports'] as $index => $report): ?>
                     <tr>
                        <td>
                           <?php 
                              $reportDate = isset($report['date']) ? date('M j, Y', strtotime($report['date'])) : 'N/A';
                              echo esc_html($reportDate);
                              ?>
                        </td>
                        <td>
                           <?php echo esc_html($report['type'] ?? 'Standard QA'); ?>
                        </td>
                        <td>
                           <?php echo esc_html($report['officer'] ?? 'N/A'); ?>
                        </td>
                        <td>
                           <i class="bi bi-file-pdf text-danger me-1"></i>
                           <?php echo esc_html($report['filename'] ?? 'qa_report_' . ($index + 1) . '.pdf'); ?>
                        </td>
                        <td>
                           <?php echo esc_html($report['uploaded_by'] ?? 'System'); ?>
                        </td>
                        <td>
                           <?php if (!empty($report['file_path'])): ?>
                           <a href="<?php echo esc_url($report['file_path']); ?>" 
                              class="btn btn-sm btn-outline-primary" 
                              download
                              title="Download Report">
                           <i class="bi bi-download me-1"></i>Download
                           </a>
                           <?php else: ?>
                           <span class="text-muted">File not available</span>
                           <?php endif; ?>
                        </td>
                     </tr>
                     <?php endforeach; ?>
                  </tbody>
               </table>
            </div>
         </div>
      </div>
      <?php endif; ?>
      <!-- Class Calendar Section -->
      <div class="card mb-4">
         <div class="card-header">
            <h4 class="mb-0">
               <i class="bi bi-calendar3 me-2"></i>Class Schedule Calendar
            </h4>
         </div>
         <div class="card-body">
            <!-- View Toggle Navigation -->
            <div class="mb-3">
               <ul class="nav nav-tabs" id="scheduleViewTabs" role="tablist">
                  <li class="nav-item" role="presentation">
                     <button class="nav-link active" id="calendar-view-tab" data-bs-toggle="tab" data-bs-target="#calendar-view" type="button" role="tab" aria-controls="calendar-view" aria-selected="true">
                     <i class="bi bi-calendar3 me-2"></i>Calendar View
                     </button>
                  </li>
                  <li class="nav-item" role="presentation">
                     <button class="nav-link" id="list-view-tab" data-bs-toggle="tab" data-bs-target="#list-view" type="button" role="tab" aria-controls="list-view" aria-selected="false">
                     <i class="bi bi-list-ul me-2"></i>List View
                     </button>
                  </li>
               </ul>
            </div>
            <!-- Tab Content Container -->
            <div class="tab-content" id="scheduleViewContent">
               <!-- Calendar View Tab Pane -->
               <div class="tab-pane fade show active" id="calendar-view" role="tabpanel" aria-labelledby="calendar-view-tab">
                  <!-- Calendar Container -->
                  <div id="classCalendar" class="mb-4">
                     <!-- FullCalendar will be rendered here -->
                  </div>
                  <!-- Calendar Loading State -->
                  <div id="calendar-loading" class="text-center py-4">
                     <div class="spinner-border text-primary me-2" role="status">
                        <span class="visually-hidden">Loading calendar...</span>
                     </div>
                     <span class="text-muted">Loading class schedule...</span>
                  </div>
                  <!-- Calendar Error State -->
                  <div id="calendar-error" class="alert alert-warning d-none">
                     <i class="bi bi-exclamation-triangle me-2"></i>
                     <strong>Calendar Unavailable:</strong>
                     <span id="calendar-error-message">Unable to load class schedule data.</span>
                  </div>
                  <!-- Calendar Legend -->
                  <div class="calendar-legend">
                     <div class="legend-item">
                        <div class="legend-color class-event"></div>
                        <span>Class Sessions</span>
                     </div>
                     <div class="legend-item">
                        <div class="legend-color public-holiday"></div>
                        <span>Public Holidays</span>
                     </div>
                     <div class="legend-item">
                        <div class="legend-color exception"></div>
                        <span>Exception Dates</span>
                     </div>
                     <div class="legend-item">
                        <div class="legend-color stop-restart"></div>
                        <span>Stop Dates</span>
                     </div>
                     <div class="legend-item">
                        <div class="legend-color stop-restart-restart"></div>
                        <span>Restart Dates</span>
                     </div>
                     <div class="legend-item">
                        <div class="legend-color stop-period"></div>
                        <span>Stop Period Days</span>
                     </div>
                  </div>
               </div>
               <!-- List View Tab Pane -->
               <div class="tab-pane fade" id="list-view" role="tabpanel" aria-labelledby="list-view-tab">
                  <!-- List View Filters -->
                  <div id="listViewFilters" class="mb-3 d-none">
                     <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                           <label for="eventTypeFilter" class="form-label text-muted small">Filter by Event Type</label>
                           <select id="eventTypeFilter" class="form-select form-select-sm">
                              <option value="">All Event Types</option>
                              <option value="class_session">Class Sessions</option>
                              <option value="public_holiday">Public Holidays</option>
                              <option value="exception">Exception Dates</option>
                              <option value="stop_date">Stop/Restart Dates</option>
                           </select>
                        </div>
                        <div class="col-md-3">
                           <label for="dateFromFilter" class="form-label text-muted small">From Date</label>
                           <input type="date" id="dateFromFilter" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                           <label for="dateToFilter" class="form-label text-muted small">To Date</label>
                           <input type="date" id="dateToFilter" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                           <button type="button" id="clearFilters" class="btn btn-outline-secondary btn-sm w-100">
                           <i class="bi bi-x-circle me-1"></i>Clear
                           </button>
                        </div>
                     </div>
                  </div>
                  <!-- List View Container -->
                  <div id="classScheduleList">
                     <!-- List view content will be rendered here -->
                  </div>
                  <!-- List View Loading State -->
                  <div id="list-loading" class="text-center py-4 d-none">
                     <div class="spinner-border text-primary me-2" role="status">
                        <span class="visually-hidden">Loading schedule list...</span>
                     </div>
                     <span class="text-muted">Loading class schedule list...</span>
                  </div>
                  <!-- List View Error State -->
                  <div id="list-error" class="alert alert-warning d-none">
                     <i class="bi bi-exclamation-triangle me-2"></i>
                     <strong>List View Unavailable:</strong>
                     <span id="list-error-message">Unable to load class schedule data.</span>
                  </div>
                  <!-- List View Empty State -->
                  <div id="list-empty" class="text-center py-5 d-none">
                     <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                     <h5 class="text-muted mt-3">No Schedule Events Found</h5>
                     <p class="text-muted">There are no scheduled events to display for this class.</p>
                  </div>
               </div>
            </div>
         </div>
      </div>
      <?php endif; ?>
   </div>
</div>
<!-- Learners Modal -->
<?php if (!empty($learners) && is_array($learners) && count($learners) > 0): ?>
<div class="modal fade" id="learnersModal" tabindex="-1" aria-labelledby="learnersModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg">
      <div class="modal-content">
         <div class="modal-header border-0 pb-0">
            <h5 class="modal-title" id="learnersModalLabel">
               <i class="bi bi-people me-2"></i>Class : <?php echo esc_html($class['class_code'] ?? 'N/A'); ?>
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body pt-2">
            <div class="row mb-3">
               <div class="col-12">
                  <div class="d-flex align-items-center justify-content-between">
                     <span class="badge badge-phoenix badge-phoenix-primary fs-9"><?php echo count($learners); ?> Total Learner<?php echo count($learners) !== 1 ? 's' : ''; ?></span>
                     <small class="text-muted">Class: <?php echo esc_html($class['class_subject'] ?? 'N/A'); ?></small>
                  </div>
               </div>
            </div>
            <div class="table-responsive">
               <table class="table table-sm fs-9 mb-0">
                  <thead>
                     <tr class="bg-body-highlight">
                        <th class="border-top border-translucent ps-3">Learner Name</th>
                        <th class="border-top border-translucent">Status</th>
                        <th class="border-top border-translucent text-end pe-3">Level/Module</th>
                     </tr>
                  </thead>
                  <tbody>
                     <?php foreach ($learners as $index => $learner): ?>
                     <tr>
                        <td class="align-middle ps-3">
                           <?php
                              // Handle different learner data formats
                              $learnerName = 'Unknown Learner';
                              if (is_array($learner)) {
                                  // New format: array with name field
                                  if (isset($learner['name'])) {
                                      $learnerName = $learner['name'];
                                  }
                                  // Legacy format: might have first_name/surname
                                  elseif (isset($learner['first_name']) || isset($learner['surname'])) {
                                      $learnerName = trim(($learner['first_name'] ?? '') . ' ' . ($learner['surname'] ?? ''));
                                  }
                                  // ID only format
                                  elseif (isset($learner['id'])) {
                                      $learnerName = 'Learner ID: ' . $learner['id'];
                                  }
                              } elseif (is_numeric($learner)) {
                                  // Legacy format: just an ID
                                  $learnerName = 'Learner ID: ' . $learner;
                              }
                              
                              echo esc_html($learnerName);
                              ?>
                        </td>
                        <td class="align-middle">
                           <?php
                              $learnerStatus = '';
                              if (is_array($learner) && isset($learner['status'])) {
                                  $learnerStatus = $learner['status'];
                              }
                              
                              if (!empty($learnerStatus)): ?>
                           <?php
                              $statusClass = 'secondary';
                              $statusIcon = 'bi-person';
                              if ($learnerStatus === 'CIC - Currently in Class') {
                                  $statusClass = 'success';
                                  $statusIcon = 'bi-check';
                              } elseif (strpos(strtolower($learnerStatus), 'walk') !== false) {
                                  $statusClass = 'warning';
                                  $statusIcon = 'bi-bars-staggered';
                              }
                              ?>
                           <div class="badge badge-phoenix fs-10 badge-phoenix-<?php echo $statusClass; ?>">
                              <span class="fw-bold"><?php echo esc_html($learnerStatus); ?></span>
                              <i class="<?php echo $statusIcon; ?> ms-1"></i>
                           </div>
                           <?php else: ?>
                           <span class="text-muted">N/A</span>
                           <?php endif; ?>
                        </td>
                        <td class="align-middle text-end py-3 pe-3">
                           <?php
                              $learnerLevel = '';
                              if (is_array($learner) && isset($learner['level'])) {
                                  $learnerLevel = $learner['level'];
                              }
                              
                              if (!empty($learnerLevel)): ?>
                           <span class="text-body-secondary"><?php echo esc_html($learnerLevel); ?></span>
                           <?php else: ?>
                           <span class="text-muted">-</span>
                           <?php endif; ?>
                        </td>
                     </tr>
                     <?php endforeach; ?>
                  </tbody>
               </table>
            </div>
            <!-- Pagination info (static for now) -->
            <div class="d-flex justify-content-between mt-3">
               <span class="d-none d-sm-inline-block">
               1 to <?php echo count($learners); ?> <span class="text-body-tertiary">Items of</span> <?php echo count($learners); ?>
               </span>
            </div>
         </div>
         <div class="modal-footer border-0 pt-0">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-1"></i>Close
            </button>
         </div>
      </div>
   </div>
</div>
<?php endif; ?>
<!-- JavaScript for functionality -->
<script>
   document.addEventListener('DOMContentLoaded', function() {
       // Hide loading indicator and show content after a brief delay
       <?php if ($show_loading): ?>
       setTimeout(function() {
           const loading = document.getElementById('single-class-loading');
           const content = document.getElementById('single-class-content');
   
           if (loading) loading.classList.add('d-none');
           if (content) content.classList.remove('d-none');
       }, 500);
       <?php endif; ?>
   
       // Initialize FullCalendar if the calendar container exists
       if (document.getElementById('classCalendar')) {
           console.log('Calendar container found, initializing...');
           initializeClassCalendar();
       } else {
           console.log('Calendar container not found');
       }
   
       // Initialize view toggle functionality
       initializeViewToggle();
       
       // Initialize month breakdown toggle functionality
       initializeMonthBreakdowns();
   });
   
   /**
    * Initialize FullCalendar for the class schedule
    * Following WordPress best practices
    */
   function initializeClassCalendar() {
       console.log('initializeClassCalendar called');
   
       // Check if FullCalendar is loaded
       console.log('FullCalendar available:', typeof FullCalendar !== 'undefined');
       console.log('WeCozaCalendar available:', typeof window.WeCozaCalendar !== 'undefined');
   
       // Pass class data to JavaScript
       const classData = {
           id: <?php echo json_encode($class['class_id'] ?? null); ?>,
           code: <?php echo json_encode($class['class_code'] ?? ''); ?>,
           subject: <?php echo json_encode($class['class_subject'] ?? ''); ?>,
           startDate: <?php echo json_encode($class['original_start_date'] ?? ''); ?>,
           deliveryDate: <?php echo json_encode($class['delivery_date'] ?? ''); ?>,
           duration: <?php echo json_encode($class['class_duration'] ?? ''); ?>,
           scheduleData: <?php echo json_encode($class['schedule_data'] ?? null); ?>,
           ajaxUrl: <?php echo json_encode(admin_url('admin-ajax.php')); ?>,
           nonce: <?php echo json_encode(wp_create_nonce('wecoza_calendar_nonce')); ?>
       };
   
       console.log('Class data:', classData);
   
       // Initialize the calendar with the class data
       if (typeof window.WeCozaCalendar !== 'undefined') {
           console.log('Initializing WeCoza Calendar...');
           window.WeCozaCalendar.init(classData);
       } else {
           console.warn('WeCoza Calendar library not loaded');
           showCalendarError('Calendar library not available');
       }
   }
   
   /**
    * Show calendar error message
    */
   function showCalendarError(message) {
       const loadingEl = document.getElementById('calendar-loading');
       const errorEl = document.getElementById('calendar-error');
       const messageEl = document.getElementById('calendar-error-message');
   
       if (loadingEl) loadingEl.style.display = 'none';
       if (errorEl) {
           errorEl.classList.remove('d-none');
           if (messageEl) messageEl.textContent = message;
       }
   }
   
   /**
    * Initialize view toggle functionality
    * Handles switching between calendar and list views with state persistence
    */
   function initializeViewToggle() {
       console.log('Initializing view toggle functionality...');
   
       // Get tab elements
       const calendarTab = document.getElementById('calendar-view-tab');
       const listTab = document.getElementById('list-view-tab');
   
       if (!calendarTab || !listTab) {
           console.warn('View toggle tabs not found');
           return;
       }
   
       // Load saved view preference from localStorage
       const savedView = localStorage.getItem('wecoza_schedule_view_preference');
       if (savedView === 'list') {
           // Switch to list view if it was the last selected view
           setTimeout(() => {
               listTab.click();
           }, 100);
       }
   
       // Add event listeners for tab switching
       calendarTab.addEventListener('shown.bs.tab', function(e) {
           console.log('Switched to calendar view');
           localStorage.setItem('wecoza_schedule_view_preference', 'calendar');
   
           // Refresh calendar when switching to calendar view
           if (typeof window.WeCozaCalendar !== 'undefined' && window.WeCozaCalendar.refreshEvents) {
               window.WeCozaCalendar.refreshEvents();
           }
       });
   
       listTab.addEventListener('shown.bs.tab', function(e) {
           console.log('Switched to list view');
           localStorage.setItem('wecoza_schedule_view_preference', 'list');
   
           // Load list view data when switching to list view
           loadListViewData();
       });
   }
   
   /**
    * Load and display data for list view
    * Fetches the same event data used by the calendar
    */
   function loadListViewData() {
       console.log('Loading list view data...');
   
       const listContainer = document.getElementById('classScheduleList');
       const listLoading = document.getElementById('list-loading');
       const listError = document.getElementById('list-error');
       const listEmpty = document.getElementById('list-empty');
   
       if (!listContainer) {
           console.error('List container not found');
           return;
       }
   
       // Show loading state
       listLoading.classList.remove('d-none');
       listError.classList.add('d-none');
       listEmpty.classList.add('d-none');
       listContainer.innerHTML = '';
   
       // Get class data (same as used for calendar)
       const classData = {
           id: <?php echo json_encode($class['class_id'] ?? null); ?>,
           code: <?php echo json_encode($class['class_code'] ?? ''); ?>,
           subject: <?php echo json_encode($class['class_subject'] ?? ''); ?>,
           ajaxUrl: <?php echo json_encode(admin_url('admin-ajax.php')); ?>,
           nonce: <?php echo json_encode(wp_create_nonce('wecoza_calendar_nonce')); ?>
       };
   
       // Fetch calendar events data
       Promise.all([
           // Fetch class events
           fetch(classData.ajaxUrl, {
               method: 'POST',
               headers: {
                   'Content-Type': 'application/x-www-form-urlencoded',
               },
               body: new URLSearchParams({
                   action: 'get_calendar_events',
                   class_id: classData.id,
                   nonce: classData.nonce
               })
           }),
           // Fetch public holidays
           fetch(classData.ajaxUrl, {
               method: 'POST',
               headers: {
                   'Content-Type': 'application/x-www-form-urlencoded',
               },
               body: new URLSearchParams({
                   action: 'get_public_holidays',
                   year: new Date().getFullYear(),
                   nonce: classData.nonce
               })
           })
       ])
       .then(responses => Promise.all(responses.map(r => r.json())))
       .then(([classEvents, holidays]) => {
           // Combine and process events
           const allEvents = [...(classEvents || []), ...(holidays || [])];
   
           // Hide loading state
           listLoading.classList.add('d-none');
   
           if (allEvents.length === 0) {
               listEmpty.classList.remove('d-none');
           } else {
               // Store events globally for filtering
               window.currentListViewEvents = allEvents;
               renderListView(allEvents, classData);
               initializeListViewFilters();
           }
       })
       .catch(error => {
           console.error('Error loading list view data:', error);
           listLoading.classList.add('d-none');
           listError.classList.remove('d-none');
   
           const errorMessage = document.getElementById('list-error-message');
           if (errorMessage) {
               errorMessage.textContent = 'Failed to load schedule data: ' + error.message;
           }
       });
   }
   
   /**
    * Render list view with event data
    * Creates a responsive table/card layout for displaying schedule events
    */
   function renderListView(events, classData) {
       console.log('Rendering list view with', events.length, 'events');
   
       const listContainer = document.getElementById('classScheduleList');
       if (!listContainer) return;
   
       // Sort events chronologically
       const sortedEvents = events.sort((a, b) => {
           const dateA = new Date(a.start || a.date);
           const dateB = new Date(b.start || b.date);
           return dateA - dateB;
       });
   
       // Group events by type for better organization
       const groupedEvents = {
           class_session: [],
           public_holiday: [],
           exception: [],
           stop_date: [],
           restart_date: [],
           stop_period: []
       };
   
       sortedEvents.forEach(event => {
           const eventType = event.extendedProps?.type || 'class_session';
           if (groupedEvents[eventType]) {
               groupedEvents[eventType].push(event);
           } else {
               groupedEvents.class_session.push(event);
           }
       });
   
       // Create list view HTML
       let listHTML = '<div class="row g-3">';
   
       // Render each event group
       Object.keys(groupedEvents).forEach(eventType => {
           const events = groupedEvents[eventType];
           if (events.length === 0) return;
   
           const groupInfo = getEventGroupInfo(eventType);
   
           listHTML += `
               <div class="col-12">
                   <div class="card border-0 shadow-sm">
                       <div class="card-header bg-light border-0 py-2">
                           <h6 class="mb-0 d-flex align-items-center">
                               <i class="${groupInfo.icon} me-2 text-${groupInfo.color}"></i>
                               ${groupInfo.title}
                               <span class="badge bg-${groupInfo.color} ms-2">${events.length}</span>
                           </h6>
                       </div>
                       <div class="card-body p-0">
                           <div class="table-responsive">
                               <table class="table table-sm mb-0">
                                   <thead class="table-light">
                                       <tr>
                                           <th class="border-0 ps-3">Date & Time</th>
                                           <th class="border-0">Details</th>
                                           <th class="border-0 text-end pe-3">Duration</th>
                                       </tr>
                                   </thead>
                                   <tbody>`;
   
           events.forEach(event => {
               listHTML += renderEventRow(event, eventType);
           });
   
           listHTML += `
                                   </tbody>
                               </table>
                           </div>
                       </div>
                   </div>
               </div>`;
       });
   
       listHTML += '</div>';
   
       listContainer.innerHTML = listHTML;
   }
   
   /**
    * Get event group information for styling and display
    */
   function getEventGroupInfo(eventType) {
       const groupInfo = {
           class_session: {
               title: 'Class Sessions',
               icon: 'bi-calendar-event',
               color: 'primary'
           },
           public_holiday: {
               title: 'Public Holidays',
               icon: 'bi-calendar-x',
               color: 'danger'
           },
           exception: {
               title: 'Exception Dates',
               icon: 'bi-exclamation-triangle',
               color: 'warning'
           },
           stop_date: {
               title: 'Stop Dates',
               icon: 'bi-stop-circle',
               color: 'danger'
           },
           restart_date: {
               title: 'Restart Dates',
               icon: 'bi-play-circle',
               color: 'success'
           },
           stop_period: {
               title: 'Stop Period Days',
               icon: 'bi-pause-circle',
               color: 'secondary'
           }
       };
   
       return groupInfo[eventType] || groupInfo.class_session;
   }
   
   /**
    * Render individual event row for list view
    */
   function renderEventRow(event, eventType) {
       const startDate = new Date(event.start || event.date);
       const endDate = event.end ? new Date(event.end) : null;
   
       // Format date and time
       const dateStr = startDate.toLocaleDateString('en-ZA', {
           weekday: 'short',
           year: 'numeric',
           month: 'short',
           day: 'numeric'
       });
   
       const timeStr = event.allDay ? 'All Day' :
           startDate.toLocaleTimeString('en-ZA', {
               hour: '2-digit',
               minute: '2-digit',
               hour12: false
           }) + (endDate ? ' - ' + endDate.toLocaleTimeString('en-ZA', {
               hour: '2-digit',
               minute: '2-digit',
               hour12: false
           }) : '');
   
       // Get event details
       const title = event.title || 'Untitled Event';
       const subject = event.extendedProps?.classSubject || '';
       const notes = event.extendedProps?.notes || '';
       const reason = event.extendedProps?.reason || '';
   
       // Calculate duration
       let durationStr = '-';
       if (endDate && !event.allDay) {
           const durationMs = endDate - startDate;
           const hours = Math.floor(durationMs / (1000 * 60 * 60));
           const minutes = Math.floor((durationMs % (1000 * 60 * 60)) / (1000 * 60));
           durationStr = hours > 0 ? `${hours}h ${minutes}m` : `${minutes}m`;
       }
   
       // Get event type styling
       const groupInfo = getEventGroupInfo(eventType);
   
       return `
           <tr>
               <td class="align-middle ps-3">
                   <div class="d-flex flex-column">
                       <span class="fw-medium">${dateStr}</span>
                       <small class="text-muted">${timeStr}</small>
                   </div>
               </td>
               <td class="align-middle">
                   <div class="d-flex flex-column">
                       <span class="fw-medium">${title}</span>
                       ${subject ? `<small class="text-muted">${subject}</small>` : ''}
                       ${notes ? `<small class="text-body-secondary">${notes}</small>` : ''}
                       ${reason ? `<small class="text-warning">Reason: ${reason}</small>` : ''}
                   </div>
               </td>
               <td class="align-middle text-end pe-3">
                   <span class="badge bg-light text-dark">${durationStr}</span>
               </td>
           </tr>`;
   }
   
   /**
    * Initialize list view filtering functionality
    */
   function initializeListViewFilters() {
       console.log('Initializing list view filters...');
   
       const filtersContainer = document.getElementById('listViewFilters');
       const eventTypeFilter = document.getElementById('eventTypeFilter');
       const dateFromFilter = document.getElementById('dateFromFilter');
       const dateToFilter = document.getElementById('dateToFilter');
       const clearFiltersBtn = document.getElementById('clearFilters');
   
       if (!filtersContainer || !eventTypeFilter || !dateFromFilter || !dateToFilter || !clearFiltersBtn) {
           console.warn('Filter elements not found');
           return;
       }
   
       // Show filters if we have events
       if (window.currentListViewEvents && window.currentListViewEvents.length > 0) {
           filtersContainer.classList.remove('d-none');
       }
   
       // Add event listeners
       eventTypeFilter.addEventListener('change', applyListViewFilters);
       dateFromFilter.addEventListener('change', applyListViewFilters);
       dateToFilter.addEventListener('change', applyListViewFilters);
       clearFiltersBtn.addEventListener('click', clearListViewFilters);
   }
   
   /**
    * Apply filters to list view
    */
   function applyListViewFilters() {
       if (!window.currentListViewEvents) return;
   
       const eventTypeFilter = document.getElementById('eventTypeFilter').value;
       const dateFromFilter = document.getElementById('dateFromFilter').value;
       const dateToFilter = document.getElementById('dateToFilter').value;
   
       let filteredEvents = [...window.currentListViewEvents];
   
       // Filter by event type
       if (eventTypeFilter) {
           filteredEvents = filteredEvents.filter(event => {
               const eventType = event.extendedProps?.type || 'class_session';
               return eventType === eventTypeFilter;
           });
       }
   
       // Filter by date range
       if (dateFromFilter) {
           const fromDate = new Date(dateFromFilter);
           filteredEvents = filteredEvents.filter(event => {
               const eventDate = new Date(event.start || event.date);
               return eventDate >= fromDate;
           });
       }
   
       if (dateToFilter) {
           const toDate = new Date(dateToFilter);
           toDate.setHours(23, 59, 59, 999); // Include the entire day
           filteredEvents = filteredEvents.filter(event => {
               const eventDate = new Date(event.start || event.date);
               return eventDate <= toDate;
           });
       }
   
       console.log(`Filtered ${filteredEvents.length} events from ${window.currentListViewEvents.length} total`);
   
       // Re-render list view with filtered events
       const listContainer = document.getElementById('classScheduleList');
       const listEmpty = document.getElementById('list-empty');
   
       if (filteredEvents.length === 0) {
           listContainer.innerHTML = '';
           listEmpty.classList.remove('d-none');
   
           // Update empty state message for filtered results
           const emptyTitle = listEmpty.querySelector('h5');
           const emptyText = listEmpty.querySelector('p');
           if (emptyTitle && emptyText) {
               emptyTitle.textContent = 'No Events Match Your Filters';
               emptyText.textContent = 'Try adjusting your filter criteria to see more events.';
           }
       } else {
           listEmpty.classList.add('d-none');
           renderListView(filteredEvents, {
               id: <?php echo json_encode($class['class_id'] ?? null); ?>,
               code: <?php echo json_encode($class['class_code'] ?? ''); ?>,
               subject: <?php echo json_encode($class['class_subject'] ?? ''); ?>
           });
       }
   }
   
   /**
    * Clear all list view filters
    */
   function clearListViewFilters() {
       document.getElementById('eventTypeFilter').value = '';
       document.getElementById('dateFromFilter').value = '';
       document.getElementById('dateToFilter').value = '';
   
       // Reset empty state message
       const listEmpty = document.getElementById('list-empty');
       const emptyTitle = listEmpty.querySelector('h5');
       const emptyText = listEmpty.querySelector('p');
       if (emptyTitle && emptyText) {
           emptyTitle.textContent = 'No Schedule Events Found';
           emptyText.textContent = 'There are no scheduled events to display for this class.';
       }
   
       // Re-render with all events
       if (window.currentListViewEvents) {
           renderListView(window.currentListViewEvents, {
               id: <?php echo json_encode($class['class_id'] ?? null); ?>,
               code: <?php echo json_encode($class['class_code'] ?? ''); ?>,
               subject: <?php echo json_encode($class['class_subject'] ?? ''); ?>
           });
       }
   }
   
   /**
    * Back To Classes Function
    * Navigates back to the classes list page
    */
   function backToClasses() {
       // Navigate to the classes list page using domain-relative URL
       window.location.href = '<?php $url = esc_url(home_url('/app/all-classes')); echo $url; ?>';
   }
   
   /**
    * Edit Class Function
    * Redirects to the edit page with the class ID
    */
   function editClass(classId) {
       // Check if user has edit permissions
       const canEdit = <?php echo (current_user_can('edit_posts') || current_user_can('manage_options')) ? 'true' : 'false'; ?>;
       if (!canEdit) {
           alert('You do not have permission to edit classes.');
           return;
       }
   
       <?php
      // 1. Find the page object for "app/new-class" (or just "new-class", depending on where it lives)
      $page = get_page_by_path('app/new-class');
      // If your "new-class" page lives directly under /app/, use exactly that path.
      // If it's a top-level page called "new-class", you can just do get_page_by_path('new-class').
      
      // 2. Grab its permalink (so WP will automatically use the correct domain/child-theme slug, etc.)
      if ($page) {
          $base_url = get_permalink($page->ID);
      } else {
          // Fallback if page not found:
          $base_url = home_url('/app/new-class/');
      }
      
      // 3. Append ?mode=update&class_id=… with add_query_arg()
      $edit_url = add_query_arg(
          [
              'mode'     => 'update',
              'class_id' => $class['class_id'],
          ],
          $base_url
      );
      
      echo "const editUrl = '" . esc_url_raw($edit_url) . "';";
      ?>
   
       // Redirect to edit page with complete URL
       window.location.href = editUrl;
   }
   
   /**
    * Delete Class Function
    * Handles AJAX deletion with proper security checks
    */
   function deleteClass(classId) {
       // Check if user is administrator
       const isAdmin = <?php echo current_user_can('manage_options') ? 'true' : 'false'; ?>;
       if (!isAdmin) {
           alert('Only administrators can delete classes.');
           return;
       }
   
       if (confirm('Are you sure you want to delete this class? This action cannot be undone.')) {
           // Show loading state
           const deleteButton = document.querySelector(`[onclick="deleteClass(${classId})"]`);
           const originalText = deleteButton.innerHTML;
           deleteButton.innerHTML = '<i class="bi bi-spinner-border me-2"></i>Deleting...';
           deleteButton.disabled = true;
   
           // Make AJAX request to delete class
           fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
               method: 'POST',
               headers: {
                   'Content-Type': 'application/x-www-form-urlencoded',
               },
               body: new URLSearchParams({
                   action: 'delete_class',
                   nonce: '<?php echo wp_create_nonce('wecoza_class_nonce'); ?>',
                   class_id: classId
               })
           })
           .then(response => response.json())
           .then(data => {
               if (data.success) {
                   // Redirect to classes list with success message
                   // Add success parameters to URL for notification (same as all-classes page)
                   const successUrl = new URL(classesUrl);
                   successUrl.searchParams.set('deleted', 'success');
                   successUrl.searchParams.set('class_subject', data.data.class_subject || 'Unknown Class');
                   successUrl.searchParams.set('class_code', data.data.class_code || '');
                   window.location.href = successUrl.toString();
               } else {
                   alert('Error deleting class: ' + (data.data || 'Unknown error'));
                   // Restore button state
                   deleteButton.innerHTML = originalText;
                   deleteButton.disabled = false;
               }
           })
           .catch(error => {
               console.error('Error:', error);
               alert('An error occurred while deleting the class. Please try again.');
               // Restore button state
               deleteButton.innerHTML = originalText;
               deleteButton.disabled = false;
           });
       }
   }
   
   /**
    * Initialize Month Breakdown Toggle Functionality
    * Handles the expandable calculation details for each month card
    */
   function initializeMonthBreakdowns() {
       console.log('Initializing month breakdown functionality...');
       
       // Get all month breakdown toggles
       const breakdownToggles = document.querySelectorAll('[data-bs-toggle="collapse"]');
       
       if (breakdownToggles.length === 0) {
           console.log('No month breakdown toggles found');
           return;
       }
       
       breakdownToggles.forEach(toggle => {
           // Add event listener for expand/collapse events
           const targetId = toggle.getAttribute('data-bs-target');
           const targetElement = document.querySelector(targetId);
           
           if (targetElement) {
               targetElement.addEventListener('show.bs.collapse', function() {
                   console.log('Expanding breakdown for:', targetId);
                   toggle.setAttribute('aria-expanded', 'true');
               });
               
               targetElement.addEventListener('hide.bs.collapse', function() {
                   console.log('Collapsing breakdown for:', targetId);
                   toggle.setAttribute('aria-expanded', 'false');
               });
           }
           
           // Add hover effect for better UX
           toggle.addEventListener('mouseenter', function() {
               this.style.backgroundColor = 'rgba(0,0,0,0.05)';
           });
           
           toggle.addEventListener('mouseleave', function() {
               this.style.backgroundColor = '';
           });
       });
       
       console.log(`Initialized ${breakdownToggles.length} month breakdown toggles`);
   }
   
   /**
    * Show Success Banner Function
    * Same implementation as all-classes page for consistency
    */
   function showSuccessBanner(message) {
       // Create success banner
       const banner = document.createElement('div');
       banner.className = 'alert alert-subtle-success alert-dismissible fade show position-fixed';
       banner.style.cssText = 'top: 80px; right: 20px; z-index: 9999; min-width: 300px;';
       banner.innerHTML = `
           <i class="bi bi-check-circle-fill me-2"></i>
           <strong>Success!</strong> ${message}
           <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
       `;
   
       // Add to page
       document.body.appendChild(banner);
   
       // Auto-remove after 5 seconds
       setTimeout(() => {
           if (banner.parentNode) {
               banner.remove();
           }
       }, 5000);
   }

   /**
    * Initialize Notes Filtering and Sorting (Reuse existing functionality)
    * Following DRY principle by reusing existing class-capture.js functionality
    */
   function initializeNotesFiltering($) {
       // Initialize collection (reuse existing pattern from class-capture.js)
       if (typeof ClassNotesQAModels !== 'undefined' && !window.classNotesCollection) {
           window.classNotesCollection = new ClassNotesQAModels.Collection(ClassNotesQAModels.Note);
       }

       // Populate collection with existing PHP data
       <?php if (!empty($class_notes_data)): ?>
           const notesData = <?php echo json_encode($class_notes_data, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
           if (window.classNotesCollection && notesData) {
               // Clear existing items and add notes data
               window.classNotesCollection.items = [];
               notesData.forEach(note => {
                   window.classNotesCollection.add(note);
               });
           }
       <?php endif; ?>

       // Attach event handlers (reuse existing handlers from class-capture.js)
       if (window.classNotesCollection && $) {
           // Priority filter handler
           $('#notes-priority-filter').off('change.notesFilter').on('change.notesFilter', function() {
               const priority = $(this).val();
               window.classNotesCollection.setFilter('priority', priority);
               refreshSingleClassNotesDisplay($);
           });

           // Sort handler  
           $('#notes-sort').off('change.notesSort').on('change.notesSort', function() {
               const sortValue = $(this).val();
               let field, order;
               
               switch (sortValue) {
                   case 'newest':
                       field = 'created_at';
                       order = 'desc';
                       break;
                   case 'oldest':
                       field = 'created_at';
                       order = 'asc';
                       break;
                   default:
                       field = 'created_at';
                       order = 'desc';
               }
               
               window.classNotesCollection.setSort(field, order);
               refreshSingleClassNotesDisplay($);
           });

           // Clear filters handler
           $('#clear-notes-filters').off('click.notesFilter').on('click.notesFilter', function() {
               // Reset form controls
               $('#notes-priority-filter').val('');
               $('#notes-sort').val('newest');
               
               // Clear collection filters
               window.classNotesCollection.setFilter('priority', '');
               window.classNotesCollection.setSort('created_at', 'desc');
               refreshSingleClassNotesDisplay($);
           });
       }
   }

   /**
    * Refresh notes display for single class view (simplified version of refreshNotesDisplay)
    */
   function refreshSingleClassNotesDisplay($) {
       if (!window.classNotesCollection || !$) return;

       const filteredData = window.classNotesCollection.getFiltered();
       const totalCount = filteredData.length;
       const allNotesCount = window.classNotesCollection.items.length;

       // Update notes count badge
       $('#notes-count').text(`${totalCount} NOTE${totalCount !== 1 ? 'S' : ''}`);

       // Store original cards if not already stored
       if (!window.originalNoteCards) {
           window.originalNoteCards = {};
           $('.note-card').each(function() {
               const noteId = $(this).data('note-id');
               if (noteId) {
                   window.originalNoteCards[noteId] = $(this).clone(true); // Clone with events
               }
           });
       }

       const $notesGrid = $('.notes-grid');

       // Show/hide based on filters
       if (totalCount === 0 && allNotesCount > 0) {
           // Show no results state
           $('#notes-no-results').removeClass('d-none').show();
           $('#notes-empty').hide();
           $notesGrid.empty();
       } else if (totalCount === 0) {
           // Show empty state
           $('#notes-empty').removeClass('d-none').show();
           $('#notes-no-results').hide();
           $notesGrid.empty();
       } else {
           // Show filtered and sorted results
           $('#notes-empty').hide();
           $('#notes-no-results').hide();
           
           // Clear the grid and rebuild in sorted order
           $notesGrid.empty();
           
           // Add cards back in the correct sorted order
           filteredData.forEach(note => {
               if (window.originalNoteCards[note.id]) {
                   $notesGrid.append(window.originalNoteCards[note.id].clone(true));
               }
           });
       }
   }

   // Initialize notes filtering when document and jQuery are ready
   function initializeWhenReady() {
       // Check if jQuery is available
       if (typeof jQuery === 'undefined') {
           setTimeout(initializeWhenReady, 100);
           return;
       }
       
       // Use jQuery safely
       jQuery(document).ready(function($) {
           // Wait for ClassNotesQAModels to be available (loaded by class-capture.js)
           function waitForModels() {
               if (typeof ClassNotesQAModels !== 'undefined') {
                   initializeNotesFiltering($);
               } else {
                   setTimeout(waitForModels, 100);
               }
           }
           waitForModels();
       });
   }
   
   // Start initialization
   initializeWhenReady();
</script>