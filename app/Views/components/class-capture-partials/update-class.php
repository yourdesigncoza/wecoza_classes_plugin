<!-- Classes Update Form -->
<form id="classes-form" class="needs-validation ydcoza-compact-form" novalidate method="POST" enctype="multipart/form-data">
   <!-- Hidden Class ID for Update -->
   <input type="hidden" id="class_id" name="class_id" value="<?php echo esc_attr($data['class_data']['class_id'] ?? ''); ?>">
   <input type="hidden" id="redirect_url" name="redirect_url" value="<?php echo esc_attr($data['redirect_url'] ?? ''); ?>">
   <input type="hidden" id="nonce" name="nonce" value="<?php echo wp_create_nonce('wecoza_class_nonce'); ?>">

   <!-- ===== Section: Basic Details ===== -->
   <div class="container container-md classes-form ps-0">
      
      <!-- UPDATE MODE: Show current values -->
      <div class="row">
         <!-- Client Name (ID) -->
         <div class="col-md-3 mb-3">
            <div class="form-floating">
               <select id="client_id" name="client_id" class="form-select" required>
                  <option value="">Select</option>
                  <?php foreach ($data['clients'] as $client): ?>
                     <option value="<?php echo esc_attr($client['id']); ?>" 
                        <?php echo ($data['class_data']['client_id'] ?? '') == $client['id'] ? 'selected' : ''; ?>>
                        <?php echo esc_html($client['name']); ?>
                     </option>
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
                              <option value="<?php echo esc_attr($site['id']); ?>"
                                 <?php echo ($data['class_data']['site_id'] ?? '') == $site['id'] ? 'selected' : ''; ?>>
                                 <?php echo esc_html($site['name']); ?>
                              </option>
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

         <!-- Address Field -->
         <div class="col-md-6 mb-3" id="address-wrapper">
            <div class="form-floating">
               <input
                  type="text"
                  id="site_address"
                  name="site_address"
                  class="form-control"
                  placeholder="Street, Suburb, Town, Postal Code"
                  value="<?php echo esc_attr($data['class_data']['class_address_line'] ?? ''); ?>"
                  readonly
                  />
               <label for="site_address">Address</label>
            </div>
         </div>
      </div>

      <?php echo section_divider(); ?>
      
      <!-- ===== Section: Class Info ===== -->
      <div class="row mt-3">
         <!-- Class Type -->
         <div class="col-md-4 mb-3">
            <div class="form-floating">
               <select id="class_type" name="class_type" class="form-select" required>
                  <option value="">Select</option>
                  <?php foreach ($data['class_types'] as $class_type): ?>
                     <option value="<?php echo esc_attr($class_type['id']); ?>"
                        <?php echo ($data['class_data']['class_type'] ?? '') == $class_type['id'] ? 'selected' : ''; ?>>
                        <?php echo esc_html($class_type['name']); ?>
                     </option>
                  <?php endforeach; ?>
               </select>
               <label for="class_type">Class Type <span class="text-danger">*</span></label>
               <div class="invalid-feedback">Please select the class type.</div>
               <div class="valid-feedback">Looks good!</div>
            </div>
         </div>

         <!-- Class Subject -->
         <div class="col-md-4 mb-3">
            <div class="form-floating">
               <select id="class_subject" name="class_subject" class="form-select" required>
                  <option value="">Select Class Type First</option>
                  <!-- Options will be populated by JavaScript based on class type -->
               </select>
               <label for="class_subject">Class Subject <span class="text-danger">*</span></label>
               <div class="invalid-feedback">Please select the class subject.</div>
               <div class="valid-feedback">Looks good!</div>
            </div>
         </div>

         <!-- Class Duration -->
         <div class="col-md-4 mb-3">
            <div class="form-floating">
               <input type="number" id="class_duration" name="class_duration" class="form-control" 
                      value="<?php echo esc_attr($data['class_data']['class_duration'] ?? ''); ?>" readonly>
               <label for="class_duration">Duration (Hours)</label>
               <div class="form-text">Automatically calculated based on class type and subject.</div>
            </div>
         </div>
      </div>

      <div class="row">
         <!-- Class Code -->
         <div class="col-md-4 mb-3">
            <div class="form-floating">
               <input type="text" id="class_code" name="class_code" class="form-control" 
                      value="<?php echo esc_attr($data['class_data']['class_code'] ?? ''); ?>" readonly>
               <label for="class_code">Class Code</label>
               <div class="form-text">Auto generated class identifier</div>
            </div>
         </div>

         <!-- Original Start Date -->
         <div class="col-md-4 mb-3">
            <div class="form-floating">
               <input type="date" id="class_start_date" name="class_start_date" class="form-control" 
                      value="<?php echo esc_attr($data['class_data']['original_start_date'] ?? ''); ?>" required>
               <label for="class_start_date">Class Original Start Date <span class="text-danger">*</span></label>
               <div class="invalid-feedback">Please select the start date.</div>
               <div class="valid-feedback">Looks good!</div>
            </div>
         </div>

         <!-- SETA Funded -->
         <div class="col-md-4 mb-3">
            <div class="form-floating">
               <select id="seta_funded" name="seta_funded" class="form-select">
                  <option value="">Select</option>
                  <?php foreach ($data['yes_no_options'] as $option): ?>
                     <option value="<?php echo esc_attr($option['id']); ?>"
                        <?php echo ($data['class_data']['seta_funded'] ?? '') == $option['id'] ? 'selected' : ''; ?>>
                        <?php echo esc_html($option['name']); ?>
                     </option>
                  <?php endforeach; ?>
               </select>
               <label for="seta_funded">SETA Funded</label>
            </div>
         </div>
      </div>

      <!-- Agent Assignment -->
      <div class="row">
         <!-- Class Agent -->
         <div class="col-md-4 mb-3">
            <div class="form-floating">
               <select id="class_agent" name="class_agent" class="form-select">
                  <option value="">Select</option>
                  <?php foreach ($data['agents'] as $agent): ?>
                     <option value="<?php echo esc_attr($agent['id']); ?>"
                        <?php echo ($data['class_data']['class_agent'] ?? '') == $agent['id'] ? 'selected' : ''; ?>>
                        <?php echo esc_html($agent['id'] . ' : ' . $agent['name']); ?>
                     </option>
                  <?php endforeach; ?>
               </select>
               <label for="class_agent">Agent</label>
            </div>
         </div>

         <!-- Project Supervisor -->
         <div class="col-md-4 mb-3">
            <div class="form-floating">
               <select id="project_supervisor" name="project_supervisor" class="form-select">
                  <option value="">Select</option>
                  <?php foreach ($data['supervisors'] as $supervisor): ?>
                     <option value="<?php echo esc_attr($supervisor['id']); ?>"
                        <?php echo ($data['class_data']['project_supervisor_id'] ?? '') == $supervisor['id'] ? 'selected' : ''; ?>>
                        <?php echo esc_html($supervisor['name']); ?>
                     </option>
                  <?php endforeach; ?>
               </select>
               <label for="project_supervisor">Supervisor</label>
            </div>
         </div>

         <!-- Delivery Date -->
         <div class="col-md-4 mb-3">
            <div class="form-floating">
               <input type="date" id="delivery_date" name="delivery_date" class="form-control" 
                      value="<?php echo esc_attr($data['class_data']['delivery_date'] ?? ''); ?>">
               <label for="delivery_date">Delivery Date</label>
            </div>
         </div>
      </div>

      <!-- Hidden fields for complex data -->
      <input type="hidden" id="class_learners_data" name="class_learners_data" value="">
      <input type="hidden" id="backup_agents_data" name="backup_agents_data" value="">
      <input type="hidden" id="schedule_data" name="schedule_data" value="">
      <input type="hidden" id="stop_restart_dates" name="stop_restart_dates" value="">

      <!-- Submit Button -->
      <div class="row mt-4">
         <div class="col-md-3">
            <?php echo button('Update Class', 'submit', 'primary'); ?>
         </div>
      </div>
   </div>
</form>

<!-- Alert container for form messages -->
<div id="form-messages" class="mt-3"></div>

<script>
// Pre-populate hidden fields with existing data
document.addEventListener('DOMContentLoaded', function() {
    // Set existing data for complex fields
    <?php if (!empty($data['class_data']['learner_ids'])): ?>
    document.getElementById('class_learners_data').value = <?php echo json_encode($data['class_data']['learner_ids']); ?>;
    <?php endif; ?>
    
    <?php if (!empty($data['class_data']['backup_agent_ids'])): ?>
    document.getElementById('backup_agents_data').value = <?php echo json_encode($data['class_data']['backup_agent_ids']); ?>;
    <?php endif; ?>
    
    <?php if (!empty($data['class_data']['schedule_data'])): ?>
    document.getElementById('schedule_data').value = <?php echo json_encode($data['class_data']['schedule_data']); ?>;
    <?php endif; ?>
    
    <?php if (!empty($data['class_data']['stop_restart_dates'])): ?>
    document.getElementById('stop_restart_dates').value = <?php echo json_encode($data['class_data']['stop_restart_dates']); ?>;
    <?php endif; ?>
});
</script>
