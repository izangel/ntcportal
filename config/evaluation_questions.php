<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Student/Client Evaluation Questions 
    | (Note: For non-teaching staff, "Student" can represent "Service User")
    |--------------------------------------------------------------------------
    */
    // 'student' => [
    //     'q1'  => 'The teacher explains the subject matter clearly and understandably.',
    //     'q2'  => 'The teacher demonstrates thorough knowledge of the topic.',
    //     'q3'  => 'The teacher encourages students to ask questions and participate.',
    //     'q4'  => 'The teacher provides relevant examples to illustrate complex concepts.',
    //     'q5'  => 'The teacher is punctual in starting and ending class sessions.',
    //     'q6'  => 'The teacher uses instructional materials (slides, videos, etc.) effectively.',
    //     'q7'  => 'The teacher treats all students with fairness and respect.',
    //     'q8'  => 'The teacher provides timely feedback on assignments and exams.',
    //     'q9'  => 'The teacher is approachable and available for consultation.',
    //     'q10' => 'Overall, the teacher creates a positive learning environment.',
    // ],

    'student' => [
    [
        'id' => 'A', 'title' => 'Course Design & Content', 
        'questions' => [
            ['k' => 'q1', 't' => 'The course objectives were clearly explained.'],
            ['k' => 'q2', 't' => 'The course content was relevant to my program.'],
            ['k' => 'q3', 't' => 'The workload was appropriate for the course level.'],
            ['k' => 'q4', 't' => 'Learning activities helped me understand the lessons.']
        ] 
    ],
    [
        'id' => 'B', 'title' => 'Teaching Effectiveness', 
        'questions' => [
            ['k' => 'q5', 't' => 'The instructor explained concepts clearly.'],
            ['k' => 'q6', 't' => 'The instructor was prepared for each class.'],
            ['k' => 'q7', 't' => 'The instructor encouraged participation and questions.'],
            ['k' => 'q8', 't' => 'The instructor used appropriate teaching strategies.']
        ] 
    ],
    [
        'id' => 'C', 'title' => 'Assessment & Feedback', 
        'questions' => [
            ['k' => 'q9', 't' => 'Assessments were aligned with course objectives.'],
            ['k' => 'q10', 't' => 'Grading criteria were clear and fair.'],
            ['k' => 'q11', 't' => 'Feedback helped me improve my performance.']
        ] 
    ],
    [
        'id' => 'D', 'title' => 'Learning Resources', 
        'questions' => [
            ['k' => 'q12', 't' => 'Learning materials (slides, modules, LMS) were helpful.'],
            ['k' => 'q13', 't' => 'Technology/tools used supported my learning.']
        ] 
    ],
    [
        'id' => 'E', 'title' => 'Learning Outcomes', 
        'questions' => [
            ['k' => 'q14', 't' => 'I achieved the learning outcomes of this course.'],
            ['k' => 'q15', 't' => 'This course helped develop my knowledge and skills.']
        ] 
    ],
    [
        'id' => 'F', 'title' => 'Overall Evaluation', 
        'questions' => [
            ['k' => 'q16', 't' => 'Overall, I am satisfied with this course.'],
            ['k' => 'q17', 't' => 'I would recommend this course to other students.']
        ] 
    ]
],
    /*
    |--------------------------------------------------------------------------
    | Peer Evaluation Questions 
    | Focus: Collaboration, Communication, and Informal Leadership
    |--------------------------------------------------------------------------
    */
    'peer' => [
        'p1'  => 'Communicates ideas and information clearly and professionally with the team.',
        'p2'  => 'Actively collaborates and contributes to the success of team projects.',
        'p3'  => 'Shows leadership by taking initiative and ownership of tasks without being asked.',
        'p4'  => 'Is reliable and completes their share of work on time to support the team.',
        'p5'  => 'Demonstrates a positive attitude that motivates and influences others.',
        'p6'  => 'Offers help and mentors colleagues to help the department grow.',
        'p7'  => 'Handles workplace conflicts or disagreements with maturity and respect.',
        'p8'  => 'Shares knowledge and resources generously with peers.',
        'p9'  => 'Respects diverse perspectives and fosters an inclusive work environment.',
        'p10' => 'Consistently embodies the core values and ethics of the institution.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Self-Evaluation Questions 
    | Focus: Personal Accountability, Growth, and Value Alignment
    |--------------------------------------------------------------------------
    */
    'self' => [
        's1'  => 'I communicate my goals and progress transparently to my team and supervisor.',
        's2'  => 'I actively seek ways to collaborate with other departments or colleagues.',
        's3'  => 'I take the lead on solving problems rather than just identifying them.',
        's4'  => 'I manage my time effectively to ensure high-quality results in my tasks.',
        's5'  => 'I demonstrate leadership by being a positive role model for my peers.',
        's6'  => 'I am committed to my personal and professional development.',
        's7'  => 'I accept constructive feedback and use it to improve my performance.',
        's8'  => 'I align my daily work with the mission and vision of the organization.',
        's9'  => 'I maintain a high standard of integrity in all my professional dealings.',
        's10' => 'I contribute to a healthy and productive workplace culture.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Supervisor Evaluation Questions 
    | Focus: Strategic Leadership, Compliance, and Professional Impact
    |--------------------------------------------------------------------------
    */
    'supervisor' => [
        'v1'  => 'Demonstrates strong leadership by driving results and meeting objectives.',
        'v2'  => 'Communicates effectively with management regarding challenges and solutions.',
        'v3'  => 'Works harmoniously with others to achieve departmental milestones.',
        'v4'  => 'Adheres to all institutional policies, procedures, and ethical standards.',
        'v5'  => 'Exhibits a high level of accountability for their actions and decisions.',
        'v6'  => 'Maintains professional conduct even under high-pressure situations.',
        'v7'  => 'Shows initiative in improving existing processes or workflows.',
        'v8'  => 'Consistently delivers work that meets or exceeds quality standards.',
        'v9'  => 'Upholds the institution’s values and represents the organization well.',
        'v10' => 'Displays the potential for increased responsibility or leadership roles.',
    ],
];