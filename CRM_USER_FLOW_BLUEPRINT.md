# MMHC CRM System - Complete User Flow Blueprint

## 🎯 **Complete User Journey Mapping**

This document provides step-by-step user flows for every module, showing exactly what happens when users click URLs and navigate through the system.

---

## 📱 **Module 1: Authentication & Users**

### **URL Structure:**
```
/auth/login          → Login page
/auth/register       → Registration page
/auth/verify-phone   → Phone verification
/auth/verify-email   → Email verification
/dashboard          → Role-based dashboard
/admin/users        → User management
```

### **Flow 1: User Registration**
```
1. User visits /auth/register
   ↓
2. Registration Form Displayed:
   - Full Name
   - Phone Number
   - Email
   - Password
   - Confirm Password
   - Role Selection (Caregiver/Patient)
   ↓
3. User fills form and clicks "Register"
   ↓
4. System validates data:
   - Phone number format
   - Email format
   - Password strength
   - Role selection
   ↓
5. If validation fails:
   - Show error messages
   - Return to form
   ↓
6. If validation passes:
   - Create user account
   - Generate unique UID (C-UID for Caregiver, P-UID for Patient)
   - Send OTP to phone
   - Redirect to /auth/verify-phone
   ↓
7. Phone Verification Page:
   - Display phone number
   - OTP input field
   - "Verify" button
   - "Resend OTP" link
   ↓
8. User enters OTP and clicks "Verify"
   ↓
9. System verifies OTP:
   - If correct: Mark phone as verified, send email verification
   - If incorrect: Show error, allow retry
   ↓
10. Email Verification:
    - User receives email with verification link
    - Clicks link → /auth/verify-email?token=xxx
    - System verifies token and marks email as verified
    ↓
11. Redirect to /dashboard with success message
```

### **Flow 2: User Login**
```
1. User visits /auth/login
   ↓
2. Login Form Displayed:
   - Phone/Email field
   - Password field
   - "Remember Me" checkbox
   - "Forgot Password" link
   ↓
3. User enters credentials and clicks "Login"
   ↓
4. System validates:
   - User exists
   - Password correct
   - Account active
   - Phone verified
   ↓
5. If validation fails:
   - Show error message
   - Return to login form
   ↓
6. If validation passes:
   - Create session
   - Set user role in session
   - Redirect to role-based dashboard
```

### **Flow 3: Role-Based Dashboard Access**
```
1. User logs in successfully
   ↓
2. System checks user role:
   - Admin → /admin/dashboard
   - Committee → /committee/dashboard
   - Caregiver → /caregiver/dashboard
   - Patient → /patient/dashboard
   ↓
3. Dashboard displays:
   - Welcome message with user name
   - Role-specific menu
   - Quick stats
   - Recent activities
```

---

## 👤 **Module 2: Profiles & Verification**

### **URL Structure:**
```
/profile/edit           → Edit profile
/profile/documents      → Document upload
/admin/verification     → Verification queue
/admin/verification/{id} → Verify specific user
```

### **Flow 1: Profile Completion**
```
1. User clicks "Complete Profile" from dashboard
   ↓
2. Redirect to /profile/edit
   ↓
3. Profile Form Displayed:
   - Personal Information:
     * First Name, Last Name
     * Date of Birth
     * Gender
     * Address (with autocomplete)
   - Contact Information:
     * Emergency Contact Name
     * Emergency Contact Phone
     * Emergency Contact Relation
   - Professional Information (for Caregivers):
     * Experience Years
     * Specializations
     * Certifications
   ↓
4. User fills form and clicks "Save Profile"
   ↓
5. System validates data:
   - Required fields
   - Date format
   - Phone format
   ↓
6. If validation fails:
   - Show field-specific errors
   - Return to form
   ↓
7. If validation passes:
   - Save profile data
   - Mark profile as completed
   - Redirect to /profile/documents
```

### **Flow 2: Document Upload**
```
1. User visits /profile/documents
   ↓
2. Document Upload Page Displayed:
   - List of required documents:
     * ID Proof (Aadhaar/PAN/Driving License)
     * Address Proof
     * Professional Certificates (for Caregivers)
     * Medical Certificates (for Caregivers)
   - Upload area for each document
   - File type restrictions shown
   - Maximum file size shown
   ↓
3. User uploads documents:
   - Drag & drop or click to upload
   - File validation (type, size)
   - Preview of uploaded file
   - "Remove" option for each file
   ↓
4. User clicks "Submit for Verification"
   ↓
5. System processes:
   - Save file paths
   - Mark documents as "Pending Verification"
   - Send notification to admin
   - Show success message
   ↓
6. Redirect to dashboard with status update
```

### **Flow 3: Admin Verification Process**
```
1. Admin visits /admin/verification
   ↓
2. Verification Queue Displayed:
   - List of users pending verification
   - User details (name, role, submission date)
   - Document count
   - "Review" button for each user
   ↓
3. Admin clicks "Review" for a user
   ↓
4. Redirect to /admin/verification/{user_id}
   ↓
5. User Verification Page:
   - User profile information
   - All uploaded documents (viewable/downloadable)
   - Verification form:
     * "Approve" button
     * "Reject" button
     * Comments field
   ↓
6. Admin reviews documents:
   - Opens each document
   - Verifies authenticity
   - Checks completeness
   ↓
7. Admin makes decision:
   - If Approve: Click "Approve", add comments
   - If Reject: Click "Reject", add rejection reason
   ↓
8. System processes decision:
   - Update user verification status
   - Send notification to user
   - Update admin dashboard
   ↓
9. Redirect back to verification queue
```

---

## 💰 **Module 3: Plans & Subscriptions**

### **URL Structure:**
```
/plans                    → View all plans
/plans/{id}              → Plan details
/subscriptions           → User subscriptions
/subscriptions/create    → Create subscription
/payments                → Payment history
/admin/plans             → Manage plans
```

### **Flow 1: Plan Selection**
```
1. User visits /plans
   ↓
2. Plans List Displayed:
   - Plan cards with:
     * Plan name
     * Description
     * Price
     * Duration
     * Features list
     * "Select Plan" button
   - Filter options (price range, duration)
   - Search functionality
   ↓
3. User clicks on a plan card
   ↓
4. Redirect to /plans/{plan_id}
   ↓
5. Plan Details Page:
   - Detailed plan information
   - Feature comparison
   - Pricing breakdown
   - "Subscribe Now" button
   ↓
6. User clicks "Subscribe Now"
   ↓
7. Redirect to /subscriptions/create?plan={plan_id}
```

### **Flow 2: Subscription Creation**
```
1. User visits /subscriptions/create?plan={plan_id}
   ↓
2. Subscription Form Displayed:
   - Selected plan details
   - Subscription duration
   - Start date selection
   - Payment method selection
   - Terms & conditions checkbox
   ↓
3. User fills form and clicks "Proceed to Payment"
   ↓
4. System validates:
   - Plan availability
   - User eligibility
   - Required fields
   ↓
5. If validation fails:
   - Show error messages
   - Return to form
   ↓
6. If validation passes:
   - Create subscription record
   - Generate payment link
   - Redirect to payment gateway
```

### **Flow 3: Payment Processing**
```
1. User redirected to payment gateway
   ↓
2. Payment Gateway Page:
   - Amount to pay
   - Payment method options (Card, UPI, Net Banking)
   - Payment form
   ↓
3. User completes payment:
   - Enters payment details
   - Clicks "Pay Now"
   ↓
4. Payment processing:
   - Gateway processes payment
   - Returns success/failure response
   ↓
5. If payment successful:
   - Update subscription status
   - Send confirmation email
   - Redirect to /subscriptions with success message
   ↓
6. If payment failed:
   - Show error message
   - Allow retry
   - Redirect back to payment form
```

---

## 💼 **Module 4: Sales & Incentives**

### **URL Structure:**
```
/sales                    → Sales dashboard
/sales/create            → Record new sale
/incentives              → Incentive dashboard
/incentives/export       → Export incentives
/admin/incentives        → Manage incentives
```

### **Flow 1: Recording a Sale**
```
1. Caregiver visits /sales/create
   ↓
2. Sales Form Displayed:
   - Patient selection (searchable dropdown)
   - Plan selection
   - Sale amount
   - Sale date
   - Notes field
   ↓
3. User fills form and clicks "Record Sale"
   ↓
4. System validates:
   - Patient exists
   - Plan is available
   - Amount is valid
   - Required fields
   ↓
5. If validation fails:
   - Show error messages
   - Return to form
   ↓
6. If validation passes:
   - Calculate commission
   - Create sale record
   - Update incentive totals
   - Send notification to committee
   ↓
7. Redirect to /sales with success message
```

### **Flow 2: Incentive Management**
```
1. User visits /incentives
   ↓
2. Incentive Dashboard Displayed:
   - Current period summary
   - Total sales amount
   - Commission earned
   - Payout status
   - Historical data chart
   ↓
3. User can:
   - View detailed breakdown
   - Export to CSV
   - View payout history
   ↓
4. If user clicks "Export":
   - Generate CSV file
   - Download file
   - Show success message
```

---

## 🏥 **Module 5: Care Requests & Assignment**

### **URL Structure:**
```
/requests                 → Care requests list
/requests/create         → Create new request
/requests/{id}           → Request details
/assignments             → Assignment dashboard
/admin/assignments       → Manage assignments
```

### **Flow 1: Creating a Care Request**
```
1. Patient visits /requests/create
   ↓
2. Care Request Form Displayed:
   - Service type selection
   - Description of needs
   - Preferred schedule (date/time)
   - Urgency level
   - Special requirements
   ↓
3. User fills form and clicks "Submit Request"
   ↓
4. System validates:
   - Required fields
   - Date format
   - Service availability
   ↓
5. If validation fails:
   - Show error messages
   - Return to form
   ↓
6. If validation passes:
   - Create request record
   - Send notification to committee
   - Show success message
   ↓
7. Redirect to /requests with new request visible
```

### **Flow 2: Assignment Process**
```
1. Committee member visits /admin/assignments
   ↓
2. Assignment Dashboard Displayed:
   - Pending requests list
   - Available caregivers
   - Assignment form
   ↓
3. Committee member selects request
   ↓
4. Assignment Form Displayed:
   - Request details
   - Caregiver selection dropdown
   - Assignment notes
   - "Assign" button
   ↓
5. Committee member assigns caregiver
   ↓
6. System processes assignment:
   - Update request status
   - Send notification to caregiver
   - Send notification to patient
   - Update assignment records
   ↓
7. Redirect to assignments dashboard
```

---

## 📋 **Module 6: Visit Management**

### **URL Structure:**
```
/visits                   → Visits dashboard
/visits/create           → Log new visit
/visits/{id}             → Visit details
/visits/{id}/feedback    → Patient feedback
/visits/{id}/attachments → Upload attachments
```

### **Flow 1: Logging a Visit**
```
1. Caregiver visits /visits/create
   ↓
2. Visit Logging Form Displayed:
   - Patient selection
   - Visit date/time
   - Services provided
   - Notes field
   - Attachment upload
   ↓
3. User fills form and clicks "Log Visit"
   ↓
4. System validates:
   - Patient exists
   - Date is valid
   - Required fields
   ↓
5. If validation fails:
   - Show error messages
   - Return to form
   ↓
6. If validation passes:
   - Create visit record
   - Save attachments
   - Send notification to patient
   - Update visit statistics
   ↓
7. Redirect to /visits with success message
```

### **Flow 2: Patient Feedback**
```
1. Patient receives notification about visit
   ↓
2. Patient clicks feedback link
   ↓
3. Redirect to /visits/{visit_id}/feedback
   ↓
4. Feedback Form Displayed:
   - Visit details
   - Rating scale (1-5 stars)
   - Comments field
   - "Submit Feedback" button
   ↓
5. Patient submits feedback
   ↓
6. System processes feedback:
   - Save rating and comments
   - Update caregiver statistics
   - Send notification to caregiver
   ↓
7. Redirect to patient dashboard
```

---

## 📧 **Module 7: Notifications**

### **URL Structure:**
```
/notifications           → Notification center
/notifications/{id}      → View notification
/admin/notifications    → Manage notifications
/admin/templates        → Notification templates
```

### **Flow 1: Notification Center**
```
1. User visits /notifications
   ↓
2. Notification Center Displayed:
   - Unread notifications count
   - Notification list with:
     * Type (email, SMS, WhatsApp)
     * Subject
     * Timestamp
     * Read/Unread status
   - Filter options
   - "Mark all as read" button
   ↓
3. User can:
   - Click notification to view details
   - Mark as read/unread
   - Delete notifications
   - Filter by type/date
```

### **Flow 2: Template Management**
```
1. Admin visits /admin/templates
   ↓
2. Template Management Displayed:
   - List of notification templates
   - Template type (email, SMS, WhatsApp)
   - Status (active/inactive)
   - "Edit" button for each template
   ↓
3. Admin clicks "Edit" on a template
   ↓
4. Template Editor Displayed:
   - Template name
   - Subject line
   - Body content with variables
   - Preview functionality
   - "Save" button
   ↓
5. Admin edits template and clicks "Save"
   ↓
6. System validates and saves template
   ↓
7. Redirect to templates list
```

---

## 📊 **Module 8: Reports & Dashboards**

### **URL Structure:**
```
/dashboard               → Main dashboard
/reports                → Reports section
/reports/sales          → Sales reports
/reports/visits         → Visit reports
/reports/export         → Export reports
```

### **Flow 1: Dashboard View**
```
1. User visits /dashboard
   ↓
2. Role-Based Dashboard Displayed:
   - Welcome message
   - Key metrics cards:
     * Total users
     * Active subscriptions
     * Pending requests
     * Recent activities
   - Charts and graphs
   - Quick action buttons
   ↓
3. User can:
   - Click on metrics for details
   - Navigate to specific sections
   - View recent activities
   - Access quick actions
```

### **Flow 2: Report Generation**
```
1. User visits /reports
   ↓
2. Reports Section Displayed:
   - Report categories
   - Date range selector
   - Filter options
   - "Generate Report" button
   ↓
3. User selects report type and filters
   ↓
4. User clicks "Generate Report"
   ↓
5. System processes request:
   - Query database
   - Generate report data
   - Display results
   ↓
6. Report Results Displayed:
   - Data table
   - Charts/graphs
   - Export options (CSV, PDF)
   - Print option
```

---

## ⚙️ **Module 9: Admin & Committee Tools**

### **URL Structure:**
```
/admin/dashboard        → Admin dashboard
/admin/verification     → Verification queue
/admin/disputes         → Dispute management
/admin/overrides        → Override panel
/admin/audit            → Audit logs
```

### **Flow 1: Dispute Management**
```
1. Admin visits /admin/disputes
   ↓
2. Disputes List Displayed:
   - Dispute ID
   - Parties involved
   - Dispute type
   - Status
   - Created date
   - "View Details" button
   ↓
3. Admin clicks "View Details"
   ↓
4. Dispute Details Page:
   - Full dispute information
   - Evidence/attachments
   - Resolution form:
     * Resolution type
     * Comments
     * "Resolve" button
   ↓
5. Admin resolves dispute
   ↓
6. System processes resolution:
   - Update dispute status
   - Send notifications
   - Update records
   ↓
7. Redirect to disputes list
```

---

## 🔗 **Module 10: Integrations & Operations**

### **URL Structure:**
```
/admin/integrations     → Integration settings
/admin/webhooks         → Webhook management
/admin/queue            → Queue monitoring
/admin/logs             → System logs
```

### **Flow 1: Integration Management**
```
1. Admin visits /admin/integrations
   ↓
2. Integration Settings Displayed:
   - Payment gateway settings
   - WhatsApp API settings
   - S3 storage settings
   - Status indicators
   - "Configure" buttons
   ↓
3. Admin clicks "Configure" for an integration
   ↓
4. Configuration Form Displayed:
   - API keys/credentials
   - Endpoint URLs
   - Test connection button
   - "Save" button
   ↓
5. Admin configures integration
   ↓
6. System tests connection
   ↓
7. If successful:
   - Save configuration
   - Show success message
   - Update status
   ↓
8. If failed:
   - Show error message
   - Allow retry
```

---

## 🎯 **Complete URL Mapping**

### **Public Routes:**
```
GET  /                    → Welcome page
GET  /auth/login         → Login page
GET  /auth/register       → Registration page
POST /auth/login         → Process login
POST /auth/register      → Process registration
GET  /auth/verify-phone  → Phone verification
POST /auth/verify-phone  → Process phone verification
GET  /auth/verify-email  → Email verification
POST /auth/verify-email  → Process email verification
```

### **Authenticated Routes:**
```
GET  /dashboard          → Role-based dashboard
GET  /profile/edit       → Edit profile
POST /profile/edit       → Update profile
GET  /profile/documents  → Document upload
POST /profile/documents   → Upload documents
GET  /plans              → View plans
GET  /plans/{id}         → Plan details
GET  /subscriptions      → User subscriptions
POST /subscriptions      → Create subscription
GET  /payments           → Payment history
GET  /sales              → Sales dashboard
POST /sales              → Record sale
GET  /incentives         → Incentive dashboard
GET  /requests           → Care requests
POST /requests           → Create request
GET  /visits             → Visits dashboard
POST /visits             → Log visit
GET  /notifications      → Notification center
GET  /reports            → Reports section
```

### **Admin Routes:**
```
GET  /admin/dashboard    → Admin dashboard
GET  /admin/users        → User management
GET  /admin/verification → Verification queue
POST /admin/verification → Process verification
GET  /admin/plans        → Manage plans
GET  /admin/disputes     → Dispute management
GET  /admin/integrations → Integration settings
```

---

## 🔄 **Complete User Journey Examples**

### **Caregiver Journey:**
```
1. Register → /auth/register
2. Verify phone → /auth/verify-phone
3. Complete profile → /profile/edit
4. Upload documents → /profile/documents
5. Wait for verification → /dashboard
6. View plans → /plans
7. Subscribe to plan → /subscriptions/create
8. Record sales → /sales/create
9. View incentives → /incentives
10. Log visits → /visits/create
```

### **Patient Journey:**
```
1. Register → /auth/register
2. Verify phone → /auth/verify-phone
3. Complete profile → /profile/edit
4. Upload documents → /profile/documents
5. Wait for verification → /dashboard
6. View plans → /plans
7. Subscribe to plan → /subscriptions/create
8. Create care request → /requests/create
9. Wait for assignment → /requests
10. Provide feedback → /visits/{id}/feedback
```

### **Admin Journey:**
```
1. Login → /auth/login
2. Admin dashboard → /admin/dashboard
3. Verify users → /admin/verification
4. Manage plans → /admin/plans
5. Handle disputes → /admin/disputes
6. View reports → /reports
7. Manage integrations → /admin/integrations
```

This blueprint provides the complete user flow for every module, showing exactly what happens when users click on URLs and navigate through the system. Each flow is detailed with step-by-step instructions and expected outcomes.
