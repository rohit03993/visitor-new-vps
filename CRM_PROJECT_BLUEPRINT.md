# MMHC CRM System - Complete Project Blueprint

## 🎯 Project Overview

**Project Name**: MMHC (Modular Healthcare CRM)  
**Technology Stack**: Laravel 11 + Bootstrap 5 + MySQL + Redis  
**Architecture**: Modular, Microservice-ready, API-first  
**Deployment**: Incremental, Always-usable modules  

## 🏗️ System Architecture

### Core Principles
- **Modular Development**: Each feature is a standalone Laravel module
- **Always Usable**: Every module release is self-contained and immediately functional
- **API-First**: RESTful APIs for future mobile app integration
- **Scalable**: Designed for horizontal scaling

### Technology Stack
```
Frontend: Bootstrap 5 + Alpine.js + Chart.js
Backend: Laravel 11 + PHP 8.2+
Database: MySQL 8.0 + Redis
Storage: AWS S3 / Local Storage
Queue: Redis Queue
Cache: Redis
Email: Laravel Mail + Queue
SMS/WhatsApp: Third-party APIs
```

## 📊 Database Schema Design

### Core Tables Structure

#### 1. Authentication & Users
```sql
-- Users table (extends Laravel's default)
users
├── id (primary)
├── uid (unique: C-UID, P-UID, M-UID)
├── role (admin, committee, caregiver, patient)
├── phone_verified_at
├── email_verified_at
├── profile_completed_at
└── timestamps

-- User profiles
user_profiles
├── user_id (foreign)
├── first_name, last_name
├── date_of_birth
├── gender
├── address (JSON)
├── emergency_contact
└── timestamps

-- User documents
user_documents
├── user_id (foreign)
├── document_type
├── file_path
├── verification_status
├── verified_by
├── verified_at
└── timestamps
```

#### 2. Plans & Subscriptions
```sql
-- Service plans
service_plans
├── id (primary)
├── name
├── description
├── base_price
├── duration_days
├── is_active
└── timestamps

-- Patient subscriptions
subscriptions
├── id (primary)
├── patient_id (foreign)
├── plan_id (foreign)
├── start_date
├── end_date
├── status (active, expired, cancelled)
├── payment_status
└── timestamps

-- Payments
payments
├── id (primary)
├── subscription_id (foreign)
├── amount
├── payment_method
├── transaction_id
├── status
├── gateway_response (JSON)
└── timestamps
```

#### 3. Sales & Incentives
```sql
-- Sales records
sales
├── id (primary)
├── seller_id (foreign - caregiver)
├── patient_id (foreign)
├── plan_id (foreign)
├── sale_amount
├── commission_rate
├── commission_amount
├── sale_date
└── timestamps

-- Incentive payouts
incentive_payouts
├── id (primary)
├── caregiver_id (foreign)
├── period_start
├── period_end
├── total_sales
├── total_commission
├── status (pending, approved, paid)
├── approved_by
├── approved_at
└── timestamps
```

#### 4. Care Management
```sql
-- Care requests
care_requests
├── id (primary)
├── patient_id (foreign)
├── service_type
├── description
├── preferred_schedule (JSON)
├── urgency_level
├── status (pending, assigned, in_progress, completed)
├── assigned_caregiver_id (foreign)
├── assigned_at
└── timestamps

-- Visit management
visits
├── id (primary)
├── care_request_id (foreign)
├── caregiver_id (foreign)
├── patient_id (foreign)
├── scheduled_start
├── scheduled_end
├── actual_start
├── actual_end
├── status (scheduled, in_progress, completed, cancelled)
├── notes
├── patient_rating
├── patient_feedback
└── timestamps

-- Visit attachments
visit_attachments
├── id (primary)
├── visit_id (foreign)
├── file_path
├── file_type
├── uploaded_by
└── timestamps
```

#### 5. Notifications
```sql
-- Notification templates
notification_templates
├── id (primary)
├── name
├── type (email, sms, whatsapp)
├── subject
├── body
├── variables (JSON)
└── timestamps

-- Notification logs
notification_logs
├── id (primary)
├── user_id (foreign)
├── template_id (foreign)
├── type
├── status (sent, failed, pending)
├── sent_at
├── error_message
└── timestamps
```

## 🧩 Module Breakdown

### Module 1: Authentication & Users (3-7 days)
**Deliverables:**
- Multi-role authentication system
- OTP-based phone verification
- Unique ID generation (C-UID, P-UID, M-UID)
- Admin user management panel
- Role-based access control

**Technical Implementation:**
```php
// Models
User (extends Laravel's default)
UserProfile
UserDocument
Role
Permission

// Controllers
AuthController
UserController
ProfileController

// Middleware
RoleMiddleware
PermissionMiddleware

// Features
- Phone OTP verification
- Email verification
- Profile completion tracking
- Document upload system
```

**Stop-Safe State:** Users can register, login, and access role-appropriate features.

### Module 2: Profiles & Verification (5-7 days)
**Deliverables:**
- Comprehensive profile forms
- Document upload system
- Admin verification queue
- Document status tracking
- Bulk verification tools

**Technical Implementation:**
```php
// Models
UserProfile
UserDocument
VerificationQueue

// Controllers
ProfileController
DocumentController
VerificationController

// Features
- Multi-step profile forms
- File upload with validation
- Admin verification dashboard
- Document type management
- Verification workflow
```

**Stop-Safe State:** Documents uploaded, verification status visible, admin can approve/reject.

### Module 3: Plans & Subscriptions (5-7 days)
**Deliverables:**
- Service plan management
- Subscription assignment
- Payment integration (Razorpay/Stripe)
- Invoice generation
- Payment tracking

**Technical Implementation:**
```php
// Models
ServicePlan
Subscription
Payment
Invoice

// Controllers
PlanController
SubscriptionController
PaymentController

// Services
PaymentService
InvoiceService

// Features
- Plan creation and management
- Subscription lifecycle
- Payment gateway integration
- Automated invoice generation
- Payment status tracking
```

**Stop-Safe State:** Plans created, subscriptions active, payments processed.

### Module 4: Sales & Incentives (5-7 days)
**Deliverables:**
- Sales recording system
- Commission calculation
- Incentive payout management
- Committee approval workflow
- Export functionality

**Technical Implementation:**
```php
// Models
Sale
IncentivePayout
CommissionRule

// Controllers
SaleController
IncentiveController
ReportController

// Services
CommissionService
IncentiveService

// Features
- Sales tracking
- Commission calculation
- Payout approval workflow
- CSV export functionality
- Performance reports
```

**Stop-Safe State:** Sales recorded, incentives calculated, reports exportable.

### Module 5: Care Requests & Assignment (5-7 days)
**Deliverables:**
- Care request submission
- Manual assignment system
- Assignment notifications
- Request status tracking
- Caregiver availability

**Technical Implementation:**
```php
// Models
CareRequest
Assignment
CaregiverAvailability

// Controllers
CareRequestController
AssignmentController

// Services
AssignmentService
NotificationService

// Features
- Request submission forms
- Assignment dashboard
- Notification system
- Status tracking
- Availability management
```

**Stop-Safe State:** Requests submitted, assignments made, notifications sent.

### Module 6: Visit Management (5-7 days)
**Deliverables:**
- Visit logging system
- Time tracking
- Note taking
- File attachments
- Patient feedback
- Rating system

**Technical Implementation:**
```php
// Models
Visit
VisitAttachment
VisitNote
PatientFeedback

// Controllers
VisitController
FeedbackController

// Services
VisitService
FeedbackService

// Features
- Visit logging
- Time tracking
- File uploads
- Feedback collection
- Rating system
```

**Stop-Safe State:** Visits logged, feedback collected, files attached.

### Module 7: Notifications (5-7 days)
**Deliverables:**
- Email notification system
- WhatsApp API integration
- Template management
- Notification queuing
- Delivery tracking

**Technical Implementation:**
```php
// Models
NotificationTemplate
NotificationLog
NotificationQueue

// Controllers
NotificationController
TemplateController

// Services
EmailService
WhatsAppService
NotificationService

// Jobs
SendEmailJob
SendWhatsAppJob

// Features
- Template management
- Queue processing
- Delivery tracking
- Multi-channel support
```

**Stop-Safe State:** Notifications sent reliably via email and WhatsApp.

### Module 8: Reports & Dashboards (5-7 days)
**Deliverables:**
- Admin dashboard
- Committee dashboard
- Caregiver dashboard
- Patient dashboard
- Export functionality
- KPI tracking

**Technical Implementation:**
```php
// Models
Dashboard
Report
KPI

// Controllers
DashboardController
ReportController

// Services
DashboardService
ReportService

// Features
- Role-based dashboards
- KPI calculations
- Export functionality
- Real-time updates
- Chart visualizations
```

**Stop-Safe State:** Dashboards functional, reports exportable, KPIs visible.

### Module 9: Admin & Committee Tools (5-7 days)
**Deliverables:**
- Verification queue
- Dispute management
- Override capabilities
- Audit trails
- System settings

**Technical Implementation:**
```php
// Models
VerificationQueue
Dispute
AuditLog
SystemSetting

// Controllers
VerificationController
DisputeController
AuditController
SettingController

// Features
- Verification workflow
- Dispute resolution
- Override capabilities
- Audit logging
- System configuration
```

**Stop-Safe State:** Committee can handle verifications and disputes.

### Module 10: Integrations & Operations (5-7 days)
**Deliverables:**
- Payment gateway webhooks
- S3 file storage
- WhatsApp API integration
- Redis queue system
- Background job processing

**Technical Implementation:**
```php
// Services
PaymentWebhookService
S3StorageService
WhatsAppApiService
QueueService

// Jobs
ProcessPaymentWebhook
ProcessFileUpload
SendWhatsAppMessage

// Features
- Webhook handling
- File storage
- API integrations
- Queue processing
- Error handling
```

**Stop-Safe State:** All integrations functional, background jobs processing.

## 🚀 Development Strategy

### Phase 1: Foundation (Week 1)
1. **Project Setup**
   - Laravel 11 installation
   - Database configuration
   - Authentication scaffolding
   - Basic UI framework

2. **Module 1: Authentication & Users**
   - User registration/login
   - Role management
   - Basic profile system

### Phase 2: Core Features (Weeks 2-3)
1. **Module 2: Profiles & Verification**
   - Document upload
   - Verification workflow

2. **Module 3: Plans & Subscriptions**
   - Plan management
   - Payment integration

### Phase 3: Business Logic (Weeks 4-5)
1. **Module 4: Sales & Incentives**
   - Commission tracking
   - Payout management

2. **Module 5: Care Requests & Assignment**
   - Request system
   - Assignment workflow

### Phase 4: Operations (Weeks 6-7)
1. **Module 6: Visit Management**
   - Visit logging
   - Feedback system

2. **Module 7: Notifications**
   - Multi-channel notifications

### Phase 5: Analytics (Weeks 8-9)
1. **Module 8: Reports & Dashboards**
   - Analytics dashboard
   - Export functionality

2. **Module 9: Admin & Committee Tools**
   - Management tools
   - Dispute resolution

### Phase 6: Integration (Week 10)
1. **Module 10: Integrations & Operations**
   - External integrations
   - Background processing

## 🧪 Testing Strategy

### Unit Testing
- Model testing
- Service testing
- Controller testing

### Integration Testing
- API endpoint testing
- Database integration testing
- External service testing

### End-to-End Testing
- User workflow testing
- Cross-module functionality
- Performance testing

## 📦 Deployment Strategy

### Development Environment
- Local development with Docker
- Database seeding
- Test data generation

### Staging Environment
- Production-like setup
- Integration testing
- Performance testing

### Production Environment
- Blue-green deployment
- Database migrations
- Rollback procedures

## 🔒 Security Considerations

### Authentication & Authorization
- JWT tokens for API
- Role-based access control
- Session management

### Data Protection
- Input validation
- SQL injection prevention
- XSS protection

### File Security
- File type validation
- Virus scanning
- Secure file storage

## 📈 Performance Optimization

### Database Optimization
- Indexing strategy
- Query optimization
- Connection pooling

### Caching Strategy
- Redis caching
- Query result caching
- Static asset caching

### Background Processing
- Queue system
- Job scheduling
- Error handling

## 🔧 Maintenance & Monitoring

### Logging
- Application logging
- Error tracking
- Performance monitoring

### Backup Strategy
- Database backups
- File backups
- Disaster recovery

### Updates & Patches
- Security updates
- Feature updates
- Bug fixes

## 📋 Success Metrics

### Technical Metrics
- System uptime: 99.9%
- Response time: <2 seconds
- Error rate: <1%

### Business Metrics
- User adoption rate
- Feature utilization
- Customer satisfaction

## 🎯 Deliverables Summary

### Code Deliverables
- Complete Laravel application
- Database migrations
- API documentation
- Unit tests
- Integration tests

### Documentation Deliverables
- Technical documentation
- User manuals
- API documentation
- Deployment guides

### Training Deliverables
- Admin training
- User training
- Technical training
- Support documentation

---

**Total Estimated Timeline: 10 weeks**  
**Total Estimated Effort: 50-70 days**  
**Team Size: 2-3 developers**  
**Budget: $15,000 - $25,000**

This blueprint provides a comprehensive roadmap for building a modular, scalable CRM system that can be deployed incrementally while maintaining full functionality at each stage.
