<!-- Classes Capture Form -->
<form id="classes-form" class="needs-validation ydcoza-compact-form" novalidate method="POST" enctype="multipart/form-data">
   <!-- Hidden Auto-generated Class ID -->
   <input type="hidden" id="class_id" name="class_id" value="auto-generated">
   <input type="hidden" id="redirect_url" name="redirect_url" value="<?php echo esc_attr($data['redirect_url'] ?? ''); ?>">
   <input type="hidden" id="nonce" name="nonce" value="<?php echo wp_create_nonce('wecoza_class_nonce'); ?>">

   <!-- ===== Section: Basic Details ===== -->
   <div class="container container-md classes-form ps-0">
      <!-- ===== Section: Basic Details ===== -->
         <!-- CREATE MODE: Full client/site selection -->
         <div class="row">
            <!-- Client Name (ID) -->
            <div class="col-md-3 mb-3">
               <div class="form-floating">
                  <select id="client_id" name="client_id" class="form-select" required>
                     <option value="">Select</option>
                     <?php foreach ($data['clients'] as $client): ?>
                        <option value="<?php echo esc_attr($client['id']); ?>"><?php echo esc_html($client['name']); ?></option>
                     <?php endforeach; ?>
                  </select>
                  <label for="client_id">Client Name (ID) <span class="text-danger">*</span></label>
                  <div class="invalid-feedback">Please select a client.</div>
                  <div class="valid-feedback">Looks good!</div>
               </div>
            </div>

            <!-- Class/Site Name -->
            <div class="col-md-3 mb-3">
               <div class="form-floating">
                  <select id="site_id" name="site_id" class="form-select" required>
                     <option value="">Select Site</option>
                     <?php foreach ($data['clients'] as $client): ?>
                        <optgroup label="<?php echo esc_attr($client['name']); ?>">
                           <?php if (isset($data['sites'][$client['id']])): ?>
                              <?php foreach ($data['sites'][$client['id']] as $site): ?>
                                 <option value="<?php echo esc_attr($site['id']); ?>"><?php echo esc_html($site['name']); ?></option>
                              <?php endforeach; ?>
                           <?php endif; ?>
                        </optgroup>
                     <?php endforeach; ?>
                  </select>
                  <label for="site_id">Class/Site Name <span class="text-danger">*</span></label>
                  <div class="invalid-feedback">Please select a class/site name.</div>
                  <div class="valid-feedback">Looks good!</div>
               </div>
            </div>

            <!-- Single Address Field (initially hidden) -->
            <div class="col-md-6 mb-3" id="address-wrapper" style="display:none;">
               <div class="form-floating">
                  <input
                     type="text"
                     id="site_address"
                     name="site_address"
                     class="form-control"
                     placeholder="Street, Suburb, Town, Postal Code"
                     readonly
                     />
                  <label for="site_address">Address</label>
               </div>
            </div>
         </div>

      <?php echo section_divider(); ?>
      
      <!-- ===== Section: Scheduling & Class Info ===== -->
      <div class="row mt-3">
         <!-- Class Type (Main Category) -->
         <div class="col-md-4 mb-3">
            <div class="form-floating">
               <select id="class_type" name="class_type" class="form-select" required>
                  <option value="">Select</option>
                  <?php foreach ($data['class_types'] as $class_type): ?>
                     <option value="<?php echo esc_attr($class_type['id']); ?>"><?php echo esc_html($class_type['name']); ?></option>
                  <?php endforeach; ?>
               </select>
               <label for="class_type">Class Type <span class="text-danger">*</span></label>
               <div class="invalid-feedback">Please select the class type.</div>
               <div class="valid-feedback">Looks good!</div>
            </div>
         </div>

         <!-- Class Subject (Specific Subject/Level/Module) -->
         <div class="col-md-4 mb-3">
            <div class="form-floating">
               <select id="class_subject" name="class_subject" class="form-select" required disabled>
                  <option value="">Select Class Type First</option>
               </select>
               <label for="class_subject">Class Subject <span class="text-danger">*</span></label>
               <div class="invalid-feedback">Please select the class subject.</div>
               <div class="valid-feedback">Looks good!</div>
            </div>
         </div>

         <!-- Class Duration (Auto-calculated) -->
         <div class="col-md-4 mb-3">
            <div class="form-floating">
               <input type="number" id="class_duration" name="class_duration" class="form-control" placeholder="Duration" readonly>
               <label for="class_duration">Duration (Hours)</label>
               <div class="form-text">Automatically calculated based on class type and subject.</div>
            </div>
         </div>
      </div>

      <div class="row">
         <!-- Class Code (Auto-generated) -->
         <div class="col-md-4 mb-3">
            <div class="form-floating">
               <input type="text" id="class_code" name="class_code" class="form-control" placeholder="Class Code" readonly>
               <label for="class_code">Class Code</label>
               <div class="form-text">Auto generated [ClientID]-[ClassType]-[SubjectID]-[YYYY]-[MM]-[DD]-[HH]-[MM] </div>
            </div>
         </div>

         <!-- Class Original Start Date -->
         <div class="col-md-4 mb-3">
            <div class="form-floating">
               <input type="date" id="class_start_date" name="class_start_date" class="form-control" placeholder="YYYY-MM-DD" required>
               <label for="class_start_date">Class Original Start Date <span class="text-danger">*</span></label>
               <div class="invalid-feedback">Please select the start date.</div>
               <div class="valid-feedback">Looks good!</div>
            </div>
         </div>
      </div>

      <!-- Class Schedule Form Section -->
      <div class="mb-4 mt-3">
         <h5 class="mb-3">Class Schedule</h5>
            <!-- CREATE MODE: Full schedule setup -->
            <p class="text-muted small mb-3">Set up the recurring schedule for this class.</p>

         <!-- Schedule Pattern Selection -->
         <div class="row mb-3">
            <div class="col-md-4 mb-3">
               <div class="form-floating">
                  <select id="schedule_pattern" name="schedule_pattern" class="form-select" required>
                     <option value="">Select</option>
                     <option value="weekly">Weekly (Every Week)</option>
                     <option value="biweekly">Bi-Weekly (Every Two Weeks)</option>
                     <option value="monthly">Monthly</option>
                     <option value="custom">Custom</option>
                  </select>
                  <label for="schedule_pattern">Schedule Pattern <span class="text-danger">*</span></label>
                  <div class="invalid-feedback">Please select a schedule pattern.</div>
                  <div class="valid-feedback">Looks good!</div>
               </div>
            </div>

            <!-- Day Selection (for weekly/biweekly) -->
            <div class="col-md-12 mb-3" id="day-selection-container">
               <label class="form-label">Days of Week <span class="text-danger">*</span></label>
               <div class="days-checkbox-group">
                  <div class="form-check form-check-inline">
                     <input class="form-check-input schedule-day-checkbox" type="checkbox" id="schedule_day_monday" name="schedule_days[]" value="Monday">
                     <label class="form-check-label" for="schedule_day_monday">Monday</label>
                  </div>
                  <div class="form-check form-check-inline">
                     <input class="form-check-input schedule-day-checkbox" type="checkbox" id="schedule_day_tuesday" name="schedule_days[]" value="Tuesday">
                     <label class="form-check-label" for="schedule_day_tuesday">Tuesday</label>
                  </div>
                  <div class="form-check form-check-inline">
                     <input class="form-check-input schedule-day-checkbox" type="checkbox" id="schedule_day_wednesday" name="schedule_days[]" value="Wednesday">
                     <label class="form-check-label" for="schedule_day_wednesday">Wednesday</label>
                  </div>
                  <div class="form-check form-check-inline">
                     <input class="form-check-input schedule-day-checkbox" type="checkbox" id="schedule_day_thursday" name="schedule_days[]" value="Thursday">
                     <label class="form-check-label" for="schedule_day_thursday">Thursday</label>
                  </div>
                  <div class="form-check form-check-inline">
                     <input class="form-check-input schedule-day-checkbox" type="checkbox" id="schedule_day_friday" name="schedule_days[]" value="Friday">
                     <label class="form-check-label" for="schedule_day_friday">Friday</label>
                  </div>
                  <div class="form-check form-check-inline">
                     <input class="form-check-input schedule-day-checkbox" type="checkbox" id="schedule_day_saturday" name="schedule_days[]" value="Saturday">
                     <label class="form-check-label" for="schedule_day_saturday">Saturday</label>
                  </div>
                  <div class="form-check form-check-inline">
                     <input class="form-check-input schedule-day-checkbox" type="checkbox" id="schedule_day_sunday" name="schedule_days[]" value="Sunday">
                     <label class="form-check-label" for="schedule_day_sunday">Sunday</label>
                  </div>
               </div>
               <div class="mt-2">
                  <button type="button" class="btn btn-sm btn-outline-primary" id="select-all-days">Select All</button>
                  <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-all-days">Clear All</button>
               </div>
               <div class="invalid-feedback">Please select at least one day.</div>
               <div class="valid-feedback">Looks good!</div>
            </div>

            <!-- Day of Month (for monthly) -->
            <div class="col-md-4 mb-3 d-none" id="day-of-month-container">
               <div class="form-floating">
                  <select id="schedule_day_of_month" name="schedule_day_of_month" class="form-select">
                     <option value="">Select</option>
                     <?php for ($i = 1; $i <= 31; $i++): ?>
                        <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                     <?php endfor; ?>
                     <option value="last">Last Day</option>
                  </select>
                  <label for="schedule_day_of_month">Day of Month <span class="text-danger">*</span></label>
                  <div class="invalid-feedback">Please select a day of the month.</div>
                  <div class="valid-feedback">Looks good!</div>
               </div>
            </div>
         </div>

         <!-- Time Selection -->
         <div class="row mb-3">
            <div class="col-md-4 mb-3">
               <div class="form-floating">
                  <select id="schedule_start_time" name="schedule_start_time" class="form-select" required>
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
                  <label for="schedule_start_time">Start Time <span class="text-danger">*</span></label>
                  <div class="invalid-feedback">Please select a start time.</div>
                  <div class="valid-feedback">Looks good!</div>
               </div>
            </div>

            <div class="col-md-4 mb-3">
               <div class="form-floating">
                  <select id="schedule_end_time" name="schedule_end_time" class="form-select" required>
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
                  <label for="schedule_end_time">End Time <span class="text-danger">*</span></label>
                  <div class="invalid-feedback">Please select an end time.</div>
                  <div class="valid-feedback">Looks good!</div>
               </div>
            </div>

            <div class="col-md-4 mb-3">
               <div class="form-floating">
                  <input type="text" id="schedule_duration" name="schedule_duration" class="form-control readonly-field" placeholder="Duration" readonly>
                  <label for="schedule_duration">Class Duration (Hours)</label>
                  <small class="text-muted">Automatically calculated</small>
               </div>
            </div>
         </div>

         <!-- Date Range -->
         <div class="row mb-3">
            <div class="col-md-4 mb-3">
               <div class="form-floating">
                  <input type="date" id="schedule_start_date" name="schedule_start_date" class="form-control" placeholder="YYYY-MM-DD" required>
                  <label for="schedule_start_date">Start Date <span class="text-danger">*</span></label>
                  <div class="invalid-feedback">Please select a start date.</div>
                  <div class="valid-feedback">Looks good!</div>
               </div>
            </div>

            <div class="col-md-4 mb-3">
               <div class="form-floating">
                  <input type="date" id="schedule_end_date" name="schedule_end_date" class="form-control readonly-field" placeholder="YYYY-MM-DD" readonly>
                  <label for="schedule_end_date">End Date</label>
                  <small class="text-muted">Automatically calculated based on class duration</small>
               </div>
            </div>

            <div class="col-md-4 d-none">
               <div class="form-floating">
                  <input type="text" id="schedule_total_hours" name="schedule_total_hours" class="form-control readonly-field" placeholder="Total Hours" readonly>
                  <label for="schedule_total_hours">Total Hours</label>
                  <small class="text-muted">Based on class type</small>
               </div>
            </div>
         </div>

         <!-- Exception Dates -->
         <div class="mb-4">
            <h6 class="mb-2">Exception Dates</h6>
            <p class="text-muted small mb-3">Add dates when classes will not occur (e.g. client closed).</p>

            <!-- Container for all exception date rows -->
            <div id="exception-dates-container"></div>

            <!-- Hidden Template Row (initially d-none) -->
            <div class="row exception-date-row align-items-center d-none" id="exception-date-row-template">
               <!-- Exception Date -->
               <div class="col-md-4 mb-2">
                  <div class="form-floating">
                     <input type="date" name="exception_dates[]" class="form-control" placeholder="YYYY-MM-DD">
                     <label>Date</label>
                     <div class="invalid-feedback">Please select a valid date.</div>
                     <div class="valid-feedback">Looks good!</div>
                  </div>
               </div>

               <!-- Reason -->
               <div class="col-md-6 mb-2">
                  <div class="form-floating">
                     <select name="exception_reasons[]" class="form-select">
                        <option value="">Select</option>
                        <option value="Client Cancelled">Client Cancelled</option>
                        <option value="Agent Absent">Agent Absent</option>
                        <option value="Public Holiday">Public Holiday</option>
                        <option value="Other">Other</option>
                     </select>
                     <label>Reason</label>
                     <div class="invalid-feedback">Please select a reason.</div>
                     <div class="valid-feedback">Looks good!</div>
                  </div>
               </div>

               <!-- Remove Button -->
               <div class="col-md-2 mb-2">
                  <div class="d-flex h-100 align-items-end">
                     <button type="button" class="btn btn-outline-danger btn-sm remove-exception-btn form-control date-remove-btn">Remove</button>
                  </div>
               </div>
            </div>

            <!-- Add Exception Button -->
            <button type="button" class="btn btn-outline-primary btn-sm" id="add-exception-date-btn">
            + Add Exception Date
            </button>
         </div>

         <!-- Public Holidays Section -->
         <div class="mb-4">
            <h6 class="mb-2">Public Holidays in Schedule</h6>
            <p class="text-muted small mb-3">By default, classes are not scheduled on public holidays. The system will only show holidays that conflict with your class schedule (when a holiday falls on a scheduled class day). You can override specific holidays to include them in the schedule.</p>

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
         <input type="hidden" id="holiday_overrides" name="schedule_data[holiday_overrides]" value="">

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

      <!-- Class Date History Section -->
      <div class="mb-4 mt-3">
         <h5 class="mb-3">Class Date History</h5>
         <p class="text-muted small mb-3">Add stop and restart dates for this class. A class can have multiple stop and restart dates.</p>

         <!-- Container for all date history rows -->
         <div id="date-history-container"></div>

         <!-- Hidden Template Row (initially d-none) -->
         <div class="row date-history-row d-none" id="date-history-row-template">
            <!-- Stop Date -->
            <div class="col-md-5 mb-2">
               <label class="form-label">Stop Date</label>
               <input type="date" name="stop_dates[]" class="form-control form-control-sm">
               <div class="invalid-feedback">Please select a valid date.</div>
               <div class="valid-feedback">Looks good!</div>
            </div>

            <!-- Restart Date -->
            <div class="col-md-5 mb-2">
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
         + Add Stop/Restart Dates
         </button>
      </div>

      <?php echo section_divider(); ?>

      <!-- ===== Section: Funding & Exam Details ===== -->
      <?php echo section_header('Funding & Exam Details'); ?>
      <div class="row">
         <!-- SETA Funded -->
         <div class="col-md-3 mb-3">
            <div class="form-floating">
               <select id="seta_funded" name="seta_funded" class="form-select" required>
                  <option value="">Select</option>
                  <?php foreach ($data['yes_no_options'] as $option): ?>
                     <option value="<?php echo $option['id']; ?>"><?php echo $option['name']; ?></option>
                  <?php endforeach; ?>
               </select>
               <label for="seta_funded">SETA Funded? <span class="text-danger">*</span></label>
               <div class="invalid-feedback">Please select if the class is SETA funded.</div>
               <div class="valid-feedback">Looks good!</div>
            </div>
         </div>

         <!-- SETA (conditionally displayed) -->
         <div class="col-md-3 mb-3" id="seta_container" style="display: none;">
            <div class="form-floating">
               <select id="seta_id" name="seta_id" class="form-select">
                  <option value="">Select</option>
                  <?php foreach ($data['setas'] as $seta): ?>
                     <option value="<?php echo $seta['id']; ?>"><?php echo $seta['name']; ?></option>
                  <?php endforeach; ?>
               </select>
               <label for="seta_id">SETA <span class="text-danger">*</span></label>
               <div class="invalid-feedback">Please select a SETA.</div>
               <div class="valid-feedback">Looks good!</div>
            </div>
         </div>

         <!-- Exam Class -->
         <div class="col-md-3 mb-3">
            <div class="form-floating">
               <select id="exam_class" name="exam_class" class="form-select" required>
                  <option value="">Select</option>
                  <?php foreach ($data['yes_no_options'] as $option): ?>
                     <option value="<?php echo $option['id']; ?>"><?php echo $option['name']; ?></option>
                  <?php endforeach; ?>
               </select>
               <label for="exam_class">Exam Class <span class="text-danger">*</span></label>
               <div class="invalid-feedback">Please select if this is an exam class.</div>
               <div class="valid-feedback">Looks good!</div>
            </div>
         </div>

         <!-- Exam Type (conditionally displayed) -->
         <div class="col-md-3 mb-3">
            <div id="exam_type_container" style="display: none;">
               <div class="form-floating">
                  <input type="text" id="exam_type" name="exam_type" class="form-control" placeholder="Enter exam type">
                  <label for="exam_type">Exam Type</label>
                  <div class="invalid-feedback">Please provide the exam type.</div>
                  <div class="valid-feedback">Looks good!</div>
               </div>
            </div>
         </div>
      </div>

      <!-- Class Learners Section -->
      <?php echo section_header('Class Learners <span class="text-danger">*</span>', 'Select learners for this class and manage their status.'); ?>

      <div class="row mb-4">
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
                  Add Selected Learners
               </button>
            </div>
         </div>

         <!-- Learners Table -->
         <div class="col-md-8">
            <div class="mb-3">
               <div class="form-label mb-2">Class Learners</div>
               <div id="class-learners-container" class="card-body card px-5">
                  <div class="bd-callout bd-callout-info" id="no-learners-message">
                     No learners added to this class yet. At least one learner is required. Select learners from the list and click "Add Selected Learners".
                  </div>
                  <table class="table table-sm fs-9 d-none" id="class-learners-table">
                     <thead>
                        <tr>
                           <th>Learner</th>
                           <th>Level/Module</th>
                           <th>Host/Walk-in Status</th>
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
            <h5 class="mb-3">Select Learners Taking Exams</h5>
            <p class="text-muted small mb-3">Not all learners in an exam class necessarily take exams. Select which learners will take exams.</p>

            <div class="row mb-4">
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
                        Add Selected Exam Learners
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

      <!-- ===== Section: Assignments & Dates ===== -->
      <?php echo section_header('Assignments & Dates', 'Assign staff to this class.'); ?>

      <!-- Class Agents Section -->
      <div class="mb-4">
         <h5 class="mb-3">Class Agents</h5>
         <p class="text-muted small mb-3">Assign the primary class agent. If the agent changes during the class, the history will be tracked.</p>

         <!-- Initial Class Agent -->
         <div class="row mb-3">
            <div class="col-md-5 mb-3">
               <div class="form-floating">
                  <select id="initial_class_agent" name="initial_class_agent" class="form-select" required>
                     <option value="">Select</option>
                     <?php foreach ($data['agents'] as $agent): ?>
                        <option value="<?php echo $agent['id']; ?>"><?php echo $agent['name']; ?></option>
                     <?php endforeach; ?>
                  </select>
                  <label for="initial_class_agent">Initial Class Agent <span class="text-danger">*</span></label>
                  <div class="invalid-feedback">Please select the initial class agent.</div>
                  <div class="valid-feedback">Looks good!</div>
               </div>
            </div>
            <div class="col-md-5 mb-3">
               <div class="form-floating">
                  <input type="date" id="initial_agent_start_date" name="initial_agent_start_date" class="form-control" placeholder="YYYY-MM-DD" required>
                  <label for="initial_agent_start_date">Start Date <span class="text-danger">*</span></label>
                  <div class="invalid-feedback">Please select the start date.</div>
                  <div class="valid-feedback">Looks good!</div>
               </div>
            </div>
         </div>

      <!-- Project Supervisor and Delivery Date -->
      <div class="row mb-4">
         <div class="col-md-5 mb-3">
            <div class="form-floating">
               <select id="project_supervisor" name="project_supervisor" class="form-select" required>
                  <option value="">Select</option>
                  <?php foreach ($data['supervisors'] as $supervisor): ?>
                     <option value="<?php echo $supervisor['id']; ?>"><?php echo $supervisor['name']; ?></option>
                  <?php endforeach; ?>
               </select>
               <label for="project_supervisor">Project Supervisor <span class="text-danger">*</span></label>
               <div class="invalid-feedback">Please select a project supervisor.</div>
               <div class="valid-feedback">Looks good!</div>
            </div>
         </div>

         <div class="col-md-5 mb-3">
            <div class="form-floating">
               <input type="date" id="delivery_date" name="delivery_date" class="form-control" placeholder="YYYY-MM-DD" required>
               <label for="delivery_date">Delivery Date <span class="text-danger">*</span></label>
               <div class="invalid-feedback">Please select the delivery date.</div>
               <div class="valid-feedback">Looks good!</div>
            </div>
         </div>
      </div>

      <!-- Backup Agents Section -->
      <div class="mt-4 mb-4">
         <h5 class="mb-3">Backup Agents</h5>
         <p class="text-muted small mb-3">Add backup agents with specific dates when they will be available.</p>

         <!-- Container for all backup agent rows -->
         <div id="backup-agents-container"></div>

         <!-- Hidden Template Row (initially d-none) -->
         <div class="row backup-agent-row align-items-center d-none" id="backup-agent-row-template">
            <!-- Backup Agent -->
            <div class="col-md-5 mb-2">
               <div class="form-floating">
                  <select name="backup_agent_ids[]" class="form-select backup-agent-select">
                     <option value="">Select</option>
                     <?php foreach ($data['agents'] as $agent): ?>
                        <option value="<?php echo $agent['id']; ?>"><?php echo $agent['name']; ?></option>
                     <?php endforeach; ?>
                  </select>
                  <label>Backup Agent</label>
                  <div class="invalid-feedback">Please select a backup agent.</div>
                  <div class="valid-feedback">Looks good!</div>
               </div>
            </div>

            <!-- Backup Date -->
            <div class="col-md-5 mb-2">
               <div class="form-floating">
                  <input type="date" name="backup_agent_dates[]" class="form-control" placeholder="YYYY-MM-DD">
                  <label>Backup Date</label>
                  <div class="invalid-feedback">Please select a valid date.</div>
                  <div class="valid-feedback">Looks good!</div>
               </div>
            </div>

            <!-- Remove Button -->
            <div class="col-md-2 mb-2">
               <div class="d-flex h-100 align-items-end">
                  <button type="button" class="btn btn-outline-danger btn-sm remove-backup-agent-btn form-control date-remove-btn">Remove</button>
               </div>
            </div>
         </div>

         <!-- Add Row Button -->
         <button type="button" class="btn btn-outline-primary btn-sm" id="add-backup-agent-btn">
         + Add Backup Agent
         </button>
      </div>

      <?php echo section_divider(); ?>
      <!-- Submit Button - Mode-aware text -->
      <div class="row mt-4">
         <div class="col-md-3">
               <?php echo button('Add New Class', 'submit', 'primary'); ?>
         </div>
      </div>
   </div>
</form>

<!-- Alert container for form messages -->
<div id="form-messages" class="mt-3"></div>
