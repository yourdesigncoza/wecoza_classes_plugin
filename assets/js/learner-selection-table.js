/**
 * Learner Selection Table JavaScript
 * Handles search, pagination, sorting, and checkbox selection for the learner selection table
 */

class LearnerSelectionTable {
    constructor() {
        this.currentPage = 1;
        this.itemsPerPage = 10;
        this.sortField = 'surname';
        this.sortDirection = 'asc';
        this.searchTerm = '';
        this.allLearners = [];
        this.filteredLearners = [];
        this.selectedLearners = new Set();
        this.assignedLearners = new Set(); // To track learners already assigned to class

        this.init();
    }

    init() {
        // Store all learner data
        this.loadLearnerData();

        // Bind events
        this.bindEvents();

        // Initial render
        this.filterAndSort();
        this.render();
    }

    loadLearnerData() {
        const rows = document.querySelectorAll('.learner-row');
        this.allLearners = Array.from(rows).map(row => {
            const checkbox = row.querySelector('.learner-checkbox');
            const learnerData = JSON.parse(checkbox.getAttribute('data-learner-data'));
            return {
                ...learnerData,
                element: row,
                checkbox: checkbox
            };
        });
    }

    bindEvents() {
        // Search functionality
        const searchInput = document.getElementById('learner-search-input');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.searchTerm = e.target.value.toLowerCase();
                this.currentPage = 1;
                this.filterAndSort();
                this.render();
            });
        }

        // Sorting functionality
        document.querySelectorAll('.sortable-column').forEach(header => {
            header.addEventListener('click', () => {
                const field = header.getAttribute('data-field');
                this.sortTable(field);
            });
        });

        // Select all checkbox
        const selectAllCheckbox = document.getElementById('select-all-learners');
        if (selectAllCheckbox) {
            selectAllCheckbox.addEventListener('change', (e) => {
                this.toggleSelectAll(e.target.checked);
            });
        }

        // Individual learner checkboxes
        document.querySelectorAll('.learner-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', (e) => {
                const learnerId = parseInt(e.target.value);
                const row = e.target.closest('.learner-row');
                const learnerData = JSON.parse(e.target.getAttribute('data-learner-data'));

                if (e.target.checked) {
                    this.selectedLearners.add(learnerId);
                    row.classList.add('table-active');
                } else {
                    this.selectedLearners.delete(learnerId);
                    row.classList.remove('table-active');
                }

                this.updateSelectAllCheckbox();
            });
        });

        // Pagination
        document.getElementById('learner-pagination-prev')?.addEventListener('click', (e) => {
            e.preventDefault();
            const prevLi = e.target.closest('.page-item');
            if (!prevLi.classList.contains('disabled') && this.currentPage > 1) {
                this.currentPage--;
                this.render();
            }
        });

        document.getElementById('learner-pagination-next')?.addEventListener('click', (e) => {
            e.preventDefault();
            const nextLi = e.target.closest('.page-item');
            const totalPages = Math.ceil(this.filteredLearners.length / this.itemsPerPage);
            if (!nextLi.classList.contains('disabled') && this.currentPage < totalPages) {
                this.currentPage++;
                this.render();
            }
        });

        // Add selected learners button
        document.getElementById('add-selected-learners-btn')?.addEventListener('click', () => {
            this.addSelectedLearners();
        });
    }

    filterAndSort() {
        // Filter learners based on search term
        this.filteredLearners = this.allLearners.filter(learner => {
            if (!this.searchTerm) return true;

            const searchableFields = [
                learner.first_name,
                learner.second_name,
                learner.initials,
                learner.surname,
                learner.id_number,
                learner.city_town_name,
                learner.province_region_name,
                learner.postal_code
            ].filter(Boolean).join(' ').toLowerCase();

            return searchableFields.includes(this.searchTerm);
        });

        // Sort filtered learners
        this.filteredLearners.sort((a, b) => {
            const aValue = a[this.sortField] || '';
            const bValue = b[this.sortField] || '';

            if (this.sortDirection === 'asc') {
                return aValue.localeCompare(bValue);
            } else {
                return bValue.localeCompare(aValue);
            }
        });
    }

    sortTable(field) {
        if (this.sortField === field) {
            this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            this.sortField = field;
            this.sortDirection = 'asc';
        }

        this.currentPage = 1;
        this.filterAndSort();
        this.render();
        this.updateSortIndicators();
    }

    updateSortIndicators() {
        document.querySelectorAll('.sortable-column').forEach(header => {
            const field = header.getAttribute('data-field');
            const icon = header.querySelector('i');

            icon.className = 'bi bi-arrow-down-up ms-1';

            if (field === this.sortField) {
                icon.className = this.sortDirection === 'asc'
                    ? 'bi bi-arrow-up ms-1'
                    : 'bi bi-arrow-down ms-1';
            }
        });
    }

    render() {
        this.updateCounts();
        this.renderTableRows();
        this.renderPagination();
    }

    updateCounts() {
        const totalLearners = this.allLearners.length;
        const showingCount = this.filteredLearners.length;

        const totalCountElement = document.getElementById('learner-total-count');
        const showingCountElement = document.getElementById('learner-showing-count');

        if (totalCountElement) totalCountElement.textContent = totalLearners;
        if (showingCountElement) showingCountElement.textContent = showingCount;
    }

    renderTableRows() {
        const tbody = document.getElementById('learner-selection-tbody');
        if (!tbody) return;
        
        const startIndex = (this.currentPage - 1) * this.itemsPerPage;
        const endIndex = startIndex + this.itemsPerPage;
        const pageLearners = this.filteredLearners.slice(startIndex, endIndex);

        // Hide all rows first
        this.allLearners.forEach(learner => {
            if (learner.element) {
                learner.element.style.display = 'none';
            }
        });

        // Show only current page learners
        pageLearners.forEach(learner => {
            if (learner.element) {
                learner.element.style.display = '';

                // Update visual state based on selection
                if (this.selectedLearners.has(learner.id)) {
                    learner.element.classList.add('table-active');
                    if (learner.checkbox) learner.checkbox.checked = true;
                } else {
                    learner.element.classList.remove('table-active');
                    if (learner.checkbox) learner.checkbox.checked = false;
                }

                // Mark already assigned learners
                if (this.assignedLearners.has(learner.id)) {
                    learner.element.classList.add('table-secondary');
                    if (learner.checkbox) learner.checkbox.disabled = true;
                } else {
                    learner.element.classList.remove('table-secondary');
                    if (learner.checkbox) learner.checkbox.disabled = false;
                }
            }
        });
    }

    renderPagination() {
        const totalPages = Math.ceil(this.filteredLearners.length / this.itemsPerPage);
        const startRecord = (this.currentPage - 1) * this.itemsPerPage + 1;
        const endRecord = Math.min(this.currentPage * this.itemsPerPage, this.filteredLearners.length);

        // Update pagination info
        const paginationStart = document.getElementById('pagination-start');
        const paginationEnd = document.getElementById('pagination-end');
        const paginationTotal = document.getElementById('pagination-total');
        
        if (paginationStart) paginationStart.textContent = this.filteredLearners.length > 0 ? startRecord : 0;
        if (paginationEnd) paginationEnd.textContent = endRecord;
        if (paginationTotal) paginationTotal.textContent = this.filteredLearners.length;

        // Update pagination buttons
        const prevButton = document.getElementById('learner-pagination-prev');
        const nextButton = document.getElementById('learner-pagination-next');
        const prevLi = prevButton ? prevButton.closest('.page-item') : null;
        const nextLi = nextButton ? nextButton.closest('.page-item') : null;

        if (prevLi) {
            if (this.currentPage === 1) {
                prevLi.classList.add('disabled');
            } else {
                prevLi.classList.remove('disabled');
            }
        }

        if (nextLi) {
            if (this.currentPage === totalPages || totalPages === 0) {
                nextLi.classList.add('disabled');
            } else {
                nextLi.classList.remove('disabled');
            }
        }

        // Render page numbers
        this.renderPageNumbers(totalPages);
    }

    renderPageNumbers(totalPages) {
        const paginationUl = document.getElementById('learner-pagination-ul');
        if (!paginationUl) return;

        // Remove existing page number elements (keep prev and next buttons)
        const existingPageNumbers = paginationUl.querySelectorAll('li:not(:first-child):not(:last-child)');
        existingPageNumbers.forEach(li => li.remove());

        if (totalPages <= 1) return;

        const maxVisiblePages = 5;
        let startPage = Math.max(1, this.currentPage - Math.floor(maxVisiblePages / 2));
        let endPage = Math.min(totalPages, startPage + maxVisiblePages - 1);

        // Adjust if we're near the end
        if (endPage - startPage < maxVisiblePages - 1) {
            startPage = Math.max(1, endPage - maxVisiblePages + 1);
        }

        // Add first page and ellipsis if needed
        if (startPage > 1) {
            this.addPageButton(paginationUl, 1);
            if (startPage > 2) {
                this.addEllipsis(paginationUl);
            }
        }

        // Add visible page numbers
        for (let i = startPage; i <= endPage; i++) {
            this.addPageButton(paginationUl, i);
        }

        // Add ellipsis and last page if needed
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                this.addEllipsis(paginationUl);
            }
            this.addPageButton(paginationUl, totalPages);
        }
    }

    addPageButton(paginationUl, pageNum) {
        const li = document.createElement('li');
        li.className = `page-item ${pageNum === this.currentPage ? 'active' : ''}`;
        
        const a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.textContent = pageNum;
        a.setAttribute('data-page-number', pageNum);
        
        a.addEventListener('click', (e) => {
            e.preventDefault();
            this.currentPage = pageNum;
            this.render();
        });
        
        li.appendChild(a);
        
        // Insert before the "next" button (last child)
        const nextButton = paginationUl.querySelector('li:last-child');
        paginationUl.insertBefore(li, nextButton);
    }

    addEllipsis(paginationUl) {
        const li = document.createElement('li');
        li.className = 'page-item disabled';
        
        const span = document.createElement('span');
        span.className = 'page-link';
        span.textContent = '...';
        
        li.appendChild(span);
        
        // Insert before the "next" button (last child)
        const nextButton = paginationUl.querySelector('li:last-child');
        paginationUl.insertBefore(li, nextButton);
    }

    toggleSelectAll(checked) {
        const currentPageLearners = this.getCurrentPageLearners();

        currentPageLearners.forEach(learner => {
            if (!this.assignedLearners.has(learner.id)) {
                if (checked) {
                    this.selectedLearners.add(learner.id);
                } else {
                    this.selectedLearners.delete(learner.id);
                }
            }
        });

        this.render();
        this.updateSelectAllCheckbox();
    }

    getCurrentPageLearners() {
        const startIndex = (this.currentPage - 1) * this.itemsPerPage;
        const endIndex = startIndex + this.itemsPerPage;
        return this.filteredLearners.slice(startIndex, endIndex);
    }

    updateSelectAllCheckbox() {
        const selectAllCheckbox = document.getElementById('select-all-learners');
        
        // Exit early if the select all checkbox doesn't exist
        if (!selectAllCheckbox) {
            return;
        }
        
        const currentPageLearners = this.getCurrentPageLearners();
        const availableLearners = currentPageLearners.filter(learner => !this.assignedLearners.has(learner.id));

        if (availableLearners.length === 0) {
            selectAllCheckbox.checked = false;
            selectAllCheckbox.indeterminate = false;
        } else {
            const selectedCount = availableLearners.filter(learner => this.selectedLearners.has(learner.id)).length;

            if (selectedCount === 0) {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = false;
            } else if (selectedCount === availableLearners.length) {
                selectAllCheckbox.checked = true;
                selectAllCheckbox.indeterminate = false;
            } else {
                selectAllCheckbox.checked = false;
                selectAllCheckbox.indeterminate = true;
            }
        }
    }

    addSelectedLearners() {
        if (this.selectedLearners.size === 0) {
            this.showNotification('Please select at least one learner to add.', 'warning');
            return;
        }

        // Get selected learner data
        const selectedLearnerData = [];
        this.selectedLearners.forEach(learnerId => {
            const learner = this.allLearners.find(l => l.id === learnerId);
            if (learner && !this.assignedLearners.has(learnerId)) {
                selectedLearnerData.push(learner);
                this.assignedLearners.add(learnerId);
            }
        });

        // Add to existing learners container
        this.addToClassLearners(selectedLearnerData);

        // Clear selection
        this.selectedLearners.clear();
        this.render();
        this.updateSelectAllCheckbox();

        // Show success message
        this.showNotification(`Added ${selectedLearnerData.length} learner(s) to the class.`, 'success');
    }

    addToClassLearners(learners) {
        // This function integrates with the existing learner assignment system
        const tbody = document.getElementById('class-learners-tbody');
        const table = document.getElementById('class-learners-table');
        const noLearnersMessage = document.getElementById('no-learners-message');

        if (tbody) {
            learners.forEach(learner => {
                // Check if learner already exists in the table
                const existingRow = tbody.querySelector(`tr[data-learner-id="${learner.id}"]`);
                if (!existingRow) {
                    const row = this.createLearnerRow(learner);
                    tbody.appendChild(row);
                }
            });

            // Show table and hide message
            if (table) table.classList.remove('d-none');
            if (noLearnersMessage) noLearnersMessage.style.display = 'none';

            // Update the hidden field with learner data
            this.updateLearnersDataField();
        }
    }

    createLearnerRow(learner) {
        const row = document.createElement('tr');
        row.setAttribute('data-learner-id', learner.id);
        row.innerHTML = `
            <td>${learner.name}</td>
            <td>
                ${classes_generate_learner_level_select_html(learner.id)}
            </td>
            <td>
                <select class="form-select form-select-sm learner-status-select" data-learner-id="${learner.id}">
                    <option value="CIC - Currently in Class">CIC - Currently in Class</option>
                    <option value="RBE - Removed by Employer">RBE - Removed by Employer</option>
                    <option value="DRO - Drop Out">DRO - Drop Out</option>
                </select>
            </td>
            <td>
                <button type="button" class="btn btn-subtle-danger btn-sm remove-learner-btn" data-learner-id="${learner.id}">Remove</button>
            </td>
        `;

        // Add remove functionality
        const removeBtn = row.querySelector('.remove-learner-btn');
        removeBtn.addEventListener('click', (e) => {
            const learnerId = parseInt(e.target.closest('[data-learner-id]').getAttribute('data-learner-id'));
            this.removeLearner(learnerId);
        });

        // Add change listeners for level and status inputs
        const levelSelect = row.querySelector('.learner-level-select');
        const statusSelect = row.querySelector('.learner-status-select');

        levelSelect.addEventListener('change', () => this.updateLearnersDataField());
        statusSelect.addEventListener('change', () => this.updateLearnersDataField());

        return row;
    }

    removeLearner(learnerId) {
        this.assignedLearners.delete(learnerId);

        // Remove row from table
        const row = document.querySelector(`#class-learners-tbody tr[data-learner-id="${learnerId}"]`);
        if (row) row.remove();

        // Show no learners message if table is empty
        const tbody = document.getElementById('class-learners-tbody');
        const table = document.getElementById('class-learners-table');
        const noLearnersMessage = document.getElementById('no-learners-message');

        if (tbody && tbody.children.length === 0) {
            if (table) table.classList.add('d-none');
            if (noLearnersMessage) noLearnersMessage.style.display = '';
        }

        // Re-enable checkbox in selection table and uncheck it
        const checkbox = document.querySelector(`.learner-checkbox[value="${learnerId}"]`);
        if (checkbox) {
            checkbox.disabled = false;
            checkbox.checked = false;
        }
        this.selectedLearners.delete(learnerId);

        // Update learners data field
        this.updateLearnersDataField();

        // Refresh the selection table to update visual states
        this.render();
    }

    updateLearnersDataField() {
        const hiddenField = document.getElementById('class_learners_data');
        const tbody = document.getElementById('class-learners-tbody');

        if (!hiddenField || !tbody) return;

        const learners = [];
        const rows = tbody.querySelectorAll('tr[data-learner-id]');

        rows.forEach(row => {
            const learnerId = row.getAttribute('data-learner-id');
            const levelSelect = row.querySelector('.learner-level-select');
            const statusSelect = row.querySelector('.learner-status-select');

            learners.push({
                id: learnerId,
                level: levelSelect.value || '',
                status: statusSelect.value || 'CIC - Currently in Class'
            });
        });

        hiddenField.value = JSON.stringify(learners);
    }

    showNotification(message, type = 'info') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 80px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;

        // Add to page
        document.body.appendChild(notification);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    // Public method to refresh the table data (useful when learners are updated)
    refresh() {
        this.loadLearnerData();
        this.filterAndSort();
        this.render();
    }

    setAssignedLearners(assignedLearnerIds) {
        // Update assigned learners set
        this.assignedLearners = new Set(assignedLearnerIds);
        
        // Update visual state of checkboxes
        this.updateAssignedLearnerVisuals();
    }

    updateAssignedLearnerVisuals() {
        const rows = document.querySelectorAll('.learner-row');
        
        rows.forEach(row => {
            const learnerId = row.getAttribute('data-learner-id');
            const checkbox = row.querySelector('.learner-checkbox');
            
            if (learnerId && this.assignedLearners.has(parseInt(learnerId))) {
                // Mark as assigned
                row.classList.add('learner-assigned');
                if (checkbox) {
                    checkbox.disabled = true;
                    checkbox.checked = true;
                    checkbox.setAttribute('title', 'This learner is already assigned to this class');
                }
            } else {
                // Not assigned
                row.classList.remove('learner-assigned');
                if (checkbox) {
                    checkbox.disabled = false;
                    checkbox.removeAttribute('title');
                }
            }
        });
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('learner-selection-table')) {
        window.learnerSelectionTable = new LearnerSelectionTable();
    }
});
