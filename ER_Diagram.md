# ER Diagram - CSTU SPACE System

## Entity Relationship Diagram

```mermaid
erDiagram
    USER ||--o{ PROJECT : "advisor"
    USER ||--o{ PROJECT : "committee"
    USER ||--o{ PROJECT_EVALUATION : "evaluates"
    USER ||--o{ LOGIN_LOG : "logs"
    USER ||--|| USER_ROLE : "has"
    
    STUDENT ||--o{ GROUP_MEMBER : "joins"
    STUDENT ||--o{ GROUP_INVITATION : "invites/invited"
    STUDENT ||--o{ LOGIN_LOG : "logs"
    STUDENT ||--o{ PROJECT_PROPOSAL : "proposes"
    
    GROUP ||--|{ GROUP_MEMBER : "has"
    GROUP ||--o{ GROUP_INVITATION : "has"
    GROUP ||--|| PROJECT : "has"
    GROUP ||--o{ PROJECT_PROPOSAL : "submits"
    
    PROJECT ||--|| PROJECT_GRADE : "has"
    PROJECT ||--o{ PROJECT_EVALUATION : "evaluated_by"
    PROJECT ||--o| EXAM_SCHEDULE : "scheduled"
    
    USER {
        int user_id PK
        string firstname_user
        string lastname_user
        string user_code UK
        int role FK
        string email_user
        string username_user
        string password_user
    }
    
    USER_ROLE {
        bigint role_id PK
        string role_name UK
        int role_code UK
        bigint role_code_bin
        timestamp created_at
        timestamp updated_at
    }
    
    STUDENT {
        int student_id PK
        string firstname_std
        string lastname_std
        string email_std
        int role
        string username_std
        string password_std
        string student_code UK
        string student_type
        string department
    }
    
    GROUP {
        bigint group_id PK
        int year
        tinyint semester
        string subject_code
        enum status_group
        timestamp created_at
        timestamp updated_at
    }
    
    GROUP_MEMBER {
        bigint groupmem_id PK
        bigint group_id FK
        string username_std FK
        timestamp created_at
        timestamp updated_at
    }
    
    GROUP_INVITATION {
        bigint invitation_id PK
        bigint group_id FK
        string inviter_username FK
        string invitee_username FK
        enum status
        text message
        timestamp responded_at
        timestamp created_at
        timestamp updated_at
    }
    
    PROJECT {
        bigint project_id PK
        bigint group_id FK,UK
        string project_name
        string project_code UK
        string advisor_code FK
        string committee1_code FK
        string committee2_code FK
        string committee3_code FK
        datetime exam_datetime
        enum student_type
        enum status_project
        text project_type
        string submission_file
        string submission_original_name
        timestamp submitted_at
        string submitted_by FK
        timestamp created_at
        timestamp updated_at
    }
    
    PROJECT_PROPOSAL {
        bigint proposal_id PK
        bigint group_id FK
        string proposed_title
        text description
        string proposed_to FK
        string proposed_by FK
        enum status
        text rejection_reason
        timestamp proposed_at
        timestamp responded_at
        timestamp created_at
        timestamp updated_at
    }
    
    PROJECT_EVALUATION {
        bigint evaluation_id PK
        bigint project_id FK
        string evaluator_code FK
        enum evaluator_role
        decimal document_score
        decimal presentation_score
        decimal total_score
        text comments
        timestamp submitted_at
        timestamp created_at
        timestamp updated_at
    }
    
    PROJECT_GRADE {
        bigint grade_id PK
        bigint project_id FK,UK
        decimal final_score
        string grade
        boolean advisor_confirmed
        timestamp advisor_confirmed_at
        boolean committee1_confirmed
        timestamp committee1_confirmed_at
        boolean committee2_confirmed
        timestamp committee2_confirmed_at
        boolean committee3_confirmed
        timestamp committee3_confirmed_at
        boolean all_confirmed
        timestamp all_confirmed_at
        boolean grade_released
        timestamp grade_released_at
        timestamp created_at
        timestamp updated_at
    }
    
    EXAM_SCHEDULE {
        bigint ex_id PK
        bigint project_id FK
        datetime ex_start_time
        datetime ex_end_time
        string location
        text notes
        timestamp created_at
        timestamp updated_at
    }
    
    LOGIN_LOG {
        bigint id PK
        string username
        string user_type
        bigint user_id FK
        bigint student_id FK
        string role
        string ip_address
        text user_agent
        enum login_status
        string failure_reason
        datetime login_time
        datetime logout_time
        int session_duration
        timestamp created_at
        timestamp updated_at
    }
    
    SYSTEM_SETTING {
        bigint setting_id PK
        string setting_key UK
        text setting_value
        string description
        timestamp created_at
        timestamp updated_at
    }
    
    RELATIONSHIP_WITH_PROJECT {
        bigint id PK
        string relationship
        string relationship_abbrev
        timestamp created_at
        timestamp updated_at
    }
```

## ความสัมพันธ์ระหว่างตาราง (Relationships)

### 1. USER & PROJECT
- **One-to-Many**: USER → PROJECT (advisor)
- **One-to-Many**: USER → PROJECT (committee1, committee2, committee3)
- อาจารย์ 1 คนสามารถเป็นที่ปรึกษาหรือกรรมการได้หลายโครงงาน

### 2. USER & PROJECT_EVALUATION
- **One-to-Many**: USER → PROJECT_EVALUATION
- อาจารย์ 1 คนสามารถประเมินได้หลายโครงงาน

### 3. USER & USER_ROLE
- **Many-to-One**: USER → USER_ROLE
- ผู้ใช้มีบทบาทตาม role_code

### 4. STUDENT & GROUP_MEMBER
- **One-to-Many**: STUDENT → GROUP_MEMBER
- นักศึกษา 1 คนสามารถเป็นสมาชิกกลุ่มได้หลายกลุ่ม (ในภาคการศึกษาต่างกัน)

### 5. STUDENT & GROUP_INVITATION
- **One-to-Many**: STUDENT → GROUP_INVITATION (inviter)
- **One-to-Many**: STUDENT → GROUP_INVITATION (invitee)
- นักศึกษาสามารถเชิญและถูกเชิญได้หลายครั้ง

### 6. GROUP & GROUP_MEMBER
- **One-to-Many**: GROUP → GROUP_MEMBER
- กลุ่ม 1 กลุ่มมีสมาชิกได้หลายคน

### 7. GROUP & PROJECT
- **One-to-One**: GROUP → PROJECT
- กลุ่ม 1 กลุ่มมีโครงงานได้ 1 โครงงาน

### 8. GROUP & PROJECT_PROPOSAL
- **One-to-Many**: GROUP → PROJECT_PROPOSAL
- กลุ่มสามารถส่งข้อเสนอโครงงานได้หลายครั้ง

### 9. PROJECT & PROJECT_GRADE
- **One-to-One**: PROJECT → PROJECT_GRADE
- โครงงาน 1 โครงมีเกรดได้ 1 เกรด

### 10. PROJECT & PROJECT_EVALUATION
- **One-to-Many**: PROJECT → PROJECT_EVALUATION
- โครงงาน 1 โครงถูกประเมินโดยหลายคน (ที่ปรึกษา + กรรมการ)

### 11. PROJECT & EXAM_SCHEDULE
- **One-to-Zero-or-One**: PROJECT → EXAM_SCHEDULE
- โครงงานอาจมีหรือไม่มีตารางสอบ

### 12. USER & LOGIN_LOG / STUDENT & LOGIN_LOG
- **One-to-Many**: USER → LOGIN_LOG
- **One-to-Many**: STUDENT → LOGIN_LOG
- เก็บประวัติการเข้าใช้งานของทุกคน

## สรุปโครงสร้างระบบ

ระบบนี้ประกอบด้วย **13 ตารางหลัก**:

1. **USER** - ข้อมูลผู้ใช้ (อาจารย์, ผู้ดูแลระบบ, เจ้าหน้าที่)
2. **USER_ROLE** - บทบาทของผู้ใช้
3. **STUDENT** - ข้อมูลนักศึกษา
4. **GROUP** - กลุ่มนักศึกษา
5. **GROUP_MEMBER** - สมาชิกในกลุ่ม
6. **GROUP_INVITATION** - คำเชิญเข้ากลุ่ม
7. **PROJECT** - โครงงาน
8. **PROJECT_PROPOSAL** - ข้อเสนอโครงงาน
9. **PROJECT_EVALUATION** - การประเมินโครงงาน
10. **PROJECT_GRADE** - เกรดโครงงาน
11. **EXAM_SCHEDULE** - ตารางสอบ
12. **LOGIN_LOG** - ประวัติการเข้าใช้งาน
13. **SYSTEM_SETTING** - การตั้งค่าระบบ
14. **RELATIONSHIP_WITH_PROJECT** - ความสัมพันธ์กับโครงงาน

## Flow การทำงานหลัก

1. **นักศึกษาสร้างกลุ่ม** → GROUP
2. **เชิญสมาชิก** → GROUP_INVITATION → GROUP_MEMBER
3. **เสนอโครงงาน** → PROJECT_PROPOSAL (ส่งให้อาจารย์)
4. **อนุมัติ → สร้างโครงงาน** → PROJECT
5. **กำหนดวันสอบ** → EXAM_SCHEDULE
6. **ส่งรายงาน** → อัพเดต PROJECT (submission_file)
7. **อาจารย์ประเมิน** → PROJECT_EVALUATION
8. **คำนวณเกรด** → PROJECT_GRADE
9. **อาจารย์ยืนยันเกรด** → อัพเดต PROJECT_GRADE
10. **ปล่อยเกรด** → นักศึกษาดูได้
