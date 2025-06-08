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

      </div>

      <!-- Submit Button -->
      <div class="row mt-4">
         <div class="col-md-3">
            <?php echo button('Add New Class', 'submit', 'primary'); ?>
         </div>
      </div>
   </div>
</form>

<!-- Alert container for form messages -->
<div id="form-messages" class="mt-3"></div>
