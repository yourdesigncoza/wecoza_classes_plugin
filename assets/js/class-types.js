/**
 * Class Types & Durations JavaScript for WeCoza Classes Plugin
 *
 * Handles the two-level selection system for class types and subjects,
 * and implements automatic duration calculation.
 * Extracted from WeCoza theme for standalone plugin
 */

/**
 * Global variable to store the AJAX URL
 * This will be set by WordPress via wp_localize_script
 */
var wecozaClass = wecozaClass || {
    ajaxUrl: '/wp-admin/admin-ajax.php',
    debug: true
};

document.addEventListener('DOMContentLoaded', function() {
    // Get DOM elements
    const classTypeSelect = document.getElementById('class_type');
    const classSubjectSelect = document.getElementById('class_subject');
    const classDurationInput = document.getElementById('class_duration');
    const classCodeInput = document.getElementById('class_code');

    // Class subjects data (will be populated via AJAX)
    let classSubjectsData = {};

    // Event listener for client change
    const clientSelect = document.getElementById('client_id');
    if (clientSelect) {
        clientSelect.addEventListener('change', function() {
            // Regenerate class code when client changes
            regenerateClassCode();
        });
    }

    // Event listener for class type change
    if (classTypeSelect) {
        classTypeSelect.addEventListener('change', function() {
            const selectedClassType = this.value;

            // Reset subject dropdown
            classSubjectSelect.innerHTML = '<option value="">Select Subject</option>';
            classSubjectSelect.disabled = !selectedClassType;

            // Reset duration and code
            classDurationInput.value = '';
            classCodeInput.value = '';

            if (selectedClassType) {
                // Fetch subjects for the selected class type
                fetchClassSubjects(selectedClassType);
            }
        });
    }

    // Event listener for class subject change
    if (classSubjectSelect) {
        classSubjectSelect.addEventListener('change', function() {
            const selectedClassType = classTypeSelect.value;
            const selectedSubject = this.value;
            const selectedClientId = document.getElementById('client_id')?.value;

            if (selectedClassType && selectedSubject && selectedClientId) {
                // Find the selected subject in the data
                const subjectData = classSubjectsData[selectedClassType].find(
                    subject => subject.id === selectedSubject
                );

                if (subjectData) {
                    // Set duration
                    classDurationInput.value = subjectData.duration;

                    // Generate class code with client ID
                    classCodeInput.value = generateClassCode(selectedClientId, selectedClassType, selectedSubject);
                }
            } else {
                // Reset duration and code
                classDurationInput.value = '';
                classCodeInput.value = '';
            }
        });
    }

    /**
     * Fetch class subjects for the selected class type
     *
     * @param {string} classType The selected class type
     */
    function fetchClassSubjects(classType) {
        // Show loading indicator
        classSubjectSelect.innerHTML = '<option value="">Loading...</option>';

        // Get the AJAX URL from WordPress
        const ajaxUrl = (typeof wecozaClass !== 'undefined' && wecozaClass.ajaxUrl)
            ? wecozaClass.ajaxUrl
            : '/wp-admin/admin-ajax.php';

        console.log('Fetching subjects for class type:', classType);
        console.log('Using AJAX URL:', ajaxUrl);

        // Make AJAX request to get subjects
        const requestUrl = `${ajaxUrl}?action=get_class_subjects&class_type=${classType}`;
        console.log('Making AJAX request to:', requestUrl);

        fetch(requestUrl)
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                console.log('Received data:', data);
                if (data.success) {
                    // Extract the subjects array from the response
                    let subjectsArray = [];

                    // Check if data.data exists and is an array
                    if (data.data && Array.isArray(data.data)) {
                        subjectsArray = data.data;
                        console.log('Found subjects array in data.data:', subjectsArray);
                    }
                    // Try to convert object to array if needed
                    else if (data.data) {
                        console.log('Data is not in expected format, attempting to convert:', data.data);
                        if (typeof data.data === 'object') {
                            subjectsArray = Object.values(data.data);
                            console.log('Converted object to array:', subjectsArray);
                        }
                    }

                    // Store subjects data for later use
                    classSubjectsData[classType] = subjectsArray;

                    // Reset dropdown
                    classSubjectSelect.innerHTML = '<option value="">Select Subject</option>';

                    // Add options to dropdown
                    if (subjectsArray.length > 0) {
                        subjectsArray.forEach(subject => {
                            if (subject && subject.id && subject.name) {
                                const option = document.createElement('option');
                                option.value = subject.id;
                                option.textContent = subject.name;
                                classSubjectSelect.appendChild(option);
                            }
                        });
                    } else {
                        console.error('No valid subjects found in the response');
                        classSubjectSelect.innerHTML = '<option value="">No subjects available</option>';
                    }
                } else {
                    // Handle error
                    classSubjectSelect.innerHTML = '<option value="">Error loading subjects</option>';
                    console.error('Error loading class subjects:', data.message || 'Unknown error');
                }
            })
            .catch(error => {
                // Handle error
                classSubjectSelect.innerHTML = '<option value="">Error loading subjects</option>';
                console.error('Error loading class subjects:', error);

                // Show more detailed error in the dropdown for debugging
                if (typeof wecozaClass !== 'undefined' && wecozaClass.debug) {
                    classSubjectSelect.innerHTML = `<option value="">Error: ${error.message}</option>`;
                }
            });
    }

    /**
     * Helper function to regenerate class code when any required field changes
     */
    function regenerateClassCode() {
        const selectedClientId = document.getElementById('client_id')?.value;
        const selectedClassType = classTypeSelect?.value;
        const selectedSubject = classSubjectSelect?.value;

        if (selectedClientId && selectedClassType && selectedSubject) {
            classCodeInput.value = generateClassCode(selectedClientId, selectedClassType, selectedSubject);
        } else {
            classCodeInput.value = '';
        }
    }

    /**
     * Generate a class code based on client ID, class type and subject
     *
     * @param {string} clientId The selected client ID
     * @param {string} classType The selected class type
     * @param {string} subjectId The selected subject ID
     * @return {string} The generated class code
     */
    function generateClassCode(clientId, classType, subjectId) {
        // Format: [ClientID]-[ClassType]-[SubjectID]-[YYYY]-[MM]-[DD]-[HH]-[MM]
        // Example: 11-REALLL-RLN-2025-06-25-02-14
        const now = new Date();

        // Create readable datetime components
        const year = now.getFullYear(); // Full year (2025)
        const month = (now.getMonth() + 1).toString().padStart(2, '0'); // Month (01-12)
        const day = now.getDate().toString().padStart(2, '0'); // Day (01-31)
        const hour = now.getHours().toString().padStart(2, '0'); // Hour (00-23)
        const minute = now.getMinutes().toString().padStart(2, '0'); // Minute (00-59)

        return `${clientId}-${classType}-${subjectId}-${year}-${month}-${day}-${hour}-${minute}`;
    }

    // Initialize on page load if elements exist
    if (classTypeSelect && classSubjectSelect) {
        // Check if we have pre-selected values (for update mode)
        const preSelectedType = classTypeSelect.value;
        const preSelectedSubject = classSubjectSelect.value;

        if (preSelectedType && preSelectedSubject) {
            // Fetch subjects for the pre-selected type
            fetchClassSubjects(preSelectedType);
            
            // After a short delay, set the pre-selected subject
            setTimeout(() => {
                classSubjectSelect.value = preSelectedSubject;
                // Trigger change event to update duration and code
                classSubjectSelect.dispatchEvent(new Event('change'));
            }, 500);
        }
    }
});
