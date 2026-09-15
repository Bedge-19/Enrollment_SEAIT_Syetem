-- Enrollment System Database
-- 22 table structures only
-- Data/INSERT statements removed

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

SET NAMES utf8mb4;

CREATE TABLE `admission` (
  `AdmissionID` int(11) NOT NULL,
  `SubmissionDate` date NOT NULL,
  `ApprovalDate` date NOT NULL,
  `Status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `Remarks` text DEFAULT NULL,
  `StudentID` int(11) NOT NULL,
  `StaffID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `blocking` (
  `BlockingID` int(11) NOT NULL,
  `BlockingDate` date NOT NULL,
  `EnrollmentID` int(11) NOT NULL,
  `SectionID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `clearance` (
  `ClearanceID` int(11) NOT NULL,
  `ClearanceDate` date NOT NULL,
  `Status` enum('Pending','Cleared','Not Cleared') NOT NULL DEFAULT 'Pending',
  `Remarks` text DEFAULT NULL,
  `StudentID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `clinic` (
  `ClinicID` int(11) NOT NULL,
  `CompletionDate` date NOT NULL,
  `Status` enum('Pending','Completed','Failed') NOT NULL DEFAULT 'Pending',
  `Remarks` text DEFAULT NULL,
  `StudentID` int(11) NOT NULL,
  `StaffID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `credited_subject` (
  `CreditID` int(11) DEFAULT NULL,
  `Status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `Remarks` text DEFAULT NULL,
  `StudentID` int(11) NOT NULL,
  `SubjectID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `department` (
  `departmentID` int(11) NOT NULL,
  `DepartmentName` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `enrollment` (
  `EnrollmentID` int(11) NOT NULL,
  `EnrollmentDate` date NOT NULL,
  `SchoolYear` varchar(9) NOT NULL,
  `Semester` enum('1st','2nd','Summer') NOT NULL,
  `Status` enum('Pending','Enrolled','Cancelled') NOT NULL DEFAULT 'Pending',
  `StudentID` int(11) NOT NULL,
  `StaffID` int(11) NOT NULL,
  `EvaluationID` int(11) NOT NULL,
  `PaymentID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `enrollment_subject` (
  `EnrollmentSubjectID` int(11) NOT NULL,
  `EnrollmentID` int(11) NOT NULL,
  `SubjectID` int(11) NOT NULL,
  `SectionID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `evaluation` (
  `EvaluationID` int(11) NOT NULL,
  `EvaluationDate` date NOT NULL,
  `Remarks` text DEFAULT NULL,
  `Status` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `StudentID` int(11) NOT NULL,
  `StaffID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `evaluation_subject` (
  `EvaluationSubjectID` int(11) NOT NULL,
  `SubjectStatus` enum('Approved','Credited','Pending') NOT NULL DEFAULT 'Pending',
  `EvaluationID` int(11) NOT NULL,
  `SubjectID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `id_validation` (
  `ValidationID` int(11) NOT NULL,
  `ValidationDate` date NOT NULL,
  `Status` enum('Pending','Completed','Failed') NOT NULL DEFAULT 'Pending',
  `StudentID` int(11) NOT NULL,
  `StaffID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `login` (
  `LoginID` int(11) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `PasswordHash` varchar(255) NOT NULL,
  `UserType` enum('Student','Staff') NOT NULL,
  `Status` enum('Active','Inactive') NOT NULL,
  `LastLogin` datetime DEFAULT NULL,
  `StudentID` int(11) NOT NULL,
  `StaffID` int(11) NOT NULL,
  `MustChangePassword` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `payment` (
  `PaymentID` int(11) NOT NULL,
  `Amount` decimal(10,2) NOT NULL,
  `PaymentDate` date NOT NULL,
  `ReceiptNo` varchar(30) NOT NULL,
  `PaymentStatus` enum('Pending','Paid','Cancelled') NOT NULL DEFAULT 'Pending',
  `StudentID` int(11) NOT NULL,
  `StaffID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `program` (
  `ProgramID` int(11) NOT NULL,
  `ProgramName` varchar(100) NOT NULL,
  `DepartmentID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `register` (
  `RegisterID` int(11) NOT NULL,
  `RegistrationDate` datetime NOT NULL,
  `RegistrationStatus` enum('Pending','Approved','Rejected') NOT NULL,
  `RegisteredBy` int(11) DEFAULT NULL,
  `StudentID` int(11) NOT NULL,
  `StaffID` int(11) NOT NULL,
  `LoginID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `schedule` (
  `ScheduleID` int(11) NOT NULL,
  `Day` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `TimeStart` time NOT NULL,
  `TimeEnd` time NOT NULL,
  `SectionID` int(11) NOT NULL,
  `SubjectID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `section` (
  `SectionID` int(11) NOT NULL,
  `SectionCode` varchar(20) NOT NULL,
  `YearLevel` tinyint(4) NOT NULL,
  `ProgramID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `staff` (
  `StaffID` int(11) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `MiddleName` varchar(50) NOT NULL,
  `Email` varchar(50) NOT NULL,
  `ContactNo` varchar(15) NOT NULL,
  `DepartmentID` int(11) NOT NULL,
  `RoleID` enum('Registrar','Dean','Accounting','Clinic','Admin') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `student` (
  `StudentID` int(11) NOT NULL,
  `StudentNo` varchar(50) NOT NULL,
  `LastName` varchar(50) NOT NULL,
  `FirstName` varchar(50) NOT NULL,
  `MiddleName` varchar(50) DEFAULT NULL,
  `BirthDate` date NOT NULL,
  `Sex` enum('Male','Female') NOT NULL,
  `Address` varchar(255) NOT NULL,
  `ContactNo` varchar(15) NOT NULL,
  `Email` varchar(100) NOT NULL,
  `StudentTypeID` int(11) NOT NULL,
  `ProgramID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `student_profile` (
  `ProfileID` int(11) NOT NULL,
  `Address` varchar(255) NOT NULL,
  `ContactNo` varchar(15) NOT NULL,
  `GuardianName` varchar(100) NOT NULL,
  `GuardianContactNo` varchar(15) NOT NULL,
  `PreviousSchoolName` varchar(255) NOT NULL,
  `PreviousProgram` varchar(100) NOT NULL,
  `LastYearLevelCompleted` varchar(20) NOT NULL,
  `GWA` decimal(4,2) NOT NULL,
  `StudentID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `student_type` (
  `StudentTypeID` int(11) NOT NULL,
  `TypeName` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `subject` (
  `SubjectID` int(11) NOT NULL,
  `SubjectCode` varchar(20) NOT NULL,
  `SubjectTitle` varchar(100) NOT NULL,
  `Units` decimal(3,1) NOT NULL DEFAULT 0.0,
  `ProgramID` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;
