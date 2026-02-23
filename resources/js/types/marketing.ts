// Email Template Types
export interface EmailTemplate {
  id: number
  name: string
  subject: string
  content_html: string
  content_text: string | null
  type: 'marketing' | 'transactional' | 'notification'
  category: string | null
  variables: string[]
  settings: Record<string, any>
  is_active: boolean
  created_by: number
  creator?: { id: number; name: string }
  created_at: string
  updated_at: string
}

export interface EmailTemplateListItem {
  id: number
  name: string
  subject: string
  type: string
  category: string | null
  is_active: boolean
  created_at: string
}

// Email Campaign Types
export type CampaignStatus = 'draft' | 'scheduled' | 'sending' | 'sent' | 'paused' | 'cancelled'
export type CampaignType = 'regular' | 'automated' | 'ab_test'

export interface EmailCampaign {
  id: number
  name: string
  subject: string
  from_name: string
  from_email: string
  reply_to: string | null
  content_html: string | null
  content_text: string | null
  type: CampaignType
  status: CampaignStatus
  settings: Record<string, any>
  template_id: number | null
  template?: { id: number; name: string }
  stats: CampaignStats
  rates?: CampaignRates
  scheduled_at: string | null
  started_at: string | null
  completed_at: string | null
  created_by: number
  creator?: { id: number; name: string }
  created_at: string
  updated_at: string
}

export interface CampaignStats {
  recipient_count: number
  sent_count: number
  delivered_count: number
  opened_count: number
  clicked_count: number
  bounced_count: number
  unsubscribed_count: number
}

export interface CampaignRates {
  delivery_rate: number
  open_rate: number
  click_rate: number
}

export interface CampaignListItem {
  id: number
  name: string
  subject: string
  type: CampaignType
  status: CampaignStatus
  recipient_count: number
  sent_count: number
  open_rate: number | null
  click_rate: number | null
  scheduled_at: string | null
  completed_at: string | null
  created_at: string
}

export interface CampaignDetailedStats {
  overview: CampaignStats
  rates: {
    delivery_rate: number
    open_rate: number
    click_rate: number
    bounce_rate: number
    unsubscribe_rate: number
    click_to_open_rate: number
  }
  engagement: {
    total_opens: number
    total_clicks: number
    unique_opens: number
    unique_clicks: number
    avg_opens_per_recipient: number
    avg_clicks_per_recipient: number
  }
  top_links: Array<{
    url: string
    click_count: number
    unique_clicks: number
  }>
  device_stats: Record<string, number>
  geo_stats: Record<string, number>
  timeline: Array<{
    date: string
    opens: number
    clicks: number
  }>
}

// Email Recipient Types
export type RecipientStatus = 'pending' | 'sent' | 'delivered' | 'opened' | 'clicked' | 'bounced' | 'failed'

export interface EmailRecipient {
  id: number
  email: string
  name: string | null
  status: RecipientStatus
  merge_fields: Record<string, any>
  sent_at: string | null
  delivered_at: string | null
  opened_at: string | null
  clicked_at: string | null
  bounced_at: string | null
  unsubscribed_at: string | null
  open_count: number
  click_count: number
  error_code: string | null
  error_message: string | null
  user?: { id: number; name: string }
}

export interface RecipientListItem {
  id: number
  email: string
  name: string | null
  status: RecipientStatus
  sent_at: string | null
  opened_at: string | null
  clicked_at: string | null
  open_count: number
  click_count: number
}

// Email List Types
export type ListType = 'static' | 'dynamic'
export type SubscriberStatus = 'subscribed' | 'unsubscribed' | 'cleaned' | 'pending'

export interface EmailList {
  id: number
  name: string
  description: string | null
  type: ListType
  filters: ListFilters | null
  subscriber_count: number
  active_count: number
  unsubscribed_count: number
  is_active: boolean
  is_default: boolean
  created_by: number
  creator?: { id: number; name: string }
  created_at: string
  updated_at: string
}

export interface ListFilters {
  roles?: string[]
  created_after?: string
  created_before?: string
  has_subscription?: boolean
  has_enrollment?: boolean
}

export interface EmailListItem {
  id: number
  name: string
  description: string | null
  type: ListType
  subscriber_count: number
  active_count: number
  is_active: boolean
  is_default: boolean
  created_at: string
}

export interface ListSubscriber {
  id: number
  email: string
  name: string | null
  status: SubscriberStatus
  custom_fields: Record<string, any>
  source: string
  subscribed_at: string | null
  unsubscribed_at: string | null
  unsubscribe_reason: string | null
  user?: { id: number; name: string }
  created_at: string
}

export interface SubscriberListItem {
  id: number
  email: string
  name: string | null
  status: SubscriberStatus
  source: string
  subscribed_at: string | null
}

// Unsubscribe Types
export interface EmailUnsubscribe {
  id: number
  email: string
  reason: string | null
  reason_label: string | null
  feedback: string | null
  scope: string
  user?: { id: number; name: string }
  campaign?: { id: number; name: string }
  created_at: string
}

export interface UnsubscribeReason {
  key: string
  label: string
}

export interface UnsubscribeStats {
  total: number
  by_reason: Record<string, number>
  last_30_days: number
}

// Form/Request Types
export interface CreateTemplateData {
  name: string
  subject: string
  content_html: string
  content_text?: string
  type?: string
  category?: string
  variables?: string[]
  settings?: Record<string, any>
  is_active?: boolean
}

export interface CreateCampaignData {
  name: string
  subject: string
  from_name: string
  from_email: string
  reply_to?: string
  template_id?: number
  content_html?: string
  content_text?: string
  type?: CampaignType
  settings?: Record<string, any>
  scheduled_at?: string
}

export interface AddRecipientsData {
  source: 'list' | 'users' | 'emails'
  list_id?: number
  user_ids?: number[]
  emails?: Array<{ email: string; name?: string }>
}

export interface CreateListData {
  name: string
  description?: string
  type?: ListType
  filters?: ListFilters
  is_active?: boolean
  is_default?: boolean
}

export interface AddSubscribersData {
  subscribers: Array<{
    email?: string
    user_id?: number
    name?: string
    source?: string
  }>
}
