export interface Course {
  id: string | number;
  title: string;
  description: string;
  slug: string;
  price: number;
  status: 'draft' | 'published' | 'archived';
  instructor?: {
    id: number;
    name: string;
  };
  blocks?: CourseBlock[];
}

export interface CourseBlock {
  id: number;
  course_id: number;
  title: string;
  order: number;
  topics?: CourseTopic[];
}

export interface CourseTopic {
  id: number;
  block_id: number;
  title: string;
  content_type: 'video' | 'pdf' | 'text';
  content_url?: string;
  order: number;
}

export interface Test {
  id: number;
  course_id: number;
  title: string;
  passing_score: number;
  questions?: Question[];
}

export interface Question {
  id: number;
  test_id: number;
  question: string;
  type: 'single' | 'multiple' | 'true_false';
  options: { id: number; text: string; is_correct: boolean }[];
}
