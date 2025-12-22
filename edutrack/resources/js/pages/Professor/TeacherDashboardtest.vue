<script setup lang="ts">
import { ref, computed, onMounted, reactive } from 'vue'

// State variables
const sidebarOpen = ref(false)
const darkMode = ref(false)
const activePage = ref('dashboard')
const currentDate = ref(new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }))
const showSectionDropdown = ref(false)
const currentSection = ref<any>(null)
const selectedAssignment = ref(null)
const showAddClassModal = ref(false)
const showAddStudentModal = ref(false)
const showScheduleModal = ref(false)

// Form states
const newClass = reactive({
  id: '',
  name: '',
  icon: '📚',
  term: 'Fall Semester 2023',
  alerts: 0,
  studentCount: 0,
  presentCount: 0,
  pendingAssignments: 0,
  toGradeCount: 0,
  averageScore: 0,
  schedule: [],
  performance: {
    quiz1: 0,
    midterm: 0,
    project: 0,
    quiz2: 0
  },
  gradeDistribution: {
    a: 0,
    b: 0,
    c: 0,
    d: 0,
    f: 0
  },
  attendanceSummary: {
    present: 0,
    absent: 0,
    late: 0,
    excused: 0
  },
  behaviorSummary: {
    positive: 0,
    warnings: 0,
    incidents: 0
  },
  assignments: [],
  students: [],
  recentActivities: [],
  behaviorReports: []
})

const newStudent = reactive({
  id: '',
  name: '',
  email: '',
  status: 'present',
  notes: '',
  score: 0,
  gradeComment: ''
})

const newScheduleItem = reactive({
  day: 'Monday',
  time: '9:00 AM - 10:30 AM',
  room: 'Room 101',
  status: 'Active',
  statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
})

// Available icons for class selection
// const availableIcons = ['📚', '🧪', '🧬', '⚗️', '🔬', '📝', '🧮', '🎨', '🎭', '🏛️', '🌍', '📊', '💻', '🎵', '📖']
const availableIcons = [
  // General & Education
  '📚', // General studies
  '📝', // Writing/Exams
  '📖', // Literature/Reading

  // Science & Technology
  '🧪', // Science
  '🔬', // Research/Laboratory
  '🧬', // Biology
  '⚗️', // Chemistry
  '🌍', // Environmental Science/Geography
  '🦠', // Microbiology
  '📡', // Telecommunications/Engineering
  '🚀', // Aerospace Engineering

  // **Programming, IT & Computer Science**
  '💻', // General IT/Computer Science
  '🖥️', // Software Development/Programming
  '⌨️', // Coding/Typing
  '📟', // Embedded Systems/Hardware
  '🗄️', // Databases/Data Science
  '🧑‍💻', // Cybersecurity/Hacking
  '📊', // Data Analytics/AI
  '📡', // Networking
  '🖲️', // Computer Hardware/Peripherals
  '⚙️', // System Administration/DevOps
  '📁', // File Management/Cloud Computing

  // **Accountancy & Business Administration**
  '💰', // Finance/Accounting
  '📈', // Business/Economics/Investment
  '📉', // Risk Management
  '📝', // Reports/Auditing
  '🔖', // Business Law/Policies
  '🏦', // Banking
  '🏛️', // Corporate Management
  '💳', // Commerce/Marketing
  '🗂️', // Documentation/Record-Keeping
  '📑', // Invoices/Reports
  '📠', // Office Work/Admin

  // **Criminology & Law Enforcement**
  '⚖️', // Law
  '🔎', // Investigation/Forensics
  '🕵️‍♂️', // Detective/Intelligence
  '🚔', // Law Enforcement
  '👮‍♂️', // Police Studies
  '🩸', // Forensic Science
  '🔫', // Firearms Training
  '🚨', // Emergency Response
  '🏛️', // Criminal Justice
  '🔐', // Security/Prison Management
  '🧑‍⚖️', // Legal Studies/Judiciary
  '📜', // Evidence/Laws/Legislation
  '🛡️', // Homeland Security

  // Engineering & Technical Fields
  '🏗️', // Civil Engineering
  '🛠️', // Mechanical Engineering
  '🔋', // Electrical Engineering
  '📡', // Electronics/Communication
  '⚙️', // Industrial Engineering
  '🛩️', // Aviation
  '📷', // Photography/Multimedia
  '🎥', // Film/Cinematography
  '🎙️', // Communication/Journalism
];


// Navigation items
const navItems = ref([
  { id: 'dashboard', name: 'Dashboard', icon: '📊' },
  { id: 'attendance', name: 'Attendance', icon: '✅' },
  { id: 'grades', name: 'Grades', icon: '🏆' },
  { id: 'behavior', name: 'Behavior Reports', icon: '📜' },
  { id: 'students', name: 'Students', icon: '👨‍🎓' }, // Added new students tab
])

// Class sections data
const classSections = ref([
  {
    id: 'sci101a',
    name: 'Science 101 - Section A',
    icon: '🧪',
    term: 'Fall Semester 2023',
    alerts: 2,
    studentCount: 28,
    presentCount: 26,
    pendingAssignments: 3,
    toGradeCount: 12,
    averageScore: 87,
    schedule: [
      { day: 'Monday', time: '9:00 AM - 10:30 AM', room: 'Science Lab 101', status: 'Active', statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' },
      { day: 'Wednesday', time: '9:00 AM - 10:30 AM', room: 'Science Lab 101', status: 'Active', statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' },
      { day: 'Friday', time: '9:00 AM - 10:30 AM', room: 'Room 203', status: 'Active', statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' }
    ],
    performance: {
      quiz1: 82,
      midterm: 78,
      project: 91,
      quiz2: 85
    },
    gradeDistribution: {
      a: 60,
      b: 80,
      c: 70,
      d: 40,
      f: 30
    },
    attendanceSummary: {
      present: 26,
      absent: 1,
      late: 1,
      excused: 0
    },
    behaviorSummary: {
      positive: 12,
      warnings: 5,
      incidents: 2
    },
    assignments: [
      { id: 'midterm', name: 'Midterm Exam' },
      { id: 'quiz1', name: 'Quiz 1' },
      { id: 'lab', name: 'Lab Report' },
      { id: 'project', name: 'Final Project' }
    ],
    students: [
      { id: '2023001', name: 'Emma Thompson', email: 'emma.t@school.edu', status: 'present', notes: '', score: 92, gradeComment: 'Excellent work!' },
      { id: '2023002', name: 'Liam Johnson', email: 'liam.j@school.edu', status: 'present', notes: '', score: 85, gradeComment: 'Good effort' },
      { id: '2023003', name: 'Olivia Davis', email: 'olivia.d@school.edu', status: 'present', notes: '', score: 78, gradeComment: 'Needs improvement in section 3' },
      { id: '2023004', name: 'Noah Wilson', email: 'noah.w@school.edu', status: 'absent', notes: 'Medical appointment', score: 0, gradeComment: 'Absent - needs to make up exam' },
      { id: '2023005', name: 'Ava Martinez', email: 'ava.m@school.edu', status: 'present', notes: '', score: 95, gradeComment: 'Outstanding performance!' },
      { id: '2023006', name: 'Ethan Brown', email: 'ethan.b@school.edu', status: 'present', notes: '', score: 82, gradeComment: '' },
      { id: '2023007', name: 'Sophia Taylor', email: 'sophia.t@school.edu', status: 'present', notes: '', score: 88, gradeComment: '' },
      { id: '2023008', name: 'Mason Anderson', email: 'mason.a@school.edu', status: 'late', notes: 'Arrived 15 minutes late', score: 79, gradeComment: 'Good work but needs more detail' },
      { id: '2023009', name: 'Isabella Thomas', email: 'isabella.t@school.edu', status: 'present', notes: '', score: 91, gradeComment: '' },
      { id: '2023010', name: 'James Jackson', email: 'james.j@school.edu', status: 'present', notes: '', score: 84, gradeComment: '' },
    ],
    recentActivities: [
      {
        icon: '📝',
        iconBg: 'bg-blue-100 dark:bg-blue-900',
        iconColor: 'text-blue-500',
        title: 'Graded Midterm Exams',
        description: 'You graded 28 midterm exams with an average score of 87%.',
        time: '2h ago'
      },
      {
        icon: '📚',
        iconBg: 'bg-green-100 dark:bg-green-900',
        iconColor: 'text-green-500',
        title: 'New Assignment Created',
        description: 'You created a new lab assignment due in 2 weeks.',
        time: '5h ago'
      },
      {
        icon: '🏆',
        iconBg: 'bg-yellow-100 dark:bg-yellow-900',
        iconColor: 'text-yellow-500',
        title: 'Student Achievement',
        description: 'Emma Thompson received the highest score on the midterm exam.',
        time: '1d ago'
      },
      {
        icon: '📢',
        iconBg: 'bg-purple-100 dark:bg-purple-900',
        iconColor: 'text-purple-500',
        title: 'Department Meeting',
        description: 'Science department meeting scheduled for tomorrow at 3 PM.',
        time: '2d ago'
      },
    ],
    behaviorReports: [
      {
        student: 'Emma Thompson',
        icon: '🌟',
        iconBg: 'bg-green-100 dark:bg-green-900',
        iconColor: 'text-green-600 dark:text-green-400',
        type: 'Positive',
        typeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        description: 'Emma has been consistently participating in class discussions and providing thoughtful insights.',
        date: 'Oct 10, 2023'
      },
      {
        student: 'Noah Wilson',
        icon: '⚠️',
        iconBg: 'bg-yellow-100 dark:bg-yellow-900',
        iconColor: 'text-yellow-600 dark:text-yellow-400',
        type: 'Warning',
        typeClass: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        description: 'Noah did not complete the assigned homework for today\'s class.',
        date: 'Oct 5, 2023'
      },
      {
        student: 'Ava Martinez',
        icon: '🏆',
        iconBg: 'bg-blue-100 dark:bg-blue-900',
        iconColor: 'text-blue-600 dark:text-blue-400',
        type: 'Positive',
        typeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        description: 'Ava\'s science project was selected as one of the top three in the class.',
        date: 'Sep 28, 2023'
      },
      {
        student: 'Liam Johnson',
        icon: '❌',
        iconBg: 'bg-red-100 dark:bg-red-900',
        iconColor: 'text-red-600 dark:text-red-400',
        type: 'Incident',
        typeClass: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        description: 'Liam was disruptive during the lab session and refused to follow safety protocols.',
        date: 'Sep 22, 2023'
      },
    ]
  },
  {
    id: 'sci101b',
    name: 'Science 101 - Section B',
    icon: '🧪',
    term: 'Fall Semester 2023',
    alerts: 0,
    studentCount: 25,
    presentCount: 23,
    pendingAssignments: 2,
    toGradeCount: 8,
    averageScore: 82,
    schedule: [
      { day: 'Monday', time: '11:00 AM - 12:30 PM', room: 'Science Lab 101', status: 'Active', statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' },
      { day: 'Wednesday', time: '11:00 AM - 12:30 PM', room: 'Science Lab 101', status: 'Active', statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' },
      { day: 'Friday', time: '11:00 AM - 12:30 PM', room: 'Room 203', status: 'Active', statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' }
    ],
    performance: {
      quiz1: 78,
      midterm: 75,
      project: 88,
      quiz2: 80
    },
    gradeDistribution: {
      a: 50,
      b: 70,
      c: 80,
      d: 50,
      f: 40
    },
    attendanceSummary: {
      present: 23,
      absent: 1,
      late: 1,
      excused: 0
    },
    behaviorSummary: {
      positive: 8,
      warnings: 3,
      incidents: 1
    },
    assignments: [
      { id: 'midterm', name: 'Midterm Exam' },
      { id: 'quiz1', name: 'Quiz 1' },
      { id: 'lab', name: 'Lab Report' },
      { id: 'project', name: 'Final Project' }
    ],
    students: [
      { id: '2023011', name: 'William Garcia', email: 'william.g@school.edu', status: 'present', notes: '', score: 88, gradeComment: 'Good work!' },
      { id: '2023012', name: 'Charlotte Rodriguez', email: 'charlotte.r@school.edu', status: 'present', notes: '', score: 79, gradeComment: 'Needs improvement' },
      { id: '2023013', name: 'Benjamin Lee', email: 'benjamin.l@school.edu', status: 'present', notes: '', score: 92, gradeComment: 'Excellent work!' },
      { id: '2023014', name: 'Amelia Hernandez', email: 'amelia.h@school.edu', status: 'absent', notes: 'Family emergency', score: 0, gradeComment: 'Absent - needs to make up exam' },
      { id: '2023015', name: 'Lucas Gonzalez', email: 'lucas.g@school.edu', status: 'present', notes: '', score: 85, gradeComment: 'Good effort' },
      { id: '2023016', name: 'Mia Wilson', email: 'mia.w@school.edu', status: 'present', notes: '', score: 77, gradeComment: 'Needs to focus more on section 2' },
      { id: '2023017', name: 'Henry Lopez', email: 'henry.l@school.edu', status: 'present', notes: '', score: 81, gradeComment: '' },
      { id: '2023018', name: 'Evelyn Clark', email: 'evelyn.c@school.edu', status: 'late', notes: 'Bus delay', score: 83, gradeComment: '' },
      { id: '2023019', name: 'Alexander Lewis', email: 'alexander.l@school.edu', status: 'present', notes: '', score: 90, gradeComment: 'Very thorough work' },
      { id: '2023020', name: 'Harper Young', email: 'harper.y@school.edu', status: 'present', notes: '', score: 76, gradeComment: 'Needs to improve lab technique' },
    ],
    recentActivities: [
      {
        icon: '📝',
        iconBg: 'bg-blue-100 dark:bg-blue-900',
        iconColor: 'text-blue-500',
        title: 'Quiz 2 Scheduled',
        description: 'Quiz 2 has been scheduled for next Friday.',
        time: '3h ago'
      },
      {
        icon: '📚',
        iconBg: 'bg-green-100 dark:bg-green-900',
        iconColor: 'text-green-500',
        title: 'Lab Reports Graded',
        description: 'You graded 25 lab reports with an average score of 82%.',
        time: '1d ago'
      },
      {
        icon: '🏆',
        iconBg: 'bg-yellow-100 dark:bg-yellow-900',
        iconColor: 'text-yellow-500',
        title: 'Student Achievement',
        description: 'Benjamin Lee received the highest score on the lab report.',
        time: '2d ago'
      },
      {
        icon: '📢',
        iconBg: 'bg-purple-100 dark:bg-purple-900',
        iconColor: 'text-purple-500',
        title: 'Field Trip',
        description: 'Field trip to the Science Museum scheduled for next month.',
        time: '3d ago'
      },
    ],
    behaviorReports: [
      {
        student: 'Benjamin Lee',
        icon: '🌟',
        iconBg: 'bg-green-100 dark:bg-green-900',
        iconColor: 'text-green-600 dark:text-green-400',
        type: 'Positive',
        typeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        description: 'Benjamin helped other students understand difficult concepts during the lab session.',
        date: 'Oct 8, 2023'
      },
      {
        student: 'Charlotte Rodriguez',
        icon: '⚠️',
        iconBg: 'bg-yellow-100 dark:bg-yellow-900',
        iconColor: 'text-yellow-600 dark:text-yellow-400',
        type: 'Warning',
        typeClass: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        description: 'Charlotte was using her phone during class despite multiple reminders.',
        date: 'Oct 3, 2023'
      },
      {
        student: 'Harper Young',
        icon: '❌',
        iconBg: 'bg-red-100 dark:bg-red-900',
        iconColor: 'text-red-600 dark:text-red-400',
        type: 'Incident',
        typeClass: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        description: 'Harper mishandled lab equipment, causing minor damage.',
        date: 'Sep 25, 2023'
      },
    ]
  },
  {
    id: 'bio202',
    name: 'Biology 202',
    icon: '🧬',
    term: 'Fall Semester 2023',
    alerts: 1,
    studentCount: 22,
    presentCount: 20,
    pendingAssignments: 1,
    toGradeCount: 5,
    averageScore: 84,
    schedule: [
      { day: 'Tuesday', time: '1:00 PM - 2:30 PM', room: 'Biology Lab 202', status: 'Active', statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' },
      { day: 'Thursday', time: '1:00 PM - 2:30 PM', room: 'Biology Lab 202', status: 'Active', statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' }
    ],
    performance: {
      quiz1: 80,
      midterm: 84,
      project: 87,
      quiz2: 82
    },
    gradeDistribution: {
      a: 55,
      b: 75,
      c: 60,
      d: 35,
      f: 25
    },
    attendanceSummary: {
      present: 20,
      absent: 1,
      late: 0,
      excused: 1
    },
    behaviorSummary: {
      positive: 10,
      warnings: 2,
      incidents: 0
    },
    assignments: [
      { id: 'midterm', name: 'Midterm Exam' },
      { id: 'quiz1', name: 'Quiz 1' },
      { id: 'dissection', name: 'Dissection Lab' },
      { id: 'project', name: 'Research Project' }
    ],
    students: [
      { id: '2023021', name: 'Daniel Moore', email: 'daniel.m@school.edu', status: 'present', notes: '', score: 88, gradeComment: 'Good work!' },
      { id: '2023022', name: 'Scarlett White', email: 'scarlett.w@school.edu', status: 'present', notes: '', score: 92, gradeComment: 'Excellent analysis' },
      { id: '2023023', name: 'Michael Harris', email: 'michael.h@school.edu', status: 'present', notes: '', score: 79, gradeComment: 'Needs more detail in responses' },
      { id: '2023024', name: 'Victoria Martin', email: 'victoria.m@school.edu', status: 'excused', notes: 'Doctor appointment', score: 0, gradeComment: 'Excused absence - will make up exam' },
      { id: '2023025', name: 'Joseph Thompson', email: 'joseph.t@school.edu', status: 'present', notes: '', score: 85, gradeComment: 'Good understanding of concepts' },
      { id: '2023026', name: 'Penelope Garcia', email: 'penelope.g@school.edu', status: 'present', notes: '', score: 90, gradeComment: 'Excellent work!' },
      { id: '2023027', name: 'David Martinez', email: 'david.m@school.edu', status: 'present', notes: '', score: 77, gradeComment: 'Needs improvement in section 3' },
      { id: '2023028', name: 'Elizabeth Robinson', email: 'elizabeth.r@school.edu', status: 'absent', notes: 'No notification', score: 0, gradeComment: 'Absent - needs to make up exam' },
    ],
    recentActivities: [
      {
        icon: '🔬',
        iconBg: 'bg-blue-100 dark:bg-blue-900',
        iconColor: 'text-blue-500',
        title: 'Lab Equipment Updated',
        description: 'New microscopes have been installed in the Biology lab.',
        time: '1d ago'
      },
      {
        icon: '📚',
        iconBg: 'bg-green-100 dark:bg-green-900',
        iconColor: 'text-green-500',
        title: 'Research Projects Assigned',
        description: 'Research project topics have been assigned to all students.',
        time: '2d ago'
      },
      {
        icon: '🏆',
        iconBg: 'bg-yellow-100 dark:bg-yellow-900',
        iconColor: 'text-yellow-500',
        title: 'Student Achievement',
        description: 'Scarlett White won the regional science fair with her biology project.',
        time: '4d ago'
      },
      {
        icon: '📢',
        iconBg: 'bg-purple-100 dark:bg-purple-900',
        iconColor: 'text-purple-500',
        title: 'Guest Speaker',
        description: 'Dr. Johnson from State University will be giving a lecture next week.',
        time: '5d ago'
      },
    ],
    behaviorReports: [
      {
        student: 'Scarlett White',
        icon: '🌟',
        iconBg: 'bg-green-100 dark:bg-green-900',
        iconColor: 'text-green-600 dark:text-green-400',
        type: 'Positive',
        typeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        description: 'Scarlett has been exceptionally helpful during lab sessions and assists other students.',
        date: 'Oct 7, 2023'
      },
      {
        student: 'Michael Harris',
        icon: '⚠️',
        iconBg: 'bg-yellow-100 dark:bg-yellow-900',
        iconColor: 'text-yellow-600 dark:text-yellow-400',
        type: 'Warning',
        typeClass: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        description: 'Michael has been consistently late in submitting assignments.',
        date: 'Oct 2, 2023'
      },
    ]
  },
  {
    id: 'chem101',
    name: 'Chemistry 101',
    icon: '⚗️',
    term: 'Fall Semester 2023',
    alerts: 0,
    studentCount: 24,
    presentCount: 22,
    pendingAssignments: 2,
    toGradeCount: 10,
    averageScore: 81,
    schedule: [
      { day: 'Tuesday', time: '9:00 AM - 10:30 AM', room: 'Chemistry Lab 101', status: 'Active', statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' },
      { day: 'Thursday', time: '9:00 AM - 10:30 AM', room: 'Chemistry Lab 101', status: 'Active', statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' }
    ],
    performance: {
      quiz1: 76,
      midterm: 81,
      project: 85,
      quiz2: 79
    },
    gradeDistribution: {
      a: 45,
      b: 65,
      c: 75,
      d: 45,
      f: 35
    },
    attendanceSummary: {
      present: 22,
      absent: 1,
      late: 1,
      excused: 0
    },
    behaviorSummary: {
      positive: 7,
      warnings: 4,
      incidents: 1
    },
    assignments: [
      { id: 'midterm', name: 'Midterm Exam' },
      { id: 'quiz1', name: 'Quiz 1' },
      { id: 'lab', name: 'Chemical Reactions Lab' },
      { id: 'project', name: 'Element Research Project' }
    ],
    students: [
      { id: '2023031', name: 'Samuel Walker', email: 'samuel.w@school.edu', status: 'present', notes: '', score: 83, gradeComment: 'Good work!' },
      { id: '2023032', name: 'Grace Allen', email: 'grace.a@school.edu', status: 'present', notes: '', score: 77, gradeComment: 'Needs improvement in calculations' },
      { id: '2023033', name: 'Andrew Scott', email: 'andrew.s@school.edu', status: 'present', notes: '', score: 91, gradeComment: 'Excellent lab work!' },
      { id: '2023034', name: 'Zoe Green', email: 'zoe.g@school.edu', status: 'late', notes: 'Traffic delay', score: 75, gradeComment: 'Needs to review chapter 4' },
      { id: '2023035', name: 'Christopher Baker', email: 'christopher.b@school.edu', status: 'present', notes: '', score: 88, gradeComment: 'Good understanding of concepts' },
      { id: '2023036', name: 'Lily Nelson', email: 'lily.n@school.edu', status: 'present', notes: '', score: 79, gradeComment: '' },
      { id: '2023037', name: 'Matthew Carter', email: 'matthew.c@school.edu', status: 'present', notes: '', score: 82, gradeComment: '' },
      { id: '2023038', name: 'Chloe Mitchell', email: 'chloe.m@school.edu', status: 'absent', notes: 'Sick', score: 0, gradeComment: 'Absent - needs to make up exam' },
    ],
    recentActivities: [
      {
        icon: '🧪',
        iconBg: 'bg-blue-100 dark:bg-blue-900',
        iconColor: 'text-blue-500',
        title: 'Lab Safety Review',
        description: 'Conducted a lab safety review session for all students.',
        time: '6h ago'
      },
      {
        icon: '📚',
        iconBg: 'bg-green-100 dark:bg-green-900',
        iconColor: 'text-green-500',
        title: 'Quiz 2 Graded',
        description: 'Quiz 2 has been graded with an average score of 79%.',
        time: '1d ago'
      },
      {
        icon: '🏆',
        iconBg: 'bg-yellow-100 dark:bg-yellow-900',
        iconColor: 'text-yellow-500',
        title: 'Student Achievement',
        description: 'Andrew Scott received the highest score on the chemical reactions lab.',
        time: '3d ago'
      },
      {
        icon: '📢',
        iconBg: 'bg-purple-100 dark:bg-purple-900',
        iconColor: 'text-purple-500',
        title: 'Lab Equipment',
        description: 'New safety equipment has been installed in the Chemistry lab.',
        time: '4d ago'
      },
    ],
    behaviorReports: [
      {
        student: 'Andrew Scott',
        icon: '🌟',
        iconBg: 'bg-green-100 dark:bg-green-900',
        iconColor: 'text-green-600 dark:text-green-400',
        type: 'Positive',
        typeClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        description: 'Andrew demonstrated excellent lab technique and helped maintain safety standards.',
        date: 'Oct 5, 2023'
      },
      {
        student: 'Grace Allen',
        icon: '⚠️',
        iconBg: 'bg-yellow-100 dark:bg-yellow-900',
        iconColor: 'text-yellow-600 dark:text-yellow-400',
        type: 'Warning',
        typeClass: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        description: 'Grace needs to be more careful with lab equipment and follow procedures more closely.',
        date: 'Oct 1, 2023'
      },
      {
        student: 'Zoe Green',
        icon: '❌',
        iconBg: 'bg-red-100 dark:bg-red-900',
        iconColor: 'text-red-600 dark:text-red-400',
        type: 'Incident',
        typeClass: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        description: 'Zoe caused a minor chemical spill by not following proper procedures.',
        date: 'Sep 24, 2023'
      },
    ]
  }
])

// Computed properties
const currentPage = computed(() => {
  return navItems.value.find(item => item.id === activePage.value) || navItems.value[0]
})

// Methods
function toggleSidebar() {
  sidebarOpen.value = !sidebarOpen.value
}

function toggleDarkMode() {
  darkMode.value = !darkMode.value
  if (darkMode.value) {
    document.documentElement.classList.add('dark')
  } else {
    document.documentElement.classList.remove('dark')
  }
}

function selectSection(section) {
  currentSection.value = section
  selectedAssignment.value = section.assignments[0]?.id || null
}

function getLetterGrade(score) {
  if (score >= 90) return 'A'
  if (score >= 80) return 'B'
  if (score >= 70) return 'C'
  if (score >= 60) return 'D'
  return 'F'
}

function getGradeClass(score) {
  if (score >= 90) return 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
  if (score >= 80) return 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'
  if (score >= 70) return 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300'
  if (score >= 60) return 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300'
  return 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300'
}

// New methods for adding classes, students, and schedules
function openAddClassModal() {
  // Reset form
  Object.assign(newClass, {
    id: generateId(),
    name: '',
    icon: '📚',
    term: 'Fall Semester 2023',
    alerts: 0,
    studentCount: 0,
    presentCount: 0,
    pendingAssignments: 0,
    toGradeCount: 0,
    averageScore: 0,
    schedule: [],
    performance: {
      quiz1: 0,
      midterm: 0,
      project: 0,
      quiz2: 0
    },
    gradeDistribution: {
      a: 0,
      b: 0,
      c: 0,
      d: 0,
      f: 0
    },
    attendanceSummary: {
      present: 0,
      absent: 0,
      late: 0,
      excused: 0
    },
    behaviorSummary: {
      positive: 0,
      warnings: 0,
      incidents: 0
    },
    assignments: [],
    students: [],
    recentActivities: [],
    behaviorReports: []
  })
  showAddClassModal.value = true
}

function addClass() {
  // Add the new class to the classSections array
  classSections.value.push({...newClass})
  showAddClassModal.value = false

  // Select the newly added class
  selectSection(classSections.value[classSections.value.length - 1])
}

function openAddStudentModal() {
  if (!currentSection.value) return

  // Reset form
  Object.assign(newStudent, {
    id: generateStudentId(),
    name: '',
    email: '',
    status: 'present',
    notes: '',
    score: 0,
    gradeComment: ''
  })
  showAddStudentModal.value = true
}

function addStudent() {
  if (!currentSection.value) return

  // Add the new student to the current section
  currentSection.value.students.push({...newStudent})
  currentSection.value.studentCount = currentSection.value.students.length
  currentSection.value.presentCount = currentSection.value.students.filter(s => s.status === 'present').length

  showAddStudentModal.value = false
}

function openScheduleModal() {
  if (!currentSection.value) return

  // Reset form
  Object.assign(newScheduleItem, {
    day: 'Monday',
    time: '9:00 AM - 10:30 AM',
    room: 'Room 101',
    status: 'Active',
    statusClass: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300'
  })
  showScheduleModal.value = true
}

function addScheduleItem() {
  if (!currentSection.value) return

  // Add the new schedule item to the current section
  currentSection.value.schedule.push({...newScheduleItem})
  showScheduleModal.value = false
}

function removeScheduleItem(index) {
  if (!currentSection.value) return

  // Remove the schedule item at the specified index
  currentSection.value.schedule.splice(index, 1)
}

function generateId() {
  return 'class_' + Math.random().toString(36).substr(2, 9)
}

function generateStudentId() {
  const year = new Date().getFullYear()
  const randomNum = Math.floor(Math.random() * 1000).toString().padStart(3, '0')
  return `${year}${randomNum}`
}

// Lifecycle hooks
onMounted(() => {
  // Check for user preference
  if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
    darkMode.value = true
    document.documentElement.classList.add('dark')
  }

  // Listen for changes in color scheme preference
  window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', event => {
    darkMode.value = event.matches
    if (darkMode.value) {
      document.documentElement.classList.add('dark')
    } else {
      document.documentElement.classList.remove('dark')
    }
  })
})
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
      <!-- Sidebar Toggle Button (Mobile) -->
      <button
        @click="toggleSidebar"
        class="fixed z-50 bottom-4 right-4 md:hidden bg-primary-600 hover:bg-primary-700 text-white p-3 rounded-full shadow-lg"
      >
        <svg v-if="sidebarOpen" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
        <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>

      <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside
          :class="[
            'w-64 bg-white dark:bg-gray-800 shadow-lg transition-all duration-300 ease-in-out',
            'fixed inset-y-0 left-0 z-40 md:relative',
            sidebarOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'
          ]"
        >
          <!-- Sidebar Header -->
          <div class="flex items-center justify-between p-4 border-b dark:border-gray-700">
            <div class="flex items-center space-x-2">
              <div class="bg-primary-600 p-1.5 rounded">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path d="M12 14l9-5-9-5-9 5 9 5z" />
                  <path d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                </svg>
              </div>
              <h1 class="text-lg font-bold">Teacher Portal</h1>
            </div>
            <button @click="toggleSidebar" class="md:hidden">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>

          <!-- Teacher Profile -->
          <div class="p-4 border-b dark:border-gray-700">
            <div class="flex items-center space-x-3">
              <img src="https://i.pravatar.cc/100" alt="Teacher Avatar" class="w-10 h-10 rounded-full" />
              <div>
                <h3 class="font-medium">Ms. Rebecca Wilson</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Science Department</p>
              </div>
            </div>
          </div>

          <!-- Class Sections -->
          <div class="p-4 border-b dark:border-gray-700">
            <div class="flex items-center justify-between mb-2">
              <h3 class="text-sm font-medium text-gray-500 dark:text-gray-400">MY CLASSES</h3>
              <button
                @click="openAddClassModal"
                class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700 text-primary-600"
                title="Add Class"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
              </button>
            </div>
            <div class="space-y-2">
              <button
                v-for="section in classSections"
                :key="section.id"
                @click="selectSection(section)"
                :class="[
                  'w-full flex items-center justify-between p-2 rounded-md text-left text-sm',
                  currentSection && currentSection.id === section.id
                    ? 'bg-primary-50 text-primary-600 dark:bg-gray-700 dark:text-primary-400'
                    : 'hover:bg-gray-100 dark:hover:bg-gray-700'
                ]"
              >
                <div class="flex items-center space-x-2">
                  <span class="text-lg">{{ section.icon }}</span>
                  <span>{{ section.name }}</span>
                </div>
                <span
                  v-if="section.alerts"
                  class="bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full"
                >
                  {{ section.alerts }}
                </span>
              </button>
            </div>
          </div>

          <!-- Navigation Menu -->
          <nav class="p-2">
            <ul class="space-y-1">
              <li v-for="(item, index) in navItems" :key="index">
                <a
                  href="#"
                  @click.prevent="activePage = item.id"
                  :class="[
                    'flex items-center space-x-3 p-3 rounded-lg transition-colors',
                    activePage === item.id
                      ? 'bg-primary-50 text-primary-600 dark:bg-gray-700 dark:text-primary-400'
                      : 'hover:bg-gray-100 dark:hover:bg-gray-700'
                  ]"
                >
                  <span class="text-xl">{{ item.icon }}</span>
                  <span>{{ item.name }}</span>
                  <span
                    v-if="item.notifications"
                    class="ml-auto bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full"
                  >
                    {{ item.notifications }}
                  </span>
                </a>
              </li>
            </ul>
          </nav>

          <!-- Sidebar Footer -->
          <div class="absolute bottom-0 w-full p-4 border-t dark:border-gray-700">
            <div class="flex items-center justify-between">
              <button class="flex items-center space-x-2 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span>Logout</span>
              </button>
              <button @click="toggleDarkMode" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                <svg v-if="darkMode" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
              </button>
            </div>
          </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto bg-gray-50 dark:bg-gray-900">
          <!-- Header -->
          <header class="bg-white dark:bg-gray-800 shadow-sm">
            <div class="flex items-center justify-between p-4">
              <div class="flex items-center space-x-2">
                <h1 class="text-xl font-semibold">{{ currentPage.name }}</h1>
                <div v-if="currentSection" class="flex items-center space-x-2">
                  <span class="text-gray-400">|</span>
                  <div class="flex items-center space-x-2">
                    <span class="text-lg">{{ currentSection.icon }}</span>
                    <span class="text-gray-600 dark:text-gray-300">{{ currentSection.name }}</span>
                  </div>
                </div>
              </div>
              <div class="flex items-center space-x-4">
                <div class="relative">
                  <button
                    @click="showSectionDropdown = !showSectionDropdown"
                    class="flex items-center space-x-1 px-3 py-1.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-sm"
                  >
                    <span>Change Section</span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                  </button>
                  <div
                    v-if="showSectionDropdown"
                    class="absolute right-0 mt-1 w-56 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-lg z-10"
                  >
                    <div class="p-2">
                      <div class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-2 px-2">SELECT CLASS</div>
                      <button
                        v-for="section in classSections"
                        :key="section.id"
                        @click="selectSection(section); showSectionDropdown = false"
                        class="w-full flex items-center space-x-2 p-2 rounded-md text-left text-sm hover:bg-gray-100 dark:hover:bg-gray-700"
                      >
                        <span class="text-lg">{{ section.icon }}</span>
                        <span>{{ section.name }}</span>
                      </button>
                    </div>
                  </div>
                </div>
                <button class="relative p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                  </svg>
                  <span class="absolute top-0 right-0 h-2 w-2 bg-red-500 rounded-full"></span>
                </button>
                <button class="p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </button>
              </div>
            </div>
          </header>

          <!-- Page Content -->
          <div class="p-6">
            <!-- No Section Selected Message -->
            <div v-if="!currentSection" class="flex flex-col items-center justify-center h-[calc(100vh-10rem)] text-center">
              <div class="bg-gray-100 dark:bg-gray-800 p-6 rounded-full mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z" />
                </svg>
              </div>
              <h2 class="text-2xl font-bold mb-2">Select a Class Section</h2>
              <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-md">Please select a class section from the sidebar or use the dropdown menu to view class-specific information.</p>
              <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <button
                  v-for="section in classSections.slice(0, 4)"
                  :key="section.id"
                  @click="selectSection(section)"
                  class="flex flex-col items-center p-4 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700"
                >
                  <span class="text-3xl mb-2">{{ section.icon }}</span>
                  <span class="text-sm font-medium">{{ section.name }}</span>
                </button>
              </div>
            </div>

            <!-- Dashboard Page -->
            <div v-if="currentSection && activePage === 'dashboard'">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <!-- Class Overview Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium">Class Overview</h3>
                    <span class="text-green-500 bg-green-100 dark:bg-green-900 px-2 py-1 rounded-full text-xs font-medium">{{ currentSection.studentCount }} Students</span>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center bg-green-100 dark:bg-green-900 text-green-500">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Present Today</p>
                      <p class="text-2xl font-bold">{{ currentSection.presentCount }}/{{ currentSection.studentCount }}</p>
                    </div>
                  </div>
                </div>

                <!-- Assignments Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium">Assignments</h3>
                    <span class="text-blue-500 bg-blue-100 dark:bg-blue-900 px-2 py-1 rounded-full text-xs font-medium">{{ currentSection.pendingAssignments }} Pending</span>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center bg-blue-100 dark:bg-blue-900 text-blue-500">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                      </svg>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">To Grade</p>
                      <p class="text-2xl font-bold">{{ currentSection.toGradeCount }}</p>
                    </div>
                  </div>
                </div>

                <!-- Class Average Card -->
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                  <div class="flex items-center justify-between mb-4">
                    <h3 class="font-medium">Class Average</h3>
                    <span class="text-purple-500 bg-purple-100 dark:bg-purple-900 px-2 py-1 rounded-full text-xs font-medium">{{ getLetterGrade(currentSection.averageScore) }}</span>
                  </div>
                  <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-full flex items-center justify-center bg-purple-100 dark:bg-purple-900 text-purple-500">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                      </svg>
                    </div>
                    <div>
                  />

                    </div>
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Average Score</p>
                      <p class="text-2xl font-bold">{{ currentSection.averageScore }}%</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Class Schedule -->
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                  <h3 class="font-medium">Class Schedule</h3>
                  <button
                    @click="openScheduleModal"
                    class="px-3 py-1 bg-primary-600 text-white rounded-md text-sm flex items-center gap-1"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Schedule
                  </button>
                </div>
                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                      <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Day</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Time</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Room</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                      <tr v-for="(schedule, index) in currentSection.schedule" :key="index">
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ schedule.day }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ schedule.time }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ schedule.room }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${schedule.statusClass}`">
                            {{ schedule.status }}
                          </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <button
                            @click="removeScheduleItem(index)"
                            class="text-red-500 hover:text-red-700"
                            title="Remove Schedule"
                          >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                          </button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Performance Chart -->
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                <h3 class="font-medium mb-4">Class Performance</h3>
                <div class="h-64 w-full">
                  <!-- Chart would be rendered here with a chart library -->
                  <div class="h-full w-full flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg">
                    <div class="space-y-4 w-full px-8">
                      <!-- Quiz 1 -->
                      <div>
                        <div class="flex justify-between mb-1">
                          <span class="text-sm font-medium">Quiz 1</span>
                          <span class="text-sm font-medium">{{ currentSection.performance.quiz1 }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                          <div class="bg-blue-600 h-2.5 rounded-full" :style="`width: ${currentSection.performance.quiz1}%`"></div>
                        </div>
                      </div>

                      <!-- Midterm Exam -->
                      <div>
                        <div class="flex justify-between mb-1">
                          <span class="text-sm font-medium">Midterm Exam</span>
                          <span class="text-sm font-medium">{{ currentSection.performance.midterm }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                          <div class="bg-green-600 h-2.5 rounded-full" :style="`width: ${currentSection.performance.midterm}%`"></div>
                        </div>
                      </div>

                      <!-- Project -->
                      <div>
                        <div class="flex justify-between mb-1">
                          <span class="text-sm font-medium">Group Project</span>
                          <span class="text-sm font-medium">{{ currentSection.performance.project }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                          <div class="bg-yellow-600 h-2.5 rounded-full" :style="`width: ${currentSection.performance.project}%`"></div>
                        </div>
                      </div>

                      <!-- Quiz 2 -->
                      <div>
                        <div class="flex justify-between mb-1">
                          <span class="text-sm font-medium">Quiz 2</span>
                          <span class="text-sm font-medium">{{ currentSection.performance.quiz2 }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-600 rounded-full h-2.5">
                          <div class="bg-purple-600 h-2.5 rounded-full" :style="`width: ${currentSection.performance.quiz2}%`"></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Recent Activity -->
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="font-medium mb-4">Recent Activity</h3>
                <div class="space-y-4">
                  <div v-for="(activity, index) in currentSection.recentActivities" :key="index" class="flex items-start space-x-4 pb-4 border-b dark:border-gray-700 last:border-0 last:pb-0">
                    <div :class="`w-10 h-10 rounded-full flex items-center justify-center ${activity.iconBg} ${activity.iconColor}`">
                      <span class="text-lg">{{ activity.icon }}</span>
                    </div>
                    <div class="flex-1">
                      <p class="font-medium">{{ activity.title }}</p>
                      <p class="text-sm text-gray-500 dark:text-gray-400">{{ activity.description }}</p>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400">{{ activity.time }}</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Attendance Page -->
            <div v-if="currentSection && activePage === 'attendance'">
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mb-6">
                <div class="p-6 border-b dark:border-gray-700 flex justify-between items-center">
                  <div>
                    <h3 class="font-medium">Attendance Records</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Class: {{ currentSection.name }} - Today's Date: {{ currentDate }}</p>
                  </div>
                  <div class="flex space-x-2">
                    <button class="px-3 py-1 bg-green-500 text-white rounded-md text-sm">Save Attendance</button>
                    <button class="px-3 py-1 bg-gray-200 dark:bg-gray-700 rounded-md text-sm">View History</button>
                  </div>
                </div>
                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                      <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Notes</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                      <tr v-for="(student, index) in currentSection.students" :key="index">
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                              <img class="h-10 w-10 rounded-full" :src="`https://i.pravatar.cc/100?img=${index + 10}`" alt="" />
                            </div>
                            <div class="ml-4">
                              <div class="text-sm font-medium">{{ student.name }}</div>
                              <div class="text-sm text-gray-500 dark:text-gray-400">{{ student.email }}</div>
                            </div>
                          </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ student.id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <select v-model="student.status" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500">
                            <option value="present">Present</option>
                            <option value="absent">Absent</option>
                            <option value="late">Late</option>
                            <option value="excused">Excused</option>
                          </select>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <input
                            type="text"
                            v-model="student.notes"
                            placeholder="Add notes..."
                            class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500 w-full"
                          />
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div class="p-4 border-t dark:border-gray-700 flex items-center justify-between">
                  <div class="text-sm text-gray-500 dark:text-gray-400">
                    Showing <span class="font-medium">1</span> to <span class="font-medium">{{ currentSection.students.length }}</span> of <span class="font-medium">{{ currentSection.studentCount }}</span> students
                  </div>
                  <div class="flex space-x-2">
                    <button class="px-3 py-1 rounded border dark:border-gray-600 text-sm">Previous</button>
                    <button class="px-3 py-1 rounded bg-primary-600 text-white text-sm">Next</button>
                  </div>
                </div>
              </div>

              <!-- Attendance Summary -->
              <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Present</p>
                      <p class="text-2xl font-bold">{{ currentSection.attendanceSummary.present }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-green-100 dark:bg-green-900 flex items-center justify-center text-green-600 dark:text-green-400">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                      </svg>
                    </div>
                  </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Absent</p>
                      <p class="text-2xl font-bold">{{ currentSection.attendanceSummary.absent }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-red-100 dark:bg-red-900 flex items-center justify-center text-red-600 dark:text-red-400">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                      </svg>
                    </div>
                  </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Late</p>
                      <p class="text-2xl font-bold">{{ currentSection.attendanceSummary.late }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-yellow-100 dark:bg-yellow-900 flex items-center justify-center text-yellow-600 dark:text-yellow-400">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </div>
                  </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Excused</p>
                      <p class="text-2xl font-bold">{{ currentSection.attendanceSummary.excused }}</p>
                    </div>
                    <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-blue-600 dark:text-blue-400">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                      </svg>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Grades Page -->
            <div v-if="currentSection && activePage === 'grades'">
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden mb-6">
                <div class="p-6 border-b dark:border-gray-700 flex justify-between items-center">
                  <div>
                    <h3 class="font-medium">Grade Management</h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Class: {{ currentSection.name }} - {{ currentSection.term }}</p>
                  </div>
                  <div class="flex space-x-2">
                    <button class="px-3 py-1 bg-primary-600 text-white rounded-md text-sm">Add Assignment</button>
                    <button class="px-3 py-1 bg-gray-200 dark:bg-gray-700 rounded-md text-sm">Export Grades</button>
                  </div>
                </div>

                <!-- Assignment Selection -->
                <div class="p-4 bg-gray-50 dark:bg-gray-700 border-b dark:border-gray-600">
                  <div class="flex flex-wrap items-center gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Assignment</label>
                      <select v-model="selectedAssignment" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm focus:ring-primary-500 focus:border-primary-500">
                        <option v-for="assignment in currentSection.assignments" :key="assignment.id" :value="assignment.id">
                          {{ assignment.name }}
                        </option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Category</label>
                      <select class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm focus:ring-primary-500 focus:border-primary-500">
                        <option>Exam</option>
                        <option>Quiz</option>
                        <option>Homework</option>
                        <option>Project</option>
                        <option>Participation</option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Weight</label>
                      <input type="number" value="20" class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm focus:ring-primary-500 focus:border-primary-500 w-20" />
                    </div>
                  </div>
                </div>

                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                      <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Score</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Grade</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Comments</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                      <tr v-for="(student, index) in currentSection.students" :key="index">
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                              <img class="h-10 w-10 rounded-full" :src="`https://i.pravatar.cc/100?img=${index + 10}`" alt="" />
                            </div>
                            <div class="ml-4">
                              <div class="text-sm font-medium">{{ student.name }}</div>
                              <div class="text-sm text-gray-500 dark:text-gray-400">{{ student.email }}</div>
                            </div>
                          </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ student.id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <input
                            type="number"
                            v-model="student.score"
                            min="0"
                            max="100"
                            class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500 w-20"
                          />
                          <span class="ml-1 text-gray-500 dark:text-gray-400">/ 100</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getGradeClass(student.score)}`">
                            {{ getLetterGrade(student.score) }}
                          </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <input
                            type="text"
                            v-model="student.gradeComment"
                            placeholder="Add comment..."
                            class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500 w-full"
                          />
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
                <div class="p-4 border-t dark:border-gray-700 flex items-center justify-between">
                  <div class="text-sm text-gray-500 dark:text-gray-400">
                    Class Average: <span class="font-medium">{{ currentSection.averageScore }}%</span> ({{ getLetterGrade(currentSection.averageScore) }})
                  </div>
                  <div class="flex space-x-2">
                    <button class="px-3 py-1 rounded border dark:border-gray-600 text-sm">Cancel</button>
                    <button class="px-3 py-1 rounded bg-primary-600 text-white text-sm">Save Grades</button>
                  </div>
                </div>
              </div>

              <!-- Grade Distribution Chart -->
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="font-medium mb-4">Grade Distribution</h3>
                <div class="h-64 w-full">
                  <!-- Chart would be rendered here with a chart library -->
                  <div class="h-full w-full flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg">
                    <div class="grid grid-cols-5 gap-4 w-full px-8">
                      <div class="flex flex-col items-center">
                        <div class="h-32 w-12 bg-green-500 rounded-t-lg relative overflow-hidden">
                          <div class="absolute bottom-0 w-full bg-green-600" :style="`height: ${currentSection.gradeDistribution.a}%`"></div>
                        </div>
                        <p class="mt-2 font-medium">A</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ Math.round(currentSection.gradeDistribution.a * currentSection.studentCount / 100) }} students</p>
                      </div>
                      <div class="flex flex-col items-center">
                        <div class="h-32 w-12 bg-blue-500 rounded-t-lg relative overflow-hidden">
                          <div class="absolute bottom-0 w-full bg-blue-600" :style="`height: ${currentSection.gradeDistribution.b}%`"></div>
                        </div>
                        <p class="mt-2 font-medium">B</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ Math.round(currentSection.gradeDistribution.b * currentSection.studentCount / 100) }} students</p>
                      </div>
                      <div class="flex flex-col items-center">
                        <div class="h-32 w-12 bg-yellow-500 rounded-t-lg relative overflow-hidden">
                          <div class="absolute bottom-0 w-full bg-yellow-600" :style="`height: ${currentSection.gradeDistribution.c}%`"></div>
                        </div>
                        <p class="mt-2 font-medium">C</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ Math.round(currentSection.gradeDistribution.c * currentSection.studentCount / 100) }} students</p>
                      </div>
                      <div class="flex flex-col items-center">
                        <div class="h-32 w-12 bg-orange-500 rounded-t-lg relative overflow-hidden">
                          <div class="absolute bottom-0 w-full bg-orange-600" :style="`height: ${currentSection.gradeDistribution.d}%`"></div>
                        </div>
                        <p class="mt-2 font-medium">D</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ Math.round(currentSection.gradeDistribution.d * currentSection.studentCount / 100) }} students</p>
                      </div>
                      <div class="flex flex-col items-center">
                        <div class="h-32 w-12 bg-red-500 rounded-t-lg relative overflow-hidden">
                          <div class="absolute bottom-0 w-full bg-red-600" :style="`height: ${currentSection.gradeDistribution.f}%`"></div>
                        </div>
                        <p class="mt-2 font-medium">F</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ Math.round(currentSection.gradeDistribution.f * currentSection.studentCount / 100) }} students</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Behavior Reports Page -->
            <div v-if="currentSection && activePage === 'behavior'">
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                <div class="flex justify-between items-center mb-6">
                  <h3 class="font-medium">Behavior Reports</h3>
                  <button class="px-3 py-1 bg-primary-600 text-white rounded-md text-sm flex items-center gap-1">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    New Report
                  </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                  <div class="p-4 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-100 dark:border-green-800">
                    <div class="flex items-center space-x-3">
                      <div class="bg-green-100 dark:bg-green-800 p-2 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                      </div>
                      <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Positive Reports</p>
                        <p class="text-xl font-bold">{{ currentSection.behaviorSummary.positive }}</p>
                      </div>
                    </div>
                  </div>
                  <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-100 dark:border-yellow-800">
                    <div class="flex items-center space-x-3">
                      <div class="bg-yellow-100 dark:bg-yellow-800 p-2 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-600 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                      </div>
                      <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Warnings</p>
                        <p class="text-xl font-bold">{{ currentSection.behaviorSummary.warnings }}</p>
                      </div>
                    </div>
                  </div>
                  <div class="p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-100 dark:border-red-800">
                    <div class="flex items-center space-x-3">
                      <div class="bg-red-100 dark:bg-red-800 p-2 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                      </div>
                      <div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">Incidents</p>
                        <p class="text-xl font-bold">{{ currentSection.behaviorSummary.incidents }}</p>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                  <div v-for="(report, index) in currentSection.behaviorReports" :key="index" class="p-6">
                    <div class="flex items-start space-x-4">
                      <div :class="`w-10 h-10 rounded-full flex items-center justify-center ${report.iconBg} ${report.iconColor}`">
                        <span class="text-lg">{{ report.icon }}</span>
                      </div>
                      <div class="flex-1">
                        <div class="flex items-center justify-between mb-2">
                          <h4 class="font-medium">{{ report.student }}</h4>
                          <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${report.typeClass}`">
                            {{ report.type }}
                          </span>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">{{ report.description }}</p>
                        <div class="flex items-center text-sm text-gray-500 dark:text-gray-400">
                          <p>{{ report.date }}</p>
                        </div>
                      </div>
                      <div class="flex space-x-2">
                        <button class="p-1 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                          </svg>
                        </button>
                        <button class="p-1 text-gray-500 hover:text-red-600 dark:hover:text-red-400">
                          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                          </svg>
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- New Report Form -->
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="font-medium mb-4">Create New Behavior Report</h3>
                <form class="space-y-4">
                  <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Student</label>
                      <select class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500 w-full">
                        <option value="">Select a student</option>
                        <option v-for="(student, index) in currentSection.students" :key="index" :value="student.id">
                          {{ student.name }}
                        </option>
                      </select>
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Report Type</label>
                      <select class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500 w-full">
                        <option value="positive">Positive</option>
                        <option value="warning">Warning</option>
                        <option value="incident">Incident</option>
                      </select>
                    </div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                    <textarea
                      rows="4"
                      placeholder="Describe the behavior..."
                      class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500 w-full"
                    ></textarea>
                  </div>
                  <div class="flex justify-end space-x-2">
                    <button type="button" class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded-md text-sm">Submit Report</button>
                  </div>
                </form>
              </div>
            </div>

            <!-- Students Page (New) -->
            <div v-if="currentSection && activePage === 'students'">
              <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
                <div class="flex justify-between items-center mb-6">
                  <h3 class="font-medium">Student Management</h3>
                  <button
                    @click="openAddStudentModal"
                    class="px-3 py-1 bg-primary-600 text-white rounded-md text-sm flex items-center gap-1"
                  >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add Student
                  </button>
                </div>

                <div class="overflow-x-auto">
                  <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                      <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Student</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Email</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Grade</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Actions</th>
                      </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                      <tr v-for="(student, index) in currentSection.students" :key="index">
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10">
                              <img class="h-10 w-10 rounded-full" :src="`https://i.pravatar.cc/100?img=${index + 10}`" alt="" />
                            </div>
                            <div class="ml-4">
                              <div class="text-sm font-medium">{{ student.name }}</div>
                            </div>
                          </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ student.id }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm">{{ student.email }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                            student.status === 'present' ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300' :
                            student.status === 'absent' ? 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300' :
                            student.status === 'late' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300' :
                            'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300'
                          }`">
                            {{ student.status.charAt(0).toUpperCase() + student.status.slice(1) }}
                          </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <span :class="`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${getGradeClass(student.score)}`">
                            {{ getLetterGrade(student.score) }} ({{ student.score }}%)
                          </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                          <div class="flex space-x-2">
                            <button class="p-1 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                              </svg>
                            </button>
                            <button class="p-1 text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                              </svg>
                            </button>
                            <button class="p-1 text-gray-500 hover:text-red-600 dark:hover:text-red-400">
                              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                              </svg>
                            </button>
                          </div>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>

              <!-- Student Statistics -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                  <h3 class="font-medium mb-4">Attendance Overview</h3>
                  <div class="h-64 w-full">
                    <div class="h-full w-full flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg">
                      <div class="grid grid-cols-4 gap-4 w-full px-8">
                        <div class="flex flex-col items-center">
                          <div class="h-32 w-12 bg-green-500 rounded-t-lg relative overflow-hidden">
                            <div class="absolute bottom-0 w-full bg-green-600" :style="`height: ${currentSection.attendanceSummary.present / currentSection.studentCount * 100}%`"></div>
                          </div>
                          <p class="mt-2 font-medium">Present</p>
                          <p class="text-xs text-gray-500 dark:text-gray-400">{{ currentSection.attendanceSummary.present }} students</p>
                        </div>
                        <div class="flex flex-col items-center">
                          <div class="h-32 w-12 bg-red-500 rounded-t-lg relative overflow-hidden">
                            <div class="absolute bottom-0 w-full bg-red-600" :style="`height: ${currentSection.attendanceSummary.absent / currentSection.studentCount * 100}%`"></div>
                          </div>
                          <p class="mt-2 font-medium">Absent</p>
                          <p class="text-xs text-gray-500 dark:text-gray-400">{{ currentSection.attendanceSummary.absent }} students</p>
                        </div>
                        <div class="flex flex-col items-center">
                          <div class="h-32 w-12 bg-yellow-500 rounded-t-lg relative overflow-hidden">
                            <div class="absolute bottom-0 w-full bg-yellow-600" :style="`height: ${currentSection.attendanceSummary.late / currentSection.studentCount * 100}%`"></div>
                          </div>
                          <p class="mt-2 font-medium">Late</p>
                          <p class="text-xs text-gray-500 dark:text-gray-400">{{ currentSection.attendanceSummary.late }} students</p>
                        </div>
                        <div class="flex flex-col items-center">
                          <div class="h-32 w-12 bg-blue-500 rounded-t-lg relative overflow-hidden">
                            <div class="absolute bottom-0 w-full bg-blue-600" :style="`height: ${currentSection.attendanceSummary.excused / currentSection.studentCount * 100}%`"></div>
                          </div>
                          <p class="mt-2 font-medium">Excused</p>
                          <p class="text-xs text-gray-500 dark:text-gray-400">{{ currentSection.attendanceSummary.excused }} students</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                  <h3 class="font-medium mb-4">Behavior Overview</h3>
                  <div class="h-64 w-full">
                    <div class="h-full w-full flex items-center justify-center bg-gray-100 dark:bg-gray-700 rounded-lg">
                      <div class="grid grid-cols-3 gap-4 w-full px-8">
                        <div class="flex flex-col items-center">
                          <div class="h-32 w-12 bg-green-500 rounded-t-lg relative overflow-hidden">
                            <div class="absolute bottom-0 w-full bg-green-600" :style="`height: ${currentSection.behaviorSummary.positive / (currentSection.behaviorSummary.positive + currentSection.behaviorSummary.warnings + currentSection.behaviorSummary.incidents) * 100}%`"></div>
                          </div>
                          <p class="mt-2 font-medium">Positive</p>
                          <p class="text-xs text-gray-500 dark:text-gray-400">{{ currentSection.behaviorSummary.positive }} reports</p>
                        </div>
                        <div class="flex flex-col items-center">
                          <div class="h-32 w-12 bg-yellow-500 rounded-t-lg relative overflow-hidden">
                            <div class="absolute bottom-0 w-full bg-yellow-600" :style="`height: ${currentSection.behaviorSummary.warnings / (currentSection.behaviorSummary.positive + currentSection.behaviorSummary.warnings + currentSection.behaviorSummary.incidents) * 100}%`"></div>
                          </div>
                          <p class="mt-2 font-medium">Warnings</p>
                          <p class="text-xs text-gray-500 dark:text-gray-400">{{ currentSection.behaviorSummary.warnings }} reports</p>
                        </div>
                        <div class="flex flex-col items-center">
                          <div class="h-32 w-12 bg-red-500 rounded-t-lg relative overflow-hidden">
                            <div class="absolute bottom-0 w-full bg-red-600" :style="`height: ${currentSection.behaviorSummary.incidents / (currentSection.behaviorSummary.positive + currentSection.behaviorSummary.warnings + currentSection.behaviorSummary.incidents) * 100}%`"></div>
                          </div>
                          <p class="mt-2 font-medium">Incidents</p>
                          <p class="text-xs text-gray-500 dark:text-gray-400">{{ currentSection.behaviorSummary.incidents }} reports</p>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>

    <!-- Add Class Modal -->
    <div v-if="showAddClassModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-md p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium">Add New Class</h3>
          <button @click="showAddClassModal = false" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <form @submit.prevent="addClass" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Class Name</label>
            <input
              type="text"
              v-model="newClass.name"
              required
              placeholder="e.g., Mathematics 101"
              class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500 w-full"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Term</label>
            <input
              type="text"
              v-model="newClass.term"
              placeholder="e.g., Fall Semester 2023"
              class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500 w-full"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Icon</label>
            <div class="grid grid-cols-5 gap-2">
              <button
                v-for="icon in availableIcons"
                :key="icon"
                type="button"
                @click="newClass.icon = icon"
                :class="[
                  'h-10 w-10 flex items-center justify-center text-lg rounded-md',
                  newClass.icon === icon
                    ? 'bg-primary-100 dark:bg-primary-900 border-2 border-primary-500'
                    : 'bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600'
                ]"
              >
                {{ icon }}
              </button>
            </div>
          </div>
          <div class="flex justify-end space-x-2 pt-4">
            <button
              type="button"
              @click="showAddClassModal = false"
              class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-primary-600 text-white rounded-md text-sm"
            >
              Add Class
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Add Student Modal -->
    <div v-if="showAddStudentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-md p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium">Add New Student</h3>
          <button @click="showAddStudentModal = false" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <form @submit.prevent="addStudent" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Student Name</label>
            <input
              type="text"
              v-model="newStudent.name"
              required
              placeholder="e.g., John Smith"
              class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500 w-full"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
            <input
              type="email"
              v-model="newStudent.email"
              required
              placeholder="e.g., john.smith@school.edu"
              class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500 w-full"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
            <select
              v-model="newStudent.status"
              class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500 w-full"
            >
              <option value="present">Present</option>
              <option value="absent">Absent</option>
              <option value="late">Late</option>
              <option value="excused">Excused</option>
            </select>
          </div>
          <div class="flex justify-end space-x-2 pt-4">
            <button
              type="button"
              @click="showAddStudentModal = false"
              class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-primary-600 text-white rounded-md text-sm"
            >
              Add Student
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Add Schedule Modal -->
    <div v-if="showScheduleModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50">
      <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg w-full max-w-md p-6">
        <div class="flex justify-between items-center mb-4">
          <h3 class="text-lg font-medium">Add Class Schedule</h3>
          <button @click="showScheduleModal = false" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>
        <form @submit.prevent="addScheduleItem" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Day</label>
            <select
              v-model="newScheduleItem.day"
              class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500 w-full"
            >
              <option>Monday</option>
              <option>Tuesday</option>
              <option>Wednesday</option>
              <option>Thursday</option>
              <option>Friday</option>
              <option>Saturday</option>
              <option>Sunday</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Time</label>
            <input
              type="text"
              v-model="newScheduleItem.time"
              required
              placeholder="e.g., 9:00 AM - 10:30 AM"
              class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500 w-full"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Room</label>
            <input
              type="text"
              v-model="newScheduleItem.room"
              required
              placeholder="e.g., Room 101"
              class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500 w-full"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Status</label>
            <select
              v-model="newScheduleItem.status"
              class="rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-sm focus:ring-primary-500 focus:border-primary-500 w-full"
            >
              <option>Active</option>
              <option>Cancelled</option>
              <option>Rescheduled</option>
            </select>
          </div>
          <div class="flex justify-end space-x-2 pt-4">
            <button
              type="button"
              @click="showScheduleModal = false"
              class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md text-sm"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 bg-primary-600 text-white rounded-md text-sm"
            >
              Add Schedule
            </button>
          </div>
        </form>
      </div>
    </div>
</template>

<style>
:root {
  --primary-50: 239, 246, 255;
  --primary-100: 219, 234, 254;
  --primary-200: 191, 219, 254;
  --primary-300: 147, 197, 253;
  --primary-400: 96, 165, 250;
  --primary-500: 59, 130, 246;
  --primary-600: 37, 99, 235;
  --primary-700: 29, 78, 216;
  --primary-800: 30, 64, 175;
  --primary-900: 30, 58, 138;
}

.bg-primary-50 { background-color: rgb(var(--primary-50)); }
.bg-primary-100 { background-color: rgb(var(--primary-100)); }
.bg-primary-600 { background-color: rgb(var(--primary-600)); }
.text-primary-600 { color: rgb(var(--primary-600)); }
.text-primary-400 { color: rgb(var(--primary-400)); }

/* Add Tailwind dark mode support */
.dark .dark\:bg-primary-900 { background-color: rgb(var(--primary-900)); }
.dark .dark\:text-primary-400 { color: rgb(var(--primary-400)); }
.dark .dark\:text-primary-300 { color: rgb(var(--primary-300)); }
</style>
