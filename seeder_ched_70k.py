"""
================================================================================
SEAIT Introllment - University Database Seeder
================================================================================
Updates university database with CHED programs, disciplines, and test users:
  Step 1: Update Programs table with 9 CHED Disciplines & 56 Degree Programs.
  Step 2: Clean existing records from users, students, and staff tables.
  Step 3: Generate 31 Staff members (Registrar, Accounting, Clinic, Admin).
  Step 4: Generate 70,000 Students using high-performance batch insertion.

Usage:
  python seeder_ched_70k.py [--students 70000] [--batch-size 5000] [--db enrollment]
================================================================================
"""

import sys
import time
import random
import argparse
import concurrent.futures
from datetime import date

if hasattr(sys.stdout, "reconfigure"):
    try:
        sys.stdout.reconfigure(encoding="utf-8", errors="replace")
        sys.stderr.reconfigure(encoding="utf-8", errors="replace")
    except Exception:
        pass

# Database driver support (mysql.connector preferred, pymysql fallback)
try:
    import mysql.connector
    from mysql.connector import errorcode
    DRIVER = "mysql.connector"
except ImportError:
    try:
        import pymysql
        DRIVER = "pymysql"
    except ImportError:
        print("[ERROR] Neither 'mysql-connector-python' nor 'pymysql' is installed.")
        print("Please run: pip install mysql-connector-python pymysql bcrypt")
        sys.exit(1)

try:
    import bcrypt
except ImportError:
    print("[ERROR] 'bcrypt' is required for password hashing.")
    print("Please run: pip install bcrypt")
    sys.exit(1)


# ==============================================================================
# CHED DISCIPLINES & ACADEMIC PROGRAMS
# ==============================================================================
CHED_CLUSTERS = {
    "Business, Management, and Accountancy": [
        "BS Accountancy",
        "BS Management Accounting",
        "BS Accounting Information System",
        "BS Business Administration",
        "BS Entrepreneurship",
        "BS Customs Administration",
        "BS Hospitality Management",
        "BS Tourism Management"
    ],
    "Engineering, Architecture, and Technology": [
        "BS Civil Engineering",
        "BS Mechanical Engineering",
        "BS Electrical Engineering",
        "BS Electronics Engineering",
        "BS Computer Engineering",
        "BS Architecture",
        "BS Interior Design",
        "BS Landscape Architecture",
        "BS Aeronautical Engineering",
        "BS Aviation"
    ],
    "Medical and Allied Health Professions": [
        "BS Nursing",
        "BS Midwifery",
        "BS Physical Therapy",
        "BS Respiratory Therapy",
        "BS Medical Technology (Medical Laboratory Science)",
        "BS Radiologic Technology",
        "BS Pharmacy"
    ],
    "Information Technology Education": [
        "BS Computer Science",
        "BS Information Technology",
        "BS Information Systems",
        "BS Entertainment and Multimedia Computing"
    ],
    "Education Science and Teacher Training": [
        "Bachelor of Elementary Education (BEEd)",
        "Bachelor of Secondary Education (BSEd)",
        "Bachelor of Early Childhood Education",
        "Bachelor of Special Needs Education",
        "Bachelor of Physical Education"
    ],
    "Social Sciences and Criminal Justice": [
        "BA/BS Psychology",
        "BA Communication",
        "BA Political Science",
        "BS Social Work",
        "BA History",
        "BS Criminology",
        "Bachelor of Forensic Science",
        "Bachelor of Laws / Juris Doctor"
    ],
    "Natural Sciences and Mathematics": [
        "BS Biology",
        "BS Chemistry",
        "BS Environmental Science",
        "BS Physics",
        "BS Mathematics",
        "BS Statistics"
    ],
    "Agriculture, Forestry, and Fisheries": [
        "BS Agriculture",
        "BS Agribusiness",
        "BS Agricultural and Biosystems Engineering",
        "BS Forestry",
        "BS Fisheries",
        "BS Marine Biology"
    ],
    "Maritime Education": [
        "BS Marine Transportation",
        "BS Marine Engineering"
    ]
}

ENROLLMENT_STATUSES = ["Enrolled", "Dropped", "Irregular", "Graduated"]
STATUS_WEIGHTS = [0.70, 0.08, 0.14, 0.08]  # Realistic university distribution

FIRST_NAMES_M = [
    "Juan", "Jose", "Carlo", "Mark", "John", "Christian", "Angelo", "Gabriel",
    "Daniel", "Michael", "Joshua", "Paolo", "Rafael", "Miguel", "Ethan", "Liam",
    "Noah", "Lucas", "Alexander", "Benjamin", "Elijah", "James", "Henry", "Sebastian",
    "Matthew", "Adrian", "Kenneth", "Justin", "Jayson", "Jerome", "Neil", "Anthony"
]

FIRST_NAMES_F = [
    "Maria", "Angela", "Bea", "Christine", "Nicole", "Alyssa", "Samantha", "Patricia",
    "Diana", "Katherine", "Andrea", "Sofia", "Chloe", "Emma", "Olivia", "Ava",
    "Isabella", "Mia", "Charlotte", "Harper", "Evelyn", "Abigail", "Emily", "Elizabeth",
    "Camila", "Jasmine", "Stephanie", "Camille", "Rochelle", "Princess", "Angel", "Karen"
]

LAST_NAMES = [
    "Santos", "Reyes", "Cruz", "Bautista", "Ocampo", "Garcia", "Mendoza", "Torres",
    "Ramos", "Flores", "Gonzales", "Lopez", "Hernandez", "Perez", "Sanchez", "Ramirez",
    "Castro", "Rivera", "Morales", "Mercado", "Villanueva", "Aquino", "Del Rosario",
    "Salazar", "Navarro", "Castillo", "Diaz", "Alcantara", "Manalo", "Fernandez",
    "Soriano", "Domingo", "Cortez", "Valencia", "Guerrero", "Valenzuela", "De Leon",
    "De la Cruz", "Tolentino", "Pineda", "Bernardo", "Cabrera", "Dela Rosa", "Pascual"
]

CITIES = [
    "Tupi, South Cotabato",
    "Koronadal City, South Cotabato",
    "General Santos City",
    "Polomolok, South Cotabato",
    "Surallah, South Cotabato",
    "Banga, South Cotabato",
    "Norala, South Cotabato",
    "Tampakan, South Cotabato",
    "Tantangan, South Cotabato",
    "Sto. Nino, South Cotabato",
    "Tacurong City, Sultan Kudarat",
    "Isulan, Sultan Kudarat",
    "Davao City",
    "Digos City, Davao del Sur"
]


# ==============================================================================
# DATABASE CONNECTION HELPER
# ==============================================================================
def get_db_connection(args):
    """Establishes MySQL connection with autocommit disabled for atomic batching."""
    if DRIVER == "mysql.connector":
        conn = mysql.connector.connect(
            host=args.host,
            port=args.port,
            user=args.user,
            password=args.password,
            database=args.db,
            autocommit=False
        )
    else:
        conn = pymysql.connect(
            host=args.host,
            port=args.port,
            user=args.user,
            password=args.password,
            db=args.db,
            autocommit=False
        )
    return conn


def ensure_schema_compatibility(conn):
    """Ensures Cluster column and Status columns exist in database schema."""
    cur = conn.cursor()
    
    # Ensure Cluster column in program
    cur.execute("""
        SELECT COUNT(*) FROM information_schema.columns 
        WHERE table_schema = DATABASE() AND table_name = 'program' AND column_name = 'Cluster'
    """)
    if cur.fetchone()[0] == 0:
        cur.execute("ALTER TABLE `program` ADD COLUMN `Cluster` VARCHAR(150) NULL AFTER `ProgramName`")
    
    # Ensure Status column in student
    cur.execute("""
        SELECT COUNT(*) FROM information_schema.columns 
        WHERE table_schema = DATABASE() AND table_name = 'student' AND column_name = 'Status'
    """)
    if cur.fetchone()[0] == 0:
        cur.execute("ALTER TABLE `student` ADD COLUMN `Status` ENUM('Enrolled','Dropped','Irregular','Graduated') NOT NULL DEFAULT 'Enrolled'")
    
    # Ensure enrollment.Status supports Dropped, Irregular, Graduated
    try:
        cur.execute("ALTER TABLE `enrollment` MODIFY COLUMN `Status` ENUM('Pending','Enrolled','Cancelled','Dropped','Irregular','Graduated') NOT NULL DEFAULT 'Enrolled'")
    except Exception:
        pass

    # Ensure central users table exists (mirroring/supporting standard relational pattern)
    cur.execute("""
        CREATE TABLE IF NOT EXISTS `users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `username` VARCHAR(50) UNIQUE NOT NULL,
            `password_hash` VARCHAR(255) NOT NULL,
            `role` VARCHAR(50) NOT NULL,
            `status` VARCHAR(20) DEFAULT 'Active',
            `student_id` INT NULL,
            `staff_id` INT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX (`username`),
            INDEX (`role`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
    """)

    conn.commit()
    cur.close()


# ==============================================================================
# STEP 1: UPDATE PROGRAMS & CHED DISCIPLINES
# ==============================================================================
def step1_update_programs(conn):
    """
    Truncates program table and inserts all 9 CHED disciplines and 56 degree programs.
    Returns list of inserted ProgramIDs.
    """
    print("\n" + "=" * 80)
    print("STEP 1: UPDATING PROGRAMS TABLE WITH CHED DISCIPLINES")
    print("=" * 80)
    cur = conn.cursor()

    # Disable FK checks to safely truncate program and department
    cur.execute("SET FOREIGN_KEY_CHECKS = 0")
    cur.execute("TRUNCATE TABLE `program`")
    cur.execute("TRUNCATE TABLE `department`")
    cur.execute("SET FOREIGN_KEY_CHECKS = 1")
    conn.commit()
    print(" [x] Truncated `program` and `department` tables.")

    # 1. Insert 9 CHED Cluster Departments
    cluster_to_dept_id = {}
    dept_insert_sql = "INSERT INTO `department` (`DepartmentName`) VALUES (%s)"
    for cluster_name in CHED_CLUSTERS.keys():
        cur.execute(dept_insert_sql, (cluster_name,))
        dept_id = cur.lastrowid
        cluster_to_dept_id[cluster_name] = dept_id

    conn.commit()
    print(f" [x] Seeded {len(cluster_to_dept_id)} CHED Discipline Cluster Departments.")

    # 2. Insert all 56 Degree Programs
    program_insert_sql = "INSERT INTO `program` (`ProgramName`, `Cluster`, `DepartmentID`) VALUES (%s, %s, %s)"
    program_records = []
    
    for cluster_name, program_list in CHED_CLUSTERS.items():
        dept_id = cluster_to_dept_id[cluster_name]
        for prog_name in program_list:
            program_records.append((prog_name, cluster_name, dept_id))

    cur.executemany(program_insert_sql, program_records)
    conn.commit()

    # Retrieve all inserted program IDs
    cur.execute("SELECT `ProgramID`, `ProgramName`, `Cluster` FROM `program` ORDER BY `ProgramID` ASC")
    all_programs = cur.fetchall()
    program_ids = [row[0] for row in all_programs]

    print(f" [x] Successfully inserted {len(program_ids)} CHED Degree Programs across 9 disciplines.")
    for cluster_name, prog_list in CHED_CLUSTERS.items():
        print(f"     * {cluster_name}: {len(prog_list)} programs")

    cur.close()
    return program_ids


# ==============================================================================
# STEP 2: CLEAN EXISTING USERS, STUDENTS & STAFF
# ==============================================================================
def step2_clean_existing_users(conn):
    """Deletes all existing records from users/login, students, and staff tables."""
    print("\n" + "=" * 80)
    print("STEP 2: CLEANING EXISTING USERS, STUDENTS, AND STAFF TABLES")
    print("=" * 80)
    cur = conn.cursor()

    cur.execute("SET FOREIGN_KEY_CHECKS = 0")
    tables_to_clean = [
        "login",
        "users",
        "staff",
        "student",
        "student_profile",
        "enrollment",
        "enrollment_subject",
        "admission",
        "blocking",
        "clearance",
        "clinic",
        "credited_subject",
        "evaluation",
        "evaluation_subject",
        "id_validation",
        "payment",
        "register"
    ]

    for tbl in tables_to_clean:
        try:
            cur.execute(f"TRUNCATE TABLE `{tbl}`")
            print(f" [x] Truncated `{tbl}`")
        except Exception as e:
            print(f" [-] Table `{tbl}` truncate notice: {e}")

    cur.execute("SET FOREIGN_KEY_CHECKS = 1")
    conn.commit()
    cur.close()
    print(" [x] All user, staff, and student records wiped clean. System ready for fresh seeding.")


# ==============================================================================
# STEP 3: GENERATE STAFF (31 RECORDS)
# ==============================================================================
def step3_generate_staff(conn, bcrypt_rounds=4):
    """
    Generates 31 staff members and user credentials:
      - STF-01 to STF-10: Registrar (10)
      - STF-11 to STF-18: Accounting (8)
      - STF-19 to STF-26: Clinic (8)
      - STF-27 to STF-31: Admin (5)
    Password: stff01 to stff31
    """
    print("\n" + "=" * 80)
    print("STEP 3: GENERATING 31 STAFF MEMBERS & USER CREDENTIALS")
    print("=" * 80)
    cur = conn.cursor()

    # Pre-hash staff passwords
    staff_meta = []
    for i in range(1, 32):
        username = f"STF-{i:02d}"
        raw_password = f"stff{i:02d}"
        
        if 1 <= i <= 10:
            role = "Registrar"
            first_name = f"RegistrarStaff{i}"
            dept_id = 1
        elif 11 <= i <= 18:
            role = "Accounting"
            first_name = f"CashierStaff{i}"
            dept_id = 1
        elif 19 <= i <= 26:
            role = "Clinic"
            first_name = f"HealthStaff{i}"
            dept_id = 3
        else:
            role = "Admin"
            first_name = f"AdminStaff{i}"
            dept_id = 4
        
        last_name = role
        email = f"stf{i:02d}@seait.edu.ph"
        contact_no = f"0917{i:07d}"
        
        staff_meta.append({
            "idx": i,
            "username": username,
            "password": raw_password,
            "role": role,
            "first_name": first_name,
            "last_name": last_name,
            "email": email,
            "contact_no": contact_no,
            "dept_id": dept_id
        })

    # Hash passwords in parallel
    print(" [x] Hashing passwords for 31 staff members...")
    def hash_staff_pwd(item):
        pw_hash = bcrypt.hashpw(item["password"].encode("utf-8"), bcrypt.gensalt(rounds=bcrypt_rounds)).decode("utf-8")
        item["hash"] = pw_hash
        return item

    with concurrent.futures.ThreadPoolExecutor(max_workers=8) as executor:
        staff_meta = list(executor.map(hash_staff_pwd, staff_meta))

    # Insert into staff table, login table, and users table
    staff_sql = """
        INSERT INTO `staff` (`LastName`, `FirstName`, `MiddleName`, `Email`, `ContactNo`, `DepartmentID`, `RoleID`)
        VALUES (%s, %s, %s, %s, %s, %s, %s)
    """
    login_sql = """
        INSERT INTO `login` (`Username`, `PasswordHash`, `UserType`, `Status`, `StaffID`, `StudentID`)
        VALUES (%s, %s, 'Staff', 'Active', %s, NULL)
    """
    users_sql = """
        INSERT INTO `users` (`username`, `password_hash`, `role`, `status`, `staff_id`, `student_id`)
        VALUES (%s, %s, %s, 'Active', %s, NULL)
    """

    for item in staff_meta:
        cur.execute(staff_sql, (
            item["last_name"],
            item["first_name"],
            "M.",
            item["email"],
            item["contact_no"],
            item["dept_id"],
            item["role"]
        ))
        staff_id = cur.lastrowid
        cur.execute(login_sql, (item["username"], item["hash"], staff_id))
        cur.execute(users_sql, (item["username"], item["hash"], item["role"], staff_id))

    conn.commit()
    print(" [x] Seeded 31 Staff Members successfully:")
    print("     * STF-01 to STF-10: Registrar   (Password: stff01 to stff10)")
    print("     * STF-11 to STF-18: Accounting  (Password: stff11 to stff18)")
    print("     * STF-19 to STF-26: Clinic      (Password: stff19 to stff26)")
    print("     * STF-27 to STF-31: Admin       (Password: stff27 to stff31)")
    cur.close()


# ==============================================================================
# STEP 4: GENERATE STUDENTS (70,000 RECORDS)
# ==============================================================================
def hash_student_chunk(indices, bcrypt_rounds):
    """Worker function for concurrent password hashing of student passwords."""
    results = []
    for idx in indices:
        pwd = f"student{idx:05d}".encode("utf-8")
        h = bcrypt.hashpw(pwd, bcrypt.gensalt(rounds=bcrypt_rounds)).decode("utf-8")
        results.append((idx, h))
    return results


def step4_generate_students(conn, program_ids, total_students=70000, batch_size=5000, bcrypt_rounds=4):
    """
    Generates 70,000 students using batch insertion:
      - Username: STU-00001 to STU-70000 (zero padded)
      - Password: student00001 to student70000 (bcrypt hashed)
      - Program: Random assignment from inserted CHED programs
      - Status: Random assignment (Enrolled, Dropped, Irregular, Graduated)
    """
    print("\n" + "=" * 80)
    print(f"STEP 4: GENERATING {total_students:,} STUDENTS WITH HIGH-PERFORMANCE BATCH INSERTION")
    print("=" * 80)
    print(f" Configuration: Total = {total_students:,} | Batch Size = {batch_size:,} | Batches = {(total_students + batch_size - 1) // batch_size}")

    start_time = time.time()
    num_batches = (total_students + batch_size - 1) // batch_size
    cur = conn.cursor()

    random.seed(42)

    student_insert_sql = """
        INSERT INTO `student` 
        (`StudentNo`, `LastName`, `FirstName`, `MiddleName`, `BirthDate`, `Sex`, `Address`, `ContactNo`, `Email`, `StudentTypeID`, `ProgramID`, `Status`)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
    """

    profile_insert_sql = """
        INSERT INTO `student_profile`
        (`Address`, `ContactNo`, `GuardianName`, `GuardianContactNo`, `PreviousSchoolName`, `PreviousProgram`, `LastYearLevelCompleted`, `GWA`, `StudentID`)
        VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)
    """

    login_insert_sql = """
        INSERT INTO `login`
        (`Username`, `PasswordHash`, `UserType`, `Status`, `StudentID`, `StaffID`)
        VALUES (%s, %s, 'Student', 'Active', %s, NULL)
    """

    users_insert_sql = """
        INSERT INTO `users`
        (`username`, `password_hash`, `role`, `status`, `student_id`, `staff_id`)
        VALUES (%s, %s, 'Student', 'Active', %s, NULL)
    """

    for b in range(num_batches):
        batch_start_time = time.time()
        b_start_idx = b * batch_size + 1
        b_end_idx = min((b + 1) * batch_size, total_students)
        current_batch_count = b_end_idx - b_start_idx + 1

        # 1. Parallel Password Hashing for current batch
        chunk_sub_size = 250
        chunks = [
            list(range(c_start, min(c_start + chunk_sub_size, b_end_idx + 1)))
            for c_start in range(b_start_idx, b_end_idx + 1, chunk_sub_size)
        ]

        hash_map = {}
        with concurrent.futures.ThreadPoolExecutor(max_workers=8) as executor:
            future_to_chunk = [executor.submit(hash_student_chunk, c, bcrypt_rounds) for c in chunks]
            for future in concurrent.futures.as_completed(future_to_chunk):
                for idx, h in future.result():
                    hash_map[idx] = h

        # 2. Build Student Records
        student_rows = []
        meta_cache = []

        for idx in range(b_start_idx, b_end_idx + 1):
            student_no = f"STU-{idx:05d}"
            sex = random.choice(["Male", "Female"])
            first_name = random.choice(FIRST_NAMES_M if sex == "Male" else FIRST_NAMES_F)
            last_name = random.choice(LAST_NAMES)
            middle_name = random.choice(LAST_NAMES)[0] + "."
            
            # Birthdate between 1999 and 2007
            b_year = random.randint(1999, 2007)
            b_month = random.randint(1, 12)
            b_day = random.randint(1, 28)
            birth_date = date(b_year, b_month, b_day)

            address = random.choice(CITIES)
            contact_no = f"09{random.randint(100000000, 999999999)}"
            email = f"stu{idx:05d}@student.seait.edu.ph"
            student_type_id = random.choices([1, 2, 3], weights=[0.80, 0.15, 0.05])[0]
            program_id = random.choice(program_ids)
            status = random.choices(ENROLLMENT_STATUSES, weights=STATUS_WEIGHTS)[0]

            student_rows.append((
                student_no,
                last_name,
                first_name,
                middle_name,
                birth_date,
                sex,
                address,
                contact_no,
                email,
                student_type_id,
                program_id,
                status
            ))

            meta_cache.append({
                "idx": idx,
                "student_no": student_no,
                "address": address,
                "contact_no": contact_no,
                "status": status,
                "last_name": last_name
            })

        # Insert batch into student table
        cur.executemany(student_insert_sql, student_rows)
        first_student_id = cur.lastrowid

        # 3. Build Profile, Login, Users & Enrollment Records using contiguous IDs
        profile_rows = []
        login_rows = []
        users_rows = []

        for i, meta in enumerate(meta_cache):
            student_id = (first_student_id + i) if (first_student_id and first_student_id > 0) else meta["idx"]
            idx = meta["idx"]
            student_no = meta["student_no"]
            status = meta["status"]
            gwa = round(random.uniform(1.20, 2.75), 2)
            guardian_name = f"{random.choice(FIRST_NAMES_M)} {meta['last_name']}"
            guardian_contact = f"09{random.randint(100000000, 999999999)}"
            pwd_hash = hash_map[idx]

            profile_rows.append((
                meta["address"],
                meta["contact_no"],
                guardian_name,
                guardian_contact,
                "Secondary Senior High School",
                "General Academic Strand",
                "Grade 12 Completed",
                gwa,
                student_id
            ))

            login_rows.append((
                student_no,
                pwd_hash,
                student_id
            ))

            users_rows.append((
                student_no,
                pwd_hash,
                student_id
            ))

        # Bulk inserts for related tables
        cur.executemany(profile_insert_sql, profile_rows)
        cur.executemany(login_insert_sql, login_rows)
        cur.executemany(users_insert_sql, users_rows)

        conn.commit()

        batch_elapsed = time.time() - batch_start_time
        cum_progress = (b_end_idx / total_students) * 100
        print(f" [OK] Batch {b + 1:>2}/{num_batches} | Inserted STU-{b_start_idx:05d} to STU-{b_end_idx:05d} ({b_end_idx:,}/{total_students:,} - {cum_progress:5.1f}%) | {batch_elapsed:.2f}s")

    total_elapsed = time.time() - start_time
    print("-" * 80)
    print(f" [x] {total_students:,} Students Generated & Linked Successfully in {total_elapsed:.2f} seconds ({total_students / total_elapsed:.1f} students/sec)!")
    cur.close()


# ==============================================================================
# MAIN ENTRYPOINT & VERIFICATION
# ==============================================================================
def main():
    parser = argparse.ArgumentParser(description="SEAIT University Database Seeder (CHED Disciplines + 70,000 Students)")
    parser.add_argument("--host", default="localhost", help="MySQL Server Host (default: localhost)")
    parser.add_argument("--port", type=int, default=3306, help="MySQL Server Port (default: 3306)")
    parser.add_argument("--user", default="root", help="MySQL Username (default: root)")
    parser.add_argument("--password", default="", help="MySQL Password (default: empty)")
    parser.add_argument("--db", default="enrollment", help="Database Name (default: enrollment)")
    parser.add_argument("--students", type=int, default=70000, help="Number of students to generate (default: 70,000)")
    parser.add_argument("--batch-size", type=int, default=5000, help="Batch size for bulk insertion (default: 5,000)")
    parser.add_argument("--rounds", type=int, default=4, help="Bcrypt cost factor (default: 4 for high-speed seeding)")
    args = parser.parse_args()

    print("=" * 80)
    print(" SEAIT INTROLLMENT - UNIVERSITY DATABASE SEEDING ENGINE")
    print(f" Driver: {DRIVER} | Python {sys.version.split()[0]} | Database: {args.db}")
    print("=" * 80)

    overall_start = time.time()

    # 1. Connect
    print(f"[*] Connecting to MySQL at {args.host}:{args.port}...")
    conn = get_db_connection(args)
    print(" [OK] Connected successfully.")

    # 2. Schema check
    ensure_schema_compatibility(conn)

    # 3. Step 1: Update Programs
    program_ids = step1_update_programs(conn)

    # 4. Step 2: Clean Existing Users
    step2_clean_existing_users(conn)

    # 5. Step 3: Generate 31 Staff Members
    step3_generate_staff(conn, bcrypt_rounds=args.rounds)

    # 6. Step 4: Generate 70,000 Students
    step4_generate_students(conn, program_ids, total_students=args.students, batch_size=args.batch_size, bcrypt_rounds=args.rounds)

    # 7. Final Verification
    print("\n" + "=" * 80)
    print("FINAL SEEDING VERIFICATION & AUDIT")
    print("=" * 80)
    cur = conn.cursor()

    cur.execute("SELECT COUNT(*) FROM `department`")
    dept_cnt = cur.fetchone()[0]

    cur.execute("SELECT COUNT(*) FROM `program`")
    prog_cnt = cur.fetchone()[0]

    cur.execute("SELECT COUNT(*) FROM `staff`")
    staff_cnt = cur.fetchone()[0]

    cur.execute("SELECT COUNT(*) FROM `student`")
    student_cnt = cur.fetchone()[0]

    cur.execute("SELECT COUNT(*) FROM `login` WHERE `UserType` = 'Staff'")
    staff_login_cnt = cur.fetchone()[0]

    cur.execute("SELECT COUNT(*) FROM `login` WHERE `UserType` = 'Student'")
    student_login_cnt = cur.fetchone()[0]

    cur.execute("SELECT COUNT(*) FROM `users`")
    users_cnt = cur.fetchone()[0]

    cur.execute("SELECT `Status`, COUNT(*) FROM `student` GROUP BY `Status`")
    status_distribution = cur.fetchall()

    print(f" [OK] CHED Departments / Clusters: {dept_cnt}")
    print(f" [OK] CHED Academic Programs:     {prog_cnt}")
    print(f" [OK] Staff Records Created:       {staff_cnt} (Logins: {staff_login_cnt})")
    print(f" [OK] Student Records Created:     {student_cnt:,} (Logins: {student_login_cnt:,})")
    print(f" [OK] Total Central Users Table:   {users_cnt:,}")
    print("\n Student Enrollment Status Distribution:")
    for status, count in status_distribution:
        pct = (count / student_cnt) * 100 if student_cnt else 0
        print(f"     - {status:<12}: {count:>6,} ({pct:5.1f}%)")

    print(f"\n Credential Sanity Check:")
    print("   * Staff Login Example:   Username: STF-01   | Password: stff01   | Role: Registrar")
    print("   * Staff Login Example:   Username: STF-11   | Password: stff11   | Role: Accounting")
    print("   * Staff Login Example:   Username: STF-19   | Password: stff19   | Role: Clinic")
    print("   * Staff Login Example:   Username: STF-27   | Password: stff27   | Role: Admin")
    print("   * Student Login Example: Username: STU-00001| Password: student00001 | Status: Active")
    print("   * Student Login Example: Username: STU-70000| Password: student70000 | Status: Active")

    cur.close()
    conn.close()

    total_time = time.time() - overall_start
    print("=" * 80)
    print(f" ALL 4 SEEDING STEPS COMPLETED IN {total_time:.2f} SECONDS ({total_time/60:.2f} MIN)!")
    print("=" * 80)


if __name__ == "__main__":
    main()
