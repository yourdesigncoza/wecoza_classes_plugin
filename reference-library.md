# Reference Library - WeCoza Classes Plugin

## Class Notes & QA Integration

### Files Modified:

1. **app/Controllers/ClassController.php**
   - Added methods: getClassQAData(), deleteClassNote(), submitQAQuestion(), uploadAttachment()
   - File upload handling with WordPress media library integration

2. **assets/js/class-capture.js**
   - Added ClassNotesQAModels with Note, QAVisit, and Collection classes
   - Implemented drag-and-drop file upload functionality
   - Form processing with validation and auto-save
   - AJAX data loading functions

3. **app/Views/components/class-capture-partials/update-class.php**
   - Added Class Note Modal (#classNoteModal)
   - Added QA Form Modal (#qaFormModal)
   - Drag-and-drop file upload interface

4. **config/app.php**
   - Registered new AJAX endpoints:
     - get_class_qa_data
     - delete_class_note
     - submit_qa_question
     - upload_attachment

### Key Components:

- **Data Models**: JavaScript OOP models for Notes and QA Visits
- **Collection Manager**: Generic collection class with search/filter/pagination
- **File Upload**: HTML5 drag-and-drop with progress tracking
- **Auto-save**: localStorage-based draft saving
- **CSRF Protection**: WordPress nonce verification

### Dependencies:

- Bootstrap 5 (modals, forms)
- jQuery (AJAX, DOM manipulation)
- WordPress Media Library API
- Font Awesome / Bootstrap Icons