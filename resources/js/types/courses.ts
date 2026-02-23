// Course Types

export interface Course {
  id: number
  title: string
  slug: string
  description: string | null
  short_description: string | null
  thumbnail: string | null
  trailer_video_url: string | null
  level: 'beginner' | 'intermediate' | 'advanced'
  level_label: string
  language: string
  duration_hours: number
  price: number
  currency: string
  formatted_price: string
  status: 'draft' | 'published' | 'archived'
  requirements: string[] | null
  what_you_learn: string[] | null
  target_audience: string[] | null
  total_blocks: number
  total_topics: number
  total_duration_seconds: number
  enrolled_count: number
  rating: number | null
  reviews_count: number
  published_at: string | null
  created_at: string
  updated_at: string
  instructor?: CourseInstructor
  blocks?: CourseBlock[]
}

export interface CourseInstructor {
  id: number
  name: string
  email: string
  avatar: string | null
  bio: string | null
}

export interface CourseBlock {
  id: number
  title: string
  description: string | null
  sort_order: number
  topics_count?: number
  total_duration_seconds?: number
  topics?: CourseTopic[]
}

export interface CourseTopic {
  id: number
  title: string
  description: string | null
  content_type: 'video' | 'text' | 'pdf' | 'quiz'
  video_duration_seconds: number
  formatted_duration: string
  is_free_preview: boolean
  sort_order: number
  video_url?: string
  embed_url?: string
  content?: string
  attachments?: TopicAttachment[]
  progress?: TopicProgress
}

export interface TopicAttachment {
  name: string
  url: string
  type: string
  size: number
}

export interface TopicProgress {
  id: number
  is_completed: boolean
  completed_at: string | null
  watch_time_seconds: number
  watch_percentage: number
  last_position_seconds: number
  last_watched_at: string | null
}

// Enrollment Types

export interface CourseEnrollment {
  id: number
  status: 'active' | 'completed' | 'expired'
  progress_percentage: number
  completed_topics: number
  total_topics: number
  enrolled_at: string
  completed_at: string | null
  expires_at: string | null
  last_activity_at: string | null
  is_active: boolean
  is_completed: boolean
  is_expired: boolean
  course?: Course
  subscription?: Subscription
}

export interface EnrollmentProgress {
  enrollment_id: number
  course_id: number
  overall_percentage: number
  completed_topics: number
  total_topics: number
  status: string
  blocks: BlockProgress[]
  tests: TestProgress[]
}

export interface BlockProgress {
  block_id: number
  block_title: string
  total_topics: number
  completed_topics: number
  percentage: number
  topics: TopicProgressDetail[]
}

export interface TopicProgressDetail {
  topic_id: number
  topic_title: string
  is_completed: boolean
  completed_at: string | null
  watch_percentage: number
  last_position_seconds: number
}

export interface TestProgress {
  test_id: number
  test_title: string
  is_required: boolean
  passing_score: number
  attempts_used: number
  max_attempts: number
  best_score: number | null
  passed: boolean
}

// Subscription Types

export interface SubscriptionPlan {
  id: number
  name: string
  description: string | null
  price: number
  currency: string
  formatted_price: string
  billing_cycle: 'weekly' | 'monthly' | 'quarterly' | 'biannual' | 'yearly' | 'lifetime'
  billing_cycle_label: string
  trial_days: number
  features: string[] | null
  course_access: 'all' | 'specific' | 'category'
  max_courses: number | null
  is_active: boolean
  is_featured: boolean
  sort_order: number
  course_ids?: number[]
}

export interface Subscription {
  id: number
  status: 'active' | 'trial' | 'past_due' | 'cancelled' | 'paused' | 'expired'
  starts_at: string
  ends_at: string | null
  trial_ends_at: string | null
  cancelled_at: string | null
  amount: number
  currency: string
  payment_provider: string | null
  payment_method: string | null
  last_payment_at: string | null
  next_payment_at: string | null
  is_active: boolean
  is_on_trial: boolean
  is_cancelled: boolean
  days_remaining: number | null
  plan?: SubscriptionPlan
}

// Test Types

export interface CourseTest {
  id: number
  title: string
  description: string | null
  type: 'quiz' | 'exam' | 'practice'
  type_label: string
  time_limit_minutes: number | null
  has_time_limit: boolean
  passing_score: number
  max_attempts: number
  has_unlimited_attempts: boolean
  show_answers_after: boolean
  shuffle_questions: boolean
  shuffle_options: boolean
  is_required: boolean
  total_questions: number
  total_points: number
  sort_order: number
  questions?: TestQuestion[]
}

export interface TestQuestion {
  id: number
  question: string
  explanation?: string
  type: 'single' | 'multiple' | 'true_false'
  type_label: string
  points: number
  sort_order: number
  options?: TestOption[]
}

export interface TestOption {
  id: number
  text: string
  is_correct?: boolean
  sort_order: number
}

export interface TestAttempt {
  id: number
  test_id: number
  score?: number
  total_points?: number
  percentage?: number
  passed?: boolean
  time_spent_seconds: number
  formatted_time_spent: string
  started_at: string
  completed_at: string | null
  is_completed: boolean
  is_in_progress: boolean
  time_remaining_seconds?: number
  is_timed_out?: boolean
  results?: TestResults
  test?: CourseTest
}

export interface TestResults {
  [questionId: number]: {
    correct: boolean
    points: number
    selected: number[]
    correct_options: number[]
  }
}

export interface TestAttemptState {
  attempt_id: number
  test_id: number
  test_title: string
  time_limit_minutes: number | null
  time_remaining_seconds: number | null
  started_at: string
  is_timed_out: boolean
  questions: AttemptQuestion[]
  total_questions: number
  answered_questions: number
}

export interface AttemptQuestion {
  id: number
  question: string
  type: 'single' | 'multiple' | 'true_false'
  points: number
  options: { id: number; text: string }[]
  selected: number[] | null
}

// Access Types

export interface CourseAccessDetails {
  can_access: boolean
  access_type: 'free' | 'subscription' | 'direct_enrollment' | 'none'
  enrollment: {
    id: number
    status: string
    progress_percentage: number
    expires_at: string | null
  } | null
  subscription: {
    id: number
    plan_name: string
    status: string
    ends_at: string | null
  } | null
  is_free: boolean
  price: number
  currency: string
}

// API Response Types

export interface PaginatedResponse<T> {
  data: T[]
  meta: {
    current_page: number
    from: number
    last_page: number
    per_page: number
    to: number
    total: number
  }
  links: {
    first: string
    last: string
    prev: string | null
    next: string | null
  }
}

export interface CourseFilters {
  search?: string
  level?: string
  status?: string
  min_price?: number
  max_price?: number
  free?: boolean
  sort_by?: 'price' | 'rating' | 'popular' | 'published_at'
  sort_direction?: 'asc' | 'desc'
  per_page?: number
  page?: number
}
