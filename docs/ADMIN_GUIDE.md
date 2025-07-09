# RenalTales - Administrator Guide

## Overview

This guide provides comprehensive information for administrators managing the RenalTales application. It covers system administration, user management, content moderation, monitoring, and maintenance procedures.

## Table of Contents

1. [Admin Access & Authentication](#admin-access--authentication)
2. [Dashboard Overview](#dashboard-overview)
3. [User Management](#user-management)
4. [Content Management](#content-management)
5. [System Configuration](#system-configuration)
6. [Security Management](#security-management)
7. [Monitoring & Analytics](#monitoring--analytics)
8. [Backup & Recovery](#backup--recovery)
9. [Maintenance Tasks](#maintenance-tasks)
10. [Troubleshooting](#troubleshooting)

## Admin Access & Authentication

### Accessing the Admin Panel

1. **Login URL**: `https://yoursite.com/admin` or `https://yoursite.com/admin/login`
2. **Admin Credentials**: Use your administrator account credentials
3. **Two-Factor Authentication**: Required for all admin accounts

### Admin Account Setup

#### Creating Admin Accounts
```sql
-- Create admin user via database
INSERT INTO users (username, email, password, role, created_at) 
VALUES ('admin', 'admin@renaltales.com', 'hashed_password', 'admin', NOW());

-- Grant admin privileges
INSERT INTO user_roles (user_id, role_id) 
VALUES (1, 1); -- 1 = admin role
```

#### Admin Role Permissions
- **Super Admin**: Full system access
- **Admin**: User and content management
- **Moderator**: Content moderation only
- **Support**: User support functions only

### Security Best Practices

#### Password Requirements
- Minimum 12 characters
- Must include uppercase, lowercase, numbers, and symbols
- Cannot reuse last 5 passwords
- Must be changed every 90 days

#### Session Management
- Admin sessions expire after 1 hour of inactivity
- Concurrent session limit: 1 per admin
- Automatic logout on browser close

## Dashboard Overview

### Main Dashboard Components

#### System Health
- **Server Status**: CPU, memory, disk usage
- **Database Status**: Connection count, query performance
- **Application Status**: Error rates, response times
- **Security Alerts**: Failed login attempts, suspicious activities

#### Quick Statistics
- Total registered users
- Active users (last 30 days)
- Stories published (today/week/month)
- Comments and interactions
- System errors (last 24 hours)

#### Recent Activity
- New user registrations
- Recent story publications
- Flagged content requiring review
- System alerts and warnings

### Navigation Menu

#### Main Sections
- **Dashboard**: Overview and quick stats
- **Users**: User management functions
- **Content**: Story and comment management
- **Settings**: System configuration
- **Security**: Security settings and logs
- **Reports**: Analytics and reports
- **Tools**: Maintenance and utilities

## User Management

### User Overview

#### User Listing
- **Search & Filter**: By username, email, registration date, status
- **Bulk Actions**: Enable/disable, delete, export
- **User Details**: Profile information, activity, statistics

#### User Status Management
- **Active**: Normal user account
- **Suspended**: Temporarily disabled
- **Banned**: Permanently disabled
- **Pending**: Awaiting email verification

### User Administration

#### Creating Users
1. Navigate to **Users > Add New**
2. Fill in required information:
   - Username
   - Email address
   - Password (temporary)
   - Role assignment
   - Profile information
3. Send welcome email with login instructions

#### Editing User Accounts
1. Find user in **Users** section
2. Click **Edit** button
3. Modify user information:
   - Personal details
   - Account status
   - Role assignments
   - Password reset
4. Save changes

#### User Moderation Actions

##### Suspending Users
1. Go to user's profile
2. Click **Actions > Suspend**
3. Select suspension duration
4. Provide reason for suspension
5. Optionally send notification email

##### Banning Users
1. Access user's profile
2. Click **Actions > Ban**
3. Select ban type (temporary/permanent)
4. Document reason for ban
5. User receives ban notification

##### Password Resets
1. Navigate to user's account
2. Click **Actions > Reset Password**
3. Generate temporary password
4. Send reset instructions via email

### User Activity Monitoring

#### Activity Logs
- Login/logout events
- Story publications and edits
- Comment activity
- Profile changes
- Security events

#### Reporting Suspicious Activity
- Multiple failed login attempts
- Unusual posting patterns
- Reported content from same user
- IP address changes

## Content Management

### Story Management

#### Story Overview
- **All Stories**: Complete list of published stories
- **Pending Review**: Stories awaiting moderation
- **Flagged Content**: User-reported stories
- **Draft Stories**: Unpublished stories

#### Content Moderation

##### Review Process
1. Navigate to **Content > Stories > Pending Review**
2. Review story content for:
   - Appropriate language
   - Relevant content
   - Community guidelines compliance
   - Privacy concerns
3. Take action:
   - **Approve**: Publish story
   - **Reject**: Send back to author with feedback
   - **Edit**: Make necessary changes
   - **Delete**: Remove permanently

##### Content Guidelines
- **Appropriate Content**: Relevant to kidney health
- **No Medical Advice**: Personal experiences only
- **Respectful Language**: No discriminatory content
- **Privacy Protection**: No personal medical details
- **Copyright Compliance**: Original content only

#### Editing Stories
1. Find story in content management
2. Click **Edit** button
3. Make necessary changes to:
   - Title and content
   - Category and tags
   - Privacy settings
   - Media attachments
4. Save changes and notify author if needed

### Comment Management

#### Comment Moderation
- **Pending Comments**: Awaiting approval
- **Flagged Comments**: User-reported comments
- **Spam Comments**: Automatically detected spam

#### Comment Actions
- **Approve**: Make comment visible
- **Reject**: Hide comment
- **Edit**: Modify comment content
- **Delete**: Remove permanently
- **Mark as Spam**: Train spam filter

### Media Management

#### File Upload Monitoring
- **Upload Activity**: Recent file uploads
- **File Types**: Images, documents, videos
- **Storage Usage**: Disk space utilization
- **Suspicious Files**: Potentially harmful uploads

#### Media Library
- **All Media**: Complete media library
- **Orphaned Files**: Files not linked to content
- **Large Files**: Files exceeding size limits
- **Cleanup Tools**: Remove unused media

## System Configuration

### Application Settings

#### General Settings
- **Site Name**: Application title
- **Site Description**: Brief description
- **Default Language**: System default language
- **Time Zone**: Server time zone
- **Date Format**: Display format for dates

#### User Settings
- **Registration**: Enable/disable user registration
- **Email Verification**: Require email verification
- **Login Method**: Username, email, or both
- **Password Requirements**: Complexity rules
- **Session Settings**: Timeout and security options

#### Content Settings
- **Story Approval**: Require moderation
- **Comment Moderation**: Enable comment approval
- **File Uploads**: Allowed file types and sizes
- **Content Limits**: Maximum story length
- **Privacy Options**: Available privacy levels

### Email Configuration

#### SMTP Settings
- **Mail Server**: SMTP server address
- **Port**: SMTP port (587 for TLS, 465 for SSL)
- **Encryption**: TLS/SSL encryption
- **Authentication**: Username and password
- **From Address**: Default sender address

#### Email Templates
- **Welcome Email**: New user registration
- **Password Reset**: Password recovery
- **Notifications**: System notifications
- **Moderation**: Content approval/rejection

### Database Configuration

#### Connection Settings
- **Database Host**: Server address
- **Database Name**: Database name
- **Username**: Database username
- **Password**: Database password
- **Connection Pool**: Max connections

#### Backup Settings
- **Backup Schedule**: Automated backup frequency
- **Backup Location**: Storage location
- **Retention Period**: How long to keep backups
- **Compression**: Enable backup compression

## Security Management

### Security Dashboard

#### Security Overview
- **Failed Login Attempts**: Recent failed logins
- **Suspicious Activity**: Unusual user behavior
- **IP Blocking**: Blocked IP addresses
- **Security Alerts**: System security warnings

#### Authentication Settings
- **Password Policy**: Complexity requirements
- **Two-Factor Auth**: Enable 2FA for users
- **Session Security**: Session timeout settings
- **Login Attempts**: Maximum failed attempts

### Access Control

#### Role-Based Access
- **Admin**: Full system access
- **Moderator**: Content moderation only
- **User**: Standard user permissions
- **Guest**: Limited read-only access

#### Permission Management
- **Create**: Create new content
- **Read**: View content
- **Update**: Edit existing content
- **Delete**: Remove content
- **Moderate**: Approve/reject content

### Security Monitoring

#### Log Files
- **Access Logs**: User access patterns
- **Error Logs**: System errors and warnings
- **Security Logs**: Security-related events
- **Audit Logs**: Administrative actions

#### Intrusion Detection
- **Brute Force**: Multiple failed login attempts
- **SQL Injection**: Database attack attempts
- **XSS Attacks**: Cross-site scripting attempts
- **File Upload**: Malicious file uploads

## Monitoring & Analytics

### System Monitoring

#### Performance Metrics
- **Response Time**: Page load times
- **Database Performance**: Query execution times
- **Memory Usage**: RAM utilization
- **CPU Usage**: Processor utilization
- **Disk Usage**: Storage utilization

#### Health Checks
- **Database Connectivity**: Database connection status
- **Email Service**: Mail server connectivity
- **External APIs**: Third-party service status
- **File System**: Storage accessibility

### User Analytics

#### Usage Statistics
- **Active Users**: Daily/weekly/monthly active users
- **User Growth**: Registration trends
- **Content Creation**: Story publication rates
- **Engagement**: Comments and interactions

#### Popular Content
- **Most Viewed**: Popular stories
- **Most Commented**: Engaging content
- **Trending Topics**: Popular categories and tags
- **User Preferences**: Language and category preferences

### Reports

#### System Reports
- **Performance Report**: System performance metrics
- **Security Report**: Security incidents and alerts
- **Error Report**: System errors and warnings
- **Backup Report**: Backup status and history

#### User Reports
- **User Activity**: User engagement metrics
- **Content Report**: Story and comment statistics
- **Moderation Report**: Content approval/rejection rates
- **Support Report**: User support ticket summary

## Backup & Recovery

### Backup Management

#### Backup Types
- **Full Backup**: Complete system backup
- **Incremental**: Changes since last backup
- **Database Only**: Database backup only
- **Files Only**: User files and media backup

#### Backup Schedule
- **Daily**: Database backups
- **Weekly**: Full system backups
- **Monthly**: Archive backups
- **On-Demand**: Manual backups

#### Backup Monitoring
- **Backup Status**: Success/failure notifications
- **Backup Size**: Storage usage tracking
- **Backup Verification**: Integrity checks
- **Retention Policy**: Automatic cleanup

### Recovery Procedures

#### Database Recovery
1. Stop application services
2. Restore database from backup
3. Verify data integrity
4. Restart application services
5. Test system functionality

#### File Recovery
1. Identify affected files
2. Restore from backup
3. Set proper permissions
4. Verify file integrity
5. Test file accessibility

#### Full System Recovery
1. Prepare recovery environment
2. Restore database and files
3. Update configuration files
4. Test all system functions
5. Monitor for issues

## Maintenance Tasks

### Regular Maintenance

#### Daily Tasks
- **Monitor Health**: Check system health dashboard
- **Review Logs**: Check error and security logs
- **User Support**: Address user issues
- **Content Moderation**: Review flagged content
- **Backup Verification**: Verify backup completion

#### Weekly Tasks
- **Performance Review**: Analyze performance metrics
- **Security Audit**: Review security logs
- **Database Maintenance**: Optimize database tables
- **Update Check**: Check for system updates
- **User Cleanup**: Remove inactive accounts

#### Monthly Tasks
- **Full System Backup**: Create comprehensive backup
- **Security Update**: Apply security patches
- **Performance Optimization**: Optimize system performance
- **User Analytics**: Generate user reports
- **Content Cleanup**: Remove obsolete content

### System Updates

#### Update Process
1. **Backup System**: Create full backup
2. **Test Environment**: Test update in staging
3. **Maintenance Mode**: Enable maintenance mode
4. **Apply Updates**: Install updates
5. **Test System**: Verify functionality
6. **Monitor**: Watch for issues

#### Update Types
- **Security Updates**: Critical security patches
- **Feature Updates**: New functionality
- **Bug Fixes**: Issue resolutions
- **Performance Updates**: Optimization improvements

### Database Maintenance

#### Optimization Tasks
- **Index Optimization**: Rebuild database indexes
- **Table Cleanup**: Remove orphaned records
- **Statistics Update**: Update query statistics
- **Disk Cleanup**: Remove temporary files
- **Performance Tuning**: Optimize queries

#### Monitoring Queries
```sql
-- Check database size
SELECT 
    table_schema AS 'Database',
    ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)'
FROM information_schema.tables
GROUP BY table_schema;

-- Check table sizes
SELECT 
    table_name AS 'Table',
    ROUND(((data_length + index_length) / 1024 / 1024), 2) AS 'Size (MB)'
FROM information_schema.tables
WHERE table_schema = 'renaltales'
ORDER BY (data_length + index_length) DESC;
```

## Troubleshooting

### Common Issues

#### Application Errors
**Issue**: 500 Internal Server Error
**Solutions**:
- Check error logs for details
- Verify file permissions
- Check database connectivity
- Review recent changes
- Restart web server

#### Database Issues
**Issue**: Database connection errors
**Solutions**:
- Check database server status
- Verify connection credentials
- Check network connectivity
- Review database logs
- Restart database service

#### Performance Issues
**Issue**: Slow page loading
**Solutions**:
- Check server resources
- Optimize database queries
- Enable caching
- Optimize images and media
- Review server configuration

### Diagnostic Tools

#### System Health Check
```bash
# Check system resources
htop
iostat -x 1
df -h

# Check web server status
systemctl status apache2
systemctl status nginx

# Check database status
systemctl status mysql
mysql -u root -p -e "SHOW PROCESSLIST;"
```

#### Log Analysis
```bash
# Check error logs
tail -f /var/log/apache2/error.log
tail -f /var/log/nginx/error.log
tail -f /var/www/renaltales/storage/logs/error.log

# Check access logs
tail -f /var/log/apache2/access.log
tail -f /var/log/nginx/access.log
```

### Emergency Procedures

#### Site Down
1. Check server status
2. Review error logs
3. Check database connectivity
4. Verify DNS settings
5. Enable maintenance mode if needed

#### Security Breach
1. Change all passwords
2. Review security logs
3. Block suspicious IPs
4. Update security patches
5. Notify affected users

#### Data Loss
1. Stop all services
2. Assess damage extent
3. Restore from backup
4. Verify data integrity
5. Resume services gradually

## Support Resources

### Documentation
- **User Guide**: End-user documentation
- **API Documentation**: Developer reference
- **Security Guide**: Security best practices
- **Deployment Guide**: Installation and setup

### Community Support
- **Admin Forum**: Administrator community
- **Developer Resources**: Technical documentation
- **Security Alerts**: Security notifications
- **Update Notifications**: System updates

### Professional Support
- **Technical Support**: Professional assistance
- **Training Services**: Administrator training
- **Consultation**: System optimization
- **Emergency Support**: 24/7 emergency assistance

---

## Version Information

- **Admin Guide Version**: 1.0
- **Application Version**: 2025.v1.0
- **Last Updated**: January 2025

---

*This guide is a living document. Please keep it updated as the system evolves and new features are added.*
